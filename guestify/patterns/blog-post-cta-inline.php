<?php
/**
 * Title: Blog — Inline CTA
 * Slug: guestify/blog-post-cta-inline
 * Categories: guestify-blog
 * Description: Inline CTA box for blog posts that promotes the product contextually.
 */

return array(
	'title'       => __( 'Blog — Inline CTA', 'guestify' ),
	'categories'  => array( 'guestify-blog' ),
	'description' => __( 'Inline CTA box for blog posts that promotes the product contextually.', 'guestify' ),
	'content'     => '<!-- wp:group {"className":"gfy-blog-cta"} -->
<div class="wp-block-group gfy-blog-cta">
	<!-- wp:heading {"level":4,"className":"gfy-blog-cta__title"} -->
	<h4 class="wp-block-heading gfy-blog-cta__title">Build Authority Through Strategic Podcast Interviews</h4>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"className":"gfy-blog-cta__text"} -->
	<p class="gfy-blog-cta__text">Guestify is the Interview Authority System that helps experts find the right shows, position their expertise, and turn interviews into lasting authority.</p>
	<!-- /wp:paragraph -->

	<!-- wp:buttons {"className":"gfy-blog-cta__action"} -->
	<div class="wp-block-buttons gfy-blog-cta__action">
		<!-- wp:button {"className":"gfy-btn gfy-btn--primary"} -->
		<div class="wp-block-button gfy-btn gfy-btn--primary"><a class="wp-block-button__link wp-element-button" href="/start">Start Your Free Trial</a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->
</div>
<!-- /wp:group -->',
);
