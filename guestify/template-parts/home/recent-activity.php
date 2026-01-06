<?php
/**
 * Template Part: Home Recent Activity
 *
 * Displays the recent activity list (Jump Back In section).
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
$activities = isset($data['recent_activity']) ? $data['recent_activity'] : array();

// Default activities if none exist (static fallback)
if (empty($activities)) {
    $activities = array(
        array(
            'type'     => 'draft',
            'icon'     => 'fa-solid fa-pen-to-square',
            'title'    => 'Get Started',
            'subtitle' => 'Complete your profile to unlock all features',
            'url'      => home_url('/app/profiles/'),
        ),
    );
}

/**
 * Get relative time string
 */
if (!function_exists('gfy_get_relative_time')) {
    function gfy_get_relative_time($timestamp) {
        $now = time();
        $diff = $now - strtotime($timestamp);

        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . ' min' . ($mins > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 172800) {
            return 'Yesterday';
        } else {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        }
    }
}
?>

<div class="gfy-home__recent-list">
    <?php if (empty($activities)): ?>
    <div class="gfy-home__recent-empty">
        <i class="fa-solid fa-clock-rotate-left"></i>
        <p>No recent activity yet. Start by exploring the tools above!</p>
    </div>
    <?php else: ?>
        <?php foreach (array_slice($activities, 0, 5) as $activity):
            $type = isset($activity['type']) ? $activity['type'] : 'default';
            $icon = isset($activity['icon']) ? $activity['icon'] : 'fa-solid fa-file';
            $title = isset($activity['title']) ? $activity['title'] : 'Untitled';
            $subtitle = isset($activity['subtitle']) ? $activity['subtitle'] : '';
            $url = isset($activity['url']) ? $activity['url'] : '#';
        ?>
        <a href="<?php echo esc_url($url); ?>" class="gfy-home__recent-item" data-activity-id="<?php echo esc_attr(isset($activity['id']) ? $activity['id'] : ''); ?>">
            <div class="gfy-home__recent-icon gfy-home__recent-icon--<?php echo esc_attr($type); ?>">
                <i class="<?php echo esc_attr($icon); ?>"></i>
            </div>
            <div class="gfy-home__recent-info">
                <h4><?php echo esc_html($title); ?></h4>
                <p><?php echo esc_html($subtitle); ?></p>
            </div>
            <div class="gfy-home__recent-arrow">
                <i class="fa-solid fa-chevron-right"></i>
            </div>
        </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
