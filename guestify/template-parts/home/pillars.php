<?php
/**
 * Template Part: Home Pillar Cards
 *
 * Displays the four main pillar cards for the workspace shortcuts.
 * Supports goal-based personalization.
 *
 * @package Guestify
 * @version 1.1.0
 *
 * @param array $args['data'] Dashboard data array
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$data = isset($args['data']) ? $args['data'] : array();
$pillars = isset($data['pillars']) ? $data['pillars'] : array();
$current_goal = isset($data['current_goal']) ? $data['current_goal'] : 'grow_revenue';

// Define pillar configurations
$pillar_config = array(
    'media_kit' => array(
        'title'       => 'Media Kit',
        'description' => 'Update your bio, photos, topics, and speaking credentials.',
        'icon'        => 'fa-solid fa-id-card',
        'url'         => home_url('/app/profiles/'),
        'variant'     => 'identity',
        'default_status' => 'Profile Ready',
    ),
    'prospector' => array(
        'title'       => 'Prospector',
        'description' => 'Find podcasts matching your niche and audience.',
        'icon'        => 'fa-solid fa-magnifying-glass',
        'url'         => home_url('/app/prospector/'),
        'variant'     => 'discovery',
        'default_status' => '0 Saved Shows',
    ),
    'showauthority' => array(
        'title'       => 'ShowAuthority',
        'description' => 'Research shows and get AI-powered pitch angles.',
        'icon'        => 'fa-solid fa-brain',
        'url'         => home_url('/app/interviews/'),
        'variant'     => 'intel',
        'default_status' => '0 Ready to Pitch',
    ),
    'outreach' => array(
        'title'       => 'Outreach',
        'description' => 'Send campaigns and manage your inbox.',
        'icon'        => 'fa-solid fa-paper-plane',
        'url'         => home_url('/app/email-system/'),
        'variant'     => 'action',
        'default_status' => 'No Messages',
    ),
);

// Goal-based pillar ordering and priorities
$goal_pillar_order = array(
    'build_authority' => array('media_kit', 'showauthority', 'outreach', 'prospector'),
    'grow_revenue'    => array('prospector', 'showauthority', 'outreach', 'media_kit'),
    'launch_promote'  => array('outreach', 'prospector', 'showauthority', 'media_kit'),
);

// Goal-based descriptions (override defaults)
$goal_descriptions = array(
    'build_authority' => array(
        'media_kit'     => 'Perfect your professional image and credibility.',
        'showauthority' => 'Position yourself as the go-to expert.',
        'outreach'      => 'Reach top-tier shows in your space.',
        'prospector'    => 'Discover authority-building opportunities.',
    ),
    'grow_revenue' => array(
        'media_kit'     => 'Showcase your offers and conversion-focused content.',
        'showauthority' => 'Research high-converting podcast audiences.',
        'outreach'      => 'Book appearances that drive sales.',
        'prospector'    => 'Find shows with your ideal buyers.',
    ),
    'launch_promote' => array(
        'media_kit'     => 'Highlight your launch story and offer.',
        'showauthority' => 'Plan your interview tour strategy.',
        'outreach'      => 'Execute your launch blitz campaign.',
        'prospector'    => 'Build your launch show list fast.',
    ),
);

// Goal-based CTAs
$goal_ctas = array(
    'build_authority' => array(
        'media_kit'     => 'Polish Profile',
        'showauthority' => 'Build Authority',
        'outreach'      => 'Reach Out',
        'prospector'    => 'Discover',
    ),
    'grow_revenue' => array(
        'media_kit'     => 'Optimize',
        'showauthority' => 'Research',
        'outreach'      => 'Convert',
        'prospector'    => 'Find Buyers',
    ),
    'launch_promote' => array(
        'media_kit'     => 'Prepare',
        'showauthority' => 'Plan Tour',
        'outreach'      => 'Launch Blitz',
        'prospector'    => 'Build List',
    ),
);

// Get the pillar order for current goal
$pillar_order = isset($goal_pillar_order[$current_goal])
    ? $goal_pillar_order[$current_goal]
    : array('media_kit', 'prospector', 'showauthority', 'outreach');

// Mark first pillar as priority
$priority_pillar = $pillar_order[0];
?>

<section class="gfy-home__pillars" data-goal="<?php echo esc_attr($current_goal); ?>" role="navigation" aria-label="<?php esc_attr_e('Main workspace tools', 'guestify'); ?>">

    <?php foreach ($pillar_order as $index => $key):
        if (!isset($pillar_config[$key])) continue;

        $config = $pillar_config[$key];
        $pillar_data = isset($pillars[$key]) ? $pillars[$key] : array();
        $status_text = isset($pillar_data['status_text']) ? $pillar_data['status_text'] : $config['default_status'];
        $is_alert = isset($pillar_data['is_alert']) && $pillar_data['is_alert'];
        $show_dot = isset($pillar_data['show_dot']) && $pillar_data['show_dot'];
        $status_icon = isset($pillar_data['icon']) ? $pillar_data['icon'] : '';

        // Get goal-specific description if available
        $description = isset($goal_descriptions[$current_goal][$key])
            ? $goal_descriptions[$current_goal][$key]
            : $config['description'];

        // Get goal-specific CTA if available
        $cta = isset($goal_ctas[$current_goal][$key])
            ? $goal_ctas[$current_goal][$key]
            : 'Open';

        // Determine if this is the priority pillar
        $is_priority = ($key === $priority_pillar);

        // Build CSS classes
        $classes = array(
            'gfy-home__pillar',
            'gfy-home__pillar--' . $config['variant'],
        );
        if ($is_priority) {
            $classes[] = 'gfy-home__pillar--priority';
        }
    ?>
    <a href="<?php echo esc_url($config['url']); ?>"
       class="<?php echo esc_attr(implode(' ', $classes)); ?>"
       data-pillar="<?php echo esc_attr($key); ?>"
       data-url="<?php echo esc_url($config['url']); ?>">
        <?php if ($is_priority): ?>
        <span class="gfy-home__pillar-badge">Recommended</span>
        <?php endif; ?>
        <div class="gfy-home__pillar-icon">
            <i class="<?php echo esc_attr($config['icon']); ?>"></i>
        </div>
        <h3 class="gfy-home__pillar-title"><?php echo esc_html($config['title']); ?></h3>
        <p class="gfy-home__pillar-desc"><?php echo esc_html($description); ?></p>
        <div class="gfy-home__pillar-status<?php echo $is_alert ? ' gfy-home__pillar-status--alert' : ''; ?>">
            <?php if ($show_dot): ?>
            <div class="status-dot"></div>
            <?php endif; ?>
            <?php if (!empty($status_icon)): ?>
            <i class="<?php echo esc_attr($status_icon); ?>"></i>
            <?php endif; ?>
            <?php echo esc_html($status_text); ?>
        </div>
        <span class="gfy-home__pillar-cta">
            <?php echo esc_html($cta); ?>
            <i class="fa-solid fa-arrow-right"></i>
        </span>
    </a>
    <?php endforeach; ?>

</section>
