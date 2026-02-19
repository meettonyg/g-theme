<?php
/**
 * Credit Admin Page
 *
 * Registers the admin page under the Guestify menu (falls back to Settings
 * if ShowAuthority / Podcast Influence Tracker is not active).
 * Renders five tabs via nav-tab-wrapper and mounts a Vue 3 + Pinia app.
 *
 * @package Guestify
 * @since   2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GFY_Credit_Admin {

    /** Admin page slug */
    const PAGE_SLUG = 'gfy-credits';

    /**
     * Initialize: hook into admin_menu and admin asset enqueuing.
     */
    public static function init(): void {
        add_action('admin_menu', [__CLASS__, 'add_menu_page']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
    }

    /**
     * Register the admin submenu page.
     *
     * Tries to sit under the main Guestify menu ('podcast-influence').
     * Falls back to Settings → Credits if the parent menu doesn't exist.
     */
    public static function add_menu_page(): void {
        global $submenu;

        $parent = 'podcast-influence';

        // Check if the Guestify parent menu exists
        if (!isset($submenu[$parent]) && !menu_page_url($parent, false)) {
            $parent = 'options-general.php';
        }

        add_submenu_page(
            $parent,
            __('Credits', 'guestify'),
            __('Credits', 'guestify'),
            'manage_options',
            self::PAGE_SLUG,
            [__CLASS__, 'render_page']
        );
    }

    /**
     * Enqueue Vue 3, Pinia, and the Credit Admin app on our page only.
     *
     * @param string $hook_suffix The current admin page hook suffix.
     */
    public static function enqueue_assets(string $hook_suffix): void {
        // Only load on our admin page
        if (strpos($hook_suffix, self::PAGE_SLUG) === false) {
            return;
        }

        // --- Vue 3 + Pinia ---
        if (class_exists('GFY_Vue_Scripts')) {
            GFY_Vue_Scripts::enqueue();
        } elseif (class_exists('PIT_Vue_Scripts')) {
            PIT_Vue_Scripts::enqueue();
        } else {
            // Fallback: register CDN scripts directly
            $vue_version      = '3.3.4';
            $vue_demi_version = '0.14.6';
            $pinia_version    = '2.1.7';

            if (!wp_script_is('vue', 'registered')) {
                wp_register_script('vue', 'https://unpkg.com/vue@' . $vue_version . '/dist/vue.global.prod.js', [], $vue_version, true);
            }
            wp_enqueue_script('vue');

            if (!wp_script_is('vue-demi', 'registered')) {
                wp_register_script('vue-demi', 'https://unpkg.com/vue-demi@' . $vue_demi_version . '/lib/index.iife.js', ['vue'], $vue_demi_version, true);
            }
            wp_enqueue_script('vue-demi');

            if (!wp_script_is('pinia', 'registered')) {
                wp_register_script('pinia', 'https://unpkg.com/pinia@' . $pinia_version . '/dist/pinia.iife.js', ['vue', 'vue-demi'], $pinia_version, true);
            }
            wp_enqueue_script('pinia');
        }

        $version = self::get_version();

        // --- Credit Admin CSS ---
        wp_enqueue_style(
            'gfy-credit-admin',
            get_template_directory_uri() . '/css/admin-credits.css',
            [],
            $version
        );

        // --- Credit Admin JS (Vue app) ---
        wp_enqueue_script(
            'gfy-credit-admin',
            get_template_directory_uri() . '/js/admin-credits.js',
            ['vue', 'pinia'],
            $version,
            true
        );

        // Pass config data to JS (same shape as pitData used by the Vue app)
        wp_localize_script('gfy-credit-admin', 'pitData', [
            'guestifyApiUrl' => rest_url('guestify/v1'),
            'nonce'          => wp_create_nonce('wp_rest'),
        ]);
    }

    /**
     * Render the admin page with nav-tab-wrapper and Vue mount point.
     */
    public static function render_page(): void {
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'users';
        $valid_tabs = ['users', 'tiers', 'mapping', 'costs', 'packs'];
        if (!in_array($active_tab, $valid_tabs, true)) {
            $active_tab = 'users';
        }
        ?>
        <div class="wrap">
            <h1><?php _e('Credit Management', 'guestify'); ?></h1>

            <nav class="nav-tab-wrapper">
                <a href="?page=<?php echo esc_attr(self::PAGE_SLUG); ?>&tab=users"
                   class="nav-tab <?php echo $active_tab === 'users' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('User Credits', 'guestify'); ?>
                </a>
                <a href="?page=<?php echo esc_attr(self::PAGE_SLUG); ?>&tab=tiers"
                   class="nav-tab <?php echo $active_tab === 'tiers' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Tier Configuration', 'guestify'); ?>
                </a>
                <a href="?page=<?php echo esc_attr(self::PAGE_SLUG); ?>&tab=mapping"
                   class="nav-tab <?php echo $active_tab === 'mapping' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Plan-Tier Mapping', 'guestify'); ?>
                </a>
                <a href="?page=<?php echo esc_attr(self::PAGE_SLUG); ?>&tab=costs"
                   class="nav-tab <?php echo $active_tab === 'costs' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Action Costs', 'guestify'); ?>
                </a>
                <a href="?page=<?php echo esc_attr(self::PAGE_SLUG); ?>&tab=packs"
                   class="nav-tab <?php echo $active_tab === 'packs' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Credit Packs', 'guestify'); ?>
                </a>
            </nav>

            <div id="pit-app-credits" data-tab="<?php echo esc_attr($active_tab); ?>" style="margin-top: 20px;">
                <p><?php _e('Loading...', 'guestify'); ?></p>
            </div>
        </div>
        <?php
    }

    /**
     * Get version for cache busting
     */
    private static function get_version(): string {
        if (defined('PIT_VERSION')) {
            return PIT_VERSION;
        }
        return wp_get_theme()->get('Version') ?: '2.0.0';
    }
}
