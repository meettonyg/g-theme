<?php
/**
 * Guestify Account REST API
 *
 * Handles REST API endpoints for the account settings page.
 *
 * @package Guestify
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Guestify_Account_API
 */
class Guestify_Account_API {

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
        // Profile endpoints
        register_rest_route(self::API_NAMESPACE, '/account/profile', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array(__CLASS__, 'get_profile'),
                'permission_callback' => array(__CLASS__, 'check_permissions'),
            ),
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array(__CLASS__, 'update_profile'),
                'permission_callback' => array(__CLASS__, 'check_permissions'),
            ),
        ));

        // Avatar endpoint
        register_rest_route(self::API_NAMESPACE, '/account/avatar', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array(__CLASS__, 'update_avatar'),
            'permission_callback' => array(__CLASS__, 'check_permissions'),
        ));

        // Notification preferences
        register_rest_route(self::API_NAMESPACE, '/account/notifications', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array(__CLASS__, 'get_notifications'),
                'permission_callback' => array(__CLASS__, 'check_permissions'),
            ),
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array(__CLASS__, 'update_notification'),
                'permission_callback' => array(__CLASS__, 'check_permissions'),
            ),
        ));

        // API key management
        register_rest_route(self::API_NAMESPACE, '/account/api-key/regenerate', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array(__CLASS__, 'regenerate_api_key'),
            'permission_callback' => array(__CLASS__, 'check_permissions'),
        ));

        // Integrations
        register_rest_route(self::API_NAMESPACE, '/account/integrations', array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array(__CLASS__, 'get_integrations'),
                'permission_callback' => array(__CLASS__, 'check_permissions'),
            ),
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array(__CLASS__, 'manage_integration'),
                'permission_callback' => array(__CLASS__, 'check_permissions'),
            ),
        ));

        // Usage data
        register_rest_route(self::API_NAMESPACE, '/account/usage', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array(__CLASS__, 'get_usage'),
            'permission_callback' => array(__CLASS__, 'check_permissions'),
        ));

        // Billing data (PMPro integration)
        register_rest_route(self::API_NAMESPACE, '/account/billing', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array(__CLASS__, 'get_billing'),
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
     * Get user profile
     */
    public static function get_profile(WP_REST_Request $request) {
        $user = wp_get_current_user();

        return new WP_REST_Response(array(
            'success' => true,
            'profile' => array(
                'id'         => $user->ID,
                'first_name' => $user->first_name,
                'last_name'  => $user->last_name,
                'email'      => $user->user_email,
                'job_title'  => get_user_meta($user->ID, 'job_title', true),
                'avatar_url' => get_avatar_url($user->ID, array('size' => 160)),
            ),
        ), 200);
    }

    /**
     * Update user profile
     */
    public static function update_profile(WP_REST_Request $request) {
        $user_id = get_current_user_id();
        $params = $request->get_json_params();

        $user_data = array('ID' => $user_id);

        if (isset($params['first_name'])) {
            $user_data['first_name'] = sanitize_text_field($params['first_name']);
        }

        if (isset($params['last_name'])) {
            $user_data['last_name'] = sanitize_text_field($params['last_name']);
        }

        if (isset($params['email'])) {
            $email = sanitize_email($params['email']);
            if (!is_email($email)) {
                return new WP_REST_Response(array(
                    'success' => false,
                    'message' => 'Invalid email address',
                ), 400);
            }
            $user_data['user_email'] = $email;
        }

        $result = wp_update_user($user_data);

        if (is_wp_error($result)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $result->get_error_message(),
            ), 400);
        }

        // Update custom meta
        if (isset($params['job_title'])) {
            update_user_meta($user_id, 'job_title', sanitize_text_field($params['job_title']));
        }

        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'Profile updated successfully',
        ), 200);
    }

    /**
     * Update user avatar
     */
    public static function update_avatar(WP_REST_Request $request) {
        $user_id = get_current_user_id();
        $files = $request->get_file_params();

        if (empty($files['avatar'])) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'No avatar file uploaded',
            ), 400);
        }

        // Handle file upload
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $attachment_id = media_handle_upload('avatar', 0);

        if (is_wp_error($attachment_id)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => $attachment_id->get_error_message(),
            ), 400);
        }

        // Store avatar ID in user meta (for custom avatar plugins)
        update_user_meta($user_id, 'guestify_avatar_id', $attachment_id);

        $avatar_url = wp_get_attachment_image_url($attachment_id, 'thumbnail');

        return new WP_REST_Response(array(
            'success'    => true,
            'message'    => 'Avatar updated successfully',
            'avatar_url' => $avatar_url,
        ), 200);
    }

    /**
     * Get notification preferences
     */
    public static function get_notifications(WP_REST_Request $request) {
        $user_id = get_current_user_id();

        $defaults = array(
            'booking_requests'   => true,
            'message_replies'    => true,
            'weekly_digest'      => false,
            'product_updates'    => true,
            'marketing_emails'   => false,
            'desktop_notifications' => true,
            'sound_alerts'       => false,
        );

        $preferences = get_user_meta($user_id, 'guestify_notification_prefs', true);
        $preferences = is_array($preferences) ? array_merge($defaults, $preferences) : $defaults;

        return new WP_REST_Response(array(
            'success'     => true,
            'preferences' => $preferences,
        ), 200);
    }

    /**
     * Update notification preference
     */
    public static function update_notification(WP_REST_Request $request) {
        $user_id = get_current_user_id();
        $params = $request->get_json_params();

        if (!isset($params['setting'])) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Missing setting parameter',
            ), 400);
        }

        $setting = sanitize_key($params['setting']);
        $enabled = !empty($params['enabled']);

        $preferences = get_user_meta($user_id, 'guestify_notification_prefs', true);
        if (!is_array($preferences)) {
            $preferences = array();
        }

        $preferences[$setting] = $enabled;
        update_user_meta($user_id, 'guestify_notification_prefs', $preferences);

        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'Notification preference updated',
        ), 200);
    }

    /**
     * Regenerate API key
     */
    public static function regenerate_api_key(WP_REST_Request $request) {
        $user_id = get_current_user_id();

        // Generate new API key
        $api_key = 'sk_live_' . wp_generate_password(32, false);

        // Store hashed version
        update_user_meta($user_id, 'guestify_api_key_hash', wp_hash($api_key));
        update_user_meta($user_id, 'guestify_api_key_created', current_time('mysql'));

        return new WP_REST_Response(array(
            'success' => true,
            'api_key' => $api_key,
            'message' => 'API key regenerated successfully',
        ), 200);
    }

    /**
     * Get integrations status
     */
    public static function get_integrations(WP_REST_Request $request) {
        $user_id = get_current_user_id();

        $integrations = array(
            'hubspot' => array(
                'name'      => 'HubSpot',
                'connected' => !empty(get_user_meta($user_id, 'guestify_hubspot_token', true)),
            ),
            'slack' => array(
                'name'      => 'Slack',
                'connected' => !empty(get_user_meta($user_id, 'guestify_slack_token', true)),
            ),
            'spotify' => array(
                'name'      => 'Spotify for Podcasters',
                'connected' => !empty(get_user_meta($user_id, 'guestify_spotify_token', true)),
            ),
            'linkedin' => array(
                'name'      => 'LinkedIn',
                'connected' => !empty(get_user_meta($user_id, 'guestify_linkedin_token', true)),
            ),
        );

        return new WP_REST_Response(array(
            'success'      => true,
            'integrations' => $integrations,
        ), 200);
    }

    /**
     * Manage integration connection
     */
    public static function manage_integration(WP_REST_Request $request) {
        $user_id = get_current_user_id();
        $params = $request->get_json_params();

        $integration = isset($params['integration']) ? sanitize_key($params['integration']) : '';
        $action = isset($params['action']) ? sanitize_key($params['action']) : 'connect';

        if (empty($integration)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Invalid integration',
            ), 400);
        }

        if ($action === 'disconnect') {
            delete_user_meta($user_id, 'guestify_' . $integration . '_token');
            delete_user_meta($user_id, 'guestify_' . $integration . '_data');

            return new WP_REST_Response(array(
                'success' => true,
                'message' => ucfirst($integration) . ' disconnected successfully',
            ), 200);
        }

        // For OAuth integrations, return the authorization URL
        $oauth_urls = array(
            'hubspot'  => 'https://app.hubspot.com/oauth/authorize',
            'slack'    => 'https://slack.com/oauth/v2/authorize',
            'linkedin' => 'https://www.linkedin.com/oauth/v2/authorization',
        );

        if (isset($oauth_urls[$integration])) {
            return new WP_REST_Response(array(
                'success'      => true,
                'redirect_url' => $oauth_urls[$integration] . '?client_id=YOUR_CLIENT_ID&redirect_uri=' . urlencode(home_url('/account/oauth-callback/' . $integration)),
            ), 200);
        }

        return new WP_REST_Response(array(
            'success' => false,
            'message' => 'Integration not available',
        ), 400);
    }

    /**
     * Get usage data
     */
    public static function get_usage(WP_REST_Request $request) {
        $user_id = get_current_user_id();
        $usage_data = guestify_get_account_usage_data($user_id);

        return new WP_REST_Response(array(
            'success' => true,
            'usage'   => $usage_data,
        ), 200);
    }

    /**
     * Get billing data
     */
    public static function get_billing(WP_REST_Request $request) {
        $user_id = get_current_user_id();
        $billing_data = guestify_get_account_billing_data($user_id);

        return new WP_REST_Response(array(
            'success' => true,
            'billing' => $billing_data,
        ), 200);
    }
}

// Initialize the API
Guestify_Account_API::init();
