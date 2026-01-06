<?php
/**
 * Template Part: Dashboard Outcomes
 *
 * Displays the 3 outcome cards (Connect, Capture, Collaborate).
 *
 * @package Guestify
 * @version 1.0.0
 *
 * @param array $args['data'] Outcomes data array
 * @param string $args['current_goal'] Current goal (revenue, authority, launch)
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$data = isset($args['data']) ? $args['data'] : array();
$current_goal = isset($args['current_goal']) ? $args['current_goal'] : 'revenue';

// Format functions
if (!function_exists('gfy_format_currency')) {
    function gfy_format_currency($amount) {
        if ($amount >= 1000) {
            return '$' . number_format($amount / 1000, 1) . 'k';
        }
        return '$' . number_format($amount);
    }
}

if (!function_exists('gfy_format_audience')) {
    function gfy_format_audience($num) {
        if ($num >= 1000000) {
            return number_format($num / 1000000, 1) . 'M';
        }
        if ($num >= 1000) {
            return number_format($num / 1000, 0) . 'K';
        }
        return number_format($num);
    }
}
?>

<section class="gfy-outcomes-grid">

    <!-- CAPTURE (Revenue) -->
    <div id="card-revenue" class="gfy-outcome-card card-capture<?php echo $current_goal === 'revenue' ? ' highlight' : ''; ?>">
        <div class="gfy-outcome-icon-box">
            <i class="fa-solid fa-sack-dollar"></i>
        </div>
        <div>
            <div class="gfy-outcome-title"><?php esc_html_e('Capture (Revenue)', 'guestify'); ?></div>
            <div class="gfy-outcome-val"><?php echo esc_html(gfy_format_currency($data['revenue'] ?? 0)); ?></div>
            <?php if (!empty($data['revenue_change'])): ?>
            <div class="gfy-outcome-sub gfy-outcome-sub--success">
                <?php echo esc_html($data['revenue_change']); ?>
            </div>
            <?php else: ?>
            <div class="gfy-outcome-sub"><?php esc_html_e('This period', 'guestify'); ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- CONNECT (Reach/Authority) -->
    <div id="card-authority" class="gfy-outcome-card card-connect<?php echo $current_goal === 'authority' ? ' highlight' : ''; ?>">
        <div class="gfy-outcome-icon-box">
            <i class="fa-solid fa-users-viewfinder"></i>
        </div>
        <div>
            <div class="gfy-outcome-title"><?php esc_html_e('Connect (Reach)', 'guestify'); ?></div>
            <div class="gfy-outcome-val"><?php echo esc_html(gfy_format_audience($data['audience'] ?? 0)); ?></div>
            <div class="gfy-outcome-sub"><?php esc_html_e('Total Audience', 'guestify'); ?></div>
        </div>
    </div>

    <!-- COLLABORATE (Deals/Launch) -->
    <div id="card-launch" class="gfy-outcome-card card-collab<?php echo $current_goal === 'launch' ? ' highlight' : ''; ?>">
        <div class="gfy-outcome-icon-box">
            <i class="fa-solid fa-handshake"></i>
        </div>
        <div>
            <div class="gfy-outcome-title"><?php esc_html_e('Collaborate (Deals)', 'guestify'); ?></div>
            <div class="gfy-outcome-val"><?php echo esc_html($data['partners'] ?? 0); ?></div>
            <div class="gfy-outcome-sub"><?php esc_html_e('Active Partners', 'guestify'); ?></div>
        </div>
    </div>

</section>
