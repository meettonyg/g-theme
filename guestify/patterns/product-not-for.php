<?php
/**
 * Title: Product Page — Not For
 * Slug: guestify/product-not-for
 * Categories: guestify-product
 * Description: Disqualifier section that repels wrong-fit users (per brand canon).
 */

return array(
	'title'       => __( 'Product Page — Not For', 'guestify' ),
	'categories'  => array( 'guestify-product' ),
	'description' => __( 'Disqualifier section that repels wrong-fit users (per brand canon).', 'guestify' ),
	'content'     => '<!-- wp:group {"className":"gfy-section gfy-section--light"} -->
<div class="wp-block-group gfy-section gfy-section--light">
	<!-- wp:group {"className":"gfy-wrapper","layout":{"type":"constrained"}} -->
	<div class="wp-block-group gfy-wrapper" style="text-align:center;">
		<!-- wp:heading {"textAlign":"center","level":3,"className":"gfy-not-for__title"} -->
		<h3 class="wp-block-heading has-text-align-center gfy-not-for__title">This Capability Is Not For Everyone</h3>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"align":"center","className":"gfy-not-for__intro"} -->
		<p class="has-text-align-center gfy-not-for__intro">Guestify is built for strategic, relationship-focused professionals. This specific tool isn\'t the right fit if:</p>
		<!-- /wp:paragraph -->

		<!-- wp:list {"className":"gfy-not-for__list"} -->
		<ul class="gfy-not-for__list">
			<!-- wp:list-item -->
			<li>[Disqualifier 1 — e.g., You want to export thousands of contacts for mass outreach]</li>
			<!-- /wp:list-item -->

			<!-- wp:list-item -->
			<li>[Disqualifier 2 — e.g., You\'re looking for guaranteed podcast placements]</li>
			<!-- /wp:list-item -->

			<!-- wp:list-item -->
			<li>[Disqualifier 3 — e.g., You prefer volume over strategic fit]</li>
			<!-- /wp:list-item -->

			<!-- wp:list-item -->
			<li>[Disqualifier 4 — e.g., You\'re not willing to invest in genuine host relationships]</li>
			<!-- /wp:list-item -->
		</ul>
		<!-- /wp:list -->

		<!-- wp:paragraph {"align":"center","className":"gfy-not-for__closing"} -->
		<p class="has-text-align-center gfy-not-for__closing">If that\'s not you — and you\'re ready to build authority strategically — [this tool] was built for you.</p>
		<!-- /wp:paragraph -->

		<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"className":"gfy-not-for__cta"} -->
		<div class="wp-block-buttons gfy-not-for__cta">
			<!-- wp:button {"className":"gfy-btn gfy-btn--primary"} -->
			<div class="wp-block-button gfy-btn gfy-btn--primary"><a class="wp-block-button__link wp-element-button" href="/start">Start Free Trial</a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->',
);
