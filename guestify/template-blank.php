<?php
/**
 * Template Name: Blank – No Header / No Footer
 */

defined( 'ABSPATH' ) || exit;

// Optionally include any <head> stuff you still need:
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
        the_content();
      endwhile;
    ?>
  </main>
  <?php wp_footer(); ?>
</body>
</html>
