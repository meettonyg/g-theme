<?php
/**
 * Title: Product Page — Feature Details
 * Slug: guestify/product-features
 * Categories: guestify-product
 * Description: Alternating feature rows with screenshot and description.
 */

return array(
	'title'       => __( 'Product Page — Feature Details', 'guestify' ),
	'categories'  => array( 'guestify-product' ),
	'description' => __( 'Alternating feature rows with screenshot and description.', 'guestify' ),
	'content'     => '<!-- wp:group {"className":"gfy-section"} -->
<div class="wp-block-group gfy-section">
	<!-- wp:group {"className":"gfy-wrapper","layout":{"type":"constrained"}} -->
	<div class="wp-block-group gfy-wrapper">
		<!-- wp:columns {"className":"gfy-content-grid gfy-feature-row"} -->
		<div class="wp-block-columns gfy-content-grid gfy-feature-row">
			<!-- wp:column {"className":"gfy-content-grid__col gfy-content-grid__col--text"} -->
			<div class="wp-block-column gfy-content-grid__col gfy-content-grid__col--text">
				<!-- wp:heading {"level":3,"className":"gfy-feature-row__title"} -->
				<h3 class="wp-block-heading gfy-feature-row__title">[Feature name — e.g., Smart Podcast Matching]</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"className":"gfy-feature-row__description"} -->
				<p class="gfy-feature-row__description">[Feature description and benefit — e.g., Our algorithm analyzes show topics, audience demographics, and host preferences to surface the podcasts where your expertise will have the most impact. No more guessing which shows are worth pursuing.]</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:column -->

			<!-- wp:column {"className":"gfy-content-grid__col gfy-content-grid__col--media"} -->
			<div class="wp-block-column gfy-content-grid__col gfy-content-grid__col--media">
				<!-- wp:image {"className":"gfy-feature-row__image"} -->
				<figure class="wp-block-image gfy-feature-row__image"><img src="/wp-content/uploads/feature-1-placeholder.png" alt="[Screenshot of feature 1]" /></figure>
				<!-- /wp:image -->
			</div>
			<!-- /wp:column -->
		</div>
		<!-- /wp:columns -->

		<!-- wp:columns {"className":"gfy-content-grid gfy-content-grid--reversed gfy-feature-row"} -->
		<div class="wp-block-columns gfy-content-grid gfy-content-grid--reversed gfy-feature-row">
			<!-- wp:column {"className":"gfy-content-grid__col gfy-content-grid__col--media"} -->
			<div class="wp-block-column gfy-content-grid__col gfy-content-grid__col--media">
				<!-- wp:image {"className":"gfy-feature-row__image"} -->
				<figure class="wp-block-image gfy-feature-row__image"><img src="/wp-content/uploads/feature-2-placeholder.png" alt="[Screenshot of feature 2]" /></figure>
				<!-- /wp:image -->
			</div>
			<!-- /wp:column -->

			<!-- wp:column {"className":"gfy-content-grid__col gfy-content-grid__col--text"} -->
			<div class="wp-block-column gfy-content-grid__col gfy-content-grid__col--text">
				<!-- wp:heading {"level":3,"className":"gfy-feature-row__title"} -->
				<h3 class="wp-block-heading gfy-feature-row__title">[Feature name — e.g., Authority Positioning Angles]</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"className":"gfy-feature-row__description"} -->
				<p class="gfy-feature-row__description">[Feature description and benefit — e.g., For each recommended show, get tailored positioning angles that frame your expertise in ways that resonate with that specific host and audience. Stand out from generic pitches.]</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:column -->
		</div>
		<!-- /wp:columns -->

		<!-- wp:columns {"className":"gfy-content-grid gfy-feature-row"} -->
		<div class="wp-block-columns gfy-content-grid gfy-feature-row">
			<!-- wp:column {"className":"gfy-content-grid__col gfy-content-grid__col--text"} -->
			<div class="wp-block-column gfy-content-grid__col gfy-content-grid__col--text">
				<!-- wp:heading {"level":3,"className":"gfy-feature-row__title"} -->
				<h3 class="wp-block-heading gfy-feature-row__title">[Feature name — e.g., Relationship Intelligence]</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"className":"gfy-feature-row__description"} -->
				<p class="gfy-feature-row__description">[Feature description and benefit — e.g., Track every host interaction, follow-up, and interview outcome in one place. Build genuine relationships that lead to repeat invitations, cross-referrals, and lasting authority in your space.]</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:column -->

			<!-- wp:column {"className":"gfy-content-grid__col gfy-content-grid__col--media"} -->
			<div class="wp-block-column gfy-content-grid__col gfy-content-grid__col--media">
				<!-- wp:image {"className":"gfy-feature-row__image"} -->
				<figure class="wp-block-image gfy-feature-row__image"><img src="/wp-content/uploads/feature-3-placeholder.png" alt="[Screenshot of feature 3]" /></figure>
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
