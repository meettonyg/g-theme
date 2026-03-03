<?php
/**
 * Title: Persona Page — Objection Handling
 * Slug: guestify/persona-objections
 * Categories: guestify-persona
 * Description: FAQ-style section addressing persona-specific objections.
 */

return array(
	'title'       => __( 'Persona Page — Objection Handling', 'guestify' ),
	'categories'  => array( 'guestify-persona' ),
	'description' => __( 'FAQ-style section addressing persona-specific objections.', 'guestify' ),
	'content'     => '<!-- wp:group {"className":"gfy-section gfy-section--light"} -->
<div class="wp-block-group gfy-section gfy-section--light">
	<!-- wp:group {"className":"gfy-wrapper","layout":{"type":"constrained"}} -->
	<div class="wp-block-group gfy-wrapper">
		<!-- wp:group {"className":"gfy-section-header","layout":{"type":"constrained"}} -->
		<div class="wp-block-group gfy-section-header" style="text-align:center;">
			<!-- wp:paragraph {"align":"center","className":"gfy-section-header__eyebrow"} -->
			<p class="has-text-align-center gfy-section-header__eyebrow">COMMON CONCERNS</p>
			<!-- /wp:paragraph -->

			<!-- wp:heading {"textAlign":"center","level":2} -->
			<h2 class="wp-block-heading has-text-align-center">Questions You Might Have</h2>
			<!-- /wp:heading -->
		</div>
		<!-- /wp:group -->

		<!-- wp:html -->
<div class="gfy-faq">
	<details class="gfy-faq__item">
		<summary class="gfy-faq__question">[Common objection 1 — e.g., This looks like a lot of work]</summary>
		<div class="gfy-faq__answer">
			<p>[Response reframing the objection — e.g., Guestify replaces scattered, manual effort with a structured system. Instead of spending hours researching shows, writing cold pitches, and tracking follow-ups in spreadsheets, you have one platform that handles the heavy lifting. Most users spend less time on outreach with Guestify than they did without it — and get significantly better results.]</p>
		</div>
	</details>

	<details class="gfy-faq__item">
		<summary class="gfy-faq__question">[Common objection 2 — e.g., Can\'t you just book me on podcasts?]</summary>
		<div class="gfy-faq__answer">
			<p>[Response explaining the infrastructure vs service difference — e.g., Booking services put you on shows. Guestify builds the infrastructure that makes every appearance count. The difference: a booking gets you one interview. An authority-building system turns that interview into compounding recognition, relationships, and revenue. We give you the tools and system to own this process long-term.]</p>
		</div>
	</details>

	<details class="gfy-faq__item">
		<summary class="gfy-faq__question">[Common objection 3 — e.g., I need clients, not visibility]</summary>
		<div class="gfy-faq__answer">
			<p>[Response introducing the Recognition-to-Revenue path — e.g., We hear this often, and we agree — visibility without outcomes is vanity. That\'s why Guestify is built around the 3 R\'s: Recognition, Revenue, and Reach. Every feature is designed to move you from being seen to being trusted to being hired. Authority is the bridge between visibility and revenue.]</p>
		</div>
	</details>
</div>
		<!-- /wp:html -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->',
);
