<?php
/**
 * Title: Product Page — Problem / Solution
 * Slug: guestify/product-problem-solution
 * Categories: guestify-product
 * Description: Split section showing the problem on left and solution on right.
 */

return array(
	'title'       => __( 'Product Page — Problem / Solution', 'guestify' ),
	'categories'  => array( 'guestify-product' ),
	'description' => __( 'Split section showing the problem on left and solution on right.', 'guestify' ),
	'content'     => '<!-- wp:group {"className":"gfy-section gfy-section--light"} -->
<div class="wp-block-group gfy-section gfy-section--light">
	<!-- wp:group {"className":"gfy-wrapper","layout":{"type":"constrained"}} -->
	<div class="wp-block-group gfy-wrapper">
		<!-- wp:columns {"className":"gfy-content-grid"} -->
		<div class="wp-block-columns gfy-content-grid">
			<!-- wp:column {"className":"gfy-content-grid__col gfy-problem-solution__problem"} -->
			<div class="wp-block-column gfy-content-grid__col gfy-problem-solution__problem">
				<!-- wp:group {"className":"gfy-problem-solution__card gfy-problem-solution__card--negative"} -->
				<div class="wp-block-group gfy-problem-solution__card gfy-problem-solution__card--negative">
					<!-- wp:heading {"level":3,"className":"gfy-problem-solution__heading"} -->
					<h3 class="wp-block-heading gfy-problem-solution__heading">Without Guestify</h3>
					<!-- /wp:heading -->

					<!-- wp:list {"className":"gfy-problem-solution__list"} -->
					<ul class="gfy-problem-solution__list">
						<!-- wp:list-item -->
						<li>[Manual process pain point — e.g., Hours spent searching podcasts one by one across multiple platforms]</li>
						<!-- /wp:list-item -->

						<!-- wp:list-item -->
						<li>[Inefficiency pain point — e.g., Generic pitches that get ignored by hosts who receive hundreds of requests]</li>
						<!-- /wp:list-item -->

						<!-- wp:list-item -->
						<li>[Missed opportunity pain point — e.g., No way to track which shows align with your expertise and audience]</li>
						<!-- /wp:list-item -->

						<!-- wp:list-item -->
						<li>[Frustration pain point — e.g., Wasted interviews on shows that don\'t move the needle for your authority]</li>
						<!-- /wp:list-item -->
					</ul>
					<!-- /wp:list -->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:column -->

			<!-- wp:column {"className":"gfy-content-grid__col gfy-problem-solution__solution"} -->
			<div class="wp-block-column gfy-content-grid__col gfy-problem-solution__solution">
				<!-- wp:group {"className":"gfy-problem-solution__card gfy-problem-solution__card--positive"} -->
				<div class="wp-block-group gfy-problem-solution__card gfy-problem-solution__card--positive">
					<!-- wp:heading {"level":3,"className":"gfy-problem-solution__heading"} -->
					<h3 class="wp-block-heading gfy-problem-solution__heading">With Guestify</h3>
					<!-- /wp:heading -->

					<!-- wp:list {"className":"gfy-problem-solution__list"} -->
					<ul class="gfy-problem-solution__list">
						<!-- wp:list-item -->
						<li>[Automated solution — e.g., AI-powered matching surfaces the right shows for your expertise in seconds]</li>
						<!-- /wp:list-item -->

						<!-- wp:list-item -->
						<li>[Efficiency gain — e.g., Personalized positioning angles generated for each show and host]</li>
						<!-- /wp:list-item -->

						<!-- wp:list-item -->
						<li>[Outcome achieved — e.g., Strategic fit scoring ensures every interview builds toward your authority goals]</li>
						<!-- /wp:list-item -->

						<!-- wp:list-item -->
						<li>[Lasting value — e.g., Relationship tracking turns one-time interviews into ongoing partnerships]</li>
						<!-- /wp:list-item -->
					</ul>
					<!-- /wp:list -->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:column -->
		</div>
		<!-- /wp:columns -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->',
);
