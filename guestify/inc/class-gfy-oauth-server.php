<?php
/**
 * Guestify OAuth Server
 *
 * Handles OAuth authorization code flow where Guestify is the PROVIDER.
 * The Guestify Starter plugin is the CLIENT.
 *
 * Flow:
 * 1. Plugin opens popup: guestify.com/connect/authorize?site_url=X&return_url=Y&state=Z
 * 2. User logs in / registers on Guestify
 * 3. User clicks "Authorize" -> auth code generated, stored in transient (10 min TTL)
 * 4. Redirect to return_url?code=ABC&state=Z
 * 5. Plugin exchanges code for tokens via REST: POST /gfy-connect/v1/token
 * 6. Guestify returns { access_token, refresh_token, expires_in, user_email, tier }
 *
 * Follows the same transient-based auth code pattern used by PIT_Google_Calendar
 * and PIT_Outlook_Calendar OAuth implementations.
 *
 * @package Guestify
 * @since   2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GFY_OAuth_Server {

    /**
     * Database table version
     */
    const DB_VERSION        = '1.0.0';
    const DB_VERSION_OPTION = 'gfy_oauth_db_version';

    /**
     * Token lifetimes
     */
    const ACCESS_TOKEN_TTL  = DAY_IN_SECONDS;          // 24 hours
    const REFRESH_TOKEN_TTL = 90 * DAY_IN_SECONDS;     // 90 days
    const AUTH_CODE_TTL     = 600;                      // 10 minutes (matches Google/Outlook pattern)

    /**
     * Encryption method
     */
    const CIPHER = 'aes-256-cbc';

    // ─────────────────────────────────────────────────────────────────────────
    // Database table management
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Create the OAuth tokens table.
     *
     * @return bool Whether the table was created successfully.
     */
    public static function create_tables(): bool {
        global $wpdb;

        $table           = $wpdb->prefix . 'gfy_oauth_tokens';
        $charset_collate = $wpdb->get_charset_collate();

        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) === $table ) {
            return true; // Already exists.
        }

        $sql = "CREATE TABLE {$table} (
            id              BIGINT UNSIGNED AUTO_INCREMENT,
            user_id         BIGINT UNSIGNED NOT NULL,
            access_token    VARCHAR(255) NOT NULL,
            refresh_token   VARCHAR(255) NOT NULL,
            site_url        VARCHAR(500) NOT NULL,
            site_name       VARCHAR(255) DEFAULT '',
            plugin_version  VARCHAR(20)  DEFAULT '',
            scopes          VARCHAR(500) DEFAULT 'entitlements,ai,transcript,sync',
            access_expires  DATETIME NOT NULL,
            refresh_expires DATETIME NOT NULL,
            created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
            last_used_at    DATETIME NULL,
            revoked         TINYINT(1) DEFAULT 0,
            PRIMARY KEY (id),
            UNIQUE KEY idx_access (access_token),
            UNIQUE KEY idx_refresh (refresh_token),
            INDEX idx_user (user_id),
            INDEX idx_site (site_url(191)),
            INDEX idx_revoked (revoked)
        ) {$charset_collate};";

        $wpdb->query( $sql );

        $success = $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) === $table;

        if ( $success ) {
            update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
        }

        return $success;
    }

    /**
     * Check if the OAuth table exists.
     *
     * @return bool
     */
    public static function table_exists(): bool {
        global $wpdb;
        $table = $wpdb->prefix . 'gfy_oauth_tokens';
        return $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) === $table;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Authorization code (transient-based, same pattern as Google Calendar)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Generate an authorization code for a user.
     *
     * The code is stored in a WordPress transient with a 10-minute TTL.
     *
     * @param int    $user_id        The authorizing user ID.
     * @param string $site_url       The plugin site URL.
     * @param string $site_name      Human-readable site name.
     * @param string $plugin_version Plugin version string.
     * @param string $state          CSRF state token from the plugin.
     *
     * @return string The authorization code.
     */
    public static function generate_auth_code(
        int $user_id,
        string $site_url,
        string $site_name = '',
        string $plugin_version = '',
        string $state = ''
    ): string {
        $code = wp_generate_password( 64, false );

        $data = [
            'user_id'        => $user_id,
            'site_url'       => esc_url_raw( $site_url ),
            'site_name'      => sanitize_text_field( $site_name ),
            'plugin_version' => sanitize_text_field( $plugin_version ),
            'state'          => sanitize_text_field( $state ),
            'created_at'     => time(),
        ];

        // Store with unique transient key (matches Google Calendar pattern).
        set_transient( 'gfy_oauth_code_' . $code, $data, self::AUTH_CODE_TTL );

        return $code;
    }

    /**
     * Validate and consume an authorization code.
     *
     * The transient is deleted on use (single-use code).
     *
     * @param string $code     The authorization code.
     * @param string $site_url The requesting site URL (must match).
     *
     * @return array|null Code data on success, null on failure.
     */
    public static function consume_auth_code( string $code, string $site_url ): ?array {
        $data = get_transient( 'gfy_oauth_code_' . $code );

        if ( false === $data || ! is_array( $data ) ) {
            return null; // Expired or invalid.
        }

        // Validate site_url matches.
        $stored_url = untrailingslashit( strtolower( $data['site_url'] ?? '' ) );
        $request_url = untrailingslashit( strtolower( esc_url_raw( $site_url ) ) );

        if ( $stored_url !== $request_url ) {
            return null; // Site URL mismatch.
        }

        // Delete transient (single-use).
        delete_transient( 'gfy_oauth_code_' . $code );

        return $data;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Token generation
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Generate and store access + refresh tokens for a user.
     *
     * @param int    $user_id        User ID.
     * @param string $site_url       Plugin site URL.
     * @param string $site_name      Site name.
     * @param string $plugin_version Plugin version.
     *
     * @return array|null Token data or null on failure.
     */
    public static function generate_tokens(
        int $user_id,
        string $site_url,
        string $site_name = '',
        string $plugin_version = ''
    ): ?array {
        global $wpdb;
        $table = $wpdb->prefix . 'gfy_oauth_tokens';

        // Revoke any existing tokens for this user + site combination.
        self::revoke_for_site( $user_id, $site_url );

        // Generate unique tokens.
        $access_token  = self::generate_token();
        $refresh_token = self::generate_token();

        $now            = current_time( 'mysql' );
        $access_expires = date( 'Y-m-d H:i:s', time() + self::ACCESS_TOKEN_TTL );
        $refresh_expires = date( 'Y-m-d H:i:s', time() + self::REFRESH_TOKEN_TTL );

        $inserted = $wpdb->insert( $table, [
            'user_id'         => $user_id,
            'access_token'    => hash( 'sha256', $access_token ),  // Store hashed.
            'refresh_token'   => hash( 'sha256', $refresh_token ), // Store hashed.
            'site_url'        => esc_url_raw( $site_url ),
            'site_name'       => sanitize_text_field( $site_name ),
            'plugin_version'  => sanitize_text_field( $plugin_version ),
            'scopes'          => 'entitlements,ai,transcript,sync',
            'access_expires'  => $access_expires,
            'refresh_expires' => $refresh_expires,
            'created_at'      => $now,
            'last_used_at'    => $now,
            'revoked'         => 0,
        ] );

        if ( ! $inserted ) {
            return null;
        }

        // Ensure user has a credit allocation.
        if ( class_exists( 'GFY_Credit_Repository' ) ) {
            GFY_Credit_Repository::get_or_create_allocation( $user_id );
        }

        // Get user tier.
        $tier = class_exists( 'GFY_Tier_Resolver' )
            ? GFY_Tier_Resolver::get_user_tier( $user_id )
            : [ 'key' => 'free', 'name' => 'Free' ];

        $user = get_user_by( 'ID', $user_id );

        return [
            'access_token'  => $access_token,       // Return raw token to client.
            'refresh_token' => $refresh_token,       // Return raw token to client.
            'expires_in'    => self::ACCESS_TOKEN_TTL,
            'token_type'    => 'Bearer',
            'user_email'    => $user ? $user->user_email : '',
            'tier'          => $tier['key'] ?? 'free',
            'tier_name'     => $tier['name'] ?? 'Free',
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Token validation
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Validate an access token and return the token record.
     *
     * @param string $access_token Raw access token from the client.
     *
     * @return object|null Token row on success, null on failure.
     */
    public static function validate_access_token( string $access_token ): ?object {
        global $wpdb;
        $table = $wpdb->prefix . 'gfy_oauth_tokens';

        $hashed = hash( 'sha256', $access_token );

        $token = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$table}
             WHERE access_token = %s
               AND revoked = 0
               AND access_expires > NOW()",
            $hashed
        ) );

        if ( ! $token ) {
            return null;
        }

        // Update last_used_at (fire-and-forget, non-blocking).
        $wpdb->update( $table, [
            'last_used_at' => current_time( 'mysql' ),
        ], [ 'id' => $token->id ] );

        return $token;
    }

    /**
     * Refresh an access token using a refresh token.
     *
     * Generates a new access token; the refresh token remains the same.
     *
     * @param string $refresh_token Raw refresh token from the client.
     *
     * @return array|null New token data or null on failure.
     */
    public static function refresh_access_token( string $refresh_token ): ?array {
        global $wpdb;
        $table = $wpdb->prefix . 'gfy_oauth_tokens';

        $hashed = hash( 'sha256', $refresh_token );

        $token = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$table}
             WHERE refresh_token = %s
               AND revoked = 0
               AND refresh_expires > NOW()",
            $hashed
        ) );

        if ( ! $token ) {
            return null;
        }

        // Generate new access token.
        $new_access_token = self::generate_token();
        $new_expires      = date( 'Y-m-d H:i:s', time() + self::ACCESS_TOKEN_TTL );

        $wpdb->update( $table, [
            'access_token'  => hash( 'sha256', $new_access_token ),
            'access_expires' => $new_expires,
            'last_used_at'  => current_time( 'mysql' ),
        ], [ 'id' => $token->id ] );

        return [
            'access_token' => $new_access_token,
            'expires_in'   => self::ACCESS_TOKEN_TTL,
            'token_type'   => 'Bearer',
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Token revocation
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Revoke a specific access token.
     *
     * @param string $access_token Raw access token.
     *
     * @return bool Whether revocation was successful.
     */
    public static function revoke_token( string $access_token ): bool {
        global $wpdb;
        $table = $wpdb->prefix . 'gfy_oauth_tokens';

        return (bool) $wpdb->update(
            $table,
            [ 'revoked' => 1 ],
            [ 'access_token' => hash( 'sha256', $access_token ) ]
        );
    }

    /**
     * Revoke all tokens for a user + site combination.
     *
     * @param int    $user_id  User ID.
     * @param string $site_url Site URL.
     *
     * @return int Number of tokens revoked.
     */
    public static function revoke_for_site( int $user_id, string $site_url ): int {
        global $wpdb;
        $table = $wpdb->prefix . 'gfy_oauth_tokens';

        return (int) $wpdb->query( $wpdb->prepare(
            "UPDATE {$table} SET revoked = 1
             WHERE user_id = %d AND site_url = %s AND revoked = 0",
            $user_id,
            esc_url_raw( $site_url )
        ) );
    }

    /**
     * Revoke all tokens for a user (disconnect all sites).
     *
     * @param int $user_id User ID.
     *
     * @return int Number of tokens revoked.
     */
    public static function revoke_all_for_user( int $user_id ): int {
        global $wpdb;
        $table = $wpdb->prefix . 'gfy_oauth_tokens';

        return (int) $wpdb->query( $wpdb->prepare(
            "UPDATE {$table} SET revoked = 1 WHERE user_id = %d AND revoked = 0",
            $user_id
        ) );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Queries
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Get all active connections for a user.
     *
     * @param int $user_id User ID.
     *
     * @return array Array of connection records.
     */
    public static function get_user_connections( int $user_id ): array {
        global $wpdb;
        $table = $wpdb->prefix . 'gfy_oauth_tokens';

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT id, site_url, site_name, plugin_version, scopes,
                    access_expires, refresh_expires, created_at, last_used_at
             FROM {$table}
             WHERE user_id = %d AND revoked = 0
             ORDER BY last_used_at DESC",
            $user_id
        ), ARRAY_A );
    }

    /**
     * Get the user ID associated with an access token.
     *
     * @param string $access_token Raw access token.
     *
     * @return int|null User ID or null.
     */
    public static function get_user_id_from_token( string $access_token ): ?int {
        $token = self::validate_access_token( $access_token );
        return $token ? (int) $token->user_id : null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Maintenance
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Clean up expired and revoked tokens.
     *
     * Intended to run via daily cron.
     *
     * @return int Number of rows deleted.
     */
    public static function cleanup_expired(): int {
        global $wpdb;
        $table = $wpdb->prefix . 'gfy_oauth_tokens';

        // Delete tokens where both access AND refresh are expired, or revoked > 30 days ago.
        return (int) $wpdb->query(
            "DELETE FROM {$table}
             WHERE (refresh_expires < NOW() AND access_expires < NOW())
                OR (revoked = 1 AND last_used_at < DATE_SUB(NOW(), INTERVAL 30 DAY))"
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Generate a cryptographically secure random token.
     *
     * @return string 64-character hex token.
     */
    private static function generate_token(): string {
        return bin2hex( random_bytes( 32 ) );
    }
}
