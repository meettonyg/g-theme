<?php
/**
 * Title: Homepage — FAQ
 * Slug: guestify/homepage-faq
 * Categories: guestify-sections
 * Description: Frequently asked questions section using a CSS-only accordion with details/summary elements.
 */

return array(
	'title'       => __( 'Homepage — FAQ', 'guestify' ),
	'categories'  => array( 'guestify-sections' ),
	'description' => __( 'Frequently asked questions section using a CSS-only accordion with details/summary elements.', 'guestify' ),
	'content'     => '<!-- wp:group {"className":"gfy-section","layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group gfy-section">

	<!-- wp:group {"className":"gfy-section__header","layout":{"type":"constrained","contentSize":"720px"}} -->
	<div class="wp-block-group gfy-section__header">

		<!-- wp:paragraph {"className":"gfy-eyebrow"} -->
		<p class="gfy-eyebrow">FAQ</p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"level":2,"className":"gfy-section__title"} -->
		<h2 class="wp-block-heading gfy-section__title">Common Questions</h2>
		<!-- /wp:heading -->

	</div>
	<!-- /wp:group -->

	<!-- wp:html -->
<div class="gfy-faq">

	<details class="gfy-faq__item">
		<summary class="gfy-faq__question">What do you mean by &ldquo;Authority Is Recognized Trust&rdquo;?</summary>
		<div class="gfy-faq__answer">
			<p>Authority isn\'t about having the most followers or publishing the most content. It\'s about being recognized as trustworthy by the people who matter in your market. When a podcast host invites you on their show, they transfer their trust to you. That\'s recognized trust &mdash; and it compounds with every relationship.</p>
		</div>
	</details>

	<details class="gfy-faq__item">
		<summary class="gfy-faq__question">How is this different from a podcast booking service?</summary>
		<div class="gfy-faq__answer">
			<p>Booking services find you shows and pitch on your behalf. You get appearances, but you don\'t own the relationships, the positioning, or the process. Guestify gives you the infrastructure to do it yourself &mdash; strategically, systematically, and in a way that compounds over time.</p>
		</div>
	</details>

	<details class="gfy-faq__item">
		<summary class="gfy-faq__question">I\'ve done podcast interviews before and they didn\'t generate results. Why would this be different?</summary>
		<div class="gfy-faq__answer">
			<p>Most interviews don\'t generate results because they lack infrastructure &mdash; no strategic targeting, no positioning system, no follow-up process, and no way to turn appearances into relationships. Guestify provides the end-to-end system that makes each interview build on the last.</p>
		</div>
	</details>

	<details class="gfy-faq__item">
		<summary class="gfy-faq__question">Is this for people who want to be on hundreds of podcasts?</summary>
		<div class="gfy-faq__answer">
			<p>No. Guestify is designed for people who want to be on the right ten shows &mdash; not a thousand random ones. This is about strategic positioning and relationship quality, not volume.</p>
		</div>
	</details>

	<details class="gfy-faq__item">
		<summary class="gfy-faq__question">What if I\'m not comfortable with self-promotion?</summary>
		<div class="gfy-faq__answer">
			<p>Good &mdash; because this isn\'t self-promotion. When a host invites you on their show, they\'re endorsing you. You\'re not selling; you\'re sharing expertise in a format where credibility is built in. Most of our users are experts who dislike self-promotion but love sharing what they know.</p>
		</div>
	</details>

</div>
	<!-- /wp:html -->

</div>
<!-- /wp:group -->',
);
