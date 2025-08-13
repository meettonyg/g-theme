<?php
/**
 * App Navigation Header
 * 
 * Only displays on /app/ and child pages
 * Uses Guestify Visual Brand Standards with dark theme
 * 
 * @package Guestify
 */

// Only show on app pages
if (!is_app_page()) {
    return;
}

$current_user = wp_get_current_user();
$user_avatar_url = get_avatar_url($current_user->ID, ['size' => 32]);
$user_initials = strtoupper(substr($current_user->first_name, 0, 1) . substr($current_user->last_name, 0, 1));
if (empty($user_initials)) {
    $user_initials = strtoupper(substr($current_user->display_name, 0, 1));
}

// Get the app menu (ID: 1398)
$app_menu = wp_get_nav_menu_object(1398);
$menu_items = wp_get_nav_menu_items($app_menu->term_id);

// Organization menu items by parent/child relationship
$menu_structure = [];
foreach ($menu_items as $item) {
    if ($item->menu_item_parent == 0) {
        $menu_structure[$item->ID] = [
            'item' => $item,
            'children' => []
        ];
    } else {
        $menu_structure[$item->menu_item_parent]['children'][] = $item;
    }
}
?>

<nav class="app-nav">
    <div class="app-nav__container">
        <!-- Brand -->
        <a href="<?php echo home_url('/app/'); ?>" class="app-nav__brand">
            <div class="app-nav__logo">
                <svg viewBox="0 0 40 40" class="app-nav__logo-icon">
                    <defs>
                        <linearGradient id="logoGradientDark" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#ffffff;stop-opacity:1" />
                            <stop offset="50%" style="stop-color:#2B7A4C;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#38B2AC;stop-opacity:1" />
                        </linearGradient>
                    </defs>
                    <rect width="40" height="40" rx="8" fill="url(#logoGradientDark)"/>
                    <g fill="#1B365D" opacity="0.9">
                        <!-- Connection nodes representing relationship intelligence -->
                        <circle cx="15" cy="15" r="3"/>
                        <circle cx="25" cy="15" r="3"/>
                        <circle cx="20" cy="25" r="3"/>
                        <!-- Connection lines -->
                        <line x1="15" y1="15" x2="25" y2="15" stroke="#1B365D" stroke-width="2" opacity="0.8"/>
                        <line x1="18" y1="18" x2="22" y2="22" stroke="#1B365D" stroke-width="2" opacity="0.8"/>
                        <line x1="22" y1="18" x2="18" y2="22" stroke="#1B365D" stroke-width="2" opacity="0.8"/>
                    </g>
                </svg>
            </div>
            <span class="app-nav__brand-text">Guestify</span>
        </a>

        <!-- Desktop Menu -->
        <ul class="app-nav__menu">
            <?php foreach ($menu_structure as $menu_id => $menu_data): 
                $item = $menu_data['item'];
                $is_active = is_app_page_active($item->url);
                $has_children = !empty($menu_data['children']);
            ?>
            <li class="app-nav__item <?php echo $has_children ? 'app-nav__item--dropdown' : ''; ?>">
                <a href="<?php echo esc_url($item->url); ?>" 
                   class="app-nav__link <?php echo $is_active ? 'app-nav__link--active' : ''; ?>">
                    <?php echo get_menu_icon($item->title); ?>
                    <?php echo esc_html($item->title); ?>
                    <?php if ($has_children): ?>
                        <svg class="app-nav__dropdown-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    <?php endif; ?>
                </a>
                
                <?php if ($has_children): ?>
                <ul class="app-nav__dropdown">
                    <?php foreach ($menu_data['children'] as $child_item): ?>
                    <li class="app-nav__dropdown-item">
                        <a href="<?php echo esc_url($child_item->url); ?>" 
                           class="app-nav__dropdown-link <?php echo is_app_page_active($child_item->url) ? 'app-nav__dropdown-link--active' : ''; ?>">
                            <?php echo get_menu_icon($child_item->title); ?>
                            <?php echo esc_html($child_item->title); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>

        <!-- User Section -->
        <div class="app-nav__user">
            <!-- Notifications -->
            <button class="app-nav__notifications" aria-label="Notifications">
                <svg class="app-nav__icon" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                </svg>
                <span class="app-nav__notifications-badge"></span>
            </button>

            <!-- Profile -->
            <a href="<?php echo home_url('/app/profile/'); ?>" class="app-nav__profile">
                <?php if ($user_avatar_url): ?>
                    <img src="<?php echo esc_url($user_avatar_url); ?>" alt="Profile" class="app-nav__avatar-img">
                <?php else: ?>
                    <div class="app-nav__avatar"><?php echo esc_html($user_initials); ?></div>
                <?php endif; ?>
                <div class="app-nav__profile-info">
                    <div class="app-nav__profile-name"><?php echo esc_html($current_user->display_name); ?></div>
                    <div class="app-nav__profile-role">Pro User</div>
                </div>
            </a>
        </div>

        <!-- Mobile Toggle -->
        <button class="app-nav__mobile-toggle" onclick="toggleMobileMenu()" aria-label="Toggle Mobile Menu">
            <svg class="app-nav__icon" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
        </svg>
    </button>
    </div>

    <!-- Mobile Menu -->
    <div class="app-nav__mobile-menu" id="mobileMenu">
        <ul class="app-nav__menu">
            <?php foreach ($menu_structure as $menu_id => $menu_data): 
                $item = $menu_data['item'];
                $is_active = is_app_page_active($item->url);
                $has_children = !empty($menu_data['children']);
            ?>
            <li class="app-nav__item">
                <a href="<?php echo esc_url($item->url); ?>" 
                   class="app-nav__link <?php echo $is_active ? 'app-nav__link--active' : ''; ?>">
                    <?php echo get_menu_icon($item->title); ?>
                    <?php echo esc_html($item->title); ?>
                </a>
                <?php if ($has_children): ?>
                    <?php foreach ($menu_data['children'] as $child_item): ?>
                    <a href="<?php echo esc_url($child_item->url); ?>" 
                       class="app-nav__link app-nav__link--child <?php echo is_app_page_active($child_item->url) ? 'app-nav__link--active' : ''; ?>">
                        <?php echo get_menu_icon($child_item->title); ?>
                        <?php echo esc_html($child_item->title); ?>
                    </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</nav>