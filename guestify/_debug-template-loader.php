<?php
/**
 * Add this code temporarily to your functions.php to debug template selection
 * It will show what template WordPress is using for each page
 */

// Debug what template is being used
add_action('wp_head', function() {
    if (current_user_can('administrator')) { // Only show to admins
        global $template;
        echo '<!-- Template being used: ' . basename($template) . ' -->' . "\n";
        echo '<!-- Post Type: ' . get_post_type() . ' -->' . "\n";
        echo '<!-- Is Single: ' . (is_single() ? 'Yes' : 'No') . ' -->' . "\n";
        echo '<!-- Is Singular: ' . (is_singular() ? 'Yes' : 'No') . ' -->' . "\n";
        
        // Check if Pods is active and what it's doing
        if (function_exists('pods')) {
            echo '<!-- Pods is active -->' . "\n";
            $current_pod = pods(get_post_type(), get_the_ID());
            if ($current_pod && $current_pod->exists()) {
                echo '<!-- Pod exists for this post type -->' . "\n";
            }
        }
    }
});

// Show template being loaded in admin bar
add_action('admin_bar_menu', function($admin_bar) {
    if (!is_admin() && current_user_can('administrator')) {
        global $template;
        $admin_bar->add_menu(array(
            'id'    => 'template-info',
            'title' => '📄 Template: ' . basename($template) . ' | Post Type: ' . get_post_type(),
            'href'  => '#',
            'meta'  => array(
                'title' => 'Current template file and post type'
            )
        ));
    }
}, 100);

// Log all template checks WordPress makes
add_filter('template_include', function($template) {
    if (strpos($_SERVER['REQUEST_URI'], '/guests/') === 0) {
        error_log('=== TEMPLATE DEBUG for ' . $_SERVER['REQUEST_URI'] . ' ===');
        error_log('Post Type: ' . get_post_type());
        error_log('Post ID: ' . get_the_ID());
        error_log('Template chosen: ' . basename($template));
        error_log('Is Single: ' . (is_single() ? 'Yes' : 'No'));
        error_log('Is Singular: ' . (is_singular() ? 'Yes' : 'No'));
        
        // Check what templates exist
        $post_type = get_post_type();
        $possible_templates = array(
            "single-{$post_type}.php",
            'single.php',
            'singular.php',
            'index.php'
        );
        
        foreach($possible_templates as $tpl) {
            $exists = file_exists(get_template_directory() . '/' . $tpl);
            error_log("Template $tpl exists: " . ($exists ? 'Yes' : 'No'));
        }
        error_log('=== END TEMPLATE DEBUG ===');
    }
    return $template;
}, 999);