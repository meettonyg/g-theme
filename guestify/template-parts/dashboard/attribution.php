<?php
/**
 * Template Part: Dashboard Attribution Table
 *
 * Displays the revenue attribution table.
 *
 * @package Guestify
 * @version 1.0.0
 *
 * @param array $args['data'] Attribution data array
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$data = isset($args['data']) ? $args['data'] : array();
?>

<div class="gfy-card">
    <div class="gfy-card-header">
        <div class="gfy-card-title"><?php esc_html_e('Revenue Attribution', 'guestify'); ?></div>
        <a href="<?php echo esc_url(home_url('/app/analytics/')); ?>" class="gfy-btn gfy-btn-outline gfy-btn-sm">
            <?php esc_html_e('View All', 'guestify'); ?>
        </a>
    </div>

    <?php if (!empty($data)): ?>
    <table class="gfy-table">
        <thead>
            <tr>
                <th><?php esc_html_e('Link Source', 'guestify'); ?></th>
                <th><?php esc_html_e('Clicks', 'guestify'); ?></th>
                <th><?php esc_html_e('Leads', 'guestify'); ?></th>
                <th><?php esc_html_e('Revenue', 'guestify'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row): ?>
            <tr>
                <td>
                    <div class="gfy-table__name"><?php echo esc_html($row['name']); ?></div>
                    <?php if (!empty($row['link'])): ?>
                    <span class="gfy-link-cell"><?php echo esc_html($row['link']); ?></span>
                    <?php endif; ?>
                </td>
                <td><?php echo esc_html(number_format($row['clicks'] ?? 0)); ?></td>
                <td><?php echo esc_html(number_format($row['leads'] ?? 0)); ?></td>
                <td class="gfy-table__revenue">
                    $<?php echo esc_html(number_format($row['revenue'] ?? 0)); ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="gfy-empty-state">
        <div class="gfy-empty-state__icon">
            <i class="fa-solid fa-chart-line"></i>
        </div>
        <div class="gfy-empty-state__title"><?php esc_html_e('No Attribution Data Yet', 'guestify'); ?></div>
        <p><?php esc_html_e('Start tracking your podcast appearances to see revenue attribution.', 'guestify'); ?></p>
    </div>
    <?php endif; ?>
</div>
