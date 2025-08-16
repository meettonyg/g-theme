<?php
/**
 * Guestify App Navigation Helper Functions
 * 
 * @package Guestify
 */

/**
 * Check if current page is an app page
 * 
 * @param string $section Optional section to check for (e.g., 'dashboard', 'interviews')
 * @return bool
 */
function is_app_page($section = '') {
    // Check if we're in admin or doing AJAX
    if (is_admin() || wp_doing_ajax()) {
        return false;
    }
    
    // Exclude specific page ID
    global $post;
    if ($post && $post->ID == 46159) {
        return false;
    }
    
    // Check URL path - most reliable method for all page types
    $current_url = $_SERVER['REQUEST_URI'];
    $url_path = parse_url($current_url, PHP_URL_PATH);
    $url_path = rtrim($url_path, '/');
    
    // Define all paths that should show app navigation
    $app_navigation_paths = ['/app', '/account', '/courses', '/tools'];
    
    // Check if URL matches any app navigation path
    foreach ($app_navigation_paths as $app_path) {
        if ($url_path === $app_path || strpos($url_path, $app_path . '/') === 0) {
            if (empty($section)) {
                return true;
            }
            // Check for specific section
            return strpos($url_path, $app_path . '/' . $section) === 0;
        }
    }
    
    return false;
}

/**
 * Check if a menu item URL matches the current page
 * 
 * @param string $url The menu item URL
 * @return bool
 */
function is_app_page_active($url) {
    $current_url = $_SERVER['REQUEST_URI'];
    $menu_path = parse_url($url, PHP_URL_PATH);
    
    if ($current_url === $menu_path) {
        return true;
    }
    
    // Check if current URL starts with menu path (for sub-pages)
    if (strlen($menu_path) > 1 && strpos($current_url, $menu_path . '/') === 0) {
        return true;
    }
    
    return false;
}

/**
 * Get appropriate icon for menu items based on title
 * 
 * @param string $title Menu item title
 * @return string SVG icon HTML
 */
function get_menu_icon($title) {
    $title_lower = strtolower($title);
    
    // Map menu titles to icons
    $icon_map = [
        'podcast prospector' => 'search',
        'interview tracker' => 'users',
        'guest profiles' => 'user-circle',
        'message builder' => 'mail',
        'value builder' => 'star',
        'dashboard' => 'dashboard',
        'analytics' => 'chart',
        'settings' => 'cog',
        'account' => 'cog',
        'training' => 'graduation-cap',
        'courses' => 'graduation-cap',
        'tools' => 'tools',
        'profile' => 'user',
        'help' => 'question-circle',
        'logout' => 'logout'
    ];
    
    // Find matching icon
    $icon_type = 'default';
    foreach ($icon_map as $key => $icon) {
        if (strpos($title_lower, $key) !== false) {
            $icon_type = $icon;
            break;
        }
    }
    
    return get_svg_icon($icon_type);
}

/**
 * Get SVG icon by type
 * 
 * @param string $type Icon type
 * @return string SVG HTML
 */
function get_svg_icon($type) {
    $icons = [
        'search' => '<svg class="app-nav__icon" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                    </svg>',
        'users' => '<svg class="app-nav__icon" viewBox="0 0 20 20" fill="currentColor">
                       <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                   </svg>',
        'user-circle' => '<svg class="app-nav__icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"/>
                        </svg>',
        'mail' => '<svg class="app-nav__icon" viewBox="0 0 20 20" fill="currentColor">
                      <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                      <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                  </svg>',
        'star' => '<svg class="app-nav__icon" viewBox="0 0 20 20" fill="currentColor">
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                  </svg>',
        'dashboard' => '<svg class="app-nav__icon" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                      </svg>',
        'chart' => '<svg class="app-nav__icon" viewBox="0 0 20 20" fill="currentColor">
                       <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"/>
                       <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"/>
                   </svg>',
        'cog' => '<svg class="app-nav__icon" viewBox="0 0 20 20" fill="currentColor">
                     <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                 </svg>',
        'user' => '<svg class="app-nav__icon" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                  </svg>',
        'graduation-cap' => '<svg class="app-nav__icon" viewBox="0 0 20 20" fill="currentColor">
                               <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.25 2.524 1 1 0 01-1.5 0z"/>
                           </svg>',
        'tools' => '<svg class="app-nav__icon" viewBox="0 0 20 20" fill="currentColor">
                       <path fill-rule="evenodd" d="M19 5.5a4.5 4.5 0 01-4.791 4.49c-.873-.055-1.808.128-2.368.8l-6.024 7.23a2.724 2.724 0 11-3.837-3.837L9.21 8.16c.672-.56.855-1.495.8-2.368a4.5 4.5 0 015.873-4.575c.324.105.39.51.15.752L13.34 4.66a.455.455 0 00-.11.494 3.01 3.01 0 001.617 1.617c.17.07.363.02.493-.111l2.692-2.692c.241-.241.647-.174.752.15.14.435.216.9.216 1.382zM4 17a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                   </svg>',
        'question-circle' => '<svg class="app-nav__icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.94 6.94a.75.75 0 11-1.061-1.061 3 3 0 112.871 5.026v.345a.75.75 0 01-1.5 0v-.5c0-.72.57-1.172 1.081-1.344A1.5 1.5 0 108.94 6.94zM10 15a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                            </svg>',
        'logout' => '<svg class="app-nav__icon" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 01-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                    </svg>',
        'default' => '<svg class="app-nav__icon" viewBox="0 0 20 20" fill="currentColor">
                         <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h8a2 2 0 012 2v10a2 2 0 002 2H4a2 2 0 01-2-2V5zm3 1h6v4H5V6zm6 6H5v2h6v-2z" clip-rule="evenodd"/>
                         <path d="M15 7h1a2 2 0 012 2v5.5a1.5 1.5 0 01-3 0V7z"/>
                     </svg>'
    ];
    
    return isset($icons[$type]) ? $icons[$type] : $icons['default'];
}

/**
 * Enqueue app navigation assets
 */
function enqueue_app_navigation_assets() {
    if (!is_app_page()) {
        return;
    }
    
    // Enqueue CSS
    wp_enqueue_style(
        'guestify-app-nav',
        get_template_directory_uri() . '/css/app-navigation.css',
        [],
        filemtime(get_template_directory() . '/css/app-navigation.css')
    );
    
    // Enqueue JavaScript
    wp_enqueue_script(
        'guestify-app-nav',
        get_template_directory_uri() . '/js/app-navigation.js',
        [],
        filemtime(get_template_directory() . '/js/app-navigation.js'),
        true
    );
}
add_action('wp_enqueue_scripts', 'enqueue_app_navigation_assets');

/**
 * Add Inter font for app navigation
 */
function enqueue_inter_font() {
    if (!is_app_page()) {
        return;
    }
    
    wp_enqueue_style(
        'guestify-inter-font',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap',
        [],
        null
    );
}
add_action('wp_enqueue_scripts', 'enqueue_inter_font');

/**
 * Remove page title on app pages
 */
function remove_app_page_titles($title, $id = null) {
    if (is_app_page() && in_the_loop() && is_main_query()) {
        return '';
    }
    return $title;
}
add_filter('the_title', 'remove_app_page_titles', 10, 2);

/**
 * Set appropriate page titles for app sections
 */
function set_app_page_titles($title) {
    if (is_app_page()) {
        $current_url = $_SERVER['REQUEST_URI'];
        $url_path = parse_url($current_url, PHP_URL_PATH);
        $url_path = rtrim($url_path, '/');
        
        if (strpos($url_path, '/account') === 0) {
            return 'Account Settings - Guestify';
        } elseif (strpos($url_path, '/courses') === 0) {
            return 'Training & Resources - Guestify';
        } elseif (strpos($url_path, '/tools') === 0) {
            return 'Tools - Guestify';
        } else {
            return 'Guestify App';
        }
    }
    return $title;
}
add_filter('wp_title', 'set_app_page_titles');
add_filter('document_title_parts', function($title) {
    if (is_app_page()) {
        $current_url = $_SERVER['REQUEST_URI'];
        $url_path = parse_url($current_url, PHP_URL_PATH);
        $url_path = rtrim($url_path, '/');
        
        if (strpos($url_path, '/account') === 0) {
            $title['title'] = 'Account Settings';
        } elseif (strpos($url_path, '/courses') === 0) {
            $title['title'] = 'Training & Resources';
        } elseif (strpos($url_path, '/tools') === 0) {
            $title['title'] = 'Tools';
        } else {
            $title['title'] = 'Guestify App';
        }
    }
    return $title;
});