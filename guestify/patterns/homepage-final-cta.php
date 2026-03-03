<?php
/**
 * Title: Homepage — Final CTA
 * Slug: guestify/homepage-final-cta
 * Categories: guestify-cta
 * Description: Full-width closing call-to-action section with accent background, headline, supporting text, dual buttons, and reassurance copy.
 */

return array(
	'title'       => __( 'Homepage — Final CTA', 'guestify' ),
	'categories'  => array( 'guestify-cta' ),
	'description' => __( 'Full-width closing call-to-action section with accent background, headline, supporting text, dual buttons, and reassurance copy.', 'guestify' ),
	'content'     => '<!-- wp:group {"className":"gfy-section gfy-section--accent","layout":{"type":"constrained","contentSize":"720px"}} -->
<div class="wp-block-group gfy-section gfy-section--accent">

	<!-- wp:heading {"level":2,"className":"gfy-final-cta__title","textAlign":"center"} -->
	<h2 class="wp-block-heading has-text-align-center gfy-final-cta__title">You\'ve Done the Work. Now Get the Recognition.</h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"align":"center","className":"gfy-final-cta__desc"} -->
	<p class="has-text-align-center gfy-final-cta__desc">Authority Is Recognized Trust. Start building yours with the Interview Authority System.</p>
	<!-- /wp:paragraph -->

	<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"className":"gfy-final-cta__buttons"} -->
	<div class="wp-block-buttons gfy-final-cta__buttons">

		<!-- wp:button {"className":"gfy-btn-cta gfy-btn-cta--on-accent"} -->
		<div class="wp-block-button gfy-btn-cta gfy-btn-cta--on-accent"><a class="wp-block-button__link wp-element-button" href="/start">Start Free Trial</a></div>
		<!-- /wp:button -->

		<!-- wp:button {"className":"gfy-btn-secondary gfy-btn-secondary--on-accent"} -->
		<div class="wp-block-button gfy-btn-secondary gfy-btn-secondary--on-accent"><a class="wp-block-button__link wp-element-button" href="/demo">Book a Demo</a></div>
		<!-- /wp:button -->

	</div>
	<!-- /wp:buttons -->

	<!-- wp:paragraph {"align":"center","className":"gfy-final-cta__reassurance"} -->
	<p class="has-text-align-center gfy-final-cta__reassurance">14-day free trial. No credit card required.</p>
	<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->',
);
