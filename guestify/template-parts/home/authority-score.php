<?php
/**
 * Template Part: Home Authority Score Widget
 *
 * Displays the user's authority score gauge with 5-dimension breakdown.
 *
 * @package Guestify
 * @version 1.0.0
 *
 * @param array $args['data'] Dashboard data array with 'authority' key
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$data = isset($args['data']) ? $args['data'] : array();
$authority = isset($data['authority']) ? $data['authority'] : array();

// Don't render if no authority data
if (empty($authority) || !isset($authority['overall_score'])) {
    return;
}

$score = (int) ($authority['overall_score'] ?? 0);
$trend = $authority['trend'] ?? 'stable';
$breakdown = $authority['breakdown'] ?? array();

// Determine score color class
$score_class = 'low';
if ($score >= 70) {
    $score_class = 'high';
} elseif ($score >= 40) {
    $score_class = 'medium';
}

// Trend icon
$trend_icon = 'fa-minus';
$trend_class = 'neutral';
if ($trend === 'up') {
    $trend_icon = 'fa-arrow-up';
    $trend_class = 'positive';
} elseif ($trend === 'down') {
    $trend_icon = 'fa-arrow-down';
    $trend_class = 'negative';
}

// Breakdown dimensions with labels and icons
$dimensions = array(
    'appearance_score' => array('label' => 'Appearances', 'icon' => 'fa-podcast'),
    'social_score'     => array('label' => 'Social Reach', 'icon' => 'fa-share-nodes'),
    'profile_score'    => array('label' => 'Profile', 'icon' => 'fa-user-check'),
    'content_score'    => array('label' => 'Content', 'icon' => 'fa-file-lines'),
    'network_score'    => array('label' => 'Network', 'icon' => 'fa-diagram-project'),
);
?>

<section class="gfy-home__authority">
    <h2 class="gfy-home__section-title">
        <i class="fa-solid fa-crown"></i>
        <?php esc_html_e('Authority Score', 'guestify'); ?>
    </h2>

    <div class="gfy-card">
        <div class="gfy-authority__header">
            <!-- Score Gauge -->
            <div class="gfy-authority__gauge gfy-authority__gauge--<?php echo esc_attr($score_class); ?>">
                <span class="gfy-authority__score"><?php echo esc_html($score); ?></span>
                <span class="gfy-authority__max">/100</span>
            </div>
            <div class="gfy-authority__trend gfy-authority__trend--<?php echo esc_attr($trend_class); ?>">
                <i class="fa-solid <?php echo esc_attr($trend_icon); ?>"></i>
                <span><?php echo esc_html(ucfirst($trend)); ?></span>
            </div>
        </div>

        <!-- 5-Dimension Breakdown -->
        <?php if (!empty($breakdown)): ?>
        <div class="gfy-authority__breakdown">
            <?php foreach ($dimensions as $key => $dim): ?>
            <?php $dim_score = isset($breakdown[$key]) ? (int) $breakdown[$key] : 0; ?>
            <div class="gfy-authority__dimension">
                <div class="gfy-authority__dim-header">
                    <i class="fa-solid <?php echo esc_attr($dim['icon']); ?>"></i>
                    <span class="gfy-authority__dim-label"><?php echo esc_html($dim['label']); ?></span>
                    <span class="gfy-authority__dim-value"><?php echo esc_html($dim_score); ?></span>
                </div>
                <div class="gfy-authority__dim-bar">
                    <div class="gfy-authority__dim-fill" style="width: <?php echo esc_attr($dim_score); ?>%;"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <a href="<?php echo esc_url(home_url('/app/dashboard/')); ?>" class="gfy-btn gfy-btn-outline gfy-btn-sm gfy-authority__cta">
            <?php esc_html_e('View Full Dashboard', 'guestify'); ?>
        </a>
    </div>
</section>
