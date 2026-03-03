<?php
/**
 * Title: Homepage — Hero (Tabbed)
 * Slug: guestify/homepage-hero-tabbed
 * Categories: guestify-hero
 * Description: A CSS-only tabbed hero section with three persona tabs: Build Recognition, Grow Revenue, Launch & Promote.
 */

return array(
	'title'       => __( 'Homepage — Hero (Tabbed)', 'guestify' ),
	'categories'  => array( 'guestify-hero' ),
	'description' => __( 'A CSS-only tabbed hero section with three persona tabs: Build Recognition, Grow Revenue, Launch & Promote.', 'guestify' ),
	'content'     => '<!-- wp:html -->
<style>
.gfy-hero-tabs {
	max-width: 1200px;
	margin: 0 auto;
	padding: 4rem 1.5rem;
}
.gfy-hero-tabs__radio {
	position: absolute;
	opacity: 0;
	pointer-events: none;
}
.gfy-hero-tabs__buttons {
	display: flex;
	gap: 0.5rem;
	justify-content: center;
	margin-bottom: 2.5rem;
}
.gfy-hero-tabs__btn {
	display: inline-block;
	padding: 0.75rem 1.5rem;
	border: 2px solid var(--gfy-color-primary-800, #1a2744);
	border-radius: 6px;
	background: transparent;
	color: var(--gfy-color-primary-800, #1a2744);
	font-weight: 600;
	font-size: 1rem;
	cursor: pointer;
	transition: background 0.2s, color 0.2s;
}
#tab-authority:checked ~ .gfy-hero-tabs__buttons label[for="tab-authority"],
#tab-revenue:checked ~ .gfy-hero-tabs__buttons label[for="tab-revenue"],
#tab-launch:checked ~ .gfy-hero-tabs__buttons label[for="tab-launch"] {
	background: var(--gfy-color-primary-800, #1a2744);
	color: #fff;
}
.gfy-hero-tabs__panels {
	position: relative;
}
.gfy-hero-tabs__panel {
	display: none;
}
#tab-authority:checked ~ .gfy-hero-tabs__panels #panel-authority,
#tab-revenue:checked ~ .gfy-hero-tabs__panels #panel-revenue,
#tab-launch:checked ~ .gfy-hero-tabs__panels #panel-launch {
	display: block;
}
.gfy-hero-tabs__grid {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 3rem;
	align-items: center;
}
@media (max-width: 768px) {
	.gfy-hero-tabs__grid {
		grid-template-columns: 1fr;
	}
}
.gfy-hero-tabs__copy {
	display: flex;
	flex-direction: column;
	gap: 1rem;
}
.gfy-pre-headline {
	font-size: 1rem;
	color: var(--gfy-color-neutral-600, #6b7280);
	line-height: 1.6;
	margin: 0;
}
.gfy-hero-tabs__copy h1 {
	font-size: 2.75rem;
	line-height: 1.15;
	color: var(--gfy-color-primary-900, #0f172a);
	margin: 0;
}
.gfy-sub-headline {
	font-size: 1.125rem;
	color: var(--gfy-color-neutral-700, #4b5563);
	line-height: 1.7;
	margin: 0;
}
.gfy-hero-tabs__cta {
	display: flex;
	gap: 1rem;
	margin-top: 0.5rem;
	flex-wrap: wrap;
}
.gfy-btn.gfy-btn-cta {
	display: inline-block;
	padding: 0.875rem 2rem;
	background: var(--gfy-color-primary-800, #1a2744);
	color: #fff;
	border-radius: 6px;
	font-weight: 600;
	font-size: 1rem;
	text-decoration: none;
	transition: background 0.2s;
}
.gfy-btn.gfy-btn-cta:hover {
	background: var(--gfy-color-primary-900, #0f172a);
}
.gfy-btn.gfy-btn-secondary {
	display: inline-block;
	padding: 0.875rem 2rem;
	background: transparent;
	color: var(--gfy-color-primary-800, #1a2744);
	border: 2px solid var(--gfy-color-primary-800, #1a2744);
	border-radius: 6px;
	font-weight: 600;
	font-size: 1rem;
	text-decoration: none;
	transition: background 0.2s, color 0.2s;
}
.gfy-btn.gfy-btn-secondary:hover {
	background: var(--gfy-color-primary-800, #1a2744);
	color: #fff;
}
.gfy-hero-tabs__reassurance {
	font-size: 0.875rem;
	color: var(--gfy-color-neutral-500, #9ca3af);
	margin-top: 0.25rem;
}
.gfy-hero-tabs__visual img {
	width: 100%;
	height: auto;
	border-radius: 12px;
	box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
}
</style>

<section class="gfy-hero-tabs">
	<input type="radio" name="gfy-hero-tab" id="tab-authority" class="gfy-hero-tabs__radio" checked />
	<input type="radio" name="gfy-hero-tab" id="tab-revenue" class="gfy-hero-tabs__radio" />
	<input type="radio" name="gfy-hero-tab" id="tab-launch" class="gfy-hero-tabs__radio" />

	<div class="gfy-hero-tabs__buttons">
		<label for="tab-authority" class="gfy-hero-tabs__btn">Build Recognition</label>
		<label for="tab-revenue" class="gfy-hero-tabs__btn">Grow Revenue</label>
		<label for="tab-launch" class="gfy-hero-tabs__btn">Launch &amp; Promote</label>
	</div>

	<div class="gfy-hero-tabs__panels">
		<div id="panel-authority" class="gfy-hero-tabs__panel">
			<div class="gfy-hero-tabs__grid">
				<div class="gfy-hero-tabs__copy">
					<p class="gfy-pre-headline">You\'re doing the work. You have the expertise. But visibility doesn\'t match your value.</p>
					<h1>Be recognized as the trusted voice</h1>
					<p class="gfy-sub-headline">Find high-authority shows, position yourself as an expert, and build host relationships &mdash; reaching the right audience with lasting influence.</p>
					<div class="gfy-hero-tabs__cta">
						<a href="/start" class="gfy-btn gfy-btn-cta">Start Free Trial</a>
						<a href="/demo" class="gfy-btn gfy-btn-secondary">Book a Demo</a>
					</div>
					<p class="gfy-hero-tabs__reassurance">14-day free trial. No credit card required.</p>
				</div>
				<div class="gfy-hero-tabs__visual">
					<img src="/wp-content/uploads/build-authority.png" alt="Authority analytics dashboard" />
				</div>
			</div>
		</div>

		<div id="panel-revenue" class="gfy-hero-tabs__panel">
			<div class="gfy-hero-tabs__grid">
				<div class="gfy-hero-tabs__copy">
					<p class="gfy-pre-headline">You\'ve been networking for years but deals still feel random.</p>
					<h1>Turn Interviews Into Predictable Revenue</h1>
					<p class="gfy-sub-headline">Stop random interviews that don\'t generate business. Target shows where your ideal audience listens, building trust that converts into trackable leads and revenue.</p>
					<div class="gfy-hero-tabs__cta">
						<a href="/start" class="gfy-btn gfy-btn-cta">Start Free Trial</a>
						<a href="/demo" class="gfy-btn gfy-btn-secondary">Book a Demo</a>
					</div>
					<p class="gfy-hero-tabs__reassurance">14-day free trial. No credit card required.</p>
				</div>
				<div class="gfy-hero-tabs__visual">
					<img src="/wp-content/uploads/grow-revenue.png" alt="Revenue tracking dashboard" />
				</div>
			</div>
		</div>

		<div id="panel-launch" class="gfy-hero-tabs__panel">
			<div class="gfy-hero-tabs__grid">
				<div class="gfy-hero-tabs__copy">
					<p class="gfy-pre-headline">You created something important. Now make sure the right people know.</p>
					<h1>Create momentum for your launch</h1>
					<p class="gfy-sub-headline">For books, courses, or products &mdash; build authentic relationships with hosts who become advocates, amplifying your message without pushy tactics.</p>
					<div class="gfy-hero-tabs__cta">
						<a href="/start" class="gfy-btn gfy-btn-cta">Start Free Trial</a>
						<a href="/demo" class="gfy-btn gfy-btn-secondary">Book a Demo</a>
					</div>
					<p class="gfy-hero-tabs__reassurance">14-day free trial. No credit card required.</p>
				</div>
				<div class="gfy-hero-tabs__visual">
					<img src="/wp-content/uploads/launch-promote.png" alt="Launch campaign dashboard" />
				</div>
			</div>
		</div>
	</div>
</section>
<!-- /wp:html -->',
);
