<?php
/**
 * Title: Blog — Author Box
 * Slug: guestify/blog-author-box
 * Categories: guestify-blog
 * Description: Author bio box for end of blog posts.
 */

return array(
	'title'       => __( 'Blog — Author Box', 'guestify' ),
	'categories'  => array( 'guestify-blog' ),
	'description' => __( 'Author bio box for end of blog posts.', 'guestify' ),
	'content'     => '<!-- wp:group {"className":"gfy-author-box"} -->
<div class="wp-block-group gfy-author-box">
	<!-- wp:columns {"className":"gfy-author-box__layout"} -->
	<div class="wp-block-columns gfy-author-box__layout">
		<!-- wp:column {"width":"80px","className":"gfy-author-box__avatar-col"} -->
		<div class="wp-block-column gfy-author-box__avatar-col" style="flex-basis:80px;">
			<!-- wp:image {"width":"80px","height":"80px","className":"gfy-author-box__avatar"} -->
			<figure class="wp-block-image gfy-author-box__avatar"><img src="/wp-content/uploads/author-avatar-placeholder.png" alt="[Author Name]" style="width:80px;height:80px;border-radius:9999px;object-fit:cover;" /></figure>
			<!-- /wp:image -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"className":"gfy-author-box__info"} -->
		<div class="wp-block-column gfy-author-box__info">
			<!-- wp:heading {"level":4,"className":"gfy-author-box__name"} -->
			<h4 class="wp-block-heading gfy-author-box__name">[Author Name]</h4>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"className":"gfy-author-box__bio"} -->
			<p class="gfy-author-box__bio">[Author bio — e.g., Tony Guarnaccia is the founder of Guestify and creator of the Interview Authority System. He helps experts build recognized authority through strategic podcast interviews.]</p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"className":"gfy-author-box__links"} -->
			<p class="gfy-author-box__links"><a href="[twitter-url]">Twitter/X</a> &nbsp; <a href="[linkedin-url]">LinkedIn</a> &nbsp; <a href="[website-url]">Website</a></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->',
);
