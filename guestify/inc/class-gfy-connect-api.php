<?php
/**
 * Guestify Connect REST API
 *
 * REST endpoints consumed by the Guestify Starter WordPress plugin.
 * All routes under namespace `gfy-connect/v1`.
 *
 * Public (no Bearer auth):
 *   POST /gfy-connect/v1/token           — Exchange auth code for tokens
 *   POST /gfy-connect/v1/token/refresh   — Refresh expired access token
 *
 * Protected (Bearer token via GFY_Token_Auth):
 *   GET  /gfy-connect/v1/entitlements    — Tier + credits + feature flags
 *   POST /gfy-connect/v1/ai/generate     — Proxy AI content generation
 *   POST /gfy-connect/v1/transcript      — Proxy audio transcription
 *   GET  /gfy-connect/v1/transcript/cache/(?P<hash>[a-f0-9]+)
 *   POST /gfy-connect/v1/sync            — Receive appearance data
 *   GET  /gfy-connect/v1/score           — Authority score + badge HTML
 *   POST /gfy-connect/v1/disconnect      — Revoke current token
 *
 * @package Guestify
 * @since   2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GFY_Connect_API {

    const NAMESPACE = 'gfy-connect/v1';

    /**
     * Register all Connect API routes.
     */
    public static function register_routes(): void {

        // ── Token exchange (public) ─────────────────────────────────────
        register_rest_route( self::NAMESPACE, '/token', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [ __CLASS__, 'exchange_token' ],
            'permission_callback' => '__return_true',
            'args'                => [
                'code'     => [ 'required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
                'site_url' => [ 'required' => true, 'type' => 'string', 'sanitize_callback' => 'esc_url_raw' ],
            ],
        ] );

        // ── Token refresh (public) ──────────────────────────────────────
        register_rest_route( self::NAMESPACE, '/token/refresh', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [ __CLASS__, 'refresh_token' ],
            'permission_callback' => '__return_true',
            'args'                => [
                'refresh_token' => [ 'required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
            ],
        ] );

        // ── Entitlements (protected) ────────────────────────────────────
        register_rest_route( self::NAMESPACE, '/entitlements', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ __CLASS__, 'get_entitlements' ],
            'permission_callback' => [ __CLASS__, 'check_token_auth' ],
        ] );

        // ── AI generation proxy (protected) ─────────────────────────────
        register_rest_route( self::NAMESPACE, '/ai/generate', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [ __CLASS__, 'ai_generate' ],
            'permission_callback' => [ __CLASS__, 'check_token_auth' ],
            'args'                => [
                'prompt'       => [ 'required' => true, 'type' => 'string' ],
                'content_type' => [ 'required' => true, 'type' => 'string' ],
                'context'      => [ 'required' => false, 'type' => 'object' ],
                'options'      => [ 'required' => false, 'type' => 'object' ],
            ],
        ] );

        // ── Transcript proxy (protected) ────────────────────────────────
        register_rest_route( self::NAMESPACE, '/transcript', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [ __CLASS__, 'transcribe' ],
            'permission_callback' => [ __CLASS__, 'check_token_auth' ],
            'args'                => [
                'audio_url' => [ 'required' => true, 'type' => 'string', 'sanitize_callback' => 'esc_url_raw' ],
            ],
        ] );

        // ── Transcript cache lookup (protected) ─────────────────────────
        register_rest_route( self::NAMESPACE, '/transcript/cache/(?P<hash>[a-f0-9]+)', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ __CLASS__, 'transcript_cache_lookup' ],
            'permission_callback' => [ __CLASS__, 'check_token_auth' ],
            'args'                => [
                'hash' => [ 'required' => true, 'type' => 'string', 'validate_callback' => function ( $v ) {
                    return (bool) preg_match( '/^[a-f0-9]{32,64}$/', $v );
                } ],
            ],
        ] );

        // ── Sync appearance data (protected) ────────────────────────────
        register_rest_route( self::NAMESPACE, '/sync', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [ __CLASS__, 'sync_appearance' ],
            'permission_callback' => [ __CLASS__, 'check_token_auth' ],
        ] );

        // ── Authority score (protected) ─────────────────────────────────
        register_rest_route( self::NAMESPACE, '/score', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ __CLASS__, 'get_score' ],
            'permission_callback' => [ __CLASS__, 'check_token_auth' ],
        ] );

        // ── Disconnect / revoke token (protected) ───────────────────────
        register_rest_route( self::NAMESPACE, '/disconnect', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [ __CLASS__, 'disconnect' ],
            'permission_callback' => [ __CLASS__, 'check_token_auth' ],
        ] );
    }

    /**
     * Permission callback: require a valid Bearer token.
     *
     * GFY_Token_Auth::authenticate() has already run by this point.
     * We just verify a user was set.
     */
    public static function check_token_auth(): bool {
        return is_user_logged_in();
    }

    // =====================================================================
    //  TOKEN ENDPOINTS (public)
    // =====================================================================

    /**
     * POST /token — Exchange an authorization code for access + refresh tokens.
     */
    public static function exchange_token( WP_REST_Request $request ) {
        $code     = $request->get_param( 'code' );
        $site_url = $request->get_param( 'site_url' );

        // Consume the auth code (single-use, from transient).
        $code_data = GFY_OAuth_Server::consume_auth_code( $code, $site_url );

        if ( ! $code_data ) {
            return new WP_Error(
                'invalid_code',
                __( 'Invalid or expired authorization code.', 'guestify' ),
                [ 'status' => 400 ]
            );
        }

        // Generate tokens.
        $tokens = GFY_OAuth_Server::generate_tokens(
            (int) $code_data['user_id'],
            $site_url,
            $code_data['site_name'] ?? '',
            $code_data['plugin_version'] ?? ''
        );

        if ( ! $tokens ) {
            return new WP_Error(
                'token_generation_failed',
                __( 'Failed to generate access tokens.', 'guestify' ),
                [ 'status' => 500 ]
            );
        }

        return rest_ensure_response( $tokens );
    }

    /**
     * POST /token/refresh — Refresh an expired access token.
     */
    public static function refresh_token( WP_REST_Request $request ) {
        $refresh_token = $request->get_param( 'refresh_token' );

        $result = GFY_OAuth_Server::refresh_access_token( $refresh_token );

        if ( ! $result ) {
            return new WP_Error(
                'invalid_refresh_token',
                __( 'Invalid or expired refresh token. Please reconnect.', 'guestify' ),
                [ 'status' => 401 ]
            );
        }

        return rest_ensure_response( $result );
    }

    // =====================================================================
    //  ENTITLEMENTS
    // =====================================================================

    /**
     * GET /entitlements — Return tier, credits, feature flags for the plugin.
     */
    public static function get_entitlements( WP_REST_Request $request ) {
        $user_id = get_current_user_id();

        // Tier info.
        $tier = GFY_Tier_Resolver::get_user_tier( $user_id );

        // Credit balance.
        $balance = GFY_Credit_Repository::get_balance( $user_id );

        // Action costs relevant to the plugin.
        $plugin_actions = [
            'plugin_ai_generation',
            'plugin_transcription',
            'plugin_ai_takeaways',
            'plugin_ai_social',
        ];

        $costs = [];
        foreach ( $plugin_actions as $action ) {
            $costs[ $action ] = GFY_Credit_Repository::get_action_cost( $action );
        }

        // Feature flags based on tier.
        $tier_key = $tier['key'] ?? 'free';
        $features = self::get_feature_flags( $tier_key );

        // Upgrade URL.
        $user       = get_user_by( 'ID', $user_id );
        $user_email = $user ? $user->user_email : '';
        $upgrade_url = add_query_arg( [
            'email'  => rawurlencode( $user_email ),
            'source' => 'plugin',
            'tier'   => rawurlencode( $tier_key ),
        ], 'https://guestify.com/upgrade/plugin-starter' );

        return rest_ensure_response( [
            'tier'    => [
                'key'  => $tier_key,
                'name' => $tier['name'] ?? 'Free',
            ],
            'credits' => [
                'balance'          => (int) $balance['total'],
                'allowance'        => (int) $balance['monthly_allowance'],
                'used'             => max( 0, (int) $balance['monthly_allowance'] - (int) $balance['allowance'] ),
                'rollover'         => (int) $balance['rollover'],
                'costs'            => $costs,
                'billing_cycle_end' => $balance['billing_cycle_end'] ?? null,
            ],
            'features'    => $features,
            'upgrade_url' => $upgrade_url,
        ] );
    }

    /**
     * Derive feature flags from tier key.
     *
     * @param string $tier_key Tier slug.
     * @return array Feature flags.
     */
    private static function get_feature_flags( string $tier_key ): array {
        // All tiers get basic features.
        $features = [
            'ai_generation'  => true,
            'transcription'  => true,
            'sync'           => true,
            'score'          => true,
            'priority_queue' => false,
        ];

        // Higher tiers get priority queue.
        $priority_tiers = [ 'plugin_starter', 'accelerator', 'velocity', 'zenith', 'unlimited' ];
        if ( in_array( $tier_key, $priority_tiers, true ) ) {
            $features['priority_queue'] = true;
        }

        return $features;
    }

    // =====================================================================
    //  AI GENERATION PROXY
    // =====================================================================

    /**
     * POST /ai/generate — Proxy AI content generation through Guestify's AI providers.
     */
    public static function ai_generate( WP_REST_Request $request ) {
        $user_id      = get_current_user_id();
        $prompt        = $request->get_param( 'prompt' );
        $content_type  = sanitize_text_field( $request->get_param( 'content_type' ) );
        $context       = $request->get_param( 'context' ) ?? [];
        $options       = $request->get_param( 'options' ) ?? [];

        // Map plugin content types to credit action types.
        $action_map = [
            'blog_post' => 'plugin_ai_generation',
            'takeaways' => 'plugin_ai_takeaways',
            'social'    => 'plugin_ai_social',
        ];

        $action_type = $action_map[ $content_type ] ?? 'plugin_ai_generation';

        // Credit gate: check before calling AI.
        $gate_check = GFY_Credit_Gate::check( $action_type, 1, $user_id );
        if ( is_wp_error( $gate_check ) ) {
            return new WP_REST_Response( [
                'success' => false,
                'code'    => $gate_check->get_error_code(),
                'message' => $gate_check->get_error_message(),
                'data'    => $gate_check->get_error_data(),
            ], 402 );
        }

        // Call AI provider via the existing factory.
        if ( ! class_exists( 'PIT_AI_Provider_Factory' ) ) {
            return new WP_Error(
                'ai_unavailable',
                __( 'AI service is currently unavailable.', 'guestify' ),
                [ 'status' => 503 ]
            );
        }

        // Build system prompt for plugin content generation.
        $system_prompt = self::build_plugin_system_prompt( $content_type, $context );

        $factory = new PIT_AI_Provider_Factory();
        $result  = $factory->execute( 'plugin_' . $content_type, $prompt, [
            'system_prompt' => $system_prompt,
            'temperature'   => $options['temperature'] ?? 0.4,
            'max_tokens'    => $options['max_tokens'] ?? 4000,
            'json_mode'     => true,
            'timeout'       => 120,
        ] );

        if ( is_wp_error( $result ) ) {
            return new WP_Error(
                'ai_generation_failed',
                $result->get_error_message(),
                [ 'status' => 500 ]
            );
        }

        // Deduct credits on success.
        $deducted = GFY_Credit_Gate::deduct( $action_type, 1, [
            'content_type' => $content_type,
            'provider'     => $result['provider'] ?? '',
            'model'        => $result['model'] ?? '',
            'tokens'       => ( $result['input_tokens'] ?? 0 ) + ( $result['output_tokens'] ?? 0 ),
            'source'       => 'plugin',
            'site_url'     => GFY_Token_Auth::get_site_url(),
        ], $user_id );

        // Parse AI response (should be JSON).
        $content = $result['content'] ?? '';
        $parsed  = json_decode( $content, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            // If not valid JSON, return raw text as body.
            $parsed = [
                'title' => '',
                'body'  => $content,
            ];
        }

        // Get updated credit balance for client cache.
        $balance = GFY_Credit_Repository::get_balance( $user_id );
        $credits_used = GFY_Credit_Repository::get_action_cost( $action_type );

        return rest_ensure_response( [
            'success'      => true,
            'content_type' => $content_type,
            'generated'    => $parsed,
            'credits_used' => $credits_used,
            'provider'     => $result['provider'] ?? '',
            'entitlements' => [
                'credits' => [
                    'balance' => (int) $balance['total'],
                ],
            ],
        ] );
    }

    /**
     * Build a system prompt for plugin AI generation tasks.
     *
     * @param string $content_type Content type (blog_post, takeaways, social).
     * @param array  $context      Context data (podcast_name, episode_title, etc.).
     * @return string System prompt.
     */
    private static function build_plugin_system_prompt( string $content_type, array $context ): string {
        $base = "You are an expert content writer specializing in podcast-based content creation. ";

        switch ( $content_type ) {
            case 'blog_post':
                $base .= "Generate a comprehensive, SEO-optimized blog post from a podcast interview transcript. "
                       . "Write in first person from the guest's perspective. "
                       . "Return valid JSON with keys: title, body (HTML), seo_title, seo_description, excerpt.";
                break;

            case 'takeaways':
                $base .= "Extract the key takeaways and actionable insights from a podcast interview transcript. "
                       . "Return valid JSON with keys: title, takeaways (array of {headline, detail}), body (formatted HTML list).";
                break;

            case 'social':
                $base .= "Create social media content from a podcast interview transcript. "
                       . "Return valid JSON with keys: title, linkedin (post text), twitter_thread (array of tweets), body (combined text).";
                break;

            default:
                $base .= "Generate content from the provided podcast transcript. Return valid JSON.";
        }

        // Add context if available.
        if ( ! empty( $context['podcast_name'] ) ) {
            $base .= "\n\nPodcast: " . sanitize_text_field( $context['podcast_name'] );
        }
        if ( ! empty( $context['episode_title'] ) ) {
            $base .= "\nEpisode: " . sanitize_text_field( $context['episode_title'] );
        }
        if ( ! empty( $context['guest_name'] ) ) {
            $base .= "\nGuest: " . sanitize_text_field( $context['guest_name'] );
        }

        return $base;
    }

    // =====================================================================
    //  TRANSCRIPT PROXY
    // =====================================================================

    /**
     * POST /transcript — Transcribe audio via Guestify's transcription service.
     */
    public static function transcribe( WP_REST_Request $request ) {
        $user_id   = get_current_user_id();
        $audio_url = $request->get_param( 'audio_url' );

        // Check credit gate.
        $gate_check = GFY_Credit_Gate::check( 'plugin_transcription', 1, $user_id );
        if ( is_wp_error( $gate_check ) ) {
            return new WP_REST_Response( [
                'success' => false,
                'code'    => $gate_check->get_error_code(),
                'message' => $gate_check->get_error_message(),
                'data'    => $gate_check->get_error_data(),
            ], 402 );
        }

        // Check cache first (free lookup).
        $url_hash = md5( strtolower( trim( $audio_url ) ) );
        $cached   = self::get_cached_transcript( $url_hash );

        if ( $cached ) {
            // Cache hit — no credit cost.
            $balance = GFY_Credit_Repository::get_balance( $user_id );

            return rest_ensure_response( [
                'success'      => true,
                'text'         => $cached['text'],
                'word_count'   => (int) $cached['word_count'],
                'language'     => $cached['language'] ?? 'en',
                'source'       => 'cache',
                'credits_used' => 0,
                'entitlements' => [
                    'credits' => [
                        'balance' => (int) $balance['total'],
                    ],
                ],
            ] );
        }

        // Call transcription service (Deepgram / Whisper).
        $transcript = self::call_transcription_service( $audio_url );

        if ( is_wp_error( $transcript ) ) {
            return new WP_Error(
                'transcription_failed',
                $transcript->get_error_message(),
                [ 'status' => 500 ]
            );
        }

        // Cache the result for other users.
        self::cache_transcript( $url_hash, $audio_url, $transcript );

        // Deduct credits.
        GFY_Credit_Gate::deduct( 'plugin_transcription', 1, [
            'audio_url' => $audio_url,
            'source'    => 'plugin',
            'site_url'  => GFY_Token_Auth::get_site_url(),
        ], $user_id );

        $balance = GFY_Credit_Repository::get_balance( $user_id );
        $credits_used = GFY_Credit_Repository::get_action_cost( 'plugin_transcription' );

        return rest_ensure_response( [
            'success'      => true,
            'text'         => $transcript['text'],
            'word_count'   => (int) $transcript['word_count'],
            'language'     => $transcript['language'] ?? 'en',
            'source'       => $transcript['source'] ?? 'whisper',
            'credits_used' => $credits_used,
            'entitlements' => [
                'credits' => [
                    'balance' => (int) $balance['total'],
                ],
            ],
        ] );
    }

    /**
     * GET /transcript/cache/{hash} — Check transcript cache (free, no credits).
     */
    public static function transcript_cache_lookup( WP_REST_Request $request ) {
        $hash   = $request->get_param( 'hash' );
        $cached = self::get_cached_transcript( $hash );

        if ( ! $cached ) {
            return new WP_Error(
                'not_found',
                __( 'Transcript not found in cache.', 'guestify' ),
                [ 'status' => 404 ]
            );
        }

        return rest_ensure_response( [
            'success'    => true,
            'text'       => $cached['text'],
            'word_count' => (int) $cached['word_count'],
            'language'   => $cached['language'] ?? 'en',
            'source'     => 'cache',
        ] );
    }

    /**
     * Look up a cached transcript by URL hash.
     *
     * @param string $hash MD5 hash of the audio URL.
     * @return array|null Transcript data or null.
     */
    private static function get_cached_transcript( string $hash ): ?array {
        global $wpdb;
        $table = $wpdb->prefix . 'gfy_transcript_cache';

        // If table doesn't exist yet, return null.
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) !== $table ) {
            return null;
        }

        $row = $wpdb->get_row( $wpdb->prepare(
            "SELECT text, word_count, language FROM {$table} WHERE url_hash = %s",
            $hash
        ), ARRAY_A );

        return $row ?: null;
    }

    /**
     * Cache a transcript result.
     *
     * @param string $hash      MD5 hash of the audio URL.
     * @param string $audio_url Original audio URL.
     * @param array  $data      Transcript data (text, word_count, language).
     */
    private static function cache_transcript( string $hash, string $audio_url, array $data ): void {
        global $wpdb;
        $table = $wpdb->prefix . 'gfy_transcript_cache';

        // Create table if it doesn't exist.
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) !== $table ) {
            self::create_transcript_cache_table();
        }

        $wpdb->replace( $table, [
            'url_hash'   => $hash,
            'audio_url'  => $audio_url,
            'text'       => $data['text'] ?? '',
            'word_count' => (int) ( $data['word_count'] ?? str_word_count( $data['text'] ?? '' ) ),
            'language'   => $data['language'] ?? 'en',
            'created_at' => current_time( 'mysql' ),
        ] );
    }

    /**
     * Create the transcript cache table.
     */
    private static function create_transcript_cache_table(): void {
        global $wpdb;
        $table           = $wpdb->prefix . 'gfy_transcript_cache';
        $charset_collate = $wpdb->get_charset_collate();

        $wpdb->query( "CREATE TABLE IF NOT EXISTS {$table} (
            id         BIGINT UNSIGNED AUTO_INCREMENT,
            url_hash   VARCHAR(64) NOT NULL,
            audio_url  VARCHAR(2000) NOT NULL,
            text       LONGTEXT NOT NULL,
            word_count INT DEFAULT 0,
            language   VARCHAR(10) DEFAULT 'en',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY idx_hash (url_hash)
        ) {$charset_collate};" );
    }

    /**
     * Call the transcription service (Deepgram or Whisper).
     *
     * @param string $audio_url Public URL of the audio file.
     * @return array|WP_Error Transcript data or error.
     */
    private static function call_transcription_service( string $audio_url ) {
        // Try Deepgram first (faster, cheaper), fall back to OpenAI Whisper.

        // Deepgram.
        $deepgram_key = class_exists( 'PIT_Settings' )
            ? PIT_Settings::get( 'deepgram_api_key' )
            : get_option( 'gfy_deepgram_api_key', '' );

        if ( ! empty( $deepgram_key ) ) {
            $result = self::transcribe_deepgram( $audio_url, $deepgram_key );
            if ( ! is_wp_error( $result ) ) {
                return $result;
            }
        }

        // OpenAI Whisper fallback.
        $openai_key = class_exists( 'PIT_Settings' )
            ? PIT_Settings::get( 'openai_api_key' )
            : get_option( 'gfy_openai_api_key', '' );

        if ( ! empty( $openai_key ) ) {
            $result = self::transcribe_whisper( $audio_url, $openai_key );
            if ( ! is_wp_error( $result ) ) {
                return $result;
            }
        }

        return new WP_Error(
            'no_transcription_service',
            __( 'No transcription service is currently configured.', 'guestify' ),
            [ 'status' => 503 ]
        );
    }

    /**
     * Transcribe via Deepgram.
     *
     * @param string $audio_url Audio URL.
     * @param string $api_key   Deepgram API key.
     * @return array|WP_Error
     */
    private static function transcribe_deepgram( string $audio_url, string $api_key ) {
        $response = wp_remote_post( 'https://api.deepgram.com/v1/listen?model=nova-2&smart_format=true&language=en', [
            'timeout' => 300,
            'headers' => [
                'Authorization' => 'Token ' . $api_key,
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode( [ 'url' => $audio_url ] ),
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status = wp_remote_retrieve_response_code( $response );
        $body   = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( 200 !== $status ) {
            return new WP_Error(
                'deepgram_error',
                $body['err_msg'] ?? 'Deepgram transcription failed.',
                [ 'status' => $status ]
            );
        }

        $text = $body['results']['channels'][0]['alternatives'][0]['transcript'] ?? '';

        return [
            'text'       => $text,
            'word_count' => str_word_count( $text ),
            'language'   => $body['results']['channels'][0]['detected_language'] ?? 'en',
            'source'     => 'deepgram',
        ];
    }

    /**
     * Transcribe via OpenAI Whisper.
     *
     * Downloads the audio file temporarily, then sends to Whisper API.
     *
     * @param string $audio_url Audio URL.
     * @param string $api_key   OpenAI API key.
     * @return array|WP_Error
     */
    private static function transcribe_whisper( string $audio_url, string $api_key ) {
        // Download audio to temp file.
        $tmp = download_url( $audio_url, 300 );
        if ( is_wp_error( $tmp ) ) {
            return $tmp;
        }

        // Build multipart request.
        $boundary = wp_generate_password( 24, false );
        $body     = '';

        // File field.
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Disposition: form-data; name=\"file\"; filename=\"audio.mp3\"\r\n";
        $body .= "Content-Type: audio/mpeg\r\n\r\n";
        $body .= file_get_contents( $tmp ) . "\r\n";

        // Model field.
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Disposition: form-data; name=\"model\"\r\n\r\n";
        $body .= "whisper-1\r\n";

        // Response format.
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Disposition: form-data; name=\"response_format\"\r\n\r\n";
        $body .= "json\r\n";

        $body .= "--{$boundary}--\r\n";

        // Clean up temp file.
        @unlink( $tmp );

        $response = wp_remote_post( 'https://api.openai.com/v1/audio/transcriptions', [
            'timeout' => 600,
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'multipart/form-data; boundary=' . $boundary,
            ],
            'body'    => $body,
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status = wp_remote_retrieve_response_code( $response );
        $data   = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( 200 !== $status ) {
            return new WP_Error(
                'whisper_error',
                $data['error']['message'] ?? 'Whisper transcription failed.',
                [ 'status' => $status ]
            );
        }

        $text = $data['text'] ?? '';

        return [
            'text'       => $text,
            'word_count' => str_word_count( $text ),
            'language'   => $data['language'] ?? 'en',
            'source'     => 'whisper',
        ];
    }

    // =====================================================================
    //  SYNC — Appearance data
    // =====================================================================

    /**
     * POST /sync — Receive appearance data from the plugin and store in Show Authority.
     */
    public static function sync_appearance( WP_REST_Request $request ) {
        $user_id = get_current_user_id();
        $data    = $request->get_json_params();

        $profile    = $data['profile'] ?? [];
        $podcast    = $data['podcast'] ?? [];
        $appearance = $data['appearance'] ?? [];

        if ( empty( $appearance ) ) {
            return new WP_Error(
                'missing_data',
                __( 'Appearance data is required.', 'guestify' ),
                [ 'status' => 400 ]
            );
        }

        global $wpdb;

        // 1. Upsert guest (pit_guests).
        $guest_id = self::upsert_guest( $user_id, $profile );

        // 2. Upsert podcast (pit_podcasts).
        $podcast_id = null;
        if ( ! empty( $podcast ) ) {
            $podcast_id = self::upsert_podcast( $podcast );
        }

        // 3. Upsert engagement (pit_engagements).
        $engagement_id = self::upsert_engagement( $guest_id, $podcast_id, $appearance );

        // 4. Link guest to engagement (pit_speaking_credits).
        if ( $engagement_id && $guest_id ) {
            self::link_speaking_credit( $guest_id, $engagement_id );
        }

        // 5. Trigger authority score recalculation.
        do_action( 'gfy_appearance_synced', $guest_id, $engagement_id, $user_id );

        return rest_ensure_response( [
            'success'             => true,
            'guest_id'            => $guest_id,
            'podcast_id'          => $podcast_id,
            'engagement_id'       => $engagement_id,
            'synced_appearances'  => 1,
            'message'             => __( 'Appearance data synced successfully.', 'guestify' ),
        ] );
    }

    /**
     * Upsert a guest record.
     *
     * @param int   $user_id WordPress user ID.
     * @param array $profile Guest profile data.
     * @return int|null Guest ID.
     */
    private static function upsert_guest( int $user_id, array $profile ): ?int {
        global $wpdb;
        $table = $wpdb->prefix . 'pit_guests';

        // Check if table exists.
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) !== $table ) {
            return null;
        }

        $user  = get_user_by( 'ID', $user_id );
        $email = $user ? $user->user_email : ( $profile['email'] ?? '' );

        if ( empty( $email ) ) {
            return null;
        }

        // Check for existing guest by email.
        $existing = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$table} WHERE email = %s",
            $email
        ) );

        $guest_data = [
            'name'      => sanitize_text_field( $profile['name'] ?? ( $user ? $user->display_name : '' ) ),
            'email'     => sanitize_email( $email ),
            'bio'       => sanitize_textarea_field( $profile['bio'] ?? '' ),
            'website'   => esc_url_raw( $profile['website'] ?? '' ),
            'linkedin'  => esc_url_raw( $profile['linkedin'] ?? '' ),
            'user_id'   => $user_id,
            'source'    => 'plugin',
        ];

        if ( $existing ) {
            // Update existing guest.
            $wpdb->update( $table, $guest_data, [ 'id' => $existing ] );
            return (int) $existing;
        }

        // Insert new guest.
        $guest_data['created_at'] = current_time( 'mysql' );
        $wpdb->insert( $table, $guest_data );
        return (int) $wpdb->insert_id;
    }

    /**
     * Upsert a podcast record.
     *
     * @param array $podcast Podcast data.
     * @return int|null Podcast ID.
     */
    private static function upsert_podcast( array $podcast ): ?int {
        global $wpdb;
        $table = $wpdb->prefix . 'pit_podcasts';

        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) !== $table ) {
            return null;
        }

        $itunes_id = ! empty( $podcast['itunes_id'] ) ? absint( $podcast['itunes_id'] ) : null;
        $rss_url   = esc_url_raw( $podcast['rss_url'] ?? '' );

        // Try to find existing by iTunes ID or RSS URL.
        $existing = null;
        if ( $itunes_id ) {
            $existing = $wpdb->get_var( $wpdb->prepare(
                "SELECT id FROM {$table} WHERE itunes_id = %d",
                $itunes_id
            ) );
        }
        if ( ! $existing && ! empty( $rss_url ) ) {
            $existing = $wpdb->get_var( $wpdb->prepare(
                "SELECT id FROM {$table} WHERE rss_url = %s",
                $rss_url
            ) );
        }

        $podcast_data = [
            'name'       => sanitize_text_field( $podcast['name'] ?? '' ),
            'itunes_id'  => $itunes_id,
            'rss_url'    => $rss_url,
            'artwork_url' => esc_url_raw( $podcast['artwork_url'] ?? '' ),
            'genre'      => sanitize_text_field( $podcast['genre'] ?? '' ),
            'source'     => 'plugin',
        ];

        if ( $existing ) {
            $wpdb->update( $table, $podcast_data, [ 'id' => $existing ] );
            return (int) $existing;
        }

        $podcast_data['created_at'] = current_time( 'mysql' );
        $wpdb->insert( $table, $podcast_data );
        return (int) $wpdb->insert_id;
    }

    /**
     * Upsert an engagement (appearance) record.
     *
     * @param int|null $guest_id   Guest ID.
     * @param int|null $podcast_id Podcast ID.
     * @param array    $appearance Appearance data.
     * @return int|null Engagement ID.
     */
    private static function upsert_engagement( ?int $guest_id, ?int $podcast_id, array $appearance ): ?int {
        global $wpdb;
        $table = $wpdb->prefix . 'pit_engagements';

        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) !== $table ) {
            return null;
        }

        // Dedup hash: combination of guest + podcast + episode title + date.
        $dedup_key = md5( implode( '|', [
            $guest_id ?? '',
            $podcast_id ?? '',
            strtolower( $appearance['episode_title'] ?? '' ),
            $appearance['published_date'] ?? '',
        ] ) );

        // Check for existing.
        $existing = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$table} WHERE dedup_hash = %s",
            $dedup_key
        ) );

        $engagement_data = [
            'guest_id'       => $guest_id,
            'podcast_id'     => $podcast_id,
            'episode_title'  => sanitize_text_field( $appearance['episode_title'] ?? '' ),
            'episode_url'    => esc_url_raw( $appearance['episode_url'] ?? '' ),
            'audio_url'      => esc_url_raw( $appearance['audio_url'] ?? '' ),
            'published_date' => sanitize_text_field( $appearance['published_date'] ?? '' ),
            'dedup_hash'     => $dedup_key,
            'source'         => 'plugin',
            'status'         => 'confirmed',
        ];

        if ( $existing ) {
            $wpdb->update( $table, $engagement_data, [ 'id' => $existing ] );
            return (int) $existing;
        }

        $engagement_data['created_at'] = current_time( 'mysql' );
        $wpdb->insert( $table, $engagement_data );
        return (int) $wpdb->insert_id;
    }

    /**
     * Link a guest to an engagement via speaking_credits.
     *
     * @param int $guest_id      Guest ID.
     * @param int $engagement_id Engagement ID.
     */
    private static function link_speaking_credit( int $guest_id, int $engagement_id ): void {
        global $wpdb;
        $table = $wpdb->prefix . 'pit_speaking_credits';

        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) !== $table ) {
            return;
        }

        // Check if link already exists.
        $exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$table} WHERE guest_id = %d AND engagement_id = %d",
            $guest_id,
            $engagement_id
        ) );

        if ( $exists ) {
            return;
        }

        $wpdb->insert( $table, [
            'guest_id'      => $guest_id,
            'engagement_id' => $engagement_id,
            'created_at'    => current_time( 'mysql' ),
        ] );
    }

    // =====================================================================
    //  AUTHORITY SCORE
    // =====================================================================

    /**
     * GET /score — Return the authority score and badge HTML.
     */
    public static function get_score( WP_REST_Request $request ) {
        $user_id = get_current_user_id();

        // Compute authority score using Show Authority's calculator if available.
        $score = self::compute_authority_score( $user_id );

        if ( ! $score ) {
            return rest_ensure_response( [
                'success' => true,
                'total'   => 0,
                'trend'   => 'stable',
                'message' => __( 'Not enough data to compute authority score.', 'guestify' ),
            ] );
        }

        // Generate badge HTML.
        $badge_html = self::render_badge_html( $score );

        // Portfolio URL.
        $user         = get_user_by( 'ID', $user_id );
        $portfolio_url = '';
        if ( $user ) {
            $portfolio_url = 'https://guestify.com/guest/' . sanitize_title( $user->display_name );
        }

        return rest_ensure_response( [
            'success'       => true,
            'total'         => (int) ( $score['total'] ?? 0 ),
            'dimensions'    => $score['dimensions'] ?? [],
            'trend'         => $score['trend'] ?? 'stable',
            'badge_html'    => $badge_html,
            'portfolio_url' => $portfolio_url,
        ] );
    }

    /**
     * Compute the authority score for a user.
     *
     * Delegates to PIT_Authority_Score if available, otherwise calculates a basic score.
     *
     * @param int $user_id User ID.
     * @return array|null Score data or null.
     */
    private static function compute_authority_score( int $user_id ): ?array {
        // Try Show Authority's score calculator first.
        if ( class_exists( 'PIT_Authority_Score' ) && method_exists( 'PIT_Authority_Score', 'compute' ) ) {
            return PIT_Authority_Score::compute( $user_id );
        }

        // Fallback: basic score from engagement count.
        global $wpdb;
        $guests_table      = $wpdb->prefix . 'pit_guests';
        $engagements_table = $wpdb->prefix . 'pit_engagements';

        // Check tables exist.
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$guests_table}'" ) !== $guests_table ) {
            return null;
        }

        // Get guest ID for this user.
        $user  = get_user_by( 'ID', $user_id );
        if ( ! $user ) {
            return null;
        }

        $guest_id = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$guests_table} WHERE email = %s OR user_id = %d ORDER BY id ASC LIMIT 1",
            $user->user_email,
            $user_id
        ) );

        if ( ! $guest_id ) {
            return null;
        }

        // Count engagements.
        $engagement_count = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$engagements_table} WHERE guest_id = %d",
            $guest_id
        ) );

        if ( $engagement_count === 0 ) {
            return null;
        }

        // Count unique podcasts.
        $podcast_count = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(DISTINCT podcast_id) FROM {$engagements_table} WHERE guest_id = %d AND podcast_id IS NOT NULL",
            $guest_id
        ) );

        // Simple authority score formula.
        $appearances_score = min( 40, $engagement_count * 4 );  // Max 40 points (10 appearances).
        $reach_score       = min( 30, $podcast_count * 6 );     // Max 30 points (5 unique shows).
        $consistency_score = min( 30, $engagement_count * 3 );  // Max 30 points.

        $total = $appearances_score + $reach_score + $consistency_score;

        return [
            'total'      => min( 100, $total ),
            'trend'      => 'stable',
            'dimensions' => [
                'appearances' => $appearances_score,
                'reach'       => $reach_score,
                'consistency' => $consistency_score,
            ],
        ];
    }

    /**
     * Render badge HTML for embedding on plugin sites.
     *
     * @param array $score Score data.
     * @return string Badge HTML.
     */
    private static function render_badge_html( array $score ): string {
        $total = (int) ( $score['total'] ?? 0 );
        $trend = $score['trend'] ?? 'stable';

        $trend_icon = [
            'up'     => '&#9650;',
            'down'   => '&#9660;',
            'stable' => '&#9654;',
        ];
        $trend_html = $trend_icon[ $trend ] ?? '';

        $dimensions_html = '';
        if ( ! empty( $score['dimensions'] ) ) {
            $dimensions_html = '<div class="gs-score-dimensions">';
            foreach ( (array) $score['dimensions'] as $dim => $val ) {
                $label = esc_html( ucfirst( $dim ) );
                $value = esc_html( $val );
                $dimensions_html .= "<div class=\"gs-dim\"><span class=\"gs-dim-label\">{$label}</span><span class=\"gs-dim-value\">{$value}</span></div>";
            }
            $dimensions_html .= '</div>';
        }

        return '<div class="gs-score-badge">'
            . '<div class="gs-score-number">'
            . '<span class="gs-score-value">' . esc_html( $total ) . '</span>'
            . '<span class="gs-score-trend gs-trend-' . esc_attr( $trend ) . '">' . $trend_html . '</span>'
            . '</div>'
            . '<div class="gs-score-label">Guest Authority Score</div>'
            . $dimensions_html
            . '</div>';
    }

    // =====================================================================
    //  DISCONNECT
    // =====================================================================

    /**
     * POST /disconnect — Revoke the current token (plugin-initiated disconnect).
     */
    public static function disconnect( WP_REST_Request $request ) {
        $token = GFY_Token_Auth::get_current_token();

        if ( $token ) {
            global $wpdb;
            $table = $wpdb->prefix . 'gfy_oauth_tokens';
            $wpdb->update( $table, [ 'revoked' => 1 ], [ 'id' => $token->id ] );
        }

        return rest_ensure_response( [
            'success' => true,
            'message' => __( 'Connection revoked successfully.', 'guestify' ),
        ] );
    }
}
