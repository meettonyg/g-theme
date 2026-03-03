<?php
/**
 * Title: Homepage — Why Interviews
 * Slug: guestify/homepage-why-interviews
 * Categories: guestify-sections
 * Description: Section explaining why interviews are the fastest trust-transfer channel, with a 4-column comparison grid.
 */

return array(
	'title'       => __( 'Homepage — Why Interviews', 'guestify' ),
	'categories'  => array( 'guestify-sections' ),
	'description' => __( 'Section explaining why interviews are the fastest trust-transfer channel, with a 4-column comparison grid.', 'guestify' ),
	'content'     => '<!-- wp:group {"className":"gfy-section gfy-section--light","layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group gfy-section gfy-section--light">

	<!-- wp:group {"className":"gfy-section__header","layout":{"type":"constrained","contentSize":"720px"}} -->
	<div class="wp-block-group gfy-section__header">

		<!-- wp:paragraph {"className":"gfy-eyebrow"} -->
		<p class="gfy-eyebrow">WHY INTERVIEWS</p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"level":2,"className":"gfy-section__title"} -->
		<h2 class="wp-block-heading gfy-section__title">The Fastest Way to Build Recognized Trust</h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"className":"gfy-section__subtitle"} -->
		<p class="gfy-section__subtitle">When a host invites you on their show, they vouch for you to their audience. The endorsement is built into the format. No other channel transfers trust this efficiently.</p>
		<!-- /wp:paragraph -->

	</div>
	<!-- /wp:group -->

	<!-- wp:columns {"className":"gfy-trust-grid"} -->
	<div class="wp-block-columns gfy-trust-grid">

		<!-- wp:column {"className":"gfy-trust-grid__card"} -->
		<div class="wp-block-column gfy-trust-grid__card">

			<!-- wp:heading {"level":3,"className":"gfy-trust-grid__title"} -->
			<h3 class="wp-block-heading gfy-trust-grid__title">Customer Reviews</h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"className":"gfy-trust-grid__desc"} -->
			<p class="gfy-trust-grid__desc">Slow to accumulate, limited reach. Builds credibility only after someone is already evaluating you.</p>
			<!-- /wp:paragraph -->

		</div>
		<!-- /wp:column -->

		<!-- wp:column {"className":"gfy-trust-grid__card"} -->
		<div class="wp-block-column gfy-trust-grid__card">

			<!-- wp:heading {"level":3,"className":"gfy-trust-grid__title"} -->
			<h3 class="wp-block-heading gfy-trust-grid__title">Peer References</h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"className":"gfy-trust-grid__desc"} -->
			<p class="gfy-trust-grid__desc">Requires existing relationships. Effective one-to-one but impossible to scale.</p>
			<!-- /wp:paragraph -->

		</div>
		<!-- /wp:column -->

		<!-- wp:column {"className":"gfy-trust-grid__card"} -->
		<div class="wp-block-column gfy-trust-grid__card">

			<!-- wp:heading {"level":3,"className":"gfy-trust-grid__title"} -->
			<h3 class="wp-block-heading gfy-trust-grid__title">Association Badges</h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"className":"gfy-trust-grid__desc"} -->
			<p class="gfy-trust-grid__desc">Expensive, passive signal. Recognized within niches but rarely differentiating.</p>
			<!-- /wp:paragraph -->

		</div>
		<!-- /wp:column -->

		<!-- wp:column {"className":"gfy-trust-grid__card gfy-trust-grid__card--highlighted"} -->
		<div class="wp-block-column gfy-trust-grid__card gfy-trust-grid__card--highlighted">

			<!-- wp:heading {"level":3,"className":"gfy-trust-grid__title"} -->
			<h3 class="wp-block-heading gfy-trust-grid__title">Host Endorsements</h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"className":"gfy-trust-grid__desc"} -->
			<p class="gfy-trust-grid__desc">Fast, scalable, trust built in. When a host vouches for you, their audience extends that trust to you immediately.</p>
			<!-- /wp:paragraph -->

		</div>
		<!-- /wp:column -->

	</div>
	<!-- /wp:columns -->

</div>
<!-- /wp:group -->',
);
