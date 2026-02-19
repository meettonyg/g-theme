<?php
/**
 * Credits Database Schema
 *
 * Creates and manages the database tables for the Authority Credits system:
 * - pit_credit_allocations: Per-user credit balance and billing cycle
 * - pit_credit_transactions: Audit log of all credit operations
 * - pit_credit_action_costs: Configurable credit costs per action type
 *
 * NOTE: Table names retain the `pit_credit_` prefix for backward compatibility.
 * No data migration is needed.
 *
 * @package Guestify
 * @since   2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GFY_Credits_Schema {

    const DB_VERSION = '1.0.0';
    const DB_VERSION_OPTION = 'pit_credits_db_version';

    /**
     * Default action costs — seeded on first install.
     * Admin-editable via pit_credit_action_costs table.
     */
    const DEFAULT_ACTION_COSTS = [
        'deep_show_intel' => [
            'credits'     => 5,
            'description' => 'Deep Show Intelligence Analysis',
            'category'    => 'ai',
        ],
        'host_enrichment' => [
            'credits'     => 3,
            'description' => 'Host Profile Enrichment',
            'category'    => 'enrichment',
        ],
        'audience_fit' => [
            'credits'     => 1,
            'description' => 'Audience Fit Score',
            'category'    => 'ai',
        ],
        'ai_positioning_rewrite' => [
            'credits'     => 4,
            'description' => 'AI Positioning Rewrite',
            'category'    => 'ai',
        ],
        'personalized_pitch' => [
            'credits'     => 2,
            'description' => 'Personalized Pitch Generation',
            'category'    => 'ai',
        ],
        'relationship_refresh' => [
            'credits'     => 1,
            'description' => 'Relationship Refresh Analysis',
            'category'    => 'ai',
        ],
        'bulk_scan' => [
            'credits'     => 10,
            'description' => 'Bulk Podcast Scan',
            'category'    => 'ai',
        ],
    ];

    /**
     * Create all credit tables
     *
     * @return bool Whether all tables were created successfully
     */
    public static function create_tables(): bool {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $success = true;

        // 1. Credit Allocations — per-user balance and cycle tracking
        $allocations_table = $wpdb->prefix . 'pit_credit_allocations';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$allocations_table}'") !== $allocations_table) {
            $sql = "CREATE TABLE {$allocations_table} (
                id BIGINT UNSIGNED AUTO_INCREMENT,
                user_id BIGINT UNSIGNED NOT NULL,
                agency_id BIGINT UNSIGNED NULL,
                client_id BIGINT UNSIGNED NULL,
                tier VARCHAR(50) NOT NULL DEFAULT 'free',
                monthly_allowance INT NOT NULL DEFAULT 0,
                current_balance INT NOT NULL DEFAULT 0,
                rollover_balance INT NOT NULL DEFAULT 0,
                overage_balance INT NOT NULL DEFAULT 0,
                hard_cap INT NOT NULL DEFAULT 0,
                billing_period ENUM('monthly', 'annual') DEFAULT 'monthly',
                billing_cycle_start DATE NULL,
                billing_cycle_end DATE NULL,
                stripe_customer_id VARCHAR(100) NULL,
                stripe_subscription_id VARCHAR(100) NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY user_id_unique (user_id),
                INDEX idx_agency (agency_id),
                INDEX idx_tier (tier),
                INDEX idx_cycle_end (billing_cycle_end)
            ) {$charset_collate};";

            $wpdb->query($sql);
            $success = $success && ($wpdb->get_var("SHOW TABLES LIKE '{$allocations_table}'") === $allocations_table);
        }

        // 2. Credit Transactions — audit log of all credit operations
        $transactions_table = $wpdb->prefix . 'pit_credit_transactions';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$transactions_table}'") !== $transactions_table) {
            $sql = "CREATE TABLE {$transactions_table} (
                id BIGINT UNSIGNED AUTO_INCREMENT,
                user_id BIGINT UNSIGNED NOT NULL,
                agency_id BIGINT UNSIGNED NULL,
                client_id BIGINT UNSIGNED NULL,
                allocation_id BIGINT UNSIGNED NOT NULL,
                action_type VARCHAR(100) NOT NULL,
                credits_used INT NOT NULL DEFAULT 0,
                balance_after INT NOT NULL DEFAULT 0,
                source_type ENUM('allowance', 'rollover', 'overage', 'refill', 'adjustment', 'rollover_grant') DEFAULT 'allowance',
                reference_id BIGINT UNSIGNED NULL,
                reference_type VARCHAR(50) NULL,
                metadata JSON NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                INDEX idx_user_created (user_id, created_at),
                INDEX idx_action_type (action_type),
                INDEX idx_allocation (allocation_id),
                INDEX idx_source_type (source_type),
                INDEX idx_reference (reference_type, reference_id)
            ) {$charset_collate};";

            $wpdb->query($sql);
            $success = $success && ($wpdb->get_var("SHOW TABLES LIKE '{$transactions_table}'") === $transactions_table);
        }

        // 3. Credit Action Costs — configurable credit costs per action
        $action_costs_table = $wpdb->prefix . 'pit_credit_action_costs';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$action_costs_table}'") !== $action_costs_table) {
            $sql = "CREATE TABLE {$action_costs_table} (
                id BIGINT UNSIGNED AUTO_INCREMENT,
                action_type VARCHAR(100) NOT NULL,
                credits_per_unit INT NOT NULL DEFAULT 1,
                description VARCHAR(255) NULL,
                category VARCHAR(50) NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY action_type_unique (action_type),
                INDEX idx_category (category),
                INDEX idx_active (is_active)
            ) {$charset_collate};";

            $wpdb->query($sql);
            $success = $success && ($wpdb->get_var("SHOW TABLES LIKE '{$action_costs_table}'") === $action_costs_table);
        }

        // Seed default action costs if table was just created
        if ($success) {
            self::seed_action_costs();
        }

        // Store version
        if ($success) {
            update_option(self::DB_VERSION_OPTION, self::DB_VERSION);
        }

        return $success;
    }

    /**
     * Seed default action costs if the table is empty
     */
    public static function seed_action_costs(): void {
        global $wpdb;
        $table = $wpdb->prefix . 'pit_credit_action_costs';

        $count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
        if ($count > 0) {
            return; // Already seeded
        }

        foreach (self::DEFAULT_ACTION_COSTS as $action_type => $config) {
            $wpdb->insert($table, [
                'action_type'     => $action_type,
                'credits_per_unit' => $config['credits'],
                'description'     => $config['description'],
                'category'        => $config['category'],
                'is_active'       => 1,
                'created_at'      => current_time('mysql'),
            ]);
        }
    }

    /**
     * Check if tables exist
     *
     * @return bool Whether all credit tables exist
     */
    public static function tables_exist(): bool {
        global $wpdb;

        $tables = [
            $wpdb->prefix . 'pit_credit_allocations',
            $wpdb->prefix . 'pit_credit_transactions',
            $wpdb->prefix . 'pit_credit_action_costs',
        ];

        foreach ($tables as $table) {
            if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") !== $table) {
                return false;
            }
        }

        return true;
    }

    /**
     * Drop all credit tables (for uninstall)
     */
    public static function drop_tables(): void {
        global $wpdb;

        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}pit_credit_transactions");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}pit_credit_allocations");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}pit_credit_action_costs");

        delete_option(self::DB_VERSION_OPTION);
    }
}
