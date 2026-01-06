<?php
/**
 * Guestify Home Dashboard REST API
 *
 * Provides REST endpoints for the home page dashboard data.
 *
 * @package Guestify
 * @version 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Guestify_Home_Dashboard_API
 *
 * Handles REST API endpoints for the home dashboard.
 */
class Guestify_Home_Dashboard_API {

	/**
	 * REST namespace
	 */
	const NAMESPACE = 'guestify/v1';

	/**
	 * Cache key prefix
	 */
	const CACHE_PREFIX = 'gfy_home_';

	/**
	 * Cache expiration in seconds (5 minutes)
	 */
	const CACHE_EXPIRATION = 300;

	/**
	 * Initialize the API
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST routes
	 */
	public function register_routes() {
		// Dashboard data endpoint
		register_rest_route( self::NAMESPACE, '/home/dashboard', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_dashboard' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );

		// Goal preference endpoint
		register_rest_route( self::NAMESPACE, '/home/goal', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'save_goal' ),
			'permission_callback' => array( $this, 'check_permission' ),
			'args'                => array(
				'goal' => array(
					'required'          => true,
					'type'              => 'string',
					'enum'              => array( 'build_authority', 'grow_revenue', 'launch_promote' ),
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		) );

		// Recent activity endpoint
		register_rest_route( self::NAMESPACE, '/home/activity', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_activity' ),
			'permission_callback' => array( $this, 'check_permission' ),
			'args'                => array(
				'limit' => array(
					'default'           => 5,
					'type'              => 'integer',
					'minimum'           => 1,
					'maximum'           => 20,
					'sanitize_callback' => 'absint',
				),
			),
		) );

		// Quick stats endpoint
		register_rest_route( self::NAMESPACE, '/home/stats', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_stats' ),
			'permission_callback' => array( $this, 'check_permission' ),
		) );

		// Clear cache endpoint (admin only)
		register_rest_route( self::NAMESPACE, '/home/cache/clear', array(
			'methods'             => WP_REST_Server::DELETABLE,
			'callback'            => array( $this, 'clear_cache' ),
			'permission_callback' => array( $this, 'check_admin_permission' ),
		) );
	}

	/**
	 * Check if user has permission to access endpoints
	 *
	 * @return bool|WP_Error
	 */
	public function check_permission() {
		if ( ! is_user_logged_in() ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You must be logged in to access this endpoint.', 'guestify' ),
				array( 'status' => 401 )
			);
		}
		return true;
	}

	/**
	 * Check if user has admin permission
	 *
	 * @return bool|WP_Error
	 */
	public function check_admin_permission() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to access this endpoint.', 'guestify' ),
				array( 'status' => 403 )
			);
		}
		return true;
	}

	/**
	 * Get dashboard data
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_dashboard( WP_REST_Request $request ) {
		$user_id = get_current_user_id();

		// Check cache first
		$cache_key = self::CACHE_PREFIX . 'dashboard_' . $user_id;
		$cached_data = get_transient( $cache_key );

		if ( false !== $cached_data ) {
			return rest_ensure_response( $cached_data );
		}

		// Get fresh data
		$data = guestify_get_home_dashboard_data( $user_id );

		// Add user info
		$current_user = wp_get_current_user();
		$data['user'] = array(
			'id'       => $user_id,
			'name'     => ! empty( $current_user->first_name ) ? $current_user->first_name : $current_user->display_name,
			'email'    => $current_user->user_email,
			'initials' => strtoupper( substr( $current_user->display_name, 0, 1 ) ),
		);

		// Add current goal
		$data['current_goal'] = get_user_meta( $user_id, 'guestify_current_goal', true ) ?: 'grow_revenue';

		// Cache the data
		set_transient( $cache_key, $data, self::CACHE_EXPIRATION );

		return rest_ensure_response( $data );
	}

	/**
	 * Save goal preference
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function save_goal( WP_REST_Request $request ) {
		$user_id = get_current_user_id();
		$goal = $request->get_param( 'goal' );

		// Update user meta
		$updated = update_user_meta( $user_id, 'guestify_current_goal', $goal );

		// Clear dashboard cache
		delete_transient( self::CACHE_PREFIX . 'dashboard_' . $user_id );

		return rest_ensure_response( array(
			'success' => true,
			'goal'    => $goal,
			'message' => __( 'Goal preference saved.', 'guestify' ),
		) );
	}

	/**
	 * Get recent activity
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_activity( WP_REST_Request $request ) {
		$user_id = get_current_user_id();
		$limit = $request->get_param( 'limit' );

		// Get activity data
		$activities = guestify_get_recent_activity( $user_id );

		// Apply limit
		$activities = array_slice( $activities, 0, $limit );

		return rest_ensure_response( array(
			'success'    => true,
			'activities' => $activities,
			'count'      => count( $activities ),
		) );
	}

	/**
	 * Get quick stats
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_stats( WP_REST_Request $request ) {
		$user_id = get_current_user_id();

		// Check cache
		$cache_key = self::CACHE_PREFIX . 'stats_' . $user_id;
		$cached_stats = get_transient( $cache_key );

		if ( false !== $cached_stats ) {
			return rest_ensure_response( $cached_stats );
		}

		// Get fresh stats
		$stats = array(
			'pitches'    => guestify_get_outreach_pitches_count( $user_id ),
			'interviews' => 0,
			'episodes'   => 0,
			'revenue'    => 0,
		);

		// Merge ShowAuthority stats
		$sa_stats = guestify_get_showauthority_stats( $user_id );
		$stats = array_merge( $stats, $sa_stats );

		// Cache the stats
		set_transient( $cache_key, $stats, self::CACHE_EXPIRATION );

		return rest_ensure_response( array(
			'success' => true,
			'stats'   => $stats,
		) );
	}

	/**
	 * Clear cache
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public function clear_cache( WP_REST_Request $request ) {
		global $wpdb;

		// Delete all home dashboard transients
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
				'_transient_' . self::CACHE_PREFIX . '%',
				'_transient_timeout_' . self::CACHE_PREFIX . '%'
			)
		);

		return rest_ensure_response( array(
			'success' => true,
			'message' => __( 'Home dashboard cache cleared.', 'guestify' ),
		) );
	}
}

// Initialize the API
new Guestify_Home_Dashboard_API();
