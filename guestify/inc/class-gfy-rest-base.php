<?php
/**
 * REST API Base Controller (Theme-level)
 *
 * Slim base class for Guestify platform REST controllers.
 * Provides permission callbacks and response helpers only.
 *
 * NOTE: This does NOT include ShowAuthority-specific features
 * (rate limiting, user context, user limits, export tracking).
 * Those stay in PIT_REST_Base for Intel plugin controllers.
 *
 * @package Guestify
 * @since   2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

abstract class GFY_REST_Base {

    /**
     * Unified API namespace
     */
    const NAMESPACE = 'guestify/v1';

    /**
     * Register routes (must be implemented by subclasses)
     */
    abstract public static function register_routes();

    /**
     * Check if user has admin permissions
     *
     * @return bool
     */
    public static function check_admin(): bool {
        return current_user_can(GFY_Constants::CAPABILITY_MANAGE);
    }

    /**
     * Check if user is logged in
     *
     * @return bool
     */
    public static function check_logged_in(): bool {
        return is_user_logged_in();
    }

    /**
     * Return a success response
     *
     * @param mixed $data   Response data
     * @param int   $status HTTP status code
     * @return WP_REST_Response
     */
    protected static function success($data, int $status = 200): WP_REST_Response {
        return new WP_REST_Response($data, $status);
    }

    /**
     * Return an error response
     *
     * @param string $code    Error code
     * @param string $message Error message
     * @param int    $status  HTTP status code
     * @return WP_Error
     */
    protected static function error(string $code, string $message, int $status = 400): WP_Error {
        return new WP_Error($code, $message, ['status' => $status]);
    }

    /**
     * Get pagination parameters from request
     *
     * @param WP_REST_Request $request
     * @return array
     */
    protected static function get_pagination_params(WP_REST_Request $request): array {
        return [
            'page'     => max(1, (int) ($request->get_param('page') ?? 1)),
            'per_page' => min(100, max(1, (int) ($request->get_param('per_page') ?? 20))),
        ];
    }

    /**
     * Get search parameter from request
     *
     * @param WP_REST_Request $request
     * @return string
     */
    protected static function get_search_param(WP_REST_Request $request): string {
        return sanitize_text_field($request->get_param('search') ?? '');
    }
}
