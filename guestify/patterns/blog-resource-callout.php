<?php
/**
 * Title: Blog — Resource Callout
 * Slug: guestify/blog-resource-callout
 * Categories: guestify-blog
 * Description: Callout box promoting a downloadable resource or lead magnet.
 */

return array(
	'title'       => __( 'Blog — Resource Callout', 'guestify' ),
	'categories'  => array( 'guestify-blog' ),
	'description' => __( 'Callout box promoting a downloadable resource or lead magnet.', 'guestify' ),
	'content'     => '<!-- wp:group {"className":"gfy-blog-resource"} -->
<div class="wp-block-group gfy-blog-resource">
	<!-- wp:paragraph {"className":"gfy-blog-resource__icon"} -->
	<p class="gfy-blog-resource__icon">&#128196;</p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"level":4,"className":"gfy-blog-resource__title"} -->
	<h4 class="wp-block-heading gfy-blog-resource__title">[Free Resource] — [Resource Title]</h4>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"className":"gfy-blog-resource__text"} -->
	<p class="gfy-blog-resource__text">[Brief description of what the resource contains and who it helps — e.g., Download our step-by-step guide to crafting podcast pitches that get accepted. Built for consultants, authors, and thought leaders who want to land interviews on top-tier shows.]</p>
	<!-- /wp:paragraph -->

	<!-- wp:buttons {"className":"gfy-blog-resource__action"} -->
	<div class="wp-block-buttons gfy-blog-resource__action">
		<!-- wp:button {"className":"gfy-btn gfy-btn--primary"} -->
		<div class="wp-block-button gfy-btn gfy-btn--primary"><a class="wp-block-button__link wp-element-button" href="/resources/[slug]">Download Free</a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->
</div>
<!-- /wp:group -->',
);
