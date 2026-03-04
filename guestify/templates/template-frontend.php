<?php
/**
 * Template Name: Frontend Page
 * Template Post Type: page
 *
 * Generic template for frontend/marketing pages built with block markup.
 * Provides the gfy-frontend wrapper class so all frontend CSS selectors match.
 *
 * @package Guestify
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="primary" class="gfy-frontend">

	<?php
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
	?>

</main><!-- #primary -->

<?php
get_footer();
