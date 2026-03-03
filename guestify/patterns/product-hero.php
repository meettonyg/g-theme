<?php
/**
 * Title: Product Page — Hero
 * Slug: guestify/product-hero
 * Categories: guestify-product
 * Description: Product page hero with promise headline and product screenshot.
 */

return array(
	'title'       => __( 'Product Page — Hero', 'guestify' ),
	'categories'  => array( 'guestify-product' ),
	'description' => __( 'Product page hero with promise headline and product screenshot.', 'guestify' ),
	'content'     => '<!-- wp:group {"className":"gfy-section gfy-section--hero"} -->
<div class="wp-block-group gfy-section gfy-section--hero">
	<!-- wp:group {"className":"gfy-wrapper","layout":{"type":"constrained"}} -->
	<div class="wp-block-group gfy-wrapper">
		<!-- wp:columns {"className":"gfy-content-grid"} -->
		<div class="wp-block-columns gfy-content-grid">
			<!-- wp:column {"className":"gfy-content-grid__col gfy-content-grid__col--text"} -->
			<div class="wp-block-column gfy-content-grid__col gfy-content-grid__col--text">
				<!-- wp:paragraph {"className":"gfy-pre-headline"} -->
				<p class="gfy-pre-headline">[CAPABILITY NAME — e.g., PODCAST DISCOVERY]</p>
				<!-- /wp:paragraph -->

				<!-- wp:heading {"level":1,"className":"gfy-hero__title"} -->
				<h1 class="wp-block-heading gfy-hero__title">[Promise headline — e.g., Find the 10 Right Shows — Not 1,000 Random Ones]</h1>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"className":"gfy-sub-headline"} -->
				<p class="gfy-sub-headline">[Supporting text explaining what this capability does and why it matters]</p>
				<!-- /wp:paragraph -->

				<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"left"},"className":"gfy-hero__cta"} -->
				<div class="wp-block-buttons gfy-hero__cta">
					<!-- wp:button {"className":"gfy-btn gfy-btn--primary"} -->
					<div class="wp-block-button gfy-btn gfy-btn--primary"><a class="wp-block-button__link wp-element-button" href="/start">Start Free Trial</a></div>
					<!-- /wp:button -->

					<!-- wp:button {"className":"gfy-btn gfy-btn--secondary"} -->
					<div class="wp-block-button gfy-btn gfy-btn--secondary"><a class="wp-block-button__link wp-element-button" href="/demo">Book a Demo</a></div>
					<!-- /wp:button -->
				</div>
				<!-- /wp:buttons -->
			</div>
			<!-- /wp:column -->

			<!-- wp:column {"className":"gfy-content-grid__col gfy-content-grid__col--media"} -->
			<div class="wp-block-column gfy-content-grid__col gfy-content-grid__col--media">
				<!-- wp:image {"className":"gfy-hero__image"} -->
				<figure class="wp-block-image gfy-hero__image"><img src="/wp-content/uploads/product-screenshot-placeholder.png" alt="[Product screenshot of capability]" /></figure>
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
