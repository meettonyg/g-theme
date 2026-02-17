<?php
/**
 * Template Name: Account Settings
 *
 * The account settings page template for logged-in users.
 * Displays user settings, billing, team, integrations, notifications, and support.
 *
 * @package Guestify
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Redirect non-logged-in users to login
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Get current panel from URL (default to 'general')
$current_panel = isset($_GET['panel']) ? sanitize_key($_GET['panel']) : 'general';
$valid_panels = array('general', 'billing', 'credits', 'team', 'integrations', 'notifications', 'support');
if (!in_array($current_panel, $valid_panels)) {
    $current_panel = 'general';
}

// Get user data
$user_data = guestify_get_account_user_data($user_id);
$billing_data = guestify_get_account_billing_data($user_id);
$usage_data = guestify_get_account_usage_data($user_id);

get_header();
?>

<div class="gfy-account">
    <div class="gfy-container">

        <header class="gfy-page-header">
            <h1 class="gfy-page-title"><?php esc_html_e('Account Settings', 'guestify'); ?></h1>
            <p class="gfy-page-desc"><?php esc_html_e('Manage your profile, preferences, and subscription details.', 'guestify'); ?></p>
        </header>

        <div class="gfy-settings-grid">

            <!-- Settings Sidebar Navigation -->
            <aside class="gfy-settings-nav" role="tablist" aria-label="<?php esc_attr_e('Settings navigation', 'guestify'); ?>">
                <a href="<?php echo esc_url(add_query_arg('panel', 'general', get_permalink())); ?>"
                   class="gfy-settings-link<?php echo $current_panel === 'general' ? ' active' : ''; ?>"
                   role="tab"
                   aria-selected="<?php echo $current_panel === 'general' ? 'true' : 'false'; ?>">
                    <i class="fa-solid fa-user"></i>
                    <span><?php esc_html_e('General', 'guestify'); ?></span>
                </a>
                <a href="<?php echo esc_url(add_query_arg('panel', 'billing', get_permalink())); ?>"
                   class="gfy-settings-link<?php echo $current_panel === 'billing' ? ' active' : ''; ?>"
                   role="tab"
                   aria-selected="<?php echo $current_panel === 'billing' ? 'true' : 'false'; ?>">
                    <i class="fa-solid fa-credit-card"></i>
                    <span><?php esc_html_e('Billing & Plan', 'guestify'); ?></span>
                </a>
                <a href="<?php echo esc_url(add_query_arg('panel', 'credits', get_permalink())); ?>"
                   class="gfy-settings-link<?php echo $current_panel === 'credits' ? ' active' : ''; ?>"
                   role="tab"
                   aria-selected="<?php echo $current_panel === 'credits' ? 'true' : 'false'; ?>">
                    <i class="fa-solid fa-coins"></i>
                    <span><?php esc_html_e('Credits & Usage', 'guestify'); ?></span>
                </a>
                <a href="<?php echo esc_url(add_query_arg('panel', 'team', get_permalink())); ?>"
                   class="gfy-settings-link<?php echo $current_panel === 'team' ? ' active' : ''; ?>"
                   role="tab"
                   aria-selected="<?php echo $current_panel === 'team' ? 'true' : 'false'; ?>">
                    <i class="fa-solid fa-users"></i>
                    <span><?php esc_html_e('Team Members', 'guestify'); ?></span>
                </a>
                <a href="<?php echo esc_url(add_query_arg('panel', 'integrations', get_permalink())); ?>"
                   class="gfy-settings-link<?php echo $current_panel === 'integrations' ? ' active' : ''; ?>"
                   role="tab"
                   aria-selected="<?php echo $current_panel === 'integrations' ? 'true' : 'false'; ?>">
                    <i class="fa-solid fa-plug"></i>
                    <span><?php esc_html_e('Integrations', 'guestify'); ?></span>
                </a>
                <a href="<?php echo esc_url(add_query_arg('panel', 'notifications', get_permalink())); ?>"
                   class="gfy-settings-link<?php echo $current_panel === 'notifications' ? ' active' : ''; ?>"
                   role="tab"
                   aria-selected="<?php echo $current_panel === 'notifications' ? 'true' : 'false'; ?>">
                    <i class="fa-solid fa-bell"></i>
                    <span><?php esc_html_e('Notifications', 'guestify'); ?></span>
                </a>
                <a href="<?php echo esc_url(add_query_arg('panel', 'support', get_permalink())); ?>"
                   class="gfy-settings-link<?php echo $current_panel === 'support' ? ' active' : ''; ?>"
                   role="tab"
                   aria-selected="<?php echo $current_panel === 'support' ? 'true' : 'false'; ?>">
                    <i class="fa-solid fa-headset"></i>
                    <span><?php esc_html_e('Support', 'guestify'); ?></span>
                </a>
            </aside>

            <!-- Settings Content Panels -->
            <section class="gfy-settings-content">
                <?php
                // Load the appropriate panel
                get_template_part('template-parts/account/panel', $current_panel, array(
                    'user_data'    => $user_data,
                    'billing_data' => $billing_data,
                    'usage_data'   => $usage_data,
                    'user_id'      => $user_id,
                ));
                ?>
            </section>

        </div>
    </div>
</div>

<?php get_footer(); ?>
