<?php
/**
 * Title: Section — Stats Bar
 * Slug: guestify/section-stats-bar
 * Categories: guestify-sections
 * Description: Four statistics in a row with large numbers and labels.
 */

return array(
	'title'       => __( 'Section — Stats Bar', 'guestify' ),
	'categories'  => array( 'guestify-sections' ),
	'description' => __( 'Four statistics in a row with large numbers and labels.', 'guestify' ),
	'content'     => '<!-- wp:group {"className":"gfy-section gfy-section--dark"} -->
<div class="wp-block-group gfy-section gfy-section--dark">
	<!-- wp:group {"className":"gfy-wrapper","layout":{"type":"constrained"}} -->
	<div class="wp-block-group gfy-wrapper">
		<!-- wp:columns {"className":"gfy-stats"} -->
		<div class="wp-block-columns gfy-stats">
			<!-- wp:column {"className":"gfy-stats__item"} -->
			<div class="wp-block-column gfy-stats__item" style="text-align:center;">
				<!-- wp:heading {"textAlign":"center","level":3,"className":"gfy-stats__number"} -->
				<h3 class="wp-block-heading has-text-align-center gfy-stats__number">25+</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"align":"center","className":"gfy-stats__label"} -->
				<p class="has-text-align-center gfy-stats__label">AI-Powered Tools</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:column -->

			<!-- wp:column {"className":"gfy-stats__item"} -->
			<div class="wp-block-column gfy-stats__item" style="text-align:center;">
				<!-- wp:heading {"textAlign":"center","level":3,"className":"gfy-stats__number"} -->
				<h3 class="wp-block-heading has-text-align-center gfy-stats__number">5</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"align":"center","className":"gfy-stats__label"} -->
				<p class="has-text-align-center gfy-stats__label">Integrated Stages</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:column -->

			<!-- wp:column {"className":"gfy-stats__item"} -->
			<div class="wp-block-column gfy-stats__item" style="text-align:center;">
				<!-- wp:heading {"textAlign":"center","level":3,"className":"gfy-stats__number"} -->
				<h3 class="wp-block-heading has-text-align-center gfy-stats__number">4</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"align":"center","className":"gfy-stats__label"} -->
				<p class="has-text-align-center gfy-stats__label">Authority Types</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:column -->

			<!-- wp:column {"className":"gfy-stats__item"} -->
			<div class="wp-block-column gfy-stats__item" style="text-align:center;">
				<!-- wp:heading {"textAlign":"center","level":3,"className":"gfy-stats__number"} -->
				<h3 class="wp-block-heading has-text-align-center gfy-stats__number">1</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"align":"center","className":"gfy-stats__label"} -->
				<p class="has-text-align-center gfy-stats__label">Complete System</p>
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
