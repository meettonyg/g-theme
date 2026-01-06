<?php
/**
 * Template Part: Dashboard Pipeline
 *
 * Displays the 5-step delivery pipeline.
 *
 * @package Guestify
 * @version 1.0.0
 *
 * @param array $args['data'] Pipeline data array
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$data = isset($args['data']) ? $args['data'] : array();

// Pipeline steps configuration
$steps = array(
    array(
        'id'        => 'discovery',
        'class'     => 'step-found',
        'icon'      => 'fa-magnifying-glass',
        'label'     => __('Discovery', 'guestify'),
        'value_key' => 'shows_found',
        'desc'      => __('Shows Found', 'guestify'),
        'rate_key'  => 'vetted_rate',
        'rate_suffix' => __('Vetted', 'guestify'),
    ),
    array(
        'id'        => 'intel',
        'class'     => 'step-research',
        'icon'      => 'fa-flask',
        'label'     => __('Intel', 'guestify'),
        'value_key' => 'shows_researched',
        'desc'      => __('Shows Researched', 'guestify'),
        'rate_key'  => 'pitched_rate',
        'rate_suffix' => __('Pitched', 'guestify'),
    ),
    array(
        'id'        => 'action',
        'class'     => 'step-pitch',
        'icon'      => 'fa-paper-plane',
        'label'     => __('Action', 'guestify'),
        'value_key' => 'pitches_sent',
        'desc'      => __('Pitches Sent', 'guestify'),
        'rate_key'  => 'booked_rate',
        'rate_suffix' => __('Booked', 'guestify'),
        'actionable' => true,
    ),
    array(
        'id'        => 'result',
        'class'     => 'step-book',
        'icon'      => 'fa-calendar-check',
        'label'     => __('Result', 'guestify'),
        'value_key' => 'interviews_booked',
        'desc'      => __('Interviews Booked', 'guestify'),
        'rate_key'  => 'aired_rate',
        'rate_suffix' => __('Aired', 'guestify'),
    ),
    array(
        'id'        => 'success',
        'class'     => 'step-air',
        'icon'      => 'fa-microphone-lines',
        'label'     => __('Success', 'guestify'),
        'value_key' => 'episodes_aired',
        'desc'      => __('Episodes Aired', 'guestify'),
    ),
);
?>

<section class="gfy-pipeline-grid">
    <?php foreach ($steps as $step): ?>
    <div class="gfy-pipe-card <?php echo esc_attr($step['class']); ?><?php echo !empty($step['actionable']) ? ' actionable' : ''; ?>"
         data-step="<?php echo esc_attr($step['id']); ?>">

        <?php if (!empty($step['rate_key']) && isset($data[$step['rate_key']])): ?>
        <div class="gfy-rate-badge">
            <?php echo esc_html($data[$step['rate_key']]); ?>% <?php echo esc_html($step['rate_suffix']); ?>
        </div>
        <?php endif; ?>

        <div class="gfy-pipe-top">
            <div class="gfy-pipe-icon">
                <i class="fa-solid <?php echo esc_attr($step['icon']); ?>"></i>
            </div>
            <?php echo esc_html($step['label']); ?>
        </div>

        <div>
            <div class="gfy-pipe-val">
                <?php echo esc_html(number_format($data[$step['value_key']] ?? 0)); ?>
            </div>
            <div class="gfy-pipe-lbl"><?php echo esc_html($step['desc']); ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</section>
