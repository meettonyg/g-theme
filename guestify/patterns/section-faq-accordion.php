<?php
/**
 * Title: Section — FAQ Accordion
 * Slug: guestify/section-faq-accordion
 * Categories: guestify-sections
 * Description: Expandable FAQ section using HTML details/summary elements.
 */

return array(
	'title'       => __( 'Section — FAQ Accordion', 'guestify' ),
	'categories'  => array( 'guestify-sections' ),
	'description' => __( 'Expandable FAQ section using HTML details/summary elements.', 'guestify' ),
	'content'     => '<!-- wp:group {"className":"gfy-section"} -->
<div class="wp-block-group gfy-section">
	<!-- wp:group {"className":"gfy-wrapper","layout":{"type":"constrained"}} -->
	<div class="wp-block-group gfy-wrapper">
		<!-- wp:group {"className":"gfy-section-header","layout":{"type":"constrained"}} -->
		<div class="wp-block-group gfy-section-header" style="text-align:center;">
			<!-- wp:paragraph {"align":"center","className":"gfy-section-header__eyebrow"} -->
			<p class="has-text-align-center gfy-section-header__eyebrow">FAQ</p>
			<!-- /wp:paragraph -->

			<!-- wp:heading {"textAlign":"center","level":2} -->
			<h2 class="wp-block-heading has-text-align-center">Frequently Asked Questions</h2>
			<!-- /wp:heading -->
		</div>
		<!-- /wp:group -->

		<!-- wp:html -->
<div class="gfy-faq">
	<details class="gfy-faq__item">
		<summary class="gfy-faq__question">How does Guestify work?</summary>
		<div class="gfy-faq__answer">
			<p>Guestify provides a complete system for building authority through podcast guest appearances. You start by discovering shows aligned with your expertise, then craft strategic positioning with AI-powered tools, conduct personalized outreach to hosts, and track your progress across every stage &mdash; from first contact to lasting relationship.</p>
		</div>
	</details>

	<details class="gfy-faq__item">
		<summary class="gfy-faq__question">Who is Guestify for?</summary>
		<div class="gfy-faq__answer">
			<p>Guestify is built for experts, consultants, business owners, and authors who want to build recognized authority in their field. If you have valuable knowledge and want the right audiences to discover it through strategic podcast appearances, Guestify gives you the infrastructure to make that happen consistently.</p>
		</div>
	</details>

	<details class="gfy-faq__item">
		<summary class="gfy-faq__question">How long until I see results?</summary>
		<div class="gfy-faq__answer">
			<p>Results depend on your engagement level, niche, and how actively you use the system. Most users begin landing their first interviews within 2&ndash;4 weeks. Authority compounds over time &mdash; the more strategically you show up, the faster recognition builds.</p>
		</div>
	</details>

	<details class="gfy-faq__item">
		<summary class="gfy-faq__question">Do you guarantee podcast placements?</summary>
		<div class="gfy-faq__answer">
			<p>No &mdash; and that\'s by design. Guaranteed placements usually mean low-quality shows that won\'t move the needle for your authority. Guestify gives you the tools, intelligence, and system to earn appearances on shows that actually matter to your audience and goals.</p>
		</div>
	</details>

	<details class="gfy-faq__item">
		<summary class="gfy-faq__question">Can I cancel anytime?</summary>
		<div class="gfy-faq__answer">
			<p>Yes, you can cancel your subscription at any time. There are no long-term contracts or cancellation fees. Your data remains accessible through the end of your billing period.</p>
		</div>
	</details>
</div>
		<!-- /wp:html -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->',
);
