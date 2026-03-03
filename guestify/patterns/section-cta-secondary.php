<?php
/**
 * Title: Section — Secondary CTA
 * Slug: guestify/section-cta-secondary
 * Categories: guestify-cta
 * Description: Lighter CTA section for mid-page placement.
 */

return array(
	'title'       => __( 'Section — Secondary CTA', 'guestify' ),
	'categories'  => array( 'guestify-cta' ),
	'description' => __( 'Lighter CTA section for mid-page placement.', 'guestify' ),
	'content'     => '<!-- wp:group {"className":"gfy-section gfy-section--light"} -->
<div class="wp-block-group gfy-section gfy-section--light">
	<!-- wp:group {"className":"gfy-wrapper","layout":{"type":"constrained"}} -->
	<div class="wp-block-group gfy-wrapper" style="text-align:center;">
		<!-- wp:heading {"textAlign":"center","level":3} -->
		<h3 class="wp-block-heading has-text-align-center">Ready to stop being the best-kept secret?</h3>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"align":"center"} -->
		<p class="has-text-align-center">See how Guestify helps experts build recognized authority through strategic podcast interviews.</p>
		<!-- /wp:paragraph -->

		<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"className":"gfy-cta__buttons"} -->
		<div class="wp-block-buttons gfy-cta__buttons">
			<!-- wp:button {"className":"gfy-btn gfy-btn--secondary"} -->
			<div class="wp-block-button gfy-btn gfy-btn--secondary"><a class="wp-block-button__link wp-element-button" href="/demo">Book a Demo</a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->',
);
