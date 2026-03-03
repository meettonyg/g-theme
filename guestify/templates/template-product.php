<?php
/**
 * Template Name: Product Page
 * Template Post Type: page
 *
 * Page template for product / service landing pages. Content is built
 * entirely with the block editor; the template provides the outer wrapper.
 *
 * @package Guestify
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="primary" class="gfy-frontend gfy-product-page">

	<?php
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
	?>

</main><!-- #primary -->

<?php
get_footer();
