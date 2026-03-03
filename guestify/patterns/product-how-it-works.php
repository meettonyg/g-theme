<?php
/**
 * Title: Product Page — How It Works
 * Slug: guestify/product-how-it-works
 * Categories: guestify-product
 * Description: Three-step process section for product capabilities.
 */

return array(
	'title'       => __( 'Product Page — How It Works', 'guestify' ),
	'categories'  => array( 'guestify-product' ),
	'description' => __( 'Three-step process section for product capabilities.', 'guestify' ),
	'content'     => '<!-- wp:group {"className":"gfy-section"} -->
<div class="wp-block-group gfy-section">
	<!-- wp:group {"className":"gfy-wrapper","layout":{"type":"constrained"}} -->
	<div class="wp-block-group gfy-wrapper">
		<!-- wp:group {"className":"gfy-section-header","layout":{"type":"constrained"}} -->
		<div class="wp-block-group gfy-section-header" style="text-align:center;">
			<!-- wp:paragraph {"align":"center","className":"gfy-eyebrow"} -->
			<p class="has-text-align-center gfy-eyebrow">HOW IT WORKS</p>
			<!-- /wp:paragraph -->

			<!-- wp:heading {"textAlign":"center","level":2,"className":"gfy-section-header__title"} -->
			<h2 class="wp-block-heading has-text-align-center gfy-section-header__title">Three Steps to [Outcome]</h2>
			<!-- /wp:heading -->
		</div>
		<!-- /wp:group -->

		<!-- wp:columns {"className":"gfy-steps"} -->
		<div class="wp-block-columns gfy-steps">
			<!-- wp:column {"className":"gfy-step"} -->
			<div class="wp-block-column gfy-step">
				<!-- wp:paragraph {"className":"gfy-step__number"} -->
				<p class="gfy-step__number">1</p>
				<!-- /wp:paragraph -->

				<!-- wp:heading {"level":3,"className":"gfy-step__title"} -->
				<h3 class="wp-block-heading gfy-step__title">[First action — e.g., Define Your Authority Goals]</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"className":"gfy-step__description"} -->
				<p class="gfy-step__description">[What happens in this step — e.g., Tell us your expertise, target audience, and the authority position you want to build. Our system maps your ideal podcast landscape.]</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:column -->

			<!-- wp:column {"className":"gfy-step"} -->
			<div class="wp-block-column gfy-step">
				<!-- wp:paragraph {"className":"gfy-step__number"} -->
				<p class="gfy-step__number">2</p>
				<!-- /wp:paragraph -->

				<!-- wp:heading {"level":3,"className":"gfy-step__title"} -->
				<h3 class="wp-block-heading gfy-step__title">[Second action — e.g., Get Matched With the Right Shows]</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"className":"gfy-step__description"} -->
				<p class="gfy-step__description">[What happens in this step — e.g., Receive curated, scored recommendations for shows that align with your expertise and reach your ideal audience.]</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:column -->

			<!-- wp:column {"className":"gfy-step"} -->
			<div class="wp-block-column gfy-step">
				<!-- wp:paragraph {"className":"gfy-step__number"} -->
				<p class="gfy-step__number">3</p>
				<!-- /wp:paragraph -->

				<!-- wp:heading {"level":3,"className":"gfy-step__title"} -->
				<h3 class="wp-block-heading gfy-step__title">[Third action — e.g., Build Authority With Every Interview]</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"className":"gfy-step__description"} -->
				<p class="gfy-step__description">[What happens in this step — e.g., Track your authority growth, manage host relationships, and measure the impact of every interview on your recognition.]</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:column -->
		</div>
		<!-- /wp:columns -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->',
);
