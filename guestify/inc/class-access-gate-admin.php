<?php
/**
 * Access Gate Admin Page
 *
 * Registers the admin page under the Guestify menu (falls back to Settings
 * if ShowAuthority / Podcast Influence Tracker is not active).
 * Renders two tabs via nav-tab-wrapper and mounts a Vue 3 + Pinia app.
 *
 * @package Guestify
 * @since   1.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GFY_Access_Gate_Admin {

    /** Admin page slug */
    const PAGE_SLUG = 'gfy-access-gate';

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
     * Falls back to Settings → Access Gate if the parent menu doesn't exist.
     */
    public static function add_menu_page(): void {
        global $submenu;

        $parent = 'podcast-influence';

        // Check if the Guestify parent menu exists
        if (!isset($submenu[$parent]) && !menu_page_url($parent, false)) {
            // ShowAuthority plugin inactive — fall back to Settings
            $parent = 'options-general.php';
        }

        add_submenu_page(
            $parent,
            __('Access Gate', 'guestify'),
            __('Access Gate', 'guestify'),
            'manage_options',
            self::PAGE_SLUG,
            [__CLASS__, 'render_page']
        );
    }

    /**
     * Enqueue Vue 3, Pinia, and the Access Gate admin app on our page only.
     *
     * @param string $hook_suffix The current admin page hook suffix.
     */
    public static function enqueue_assets(string $hook_suffix): void {
        // Only load on our admin page
        if (strpos($hook_suffix, self::PAGE_SLUG) === false) {
            return;
        }

        // --- Vue 3 + Pinia ---
        if (class_exists('PIT_Vue_Scripts')) {
            // Use the centralized enqueue from ShowAuthority
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

        // --- Access Gate CSS ---
        wp_enqueue_style(
            'gfy-access-gate-admin',
            get_template_directory_uri() . '/css/admin-access-gate.css',
            [],
            filemtime(get_template_directory() . '/css/admin-access-gate.css')
        );

        // --- Access Gate JS (Vue app) ---
        wp_enqueue_script(
            'gfy-access-gate-admin',
            get_template_directory_uri() . '/js/admin-access-gate.js',
            ['vue', 'pinia'],
            filemtime(get_template_directory() . '/js/admin-access-gate.js'),
            true
        );

        // Pass config data to JS
        wp_localize_script('gfy-access-gate-admin', 'gfyAccessGateData', [
            'apiUrl' => rest_url('guestify/v1'),
            'nonce'  => wp_create_nonce('wp_rest'),
        ]);
    }

    /**
     * Render the admin page with nav-tab-wrapper and Vue mount point.
     */
    public static function render_page(): void {
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'rules';
        $valid_tabs = ['rules', 'test'];
        if (!in_array($active_tab, $valid_tabs, true)) {
            $active_tab = 'rules';
        }
        ?>
        <div class="wrap">
            <h1><?php _e('Access Gate', 'guestify'); ?></h1>
            <p class="description"><?php _e('Manage page-level access rules for virtual pages. Control which URL paths require authentication, which membership tier, and where denied users are redirected.', 'guestify'); ?></p>

            <nav class="nav-tab-wrapper">
                <a href="?page=<?php echo esc_attr(self::PAGE_SLUG); ?>&tab=rules"
                   class="nav-tab <?php echo $active_tab === 'rules' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Rules', 'guestify'); ?>
                </a>
                <a href="?page=<?php echo esc_attr(self::PAGE_SLUG); ?>&tab=test"
                   class="nav-tab <?php echo $active_tab === 'test' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Test URL', 'guestify'); ?>
                </a>
            </nav>

            <div id="gfy-app-access-gate" data-tab="<?php echo esc_attr($active_tab); ?>" style="margin-top: 20px;">
                <p><?php _e('Loading...', 'guestify'); ?></p>
            </div>
        </div>
        <?php
    }
}
