<?php
/**
 * Guestify Connect Setup
 *
 * Handles one-time setup tasks for the Connect module:
 * - Creates the OAuth tokens table
 * - Adds plugin-specific tiers to the tier configuration
 * - Registers plugin-specific credit action types
 * - Creates the transcript cache table
 * - Schedules cleanup cron
 *
 * Runs once via a version check in wp_options.
 *
 * @package Guestify
 * @since   2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GFY_Connect_Setup {

    const VERSION        = '1.0.0';
    const VERSION_OPTION = 'gfy_connect_version';

    /**
     * Plugin-specific tier definitions to merge into guestify_tier_config.
     */
    const PLUGIN_TIERS = [
        'plugin_free' => [
            'name'              => 'Plugin Free',
            'tags'              => [ 'mem: guestify plugin free' ],
            'priority'          => 10,      // Below accelerator (40).
            'credits'           => 50,      // ~3 AI generations + 1 transcription.
            'opportunities'     => 0,       // Plugin users don't get SaaS features.
            'campaigns'         => 0,
            'profiles'          => 1,
            'daily_enrichments' => 0,
        ],
        'plugin_starter' => [
            'name'              => 'Plugin Starter',
            'tags'              => [ 'mem: guestify plugin starter' ],
            'priority'          => 30,      // Below accelerator (40).
            'credits'           => 300,     // ~25 AI gen + 5 transcriptions.
            'opportunities'     => 0,
            'campaigns'         => 0,
            'profiles'          => 1,
            'daily_enrichments' => 0,
        ],
    ];

    /**
     * Plugin-specific credit action types.
     */
    const PLUGIN_ACTION_COSTS = [
        'plugin_ai_generation' => [
            'credits'     => 10,
            'description' => 'Plugin: AI Blog Post Generation',
            'category'    => 'plugin',
        ],
        'plugin_transcription' => [
            'credits'     => 15,
            'description' => 'Plugin: Audio Transcription',
            'category'    => 'plugin',
        ],
        'plugin_ai_takeaways' => [
            'credits'     => 5,
            'description' => 'Plugin: Key Takeaways Generation',
            'category'    => 'plugin',
        ],
        'plugin_ai_social' => [
            'credits'     => 5,
            'description' => 'Plugin: Social Media Snippets',
            'category'    => 'plugin',
        ],
    ];

    /**
     * Run the setup process.
     *
     * Checks version and runs migrations if needed.
     */
    public static function maybe_setup(): void {
        $installed_version = get_option( self::VERSION_OPTION, '' );

        if ( $installed_version === self::VERSION ) {
            return; // Already up to date.
        }

        self::run_setup();

        update_option( self::VERSION_OPTION, self::VERSION );
    }

    /**
     * Execute all setup tasks.
     */
    private static function run_setup(): void {
        // 1. Create OAuth tokens table.
        if ( class_exists( 'GFY_OAuth_Server' ) ) {
            GFY_OAuth_Server::create_tables();
        }

        // 2. Add plugin tiers to tier config.
        self::add_plugin_tiers();

        // 3. Register plugin credit action types.
        self::add_plugin_action_costs();

        // 4. Schedule token cleanup cron.
        self::schedule_cron();

        // 5. Create transcript cache table.
        self::create_transcript_cache_table();
    }

    /**
     * Merge plugin tiers into the existing guestify_tier_config.
     *
     * Only adds tiers that don't already exist (won't overwrite admin edits).
     */
    private static function add_plugin_tiers(): void {
        $tiers = get_option( GFY_Tier_Resolver::OPTION_KEY, [] );

        if ( ! is_array( $tiers ) ) {
            $tiers = GFY_Tier_Resolver::DEFAULT_TIERS;
        }

        $added = false;

        foreach ( self::PLUGIN_TIERS as $key => $config ) {
            if ( ! isset( $tiers[ $key ] ) ) {
                $tiers[ $key ] = $config;
                $added = true;
            }
        }

        if ( $added ) {
            update_option( GFY_Tier_Resolver::OPTION_KEY, $tiers, false );
        }
    }

    /**
     * Add plugin-specific credit action costs to the pit_credit_action_costs table.
     *
     * Only inserts actions that don't already exist.
     */
    private static function add_plugin_action_costs(): void {
        global $wpdb;
        $table = $wpdb->prefix . 'pit_credit_action_costs';

        // Check table exists.
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) !== $table ) {
            return; // Credit tables not yet created.
        }

        foreach ( self::PLUGIN_ACTION_COSTS as $action_type => $config ) {
            $exists = $wpdb->get_var( $wpdb->prepare(
                "SELECT id FROM {$table} WHERE action_type = %s",
                $action_type
            ) );

            if ( ! $exists ) {
                $wpdb->insert( $table, [
                    'action_type'      => $action_type,
                    'credits_per_unit' => $config['credits'],
                    'description'      => $config['description'],
                    'category'         => $config['category'],
                    'is_active'        => 1,
                    'created_at'       => current_time( 'mysql' ),
                ] );
            }
        }
    }

    /**
     * Schedule the daily token cleanup cron job.
     */
    private static function schedule_cron(): void {
        if ( ! wp_next_scheduled( 'gfy_oauth_cleanup' ) ) {
            wp_schedule_event( time(), 'daily', 'gfy_oauth_cleanup' );
        }
    }

    /**
     * Create the transcript cache table.
     */
    private static function create_transcript_cache_table(): void {
        global $wpdb;
        $table           = $wpdb->prefix . 'gfy_transcript_cache';
        $charset_collate = $wpdb->get_charset_collate();

        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) === $table ) {
            return; // Already exists.
        }

        $wpdb->query( "CREATE TABLE {$table} (
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
     * Force re-run setup (useful after manual version reset).
     */
    public static function force_setup(): void {
        delete_option( self::VERSION_OPTION );
        self::run_setup();
        update_option( self::VERSION_OPTION, self::VERSION );
    }

    /**
     * Clean up on deactivation / theme switch.
     */
    public static function cleanup(): void {
        wp_clear_scheduled_hook( 'gfy_oauth_cleanup' );
    }
}
