<?php
/**
 * Title: Persona Page — Framework
 * Slug: guestify/persona-framework
 * Categories: guestify-persona
 * Description: Authority Stack or relevant framework teaching section.
 */

return array(
	'title'       => __( 'Persona Page — Framework', 'guestify' ),
	'categories'  => array( 'guestify-persona' ),
	'description' => __( 'Authority Stack or relevant framework teaching section.', 'guestify' ),
	'content'     => '<!-- wp:group {"className":"gfy-section gfy-section--dark"} -->
<div class="wp-block-group gfy-section gfy-section--dark">
	<!-- wp:group {"className":"gfy-wrapper","layout":{"type":"constrained"}} -->
	<div class="wp-block-group gfy-wrapper">
		<!-- wp:group {"className":"gfy-section-header","layout":{"type":"constrained"}} -->
		<div class="wp-block-group gfy-section-header" style="text-align:center;">
			<!-- wp:paragraph {"align":"center","className":"gfy-section-header__eyebrow"} -->
			<p class="has-text-align-center gfy-section-header__eyebrow">THE FRAMEWORK</p>
			<!-- /wp:paragraph -->

			<!-- wp:heading {"textAlign":"center","level":2} -->
			<h2 class="wp-block-heading has-text-align-center">[How Authority Compounds]</h2>
			<!-- /wp:heading -->
		</div>
		<!-- /wp:group -->

		<!-- wp:html -->
<div class="gfy-authority-stack">
	<div class="gfy-authority-stack__layer gfy-authority-stack__layer--borrowed">
		<div class="gfy-authority-stack__label">
			<span class="gfy-authority-stack__number">1</span>
			<h3 class="gfy-authority-stack__title">Borrowed Authority</h3>
		</div>
		<p class="gfy-authority-stack__description">[How this persona gains access through strategic appearances — e.g., When you appear on a respected podcast, the host\'s credibility transfers to you. Their audience sees you as vetted and trustworthy before you say a word.]</p>
	</div>

	<div class="gfy-authority-stack__layer gfy-authority-stack__layer--articulated">
		<div class="gfy-authority-stack__label">
			<span class="gfy-authority-stack__number">2</span>
			<h3 class="gfy-authority-stack__title">Articulated Authority</h3>
		</div>
		<p class="gfy-authority-stack__description">[How this persona builds clear positioning — e.g., Through strategic interview preparation and messaging frameworks, you develop a clear, compelling point of view that distinguishes you from every other expert in your space.]</p>
	</div>

	<div class="gfy-authority-stack__layer gfy-authority-stack__layer--earned">
		<div class="gfy-authority-stack__label">
			<span class="gfy-authority-stack__number">3</span>
			<h3 class="gfy-authority-stack__title">Earned Authority</h3>
		</div>
		<p class="gfy-authority-stack__description">[How this persona becomes recognized — e.g., Consistent, strategic appearances build a body of work. Over time, you become the name people think of first when your topic comes up. Recognition replaces self-promotion.]</p>
	</div>

	<div class="gfy-authority-stack__layer gfy-authority-stack__layer--demonstrated">
		<div class="gfy-authority-stack__label">
			<span class="gfy-authority-stack__number">4</span>
			<h3 class="gfy-authority-stack__title">Demonstrated Authority</h3>
		</div>
		<p class="gfy-authority-stack__description">[How this persona turns recognition into outcomes — e.g., Authority becomes tangible when it drives results: inbound leads, speaking invitations, partnership opportunities, and revenue growth that compounds over time.]</p>
	</div>
</div>
		<!-- /wp:html -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->',
);
