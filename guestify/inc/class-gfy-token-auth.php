<?php
/**
 * Guestify Token Authentication Middleware
 *
 * Authenticates incoming REST API requests that carry a Bearer token
 * in the Authorization header. ONLY applies to routes under the
 * `gfy-connect/v1` namespace — does not affect session-based authentication
 * on any other endpoints.
 *
 * Hooks into WordPress `determine_current_user` filter (priority 20, after
 * default cookie-based auth at priority 10).
 *
 * Usage:
 *   GFY_Token_Auth::init();  // Called once during theme init.
 *
 * @package Guestify
 * @since   2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GFY_Token_Auth {

    /**
     * REST namespace that this middleware protects.
     */
    const PROTECTED_NAMESPACE = 'gfy-connect/v1';

    /**
     * Public endpoints that do NOT require Bearer auth.
     * These use their own auth (e.g. auth code or refresh token).
     */
    const PUBLIC_ROUTES = [
        '/gfy-connect/v1/token',
        '/gfy-connect/v1/token/refresh',
    ];

    /**
     * Initialize the middleware.
     *
     * Hooks into `determine_current_user` to inject token-based auth.
     */
    public static function init(): void {
        add_filter( 'determine_current_user', [ __CLASS__, 'authenticate' ], 20 );
        add_filter( 'rest_authentication_errors', [ __CLASS__, 'check_auth_error' ], 99 );
    }

    /**
     * Attempt to authenticate the request via Bearer token.
     *
     * If the request carries an Authorization: Bearer header and targets
     * a gfy-connect/v1 endpoint, validate the token and set the current user.
     *
     * @param int|false $user_id The user ID determined by previous auth handlers.
     *
     * @return int|false User ID if authenticated, otherwise pass through.
     */
    public static function authenticate( $user_id ) {
        // If a user is already authenticated (e.g. cookie-based), let it stand.
        if ( ! empty( $user_id ) ) {
            return $user_id;
        }

        // Only process REST API requests.
        if ( ! self::is_rest_request() ) {
            return $user_id;
        }

        // Only process requests for our namespace.
        if ( ! self::is_connect_request() ) {
            return $user_id;
        }

        // Skip public routes (token exchange, refresh).
        if ( self::is_public_route() ) {
            return $user_id;
        }

        // Extract Bearer token from Authorization header.
        $token = self::get_bearer_token();

        if ( empty( $token ) ) {
            // No token provided — let rest_authentication_errors handle it.
            return $user_id;
        }

        // Validate the token via OAuth server.
        if ( ! class_exists( 'GFY_OAuth_Server' ) ) {
            return $user_id;
        }

        $token_record = GFY_OAuth_Server::validate_access_token( $token );

        if ( ! $token_record ) {
            // Store the failure so check_auth_error can return a 401.
            self::$auth_error = new WP_Error(
                'invalid_token',
                __( 'Invalid or expired access token.', 'guestify' ),
                [ 'status' => 401 ]
            );
            return $user_id;
        }

        // Verify the user exists.
        $wp_user = get_user_by( 'ID', $token_record->user_id );
        if ( ! $wp_user ) {
            self::$auth_error = new WP_Error(
                'user_not_found',
                __( 'Token user no longer exists.', 'guestify' ),
                [ 'status' => 401 ]
            );
            return $user_id;
        }

        // Set the current user for this request.
        wp_set_current_user( $token_record->user_id );

        // Store token metadata for downstream use (scopes, site_url, etc.).
        self::$current_token = $token_record;

        return (int) $token_record->user_id;
    }

    /**
     * Return auth errors for Connect API requests that fail token validation.
     *
     * @param WP_Error|null|true $error Existing auth error.
     *
     * @return WP_Error|null|true
     */
    public static function check_auth_error( $error ) {
        // Don't override existing errors.
        if ( ! is_null( $error ) ) {
            return $error;
        }

        // Only apply to our namespace.
        if ( ! self::is_connect_request() ) {
            return $error;
        }

        // Skip public routes.
        if ( self::is_public_route() ) {
            return $error;
        }

        // Return stored auth error.
        if ( self::$auth_error ) {
            return self::$auth_error;
        }

        return $error;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Token context (available to downstream handlers)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @var object|null Current authenticated token record.
     */
    private static ?object $current_token = null;

    /**
     * @var WP_Error|null Auth error to return.
     */
    private static ?WP_Error $auth_error = null;

    /**
     * Get the current authenticated token record.
     *
     * Useful for checking scopes, site_url, etc. in endpoint callbacks.
     *
     * @return object|null Token row or null if not token-authenticated.
     */
    public static function get_current_token(): ?object {
        return self::$current_token;
    }

    /**
     * Check if the current request has a specific scope.
     *
     * @param string $scope Scope to check (e.g. 'ai', 'transcript', 'sync').
     *
     * @return bool
     */
    public static function has_scope( string $scope ): bool {
        if ( ! self::$current_token ) {
            return false;
        }

        $scopes = array_map( 'trim', explode( ',', self::$current_token->scopes ?? '' ) );
        return in_array( $scope, $scopes, true );
    }

    /**
     * Get the site URL from the current token.
     *
     * @return string Site URL or empty string.
     */
    public static function get_site_url(): string {
        return self::$current_token->site_url ?? '';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Extract Bearer token from the Authorization header.
     *
     * @return string|null Token string or null.
     */
    private static function get_bearer_token(): ?string {
        $headers = self::get_authorization_header();

        if ( empty( $headers ) ) {
            return null;
        }

        if ( preg_match( '/^Bearer\s+(.+)$/i', $headers, $matches ) ) {
            return trim( $matches[1] );
        }

        return null;
    }

    /**
     * Get the Authorization header value.
     *
     * Tries multiple sources since servers handle this differently.
     *
     * @return string Header value or empty string.
     */
    private static function get_authorization_header(): string {
        // Apache + mod_rewrite.
        if ( isset( $_SERVER['HTTP_AUTHORIZATION'] ) ) {
            return trim( $_SERVER['HTTP_AUTHORIZATION'] );
        }

        // Nginx proxy.
        if ( isset( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) ) {
            return trim( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] );
        }

        // Apache with CGI/FastCGI.
        if ( function_exists( 'apache_request_headers' ) ) {
            $headers = apache_request_headers();
            if ( is_array( $headers ) ) {
                // Header keys can be case-insensitive.
                foreach ( $headers as $key => $value ) {
                    if ( strtolower( $key ) === 'authorization' ) {
                        return trim( $value );
                    }
                }
            }
        }

        return '';
    }

    /**
     * Check if the current request is a REST API request.
     *
     * @return bool
     */
    private static function is_rest_request(): bool {
        if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
            return true;
        }

        // Fallback: check the request URI.
        $rest_prefix = trailingslashit( rest_get_url_prefix() );
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';

        return ( false !== strpos( $request_uri, $rest_prefix ) );
    }

    /**
     * Check if the current request targets the gfy-connect namespace.
     *
     * @return bool
     */
    private static function is_connect_request(): bool {
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $rest_prefix = rest_get_url_prefix();

        return ( false !== strpos( $request_uri, $rest_prefix . '/' . self::PROTECTED_NAMESPACE ) );
    }

    /**
     * Check if the current request targets a public (no-auth) route.
     *
     * @return bool
     */
    private static function is_public_route(): bool {
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $rest_prefix = rest_get_url_prefix();

        foreach ( self::PUBLIC_ROUTES as $route ) {
            $full_path = $rest_prefix . $route;

            // Exact match or match with trailing content (query params).
            if ( false !== strpos( $request_uri, $full_path ) ) {
                // Make sure it's the exact endpoint, not a prefix match.
                $after = substr( $request_uri, strpos( $request_uri, $full_path ) + strlen( $full_path ) );
                if ( $after === '' || $after[0] === '?' || $after[0] === '/' ) {
                    return true;
                }
            }
        }

        return false;
    }
}
