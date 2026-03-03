<?php
/**
 * Guestify Site Setup — Programmatic Page & Menu Creation
 *
 * Creates all frontend marketing pages with block pattern content,
 * sets page templates, parent/child relationships, and navigation menus.
 *
 * Usage:
 *   WP-CLI:  wp eval 'GFY_Site_Setup::run();'
 *   Admin:   Guestify → Site Setup → "Build Frontend Pages" button
 *
 * Safe to run multiple times — skips pages that already exist (by slug).
 *
 * @package Guestify
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GFY_Site_Setup {

	/**
	 * Run the full setup.
	 */
	public static function run() {
		$results = [];
		$results['pages']    = self::create_pages();
		$results['homepage'] = self::set_homepage();
		$results['menus']    = self::create_menus();

		// Flush rewrite rules after creating pages
		flush_rewrite_rules();

		return $results;
	}

	/**
	 * Load a pattern file and return its block markup content.
	 *
	 * @param string $pattern_name Filename without .php (e.g., 'homepage-hero-tabbed')
	 * @return string Block markup or empty string if file not found.
	 */
	private static function get_pattern_content( $pattern_name ) {
		$file = get_template_directory() . '/patterns/' . $pattern_name . '.php';
		if ( ! file_exists( $file ) ) {
			return '';
		}
		$pattern = require $file;
		return isset( $pattern['content'] ) ? $pattern['content'] : '';
	}

	/**
	 * Create a page if it doesn't already exist (matched by slug).
	 *
	 * @param array $args {
	 *     @type string $slug          Page slug.
	 *     @type string $title         Page title.
	 *     @type string $content       Block markup content.
	 *     @type int    $parent        Parent page ID (0 for top-level).
	 *     @type string $template      Page template file (relative to theme).
	 *     @type int    $menu_order    Menu order for sorting.
	 * }
	 * @return array ['id' => int, 'status' => 'created'|'exists'|'error', 'slug' => string]
	 */
	private static function create_page( $args ) {
		$defaults = [
			'slug'       => '',
			'title'      => '',
			'content'    => '',
			'parent'     => 0,
			'template'   => '',
			'menu_order' => 0,
		];
		$args = wp_parse_args( $args, $defaults );

		// Check if page already exists by slug
		$existing = get_page_by_path( $args['slug'] );
		if ( $existing ) {
			return [
				'id'     => $existing->ID,
				'status' => 'exists',
				'slug'   => $args['slug'],
			];
		}

		// Also check with parent path for child pages
		if ( $args['parent'] ) {
			$parent_post = get_post( $args['parent'] );
			if ( $parent_post ) {
				$full_path = $parent_post->post_name . '/' . $args['slug'];
				$existing = get_page_by_path( $full_path );
				if ( $existing ) {
					return [
						'id'     => $existing->ID,
						'status' => 'exists',
						'slug'   => $args['slug'],
					];
				}
			}
		}

		$page_id = wp_insert_post( [
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $args['title'],
			'post_name'    => $args['slug'],
			'post_content' => $args['content'],
			'post_parent'  => $args['parent'],
			'menu_order'   => $args['menu_order'],
		] );

		if ( is_wp_error( $page_id ) ) {
			return [
				'id'     => 0,
				'status' => 'error',
				'slug'   => $args['slug'],
				'error'  => $page_id->get_error_message(),
			];
		}

		// Set page template if specified
		if ( ! empty( $args['template'] ) ) {
			update_post_meta( $page_id, '_wp_page_template', $args['template'] );
		}

		return [
			'id'     => $page_id,
			'status' => 'created',
			'slug'   => $args['slug'],
		];
	}

	/**
	 * Create all frontend pages with pattern content.
	 *
	 * @return array Results for each page.
	 */
	public static function create_pages() {
		$results = [];

		// ─────────────────────────────────────────────
		// CORE PAGES
		// ─────────────────────────────────────────────

		$results[] = self::create_page( [
			'slug'       => 'home',
			'title'      => 'Home',
			'menu_order' => 1,
			'content'    => self::assemble_homepage(),
		] );

		$results[] = self::create_page( [
			'slug'       => 'how-it-works',
			'title'      => 'How It Works',
			'menu_order' => 2,
			'content'    => self::assemble_how_it_works(),
		] );

		$results[] = self::create_page( [
			'slug'       => 'pricing',
			'title'      => 'Pricing',
			'menu_order' => 3,
			'content'    => self::assemble_pricing(),
		] );

		$results[] = self::create_page( [
			'slug'    => 'demo',
			'title'   => 'Book a Demo',
			'content' => self::assemble_demo(),
		] );

		$results[] = self::create_page( [
			'slug'    => 'about',
			'title'   => 'About',
			'content' => self::assemble_about(),
		] );

		$results[] = self::create_page( [
			'slug'    => 'contact',
			'title'   => 'Contact',
			'content' => self::assemble_contact(),
		] );

		// ─────────────────────────────────────────────
		// PRODUCT PAGES (parent + children)
		// ─────────────────────────────────────────────

		$product_result = self::create_page( [
			'slug'       => 'product',
			'title'      => 'Product',
			'template'   => 'templates/template-product.php',
			'menu_order' => 4,
			'content'    => self::assemble_product_overview(),
		] );
		$results[]   = $product_result;
		$product_id  = $product_result['id'];

		$product_children = [
			[
				'slug'    => 'podcast-discovery',
				'title'   => 'Podcast Discovery',
				'content' => self::assemble_product_page(
					'PODCAST DISCOVERY',
					'Find the 10 Right Shows — Not 1,000 Random Ones',
					'Intelligent podcast discovery that finds shows where your ideal audience listens. Multi-source search across PodcastIndex, Taddy, and YouTube — with fit scoring that identifies strategic matches, not random results.',
					'Discovery Intelligence',
					'Strategic show discovery and audience alignment',
					'Borrowed Authority'
				),
			],
			[
				'slug'    => 'authority-positioning',
				'title'   => 'Authority Positioning',
				'content' => self::assemble_product_page(
					'AUTHORITY POSITIONING',
					'Sound Like the Expert You Are — Every Time',
					'AI-assisted media kits, authority hooks, and positioning tools that make hosts say yes. 25+ content tools purpose-built for interview positioning — not generic marketing copy.',
					'Authority Positioning System',
					'Professional media kits and AI-powered positioning',
					'Articulated Authority'
				),
			],
			[
				'slug'    => 'outreach-booking',
				'title'   => 'Outreach & Booking',
				'content' => self::assemble_product_page(
					'OUTREACH & BOOKING',
					'Reach Hosts Where They Respond — Without Copy-Paste Chaos',
					'Multi-channel outreach across email and LinkedIn with smart templates, automated follow-ups, and personalized pitches. The only interview system with LinkedIn automation built in.',
					'Outreach & Booking System',
					'Multi-channel outreach and relationship initiation',
					'Borrowed Authority'
				),
			],
			[
				'slug'    => 'interview-tracking',
				'title'   => 'Interview Tracking',
				'content' => self::assemble_product_page(
					'INTERVIEW TRACKING',
					'See What\'s Working — And What to Do Next',
					'Full pipeline CRM built for interviews, not sales. Track every opportunity from first pitch to published episode, with intelligence that shows which appearances drive real outcomes.',
					'Interview Tracking Pipeline',
					'Opportunity tracking and outcome attribution',
					'Earned + Demonstrated Authority'
				),
			],
			[
				'slug'    => 'relationship-management',
				'title'   => 'Relationship Management',
				'content' => self::assemble_product_page(
					'RELATIONSHIP MANAGEMENT',
					'The Interview Is the Beginning, Not the End',
					'Post-interview nurturing, content repurposing, and the 3 C\'s framework (Connect, Collaborate, Convert) that turns single appearances into compounding partnerships and revenue.',
					'Relationship Leverage System',
					'Post-interview nurturing and monetization',
					'Demonstrated Authority'
				),
			],
			[
				'slug'    => 'agency-operations',
				'title'   => 'Agency Operations',
				'content' => self::assemble_product_page(
					'AGENCY OPERATIONS',
					'Run Podcast Guesting as a Scalable Service',
					'Multi-tenant data isolation, per-client pipeline visibility, and white-label options. Everything you need to manage podcast guesting across multiple clients without spreadsheet chaos.',
					'Agency Operations Platform',
					'Multi-client management and scalable delivery',
					'All Authority Types'
				),
			],
		];

		foreach ( $product_children as $i => $child ) {
			$child['parent']     = $product_id;
			$child['template']   = 'templates/template-product.php';
			$child['menu_order'] = $i + 1;
			$results[] = self::create_page( $child );
		}

		// ─────────────────────────────────────────────
		// PERSONA PAGES (parent + children)
		// ─────────────────────────────────────────────

		// Hidden parent page — just a URL container
		$for_result = self::create_page( [
			'slug'       => 'for',
			'title'      => 'Solutions',
			'menu_order' => 5,
			'content'    => '',
		] );
		$results[] = $for_result;
		$for_id    = $for_result['id'];

		$persona_pages = [
			[
				'slug'  => 'experts-consultants',
				'title' => 'For Experts & Consultants',
				'data'  => [
					'wound'     => 'You\'re doing the work. You have the expertise. But visibility doesn\'t match your value.',
					'headline'  => 'Stop Being the Best-Kept Secret in Your Industry',
					'subhead'   => 'Find high-authority shows, position yourself as the obvious expert, and build host relationships that compound into recognized authority — without agencies, generic pitches, or self-promotional tactics.',
					'problem_title' => 'The Expertise-Visibility Gap',
					'pain1_title' => 'PR agencies that charge thousands but deliver sporadic, misaligned appearances',
					'pain1_desc'  => 'You\'ve invested $3K-$5K per month and gotten a handful of irrelevant shows. The ROI is invisible.',
					'pain2_title' => 'Generic pitches that get ignored',
					'pain2_desc'  => 'Your VA sends the same template to every host. Response rates sit below 5%. It feels humiliating.',
					'pain3_title' => 'No system to turn appearances into lasting authority',
					'pain3_desc'  => 'You do the interview, it airs, and nothing happens. No follow-up, no relationship, no compounding effect.',
					'objection1_q' => 'This looks like a lot of work. Can\'t someone just do it for me?',
					'objection1_a' => 'Booking services do it for you — but you don\'t own the relationships, the positioning, or the process. Guestify gives you the infrastructure to do it strategically, in less time than you\'d spend managing an agency. And everything you build, you own.',
					'objection2_q' => 'I don\'t need more visibility — I need clients.',
					'objection2_a' => 'Visibility without infrastructure is noise. Guestify builds Recognition first, then creates the path to Revenue. When hosts vouch for you to their audience, that trust converts at rates paid ads never will.',
					'objection3_q' => 'I\'ve tried podcast guesting before and it didn\'t work.',
					'objection3_a' => 'It didn\'t work because you had no system — no strategic targeting, no positioning assets, no follow-up process. The interview itself was never the problem. The missing infrastructure was.',
				],
			],
			[
				'slug'  => 'business-owners',
				'title' => 'For Business Owners',
				'data'  => [
					'wound'     => 'You\'ve been networking for years but the deals still feel random.',
					'headline'  => 'Turn Interviews Into Predictable Revenue',
					'subhead'   => 'Stop random interviews that don\'t generate business. Target shows where your ideal audience listens, build trust that converts, and systematize the relationship-to-revenue pipeline you\'ve always known works.',
					'problem_title' => 'The Revenue Ceiling Paradox',
					'pain1_title' => 'Paid ads getting more expensive with declining ROI',
					'pain1_desc'  => 'You\'re spending $2K-$10K per month and CAC keeps climbing. The channel that built the business is failing.',
					'pain2_title' => 'Cold outreach that no one responds to',
					'pain2_desc'  => 'Less than 2% response rates on cold email. LinkedIn feels like shouting into a void. Referrals are inconsistent.',
					'pain3_title' => 'Relationships that don\'t scale',
					'pain3_desc'  => 'You know relationships drive revenue, but you can\'t manage them manually at the next level of growth.',
					'objection1_q' => 'How do I know this will actually generate revenue?',
					'objection1_a' => 'Guestify includes revenue attribution in the Interview Tracker and the 3 C\'s framework (Connect, Collaborate, Convert) inside Demonstrated Authority. You\'ll see exactly which relationships drive which outcomes.',
					'objection2_q' => 'This seems expensive compared to running ads.',
					'objection2_a' => 'Podcast guesting delivers 40-60% lower customer acquisition cost than paid ads, and the relationships compound over time. Ads stop the moment you stop paying. Authority keeps working.',
					'objection3_q' => 'Why can\'t I just export contacts and run my own outreach?',
					'objection3_a' => 'Because 10 great relationships will always outperform 1,000 cold pitches. Guestify is built for strategic connection, not mass outreach. That\'s what makes the revenue predictable.',
				],
			],
			[
				'slug'  => 'authors-creators',
				'title' => 'For Authors & Creators',
				'data'  => [
					'wound'     => 'You created something important. Your launch window won\'t wait.',
					'headline'  => 'Create Unstoppable Momentum for Your Launch',
					'subhead'   => 'For books, courses, or products — coordinate strategic podcast appearances that build authentic buzz, turn hosts into advocates, and amplify your message before the window closes.',
					'problem_title' => 'The Visibility Cliff',
					'pain1_title' => 'A launch window that\'s closing with too few appearances confirmed',
					'pain1_desc'  => 'You\'ve pitched 30 shows and heard back from 4. The clock is ticking and there\'s no pattern to what works.',
					'pain2_title' => 'Random appearances that don\'t coordinate',
					'pain2_desc'  => 'Episodes air at random times — some before your launch, some months after. There\'s no strategic timing.',
					'pain3_title' => 'Momentum that dies after launch day',
					'pain3_desc'  => 'The buzz fades the moment the cart closes. All that relationship-building energy evaporates.',
					'objection1_q' => 'I only need this for my launch. Is it worth it?',
					'objection1_a' => 'The relationships you build during launch become your most valuable long-term assets. Guestify helps you coordinate the launch, then transition those relationships into ongoing Recognition and Revenue.',
					'objection2_q' => 'What if appearances air after my launch window?',
					'objection2_a' => 'Guestify\'s timing intelligence and campaign mode help you coordinate scheduling with hosts. You\'ll know lead times upfront and plan appearances to align with your window.',
					'objection3_q' => 'I don\'t have time to manage outreach during a launch.',
					'objection3_a' => 'That\'s exactly why you need infrastructure instead of manual effort. Set up your campaign in Guestify before launch, then the system handles follow-ups and tracking while you focus on delivery.',
				],
			],
		];

		foreach ( $persona_pages as $i => $persona ) {
			$results[] = self::create_page( [
				'slug'       => $persona['slug'],
				'title'      => $persona['title'],
				'parent'     => $for_id,
				'template'   => 'templates/template-persona.php',
				'menu_order' => $i + 1,
				'content'    => self::assemble_persona_page( $persona['data'] ),
			] );
		}

		// Agency page (separate — not full persona treatment)
		$results[] = self::create_page( [
			'slug'       => 'agencies',
			'title'      => 'For Agencies',
			'parent'     => $for_id,
			'template'   => 'templates/template-persona.php',
			'menu_order' => 4,
			'content'    => self::assemble_persona_page( [
				'wound'     => 'You\'re managing podcast guesting across clients with spreadsheets and email threads.',
				'headline'  => 'Run Podcast Guesting as a Scalable Service',
				'subhead'   => 'Multi-tenant data isolation, per-client pipelines, and white-label options. Everything you need to deliver podcast guesting at agency scale — without the operational chaos.',
				'problem_title' => 'The Agency Scaling Problem',
				'pain1_title' => 'Client data bleeding across accounts',
				'pain1_desc'  => 'No isolation between clients means one mistake affects everyone. Manual reporting eats your margins.',
				'pain2_title' => 'No per-client ROI visibility',
				'pain2_desc'  => 'You can\'t show clients what\'s working because your tracking lives in 5 different spreadsheets.',
				'pain3_title' => 'Processes that break at scale',
				'pain3_desc'  => 'What worked for 3 clients collapses at 10. There\'s no infrastructure to scale delivery.',
				'objection1_q' => 'Can I white-label this for my clients?',
				'objection1_a' => 'Yes. Guestify\'s agency tier includes white-label options so your clients see your brand, not ours.',
				'objection2_q' => 'How does per-client billing work?',
				'objection2_a' => 'Agency pricing is per-client with volume tiers. Each client gets isolated data, dedicated pipelines, and individual reporting.',
				'objection3_q' => 'We already have our own outreach tools.',
				'objection3_a' => 'Guestify isn\'t just outreach — it\'s the full interview authority workflow from discovery through relationship management. Your existing tools handle one piece. Guestify connects the entire pipeline.',
			] ),
		] );

		// ─────────────────────────────────────────────
		// CONVERSION PAGES
		// ─────────────────────────────────────────────

		$results[] = self::create_page( [
			'slug'    => 'webinar',
			'title'   => 'Free Masterclass',
			'content' => self::assemble_webinar(),
		] );

		$results[] = self::create_page( [
			'slug'     => 'start',
			'title'    => 'Start Free Trial',
			'template' => 'template-blank.php',
			'content'  => self::assemble_start(),
		] );

		$results[] = self::create_page( [
			'slug'     => 'apply',
			'title'    => 'Apply',
			'template' => 'template-blank.php',
			'content'  => self::assemble_apply(),
		] );

		// ─────────────────────────────────────────────
		// TRUST / LEGAL PAGES
		// ─────────────────────────────────────────────

		$results[] = self::create_page( [
			'slug'    => 'privacy',
			'title'   => 'Privacy Policy',
			'content' => '<!-- wp:paragraph --><p>Privacy policy content goes here.</p><!-- /wp:paragraph -->',
		] );

		$results[] = self::create_page( [
			'slug'    => 'terms',
			'title'   => 'Terms of Service',
			'content' => '<!-- wp:paragraph --><p>Terms of service content goes here.</p><!-- /wp:paragraph -->',
		] );

		$results[] = self::create_page( [
			'slug'    => 'security',
			'title'   => 'Security',
			'content' => '<!-- wp:paragraph --><p>Security information goes here.</p><!-- /wp:paragraph -->',
		] );

		// ─────────────────────────────────────────────
		// UTILITY PAGES
		// ─────────────────────────────────────────────

		$results[] = self::create_page( [
			'slug'    => 'wall-of-love',
			'title'   => 'Wall of Love',
			'content' => self::get_pattern_content( 'section-testimonial-row' )
			           . "\n\n"
			           . self::get_pattern_content( 'section-cta-primary' ),
		] );

		return $results;
	}

	// ─────────────────────────────────────────────────────
	// PAGE CONTENT ASSEMBLERS
	// Each method concatenates patterns into full page content
	// ─────────────────────────────────────────────────────

	/**
	 * Homepage — all 10 sections from patterns.
	 */
	private static function assemble_homepage() {
		$sections = [
			'homepage-hero-tabbed',
			'homepage-why-interviews',
			'homepage-relational-thesis',
			'homepage-authority-stack',
			'homepage-how-it-works',
			'homepage-social-proof',
			'homepage-persona-pathways',
			'homepage-differentiation',
			'homepage-faq',
			'homepage-final-cta',
		];

		$content = '';
		foreach ( $sections as $pattern ) {
			$content .= self::get_pattern_content( $pattern ) . "\n\n";
		}
		return $content;
	}

	/**
	 * How It Works page.
	 */
	private static function assemble_how_it_works() {
		return self::get_pattern_content( 'homepage-how-it-works' )
		     . "\n\n"
		     . self::get_pattern_content( 'homepage-authority-stack' )
		     . "\n\n"
		     . self::get_pattern_content( 'section-cta-primary' );
	}

	/**
	 * Pricing page — placeholder with CTA.
	 */
	private static function assemble_pricing() {
		return '<!-- wp:group {"className":"gfy-section","layout":{"type":"constrained","contentSize":"960px"}} -->
<div class="wp-block-group gfy-section">

<!-- wp:heading {"textAlign":"center","level":1} -->
<h1 class="wp-block-heading has-text-align-center">Simple, Transparent Pricing</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","className":"gfy-sub-headline"} -->
<p class="has-text-align-center gfy-sub-headline">Everything you need to build recognized authority through strategic podcast interviews.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center"><em>Pricing details coming soon. Join the waitlist to be notified.</em></p>
<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->'
		     . "\n\n"
		     . self::get_pattern_content( 'section-faq-accordion' )
		     . "\n\n"
		     . self::get_pattern_content( 'section-cta-primary' );
	}

	/**
	 * Demo page.
	 */
	private static function assemble_demo() {
		return '<!-- wp:group {"className":"gfy-section","layout":{"type":"constrained","contentSize":"768px"}} -->
<div class="wp-block-group gfy-section">

<!-- wp:heading {"textAlign":"center","level":1} -->
<h1 class="wp-block-heading has-text-align-center">See Guestify in Action</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","className":"gfy-sub-headline"} -->
<p class="has-text-align-center gfy-sub-headline">Book a 20-minute demo and see how the Interview Authority System helps you find the right shows, position your expertise, and turn interviews into lasting authority.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center","level":3} -->
<h3 class="wp-block-heading has-text-align-center">What you\'ll see:</h3>
<!-- /wp:heading -->

<!-- wp:list -->
<ul class="wp-block-list">
<li>How intelligent discovery finds the 10 right shows for your goals</li>
<li>AI-assisted positioning that makes hosts say yes</li>
<li>The relationship pipeline that turns interviews into outcomes</li>
</ul>
<!-- /wp:list -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center"><em>Embed your scheduling tool (Calendly, SavvyCal, etc.) below this section.</em></p>
<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->';
	}

	/**
	 * About page.
	 */
	private static function assemble_about() {
		return '<!-- wp:group {"className":"gfy-section","layout":{"type":"constrained","contentSize":"768px"}} -->
<div class="wp-block-group gfy-section">

<!-- wp:paragraph {"className":"gfy-pre-headline"} -->
<p class="gfy-pre-headline">OUR STORY</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">The Problem We Saw</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Experts were doing exceptional work — and nobody knew. Not because they lacked talent, but because they lacked infrastructure. The Attention Economy had convinced everyone that more content, more followers, and more noise was the path to authority.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>It wasn\'t. The most recognized experts in any field didn\'t get there by being the loudest. They got there because credible people vouched for them — systematically, repeatedly, to the right audiences.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Authority Is Recognized Trust</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>That insight changed everything. We built Guestify as the infrastructure layer for what we call the Authority Economy — where recognized trust, not raw attention, determines who gets chosen.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Podcast interviews are the fastest trust transfer mechanism available. When a host invites you on their show, they vouch for you. Their audience trusts you because the host trusts you. That\'s borrowed authority — and with the right system, it compounds into something permanent.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Every Expert Deserves to Be Recognized</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Guestify exists so that the best-kept secrets in every industry can become the obvious choices. Not through self-promotion. Not through agencies. Through a system that turns who you know and who knows you into the most valuable asset in your business.</p>
<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->'
		     . "\n\n"
		     . self::get_pattern_content( 'section-cta-primary' );
	}

	/**
	 * Contact page.
	 */
	private static function assemble_contact() {
		return '<!-- wp:group {"className":"gfy-section","layout":{"type":"constrained","contentSize":"640px"}} -->
<div class="wp-block-group gfy-section">

<!-- wp:heading {"textAlign":"center","level":1} -->
<h1 class="wp-block-heading has-text-align-center">Get in Touch</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","className":"gfy-sub-headline"} -->
<p class="has-text-align-center gfy-sub-headline">Have a question about Guestify? We\'d love to hear from you.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center"><em>Embed your contact form here (WPForms, Gravity Forms, etc.)</em></p>
<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->';
	}

	/**
	 * Product overview page.
	 */
	private static function assemble_product_overview() {
		return '<!-- wp:group {"className":"gfy-section","layout":{"type":"constrained","contentSize":"768px"}} -->
<div class="wp-block-group gfy-section">

<!-- wp:paragraph {"className":"gfy-pre-headline","align":"center"} -->
<p class="gfy-pre-headline has-text-align-center">THE INTERVIEW AUTHORITY SYSTEM</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center","level":1} -->
<h1 class="wp-block-heading has-text-align-center">Everything You Need to Build Recognized Authority</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","className":"gfy-sub-headline"} -->
<p class="has-text-align-center gfy-sub-headline">Guestify is the end-to-end platform that turns podcast guest interviews into authority, partnerships, and revenue. Six integrated capabilities. One system.</p>
<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->'
		     . "\n\n"
		     . self::get_pattern_content( 'section-feature-grid' )
		     . "\n\n"
		     . self::get_pattern_content( 'homepage-how-it-works' )
		     . "\n\n"
		     . self::get_pattern_content( 'section-stats-bar' )
		     . "\n\n"
		     . self::get_pattern_content( 'section-cta-primary' );
	}

	/**
	 * Individual product page — assembled from product patterns with custom content.
	 */
	private static function assemble_product_page( $eyebrow, $headline, $subhead, $feature_name, $feature_desc, $authority_type ) {
		$hero = '<!-- wp:group {"className":"gfy-section gfy-product-hero","layout":{"type":"constrained","contentSize":"1280px"}} -->
<div class="wp-block-group gfy-section gfy-product-hero">
<div class="gfy-content-grid">
<div class="gfy-content-grid__col gfy-content-grid__col--text">

<!-- wp:paragraph {"className":"gfy-pre-headline"} -->
<p class="gfy-pre-headline">' . esc_html( $eyebrow ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">' . esc_html( $headline ) . '</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"gfy-sub-headline"} -->
<p class="gfy-sub-headline">' . esc_html( $subhead ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex"}} -->
<div class="wp-block-buttons">
<!-- wp:button {"className":"gfy-btn-cta"} -->
<div class="wp-block-button gfy-btn-cta"><a class="wp-block-button__link wp-element-button" href="/start">Start Free Trial</a></div>
<!-- /wp:button -->
<!-- wp:button {"className":"is-style-outline gfy-btn-secondary"} -->
<div class="wp-block-button is-style-outline gfy-btn-secondary"><a class="wp-block-button__link wp-element-button" href="/demo">Book a Demo</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->

</div>
<div class="gfy-content-grid__col gfy-content-grid__col--visual">

<!-- wp:image {"className":"gfy-product-screenshot"} -->
<figure class="wp-block-image gfy-product-screenshot"><img src="/wp-content/uploads/product-placeholder.png" alt="' . esc_attr( $feature_name ) . ' screenshot"/></figure>
<!-- /wp:image -->

</div>
</div>
</div>
<!-- /wp:group -->';

		return $hero
		     . "\n\n"
		     . self::get_pattern_content( 'product-problem-solution' )
		     . "\n\n"
		     . self::get_pattern_content( 'product-how-it-works' )
		     . "\n\n"
		     . self::get_pattern_content( 'product-features' )
		     . "\n\n"
		     . self::get_pattern_content( 'product-not-for' )
		     . "\n\n"
		     . self::get_pattern_content( 'section-cta-primary' );
	}

	/**
	 * Persona page — assembled with actual persona-specific content.
	 */
	private static function assemble_persona_page( $data ) {
		// Hero
		$hero = '<!-- wp:group {"className":"gfy-section gfy-hero-persona","layout":{"type":"constrained","contentSize":"1280px"}} -->
<div class="wp-block-group gfy-section gfy-hero-persona">
<div class="gfy-content-grid">
<div class="gfy-content-grid__col gfy-content-grid__col--text">

<!-- wp:paragraph {"className":"gfy-pre-headline"} -->
<p class="gfy-pre-headline">' . esc_html( $data['wound'] ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">' . esc_html( $data['headline'] ) . '</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"gfy-sub-headline"} -->
<p class="gfy-sub-headline">' . esc_html( $data['subhead'] ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex"}} -->
<div class="wp-block-buttons">
<!-- wp:button {"className":"gfy-btn-cta"} -->
<div class="wp-block-button gfy-btn-cta"><a class="wp-block-button__link wp-element-button" href="/start">Start Free Trial</a></div>
<!-- /wp:button -->
<!-- wp:button {"className":"is-style-outline gfy-btn-secondary"} -->
<div class="wp-block-button is-style-outline gfy-btn-secondary"><a class="wp-block-button__link wp-element-button" href="/demo">Book a Demo</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->

<!-- wp:paragraph {"className":"gfy-hero-tabs__reassurance","fontSize":"sm"} -->
<p class="gfy-hero-tabs__reassurance has-sm-font-size">14-day free trial. No credit card required.</p>
<!-- /wp:paragraph -->

</div>
<div class="gfy-content-grid__col gfy-content-grid__col--visual">

<!-- wp:image -->
<figure class="wp-block-image"><img src="/wp-content/uploads/persona-placeholder.png" alt="' . esc_attr( $data['headline'] ) . '"/></figure>
<!-- /wp:image -->

</div>
</div>
</div>
<!-- /wp:group -->';

		// Problem section with real pain points
		$problem = '<!-- wp:group {"className":"gfy-section gfy-section--light","layout":{"type":"constrained","contentSize":"1280px"}} -->
<div class="wp-block-group gfy-section gfy-section--light">

<!-- wp:paragraph {"align":"center","className":"gfy-section__eyebrow"} -->
<p class="has-text-align-center gfy-section__eyebrow">THE PROBLEM</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center","level":2} -->
<h2 class="wp-block-heading has-text-align-center">' . esc_html( $data['problem_title'] ) . '</h2>
<!-- /wp:heading -->

<!-- wp:columns {"className":"gfy-feature-grid"} -->
<div class="wp-block-columns gfy-feature-grid">

<!-- wp:column {"className":"gfy-feature-card"} -->
<div class="wp-block-column gfy-feature-card">
<!-- wp:heading {"level":4,"className":"gfy-feature-card__title"} -->
<h4 class="wp-block-heading gfy-feature-card__title">' . esc_html( $data['pain1_title'] ) . '</h4>
<!-- /wp:heading -->
<!-- wp:paragraph {"className":"gfy-feature-card__description"} -->
<p class="gfy-feature-card__description">' . esc_html( $data['pain1_desc'] ) . '</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

<!-- wp:column {"className":"gfy-feature-card"} -->
<div class="wp-block-column gfy-feature-card">
<!-- wp:heading {"level":4,"className":"gfy-feature-card__title"} -->
<h4 class="wp-block-heading gfy-feature-card__title">' . esc_html( $data['pain2_title'] ) . '</h4>
<!-- /wp:heading -->
<!-- wp:paragraph {"className":"gfy-feature-card__description"} -->
<p class="gfy-feature-card__description">' . esc_html( $data['pain2_desc'] ) . '</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

<!-- wp:column {"className":"gfy-feature-card"} -->
<div class="wp-block-column gfy-feature-card">
<!-- wp:heading {"level":4,"className":"gfy-feature-card__title"} -->
<h4 class="wp-block-heading gfy-feature-card__title">' . esc_html( $data['pain3_title'] ) . '</h4>
<!-- /wp:heading -->
<!-- wp:paragraph {"className":"gfy-feature-card__description"} -->
<p class="gfy-feature-card__description">' . esc_html( $data['pain3_desc'] ) . '</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->

</div>
<!-- /wp:group -->';

		// Objections with real Q&A
		$objections = '<!-- wp:group {"className":"gfy-section gfy-section--light","layout":{"type":"constrained","contentSize":"768px"}} -->
<div class="wp-block-group gfy-section gfy-section--light">

<!-- wp:paragraph {"align":"center","className":"gfy-section__eyebrow"} -->
<p class="has-text-align-center gfy-section__eyebrow">COMMON CONCERNS</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center","level":2} -->
<h2 class="wp-block-heading has-text-align-center">Questions You Might Have</h2>
<!-- /wp:heading -->

<!-- wp:html -->
<div class="gfy-faq">
<details class="gfy-faq__item">
<summary class="gfy-faq__question">' . esc_html( $data['objection1_q'] ) . '</summary>
<div class="gfy-faq__answer"><p>' . esc_html( $data['objection1_a'] ) . '</p></div>
</details>
<details class="gfy-faq__item">
<summary class="gfy-faq__question">' . esc_html( $data['objection2_q'] ) . '</summary>
<div class="gfy-faq__answer"><p>' . esc_html( $data['objection2_a'] ) . '</p></div>
</details>
<details class="gfy-faq__item">
<summary class="gfy-faq__question">' . esc_html( $data['objection3_q'] ) . '</summary>
<div class="gfy-faq__answer"><p>' . esc_html( $data['objection3_a'] ) . '</p></div>
</details>
</div>
<!-- /wp:html -->

</div>
<!-- /wp:group -->';

		return $hero
		     . "\n\n" . $problem
		     . "\n\n" . self::get_pattern_content( 'persona-paradigm-shift' )
		     . "\n\n" . self::get_pattern_content( 'persona-framework' )
		     . "\n\n" . self::get_pattern_content( 'persona-capabilities' )
		     . "\n\n" . $objections
		     . "\n\n" . self::get_pattern_content( 'section-cta-primary' );
	}

	/**
	 * Webinar registration page.
	 */
	private static function assemble_webinar() {
		return '<!-- wp:group {"className":"gfy-section","layout":{"type":"constrained","contentSize":"768px"}} -->
<div class="wp-block-group gfy-section">

<!-- wp:heading {"textAlign":"center","level":1} -->
<h1 class="wp-block-heading has-text-align-center">The Authority Economy: How Experts Build Recognized Trust</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","className":"gfy-sub-headline"} -->
<p class="has-text-align-center gfy-sub-headline">Discover why the smartest experts are building authority through strategic podcast interviews — and the four-layer system that makes it compound.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center"><em>Embed webinar registration form here (EverWebinar, WebinarJam, etc.)</em></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center","level":3} -->
<h3 class="wp-block-heading has-text-align-center">In this masterclass, you\'ll learn:</h3>
<!-- /wp:heading -->

<!-- wp:list -->
<ul class="wp-block-list">
<li>Why the Attention Economy is failing experts — and what\'s replacing it</li>
<li>The four types of authority and how interviews build all of them</li>
<li>The trust transfer mechanism that makes podcast guesting the fastest path to recognition</li>
<li>A live walkthrough of the system that makes it repeatable</li>
</ul>
<!-- /wp:list -->

</div>
<!-- /wp:group -->';
	}

	/**
	 * Free trial signup page.
	 */
	private static function assemble_start() {
		return '<!-- wp:group {"className":"gfy-section","layout":{"type":"constrained","contentSize":"480px"}} -->
<div class="wp-block-group gfy-section" style="min-height:100vh;display:flex;align-items:center;">

<!-- wp:heading {"textAlign":"center","level":1} -->
<h1 class="wp-block-heading has-text-align-center">Start Building Authority</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">14-day free trial. No credit card required.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center"><em>Embed signup form here.</em></p>
<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->';
	}

	/**
	 * Application page.
	 */
	private static function assemble_apply() {
		return '<!-- wp:group {"className":"gfy-section","layout":{"type":"constrained","contentSize":"640px"}} -->
<div class="wp-block-group gfy-section" style="min-height:100vh;display:flex;align-items:center;">

<!-- wp:heading {"textAlign":"center","level":1} -->
<h1 class="wp-block-heading has-text-align-center">Apply for the Authority Stack Edition</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">Complete this short application to see if Guestify is the right fit for your authority goals.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center"><em>Embed application form here.</em></p>
<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->';
	}

	/**
	 * Set the homepage as the static front page.
	 */
	public static function set_homepage() {
		$home_page = get_page_by_path( 'home' );
		if ( ! $home_page ) {
			return [ 'status' => 'skipped', 'reason' => 'Home page not found' ];
		}

		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home_page->ID );

		// Set blog page if it exists
		$blog_page = get_page_by_path( 'blog' );
		if ( $blog_page ) {
			update_option( 'page_for_posts', $blog_page->ID );
		}

		return [ 'status' => 'set', 'page_id' => $home_page->ID ];
	}

	/**
	 * Create the frontend navigation menu.
	 */
	public static function create_menus() {
		$results = [];

		// ── Primary frontend menu ──
		$menu_name = 'Frontend Primary';
		$menu_exists = wp_get_nav_menu_object( $menu_name );

		if ( ! $menu_exists ) {
			$menu_id = wp_create_nav_menu( $menu_name );

			if ( ! is_wp_error( $menu_id ) ) {
				// How It Works
				$hiw_page = get_page_by_path( 'how-it-works' );
				if ( $hiw_page ) {
					wp_update_nav_menu_item( $menu_id, 0, [
						'menu-item-title'     => 'How It Works',
						'menu-item-object-id' => $hiw_page->ID,
						'menu-item-object'    => 'page',
						'menu-item-type'      => 'post_type',
						'menu-item-status'    => 'publish',
						'menu-item-position'  => 1,
					] );
				}

				// Product (parent)
				$product_page = get_page_by_path( 'product' );
				if ( $product_page ) {
					$product_menu_id = wp_update_nav_menu_item( $menu_id, 0, [
						'menu-item-title'     => 'Product',
						'menu-item-object-id' => $product_page->ID,
						'menu-item-object'    => 'page',
						'menu-item-type'      => 'post_type',
						'menu-item-status'    => 'publish',
						'menu-item-position'  => 2,
					] );

					// Product children
					$product_children = [
						'product/podcast-discovery'       => 'Podcast Discovery',
						'product/authority-positioning'    => 'Authority Positioning',
						'product/outreach-booking'        => 'Outreach & Booking',
						'product/interview-tracking'      => 'Interview Tracking',
						'product/relationship-management'  => 'Relationship Management',
						'product/agency-operations'       => 'Agency Operations',
					];

					$pos = 1;
					foreach ( $product_children as $path => $title ) {
						$child = get_page_by_path( $path );
						if ( $child ) {
							wp_update_nav_menu_item( $menu_id, 0, [
								'menu-item-title'     => $title,
								'menu-item-object-id' => $child->ID,
								'menu-item-object'    => 'page',
								'menu-item-type'      => 'post_type',
								'menu-item-parent-id' => $product_menu_id,
								'menu-item-status'    => 'publish',
								'menu-item-position'  => $pos++,
							] );
						}
					}
				}

				// For (parent)
				$for_page = get_page_by_path( 'for' );
				if ( $for_page ) {
					$for_menu_id = wp_update_nav_menu_item( $menu_id, 0, [
						'menu-item-title'     => 'Solutions',
						'menu-item-object-id' => $for_page->ID,
						'menu-item-object'    => 'page',
						'menu-item-type'      => 'post_type',
						'menu-item-status'    => 'publish',
						'menu-item-position'  => 3,
					] );

					$for_children = [
						'for/experts-consultants' => 'Experts & Consultants',
						'for/business-owners'     => 'Business Owners',
						'for/authors-creators'    => 'Authors & Creators',
						'for/agencies'            => 'Agencies',
					];

					$pos = 1;
					foreach ( $for_children as $path => $title ) {
						$child = get_page_by_path( $path );
						if ( $child ) {
							wp_update_nav_menu_item( $menu_id, 0, [
								'menu-item-title'     => $title,
								'menu-item-object-id' => $child->ID,
								'menu-item-object'    => 'page',
								'menu-item-type'      => 'post_type',
								'menu-item-parent-id' => $for_menu_id,
								'menu-item-status'    => 'publish',
								'menu-item-position'  => $pos++,
							] );
						}
					}
				}

				// Results
				wp_update_nav_menu_item( $menu_id, 0, [
					'menu-item-title'  => 'Results',
					'menu-item-url'    => home_url( '/results/' ),
					'menu-item-type'   => 'custom',
					'menu-item-status' => 'publish',
					'menu-item-position' => 4,
				] );

				// Pricing
				$pricing_page = get_page_by_path( 'pricing' );
				if ( $pricing_page ) {
					wp_update_nav_menu_item( $menu_id, 0, [
						'menu-item-title'     => 'Pricing',
						'menu-item-object-id' => $pricing_page->ID,
						'menu-item-object'    => 'page',
						'menu-item-type'      => 'post_type',
						'menu-item-status'    => 'publish',
						'menu-item-position'  => 5,
					] );
				}

				// Blog
				wp_update_nav_menu_item( $menu_id, 0, [
					'menu-item-title'  => 'Blog',
					'menu-item-url'    => home_url( '/blog/' ),
					'menu-item-type'   => 'custom',
					'menu-item-status' => 'publish',
					'menu-item-position' => 6,
				] );

				// Assign to 'frontend' menu location
				$locations = get_theme_mod( 'nav_menu_locations', [] );
				$locations['frontend'] = $menu_id;
				set_theme_mod( 'nav_menu_locations', $locations );

				$results['primary'] = [ 'status' => 'created', 'menu_id' => $menu_id ];
			}
		} else {
			$results['primary'] = [ 'status' => 'exists' ];
		}

		return $results;
	}

	// ─────────────────────────────────────────────────────
	// ADMIN UI
	// ─────────────────────────────────────────────────────

	/**
	 * Initialize admin page.
	 */
	public static function init_admin() {
		add_action( 'admin_menu', [ __CLASS__, 'add_admin_page' ] );
		add_action( 'admin_init', [ __CLASS__, 'handle_admin_action' ] );
	}

	/**
	 * Add admin menu page.
	 */
	public static function add_admin_page() {
		add_submenu_page(
			'themes.php',
			'Guestify Site Setup',
			'Site Setup',
			'manage_options',
			'gfy-site-setup',
			[ __CLASS__, 'render_admin_page' ]
		);
	}

	/**
	 * Handle the setup action.
	 */
	public static function handle_admin_action() {
		if ( ! isset( $_POST['gfy_run_setup'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'gfy_site_setup' ) ) {
			wp_die( 'Security check failed.' );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Insufficient permissions.' );
		}

		$results = self::run();

		set_transient( 'gfy_setup_results', $results, 60 );

		wp_redirect( admin_url( 'themes.php?page=gfy-site-setup&setup=done' ) );
		exit;
	}

	/**
	 * Render the admin page.
	 */
	public static function render_admin_page() {
		$results = get_transient( 'gfy_setup_results' );
		if ( $results ) {
			delete_transient( 'gfy_setup_results' );
		}
		?>
		<div class="wrap">
			<h1>Guestify Site Setup</h1>
			<p>Creates all frontend marketing pages with content, sets page templates, parent relationships, and navigation menus.</p>
			<p><strong>Safe to run multiple times</strong> — existing pages are skipped (matched by slug).</p>

			<?php if ( $results ) : ?>
				<div class="notice notice-success">
					<p><strong>Setup complete!</strong> Here are the results:</p>
				</div>
				<table class="widefat fixed striped" style="max-width:600px;">
					<thead>
						<tr><th>Page</th><th>Status</th></tr>
					</thead>
					<tbody>
						<?php foreach ( $results['pages'] as $page_result ) : ?>
							<tr>
								<td><code>/<?php echo esc_html( $page_result['slug'] ); ?>/</code></td>
								<td>
									<?php if ( $page_result['status'] === 'created' ) : ?>
										<span style="color:green;">&#10003; Created</span>
										(<a href="<?php echo get_edit_post_link( $page_result['id'] ); ?>">Edit</a>)
									<?php elseif ( $page_result['status'] === 'exists' ) : ?>
										<span style="color:gray;">&#8212; Already exists</span>
									<?php else : ?>
										<span style="color:red;">&#10007; Error</span>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

			<form method="post" style="margin-top:20px;">
				<?php wp_nonce_field( 'gfy_site_setup' ); ?>
				<input type="hidden" name="gfy_run_setup" value="1">
				<?php submit_button( 'Build Frontend Pages', 'primary', 'submit', false ); ?>
			</form>
		</div>
		<?php
	}

	/* ===========================================================
	   SINGLE-PAGE CREATOR
	   ===========================================================
	   For creating one page at a time via WP-CLI or the admin.

	   WP-CLI (inline content):
	     wp eval 'GFY_Site_Setup::create_single("my-slug", "My Title", "<!-- wp:paragraph --><p>Hello</p><!-- /wp:paragraph -->");'

	   WP-CLI (from file — best for Claude-generated pages):
	     wp eval 'GFY_Site_Setup::create_single_from_file("my-slug", "My Title", "/path/to/page-content.html");'

	   Both methods:
	     - Skip creation if a page with that slug already exists
	     - Optionally accept a page template and parent slug
	     - Return the page ID
	   =========================================================== */

	/**
	 * Create a single page with inline block markup.
	 *
	 * @param string $slug     Page slug.
	 * @param string $title    Page title.
	 * @param string $content  Block markup (HTML with <!-- wp: --> comments).
	 * @param array  $extra    Optional: 'template' (string), 'parent_slug' (string).
	 * @return array           ['id' => int, 'status' => string, 'slug' => string]
	 */
	public static function create_single( $slug, $title, $content, $extra = [] ) {
		$parent = 0;
		if ( ! empty( $extra['parent_slug'] ) ) {
			$parent_page = get_page_by_path( $extra['parent_slug'] );
			if ( $parent_page ) {
				$parent = $parent_page->ID;
			}
		}

		$result = self::create_page( [
			'slug'     => $slug,
			'title'    => $title,
			'content'  => $content,
			'parent'   => $parent,
			'template' => isset( $extra['template'] ) ? $extra['template'] : '',
		] );

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			if ( $result['status'] === 'created' ) {
				\WP_CLI::success( "Created page \"{$title}\" (/{$slug}/) — ID {$result['id']}" );
			} elseif ( $result['status'] === 'exists' ) {
				\WP_CLI::warning( "Page /{$slug}/ already exists — ID {$result['id']}. Use --force to overwrite." );
			} else {
				\WP_CLI::error( "Failed to create page /{$slug}/." );
			}
		}

		return $result;
	}

	/**
	 * Create a single page from a content file on disk.
	 *
	 * Workflow:
	 *   1. Claude generates block markup → save to a .html file in the theme
	 *   2. Run: wp eval 'GFY_Site_Setup::create_single_from_file("slug", "Title", "/full/path/to/file.html");'
	 *
	 * @param string $slug     Page slug.
	 * @param string $title    Page title.
	 * @param string $file     Absolute path to .html file with block markup.
	 * @param array  $extra    Optional: 'template', 'parent_slug'.
	 * @return array
	 */
	public static function create_single_from_file( $slug, $title, $file, $extra = [] ) {
		if ( ! file_exists( $file ) ) {
			if ( defined( 'WP_CLI' ) && WP_CLI ) {
				\WP_CLI::error( "File not found: {$file}" );
			}
			return [ 'id' => 0, 'status' => 'error', 'slug' => $slug, 'error' => 'File not found' ];
		}

		$content = file_get_contents( $file );
		return self::create_single( $slug, $title, $content, $extra );
	}

	/**
	 * Update an existing page's content (by slug). Useful for refreshing
	 * a page with new block markup from Claude.
	 *
	 * @param string $slug    Page slug.
	 * @param string $content New block markup.
	 * @return array          ['id' => int, 'status' => 'updated'|'not_found'|'error']
	 */
	public static function update_page_content( $slug, $content ) {
		$page = get_page_by_path( $slug );
		if ( ! $page ) {
			if ( defined( 'WP_CLI' ) && WP_CLI ) {
				\WP_CLI::error( "Page /{$slug}/ not found." );
			}
			return [ 'id' => 0, 'status' => 'not_found', 'slug' => $slug ];
		}

		$result = wp_update_post( [
			'ID'           => $page->ID,
			'post_content' => $content,
		] );

		if ( is_wp_error( $result ) ) {
			return [ 'id' => $page->ID, 'status' => 'error', 'slug' => $slug ];
		}

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::success( "Updated page /{$slug}/ — ID {$page->ID}" );
		}

		return [ 'id' => $page->ID, 'status' => 'updated', 'slug' => $slug ];
	}
}
