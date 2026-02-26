<?php
/**
 * Guestify Dashboard REST API
 *
 * Handles REST API endpoints for the performance dashboard.
 *
 * @package Guestify
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Guestify_Dashboard_API
 */
class Guestify_Dashboard_API {

    /**
     * API namespace
     */
    const API_NAMESPACE = 'guestify/v1';

    /**
     * Initialize the API
     */
    public static function init() {
        add_action('rest_api_init', array(__CLASS__, 'register_routes'));
    }

    /**
     * Register REST API routes
     */
    public static function register_routes() {
        // Get full dashboard data
        register_rest_route(self::API_NAMESPACE, '/dashboard/data', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array(__CLASS__, 'get_dashboard_data'),
            'permission_callback' => array(__CLASS__, 'check_permissions'),
            'args'                => array(
                'period' => array(
                    'default'           => '30days',
                    'sanitize_callback' => 'sanitize_key',
                ),
                'goal' => array(
                    'default'           => 'revenue',
                    'sanitize_callback' => 'sanitize_key',
                ),
            ),
        ));

        // Save goal preference
        register_rest_route(self::API_NAMESPACE, '/dashboard/goal', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array(__CLASS__, 'save_goal'),
            'permission_callback' => array(__CLASS__, 'check_permissions'),
        ));

        // Get pipeline data only
        register_rest_route(self::API_NAMESPACE, '/dashboard/pipeline', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array(__CLASS__, 'get_pipeline_data'),
            'permission_callback' => array(__CLASS__, 'check_permissions'),
        ));

        // Get outcomes data only
        register_rest_route(self::API_NAMESPACE, '/dashboard/outcomes', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array(__CLASS__, 'get_outcomes_data'),
            'permission_callback' => array(__CLASS__, 'check_permissions'),
        ));

        // Get attribution data only
        register_rest_route(self::API_NAMESPACE, '/dashboard/attribution', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array(__CLASS__, 'get_attribution_data'),
            'permission_callback' => array(__CLASS__, 'check_permissions'),
        ));
    }

    /**
     * Check if user has permission
     */
    public static function check_permissions() {
        return is_user_logged_in();
    }

    /**
     * Get full dashboard data (cached 15 min per user+period+goal)
     */
    public static function get_dashboard_data(WP_REST_Request $request) {
        $user_id = get_current_user_id();
        $period = $request->get_param('period');
        $goal = $request->get_param('goal');

        $cache_key = sprintf('gfy_dashboard_%d_%s_%s', $user_id, $period, $goal);
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return new WP_REST_Response($cached, 200);
        }

        $pipeline = guestify_get_pipeline_data($user_id, $period);
        $outcomes = guestify_get_outcomes_data($user_id, $period);
        $attribution = guestify_get_attribution_data($user_id, $period);

        $data = array(
            'success'     => true,
            'pipeline'    => $pipeline,
            'outcomes'    => $outcomes,
            'attribution' => $attribution,
            'insight'     => $pipeline['insight'] ?? '',
        );

        set_transient($cache_key, $data, 15 * MINUTE_IN_SECONDS);

        return new WP_REST_Response($data, 200);
    }

    /**
     * Save goal preference
     */
    public static function save_goal(WP_REST_Request $request) {
        $user_id = get_current_user_id();
        $params = $request->get_json_params();
        $goal = isset($params['goal']) ? sanitize_key($params['goal']) : 'revenue';

        $valid_goals = array('revenue', 'authority', 'launch');
        if (!in_array($goal, $valid_goals)) {
            $goal = 'revenue';
        }

        update_user_meta($user_id, 'guestify_current_goal', $goal);

        // Clear dashboard caches for this user
        self::clear_user_cache($user_id);

        return new WP_REST_Response(array(
            'success' => true,
            'goal'    => $goal,
        ), 200);
    }

    /**
     * Clear all dashboard caches for a user
     */
    public static function clear_user_cache($user_id) {
        global $wpdb;
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
            '_transient_gfy_dashboard_' . $user_id . '_%',
            '_transient_timeout_gfy_dashboard_' . $user_id . '_%'
        ));
    }

    /**
     * Get pipeline data only
     */
    public static function get_pipeline_data(WP_REST_Request $request) {
        $user_id = get_current_user_id();
        $period = $request->get_param('period') ?: '30days';

        $cache_key = sprintf('gfy_dashboard_%d_%s_pipeline', $user_id, $period);
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return new WP_REST_Response($cached, 200);
        }

        $data = array(
            'success'  => true,
            'pipeline' => guestify_get_pipeline_data($user_id, $period),
        );
        set_transient($cache_key, $data, 15 * MINUTE_IN_SECONDS);

        return new WP_REST_Response($data, 200);
    }

    /**
     * Get outcomes data only
     */
    public static function get_outcomes_data(WP_REST_Request $request) {
        $user_id = get_current_user_id();
        $period = $request->get_param('period') ?: '30days';

        $cache_key = sprintf('gfy_dashboard_%d_%s_outcomes', $user_id, $period);
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return new WP_REST_Response($cached, 200);
        }

        $data = array(
            'success'  => true,
            'outcomes' => guestify_get_outcomes_data($user_id, $period),
        );
        set_transient($cache_key, $data, 15 * MINUTE_IN_SECONDS);

        return new WP_REST_Response($data, 200);
    }

    /**
     * Get attribution data only
     */
    public static function get_attribution_data(WP_REST_Request $request) {
        $user_id = get_current_user_id();
        $period = $request->get_param('period') ?: '30days';

        $cache_key = sprintf('gfy_dashboard_%d_%s_attribution', $user_id, $period);
        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return new WP_REST_Response($cached, 200);
        }

        $data = array(
            'success'     => true,
            'attribution' => guestify_get_attribution_data($user_id, $period),
        );
        set_transient($cache_key, $data, 15 * MINUTE_IN_SECONDS);

        return new WP_REST_Response($data, 200);
    }
}

// Initialize the API
Guestify_Dashboard_API::init();
