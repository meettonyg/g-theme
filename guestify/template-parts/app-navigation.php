<?php
/**
 * App Navigation Header
 * 
 * Displays on /app/, /account/, /courses/, /tools/ and their child pages
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

// Get the app menu from registered location
$menu_locations = get_nav_menu_locations();
$app_menu_id = isset($menu_locations['app-menu']) ? $menu_locations['app-menu'] : 0;
$menu_items = $app_menu_id ? wp_get_nav_menu_items($app_menu_id) : [];

// Fallback to menu ID 1398 if no menu assigned to location (for backwards compatibility)
if (empty($menu_items)) {
    $app_menu = wp_get_nav_menu_object(1398);
    if ($app_menu) {
        $menu_items = wp_get_nav_menu_items($app_menu->term_id);
    }
}

// Ensure menu_items is an array
if (!is_array($menu_items)) {
    $menu_items = [];
}

// Organize menu items by parent/child relationship
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
                <i class="fa-solid fa-microphone-lines" style="font-size: 32px; color: #ED8936;"></i>
            </div>
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

            <!-- User Dropdown -->
            <div class="app-nav__user-dropdown">
                <button class="app-nav__profile" onclick="toggleUserDropdown()" aria-label="User Menu">
                    <?php if ($user_avatar_url): ?>
                        <img src="<?php echo esc_url($user_avatar_url); ?>" alt="Profile" class="app-nav__avatar-img">
                    <?php else: ?>
                        <div class="app-nav__avatar"><?php echo esc_html($user_initials); ?></div>
                    <?php endif; ?>
                    <div class="app-nav__profile-info">
                        <div class="app-nav__profile-name"><?php echo esc_html($current_user->display_name); ?></div>
                        <div class="app-nav__profile-role">Pro User</div>
                    </div>
                    <svg class="app-nav__dropdown-caret" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
                
                <!-- User Dropdown Menu -->
                <div class="app-nav__user-menu" id="userDropdownMenu">
                    <div class="app-nav__user-menu-header">
                        <div class="app-nav__user-menu-name"><?php echo esc_html($current_user->display_name); ?></div>
                        <div class="app-nav__user-menu-email"><?php echo esc_html($current_user->user_email); ?></div>
                    </div>
                    
                    <div class="app-nav__user-menu-divider"></div>
                    
                    <div class="app-nav__user-menu-section">
                        <a href="<?php echo home_url('/app/onboarding/'); ?>" class="app-nav__user-menu-item app-nav__user-menu-item--progress">
                            <svg class="app-nav__user-menu-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                            </svg>
                            Guest Onboarding
                        </a>
                        
                        <a href="<?php echo home_url('/courses/'); ?>" class="app-nav__user-menu-item">
                            <svg class="app-nav__user-menu-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.25 2.524 1 1 0 01-1.5 0z"/>
                            </svg>
                            Training & Courses
                        </a>
                    </div>
                    
                    <div class="app-nav__user-menu-divider"></div>
                    
                    <div class="app-nav__user-menu-section">
                        <a href="<?php echo esc_url('https://guestify.ai/account/'); ?>" class="app-nav__user-menu-item">
                            <svg class="app-nav__user-menu-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                            </svg>
                            Account Settings
                        </a>
                        
                        <a href="<?php echo wp_logout_url(home_url('/login/')); ?>" class="app-nav__user-menu-item app-nav__user-menu-item--danger">
                            <svg class="app-nav__user-menu-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                            </svg>
                            Log Out
                        </a>
                    </div>
                </div>
            </div>
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