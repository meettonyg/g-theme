<?php
/**
 * Vue.js Script Registration Helper
 *
 * Centralized registration of Vue.js, Vue-Demi, and Pinia scripts.
 * Allows easy switching between CDN and local bundled files.
 *
 * @package Guestify
 * @since   2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GFY_Vue_Scripts {

    /**
     * Whether to use local vendor files instead of CDN
     */
    const USE_LOCAL_VENDOR = false;

    /**
     * Script versions
     */
    const VUE_VERSION = '3.3.4';
    const VUE_DEMI_VERSION = '0.14.6';
    const PINIA_VERSION = '2.1.7';

    /**
     * Register and enqueue Vue.js scripts
     */
    public static function enqueue() {
        // Vue 3
        if (!wp_script_is('vue', 'registered')) {
            wp_register_script(
                'vue',
                self::get_vue_url(),
                [],
                self::VUE_VERSION,
                true
            );
        }
        wp_enqueue_script('vue');

        // Vue Demi (required for Pinia)
        if (!wp_script_is('vue-demi', 'registered')) {
            wp_register_script(
                'vue-demi',
                self::get_vue_demi_url(),
                ['vue'],
                self::VUE_DEMI_VERSION,
                true
            );
        }
        wp_enqueue_script('vue-demi');

        // Pinia
        if (!wp_script_is('pinia', 'registered')) {
            wp_register_script(
                'pinia',
                self::get_pinia_url(),
                ['vue', 'vue-demi'],
                self::PINIA_VERSION,
                true
            );
        }
        wp_enqueue_script('pinia');

        // Shared API utility
        if (!wp_script_is('guestify-api', 'registered')) {
            $api_path = self::get_shared_js_path('api.js');
            if ($api_path) {
                wp_register_script(
                    'guestify-api',
                    $api_path,
                    [],
                    self::get_version(),
                    true
                );
                wp_enqueue_script('guestify-api');
            }
        } else {
            wp_enqueue_script('guestify-api');
        }

        // Shared formatting utilities
        if (!wp_script_is('pit-formatting', 'registered')) {
            $fmt_path = self::get_shared_js_path('formatting.js');
            if ($fmt_path) {
                wp_register_script(
                    'pit-formatting',
                    $fmt_path,
                    [],
                    self::get_version(),
                    true
                );
                wp_enqueue_script('pit-formatting');
            }
        } else {
            wp_enqueue_script('pit-formatting');
        }

        // Shared download utilities
        if (!wp_script_is('pit-download', 'registered')) {
            $dl_path = self::get_shared_js_path('download.js');
            if ($dl_path) {
                wp_register_script(
                    'pit-download',
                    $dl_path,
                    [],
                    self::get_version(),
                    true
                );
                wp_enqueue_script('pit-download');
            }
        } else {
            wp_enqueue_script('pit-download');
        }

        // Shared debounce utility
        if (!wp_script_is('pit-debounce', 'registered')) {
            $db_path = self::get_shared_js_path('debounce.js');
            if ($db_path) {
                wp_register_script(
                    'pit-debounce',
                    $db_path,
                    [],
                    self::get_version(),
                    true
                );
                wp_enqueue_script('pit-debounce');
            }
        } else {
            wp_enqueue_script('pit-debounce');
        }
    }

    /**
     * Get Vue.js URL (local or CDN)
     */
    private static function get_vue_url() {
        if (self::USE_LOCAL_VENDOR && self::local_file_exists('vue.global.prod.js')) {
            return get_template_directory_uri() . '/js/vendor/vue.global.prod.js';
        }
        return 'https://unpkg.com/vue@' . self::VUE_VERSION . '/dist/vue.global.prod.js';
    }

    /**
     * Get Vue-Demi URL (local or CDN)
     */
    private static function get_vue_demi_url() {
        if (self::USE_LOCAL_VENDOR && self::local_file_exists('vue-demi.iife.js')) {
            return get_template_directory_uri() . '/js/vendor/vue-demi.iife.js';
        }
        return 'https://unpkg.com/vue-demi@' . self::VUE_DEMI_VERSION . '/lib/index.iife.js';
    }

    /**
     * Get Pinia URL (local or CDN)
     */
    private static function get_pinia_url() {
        if (self::USE_LOCAL_VENDOR && self::local_file_exists('pinia.iife.js')) {
            return get_template_directory_uri() . '/js/vendor/pinia.iife.js';
        }
        return 'https://unpkg.com/pinia@' . self::PINIA_VERSION . '/dist/pinia.iife.js';
    }

    /**
     * Check if local vendor file exists
     */
    private static function local_file_exists($filename) {
        return file_exists(get_template_directory() . '/js/vendor/' . $filename);
    }

    /**
     * Get shared JS path — checks plugin first, then theme
     */
    private static function get_shared_js_path($filename) {
        // If ShowAuthority is active, use its shared JS
        if (defined('PIT_PLUGIN_URL') && defined('PIT_PLUGIN_DIR')) {
            if (file_exists(PIT_PLUGIN_DIR . 'assets/js/shared/' . $filename)) {
                return PIT_PLUGIN_URL . 'assets/js/shared/' . $filename;
            }
        }

        // Fallback: check theme
        $theme_path = get_template_directory() . '/js/shared/' . $filename;
        if (file_exists($theme_path)) {
            return get_template_directory_uri() . '/js/shared/' . $filename;
        }

        return null;
    }

    /**
     * Get version for cache busting
     */
    private static function get_version() {
        if (defined('PIT_VERSION')) {
            return PIT_VERSION;
        }
        return wp_get_theme()->get('Version') ?: '1.0.0';
    }

    /**
     * Get script dependencies for custom Vue apps
     */
    public static function get_dependencies() {
        return ['vue', 'pinia', 'guestify-api', 'pit-formatting', 'pit-download', 'pit-debounce'];
    }
}
