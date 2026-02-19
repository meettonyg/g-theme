<?php
/**
 * WP Fusion Credit Sync
 *
 * Listens to plan lifecycle actions fired by GFY_WPFusion_Integration
 * (in guestify-core) and syncs credit allocations accordingly.
 *
 * Data flow:
 *   GHL subscription change
 *     -> WP Fusion applies/removes tags on WP user
 *       -> wpf_tags_modified fires
 *         -> GFY_WPFusion_Integration fires gfy_plan_activated etc.
 *           -> THIS CLASS listens and calls GFY_Credit_Repository
 *
 * @package Guestify
 * @since   2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GFY_WPFusion_Credit_Sync {

    /**
     * wp_options key for plan-to-tier mapping (admin-editable)
     */
    const PLAN_TIER_MAP_OPTION = 'guestify_plan_tier_map';

    /**
     * Default plan -> tier mapping
     */
    const DEFAULT_PLAN_TIER_MAP = [
        'starter'      => ['tier' => 'accelerator', 'billing_period' => 'monthly'],
        'professional' => ['tier' => 'velocity',    'billing_period' => 'monthly'],
        'enterprise'   => ['tier' => 'zenith',      'billing_period' => 'annual'],
        'free'         => ['tier' => 'free',         'billing_period' => 'monthly'],
    ];

    // -------------------------------------------------------------------------
    // Initialization — hook into gfy_plan_* actions
    // -------------------------------------------------------------------------

    /**
     * Register all WordPress action listeners
     */
    public static function init() {
        // Primary hooks: plan lifecycle events from guestify-core
        add_action('gfy_plan_activated',   [__CLASS__, 'handle_plan_activated'],   10, 2);
        add_action('gfy_plan_cancelled',   [__CLASS__, 'handle_plan_cancelled'],   10, 1);
        add_action('gfy_plan_upgraded',    [__CLASS__, 'handle_plan_upgraded'],    10, 3);
        add_action('gfy_plan_downgraded',  [__CLASS__, 'handle_plan_downgraded'],  10, 2);
        add_action('gfy_plan_reactivated', [__CLASS__, 'handle_plan_reactivated'], 10, 2);

        // Backup hook: catch direct tag changes (admin manual edits, etc.)
        add_action('wpf_tags_modified', [__CLASS__, 'handle_tags_modified'], 20, 2);
    }

    // -------------------------------------------------------------------------
    // Plan Lifecycle Handlers
    // -------------------------------------------------------------------------

    /**
     * Handle plan activation (new subscription)
     *
     * @param int    $user_id WP user ID
     * @param string $plan    Agency plan name
     */
    public static function handle_plan_activated(int $user_id, string $plan) {
        $mapping = self::resolve_plan_mapping($plan);
        if (!$mapping) {
            self::log('activation_skipped', $user_id, "No tier mapping for plan: {$plan}");
            return;
        }

        $tier           = $mapping['tier'];
        $billing_period = $mapping['billing_period'];

        GFY_Credit_Repository::update_tier_allocation($user_id, $tier, true);
        self::set_billing_cycle($user_id, $billing_period);

        self::log('plan_activated', $user_id, "Plan '{$plan}' -> tier '{$tier}', period '{$billing_period}'");

        do_action('guestify_credit_tier_changed', $user_id, $tier, 'activated');
    }

    /**
     * Handle plan cancellation
     *
     * @param int $user_id WP user ID
     */
    public static function handle_plan_cancelled(int $user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'pit_credit_allocations';

        $alloc = GFY_Credit_Repository::get_allocation($user_id);
        if (!$alloc) {
            self::log('cancel_skipped', $user_id, 'No allocation found');
            return;
        }

        $wpdb->update($table, [
            'monthly_allowance' => 0,
            'hard_cap'          => 0,
            'tier'              => 'cancelled',
        ], ['id' => $alloc->id]);

        self::log_transaction($user_id, $alloc->id, 'plan_cancelled', 0, [
            'previous_tier'      => $alloc->tier,
            'remaining_balance'  => (int) $alloc->current_balance + (int) $alloc->rollover_balance + (int) $alloc->overage_balance,
            'cycle_end'          => $alloc->billing_cycle_end,
        ]);

        self::log('plan_cancelled', $user_id, "Tier '{$alloc->tier}' cancelled, balance preserved until {$alloc->billing_cycle_end}");

        do_action('guestify_credit_tier_changed', $user_id, 'cancelled', 'cancelled');
    }

    /**
     * Handle plan upgrade
     *
     * @param int    $agency_id Agency (user) ID
     * @param string $old_plan  Previous plan name
     * @param string $new_plan  New plan name
     */
    public static function handle_plan_upgraded(int $agency_id, string $old_plan, string $new_plan) {
        $user_id = $agency_id;

        $mapping = self::resolve_plan_mapping($new_plan);
        if (!$mapping) {
            self::log('upgrade_skipped', $user_id, "No tier mapping for plan: {$new_plan}");
            return;
        }

        $tier           = $mapping['tier'];
        $billing_period = $mapping['billing_period'];

        $old_alloc = GFY_Credit_Repository::get_allocation($user_id);
        $old_tier  = $old_alloc ? $old_alloc->tier : 'none';

        GFY_Credit_Repository::update_tier_allocation($user_id, $tier, true);
        self::set_billing_cycle($user_id, $billing_period);

        $alloc = GFY_Credit_Repository::get_allocation($user_id);
        if ($alloc) {
            self::log_transaction($user_id, $alloc->id, 'plan_upgraded', 0, [
                'old_plan' => $old_plan,
                'new_plan' => $new_plan,
                'old_tier' => $old_tier,
                'new_tier' => $tier,
            ]);
        }

        self::log('plan_upgraded', $user_id, "'{$old_plan}' ({$old_tier}) -> '{$new_plan}' ({$tier})");

        do_action('guestify_credit_tier_changed', $user_id, $tier, 'upgraded');
    }

    /**
     * Handle plan downgrade
     *
     * @param int    $user_id WP user ID
     * @param string $plan    New (lower) plan name
     */
    public static function handle_plan_downgraded(int $user_id, string $plan) {
        $mapping = self::resolve_plan_mapping($plan);
        if (!$mapping) {
            self::log('downgrade_skipped', $user_id, "No tier mapping for plan: {$plan}");
            return;
        }

        $tier           = $mapping['tier'];
        $billing_period = $mapping['billing_period'];

        $old_alloc = GFY_Credit_Repository::get_allocation($user_id);
        $old_tier  = $old_alloc ? $old_alloc->tier : 'none';

        GFY_Credit_Repository::update_tier_allocation($user_id, $tier, false);

        global $wpdb;
        $table = $wpdb->prefix . 'pit_credit_allocations';
        $wpdb->update($table, [
            'billing_period' => $billing_period,
        ], ['user_id' => $user_id]);

        $alloc = GFY_Credit_Repository::get_allocation($user_id);
        if ($alloc) {
            self::log_transaction($user_id, $alloc->id, 'plan_downgraded', 0, [
                'old_tier'        => $old_tier,
                'new_tier'        => $tier,
                'plan'            => $plan,
                'capped_balance'  => (int) $alloc->current_balance,
            ]);
        }

        self::log('plan_downgraded', $user_id, "'{$old_tier}' -> '{$tier}' (plan: {$plan})");

        do_action('guestify_credit_tier_changed', $user_id, $tier, 'downgraded');
    }

    /**
     * Handle plan reactivation (same as activation)
     *
     * @param int    $user_id WP user ID
     * @param string $plan    Plan name
     */
    public static function handle_plan_reactivated(int $user_id, string $plan) {
        self::handle_plan_activated($user_id, $plan);
        self::log('plan_reactivated', $user_id, "Plan '{$plan}' reactivated (treated as activation)");
    }

    // -------------------------------------------------------------------------
    // Backup: Direct WP Fusion Tag Changes
    // -------------------------------------------------------------------------

    /**
     * Handle direct tag modifications (catches admin manual edits, etc.)
     *
     * @param int   $user_id WP user ID
     * @param array $tags    User's current tags
     */
    public static function handle_tags_modified(int $user_id, array $tags) {
        if (!class_exists('GFY_Tier_Resolver')) {
            return;
        }

        $resolved = GFY_Tier_Resolver::get_user_tier($user_id);
        if (!$resolved) {
            return;
        }

        $resolved_tier = $resolved['key'] ?? null;
        if (!$resolved_tier) {
            return;
        }

        $alloc = GFY_Credit_Repository::get_allocation($user_id);
        $stored_tier = $alloc ? $alloc->tier : null;

        if ($stored_tier === $resolved_tier) {
            return;
        }

        $tier_order = GFY_Tier_Resolver::get_tier_priority_order();
        $old_index  = array_search($stored_tier, $tier_order, true);
        $new_index  = array_search($resolved_tier, $tier_order, true);

        $is_upgrade = ($old_index === false) || ($new_index !== false && $new_index < $old_index);

        GFY_Credit_Repository::update_tier_allocation($user_id, $resolved_tier, $is_upgrade);

        if (!$alloc || empty($alloc->billing_cycle_end)) {
            $billing_period = self::guess_billing_period($resolved_tier);
            self::set_billing_cycle($user_id, $billing_period);
        }

        self::log('tags_sync', $user_id, "Tag change detected: '{$stored_tier}' -> '{$resolved_tier}' (upgrade: " . ($is_upgrade ? 'yes' : 'no') . ')');

        do_action('guestify_credit_tier_changed', $user_id, $resolved_tier, 'tags_sync');
    }

    // -------------------------------------------------------------------------
    // Billing Cycle Management
    // -------------------------------------------------------------------------

    /**
     * Set billing cycle dates for a user's allocation
     *
     * @param int    $user_id        WP user ID
     * @param string $billing_period 'monthly' or 'annual'
     */
    private static function set_billing_cycle(int $user_id, string $billing_period = 'monthly') {
        global $wpdb;
        $table = $wpdb->prefix . 'pit_credit_allocations';

        $now = current_time('Y-m-d');

        if ($billing_period === 'annual') {
            $cycle_end = gmdate('Y-m-d', strtotime($now . ' + 365 days'));
        } else {
            $cycle_end = gmdate('Y-m-d', strtotime($now . ' + 30 days'));
        }

        $wpdb->update($table, [
            'billing_cycle_start' => $now,
            'billing_cycle_end'   => $cycle_end,
            'billing_period'      => $billing_period,
        ], ['user_id' => $user_id]);
    }

    /**
     * Guess billing period from tier name
     *
     * @param string $tier Tier key
     * @return string
     */
    private static function guess_billing_period(string $tier): string {
        $map = self::get_plan_tier_map();
        foreach ($map as $plan => $config) {
            if (($config['tier'] ?? '') === $tier && !empty($config['billing_period'])) {
                return $config['billing_period'];
            }
        }
        return ($tier === 'zenith') ? 'annual' : 'monthly';
    }

    // -------------------------------------------------------------------------
    // Plan-to-Tier Mapping
    // -------------------------------------------------------------------------

    /**
     * Get the plan-to-tier mapping
     *
     * @return array
     */
    public static function get_plan_tier_map(): array {
        $stored = get_option(self::PLAN_TIER_MAP_OPTION);
        if (is_array($stored) && !empty($stored)) {
            return $stored;
        }
        return self::DEFAULT_PLAN_TIER_MAP;
    }

    /**
     * Save the plan-to-tier mapping
     *
     * @param array $map
     * @return bool
     */
    public static function save_plan_tier_map(array $map): bool {
        return update_option(self::PLAN_TIER_MAP_OPTION, $map, false);
    }

    /**
     * Resolve a plan name to its tier + billing period config
     *
     * @param string $plan Agency plan name
     * @return array|null
     */
    public static function resolve_plan_mapping(string $plan): ?array {
        $plan = strtolower(trim($plan));
        $map  = self::get_plan_tier_map();

        if (isset($map[$plan])) {
            return $map[$plan];
        }

        // Fuzzy fallback
        foreach ($map as $key => $config) {
            if (strpos($plan, $key) !== false || strpos($key, $plan) !== false) {
                return $config;
            }
        }

        return null;
    }

    // -------------------------------------------------------------------------
    // Transaction Logging
    // -------------------------------------------------------------------------

    /**
     * Log a credit transaction for plan changes
     */
    private static function log_transaction(int $user_id, int $allocation_id, string $action_type, int $credits_used = 0, array $metadata = []) {
        global $wpdb;
        $table = $wpdb->prefix . 'pit_credit_transactions';

        $alloc = GFY_Credit_Repository::get_allocation($user_id);
        $balance_after = $alloc
            ? (int) $alloc->current_balance + (int) $alloc->rollover_balance + (int) $alloc->overage_balance
            : 0;

        $wpdb->insert($table, [
            'user_id'        => $user_id,
            'allocation_id'  => $allocation_id,
            'action_type'    => $action_type,
            'credits_used'   => $credits_used,
            'balance_after'  => $balance_after,
            'source_type'    => 'system',
            'reference_id'   => null,
            'reference_type' => 'wpfusion_sync',
            'metadata'       => wp_json_encode($metadata),
            'created_at'     => current_time('mysql'),
        ]);
    }

    /**
     * Internal logging helper
     */
    private static function log(string $event, int $user_id, string $message) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf('[GFY_WPFusion_Credit_Sync] %s | user:%d | %s', $event, $user_id, $message));
        }
        do_action('guestify_credit_sync_log', $event, $user_id, $message);
    }

    // -------------------------------------------------------------------------
    // REST API Routes (Admin: Plan-Tier Mapping)
    // -------------------------------------------------------------------------

    /**
     * Register admin REST routes
     */
    public static function register_routes() {
        $namespace = 'guestify/v1';

        register_rest_route($namespace, '/credit-sync/plan-tier-map', [
            'methods'             => 'GET',
            'callback'            => [__CLASS__, 'rest_get_plan_tier_map'],
            'permission_callback' => function () {
                return current_user_can(GFY_Constants::CAPABILITY_MANAGE);
            },
        ]);

        register_rest_route($namespace, '/credit-sync/plan-tier-map', [
            'methods'             => 'PUT',
            'callback'            => [__CLASS__, 'rest_update_plan_tier_map'],
            'permission_callback' => function () {
                return current_user_can(GFY_Constants::CAPABILITY_MANAGE);
            },
        ]);

        register_rest_route($namespace, '/credit-sync/sync-user', [
            'methods'             => 'POST',
            'callback'            => [__CLASS__, 'rest_sync_user'],
            'permission_callback' => function () {
                return current_user_can(GFY_Constants::CAPABILITY_MANAGE);
            },
        ]);
    }

    /**
     * GET /credit-sync/plan-tier-map
     */
    public static function rest_get_plan_tier_map(): WP_REST_Response {
        $map = self::get_plan_tier_map();

        $available_tiers = [];
        if (class_exists('GFY_Tier_Resolver')) {
            $tiers = GFY_Tier_Resolver::get_all_tiers();
            foreach ($tiers as $key => $config) {
                $available_tiers[$key] = [
                    'name'    => $config['name'] ?? $key,
                    'credits' => $config['credits'] ?? 0,
                ];
            }
        }

        return rest_ensure_response([
            'success' => true,
            'data'    => [
                'map'             => $map,
                'available_tiers' => $available_tiers,
                'defaults'        => self::DEFAULT_PLAN_TIER_MAP,
            ],
        ]);
    }

    /**
     * PUT /credit-sync/plan-tier-map
     */
    public static function rest_update_plan_tier_map(WP_REST_Request $request): WP_REST_Response {
        $map = $request->get_json_params();

        if (!is_array($map) || empty($map)) {
            return rest_ensure_response([
                'success' => false,
                'message' => 'Plan-tier map must be a non-empty object.',
            ]);
        }

        foreach ($map as $plan => $config) {
            if (!is_array($config) || empty($config['tier'])) {
                return rest_ensure_response([
                    'success' => false,
                    'message' => "Plan '{$plan}' must have at least a 'tier' key.",
                ]);
            }

            if (!in_array($config['billing_period'] ?? 'monthly', ['monthly', 'annual'], true)) {
                $map[$plan]['billing_period'] = 'monthly';
            }
        }

        self::save_plan_tier_map($map);

        return rest_ensure_response([
            'success' => true,
            'data'    => self::get_plan_tier_map(),
            'message' => 'Plan-tier mapping updated.',
        ]);
    }

    /**
     * POST /credit-sync/sync-user
     */
    public static function rest_sync_user(WP_REST_Request $request): WP_REST_Response {
        $user_id = (int) $request->get_param('user_id');

        if (!$user_id || !get_user_by('ID', $user_id)) {
            return rest_ensure_response([
                'success' => false,
                'message' => 'Invalid user ID.',
            ]);
        }

        if (!class_exists('GFY_Tier_Resolver')) {
            return rest_ensure_response([
                'success' => false,
                'message' => 'Tier resolver not available.',
            ]);
        }

        $resolved = GFY_Tier_Resolver::get_user_tier($user_id);
        $resolved_tier = $resolved ? ($resolved['key'] ?? 'free') : 'free';

        $old_alloc = GFY_Credit_Repository::get_allocation($user_id);
        $old_tier  = $old_alloc ? $old_alloc->tier : 'none';

        GFY_Credit_Repository::update_tier_allocation($user_id, $resolved_tier, true);

        $alloc = GFY_Credit_Repository::get_allocation($user_id);
        if ($alloc && empty($alloc->billing_cycle_end)) {
            $billing_period = self::guess_billing_period($resolved_tier);
            self::set_billing_cycle($user_id, $billing_period);
        }

        $final_alloc = GFY_Credit_Repository::get_allocation($user_id);

        self::log('manual_sync', $user_id, "Admin triggered sync: '{$old_tier}' -> '{$resolved_tier}'");

        return rest_ensure_response([
            'success' => true,
            'data'    => [
                'user_id'         => $user_id,
                'previous_tier'   => $old_tier,
                'resolved_tier'   => $resolved_tier,
                'allocation'      => $final_alloc ? [
                    'tier'              => $final_alloc->tier,
                    'monthly_allowance' => (int) $final_alloc->monthly_allowance,
                    'current_balance'   => (int) $final_alloc->current_balance,
                    'rollover_balance'  => (int) $final_alloc->rollover_balance,
                    'overage_balance'   => (int) $final_alloc->overage_balance,
                    'billing_period'    => $final_alloc->billing_period,
                    'billing_cycle_end' => $final_alloc->billing_cycle_end,
                ] : null,
            ],
            'message' => "User synced from '{$old_tier}' to '{$resolved_tier}'.",
        ]);
    }
}
