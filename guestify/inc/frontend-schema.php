<?php
/**
 * Guestify Frontend Schema — JSON-LD Structured Data (AEO-Optimized v3)
 *
 * Outputs a unified @graph knowledge graph in wp_head for frontend pages.
 * Uses @id references to build a connected entity graph that AI engines
 * can traverse: Organization → Person → Article → SoftwareApplication.
 *
 * Architecture (per AEO expert review, 2026-03):
 *   - Single @graph array (not separate <script> blocks)
 *   - Full Organization on homepage + about only; @id stub elsewhere
 *   - SoftwareApplication (SaaS industry standard, not WebApplication)
 *   - Absolute canonical URLs for all @id values
 *   - No Review without numeric rating; case studies = Article only
 *   - LearningResource for resource/lead-magnet pages
 *   - Speakable skipped (restricted to news publishers, low ROI for B2B SaaS)
 *
 * AUTOMATED (theme handles via @graph):
 *   Organization, WebSite, SoftwareApplication, BreadcrumbList,
 *   Article, Person (founder), LearningResource
 *
 * MANUAL (page builder prompt generates in separate <!-- wp:html --> blocks):
 *   FAQPage, HowTo, Offer (pricing — single source of truth), VideoObject
 *
 * @package Guestify
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* =========================================================================
   CONSTANTS — @id fragment identifiers (combined with home_url at runtime)
   ========================================================================= */

define( 'GFY_SCHEMA_ORG_ID',     '#organization' );
define( 'GFY_SCHEMA_SITE_ID',    '#website' );
define( 'GFY_SCHEMA_APP_ID',     '#application' );
define( 'GFY_SCHEMA_FOUNDER_ID', '#founder' );


/* =========================================================================
   HELPER — Build absolute @id URL from fragment
   ========================================================================= */

/**
 * Build an absolute @id URL: https://guestify.ai/#fragment
 *
 * @param string $fragment  Hash fragment, e.g. '#organization'.
 * @return string  Full absolute URL.
 */
function guestify_schema_id( $fragment ) {
	return home_url( '/' ) . ltrim( $fragment, '/' );
}


/* =========================================================================
   MAIN OUTPUT — Single @graph array in wp_head
   ========================================================================= */

/**
 * Output a unified JSON-LD @graph on frontend pages.
 *
 * Server-side rendered in wp_head (priority 5) so AI crawlers (GPTBot,
 * ClaudeBot, PerplexityBot) see it in raw HTML without JS execution.
 */
function guestify_frontend_schema() {
	// Never run in admin or REST/AJAX contexts
	if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}

	$graph = [];

	/* ── 1. Organization ───────────────────────────────────────────────
	 * Full version on homepage + about (anchors the knowledge graph).
	 * Lightweight stub on all other pages (just @id, @type, name, url).
	 */
	if ( is_front_page() || is_page( 'about' ) ) {
		$graph[] = guestify_schema_organization_full();
	} else {
		$graph[] = guestify_schema_organization_stub();
	}

	/* ── 2. WebSite — homepage only ────────────────────────────────── */
	if ( is_front_page() ) {
		$graph[] = guestify_schema_website();
	}

	/* ── 3. SoftwareApplication — homepage + product pages ─────────── */
	if ( is_front_page() || guestify_is_product_page() ) {
		$graph[] = guestify_schema_software_application();
	}

	/* ── 4. BreadcrumbList — every page except homepage ────────────── */
	if ( ! is_front_page() ) {
		$breadcrumb = guestify_schema_breadcrumb();
		if ( $breadcrumb ) {
			$graph[] = $breadcrumb;
		}
	}

	/* ── 5. Article + Person author — blog posts ──────────────────── */
	if ( is_singular( 'post' ) ) {
		$graph[] = guestify_schema_article();
	}

	/* ── 6. Article — case studies (no Review without numeric rating) */
	if ( is_singular( 'gfy_case_study' ) ) {
		$graph[] = guestify_schema_case_study();
	}

	/* ── 7. Person (founder) — about page ─────────────────────────── */
	if ( is_page( 'about' ) ) {
		$graph[] = guestify_schema_founder();
	}

	/* ── 8. LearningResource — resource/lead-magnet pages ─────────── */
	if ( is_singular( 'gfy_resource' ) ) {
		$resource = guestify_schema_learning_resource();
		if ( $resource ) {
			$graph[] = $resource;
		}
	}

	// Remove nulls and re-index
	$graph = array_values( array_filter( $graph ) );

	if ( empty( $graph ) ) {
		return;
	}

	$output = [
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	];

	printf(
		"\n" . '<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode( $output, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT )
	);
}
add_action( 'wp_head', 'guestify_frontend_schema', 5 );


/* =========================================================================
   ORGANIZATION — Full (homepage + about)
   Anchors the entire knowledge graph. Includes founder, contact, sameAs.
   ========================================================================= */

function guestify_schema_organization_full() {
	return [
		'@type'        => 'Organization',
		'@id'          => guestify_schema_id( GFY_SCHEMA_ORG_ID ),
		'name'         => 'Guestify',
		'url'          => home_url( '/' ),
		'logo'         => [
			'@type'  => 'ImageObject',
			'url'    => get_template_directory_uri() . '/assets/images/guestify-logo.png',
			'width'  => 300,
			'height' => 60,
		],
		'description'  => 'Guestify is the Interview Authority System — an end-to-end SaaS platform that turns podcast guest interviews into authority, partnerships, and revenue.',
		'foundingDate' => '2024',
		'founder'      => [
			'@type' => 'Person',
			'@id'   => guestify_schema_id( GFY_SCHEMA_FOUNDER_ID ),
			'name'  => 'Tony Guarnaccia',
		],
		'sameAs'       => [
			'https://www.linkedin.com/company/guestify',
			// TODO: Add Crunchbase, Twitter/X, G2 as profiles are created
		],
		'contactPoint' => [
			'@type'       => 'ContactPoint',
			'contactType' => 'customer support',
			'url'         => home_url( '/contact' ),
		],
		'knowsAbout'   => [
			'Podcast guest interview strategy',
			'Authority building for experts',
			'Interview-based thought leadership',
			'Podcast outreach automation',
		],
	];
}


/* =========================================================================
   ORGANIZATION — Stub (all non-homepage/about pages)
   Reinforces entity via @id without schema bloat.
   ========================================================================= */

function guestify_schema_organization_stub() {
	return [
		'@type' => 'Organization',
		'@id'   => guestify_schema_id( GFY_SCHEMA_ORG_ID ),
		'name'  => 'Guestify',
		'url'   => home_url( '/' ),
	];
}


/* =========================================================================
   WEBSITE — Homepage only
   Canonical site entity for AI engines.
   ========================================================================= */

function guestify_schema_website() {
	return [
		'@type'       => 'WebSite',
		'@id'         => guestify_schema_id( GFY_SCHEMA_SITE_ID ),
		'name'        => 'Guestify — The Interview Authority System',
		'url'         => home_url( '/' ),
		'publisher'   => [
			'@id' => guestify_schema_id( GFY_SCHEMA_ORG_ID ),
		],
		'description' => 'Guestify is the Interview Authority System — an end-to-end SaaS platform that helps experts, consultants, and agencies turn podcast guest interviews into recognized authority, strategic partnerships, and predictable revenue.',
	];
}


/* =========================================================================
   SOFTWARE APPLICATION — Homepage + /product pages
   SaaS industry standard (replaces WebApplication per expert review).
   Simplified offers (free trial only — pricing page is single source of truth).
   ========================================================================= */

function guestify_schema_software_application() {
	return [
		'@type'                  => 'SoftwareApplication',
		'@id'                    => guestify_schema_id( GFY_SCHEMA_APP_ID ),
		'name'                   => 'Guestify',
		'applicationCategory'    => 'BusinessApplication',
		'applicationSubCategory' => 'Podcast Guest Management',
		'operatingSystem'        => 'Web',
		'description'            => 'Guestify is the Interview Authority System — find strategic podcast opportunities, position your expertise with AI, manage outreach systematically, and turn interviews into authority, partnerships, and revenue.',
		'url'                    => home_url( '/product' ),
		'creator'                => [
			'@id' => guestify_schema_id( GFY_SCHEMA_ORG_ID ),
		],
		'offers'                 => [
			[
				'@type'         => 'Offer',
				'name'          => 'Free Trial',
				'price'         => '0',
				'priceCurrency' => 'USD',
				'description'   => '14-day free trial. No credit card required.',
				'url'           => home_url( '/start' ),
			],
			// Full pricing tiers live on /pricing page as single source of truth
			// (manual Offer schema generated by page builder prompt)
		],
		'featureList'            => [
			'Discovery Intelligence — AI-powered podcast matching to find the 10 right shows, not 1,000 random ones',
			'Authority Positioning — AI-generated media kits and pitches tailored to each show',
			'Outreach & Booking — Multi-channel host outreach via email and LinkedIn',
			'Interview Tracking — Full pipeline management from pitch to published episode',
			'Relationship Leverage — Turn host relationships into partnerships and revenue',
			'Agency Operations — Multi-tenant management for podcast guesting agencies',
		],
	];
}


/* =========================================================================
   BREADCRUMB — Every page except homepage
   ========================================================================= */

function guestify_schema_breadcrumb() {
	if ( is_front_page() ) {
		return null;
	}

	$items    = [];
	$position = 1;

	// Home (always first)
	$items[] = [
		'@type'    => 'ListItem',
		'position' => $position++,
		'name'     => 'Home',
		'item'     => home_url( '/' ),
	];

	if ( is_singular() ) {
		$post = get_queried_object();

		// Parent page (e.g. Product → Podcast Discovery)
		if ( $post->post_parent ) {
			$parent = get_post( $post->post_parent );
			if ( $parent ) {
				$items[] = [
					'@type'    => 'ListItem',
					'position' => $position++,
					'name'     => $parent->post_title,
					'item'     => get_permalink( $parent ),
				];
			}
		}

		// Blog posts get "Blog" parent
		if ( 'post' === $post->post_type ) {
			$items[] = [
				'@type'    => 'ListItem',
				'position' => $position++,
				'name'     => 'Blog',
				'item'     => home_url( '/blog' ),
			];
		}

		// Case studies get "Results" parent
		if ( 'gfy_case_study' === $post->post_type ) {
			$items[] = [
				'@type'    => 'ListItem',
				'position' => $position++,
				'name'     => 'Results',
				'item'     => home_url( '/results' ),
			];
		}

		// Resources get "Resources" parent
		if ( 'gfy_resource' === $post->post_type ) {
			$items[] = [
				'@type'    => 'ListItem',
				'position' => $position++,
				'name'     => 'Resources',
				'item'     => home_url( '/resources' ),
			];
		}

		// Current page (no 'item' URL per Google spec for last crumb)
		$items[] = [
			'@type'    => 'ListItem',
			'position' => $position,
			'name'     => $post->post_title,
		];

	} elseif ( is_post_type_archive( 'gfy_case_study' ) ) {
		$items[] = [ '@type' => 'ListItem', 'position' => $position, 'name' => 'Results' ];
	} elseif ( is_post_type_archive( 'gfy_resource' ) ) {
		$items[] = [ '@type' => 'ListItem', 'position' => $position, 'name' => 'Resources' ];
	} elseif ( is_home() ) {
		$items[] = [ '@type' => 'ListItem', 'position' => $position, 'name' => 'Blog' ];
	}

	return [
		'@type'            => 'BreadcrumbList',
		'itemListElement'  => $items,
	];
}


/* =========================================================================
   ARTICLE — Blog posts (nested author Person → Organization)
   ========================================================================= */

function guestify_schema_article() {
	$post = get_queried_object();
	if ( ! $post ) {
		return null;
	}

	// Author — fall back to Organization if no personal author
	$author        = get_userdata( $post->post_author );
	$author_name   = $author ? $author->display_name : 'Guestify';
	$author_schema = $author ? [
		'@type'    => 'Person',
		'name'     => $author_name,
		'url'      => home_url( '/about' ),
		'worksFor' => [
			'@id' => guestify_schema_id( GFY_SCHEMA_ORG_ID ),
		],
	] : [
		'@id' => guestify_schema_id( GFY_SCHEMA_ORG_ID ),
	];

	$schema = [
		'@type'            => 'Article',
		'headline'         => $post->post_title,
		'description'      => has_excerpt( $post )
			? get_the_excerpt( $post )
			: wp_trim_words( wp_strip_all_tags( $post->post_content ), 30 ),
		'url'              => get_permalink( $post ),
		'datePublished'    => get_the_date( 'c', $post ),
		'dateModified'     => get_the_modified_date( 'c', $post ),
		'author'           => $author_schema,
		'publisher'        => [
			'@id' => guestify_schema_id( GFY_SCHEMA_ORG_ID ),
		],
		'mainEntityOfPage' => [
			'@type' => 'WebPage',
			'@id'   => get_permalink( $post ),
		],
		'isPartOf'         => [
			'@id' => guestify_schema_id( GFY_SCHEMA_SITE_ID ),
		],
	];

	if ( has_post_thumbnail( $post ) ) {
		$schema['image'] = get_the_post_thumbnail_url( $post, 'large' );
	}

	return $schema;
}


/* =========================================================================
   CASE STUDY — Article only (no Review without numeric rating per Google
   rich results guidelines; on-page testimonial text does the heavy lifting)
   ========================================================================= */

function guestify_schema_case_study() {
	$post = get_queried_object();
	if ( ! $post ) {
		return null;
	}

	$schema = [
		'@type'            => 'Article',
		'headline'         => $post->post_title,
		'description'      => has_excerpt( $post )
			? get_the_excerpt( $post )
			: wp_trim_words( wp_strip_all_tags( $post->post_content ), 50 ),
		'url'              => get_permalink( $post ),
		'datePublished'    => get_the_date( 'c', $post ),
		'dateModified'     => get_the_modified_date( 'c', $post ),
		'about'            => [
			'@id' => guestify_schema_id( GFY_SCHEMA_APP_ID ),
		],
		'publisher'        => [
			'@id' => guestify_schema_id( GFY_SCHEMA_ORG_ID ),
		],
		'mainEntityOfPage' => [
			'@type' => 'WebPage',
			'@id'   => get_permalink( $post ),
		],
	];

	if ( has_post_thumbnail( $post ) ) {
		$schema['image'] = get_the_post_thumbnail_url( $post, 'large' );
	}

	return $schema;
}


/* =========================================================================
   FOUNDER — About page (Person with credentials)
   ========================================================================= */

function guestify_schema_founder() {
	return [
		'@type'      => 'Person',
		'@id'        => guestify_schema_id( GFY_SCHEMA_FOUNDER_ID ),
		'name'       => 'Tony Guarnaccia',
		'jobTitle'   => 'Founder & CEO',
		'url'        => home_url( '/about' ),
		'worksFor'   => [
			'@id' => guestify_schema_id( GFY_SCHEMA_ORG_ID ),
		],
		'knowsAbout' => [
			'Interview Authority',
			'Podcast guest strategy',
			'Authority Economy',
			'B2B thought leadership',
		],
		'sameAs'     => [
			'https://www.linkedin.com/in/tonyguarnaccia',
			// TODO: Add Crunchbase, personal site, Twitter/X as available
		],
	];
}


/* =========================================================================
   LEARNING RESOURCE — Resource / lead-magnet pages (gfy_resource CPT)
   ========================================================================= */

function guestify_schema_learning_resource() {
	$post = get_queried_object();
	if ( ! $post ) {
		return null;
	}

	// Map persona meta to audience description
	$persona = get_post_meta( $post->ID, 'gfy_resource_target_persona', true );
	$audiences = [
		'authority-builder'  => 'Experts and consultants building recognized authority through podcast guest interviews',
		'revenue-generator'  => 'Business owners turning podcast appearances into revenue and partnerships',
		'launch-promoter'    => 'Authors, creators, and course launchers maximizing visibility through podcast guesting',
		'agency-operator'    => 'Agency owners running podcast guesting as a scalable service',
	];

	$schema = [
		'@type'       => 'LearningResource',
		'name'        => $post->post_title,
		'description' => has_excerpt( $post )
			? get_the_excerpt( $post )
			: wp_trim_words( wp_strip_all_tags( $post->post_content ), 40 ),
		'url'         => get_permalink( $post ),
		'provider'    => [
			'@id' => guestify_schema_id( GFY_SCHEMA_ORG_ID ),
		],
		'isPartOf'    => [
			'@id' => guestify_schema_id( GFY_SCHEMA_SITE_ID ),
		],
	];

	// Add audience if persona meta is set
	if ( $persona && isset( $audiences[ $persona ] ) ) {
		$schema['audience'] = [
			'@type'         => 'Audience',
			'audienceType'  => $audiences[ $persona ],
		];
	}

	// Gated resource = access mode
	$is_gated = get_post_meta( $post->ID, 'gfy_resource_gated', true );
	if ( $is_gated ) {
		$schema['isAccessibleForFree'] = false;
		$schema['conditionsOfAccess']  = 'Requires email registration';
	} else {
		$schema['isAccessibleForFree'] = true;
	}

	if ( has_post_thumbnail( $post ) ) {
		$schema['image'] = get_the_post_thumbnail_url( $post, 'large' );
	}

	return $schema;
}


/* =========================================================================
   HELPERS
   ========================================================================= */

/**
 * Check if current page is any product page (overview or sub-page).
 */
function guestify_is_product_page() {
	if ( ! is_page() ) {
		return false;
	}
	$post = get_queried_object();
	if ( ! $post ) {
		return false;
	}
	// Product overview page
	if ( 'product' === $post->post_name ) {
		return true;
	}
	// Product sub-page (child of product)
	if ( $post->post_parent ) {
		$parent = get_post( $post->post_parent );
		return $parent && 'product' === $parent->post_name;
	}
	return false;
}

/**
 * Check if current page is a product SUB-page only (not the overview).
 */
function guestify_is_product_subpage() {
	if ( ! is_page() ) {
		return false;
	}
	$post = get_queried_object();
	if ( ! $post || ! $post->post_parent ) {
		return false;
	}
	$parent = get_post( $post->post_parent );
	return $parent && 'product' === $parent->post_name;
}


/* =========================================================================
   SEO PLUGIN CONFLICT PREVENTION
   Disable schema generation from Yoast, RankMath, and AIOSEO for types
   we handle ourselves. Prevents duplicate/conflicting @organization entities.
   ========================================================================= */

/**
 * Yoast SEO: remove their schema graph pieces we override.
 */
function guestify_disable_yoast_schema( $pieces ) {
	// Remove Yoast's versions of schemas we control
	$remove = [
		'Yoast\WP\SEO\Generators\Schema\Organization',
		'Yoast\WP\SEO\Generators\Schema\Website',
		'Yoast\WP\SEO\Generators\Schema\Breadcrumb',
		'Yoast\WP\SEO\Generators\Schema\Article',
		'Yoast\WP\SEO\Generators\Schema\Person',
	];

	return array_filter( $pieces, function ( $piece ) use ( $remove ) {
		foreach ( $remove as $class ) {
			if ( $piece instanceof $class ) {
				return false;
			}
		}
		return true;
	} );
}
add_filter( 'wpseo_schema_graph_pieces', 'guestify_disable_yoast_schema', 99 );

/**
 * RankMath: disable their JSON-LD output entirely (we handle all schema).
 */
function guestify_disable_rankmath_schema() {
	// Only disable on frontend pages where we output our own schema
	if ( is_admin() ) {
		return;
	}
	remove_all_actions( 'rank_math/json_ld' );
}
add_action( 'wp', 'guestify_disable_rankmath_schema', 99 );

/**
 * All In One SEO: disable their schema output.
 */
function guestify_disable_aioseo_schema( $graphs ) {
	// Return empty array to suppress all AIOSEO schema
	return [];
}
add_filter( 'aioseo_schema_output', 'guestify_disable_aioseo_schema', 99 );
