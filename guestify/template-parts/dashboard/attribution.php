<?php
/**
 * Template Part: Dashboard Attribution Table
 *
 * Displays revenue summary, revenue-by-show breakdown, and link tracking table.
 *
 * @package Guestify
 * @version 2.0.0
 *
 * @param array $args['data'] Attribution data array with keys:
 *   - 'links'           => Link tracking rows (legacy)
 *   - 'revenue_summary' => Revenue totals from PIT_Revenue_Attribution
 *   - 'revenue_by_show' => Per-podcast revenue breakdown
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$data = isset($args['data']) ? $args['data'] : array();

// Separate link tracking (legacy) from revenue intelligence data
$links = isset($data['links']) ? $data['links'] : $data;
$revenue_summary = isset($data['revenue_summary']) ? $data['revenue_summary'] : array();
$revenue_by_show = isset($data['revenue_by_show']) ? $data['revenue_by_show'] : array();
?>

<?php if (!empty($revenue_summary)): ?>
<!-- REVENUE SUMMARY CARDS -->
<div class="gfy-card" style="margin-bottom: 1.5rem;">
    <div class="gfy-card-header">
        <div class="gfy-card-title"><?php esc_html_e('Revenue Overview', 'guestify'); ?></div>
    </div>
    <div class="gfy-attribution__summary">
        <div class="gfy-attribution__metric">
            <span class="gfy-attribution__metric-value">$<?php echo esc_html(number_format($revenue_summary['total_actual'] ?? 0)); ?></span>
            <span class="gfy-attribution__metric-label"><?php esc_html_e('Actual Revenue', 'guestify'); ?></span>
        </div>
        <div class="gfy-attribution__metric">
            <span class="gfy-attribution__metric-value">$<?php echo esc_html(number_format($revenue_summary['total_pipeline'] ?? 0)); ?></span>
            <span class="gfy-attribution__metric-label"><?php esc_html_e('Pipeline Value', 'guestify'); ?></span>
        </div>
        <div class="gfy-attribution__metric">
            <span class="gfy-attribution__metric-value">$<?php echo esc_html(number_format($revenue_summary['total_commission'] ?? 0)); ?></span>
            <span class="gfy-attribution__metric-label"><?php esc_html_e('Commission', 'guestify'); ?></span>
        </div>
        <?php if (!empty($revenue_summary['roi_percentage'])): ?>
        <div class="gfy-attribution__metric">
            <span class="gfy-attribution__metric-value"><?php echo esc_html(round($revenue_summary['roi_percentage'])); ?>%</span>
            <span class="gfy-attribution__metric-label"><?php esc_html_e('ROI', 'guestify'); ?></span>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- REVENUE BY SHOW -->
<?php if (!empty($revenue_by_show)): ?>
<div class="gfy-card" style="margin-bottom: 1.5rem;">
    <div class="gfy-card-header">
        <div class="gfy-card-title"><?php esc_html_e('Revenue by Show', 'guestify'); ?></div>
    </div>
    <table class="gfy-table">
        <thead>
            <tr>
                <th><?php esc_html_e('Podcast', 'guestify'); ?></th>
                <th><?php esc_html_e('Appearances', 'guestify'); ?></th>
                <th><?php esc_html_e('Estimated', 'guestify'); ?></th>
                <th><?php esc_html_e('Actual', 'guestify'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($revenue_by_show as $show): ?>
            <tr>
                <td>
                    <div class="gfy-table__name"><?php echo esc_html($show['podcast_title'] ?? 'Unknown'); ?></div>
                </td>
                <td><?php echo esc_html($show['appearance_count'] ?? 0); ?></td>
                <td>$<?php echo esc_html(number_format($show['total_estimated'] ?? 0)); ?></td>
                <td class="gfy-table__revenue">$<?php echo esc_html(number_format($show['total_actual'] ?? 0)); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- LINK TRACKING TABLE -->
<div class="gfy-card">
    <div class="gfy-card-header">
        <div class="gfy-card-title"><?php esc_html_e('Revenue Attribution', 'guestify'); ?></div>
        <a href="<?php echo esc_url(home_url('/app/analytics/')); ?>" class="gfy-btn gfy-btn-outline gfy-btn-sm">
            <?php esc_html_e('View All', 'guestify'); ?>
        </a>
    </div>

    <?php if (!empty($links) && is_array($links)): ?>
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
            <?php foreach ($links as $row): ?>
            <?php if (!is_array($row)) continue; ?>
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
