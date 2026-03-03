<?php
/**
 * Title: Persona Page — Hero
 * Slug: guestify/persona-hero
 * Categories: guestify-persona
 * Description: Hero section for persona landing pages with wound-activated headline.
 */

return array(
	'title'       => __( 'Persona Page — Hero', 'guestify' ),
	'categories'  => array( 'guestify-persona' ),
	'description' => __( 'Hero section for persona landing pages with wound-activated headline.', 'guestify' ),
	'content'     => '<!-- wp:group {"className":"gfy-section gfy-hero-persona"} -->
<div class="wp-block-group gfy-section gfy-hero-persona">
	<!-- wp:group {"className":"gfy-wrapper","layout":{"type":"constrained"}} -->
	<div class="wp-block-group gfy-wrapper">
		<!-- wp:columns {"className":"gfy-content-grid"} -->
		<div class="wp-block-columns gfy-content-grid">
			<!-- wp:column -->
			<div class="wp-block-column">
				<!-- wp:paragraph {"className":"gfy-pre-headline"} -->
				<p class="gfy-pre-headline">[Wound activation statement — e.g., You\'re doing the work but visibility doesn\'t match your value.]</p>
				<!-- /wp:paragraph -->

				<!-- wp:heading {"level":1} -->
				<h1 class="wp-block-heading">[Primary headline — e.g., Stop Being the Best-Kept Secret in Your Industry]</h1>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"className":"gfy-sub-headline"} -->
				<p class="gfy-sub-headline">[Supporting text that connects the wound to the solution. Explain how strategic podcast appearances build the recognized authority this persona needs.]</p>
				<!-- /wp:paragraph -->

				<!-- wp:buttons {"className":"gfy-cta__buttons"} -->
				<div class="wp-block-buttons gfy-cta__buttons">
					<!-- wp:button {"className":"gfy-btn gfy-btn--primary"} -->
					<div class="wp-block-button gfy-btn gfy-btn--primary"><a class="wp-block-button__link wp-element-button" href="/start">Start Free Trial</a></div>
					<!-- /wp:button -->

					<!-- wp:button {"className":"gfy-btn gfy-btn--secondary"} -->
					<div class="wp-block-button gfy-btn gfy-btn--secondary"><a class="wp-block-button__link wp-element-button" href="/demo">Book a Demo</a></div>
					<!-- /wp:button -->
				</div>
				<!-- /wp:buttons -->

				<!-- wp:paragraph {"className":"gfy-cta__reassurance"} -->
				<p class="gfy-cta__reassurance">14-day free trial. No credit card required.</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:column -->

			<!-- wp:column -->
			<div class="wp-block-column">
				<!-- wp:image {"className":"gfy-hero-persona__image"} -->
				<figure class="wp-block-image gfy-hero-persona__image"><img src="/wp-content/uploads/persona-hero-placeholder.png" alt="[Descriptive alt text — e.g., Dashboard showing podcast discovery and authority tracking for consultants]" /></figure>
				<!-- /wp:image -->
			</div>
			<!-- /wp:column -->
		</div>
		<!-- /wp:columns -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->',
);
