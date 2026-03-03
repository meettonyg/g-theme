<?php
/**
 * Template Name: Resource Page
 * Template Post Type: page
 *
 * Page template for resource landing pages (lead magnets, downloads,
 * guides). Content is built entirely with the block editor; the
 * template provides the outer wrapper.
 *
 * @package Guestify
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="primary" class="gfy-frontend gfy-resource-page">

	<?php
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
	?>

</main><!-- #primary -->

<?php
get_footer();
