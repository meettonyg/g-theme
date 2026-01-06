<?php
/**
 * Template Name: App Home Dashboard
 * Template Post Type: page
 *
 * Dynamic dashboard home page for the Guestify app.
 * Integrates all plugins: Media Kit, Prospector, ShowAuthority, Outreach
 *
 * @package Guestify
 * @version 1.1.0
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

// Add resource hints for performance
add_action('wp_head', function() {
    $theme_url = get_template_directory_uri();
    echo '<link rel="preload" href="' . esc_url($theme_url . '/css/home.css') . '" as="style">' . "\n";
    echo '<link rel="preload" href="' . esc_url($theme_url . '/js/home.js') . '" as="script">' . "\n";
    echo '<link rel="preconnect" href="' . esc_url(rest_url()) . '">' . "\n";
}, 1);

get_header();

// Get current user data
$current_user = wp_get_current_user();
$user_name = !empty($current_user->first_name) ? $current_user->first_name : $current_user->display_name;
$user_initials = strtoupper(substr($user_name, 0, 1));

// Get user goal preference
$current_goal = get_user_meta($current_user->ID, 'guestify_current_goal', true);
if (empty($current_goal)) {
    $current_goal = 'grow_revenue';
}

// Get initial dashboard data (will be updated via JavaScript)
$dashboard_data = guestify_get_home_dashboard_data($current_user->ID);

// Add current_goal to dashboard data for template parts
$dashboard_data['current_goal'] = $current_goal;
?>

<main id="primary" class="gfy-home">

    <!-- WELCOME HEADER -->
    <header class="gfy-home__welcome">
        <h1 class="gfy-home__greeting">Welcome back, <?php echo esc_html($user_name); ?></h1>
        <p class="gfy-home__date">
            <i class="fa-regular fa-calendar"></i>
            <span class="gfy-home__date-text"><?php echo esc_html(date_i18n('l, F j')); ?></span>
            <?php if (!empty($dashboard_data['tasks_due']) && $dashboard_data['tasks_due'] > 0): ?>
            <span class="gfy-home__tasks-badge">
                <i class="fa-solid fa-bell"></i>
                <?php echo esc_html($dashboard_data['tasks_due']); ?> Task<?php echo $dashboard_data['tasks_due'] !== 1 ? 's' : ''; ?> Due
            </span>
            <?php endif; ?>
        </p>

        <div class="gfy-home__goal-wrapper">
            <div class="gfy-home__goal-toggle" id="goalSelector">
                <button class="gfy-home__goal-btn<?php echo $current_goal === 'build_authority' ? ' active' : ''; ?>" data-goal="build_authority">
                    <i class="fa-solid fa-crown"></i>
                    Build Authority
                </button>
                <button class="gfy-home__goal-btn<?php echo $current_goal === 'grow_revenue' ? ' active' : ''; ?>" data-goal="grow_revenue">
                    <i class="fa-solid fa-dollar-sign"></i>
                    Grow Revenue
                </button>
                <button class="gfy-home__goal-btn<?php echo $current_goal === 'launch_promote' ? ' active' : ''; ?>" data-goal="launch_promote">
                    <i class="fa-solid fa-rocket"></i>
                    Launch & Promote
                </button>
            </div>
        </div>
    </header>

    <!-- QUICK STATS BAR -->
    <?php get_template_part('template-parts/home/quick-stats', null, array('data' => $dashboard_data)); ?>

    <!-- MAIN WORKSPACE SHORTCUTS (The 4 Pillars) -->
    <?php get_template_part('template-parts/home/pillars', null, array('data' => $dashboard_data)); ?>

    <!-- SPLIT: RECENT ACTIVITY & SUPPORT -->
    <section class="gfy-home__split">

        <!-- LEFT: Jump Back In -->
        <div>
            <h2 class="gfy-home__section-title">
                <i class="fa-solid fa-clock-rotate-left"></i>
                Jump Back In
            </h2>
            <?php get_template_part('template-parts/home/recent-activity', null, array('data' => $dashboard_data)); ?>
        </div>

        <!-- RIGHT: Community & Support -->
        <div>
            <h2 class="gfy-home__section-title">
                <i class="fa-solid fa-circle-question"></i>
                Community & Support
            </h2>
            <?php get_template_part('template-parts/home/support-cards'); ?>
        </div>

    </section>

</main>

<?php
// Output JavaScript configuration
$js_config = array(
    'apiUrl'      => esc_url(rest_url('guestify/v1/')),
    'nonce'       => wp_create_nonce('wp_rest'),
    'userName'    => esc_html($user_name),
    'currentGoal' => esc_attr($current_goal),
    'userId'      => $current_user->ID,
);
?>
<script>
    window.guestifyHomeConfig = <?php echo json_encode($js_config); ?>;
</script>

<?php
get_footer();
