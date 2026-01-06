<?php
/**
 * Template Part: Account Support Panel
 *
 * Displays the support/contact settings panel.
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
?>

<div id="support" class="gfy-panel" role="tabpanel">
    <div class="gfy-card">
        <div class="gfy-card__header">
            <div>
                <h2 class="gfy-card__title">
                    <i class="fa-solid fa-comment-dots" style="color: var(--gfy-primary-800);"></i>
                    <?php esc_html_e('Contact Guestify', 'guestify'); ?>
                </h2>
            </div>
        </div>
        <div class="gfy-card__body">
            <div class="gfy-support-layout">
                <!-- Contact Form (Left) -->
                <form class="gfy-support-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                    <?php wp_nonce_field('guestify_support_contact', 'support_nonce'); ?>
                    <input type="hidden" name="action" value="guestify_support_contact">

                    <div class="gfy-form-group">
                        <label class="gfy-label" for="support-name"><?php esc_html_e('Name', 'guestify'); ?></label>
                        <input type="text"
                               id="support-name"
                               name="name"
                               class="gfy-input"
                               placeholder="<?php esc_attr_e('Your name', 'guestify'); ?>"
                               value="<?php echo esc_attr($user_data['display_name'] ?? ''); ?>"
                               required>
                    </div>
                    <div class="gfy-form-group">
                        <label class="gfy-label" for="support-email"><?php esc_html_e('Email', 'guestify'); ?></label>
                        <input type="email"
                               id="support-email"
                               name="email"
                               class="gfy-input"
                               placeholder="<?php esc_attr_e('your@email.com', 'guestify'); ?>"
                               value="<?php echo esc_attr($user_data['email'] ?? ''); ?>"
                               required>
                    </div>
                    <div class="gfy-form-group">
                        <label class="gfy-label" for="support-subject"><?php esc_html_e('Subject', 'guestify'); ?></label>
                        <input type="text"
                               id="support-subject"
                               name="subject"
                               class="gfy-input"
                               placeholder="<?php esc_attr_e('How can we help?', 'guestify'); ?>"
                               required>
                    </div>
                    <div class="gfy-form-group">
                        <label class="gfy-label" for="support-message"><?php esc_html_e('Message', 'guestify'); ?></label>
                        <textarea id="support-message"
                                  name="message"
                                  class="gfy-input gfy-textarea"
                                  rows="5"
                                  placeholder="<?php esc_attr_e('Tell us more...', 'guestify'); ?>"
                                  required></textarea>
                    </div>
                    <button type="submit" class="gfy-btn gfy-btn--primary">
                        <i class="fa-solid fa-paper-plane"></i>
                        <?php esc_html_e('Send Message', 'guestify'); ?>
                    </button>
                </form>

                <!-- Support Resources (Right) -->
                <div class="gfy-support-resources">
                    <h3 class="gfy-support-resources__title"><?php esc_html_e('Support Resources', 'guestify'); ?></h3>
                    <div class="gfy-resource-list">
                        <a href="<?php echo esc_url(home_url('/help/')); ?>" class="gfy-resource-item">
                            <div class="gfy-resource-item__icon gfy-resource-item__icon--red">
                                <i class="fa-solid fa-circle-question"></i>
                            </div>
                            <span><?php esc_html_e('Help Center & FAQs', 'guestify'); ?></span>
                        </a>
                        <a href="<?php echo esc_url(home_url('/tutorials/')); ?>" class="gfy-resource-item">
                            <div class="gfy-resource-item__icon gfy-resource-item__icon--teal">
                                <i class="fa-solid fa-play"></i>
                            </div>
                            <span><?php esc_html_e('Video Tutorials', 'guestify'); ?></span>
                        </a>
                        <a href="<?php echo esc_url(home_url('/docs/')); ?>" class="gfy-resource-item">
                            <div class="gfy-resource-item__icon gfy-resource-item__icon--green">
                                <i class="fa-solid fa-chart-simple"></i>
                            </div>
                            <span><?php esc_html_e('Documentation', 'guestify'); ?></span>
                        </a>
                        <a href="<?php echo esc_url(home_url('/community/')); ?>" class="gfy-resource-item">
                            <div class="gfy-resource-item__icon gfy-resource-item__icon--purple">
                                <i class="fa-solid fa-users"></i>
                            </div>
                            <span><?php esc_html_e('Community Forum', 'guestify'); ?></span>
                        </a>
                        <a href="<?php echo esc_url(home_url('/demo/')); ?>" class="gfy-resource-item">
                            <div class="gfy-resource-item__icon gfy-resource-item__icon--pink">
                                <i class="fa-solid fa-calendar"></i>
                            </div>
                            <span><?php esc_html_e('Book a Demo Call', 'guestify'); ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
