<?php
/**
 * Template Part: Home Quick Stats Bar
 *
 * Displays the quick stats bar with key metrics from all plugins.
 *
 * @package Guestify
 * @version 1.0.0
 *
 * @param array $args['data'] Dashboard data array
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$data = isset($args['data']) ? $args['data'] : array();
$stats = isset($data['stats']) ? $data['stats'] : array();

// Default values with fallbacks
$pitches = isset($stats['pitches']) ? $stats['pitches'] : 0;
$interviews = isset($stats['interviews']) ? $stats['interviews'] : 0;
$episodes = isset($stats['episodes']) ? $stats['episodes'] : 0;
$revenue = isset($stats['revenue']) ? $stats['revenue'] : 0;

/**
 * Format large numbers with K/M suffix
 */
function gfy_format_stat_number($num, $is_currency = false) {
    if ($num >= 1000000) {
        return ($is_currency ? '$' : '') . number_format($num / 1000000, 1) . 'M';
    }
    if ($num >= 1000) {
        return ($is_currency ? '$' : '') . number_format($num / 1000, 1) . 'K';
    }
    return ($is_currency ? '$' : '') . number_format($num);
}
?>

<section class="gfy-home__stats">
    <div class="gfy-home__stat" data-stat="pitches">
        <div class="gfy-home__stat-icon gfy-home__stat-icon--info">
            <i class="fa-solid fa-paper-plane"></i>
        </div>
        <div class="gfy-home__stat-content">
            <div class="gfy-home__stat-value"><?php echo esc_html($pitches); ?></div>
            <div class="gfy-home__stat-label">Pitches This Month</div>
        </div>
    </div>

    <div class="gfy-home__stat" data-stat="interviews">
        <div class="gfy-home__stat-icon gfy-home__stat-icon--success">
            <i class="fa-solid fa-calendar-check"></i>
        </div>
        <div class="gfy-home__stat-content">
            <div class="gfy-home__stat-value"><?php echo esc_html($interviews); ?></div>
            <div class="gfy-home__stat-label">Interviews Booked</div>
        </div>
    </div>

    <div class="gfy-home__stat" data-stat="episodes">
        <div class="gfy-home__stat-icon gfy-home__stat-icon--purple">
            <i class="fa-solid fa-podcast"></i>
        </div>
        <div class="gfy-home__stat-content">
            <div class="gfy-home__stat-value"><?php echo esc_html($episodes); ?></div>
            <div class="gfy-home__stat-label">Episodes Aired</div>
        </div>
    </div>

    <div class="gfy-home__stat" data-stat="revenue">
        <div class="gfy-home__stat-icon gfy-home__stat-icon--success">
            <i class="fa-solid fa-dollar-sign"></i>
        </div>
        <div class="gfy-home__stat-content">
            <div class="gfy-home__stat-value"><?php echo esc_html(gfy_format_stat_number($revenue, true)); ?></div>
            <div class="gfy-home__stat-label">Revenue Tracked</div>
        </div>
    </div>
</section>
