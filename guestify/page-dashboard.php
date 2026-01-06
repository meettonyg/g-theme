<?php
/**
 * Template Name: Performance Dashboard
 *
 * The performance reports dashboard for logged-in users.
 * Displays pipeline activity, outcomes, and revenue attribution.
 *
 * @package Guestify
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Redirect non-logged-in users to login
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Get time period from URL
$time_period = isset($_GET['period']) ? sanitize_key($_GET['period']) : '30days';
$valid_periods = array('30days', '90days', 'ytd', 'all');
if (!in_array($time_period, $valid_periods)) {
    $time_period = '30days';
}

// Get current goal from URL or user meta
$current_goal = isset($_GET['goal']) ? sanitize_key($_GET['goal']) : get_user_meta($user_id, 'guestify_current_goal', true);
if (empty($current_goal)) {
    $current_goal = 'revenue';
}

// Get dashboard data
$pipeline_data = guestify_get_pipeline_data($user_id, $time_period);
$outcomes_data = guestify_get_outcomes_data($user_id, $time_period);
$attribution_data = guestify_get_attribution_data($user_id, $time_period);
$journey_stage = guestify_get_user_journey_stage($user_id);

get_header();
?>

<main class="gfy-main">

    <!-- Header -->
    <header class="gfy-header">
        <div>
            <h1 class="gfy-page-title"><?php esc_html_e('Performance Reports', 'guestify'); ?></h1>
            <p class="gfy-page-subtitle"><?php esc_html_e('Track your pipeline activity and resulting guest outcomes.', 'guestify'); ?></p>
        </div>
        <div class="gfy-controls">
            <select class="gfy-time-select" id="timePeriodSelect">
                <option value="30days" <?php selected($time_period, '30days'); ?>><?php esc_html_e('Last 30 Days', 'guestify'); ?></option>
                <option value="90days" <?php selected($time_period, '90days'); ?>><?php esc_html_e('Last 90 Days', 'guestify'); ?></option>
                <option value="ytd" <?php selected($time_period, 'ytd'); ?>><?php esc_html_e('Year to Date', 'guestify'); ?></option>
            </select>
            <div class="gfy-goal-toggle" id="goalSelector">
                <button class="gfy-goal-btn<?php echo $current_goal === 'revenue' ? ' active' : ''; ?>" data-goal="revenue">
                    <?php esc_html_e('Grow Revenue', 'guestify'); ?>
                </button>
                <button class="gfy-goal-btn<?php echo $current_goal === 'authority' ? ' active' : ''; ?>" data-goal="authority">
                    <?php esc_html_e('Build Authority', 'guestify'); ?>
                </button>
                <button class="gfy-goal-btn<?php echo $current_goal === 'launch' ? ' active' : ''; ?>" data-goal="launch">
                    <?php esc_html_e('Launch', 'guestify'); ?>
                </button>
            </div>
        </div>
    </header>

    <!-- Journey Progress Bar -->
    <div class="gfy-journey-bar">
        <div class="gfy-journey-status">
            <i class="fa-solid fa-location-dot" style="color:var(--gfy-action-500);"></i>
            <?php printf(esc_html__('Current Stage: %s', 'guestify'), esc_html($journey_stage['label'])); ?>
        </div>
        <div class="gfy-journey-steps">
            <?php
            $stages = array('identity', 'discovery', 'intelligence', 'action');
            $current_index = array_search($journey_stage['id'], $stages);
            foreach ($stages as $index => $stage):
                $is_done = $index < $current_index;
                $is_active = $index === $current_index;
            ?>
                <span class="gfy-dot<?php echo $is_done ? ' done' : ($is_active ? ' active' : ''); ?>" title="<?php echo esc_attr(ucfirst($stage)); ?>"></span>
                <?php if ($index < count($stages) - 1): ?>
                <div class="gfy-line<?php echo $is_done ? ' done' : ''; ?>"></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <div class="gfy-journey-cta" id="journeyCta">
            <i class="fa-solid fa-circle-info"></i>
            <?php echo esc_html($journey_stage['cta']); ?>
        </div>
    </div>

    <!-- SECTION 1: DELIVERY PIPELINE -->
    <div class="gfy-section-header">
        <?php esc_html_e('1. Delivery Pipeline (Activity)', 'guestify'); ?>
        <div class="gfy-section-line"></div>
    </div>

    <?php get_template_part('template-parts/dashboard/pipeline', null, array('data' => $pipeline_data)); ?>

    <!-- INSIGHT BANNER -->
    <?php if (!empty($pipeline_data['insight'])): ?>
    <div class="gfy-insight-banner">
        <div class="gfy-insight-left">
            <i class="fa-solid fa-lightbulb gfy-insight-icon"></i>
            <div class="gfy-insight-text">
                <?php echo wp_kses_post($pipeline_data['insight']); ?>
            </div>
        </div>
        <a href="<?php echo esc_url(home_url('/app/benchmarks/')); ?>" class="gfy-btn gfy-btn-sm" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white;">
            <?php esc_html_e('View Benchmarks', 'guestify'); ?>
        </a>
    </div>
    <?php endif; ?>

    <!-- SECTION 2: OUTCOMES -->
    <div class="gfy-section-header">
        <?php esc_html_e('2. Performance Outcomes (Results)', 'guestify'); ?>
        <div class="gfy-section-line"></div>
    </div>

    <?php get_template_part('template-parts/dashboard/outcomes', null, array(
        'data' => $outcomes_data,
        'current_goal' => $current_goal
    )); ?>

    <!-- SECTION 3: ATTRIBUTION & ACTIONS -->
    <section class="gfy-split-grid">

        <!-- Attribution Table -->
        <?php get_template_part('template-parts/dashboard/attribution', null, array('data' => $attribution_data)); ?>

        <!-- Quick Actions -->
        <div class="gfy-action-card">
            <div class="gfy-action-title"><?php esc_html_e('Keep the momentum!', 'guestify'); ?></div>
            <div class="gfy-action-text">
                <?php
                $ready_count = $pipeline_data['ready_to_pitch'] ?? 0;
                printf(
                    esc_html__('You have %s in your CRM ready to pitch.', 'guestify'),
                    '<strong>' . sprintf(_n('%d vetted show', '%d vetted shows', $ready_count, 'guestify'), $ready_count) . '</strong>'
                );
                ?>
            </div>
            <div>
                <a href="<?php echo esc_url(home_url('/app/outreach/')); ?>" class="gfy-btn gfy-btn-primary" style="width: 100%;">
                    <i class="fa-solid fa-paper-plane"></i>
                    <?php esc_html_e('Start Outreach Campaign', 'guestify'); ?>
                </a>
            </div>
        </div>

    </section>

</main>

<?php get_footer(); ?>
