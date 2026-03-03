<?php
/**
 * Title: Section — Primary CTA
 * Slug: guestify/section-cta-primary
 * Categories: guestify-cta
 * Description: Full-width CTA with headline, subtext, and two buttons on accent background.
 */

return array(
	'title'       => __( 'Section — Primary CTA', 'guestify' ),
	'categories'  => array( 'guestify-cta' ),
	'description' => __( 'Full-width CTA with headline, subtext, and two buttons on accent background.', 'guestify' ),
	'content'     => '<!-- wp:group {"className":"gfy-section gfy-section--accent"} -->
<div class="wp-block-group gfy-section gfy-section--accent">
	<!-- wp:group {"className":"gfy-wrapper","layout":{"type":"constrained"}} -->
	<div class="wp-block-group gfy-wrapper" style="text-align:center;">
		<!-- wp:heading {"textAlign":"center","level":2} -->
		<h2 class="wp-block-heading has-text-align-center">You\'ve Done the Work. Now Get the Recognition.</h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"align":"center"} -->
		<p class="has-text-align-center">Authority Is Recognized Trust. Start building yours.</p>
		<!-- /wp:paragraph -->

		<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"className":"gfy-cta__buttons"} -->
		<div class="wp-block-buttons gfy-cta__buttons">
			<!-- wp:button {"className":"gfy-btn gfy-btn--primary"} -->
			<div class="wp-block-button gfy-btn gfy-btn--primary"><a class="wp-block-button__link wp-element-button" href="/start">Start Free Trial</a></div>
			<!-- /wp:button -->

			<!-- wp:button {"className":"gfy-btn gfy-btn--secondary"} -->
			<div class="wp-block-button gfy-btn gfy-btn--secondary"><a class="wp-block-button__link wp-element-button" href="/demo">Book a Demo</a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->

		<!-- wp:paragraph {"align":"center","className":"gfy-cta__reassurance"} -->
		<p class="has-text-align-center gfy-cta__reassurance">14-day free trial. No credit card required.</p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->',
);
