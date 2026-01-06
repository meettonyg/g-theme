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
     * Get full dashboard data
     */
    public static function get_dashboard_data(WP_REST_Request $request) {
        $user_id = get_current_user_id();
        $period = $request->get_param('period');
        $goal = $request->get_param('goal');

        $pipeline = guestify_get_pipeline_data($user_id, $period);
        $outcomes = guestify_get_outcomes_data($user_id, $period);
        $attribution = guestify_get_attribution_data($user_id, $period);

        return new WP_REST_Response(array(
            'success'     => true,
            'pipeline'    => $pipeline,
            'outcomes'    => $outcomes,
            'attribution' => $attribution,
            'insight'     => $pipeline['insight'] ?? '',
        ), 200);
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

        return new WP_REST_Response(array(
            'success' => true,
            'goal'    => $goal,
        ), 200);
    }

    /**
     * Get pipeline data only
     */
    public static function get_pipeline_data(WP_REST_Request $request) {
        $user_id = get_current_user_id();
        $period = $request->get_param('period') ?: '30days';

        return new WP_REST_Response(array(
            'success'  => true,
            'pipeline' => guestify_get_pipeline_data($user_id, $period),
        ), 200);
    }

    /**
     * Get outcomes data only
     */
    public static function get_outcomes_data(WP_REST_Request $request) {
        $user_id = get_current_user_id();
        $period = $request->get_param('period') ?: '30days';

        return new WP_REST_Response(array(
            'success'  => true,
            'outcomes' => guestify_get_outcomes_data($user_id, $period),
        ), 200);
    }

    /**
     * Get attribution data only
     */
    public static function get_attribution_data(WP_REST_Request $request) {
        $user_id = get_current_user_id();
        $period = $request->get_param('period') ?: '30days';

        return new WP_REST_Response(array(
            'success'     => true,
            'attribution' => guestify_get_attribution_data($user_id, $period),
        ), 200);
    }
}

// Initialize the API
Guestify_Dashboard_API::init();
