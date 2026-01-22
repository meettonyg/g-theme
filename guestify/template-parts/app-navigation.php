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

// Output REST API nonce for JavaScript notification calls
// This is output directly in the template to ensure it's available
// on any page where the nav bar is displayed
if (is_user_logged_in()) :
?>
<script>
window.guestifyAppNav = {
    nonce: '<?php echo wp_create_nonce('wp_rest'); ?>',
    restUrl: '<?php echo esc_url(rest_url('guestify/v1/')); ?>'
};
</script>
<?php endif;

$current_user = wp_get_current_user();
$user_avatar_url = get_avatar_url($current_user->ID, ['size' => 32]);
$user_initials = strtoupper(substr($current_user->first_name, 0, 1) . substr($current_user->last_name, 0, 1));
if (empty($user_initials)) {
    $user_initials = strtoupper(substr($current_user->display_name, 0, 1));
}

// Get onboarding progress from cached usermeta (synced by mk4 plugin)
// Only show if GMKB_Onboarding_Repository is available (mk4 plugin active)
$onboarding_progress = null;
if (class_exists('GMKB_Onboarding_Repository')) {
    // Use cached value for performance; falls back to fresh calculation if empty
    $cached_progress = get_user_meta($current_user->ID, 'guestify_onboarding_progress_percent', true);
    if ($cached_progress !== '' && $cached_progress !== false) {
        $onboarding_progress = (int) $cached_progress;
    } else {
        // Fallback: calculate fresh if no cached value exists
        $repo = new GMKB_Onboarding_Repository();
        $progress_data = $repo->calculate_progress($current_user->ID);
        $onboarding_progress = $progress_data['points']['percentage'] ?? 0;
    }
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
                    <?php
                    $child_count = 0;
                    $total_children = count($menu_data['children']);
                    $parent_title = strtolower($item->title);

                    // Define section breaks for each parent menu (matching mockup)
                    $section_config = [
                        'prospector' => [
                            'label_at_start' => 'Search By',
                            'breaks' => [
                                ['after' => 'Podcasts by Title', 'divider' => true, 'label' => 'Advanced']
                            ]
                        ],
                        'pipeline' => [
                            'breaks' => [
                                ['after' => 'My Interviews', 'divider' => true]
                            ]
                        ],
                        'outreach' => [
                            'breaks' => [
                                ['after' => 'Templates', 'divider' => true]
                            ]
                        ],
                        'media kit' => [
                            'label_at_start' => 'Profile',
                            'breaks' => [
                                ['after' => 'Social Links', 'divider' => true, 'label' => 'Media Kit'],
                                ['after' => 'AI Content Tools', 'divider' => true]
                            ]
                        ],
                        'insights' => []
                    ];

                    $config = isset($section_config[$parent_title]) ? $section_config[$parent_title] : [];

                    // Show initial label if configured
                    if (!empty($config['label_at_start'])): ?>
                    <li class="app-nav__dropdown-label"><?php echo esc_html($config['label_at_start']); ?></li>
                    <?php endif;

                    foreach ($menu_data['children'] as $child_item):
                        $child_title = $child_item->title;
                    ?>
                    <li class="app-nav__dropdown-item">
                        <a href="<?php echo esc_url($child_item->url); ?>"
                           class="app-nav__dropdown-link <?php echo is_app_page_active($child_item->url) ? 'app-nav__dropdown-link--active' : ''; ?>">
                            <?php echo get_menu_icon($child_title); ?>
                            <?php echo esc_html($child_title); ?>
                        </a>
                    </li>
                    <?php
                    // Check if we need a divider/label after this item
                    if (!empty($config['breaks'])):
                        foreach ($config['breaks'] as $break):
                            if ($break['after'] === $child_title):
                                if (!empty($break['divider'])): ?>
                    <li class="app-nav__dropdown-divider"></li>
                                <?php endif;
                                if (!empty($break['label'])): ?>
                    <li class="app-nav__dropdown-label"><?php echo esc_html($break['label']); ?></li>
                                <?php endif;
                            endif;
                        endforeach;
                    endif;

                    $child_count++;
                    endforeach; ?>
                </ul>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>

        <!-- User Section -->
        <div class="app-nav__user">
            <!-- Command Palette Search Button -->
            <button class="app-nav__search-btn" id="commandPaletteToggle" aria-label="Search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span class="app-nav__search-text">Search</span>
                <kbd class="app-nav__search-kbd">Ctrl+K</kbd>
            </button>

            <!-- Notifications -->
            <div class="app-nav__notifications-wrapper">
                <button class="app-nav__notifications" aria-label="Notifications" onclick="toggleNotificationsPanel()">
                    <i class="fa-solid fa-bell"></i>
                    <span class="app-nav__notifications-badge" id="notificationsBadge"></span>
                </button>

                <!-- Notifications Panel -->
                <div class="app-nav__notifications-panel" id="notificationsPanel">
                    <div class="app-nav__notifications-header">
                        <h3>Notifications</h3>
                        <button class="app-nav__notifications-mark-read" onclick="markAllNotificationsRead()">
                            Mark all as read
                        </button>
                    </div>
                    <div class="app-nav__notifications-list" id="notificationsList">
                        <div class="app-nav__notifications-loading">
                            <span>Loading...</span>
                        </div>
                    </div>
                    <div class="app-nav__notifications-footer">
                        <a href="<?php echo esc_url(home_url('/account/notifications/')); ?>">Notification Settings</a>
                    </div>
                </div>
            </div>

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
                        <?php if ($onboarding_progress !== null) : ?>
                        <a href="<?php echo home_url('/app/onboarding/'); ?>"
                           class="app-nav__user-menu-item app-nav__user-menu-item--progress"
                           data-progress="<?php echo esc_attr($onboarding_progress); ?>">
                            <svg class="app-nav__user-menu-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                            </svg>
                            Guest Onboarding
                        </a>
                        <?php endif; ?>
                        
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

<!-- Command Palette Modal -->
<div class="command-palette" id="commandPalette" role="dialog" aria-modal="true" aria-labelledby="commandPaletteLabel">
    <div class="command-palette__backdrop" id="commandPaletteBackdrop"></div>
    <div class="command-palette__container">
        <div class="command-palette__header">
            <div class="command-palette__search-wrapper">
                <i class="fa-solid fa-magnifying-glass command-palette__search-icon"></i>
                <input
                    type="text"
                    class="command-palette__input"
                    id="commandPaletteInput"
                    placeholder="Search podcasts, guests, campaigns, or type a command..."
                    autocomplete="off"
                    spellcheck="false"
                >
                <kbd class="command-palette__esc">ESC</kbd>
            </div>
        </div>

        <div class="command-palette__body" id="commandPaletteBody">
            <!-- Recent Section (shown by default) -->
            <div class="command-palette__section" id="cpSectionRecent">
                <div class="command-palette__section-header">
                    <i class="fa-solid fa-clock"></i>
                    Recent
                </div>
                <div class="command-palette__results" id="cpResultsRecent"></div>
            </div>

            <!-- Quick Actions Section -->
            <div class="command-palette__section" id="cpSectionActions">
                <div class="command-palette__section-header">
                    <i class="fa-solid fa-bolt"></i>
                    Quick Actions
                </div>
                <div class="command-palette__results" id="cpResultsActions">
                    <a href="/app/prospector/search/" class="command-palette__item" data-action="navigate">
                        <i class="fa-solid fa-magnifying-glass command-palette__item-icon"></i>
                        <span class="command-palette__item-text">Search for podcasts</span>
                        <span class="command-palette__item-hint">Prospector</span>
                    </a>
                    <a href="/app/pipeline/" class="command-palette__item" data-action="navigate">
                        <i class="fa-solid fa-plus command-palette__item-icon"></i>
                        <span class="command-palette__item-text">Add podcast to pipeline</span>
                        <span class="command-palette__item-hint">Pipeline</span>
                    </a>
                    <a href="/app/outreach/campaigns/" class="command-palette__item" data-action="navigate">
                        <i class="fa-solid fa-paper-plane command-palette__item-icon"></i>
                        <span class="command-palette__item-text">Create outreach campaign</span>
                        <span class="command-palette__item-hint">Outreach</span>
                    </a>
                    <a href="/app/media-kit/profile/" class="command-palette__item" data-action="navigate">
                        <i class="fa-solid fa-user-pen command-palette__item-icon"></i>
                        <span class="command-palette__item-text">Edit my profile</span>
                        <span class="command-palette__item-hint">Media Kit</span>
                    </a>
                </div>
            </div>

            <!-- Navigation Section -->
            <div class="command-palette__section" id="cpSectionNav">
                <div class="command-palette__section-header">
                    <i class="fa-solid fa-compass"></i>
                    Navigation
                </div>
                <div class="command-palette__results" id="cpResultsNav"></div>
            </div>

            <!-- Search Results (hidden by default, shown when typing) -->
            <div class="command-palette__section command-palette__section--hidden" id="cpSectionPodcasts">
                <div class="command-palette__section-header">
                    <i class="fa-solid fa-podcast"></i>
                    Podcasts
                </div>
                <div class="command-palette__results" id="cpResultsPodcasts"></div>
            </div>

            <div class="command-palette__section command-palette__section--hidden" id="cpSectionGuests">
                <div class="command-palette__section-header">
                    <i class="fa-solid fa-users"></i>
                    Guests
                </div>
                <div class="command-palette__results" id="cpResultsGuests"></div>
            </div>

            <div class="command-palette__section command-palette__section--hidden" id="cpSectionCampaigns">
                <div class="command-palette__section-header">
                    <i class="fa-solid fa-bullhorn"></i>
                    Campaigns
                </div>
                <div class="command-palette__results" id="cpResultsCampaigns"></div>
            </div>

            <!-- Empty State -->
            <div class="command-palette__empty" id="cpEmpty" style="display: none;">
                <i class="fa-solid fa-search"></i>
                <p>No results found</p>
                <span>Try a different search term</span>
            </div>

            <!-- Loading State -->
            <div class="command-palette__loading" id="cpLoading" style="display: none;">
                <i class="fa-solid fa-spinner fa-spin"></i>
                <span>Searching...</span>
            </div>
        </div>

        <div class="command-palette__footer">
            <div class="command-palette__shortcuts">
                <span><kbd>↑</kbd><kbd>↓</kbd> Navigate</span>
                <span><kbd>↵</kbd> Select</span>
                <span><kbd>ESC</kbd> Close</span>
            </div>
        </div>
    </div>
</div>