<?php
/**
 * Template Part: Account Notifications Panel
 *
 * Displays the notification settings panel.
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

// Get notification preferences
$defaults = array(
    'booking_requests'      => true,
    'message_replies'       => true,
    'weekly_digest'         => false,
    'product_updates'       => true,
    'marketing_emails'      => false,
    'desktop_notifications' => true,
    'sound_alerts'          => false,
);

$prefs = get_user_meta($user_id, 'guestify_notification_prefs', true);
$prefs = is_array($prefs) ? array_merge($defaults, $prefs) : $defaults;
?>

<div id="notifications" class="gfy-panel" role="tabpanel">
    <div class="gfy-card">
        <div class="gfy-card__header">
            <div>
                <h2 class="gfy-card__title"><?php esc_html_e('Email Notifications', 'guestify'); ?></h2>
                <p class="gfy-card__desc"><?php esc_html_e('Choose what updates you receive by email.', 'guestify'); ?></p>
            </div>
        </div>
        <div class="gfy-card__body">
            <div class="gfy-notification-row">
                <div>
                    <div class="gfy-notification-row__label"><?php esc_html_e('New booking requests', 'guestify'); ?></div>
                    <div class="gfy-notification-row__desc"><?php esc_html_e('Get notified when someone wants to book you as a guest.', 'guestify'); ?></div>
                </div>
                <label class="gfy-toggle">
                    <input type="checkbox"
                           class="gfy-toggle__input"
                           data-setting="booking_requests"
                           <?php checked($prefs['booking_requests']); ?>>
                    <span class="gfy-toggle__slider"></span>
                </label>
            </div>
            <div class="gfy-notification-row">
                <div>
                    <div class="gfy-notification-row__label"><?php esc_html_e('Message replies', 'guestify'); ?></div>
                    <div class="gfy-notification-row__desc"><?php esc_html_e('Receive emails when hosts reply to your outreach.', 'guestify'); ?></div>
                </div>
                <label class="gfy-toggle">
                    <input type="checkbox"
                           class="gfy-toggle__input"
                           data-setting="message_replies"
                           <?php checked($prefs['message_replies']); ?>>
                    <span class="gfy-toggle__slider"></span>
                </label>
            </div>
            <div class="gfy-notification-row">
                <div>
                    <div class="gfy-notification-row__label"><?php esc_html_e('Weekly digest', 'guestify'); ?></div>
                    <div class="gfy-notification-row__desc"><?php esc_html_e('Summary of your podcast prospecting activity.', 'guestify'); ?></div>
                </div>
                <label class="gfy-toggle">
                    <input type="checkbox"
                           class="gfy-toggle__input"
                           data-setting="weekly_digest"
                           <?php checked($prefs['weekly_digest']); ?>>
                    <span class="gfy-toggle__slider"></span>
                </label>
            </div>
            <div class="gfy-notification-row">
                <div>
                    <div class="gfy-notification-row__label"><?php esc_html_e('Product updates', 'guestify'); ?></div>
                    <div class="gfy-notification-row__desc"><?php esc_html_e('News about new features and improvements.', 'guestify'); ?></div>
                </div>
                <label class="gfy-toggle">
                    <input type="checkbox"
                           class="gfy-toggle__input"
                           data-setting="product_updates"
                           <?php checked($prefs['product_updates']); ?>>
                    <span class="gfy-toggle__slider"></span>
                </label>
            </div>
            <div class="gfy-notification-row">
                <div>
                    <div class="gfy-notification-row__label"><?php esc_html_e('Marketing emails', 'guestify'); ?></div>
                    <div class="gfy-notification-row__desc"><?php esc_html_e('Tips, case studies, and promotional content.', 'guestify'); ?></div>
                </div>
                <label class="gfy-toggle">
                    <input type="checkbox"
                           class="gfy-toggle__input"
                           data-setting="marketing_emails"
                           <?php checked($prefs['marketing_emails']); ?>>
                    <span class="gfy-toggle__slider"></span>
                </label>
            </div>
        </div>
    </div>

    <div class="gfy-card">
        <div class="gfy-card__header">
            <div>
                <h2 class="gfy-card__title"><?php esc_html_e('In-App Notifications', 'guestify'); ?></h2>
                <p class="gfy-card__desc"><?php esc_html_e('Control notifications within the Guestify app.', 'guestify'); ?></p>
            </div>
        </div>
        <div class="gfy-card__body">
            <div class="gfy-notification-row">
                <div>
                    <div class="gfy-notification-row__label"><?php esc_html_e('Desktop notifications', 'guestify'); ?></div>
                    <div class="gfy-notification-row__desc"><?php esc_html_e('Show browser notifications for important updates.', 'guestify'); ?></div>
                </div>
                <label class="gfy-toggle">
                    <input type="checkbox"
                           class="gfy-toggle__input"
                           data-setting="desktop_notifications"
                           <?php checked($prefs['desktop_notifications']); ?>>
                    <span class="gfy-toggle__slider"></span>
                </label>
            </div>
            <div class="gfy-notification-row">
                <div>
                    <div class="gfy-notification-row__label"><?php esc_html_e('Sound alerts', 'guestify'); ?></div>
                    <div class="gfy-notification-row__desc"><?php esc_html_e('Play a sound when you receive new notifications.', 'guestify'); ?></div>
                </div>
                <label class="gfy-toggle">
                    <input type="checkbox"
                           class="gfy-toggle__input"
                           data-setting="sound_alerts"
                           <?php checked($prefs['sound_alerts']); ?>>
                    <span class="gfy-toggle__slider"></span>
                </label>
            </div>
        </div>
    </div>
</div>
