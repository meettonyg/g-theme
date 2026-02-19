<?php
/**
 * Credit Repository
 *
 * Core operations for the Authority Credits system: balance queries,
 * spending, refilling, and transaction logging.
 *
 * Draw order for spending: allowance first -> rollover -> overage
 * (most perishable credits are consumed first)
 *
 * @package Guestify
 * @since   2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GFY_Credit_Repository {

    // -------------------------------------------------------------------------
    // Allocation CRUD
    // -------------------------------------------------------------------------

    /**
     * Get a user's credit allocation record
     *
     * @param int $user_id User ID
     * @return object|null Allocation row or null
     */
    public static function get_allocation(int $user_id): ?object {
        global $wpdb;
        $table = $wpdb->prefix . 'pit_credit_allocations';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE user_id = %d",
            $user_id
        ));
    }

    /**
     * Get or create a user's credit allocation, initializing from their tier
     *
     * @param int $user_id User ID
     * @return object Allocation row
     */
    public static function get_or_create_allocation(int $user_id): object {
        $allocation = self::get_allocation($user_id);

        if ($allocation) {
            return $allocation;
        }

        // Resolve tier and create allocation
        $tier = GFY_Tier_Resolver::get_user_tier($user_id);
        $monthly = (int) ($tier['credits'] ?? 0);

        // Hard cap = 3x monthly (per spec)
        $hard_cap = $monthly > 0 ? $monthly * 3 : 0;

        global $wpdb;
        $table = $wpdb->prefix . 'pit_credit_allocations';

        $wpdb->insert($table, [
            'user_id'             => $user_id,
            'tier'                => $tier['key'] ?? 'free',
            'monthly_allowance'   => $monthly,
            'current_balance'     => $monthly,
            'rollover_balance'    => 0,
            'overage_balance'     => 0,
            'hard_cap'            => $hard_cap,
            'billing_period'      => 'monthly',
            'billing_cycle_start' => current_time('Y-m-d'),
            'billing_cycle_end'   => date('Y-m-d', strtotime('+1 month')),
            'created_at'          => current_time('mysql'),
        ]);

        return self::get_allocation($user_id);
    }

    // -------------------------------------------------------------------------
    // Balance queries
    // -------------------------------------------------------------------------

    /**
     * Get a user's credit balance breakdown
     *
     * @param int $user_id User ID
     * @return array Balance breakdown
     */
    public static function get_balance(int $user_id): array {
        $alloc = self::get_or_create_allocation($user_id);

        $total = $alloc->current_balance + $alloc->rollover_balance + $alloc->overage_balance;
        $monthly = (int) $alloc->monthly_allowance;
        $percent_used = $monthly > 0
            ? round((($monthly - $alloc->current_balance) / $monthly) * 100, 1)
            : 0;

        return [
            'allowance'     => (int) $alloc->current_balance,
            'rollover'      => (int) $alloc->rollover_balance,
            'overage'       => (int) $alloc->overage_balance,
            'total'         => $total,
            'monthly_allowance' => $monthly,
            'hard_cap'      => (int) $alloc->hard_cap,
            'percent_used'  => min(100, max(0, $percent_used)),
            'tier'          => $alloc->tier,
            'billing_period'      => $alloc->billing_period,
            'billing_cycle_start' => $alloc->billing_cycle_start,
            'billing_cycle_end'   => $alloc->billing_cycle_end,
        ];
    }

    /**
     * Get the credit cost for an action type
     *
     * @param string $action_type Action type key
     * @return int Credits per unit (0 if unknown/inactive)
     */
    public static function get_action_cost(string $action_type): int {
        global $wpdb;
        $table = $wpdb->prefix . 'pit_credit_action_costs';

        $cost = $wpdb->get_var($wpdb->prepare(
            "SELECT credits_per_unit FROM {$table} WHERE action_type = %s AND is_active = 1",
            $action_type
        ));

        return $cost !== null ? (int) $cost : 0;
    }

    /**
     * Get all active action costs (for API/UI)
     *
     * @return array Action costs
     */
    public static function get_all_action_costs(): array {
        global $wpdb;
        $table = $wpdb->prefix . 'pit_credit_action_costs';

        return $wpdb->get_results(
            "SELECT action_type, credits_per_unit, description, category FROM {$table} WHERE is_active = 1 ORDER BY category, credits_per_unit DESC",
            ARRAY_A
        );
    }

    // -------------------------------------------------------------------------
    // Spending
    // -------------------------------------------------------------------------

    /**
     * Check if a user can afford an action
     *
     * @param int    $user_id     User ID
     * @param string $action_type Action type key
     * @param int    $units       Number of units (default 1)
     * @return bool Whether the user has enough credits
     */
    public static function can_spend(int $user_id, string $action_type, int $units = 1): bool {
        $cost = self::get_action_cost($action_type) * $units;
        if ($cost <= 0) {
            return true; // Free or unknown action
        }

        $alloc = self::get_or_create_allocation($user_id);
        $total = $alloc->current_balance + $alloc->rollover_balance + $alloc->overage_balance;

        // Check hard cap — total lifetime spend in this cycle
        if ((int) $alloc->hard_cap > 0 && $total <= 0) {
            return false;
        }

        return $total >= $cost;
    }

    /**
     * Spend credits for an action
     *
     * Draw order: allowance -> rollover -> overage (most perishable first)
     *
     * @param int    $user_id     User ID
     * @param string $action_type Action type key
     * @param int    $units       Number of units (default 1)
     * @param array  $metadata    Additional metadata (provider, tokens, etc.)
     * @return bool Whether spending was successful
     */
    public static function spend(int $user_id, string $action_type, int $units = 1, array $metadata = []): bool {
        $cost = self::get_action_cost($action_type) * $units;
        if ($cost <= 0) {
            return true; // Nothing to spend
        }

        $alloc = self::get_or_create_allocation($user_id);
        $total = $alloc->current_balance + $alloc->rollover_balance + $alloc->overage_balance;

        if ($total < $cost) {
            return false;
        }

        global $wpdb;
        $alloc_table = $wpdb->prefix . 'pit_credit_allocations';
        $tx_table = $wpdb->prefix . 'pit_credit_transactions';

        // Calculate draw-down across buckets
        $remaining = $cost;
        $from_allowance = 0;
        $from_rollover = 0;
        $from_overage = 0;

        // Draw from allowance first
        if ($remaining > 0 && $alloc->current_balance > 0) {
            $from_allowance = min($remaining, (int) $alloc->current_balance);
            $remaining -= $from_allowance;
        }

        // Then rollover
        if ($remaining > 0 && $alloc->rollover_balance > 0) {
            $from_rollover = min($remaining, (int) $alloc->rollover_balance);
            $remaining -= $from_rollover;
        }

        // Then overage
        if ($remaining > 0 && $alloc->overage_balance > 0) {
            $from_overage = min($remaining, (int) $alloc->overage_balance);
            $remaining -= $from_overage;
        }

        if ($remaining > 0) {
            return false; // Shouldn't happen given the total check above
        }

        // Update balances
        $new_balance = (int) $alloc->current_balance - $from_allowance;
        $new_rollover = (int) $alloc->rollover_balance - $from_rollover;
        $new_overage = (int) $alloc->overage_balance - $from_overage;

        $wpdb->update($alloc_table, [
            'current_balance'  => $new_balance,
            'rollover_balance' => $new_rollover,
            'overage_balance'  => $new_overage,
        ], ['id' => $alloc->id]);

        // Determine source type for the transaction log
        $source_type = 'allowance';
        if ($from_rollover > 0 && $from_allowance === 0) {
            $source_type = 'rollover';
        } elseif ($from_overage > 0 && $from_allowance === 0 && $from_rollover === 0) {
            $source_type = 'overage';
        }

        // Log transaction
        $tx_metadata = array_merge($metadata, [
            'from_allowance' => $from_allowance,
            'from_rollover'  => $from_rollover,
            'from_overage'   => $from_overage,
        ]);

        $wpdb->insert($tx_table, [
            'user_id'        => $user_id,
            'agency_id'      => $alloc->agency_id,
            'client_id'      => $alloc->client_id,
            'allocation_id'  => $alloc->id,
            'action_type'    => $action_type,
            'credits_used'   => $cost,
            'balance_after'  => $new_balance + $new_rollover + $new_overage,
            'source_type'    => $source_type,
            'reference_id'   => $metadata['reference_id'] ?? null,
            'reference_type' => $metadata['reference_type'] ?? null,
            'metadata'       => wp_json_encode($tx_metadata),
            'created_at'     => current_time('mysql'),
        ]);

        /**
         * Fired after credits are spent
         *
         * @param int    $user_id     User ID
         * @param string $action_type Action type
         * @param int    $cost        Credits spent
         * @param int    $balance     Remaining total balance
         */
        do_action('guestify_credits_spent', $user_id, $action_type, $cost, $new_balance + $new_rollover + $new_overage);

        return true;
    }

    // -------------------------------------------------------------------------
    // Transaction history
    // -------------------------------------------------------------------------

    /**
     * Get recent transactions for a user
     *
     * @param int $user_id  User ID
     * @param int $page     Page number (1-based)
     * @param int $per_page Items per page
     * @return array Transactions with pagination
     */
    public static function get_transactions(int $user_id, int $page = 1, int $per_page = 20): array {
        global $wpdb;
        $table = $wpdb->prefix . 'pit_credit_transactions';

        $offset = ($page - 1) * $per_page;

        $total = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE user_id = %d",
            $user_id
        ));

        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT id, action_type, credits_used, balance_after, source_type, reference_id, reference_type, metadata, created_at
             FROM {$table}
             WHERE user_id = %d
             ORDER BY created_at DESC
             LIMIT %d OFFSET %d",
            $user_id,
            $per_page,
            $offset
        ), ARRAY_A);

        // Decode metadata JSON
        foreach ($rows as &$row) {
            $row['metadata'] = $row['metadata'] ? json_decode($row['metadata'], true) : null;
        }

        return [
            'transactions' => $rows,
            'total'        => $total,
            'pages'        => (int) ceil($total / $per_page),
            'page'         => $page,
        ];
    }

    /**
     * Get a user's usage summary for the current billing cycle
     *
     * @param int $user_id User ID
     * @return array Usage breakdown by action type
     */
    public static function get_usage_summary(int $user_id): array {
        global $wpdb;
        $table = $wpdb->prefix . 'pit_credit_transactions';

        $alloc = self::get_or_create_allocation($user_id);

        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT action_type, SUM(credits_used) as total_credits, COUNT(*) as action_count
             FROM {$table}
             WHERE user_id = %d AND created_at >= %s AND source_type NOT IN ('refill', 'adjustment', 'rollover_grant')
             GROUP BY action_type
             ORDER BY total_credits DESC",
            $user_id,
            $alloc->billing_cycle_start . ' 00:00:00'
        ), ARRAY_A);

        $total_spent = 0;
        foreach ($rows as &$row) {
            $row['total_credits'] = (int) $row['total_credits'];
            $row['action_count'] = (int) $row['action_count'];
            $total_spent += $row['total_credits'];
        }

        return [
            'by_action'   => $rows,
            'total_spent' => $total_spent,
            'cycle_start' => $alloc->billing_cycle_start,
            'cycle_end'   => $alloc->billing_cycle_end,
        ];
    }

    /**
     * Get a "remaining action budget" estimate
     *
     * Translates remaining credits into estimated actions.
     *
     * @param int $user_id User ID
     * @return array Estimated remaining actions per type
     */
    public static function get_action_budget(int $user_id): array {
        $balance = self::get_balance($user_id);
        $total = $balance['total'];
        $actions = self::get_all_action_costs();

        $budget = [];
        foreach ($actions as $action) {
            $cost = (int) $action['credits_per_unit'];
            $budget[] = [
                'action_type' => $action['action_type'],
                'description' => $action['description'],
                'cost'        => $cost,
                'remaining'   => $cost > 0 ? (int) floor($total / $cost) : -1,
            ];
        }

        return $budget;
    }

    // -------------------------------------------------------------------------
    // Admin operations
    // -------------------------------------------------------------------------

    /**
     * Manually adjust a user's credit balance (admin tool)
     *
     * @param int    $user_id    User ID
     * @param int    $amount     Credits to add (positive) or remove (negative)
     * @param string $reason     Reason for adjustment
     * @return bool Whether the adjustment was successful
     */
    public static function adjust_balance(int $user_id, int $amount, string $reason = ''): bool {
        $alloc = self::get_or_create_allocation($user_id);

        global $wpdb;
        $alloc_table = $wpdb->prefix . 'pit_credit_allocations';
        $tx_table = $wpdb->prefix . 'pit_credit_transactions';

        // Apply adjustment to current_balance (can go negative for removals, but floor at 0)
        $new_balance = max(0, (int) $alloc->current_balance + $amount);

        $wpdb->update($alloc_table, [
            'current_balance' => $new_balance,
        ], ['id' => $alloc->id]);

        // Log the adjustment
        $wpdb->insert($tx_table, [
            'user_id'       => $user_id,
            'agency_id'     => $alloc->agency_id,
            'client_id'     => $alloc->client_id,
            'allocation_id' => $alloc->id,
            'action_type'   => 'admin_adjustment',
            'credits_used'  => $amount, // Positive for additions, negative for removals
            'balance_after' => $new_balance + (int) $alloc->rollover_balance + (int) $alloc->overage_balance,
            'source_type'   => 'adjustment',
            'metadata'      => wp_json_encode(['reason' => $reason, 'admin_id' => get_current_user_id()]),
            'created_at'    => current_time('mysql'),
        ]);

        return true;
    }

    /**
     * Update a user's allocation when their tier changes
     *
     * @param int    $user_id  User ID
     * @param string $new_tier New tier key
     * @param bool   $is_upgrade Whether this is an upgrade (grants full allocation) or downgrade (caps balance)
     * @return bool Success
     */
    public static function update_tier_allocation(int $user_id, string $new_tier, bool $is_upgrade = true): bool {
        $alloc = self::get_or_create_allocation($user_id);
        $tier_config = GFY_Tier_Resolver::get_tier($new_tier);

        if (!$tier_config) {
            return false;
        }

        $new_monthly = (int) ($tier_config['credits'] ?? 0);
        $new_hard_cap = $new_monthly > 0 ? $new_monthly * 3 : 0;

        global $wpdb;
        $table = $wpdb->prefix . 'pit_credit_allocations';

        $update_data = [
            'tier'              => $new_tier,
            'monthly_allowance' => $new_monthly,
            'hard_cap'          => $new_hard_cap,
        ];

        if ($is_upgrade) {
            // Upgrade: grant full new allocation immediately
            $update_data['current_balance'] = $new_monthly;
        } else {
            // Downgrade: cap balance at new tier's allocation
            $update_data['current_balance'] = min((int) $alloc->current_balance, $new_monthly);
        }

        return $wpdb->update($table, $update_data, ['id' => $alloc->id]) !== false;
    }

    // -------------------------------------------------------------------------
    // Refill + Rollover
    // -------------------------------------------------------------------------

    /**
     * Process credit refill for a single user
     *
     * @param int $user_id User ID
     * @return bool Whether refill was processed
     */
    public static function refill_monthly(int $user_id): bool {
        $alloc = self::get_allocation($user_id);
        if (!$alloc) {
            return false;
        }

        // Check if cycle has elapsed
        if ($alloc->billing_cycle_end && strtotime($alloc->billing_cycle_end) > time()) {
            return false; // Not yet due
        }

        global $wpdb;
        $alloc_table = $wpdb->prefix . 'pit_credit_allocations';
        $tx_table = $wpdb->prefix . 'pit_credit_transactions';

        $monthly = (int) $alloc->monthly_allowance;
        $rollover_amount = 0;

        // Annual plans: roll over up to 25% of monthly allowance
        if ($alloc->billing_period === 'annual' && $monthly > 0) {
            $unused = (int) $alloc->current_balance;
            $max_rollover = (int) floor($monthly * 0.25);
            $rollover_amount = min($unused, $max_rollover);
        }

        // New balance = fresh monthly + rollover (capped at 125% of monthly)
        $new_balance = $monthly;
        $max_total = (int) floor($monthly * 1.25);
        $effective_rollover = min($rollover_amount, $max_total - $monthly);

        // Advance cycle
        $new_cycle_start = current_time('Y-m-d');
        $new_cycle_end = date('Y-m-d', strtotime('+1 month', strtotime($new_cycle_start)));

        $wpdb->update($alloc_table, [
            'current_balance'     => $new_balance,
            'rollover_balance'    => max(0, $effective_rollover),
            'billing_cycle_start' => $new_cycle_start,
            'billing_cycle_end'   => $new_cycle_end,
        ], ['id' => $alloc->id]);

        // Log refill transaction
        $wpdb->insert($tx_table, [
            'user_id'       => $user_id,
            'agency_id'     => $alloc->agency_id,
            'client_id'     => $alloc->client_id,
            'allocation_id' => $alloc->id,
            'action_type'   => 'monthly_refill',
            'credits_used'  => $monthly, // Positive = credits granted
            'balance_after' => $new_balance + $effective_rollover + (int) $alloc->overage_balance,
            'source_type'   => 'refill',
            'metadata'      => wp_json_encode([
                'previous_balance' => (int) $alloc->current_balance,
                'rollover_amount'  => $effective_rollover,
                'billing_period'   => $alloc->billing_period,
            ]),
            'created_at'    => current_time('mysql'),
        ]);

        if ($effective_rollover > 0) {
            $wpdb->insert($tx_table, [
                'user_id'       => $user_id,
                'agency_id'     => $alloc->agency_id,
                'client_id'     => $alloc->client_id,
                'allocation_id' => $alloc->id,
                'action_type'   => 'rollover_credit',
                'credits_used'  => $effective_rollover,
                'balance_after' => $new_balance + $effective_rollover + (int) $alloc->overage_balance,
                'source_type'   => 'rollover_grant',
                'metadata'      => wp_json_encode(['unused_from_previous' => (int) $alloc->current_balance]),
                'created_at'    => current_time('mysql'),
            ]);
        }

        return true;
    }

    /**
     * Process refills for ALL users whose billing cycle has elapsed
     * Called by the daily cron job.
     *
     * @return int Number of users refilled
     */
    public static function process_all_refills(): int {
        global $wpdb;
        $table = $wpdb->prefix . 'pit_credit_allocations';

        // Find all allocations where cycle has elapsed (resilient to missed days)
        $due_users = $wpdb->get_col(
            "SELECT user_id FROM {$table} WHERE billing_cycle_end <= CURDATE()"
        );

        $count = 0;
        foreach ($due_users as $user_id) {
            if (self::refill_monthly((int) $user_id)) {
                $count++;
            }
        }

        return $count;
    }
}
