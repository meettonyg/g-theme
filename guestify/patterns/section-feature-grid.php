<?php
/**
 * Title: Section — Feature Grid
 * Slug: guestify/section-feature-grid
 * Categories: guestify-sections
 * Description: Three-column grid of feature cards with icon, title, and description.
 */

return array(
	'title'       => __( 'Section — Feature Grid', 'guestify' ),
	'categories'  => array( 'guestify-sections' ),
	'description' => __( 'Three-column grid of feature cards with icon, title, and description.', 'guestify' ),
	'content'     => '<!-- wp:group {"className":"gfy-section"} -->
<div class="wp-block-group gfy-section">
	<!-- wp:group {"className":"gfy-wrapper","layout":{"type":"constrained"}} -->
	<div class="wp-block-group gfy-wrapper">
		<!-- wp:group {"className":"gfy-section-header","layout":{"type":"constrained"}} -->
		<div class="wp-block-group gfy-section-header" style="text-align:center;">
			<!-- wp:heading {"textAlign":"center","level":2} -->
			<h2 class="wp-block-heading has-text-align-center">Key Capabilities</h2>
			<!-- /wp:heading -->
		</div>
		<!-- /wp:group -->

		<!-- wp:columns {"className":"gfy-feature-grid"} -->
		<div class="wp-block-columns gfy-feature-grid">
			<!-- wp:column {"className":"gfy-feature-card"} -->
			<div class="wp-block-column gfy-feature-card">
				<!-- wp:html -->
<div class="gfy-feature-card__icon"><i class="fa-solid fa-magnifying-glass"></i></div>
				<!-- /wp:html -->

				<!-- wp:heading {"level":3,"className":"gfy-feature-card__title"} -->
				<h3 class="wp-block-heading gfy-feature-card__title">Intelligent Discovery</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"className":"gfy-feature-card__description"} -->
				<p class="gfy-feature-card__description">Find the right shows for your expertise and audience. AI-powered search analyzes topics, listener demographics, and host alignment so every pitch goes to a show that fits.</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:column -->

			<!-- wp:column {"className":"gfy-feature-card"} -->
			<div class="wp-block-column gfy-feature-card">
				<!-- wp:html -->
<div class="gfy-feature-card__icon"><i class="fa-solid fa-bullseye"></i></div>
				<!-- /wp:html -->

				<!-- wp:heading {"level":3,"className":"gfy-feature-card__title"} -->
				<h3 class="wp-block-heading gfy-feature-card__title">Strategic Positioning</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"className":"gfy-feature-card__description"} -->
				<p class="gfy-feature-card__description">Craft pitches that resonate. Position your expertise in ways that speak directly to what hosts and their audiences care about, so you stand out from generic guest requests.</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:column -->

			<!-- wp:column {"className":"gfy-feature-card"} -->
			<div class="wp-block-column gfy-feature-card">
				<!-- wp:html -->
<div class="gfy-feature-card__icon"><i class="fa-solid fa-handshake"></i></div>
				<!-- /wp:html -->

				<!-- wp:heading {"level":3,"className":"gfy-feature-card__title"} -->
				<h3 class="wp-block-heading gfy-feature-card__title">Relationship Management</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"className":"gfy-feature-card__description"} -->
				<p class="gfy-feature-card__description">Turn one-time appearances into lasting professional relationships. Track every interaction, follow up at the right time, and build a network of advocates who amplify your message.</p>
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
