<?php
/**
 * REST API Controller — Authority Credits
 *
 * User-facing endpoints for credit balance, transactions, and action costs.
 * Admin endpoints for adjusting balances and managing action costs.
 *
 * @package Guestify
 * @since   2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GFY_REST_Credits extends GFY_REST_Base {

    /**
     * Register routes
     */
    public static function register_routes() {
        // --- User endpoints ---

        // Get credit balance + tier info
        register_rest_route(self::NAMESPACE, '/credits/balance', [
            'methods'             => 'GET',
            'callback'            => [__CLASS__, 'get_balance'],
            'permission_callback' => [__CLASS__, 'check_logged_in'],
        ]);

        // Get transaction history
        register_rest_route(self::NAMESPACE, '/credits/transactions', [
            'methods'             => 'GET',
            'callback'            => [__CLASS__, 'get_transactions'],
            'permission_callback' => [__CLASS__, 'check_logged_in'],
        ]);

        // Get action costs + remaining action budget
        register_rest_route(self::NAMESPACE, '/credits/actions', [
            'methods'             => 'GET',
            'callback'            => [__CLASS__, 'get_actions'],
            'permission_callback' => [__CLASS__, 'check_logged_in'],
        ]);

        // Get usage summary for current billing cycle
        register_rest_route(self::NAMESPACE, '/credits/usage', [
            'methods'             => 'GET',
            'callback'            => [__CLASS__, 'get_usage'],
            'permission_callback' => [__CLASS__, 'check_logged_in'],
        ]);

        // --- Admin endpoints ---

        // Adjust a user's balance (admin)
        register_rest_route(self::NAMESPACE, '/credits/adjust', [
            'methods'             => 'POST',
            'callback'            => [__CLASS__, 'adjust_balance'],
            'permission_callback' => [__CLASS__, 'check_admin'],
        ]);

        // Get / Update action costs (admin)
        register_rest_route(self::NAMESPACE, '/credits/action-costs', [
            'methods'             => 'GET',
            'callback'            => [__CLASS__, 'get_action_costs_admin'],
            'permission_callback' => [__CLASS__, 'check_admin'],
        ]);

        register_rest_route(self::NAMESPACE, '/credits/action-costs/(?P<action_type>[a-z_]+)', [
            'methods'             => 'PUT',
            'callback'            => [__CLASS__, 'update_action_cost'],
            'permission_callback' => [__CLASS__, 'check_admin'],
        ]);

        // Get a specific user's balance (admin)
        register_rest_route(self::NAMESPACE, '/credits/user/(?P<user_id>\d+)/balance', [
            'methods'             => 'GET',
            'callback'            => [__CLASS__, 'get_user_balance_admin'],
            'permission_callback' => [__CLASS__, 'check_admin'],
        ]);
    }

    // -------------------------------------------------------------------------
    // User endpoints
    // -------------------------------------------------------------------------

    /**
     * GET /credits/balance
     */
    public static function get_balance(): WP_REST_Response {
        $user_id = get_current_user_id();
        $balance = GFY_Credit_Repository::get_balance($user_id);
        $tier = GFY_Tier_Resolver::get_user_tier_summary($user_id);

        return rest_ensure_response([
            'success' => true,
            'data'    => array_merge($balance, ['tier' => $tier['tier']]),
        ]);
    }

    /**
     * GET /credits/transactions
     */
    public static function get_transactions(WP_REST_Request $request): WP_REST_Response {
        $user_id = get_current_user_id();
        $page = max(1, (int) $request->get_param('page') ?: 1);
        $per_page = min(100, max(1, (int) $request->get_param('per_page') ?: 20));

        $result = GFY_Credit_Repository::get_transactions($user_id, $page, $per_page);

        return rest_ensure_response([
            'success' => true,
            'data'    => $result,
        ]);
    }

    /**
     * GET /credits/actions — action costs + remaining budget
     */
    public static function get_actions(): WP_REST_Response {
        $user_id = get_current_user_id();
        $budget = GFY_Credit_Repository::get_action_budget($user_id);
        $balance = GFY_Credit_Repository::get_balance($user_id);

        return rest_ensure_response([
            'success' => true,
            'data'    => [
                'actions'       => $budget,
                'total_credits' => $balance['total'],
            ],
        ]);
    }

    /**
     * GET /credits/usage — usage summary for current cycle
     */
    public static function get_usage(): WP_REST_Response {
        $user_id = get_current_user_id();
        $usage = GFY_Credit_Repository::get_usage_summary($user_id);

        return rest_ensure_response([
            'success' => true,
            'data'    => $usage,
        ]);
    }

    // -------------------------------------------------------------------------
    // Admin endpoints
    // -------------------------------------------------------------------------

    /**
     * POST /credits/adjust
     */
    public static function adjust_balance(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $body = $request->get_json_params();

        $target_user_id = (int) ($body['user_id'] ?? 0);
        $amount = (int) ($body['amount'] ?? 0);
        $reason = sanitize_text_field($body['reason'] ?? '');

        if (!$target_user_id || $amount === 0) {
            return self::error('invalid_params', 'user_id and non-zero amount are required.');
        }

        if (!get_user_by('ID', $target_user_id)) {
            return self::error('user_not_found', 'User not found.', 404);
        }

        $success = GFY_Credit_Repository::adjust_balance($target_user_id, $amount, $reason);

        if (!$success) {
            return self::error('adjust_failed', 'Failed to adjust balance.', 500);
        }

        return rest_ensure_response([
            'success' => true,
            'message' => sprintf('Adjusted %d credits for user %d.', $amount, $target_user_id),
            'balance' => GFY_Credit_Repository::get_balance($target_user_id),
        ]);
    }

    /**
     * GET /credits/action-costs (admin)
     */
    public static function get_action_costs_admin(): WP_REST_Response {
        global $wpdb;
        $table = $wpdb->prefix . 'pit_credit_action_costs';

        $costs = $wpdb->get_results(
            "SELECT * FROM {$table} ORDER BY category, credits_per_unit DESC",
            ARRAY_A
        );

        return rest_ensure_response([
            'success' => true,
            'data'    => $costs,
        ]);
    }

    /**
     * PUT /credits/action-costs/{action_type} (admin)
     */
    public static function update_action_cost(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $action_type = sanitize_key($request->get_param('action_type'));
        $body = $request->get_json_params();

        global $wpdb;
        $table = $wpdb->prefix . 'pit_credit_action_costs';

        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE action_type = %s",
            $action_type
        ));

        if (!$existing) {
            return self::error('not_found', 'Action type not found.', 404);
        }

        $update = [];
        if (isset($body['credits_per_unit'])) {
            $update['credits_per_unit'] = max(0, (int) $body['credits_per_unit']);
        }
        if (isset($body['description'])) {
            $update['description'] = sanitize_text_field($body['description']);
        }
        if (isset($body['is_active'])) {
            $update['is_active'] = $body['is_active'] ? 1 : 0;
        }

        if (empty($update)) {
            return self::error('no_changes', 'No valid fields to update.');
        }

        $wpdb->update($table, $update, ['action_type' => $action_type]);

        $updated = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE action_type = %s",
            $action_type
        ), ARRAY_A);

        return rest_ensure_response([
            'success' => true,
            'data'    => $updated,
            'message' => "Action cost for '{$action_type}' updated.",
        ]);
    }

    /**
     * GET /credits/user/{user_id}/balance (admin)
     */
    public static function get_user_balance_admin(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $user_id = (int) $request->get_param('user_id');

        if (!get_user_by('ID', $user_id)) {
            return self::error('user_not_found', 'User not found.', 404);
        }

        return rest_ensure_response([
            'success' => true,
            'data'    => GFY_Credit_Repository::get_balance($user_id),
        ]);
    }
}
