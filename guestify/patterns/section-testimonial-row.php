<?php
/**
 * Title: Section — Testimonial Row
 * Slug: guestify/section-testimonial-row
 * Categories: guestify-sections
 * Description: Three testimonials in a row with avatar, quote, name, and title.
 */

return array(
	'title'       => __( 'Section — Testimonial Row', 'guestify' ),
	'categories'  => array( 'guestify-sections' ),
	'description' => __( 'Three testimonials in a row with avatar, quote, name, and title.', 'guestify' ),
	'content'     => '<!-- wp:group {"className":"gfy-section"} -->
<div class="wp-block-group gfy-section">
	<!-- wp:group {"className":"gfy-wrapper","layout":{"type":"constrained"}} -->
	<div class="wp-block-group gfy-wrapper">
		<!-- wp:columns {"className":"gfy-feature-grid"} -->
		<div class="wp-block-columns gfy-feature-grid">
			<!-- wp:column {"className":"gfy-testimonial"} -->
			<div class="wp-block-column gfy-testimonial">
				<!-- wp:paragraph {"className":"gfy-testimonial__quote"} -->
				<p class="gfy-testimonial__quote">&ldquo;[Client quote about results achieved with Guestify — e.g., Within three months I was appearing on top-tier shows in my niche and seeing real inbound leads.]&rdquo;</p>
				<!-- /wp:paragraph -->

				<!-- wp:group {"className":"gfy-testimonial__author"} -->
				<div class="wp-block-group gfy-testimonial__author">
					<!-- wp:paragraph {"className":"gfy-testimonial__name"} -->
					<p class="gfy-testimonial__name"><strong>[Client Name]</strong></p>
					<!-- /wp:paragraph -->

					<!-- wp:paragraph {"className":"gfy-testimonial__title"} -->
					<p class="gfy-testimonial__title">[Title, Company]</p>
					<!-- /wp:paragraph -->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:column -->

			<!-- wp:column {"className":"gfy-testimonial"} -->
			<div class="wp-block-column gfy-testimonial">
				<!-- wp:paragraph {"className":"gfy-testimonial__quote"} -->
				<p class="gfy-testimonial__quote">&ldquo;[Client quote about the authority-building experience — e.g., Guestify gave me the system I was missing. I stopped guessing and started getting recognized.]&rdquo;</p>
				<!-- /wp:paragraph -->

				<!-- wp:group {"className":"gfy-testimonial__author"} -->
				<div class="wp-block-group gfy-testimonial__author">
					<!-- wp:paragraph {"className":"gfy-testimonial__name"} -->
					<p class="gfy-testimonial__name"><strong>[Client Name]</strong></p>
					<!-- /wp:paragraph -->

					<!-- wp:paragraph {"className":"gfy-testimonial__title"} -->
					<p class="gfy-testimonial__title">[Title, Company]</p>
					<!-- /wp:paragraph -->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:column -->

			<!-- wp:column {"className":"gfy-testimonial"} -->
			<div class="wp-block-column gfy-testimonial">
				<!-- wp:paragraph {"className":"gfy-testimonial__quote"} -->
				<p class="gfy-testimonial__quote">&ldquo;[Client quote about revenue or business outcomes — e.g., The relationships I built through podcast appearances turned into my highest-value clients.]&rdquo;</p>
				<!-- /wp:paragraph -->

				<!-- wp:group {"className":"gfy-testimonial__author"} -->
				<div class="wp-block-group gfy-testimonial__author">
					<!-- wp:paragraph {"className":"gfy-testimonial__name"} -->
					<p class="gfy-testimonial__name"><strong>[Client Name]</strong></p>
					<!-- /wp:paragraph -->

					<!-- wp:paragraph {"className":"gfy-testimonial__title"} -->
					<p class="gfy-testimonial__title">[Title, Company]</p>
					<!-- /wp:paragraph -->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:column -->
		</div>
		<!-- /wp:columns -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->',
);
