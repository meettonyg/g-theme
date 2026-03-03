<?php
/**
 * Title: Homepage — Persona Pathways
 * Slug: guestify/homepage-persona-pathways
 * Categories: guestify-sections
 * Description: Three-column card section for the three Guestify personas: Authority Builders, Revenue Generators, Launch Promoters.
 */

return array(
	'title'       => __( 'Homepage — Persona Pathways', 'guestify' ),
	'categories'  => array( 'guestify-sections' ),
	'description' => __( 'Three-column card section for the three Guestify personas with distinct visual indicators.', 'guestify' ),
	'content'     => '<!-- wp:group {"className":"gfy-section","layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group gfy-section">

	<!-- wp:group {"className":"gfy-section__header","layout":{"type":"constrained","contentSize":"720px"}} -->
	<div class="wp-block-group gfy-section__header">

		<!-- wp:paragraph {"className":"gfy-eyebrow"} -->
		<p class="gfy-eyebrow">YOUR PATH</p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"level":2,"className":"gfy-section__title"} -->
		<h2 class="wp-block-heading gfy-section__title">Choose Your Starting Point</h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"className":"gfy-section__subtitle"} -->
		<p class="gfy-section__subtitle">Whether you\'re building recognition, growing revenue, or launching something new &mdash; Guestify meets you where you are.</p>
		<!-- /wp:paragraph -->

	</div>
	<!-- /wp:group -->

	<!-- wp:columns {"className":"gfy-persona-grid"} -->
	<div class="wp-block-columns gfy-persona-grid">

		<!-- wp:column {"className":"gfy-persona-grid__card gfy-persona-grid__card--recognition"} -->
		<div class="wp-block-column gfy-persona-grid__card gfy-persona-grid__card--recognition">

			<!-- wp:heading {"level":3,"className":"gfy-persona-grid__title"} -->
			<h3 class="wp-block-heading gfy-persona-grid__title">Build Recognition</h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"className":"gfy-persona-grid__desc"} -->
			<p class="gfy-persona-grid__desc">For experts and consultants who are tired of being the best-kept secret in their industry.</p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"className":"gfy-persona-grid__best-for"} -->
			<p class="gfy-persona-grid__best-for"><strong>Best for:</strong> Consultants, coaches, SaaS founders, executives</p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"className":"gfy-persona-grid__link"} -->
			<p class="gfy-persona-grid__link"><a href="/for/experts-consultants">Explore this path &rarr;</a></p>
			<!-- /wp:paragraph -->

		</div>
		<!-- /wp:column -->

		<!-- wp:column {"className":"gfy-persona-grid__card gfy-persona-grid__card--revenue"} -->
		<div class="wp-block-column gfy-persona-grid__card gfy-persona-grid__card--revenue">

			<!-- wp:heading {"level":3,"className":"gfy-persona-grid__title"} -->
			<h3 class="wp-block-heading gfy-persona-grid__title">Grow Revenue</h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"className":"gfy-persona-grid__desc"} -->
			<p class="gfy-persona-grid__desc">For business owners who know relationships drive revenue &mdash; and want to systematize them.</p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"className":"gfy-persona-grid__best-for"} -->
			<p class="gfy-persona-grid__best-for"><strong>Best for:</strong> Service businesses, operators, entrepreneurs</p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"className":"gfy-persona-grid__link"} -->
			<p class="gfy-persona-grid__link"><a href="/for/business-owners">Explore this path &rarr;</a></p>
			<!-- /wp:paragraph -->

		</div>
		<!-- /wp:column -->

		<!-- wp:column {"className":"gfy-persona-grid__card gfy-persona-grid__card--launch"} -->
		<div class="wp-block-column gfy-persona-grid__card gfy-persona-grid__card--launch">

			<!-- wp:heading {"level":3,"className":"gfy-persona-grid__title"} -->
			<h3 class="wp-block-heading gfy-persona-grid__title">Launch &amp; Promote</h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"className":"gfy-persona-grid__desc"} -->
			<p class="gfy-persona-grid__desc">For authors and creators with a launch window that won\'t wait.</p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"className":"gfy-persona-grid__best-for"} -->
			<p class="gfy-persona-grid__best-for"><strong>Best for:</strong> Authors, course creators, speakers</p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"className":"gfy-persona-grid__link"} -->
			<p class="gfy-persona-grid__link"><a href="/for/authors-creators">Explore this path &rarr;</a></p>
			<!-- /wp:paragraph -->

		</div>
		<!-- /wp:column -->

	</div>
	<!-- /wp:columns -->

</div>
<!-- /wp:group -->',
);
