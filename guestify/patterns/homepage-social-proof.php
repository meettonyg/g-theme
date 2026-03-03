<?php
/**
 * Title: Homepage — Social Proof
 * Slug: guestify/homepage-social-proof
 * Categories: guestify-sections
 * Description: Testimonial section with a featured quote and three persona-specific testimonial placeholders.
 */

return array(
	'title'       => __( 'Homepage — Social Proof', 'guestify' ),
	'categories'  => array( 'guestify-sections' ),
	'description' => __( 'Testimonial section with a featured quote and three persona-specific testimonial placeholders.', 'guestify' ),
	'content'     => '<!-- wp:group {"className":"gfy-section gfy-section--light","layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group gfy-section gfy-section--light">

	<!-- wp:group {"className":"gfy-section__header","layout":{"type":"constrained","contentSize":"720px"}} -->
	<div class="wp-block-group gfy-section__header">

		<!-- wp:paragraph {"className":"gfy-eyebrow"} -->
		<p class="gfy-eyebrow">RESULTS</p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"level":2,"className":"gfy-section__title"} -->
		<h2 class="wp-block-heading gfy-section__title">What Happens When Authority Compounds</h2>
		<!-- /wp:heading -->

	</div>
	<!-- /wp:group -->

	<!-- wp:group {"className":"gfy-testimonial gfy-testimonial--featured","layout":{"type":"constrained","contentSize":"800px"}} -->
	<div class="wp-block-group gfy-testimonial gfy-testimonial--featured">

		<!-- wp:paragraph {"className":"gfy-testimonial__quote","fontSize":"large"} -->
		<p class="gfy-testimonial__quote">&ldquo;The revenue &mdash; six figures plus &mdash; came from the relationships I built with hosts, not from their listeners.&rdquo;</p>
		<!-- /wp:paragraph -->

		<!-- wp:paragraph {"className":"gfy-testimonial__attribution"} -->
		<p class="gfy-testimonial__attribution"><strong>Steve Brossman</strong>, Business Strategist</p>
		<!-- /wp:paragraph -->

	</div>
	<!-- /wp:group -->

	<!-- wp:columns {"className":"gfy-testimonials-grid"} -->
	<div class="wp-block-columns gfy-testimonials-grid">

		<!-- wp:column {"className":"gfy-testimonial__card"} -->
		<div class="wp-block-column gfy-testimonial__card">

			<!-- wp:paragraph {"className":"gfy-testimonial__quote"} -->
			<p class="gfy-testimonial__quote">&ldquo;[Authority Builder testimonial placeholder.]&rdquo;</p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"className":"gfy-testimonial__attribution"} -->
			<p class="gfy-testimonial__attribution"><strong>[Authority Builder name]</strong> &mdash; [title]</p>
			<!-- /wp:paragraph -->

		</div>
		<!-- /wp:column -->

		<!-- wp:column {"className":"gfy-testimonial__card"} -->
		<div class="wp-block-column gfy-testimonial__card">

			<!-- wp:paragraph {"className":"gfy-testimonial__quote"} -->
			<p class="gfy-testimonial__quote">&ldquo;[Revenue Generator testimonial placeholder.]&rdquo;</p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"className":"gfy-testimonial__attribution"} -->
			<p class="gfy-testimonial__attribution"><strong>[Revenue Generator name]</strong> &mdash; [title]</p>
			<!-- /wp:paragraph -->

		</div>
		<!-- /wp:column -->

		<!-- wp:column {"className":"gfy-testimonial__card"} -->
		<div class="wp-block-column gfy-testimonial__card">

			<!-- wp:paragraph {"className":"gfy-testimonial__quote"} -->
			<p class="gfy-testimonial__quote">&ldquo;[Launch Promoter testimonial placeholder.]&rdquo;</p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"className":"gfy-testimonial__attribution"} -->
			<p class="gfy-testimonial__attribution"><strong>[Launch Promoter name]</strong> &mdash; [title]</p>
			<!-- /wp:paragraph -->

		</div>
		<!-- /wp:column -->

	</div>
	<!-- /wp:columns -->

</div>
<!-- /wp:group -->',
);
