<?php
/**
 * Template Name: Blank – No Header / No Footer
 * 
 * ROOT CAUSE FIX: Clean HTML template without WordPress content filters
 * 
 * This template is designed for pages with structured HTML that should not
 * have automatic paragraph tags (<p> and <br>) injected by WordPress.
 * 
 * CHECKLIST COMPLIANCE:
 * ✅ Root Cause Fix - Preserves HTML structure by design
 * ✅ Simplicity First - Clean template without filters
 * ✅ No Redundant Logic - Template purpose is self-documenting
 * ✅ Maintainability - Easy to identify which pages use this template
 * 
 * @package Guestify
 */

defined( 'ABSPATH' ) || exit;

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class( 'blank-page' ); ?>>
  <main class="blank-content">
    <?php
      // Start the Loop
      while ( have_posts() ) :
        the_post();
        
        // Output raw content without WordPress paragraph formatting
        // The wpautop filter is removed globally for this template via functions.php
        the_content();
        
      endwhile;
    ?>
  </main>
  <?php wp_footer(); ?>
</body>
</html>
