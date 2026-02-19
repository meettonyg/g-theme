<?php
/**
 * Guestify Tier Resolver
 *
 * Resolves a user's membership tier from WP Fusion tags (synced from GoHighLevel).
 * Provides per-tier limits for credits, opportunities, campaigns, and profiles.
 *
 * All tier configuration is stored in wp_options and editable via the admin UI.
 * The DEFAULT_TIERS constant is only used to seed the initial configuration.
 *
 * @package Guestify
 * @since   2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GFY_Tier_Resolver {

    /**
     * Option key for storing tier configuration in wp_options
     */
    const OPTION_KEY = 'guestify_tier_config';

    /**
     * Default tier configuration — used only to seed initial wp_options value.
     * After first load, all values come from the database and are admin-editable.
     */
    const DEFAULT_TIERS = [
        'unlimited' => [
            'name'          => 'Unlimited',
            'tags'          => ['mem: guestify pos unlimited'],
            'priority'      => 100,
            'credits'       => -1,
            'opportunities' => -1,
            'campaigns'     => -1,
            'profiles'      => -1,
            'daily_enrichments' => -1,
        ],
        'zenith' => [
            'name'          => 'Zenith',
            'tags'          => ['mem: guestify zenith', 'mem: guestify zenith trial'],
            'priority'      => 80,
            'credits'       => 4000,
            'opportunities' => 1000,
            'campaigns'     => 150,
            'profiles'      => 10,
            'daily_enrichments' => 250,
        ],
        'velocity' => [
            'name'          => 'Velocity',
            'tags'          => ['mem: guestify velocity', 'mem: guestify velocity trial'],
            'priority'      => 60,
            'credits'       => 1200,
            'opportunities' => 200,
            'campaigns'     => 40,
            'profiles'      => 3,
            'daily_enrichments' => 80,
        ],
        'accelerator' => [
            'name'          => 'Accelerator',
            'tags'          => ['mem: guestify accel', 'mem: guestify accel trial', 'mem: guestify pos free'],
            'priority'      => 40,
            'credits'       => 300,
            'opportunities' => 50,
            'campaigns'     => 10,
            'profiles'      => 1,
            'daily_enrichments' => 20,
        ],
        'free' => [
            'name'          => 'Free',
            'tags'          => [],
            'priority'      => 0,
            'credits'       => 0,
            'opportunities' => 10,
            'campaigns'     => 2,
            'profiles'      => 1,
            'daily_enrichments' => 5,
        ],
    ];

    /**
     * Get all configured tiers from database (or seed defaults on first access)
     *
     * @return array Tier configurations keyed by tier slug
     */
    public static function get_tiers(): array {
        $tiers = get_option(self::OPTION_KEY, null);

        if ($tiers === null || !is_array($tiers)) {
            $tiers = self::DEFAULT_TIERS;
            update_option(self::OPTION_KEY, $tiers, false);
        }

        return $tiers;
    }

    /**
     * Save tier configuration to database
     *
     * @param array $tiers Full tier config array
     * @return bool Whether save was successful
     */
    public static function save_tiers(array $tiers): bool {
        return update_option(self::OPTION_KEY, $tiers, false);
    }

    /**
     * Get a single tier's configuration
     *
     * @param string $tier_key Tier slug (e.g. 'zenith', 'velocity', 'accelerator')
     * @return array|null Tier config or null if not found
     */
    public static function get_tier(string $tier_key): ?array {
        $tiers = self::get_tiers();
        if (!isset($tiers[$tier_key])) {
            return null;
        }
        $tier = $tiers[$tier_key];
        $tier['key'] = $tier_key;
        return $tier;
    }

    /**
     * Get all configured tiers (alias for get_tiers)
     *
     * @return array Tier configurations keyed by tier slug
     */
    public static function get_all_tiers(): array {
        return self::get_tiers();
    }

    /**
     * Get tier keys ordered by priority (highest first)
     *
     * Useful for determining upgrade vs downgrade direction.
     * Lower index = higher tier.
     *
     * @return array Ordered tier keys, e.g. ['unlimited', 'zenith', 'velocity', 'accelerator', 'free']
     */
    public static function get_tier_priority_order(): array {
        $tiers = self::get_tiers();

        // Sort by priority descending (highest priority first)
        uasort($tiers, function ($a, $b) {
            return ($b['priority'] ?? 0) - ($a['priority'] ?? 0);
        });

        return array_keys($tiers);
    }

    /**
     * Update a single tier's configuration
     *
     * @param string $tier_key Tier slug
     * @param array  $tier_data Updated config
     * @return bool Whether save was successful
     */
    public static function update_tier(string $tier_key, array $tier_data): bool {
        $tiers = self::get_tiers();

        if (!isset($tier_data['name'])) {
            return false;
        }

        // Ensure numeric values for all limit fields
        foreach (['credits', 'opportunities', 'campaigns', 'profiles', 'daily_enrichments', 'priority'] as $field) {
            if (isset($tier_data[$field])) {
                $tier_data[$field] = (int) $tier_data[$field];
            }
        }

        // Ensure tags is an array
        if (!isset($tier_data['tags']) || !is_array($tier_data['tags'])) {
            $tier_data['tags'] = [];
        }

        $tiers[$tier_key] = $tier_data;

        return self::save_tiers($tiers);
    }

    /**
     * Delete a tier from the configuration.
     *
     * Protected tiers ('free', 'unlimited') cannot be deleted.
     *
     * @param string $tier_key Tier slug to delete.
     * @return bool Whether deletion was successful.
     */
    public static function delete_tier(string $tier_key): bool {
        // Never allow deletion of the anchoring tiers
        if (in_array($tier_key, ['free', 'unlimited'], true)) {
            return false;
        }

        $tiers = self::get_tiers();

        if (!isset($tiers[$tier_key])) {
            return false;
        }

        unset($tiers[$tier_key]);

        return self::save_tiers($tiers);
    }

    /**
     * Reset tiers to defaults
     *
     * @return bool Whether reset was successful
     */
    public static function reset_to_defaults(): bool {
        return update_option(self::OPTION_KEY, self::DEFAULT_TIERS, false);
    }

    /**
     * Resolve a user's effective membership tier
     *
     * Uses WP Fusion tags to determine which tier the user belongs to.
     * Returns the highest-priority matching tier.
     *
     * @param int|null $user_id User ID (defaults to current user)
     * @return array Tier configuration with 'key' added
     */
    public static function get_user_tier(?int $user_id = null): array {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }

        if (!$user_id) {
            return self::get_default_tier();
        }

        // Admins get unlimited
        if (user_can($user_id, GFY_Constants::CAPABILITY_MANAGE)) {
            $tiers = self::get_tiers();
            $tier = $tiers['unlimited'] ?? self::DEFAULT_TIERS['unlimited'];
            $tier['key'] = 'unlimited';
            return $tier;
        }

        // Get user's WP Fusion tags
        $user_tags = self::get_user_tags($user_id);

        if (empty($user_tags)) {
            return self::get_default_tier();
        }

        // Find the highest priority tier that matches the user's tags
        $tiers = self::get_tiers();
        $matched_tier = null;
        $highest_priority = -1;

        foreach ($tiers as $tier_key => $tier) {
            if (empty($tier['tags'])) {
                continue;
            }

            $tag_match = array_intersect($user_tags, $tier['tags']);

            if (!empty($tag_match) && ($tier['priority'] ?? 0) > $highest_priority) {
                $matched_tier = $tier;
                $matched_tier['key'] = $tier_key;
                $highest_priority = $tier['priority'] ?? 0;
            }
        }

        return $matched_tier ?? self::get_default_tier();
    }

    /**
     * Get a specific limit value for a user's tier
     *
     * @param string   $limit_name Limit key: 'credits', 'opportunities', 'campaigns', 'profiles', 'daily_enrichments'
     * @param int|null $user_id    User ID (defaults to current user)
     * @return int Limit value (-1 = unlimited)
     */
    public static function get_user_limit(string $limit_name, ?int $user_id = null): int {
        $tier = self::get_user_tier($user_id);
        return (int) ($tier[$limit_name] ?? 0);
    }

    /**
     * Get a specific limit value for a named tier
     *
     * @param string $tier_key   Tier slug
     * @param string $limit_name Limit key
     * @return int Limit value (-1 = unlimited)
     */
    public static function get_tier_limit(string $tier_key, string $limit_name): int {
        $tiers = self::get_tiers();
        if (!isset($tiers[$tier_key])) {
            return 0;
        }
        return (int) ($tiers[$tier_key][$limit_name] ?? 0);
    }

    /**
     * Get the default (free) tier
     *
     * @return array Default tier configuration with 'key' added
     */
    public static function get_default_tier(): array {
        $tiers = self::get_tiers();
        $default = $tiers['free'] ?? self::DEFAULT_TIERS['free'];
        $default['key'] = 'free';
        return $default;
    }

    /**
     * Get WP Fusion tags for a user
     *
     * @param int $user_id User ID
     * @return array Array of tag names
     */
    public static function get_user_tags(int $user_id): array {
        $tags = [];

        if (function_exists('wp_fusion') && method_exists(wp_fusion()->user, 'get_tags')) {
            $tag_ids = wp_fusion()->user->get_tags($user_id);

            if (!empty($tag_ids) && is_array($tag_ids)) {
                foreach ($tag_ids as $tag_id) {
                    $tag_name = wp_fusion()->user->get_tag_label($tag_id);
                    if ($tag_name) {
                        $tags[] = $tag_name;
                    }
                }
            }
        }

        /**
         * Filter user membership tags
         *
         * Allows other plugins to add/modify tags for tier resolution.
         *
         * @param array $tags    Current tags
         * @param int   $user_id User ID
         */
        return apply_filters('guestify_user_membership_tags', $tags, $user_id);
    }

    /**
     * Get summary of a user's tier and all limits (for API responses)
     *
     * @param int|null $user_id User ID (defaults to current user)
     * @return array Complete tier status
     */
    public static function get_user_tier_summary(?int $user_id = null): array {
        $tier = self::get_user_tier($user_id);

        return [
            'tier' => [
                'key'  => $tier['key'] ?? 'free',
                'name' => $tier['name'] ?? 'Free',
            ],
            'limits' => [
                'credits'           => (int) ($tier['credits'] ?? 0),
                'opportunities'     => (int) ($tier['opportunities'] ?? 0),
                'campaigns'         => (int) ($tier['campaigns'] ?? 0),
                'profiles'          => (int) ($tier['profiles'] ?? 0),
                'daily_enrichments' => (int) ($tier['daily_enrichments'] ?? 0),
            ],
        ];
    }
}
