<?php
/**
* The template for displaying the footer
*
* Contains the closing of the #content div and all content after.
*
* @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
*
* @package Guestify
*/

?>

<?php if ( !is_app_page() && !is_blank_canvas_page() ) : ?>
<!-- Public Footer -->
<footer id="colophon" class="site-footer">
<div class="site-info">
<a href="<?php echo esc_url( __( 'https://wordpress.org/', 'guestify' ) ); ?>">
 <?php
 /* translators: %s: CMS name, i.e. WordPress. */
  printf( esc_html__( 'Proudly powered by %s', 'guestify' ), 'WordPress' );
  ?>
</a>
<span class="sep"> | </span>
 <?php
 /* translators: 1: Theme name, 2: Theme author. */
  printf( esc_html__( 'Theme: %1$s by %2$s.', 'guestify' ), 'guestify', '<a href="https://guestify.ai">Tony Guarnaccia</a>' );
    ?>
			</div><!-- .site-info -->
		</footer><!-- #colophon -->
	<?php endif; ?>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
