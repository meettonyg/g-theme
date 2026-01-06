<?php
/**
 * Template Part: Account Integrations Panel
 *
 * Displays the integrations settings panel.
 *
 * @package Guestify
 * @version 1.0.0
 *
 * @param array $args['user_id'] User ID
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$user_id = isset($args['user_id']) ? $args['user_id'] : get_current_user_id();

// Define available integrations
$integrations = array(
    array(
        'id'          => 'hubspot',
        'name'        => 'HubSpot',
        'description' => __('Sync contacts and deals automatically.', 'guestify'),
        'icon'        => 'fa-solid fa-h',
        'color'       => '#FF7A59',
        'connected'   => !empty(get_user_meta($user_id, 'guestify_hubspot_token', true)),
    ),
    array(
        'id'          => 'slack',
        'name'        => 'Slack',
        'description' => __('Get notified of new bookings and messages.', 'guestify'),
        'icon'        => 'fa-brands fa-slack',
        'color'       => '#4A154B',
        'connected'   => !empty(get_user_meta($user_id, 'guestify_slack_token', true)),
    ),
    array(
        'id'          => 'spotify',
        'name'        => 'Spotify for Podcasters',
        'description' => __('Import your podcast analytics.', 'guestify'),
        'icon'        => 'fa-brands fa-spotify',
        'color'       => '#1DB954',
        'connected'   => !empty(get_user_meta($user_id, 'guestify_spotify_token', true)),
    ),
    array(
        'id'          => 'linkedin',
        'name'        => 'LinkedIn',
        'description' => __('Share bookings to your professional network.', 'guestify'),
        'icon'        => 'fa-brands fa-linkedin',
        'color'       => '#0077B5',
        'connected'   => !empty(get_user_meta($user_id, 'guestify_linkedin_token', true)),
    ),
);

// Get API key
$api_key = get_user_meta($user_id, 'guestify_api_key', true);
if (empty($api_key)) {
    $api_key = 'sk_live_' . wp_generate_password(32, false);
    update_user_meta($user_id, 'guestify_api_key', $api_key);
}
// Mask the key for display
$api_key_masked = substr($api_key, 0, 10) . str_repeat('*', 20) . substr($api_key, -4);
?>

<div id="integrations" class="gfy-panel" role="tabpanel">
    <div class="gfy-card">
        <div class="gfy-card__header">
            <div>
                <h2 class="gfy-card__title"><?php esc_html_e('Connected Apps', 'guestify'); ?></h2>
                <p class="gfy-card__desc"><?php esc_html_e('Supercharge Guestify with third-party tools.', 'guestify'); ?></p>
            </div>
        </div>
        <div class="gfy-card__body">
            <?php foreach ($integrations as $integration): ?>
            <div class="gfy-integration">
                <div class="gfy-integration__info">
                    <div class="gfy-integration__icon" style="color: <?php echo esc_attr($integration['color']); ?>;">
                        <i class="<?php echo esc_attr($integration['icon']); ?>"></i>
                    </div>
                    <div>
                        <div class="gfy-integration__name"><?php echo esc_html($integration['name']); ?></div>
                        <div class="gfy-integration__desc"><?php echo esc_html($integration['description']); ?></div>
                    </div>
                </div>
                <?php if ($integration['connected']): ?>
                <button class="gfy-btn gfy-btn--success"
                        data-integration="<?php echo esc_attr($integration['id']); ?>"
                        data-action="disconnect">
                    <i class="fa-solid fa-check"></i>
                    <?php esc_html_e('Connected', 'guestify'); ?>
                </button>
                <?php else: ?>
                <button class="gfy-btn gfy-btn--secondary"
                        data-integration="<?php echo esc_attr($integration['id']); ?>"
                        data-action="connect">
                    <?php esc_html_e('Connect', 'guestify'); ?>
                </button>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="gfy-card">
        <div class="gfy-card__header">
            <div>
                <h2 class="gfy-card__title"><?php esc_html_e('API Access', 'guestify'); ?></h2>
                <p class="gfy-card__desc"><?php esc_html_e('Manage API keys for custom integrations.', 'guestify'); ?></p>
            </div>
        </div>
        <div class="gfy-card__body">
            <div class="gfy-form-group">
                <label class="gfy-label" for="apiKey"><?php esc_html_e('API Key', 'guestify'); ?></label>
                <div style="display: flex; gap: var(--gfy-space-2);">
                    <input type="password"
                           id="apiKey"
                           class="gfy-input"
                           value="<?php echo esc_attr($api_key); ?>"
                           disabled
                           style="flex: 1;">
                    <button class="gfy-btn gfy-btn--secondary" data-action="copy-api-key">
                        <i class="fa-solid fa-copy"></i>
                        <?php esc_html_e('Copy', 'guestify'); ?>
                    </button>
                    <button class="gfy-btn gfy-btn--secondary" data-action="regenerate-api-key">
                        <i class="fa-solid fa-arrows-rotate"></i>
                        <?php esc_html_e('Regenerate', 'guestify'); ?>
                    </button>
                </div>
                <p class="gfy-helper-text"><?php esc_html_e('Keep this key secret. Do not share it publicly.', 'guestify'); ?></p>
            </div>
        </div>
    </div>
</div>
