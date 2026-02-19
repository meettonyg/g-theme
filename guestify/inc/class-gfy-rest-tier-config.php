<?php
/**
 * REST API Controller — Tier Configuration (Admin)
 *
 * Provides admin endpoints to view and edit tier limits.
 * All limit values are stored in wp_options and editable via these endpoints.
 *
 * @package Guestify
 * @since   2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GFY_REST_Tier_Config extends GFY_REST_Base {

    /**
     * Register routes
     */
    public static function register_routes() {
        // Get all tiers (admin)
        register_rest_route(self::NAMESPACE, '/tier-config', [
            'methods'             => 'GET',
            'callback'            => [__CLASS__, 'get_tiers'],
            'permission_callback' => [__CLASS__, 'check_admin'],
        ]);

        // Update or create a single tier (admin)
        register_rest_route(self::NAMESPACE, '/tier-config/(?P<tier_key>[a-z_]+)', [
            [
                'methods'             => 'PUT',
                'callback'            => [__CLASS__, 'update_tier'],
                'permission_callback' => [__CLASS__, 'check_admin'],
            ],
            [
                'methods'             => 'DELETE',
                'callback'            => [__CLASS__, 'delete_tier'],
                'permission_callback' => [__CLASS__, 'check_admin'],
            ],
        ]);

        // Reset all tiers to defaults (admin)
        register_rest_route(self::NAMESPACE, '/tier-config/reset', [
            'methods'             => 'POST',
            'callback'            => [__CLASS__, 'reset_tiers'],
            'permission_callback' => [__CLASS__, 'check_admin'],
        ]);

        // Get current user's tier summary (authenticated user)
        register_rest_route(self::NAMESPACE, '/tier-config/my-tier', [
            'methods'             => 'GET',
            'callback'            => [__CLASS__, 'get_my_tier'],
            'permission_callback' => function () {
                return is_user_logged_in();
            },
        ]);

        // Get a specific user's tier (admin)
        register_rest_route(self::NAMESPACE, '/tier-config/user/(?P<user_id>\d+)', [
            'methods'             => 'GET',
            'callback'            => [__CLASS__, 'get_user_tier'],
            'permission_callback' => [__CLASS__, 'check_admin'],
        ]);
    }

    /**
     * Get all tier configurations
     */
    public static function get_tiers(): WP_REST_Response {
        $tiers = GFY_Tier_Resolver::get_tiers();

        return rest_ensure_response([
            'success' => true,
            'tiers'   => $tiers,
        ]);
    }

    /**
     * Update a single tier's configuration
     */
    public static function update_tier(WP_REST_Request $request) {
        $tier_key = sanitize_key($request->get_param('tier_key'));
        $body = $request->get_json_params();

        if (empty($body) || !is_array($body)) {
            return self::error('invalid_data', 'Request body must be a JSON object.');
        }

        // Prevent creation without name
        $tiers = GFY_Tier_Resolver::get_tiers();
        if (!isset($tiers[$tier_key]) && !isset($body['name'])) {
            return self::error('missing_name', 'New tiers require a name field.');
        }

        // Merge with existing values for partial updates
        $existing = $tiers[$tier_key] ?? [];
        $merged = array_merge($existing, $body);

        $success = GFY_Tier_Resolver::update_tier($tier_key, $merged);

        if (!$success) {
            return self::error('save_failed', 'Failed to save tier configuration.', 500);
        }

        return rest_ensure_response([
            'success' => true,
            'tier'    => GFY_Tier_Resolver::get_tier($tier_key),
            'message' => "Tier '{$tier_key}' updated successfully.",
        ]);
    }

    /**
     * Delete a tier (admin only).
     * Protected tiers (free, unlimited) cannot be deleted.
     */
    public static function delete_tier(WP_REST_Request $request): WP_REST_Response {
        $tier_key = sanitize_key($request->get_param('tier_key'));

        if (in_array($tier_key, ['free', 'unlimited'], true)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => "The '{$tier_key}' tier is protected and cannot be deleted.",
            ], 400);
        }

        $success = GFY_Tier_Resolver::delete_tier($tier_key);

        if (!$success) {
            return new WP_REST_Response([
                'success' => false,
                'message' => "Tier '{$tier_key}' not found.",
            ], 404);
        }

        return rest_ensure_response([
            'success' => true,
            'tiers'   => GFY_Tier_Resolver::get_tiers(),
            'message' => "Tier '{$tier_key}' deleted.",
        ]);
    }

    /**
     * Reset all tiers to factory defaults
     */
    public static function reset_tiers(): WP_REST_Response {
        GFY_Tier_Resolver::reset_to_defaults();

        return rest_ensure_response([
            'success' => true,
            'tiers'   => GFY_Tier_Resolver::get_tiers(),
            'message' => 'Tiers reset to defaults.',
        ]);
    }

    /**
     * Get the current authenticated user's tier summary
     */
    public static function get_my_tier(): WP_REST_Response {
        return rest_ensure_response([
            'success' => true,
            'data'    => GFY_Tier_Resolver::get_user_tier_summary(),
        ]);
    }

    /**
     * Get a specific user's tier (admin only)
     */
    public static function get_user_tier(WP_REST_Request $request): WP_REST_Response {
        $user_id = (int) $request->get_param('user_id');

        $user = get_user_by('ID', $user_id);
        if (!$user) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        $summary = GFY_Tier_Resolver::get_user_tier_summary($user_id);
        $summary['user'] = [
            'id'    => $user_id,
            'email' => $user->user_email,
            'name'  => $user->display_name,
            'tags'  => GFY_Tier_Resolver::get_user_tags($user_id),
        ];

        return rest_ensure_response([
            'success' => true,
            'data'    => $summary,
        ]);
    }
}
