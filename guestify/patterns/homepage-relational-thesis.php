<?php
/**
 * Title: Homepage — The Relational Thesis
 * Slug: guestify/homepage-relational-thesis
 * Categories: guestify-sections
 * Description: Section presenting the paradigm shift from the Attention Economy to the Authority Economy, with a comparison table.
 */

return array(
	'title'       => __( 'Homepage — The Relational Thesis', 'guestify' ),
	'categories'  => array( 'guestify-sections' ),
	'description' => __( 'Section presenting the paradigm shift from the Attention Economy to the Authority Economy, with a comparison table.', 'guestify' ),
	'content'     => '<!-- wp:group {"className":"gfy-section","layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group gfy-section">

	<!-- wp:group {"className":"gfy-section__header","layout":{"type":"constrained","contentSize":"720px"}} -->
	<div class="wp-block-group gfy-section__header">

		<!-- wp:paragraph {"className":"gfy-eyebrow"} -->
		<p class="gfy-eyebrow">THE NEW PARADIGM</p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"level":2,"className":"gfy-section__title"} -->
		<h2 class="wp-block-heading gfy-section__title">Authority Is Relational, Not Transactional</h2>
		<!-- /wp:heading -->

	</div>
	<!-- /wp:group -->

	<!-- wp:columns {"className":"gfy-thesis-grid"} -->
	<div class="wp-block-columns gfy-thesis-grid">

		<!-- wp:column {"className":"gfy-thesis-grid__text"} -->
		<div class="wp-block-column gfy-thesis-grid__text">

			<!-- wp:paragraph -->
			<p>The old model treated interviews as transactions: pitch, appear, move on. Success was measured in downloads and impressions. But downloads don\'t build authority. Relationships do.</p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph -->
			<p>The Attention Economy rewards the loudest voice. The Authority Economy rewards the most trusted one.</p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph -->
			<p>When you shift from collecting appearances to building relationships, every interview becomes a compounding asset. Hosts become advocates. Audiences become communities. And trust becomes the currency that unlocks revenue, partnerships, and long-term recognition.</p>
			<!-- /wp:paragraph -->

		</div>
		<!-- /wp:column -->

		<!-- wp:column {"className":"gfy-thesis-grid__comparison"} -->
		<div class="wp-block-column gfy-thesis-grid__comparison">

			<!-- wp:html -->
<table class="gfy-comparison-card">
	<thead>
		<tr>
			<th></th>
			<th class="gfy-comparison-card__col--old">Transactional Player</th>
			<th class="gfy-comparison-card__col--new">Relational Player</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="gfy-comparison-card__label">Treats appearances as</td>
			<td class="gfy-comparison-card__col--old">Campaigns</td>
			<td class="gfy-comparison-card__col--new">Relationships</td>
		</tr>
		<tr>
			<td class="gfy-comparison-card__label">Measures success by</td>
			<td class="gfy-comparison-card__col--old">Downloads</td>
			<td class="gfy-comparison-card__col--new">Partnerships formed</td>
		</tr>
		<tr>
			<td class="gfy-comparison-card__label">Follow-up strategy</td>
			<td class="gfy-comparison-card__col--old">None</td>
			<td class="gfy-comparison-card__col--new">Systematic nurturing</td>
		</tr>
		<tr>
			<td class="gfy-comparison-card__label">Result</td>
			<td class="gfy-comparison-card__col--old">Burns through opportunities</td>
			<td class="gfy-comparison-card__col--new">Compounds trust over time</td>
		</tr>
	</tbody>
</table>
			<!-- /wp:html -->

		</div>
		<!-- /wp:column -->

	</div>
	<!-- /wp:columns -->

</div>
<!-- /wp:group -->',
);
