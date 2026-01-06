<?php
/**
 * Template Part: Account General Panel
 *
 * Displays the general/profile settings panel.
 *
 * @package Guestify
 * @version 1.0.0
 *
 * @param array $args['user_data'] User data array
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$user_data = isset($args['user_data']) ? $args['user_data'] : array();
$avatar_url = isset($user_data['avatar_url']) ? $user_data['avatar_url'] : '';
$initials = isset($user_data['initials']) ? $user_data['initials'] : 'U';
?>

<div id="general" class="gfy-panel" role="tabpanel">
    <div class="gfy-card">
        <div class="gfy-card__header">
            <div>
                <h2 class="gfy-card__title"><?php esc_html_e('Personal Information', 'guestify'); ?></h2>
                <p class="gfy-card__desc"><?php esc_html_e('Used for your Media Kit and Outreach sender profile.', 'guestify'); ?></p>
            </div>
        </div>
        <div class="gfy-card__body">
            <form id="profile-form" class="gfy-account-form" data-endpoint="profile">
                <?php wp_nonce_field('guestify_account_profile', 'profile_nonce'); ?>

                <!-- Avatar Upload -->
                <div class="gfy-avatar-upload">
                    <?php if ($avatar_url): ?>
                        <div class="gfy-avatar gfy-avatar--lg">
                            <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php esc_attr_e('Profile Avatar', 'guestify'); ?>">
                        </div>
                    <?php else: ?>
                        <div class="gfy-avatar gfy-avatar--lg gfy-avatar--upload-placeholder">
                            <i class="fa-solid fa-user"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <label for="avatar-upload" class="gfy-btn gfy-btn--secondary" style="cursor: pointer;">
                            <i class="fa-solid fa-upload"></i>
                            <?php esc_html_e('Change Avatar', 'guestify'); ?>
                        </label>
                        <input type="file" id="avatar-upload" name="avatar" accept="image/jpeg,image/png,image/gif" style="display: none;">
                        <p class="gfy-helper-text"><?php esc_html_e('JPG, GIF or PNG. Max size of 800K.', 'guestify'); ?></p>
                    </div>
                </div>

                <div class="gfy-form-grid">
                    <div class="gfy-form-group">
                        <label class="gfy-label" for="firstName"><?php esc_html_e('First name', 'guestify'); ?></label>
                        <input type="text"
                               id="firstName"
                               name="first_name"
                               class="gfy-input"
                               value="<?php echo esc_attr($user_data['first_name'] ?? ''); ?>">
                    </div>
                    <div class="gfy-form-group">
                        <label class="gfy-label" for="lastName"><?php esc_html_e('Last name', 'guestify'); ?></label>
                        <input type="text"
                               id="lastName"
                               name="last_name"
                               class="gfy-input"
                               value="<?php echo esc_attr($user_data['last_name'] ?? ''); ?>">
                    </div>
                    <div class="gfy-form-group gfy-form-group--full">
                        <label class="gfy-label" for="email"><?php esc_html_e('Email address', 'guestify'); ?></label>
                        <input type="email"
                               id="email"
                               name="email"
                               class="gfy-input"
                               value="<?php echo esc_attr($user_data['email'] ?? ''); ?>">
                    </div>
                    <div class="gfy-form-group gfy-form-group--full">
                        <label class="gfy-label" for="jobTitle"><?php esc_html_e('Job title', 'guestify'); ?></label>
                        <input type="text"
                               id="jobTitle"
                               name="job_title"
                               class="gfy-input"
                               value="<?php echo esc_attr($user_data['job_title'] ?? ''); ?>">
                    </div>
                </div>
            </form>
        </div>
        <div class="gfy-card__footer">
            <button type="button" class="gfy-btn gfy-btn--secondary"><?php esc_html_e('Cancel', 'guestify'); ?></button>
            <button type="submit" form="profile-form" class="gfy-btn gfy-btn--primary">
                <i class="fa-solid fa-check"></i>
                <?php esc_html_e('Save Changes', 'guestify'); ?>
            </button>
        </div>
    </div>
</div>
