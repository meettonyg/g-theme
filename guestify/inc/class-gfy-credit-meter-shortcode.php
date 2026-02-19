<?php
/**
 * Credit Meter Shortcode
 *
 * Renders the Authority Credits Vue.js widget showing:
 * - Balance gauge (allowance / rollover / overage breakdown)
 * - Transaction feed (recent credit usage)
 * - Upgrade prompts at 80%, 100%, and hard cap thresholds
 * - Credit pack purchase buttons
 *
 * Usage: [guestify_credit_meter]
 * Usage: [guestify_credit_meter view="compact"]
 *
 * @package Guestify
 * @since   2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GFY_Credit_Meter_Shortcode {

    /**
     * Initialize the shortcode
     */
    public static function init() {
        add_shortcode('guestify_credit_meter', [__CLASS__, 'render']);
        add_shortcode('sa_credit_meter', [__CLASS__, 'render']); // Alias
    }

    /**
     * Render the credit meter widget
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public static function render($atts) {
        if (!is_user_logged_in()) {
            return '<div class="pit-credit-meter pit-credit-meter--empty">Please log in to view your credit balance.</div>';
        }

        $atts = shortcode_atts([
            'view'              => 'full',    // 'full', 'compact', 'widget'
            'show_transactions' => 'true',
            'show_actions'      => 'true',
            'show_purchase'     => 'true',
        ], $atts, 'guestify_credit_meter');

        self::enqueue_scripts($atts);

        ob_start();
        ?>
        <div id="credit-meter-app"
             class="pit-credit-meter"
             data-view="<?php echo esc_attr($atts['view']); ?>"
             data-show-transactions="<?php echo esc_attr($atts['show_transactions']); ?>"
             data-show-actions="<?php echo esc_attr($atts['show_actions']); ?>"
             data-show-purchase="<?php echo esc_attr($atts['show_purchase']); ?>">
            <div class="pit-loading">
                <div class="pit-loading-spinner"></div>
                <p>Loading credit meter...</p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Enqueue required scripts and styles
     */
    private static function enqueue_scripts($atts) {
        // Use centralized Vue/Pinia helper
        GFY_Vue_Scripts::enqueue();

        // Credit Meter Vue app — check plugin first, then theme
        $js_url  = self::get_asset_url('js/credit-meter-vue.js');
        $css_url = self::get_asset_url('css/credit-meter.css');
        $version = self::get_version();

        if ($js_url) {
            wp_enqueue_script(
                'pit-credit-meter',
                $js_url,
                GFY_Vue_Scripts::get_dependencies(),
                $version,
                true
            );
        }

        if ($css_url) {
            wp_enqueue_style(
                'pit-credit-meter',
                $css_url,
                [],
                $version
            );
        }

        // Localize script data
        $base_config = self::get_base_config();
        wp_localize_script('pit-credit-meter', 'pitCreditMeterData', array_merge(
            $base_config,
            [
                'pricingUrl' => home_url('/pricing/'),
                'view'       => $atts['view'],
            ]
        ));
    }

    /**
     * Get an asset URL — checks plugin assets first, then theme
     *
     * @param string $relative_path
     * @return string|null
     */
    private static function get_asset_url(string $relative_path): ?string {
        // Check ShowAuthority plugin first
        if (defined('PIT_PLUGIN_URL') && defined('PIT_PLUGIN_DIR')) {
            if (file_exists(PIT_PLUGIN_DIR . 'assets/' . $relative_path)) {
                return PIT_PLUGIN_URL . 'assets/' . $relative_path;
            }
        }

        // Check theme
        $theme_path = get_template_directory() . '/' . $relative_path;
        if (file_exists($theme_path)) {
            return get_template_directory_uri() . '/' . $relative_path;
        }

        return null;
    }

    /**
     * Get base config for localized script data
     *
     * @return array
     */
    private static function get_base_config(): array {
        // Use PIT_Shortcode_Data_Helper if available (ShowAuthority active)
        if (class_exists('PIT_Shortcode_Data_Helper') && method_exists('PIT_Shortcode_Data_Helper', 'get_base_config')) {
            return PIT_Shortcode_Data_Helper::get_base_config();
        }

        // Fallback: minimal config
        return [
            'guestifyApiUrl' => rest_url('guestify/v1'),
            'nonce'          => wp_create_nonce('wp_rest'),
            'userId'         => get_current_user_id(),
        ];
    }

    /**
     * Get version for cache busting
     */
    private static function get_version(): string {
        if (defined('PIT_VERSION')) {
            return PIT_VERSION;
        }
        return wp_get_theme()->get('Version') ?: '1.0.0';
    }
}
