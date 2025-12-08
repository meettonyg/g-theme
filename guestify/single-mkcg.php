<?php
/**
 * Template for Media Kit Builder (mkcg post type)
 * 
 * This is a CLEAN template with NO theme styling, header, or footer
 * The Vue.js app has complete control over the entire page
 *
 * @package Guestify
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title('|', true, 'right'); ?></title>
    
    <!-- ROOT FIX: Aggressive CSS reset to block theme styles -->
    <style>
        /* Strip all theme styling */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body.gmkb-builder-page {
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }
        
        /* Ensure Vue app takes full viewport */
        #app {
            min-height: 100vh;
            width: 100%;
        }
        
        /* Hide any theme headers/footers that might leak through */
        .site-header,
        .site-footer,
        header,
        footer {
            display: none !important;
        }
    </style>
    
    <?php wp_head(); ?>
</head>
<body <?php body_class('gmkb-builder-page'); ?>>

<!-- Vue.js Mount Point - The Vue app will take over this entire div -->
<div id="app"></div>

<?php wp_footer(); ?>
</body>
</html>
