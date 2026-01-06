<?php
/**
 * Template Part: Home Support Cards
 *
 * Displays the community and support cards section.
 *
 * @package Guestify
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get support card content (can be made dynamic via options later)
$workshop_title = 'Weekly Live Workshop';
$workshop_text = 'Join us every Tuesday at 2PM EST for "Mastering the Perfect Pitch".';
$workshop_url = home_url('/workshop/');

$help_title = 'Need Help?';
$help_text = 'Our team is here to help you get your first booking. Book a 1:1 onboarding call.';
$help_url = home_url('/contact/');
?>

<!-- Workshop Banner -->
<div class="gfy-home__support-card gfy-home__support-card--workshop">
    <div class="gfy-home__support-header">
        <div class="gfy-home__support-icon">
            <i class="fa-solid fa-graduation-cap"></i>
        </div>
        <div class="gfy-home__support-title"><?php echo esc_html($workshop_title); ?></div>
    </div>
    <div class="gfy-home__support-text"><?php echo esc_html($workshop_text); ?></div>
    <a href="<?php echo esc_url($workshop_url); ?>" class="gfy-home__text-link">
        Register for free
        <i class="fa-solid fa-arrow-right"></i>
    </a>
</div>

<!-- Support Banner -->
<div class="gfy-home__support-card gfy-home__support-card--help">
    <div class="gfy-home__support-header">
        <div class="gfy-home__support-icon">
            <i class="fa-solid fa-headset"></i>
        </div>
        <div class="gfy-home__support-title"><?php echo esc_html($help_title); ?></div>
    </div>
    <div class="gfy-home__support-text"><?php echo esc_html($help_text); ?></div>
    <a href="<?php echo esc_url($help_url); ?>" class="gfy-home__text-link">
        Contact Support
        <i class="fa-solid fa-arrow-right"></i>
    </a>
</div>
