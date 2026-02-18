<?php
/**
 * REST API Controller — Access Gate Rules (Admin)
 *
 * Provides admin endpoints to view, create, update, delete, and test
 * access gate rules. All rules are stored in wp_options.
 *
 * Endpoints:
 *   GET    /guestify/v1/access-gate/rules  – list all rules
 *   POST   /guestify/v1/access-gate/rules  – create/update a custom rule
 *   DELETE /guestify/v1/access-gate/rules  – delete a custom rule
 *   POST   /guestify/v1/access-gate/test   – test which rule matches a path
 *   POST   /guestify/v1/access-gate/reset  – remove all custom rules
 *
 * @package Guestify
 * @since   1.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GFY_Access_Gate_API {

    const API_NAMESPACE = 'guestify/v1';

    /**
     * Initialize: register REST routes on rest_api_init.
     */
    public static function init(): void {
        add_action('rest_api_init', [__CLASS__, 'register_routes']);
    }

    /**
     * Register all REST routes.
     */
    public static function register_routes(): void {
        // List / Create / Delete rules
        register_rest_route(self::API_NAMESPACE, '/access-gate/rules', [
            [
                'methods'             => 'GET',
                'callback'            => [__CLASS__, 'get_rules'],
                'permission_callback' => [__CLASS__, 'check_admin'],
            ],
            [
                'methods'             => 'POST',
                'callback'            => [__CLASS__, 'save_rule'],
                'permission_callback' => [__CLASS__, 'check_admin'],
            ],
            [
                'methods'             => 'DELETE',
                'callback'            => [__CLASS__, 'delete_rule'],
                'permission_callback' => [__CLASS__, 'check_admin'],
            ],
        ]);

        // Test a URL path against the rules
        register_rest_route(self::API_NAMESPACE, '/access-gate/test', [
            'methods'             => 'POST',
            'callback'            => [__CLASS__, 'test_path'],
            'permission_callback' => [__CLASS__, 'check_admin'],
        ]);

        // Reset all custom rules
        register_rest_route(self::API_NAMESPACE, '/access-gate/reset', [
            'methods'             => 'POST',
            'callback'            => [__CLASS__, 'reset_rules'],
            'permission_callback' => [__CLASS__, 'check_admin'],
        ]);
    }

    /**
     * Permission check: must be a WordPress admin.
     */
    public static function check_admin(): bool {
        return current_user_can('manage_options');
    }

    /* ------------------------------------------------------------------
     * GET /access-gate/rules
     * ----------------------------------------------------------------*/

    /**
     * List all rules (defaults + custom + plugin) with source labels.
     * Also returns available tiers and the hardcoded defaults.
     */
    public static function get_rules(): WP_REST_Response {
        $gate  = GFY_Access_Gate::get_instance();
        $rules = $gate->get_all_rules();
        $tiers = self::get_available_tiers();

        return rest_ensure_response([
            'success'  => true,
            'rules'    => $rules,
            'tiers'    => $tiers,
            'defaults' => GFY_Access_Gate::get_default_rules(),
        ]);
    }

    /* ------------------------------------------------------------------
     * POST /access-gate/rules
     * ----------------------------------------------------------------*/

    /**
     * Create or update a custom rule.
     */
    public static function save_rule(WP_REST_Request $request): WP_REST_Response {
        $params = $request->get_json_params();

        // Validate path
        $path = $params['path'] ?? '';
        if (empty($path) || !is_string($path)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Path is required.',
            ], 400);
        }

        // Normalise path
        $path = '/' . trim(sanitize_text_field($path), '/') . '/';

        // Build the rule config
        $rule = [
            'auth_required' => !empty($params['auth_required']),
            'public'        => !empty($params['public']),
            'required_tier' => sanitize_key($params['required_tier'] ?? ''),
            'required_tags' => self::sanitize_tags($params['required_tags'] ?? []),
            'capability'    => sanitize_text_field($params['capability'] ?? ''),
            'redirect_to'   => sanitize_text_field($params['redirect_to'] ?? ''),
            'match_type'    => in_array($params['match_type'] ?? '', ['prefix', 'exact'], true)
                                ? $params['match_type']
                                : 'prefix',
        ];

        // Public and auth_required are mutually exclusive
        if ($rule['public']) {
            $rule['auth_required'] = false;
            $rule['required_tier'] = '';
            $rule['required_tags'] = [];
            $rule['capability']    = '';
            $rule['redirect_to']   = '';
        }

        // Validate tier if specified
        if (!empty($rule['required_tier'])) {
            $tiers = self::get_available_tiers();
            if (!isset($tiers[$rule['required_tier']])) {
                return new WP_REST_Response([
                    'success' => false,
                    'message' => 'Invalid tier: ' . $rule['required_tier'],
                ], 400);
            }
        }

        // Save to wp_options
        $custom = get_option(GFY_Access_Gate::OPTION_KEY, []);
        if (!is_array($custom)) {
            $custom = [];
        }

        $custom[$path] = $rule;
        update_option(GFY_Access_Gate::OPTION_KEY, $custom, false);

        return rest_ensure_response([
            'success' => true,
            'message' => 'Rule saved.',
            'path'    => $path,
            'rule'    => $rule,
        ]);
    }

    /* ------------------------------------------------------------------
     * DELETE /access-gate/rules
     * ----------------------------------------------------------------*/

    /**
     * Delete a custom rule.
     */
    public static function delete_rule(WP_REST_Request $request): WP_REST_Response {
        $params = $request->get_json_params();
        $path   = $params['path'] ?? '';

        if (empty($path)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Path is required.',
            ], 400);
        }

        $path = '/' . trim(sanitize_text_field($path), '/') . '/';

        $custom = get_option(GFY_Access_Gate::OPTION_KEY, []);
        if (!is_array($custom) || !isset($custom[$path])) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Custom rule not found for path: ' . $path,
            ], 404);
        }

        unset($custom[$path]);
        update_option(GFY_Access_Gate::OPTION_KEY, $custom, false);

        return rest_ensure_response([
            'success' => true,
            'message' => 'Rule deleted.',
            'path'    => $path,
        ]);
    }

    /* ------------------------------------------------------------------
     * POST /access-gate/test
     * ----------------------------------------------------------------*/

    /**
     * Test which rule matches a given URL path.
     */
    public static function test_path(WP_REST_Request $request): WP_REST_Response {
        $params    = $request->get_json_params();
        $test_path = $params['test_path'] ?? '';

        if (empty($test_path)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'test_path is required.',
            ], 400);
        }

        $gate   = GFY_Access_Gate::get_instance();
        $result = $gate->test_match(sanitize_text_field($test_path));

        // Determine what would happen
        $outcome = 'allow';
        if ($result === null) {
            $outcome = 'no_rule';
        } elseif (!empty($result['public'])) {
            $outcome = 'allow';
        } elseif (!empty($result['auth_required'])) {
            $outcome = 'login_required';
            if (!empty($result['required_tier']) || !empty($result['required_tags']) || !empty($result['capability'])) {
                $outcome = 'tier_or_capability_required';
            }
        }

        return rest_ensure_response([
            'success'  => true,
            'matched'  => $result !== null,
            'rule'     => $result,
            'outcome'  => $outcome,
        ]);
    }

    /* ------------------------------------------------------------------
     * POST /access-gate/reset
     * ----------------------------------------------------------------*/

    /**
     * Remove all custom rules, restoring defaults.
     */
    public static function reset_rules(): WP_REST_Response {
        delete_option(GFY_Access_Gate::OPTION_KEY);

        return rest_ensure_response([
            'success' => true,
            'message' => 'All custom rules removed. Defaults restored.',
        ]);
    }

    /* ------------------------------------------------------------------
     * Helpers
     * ----------------------------------------------------------------*/

    /**
     * Get available tiers from PIT_Guestify_Tier_Resolver (if loaded).
     *
     * @return array Tiers keyed by slug with name + priority.
     */
    private static function get_available_tiers(): array {
        if (!class_exists('PIT_Guestify_Tier_Resolver')) {
            // Fallback: return the standard tiers
            return [
                'free'        => ['name' => 'Free',        'priority' => 0],
                'accelerator' => ['name' => 'Accelerator', 'priority' => 40],
                'velocity'    => ['name' => 'Velocity',    'priority' => 60],
                'zenith'      => ['name' => 'Zenith',      'priority' => 80],
                'unlimited'   => ['name' => 'Unlimited',   'priority' => 100],
            ];
        }

        $tiers  = PIT_Guestify_Tier_Resolver::get_tiers();
        $result = [];
        foreach ($tiers as $key => $tier) {
            $result[$key] = [
                'name'     => $tier['name'] ?? ucfirst($key),
                'priority' => (int) ($tier['priority'] ?? 0),
            ];
        }
        return $result;
    }

    /**
     * Sanitize a tags array (accepts array or comma-separated string).
     *
     * @param mixed $tags Raw tags input.
     * @return array Sanitized tag strings.
     */
    private static function sanitize_tags($tags): array {
        if (is_string($tags)) {
            $tags = array_map('trim', explode(',', $tags));
        }
        if (!is_array($tags)) {
            return [];
        }
        return array_values(array_filter(array_map('sanitize_text_field', $tags)));
    }
}
