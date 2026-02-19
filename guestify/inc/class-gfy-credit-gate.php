<?php
/**
 * Credit Gate — Pre-action enforcement middleware
 *
 * Call check() before a credit-consuming action to verify the user
 * has enough credits. Call deduct() after the action succeeds.
 *
 * Usage:
 *   $check = GFY_Credit_Gate::check('deep_show_intel');
 *   if (is_wp_error($check)) return $check;
 *   // ... perform the action ...
 *   GFY_Credit_Gate::deduct('deep_show_intel', 1, ['podcast_id' => $id]);
 *
 * @package Guestify
 * @since   2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GFY_Credit_Gate {

    /**
     * Check whether the current user can afford an action
     *
     * @param string   $action_type Action type key (must match pit_credit_action_costs table)
     * @param int      $units       Number of units
     * @param int|null $user_id     User ID (defaults to current user)
     * @return true|WP_Error True if allowed, WP_Error if insufficient credits
     */
    public static function check(string $action_type, int $units = 1, ?int $user_id = null): bool|WP_Error {
        $user_id = $user_id ?? get_current_user_id();

        if (!$user_id) {
            return new WP_Error('not_logged_in', 'You must be logged in to perform this action.', ['status' => 401]);
        }

        // Ensure credit tables exist before checking
        if (!GFY_Credits_Schema::tables_exist()) {
            return true; // Graceful: don't block if credits system isn't installed yet
        }

        $cost = GFY_Credit_Repository::get_action_cost($action_type);
        if ($cost <= 0) {
            return true; // Free action or unknown action type
        }

        $total_cost = $cost * $units;
        $balance = GFY_Credit_Repository::get_balance($user_id);

        if ($balance['total'] >= $total_cost) {
            return true;
        }

        // Determine which upgrade prompt to show
        $tier_summary = GFY_Tier_Resolver::get_user_tier_summary($user_id);
        $tier_name = $tier_summary['tier']['name'] ?? 'Free';

        // At hard cap (3x allocation exhausted)
        if ($balance['total'] <= 0 && $balance['hard_cap'] > 0) {
            return new WP_Error(
                'credit_hard_cap',
                sprintf(
                    'You have reached your credit limit for this billing cycle. Your %s plan credits will refill on %s.',
                    $tier_name,
                    date('F j', strtotime($balance['billing_cycle_end']))
                ),
                [
                    'status'      => 403,
                    'code'        => 'hard_cap',
                    'balance'     => $balance,
                    'cost'        => $total_cost,
                    'refill_date' => $balance['billing_cycle_end'],
                    'upgrade_url' => home_url('/pricing/'),
                ]
            );
        }

        // Out of allowance but could buy overages
        return new WP_Error(
            'insufficient_credits',
            sprintf(
                'This action requires %d credits but you only have %d remaining. Upgrade your plan or purchase additional credits.',
                $total_cost,
                $balance['total']
            ),
            [
                'status'      => 402,
                'code'        => 'insufficient',
                'balance'     => $balance,
                'cost'        => $total_cost,
                'upgrade_url' => home_url('/pricing/'),
            ]
        );
    }

    /**
     * Deduct credits after a successful action
     *
     * @param string   $action_type Action type key
     * @param int      $units       Number of units
     * @param array    $metadata    Additional context (podcast_id, provider, etc.)
     * @param int|null $user_id     User ID (defaults to current user)
     * @return bool Whether deduction was successful
     */
    public static function deduct(string $action_type, int $units = 1, array $metadata = [], ?int $user_id = null): bool {
        $user_id = $user_id ?? get_current_user_id();

        if (!$user_id || !GFY_Credits_Schema::tables_exist()) {
            return false;
        }

        return GFY_Credit_Repository::spend($user_id, $action_type, $units, $metadata);
    }
}
