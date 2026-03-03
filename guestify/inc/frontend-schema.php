<?php
/**
 * Guestify Frontend Schema — JSON-LD Structured Data
 *
 * Outputs schema markup in wp_head for frontend (marketing) pages only.
 * Handles: Organization, WebApplication, BreadcrumbList, FAQPage, Article, HowTo.
 *
 * Page-specific schema (individual FAQ items, case study Review, etc.) is embedded
 * directly in block markup via Custom HTML blocks — see the page builder prompt.
 *
 * @package Guestify
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Output JSON-LD schema on frontend pages.
 */
function guestify_frontend_schema() {
	// Only run on frontend pages
	if ( function_exists( 'is_frontend_page' ) && ! is_frontend_page() ) {
		return;
	}

	$schemas = [];

	// 1. Organization — every frontend page
	$schemas[] = guestify_schema_organization();

	// 2. BreadcrumbList — every page except homepage
	if ( ! is_front_page() ) {
		$breadcrumb = guestify_schema_breadcrumb();
		if ( $breadcrumb ) {
			$schemas[] = $breadcrumb;
		}
	}

	// 3. WebApplication — homepage + product pages
	if ( is_front_page() || guestify_is_product_page() ) {
		$schemas[] = guestify_schema_web_application();
	}

	// 4. Article — single blog posts
	if ( is_singular( 'post' ) ) {
		$schemas[] = guestify_schema_article();
	}

	// 5. Review — case studies
	if ( is_singular( 'gfy_case_study' ) ) {
		$schemas[] = guestify_schema_case_study_review();
	}

	// Output all schemas
	foreach ( $schemas as $schema ) {
		if ( ! empty( $schema ) ) {
			printf(
				'<script type="application/ld+json">%s</script>' . "\n",
				wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT )
			);
		}
	}
}
add_action( 'wp_head', 'guestify_frontend_schema', 5 );


/**
 * Organization schema — sitewide.
 */
function guestify_schema_organization() {
	return [
		'@context'    => 'https://schema.org',
		'@type'       => 'Organization',
		'name'        => 'Guestify',
		'url'         => home_url( '/' ),
		'logo'        => get_template_directory_uri() . '/assets/images/guestify-logo.png',
		'description' => 'The Interview Authority System — the end-to-end platform that turns podcast guest interviews into authority, partnerships, and revenue.',
		'foundingDate' => '2024',
		'sameAs'      => [
			'https://www.linkedin.com/company/guestify',
			// Add other social profiles as they exist
		],
		'contactPoint' => [
			'@type'       => 'ContactPoint',
			'contactType' => 'customer support',
			'url'         => home_url( '/contact' ),
		],
	];
}


/**
 * WebApplication schema — homepage + product pages.
 */
function guestify_schema_web_application() {
	return [
		'@context'          => 'https://schema.org',
		'@type'             => 'WebApplication',
		'name'              => 'Guestify',
		'applicationCategory' => 'BusinessApplication',
		'operatingSystem'   => 'Web',
		'description'       => 'The Interview Authority System — find strategic podcast opportunities, position your expertise with AI, manage outreach systematically, and turn interviews into authority, partnerships, and revenue.',
		'url'               => home_url( '/product' ),
		'offers'            => [
			[
				'@type'         => 'Offer',
				'name'          => 'Free Trial',
				'price'         => '0',
				'priceCurrency' => 'USD',
				'description'   => '14-day free trial. No credit card required.',
				'url'           => home_url( '/start' ),
			],
			[
				'@type'         => 'Offer',
				'name'          => '90-Day Authority Launch Partner',
				'price'         => '1997',
				'priceCurrency' => 'USD',
				'description'   => 'Full Authority Stack system with onboarding, strategy session, and all six capability pillars.',
				'url'           => home_url( '/pricing' ),
			],
		],
		'featureList'       => [
			'Discovery Intelligence — Find the 10 right shows, not 1,000 random ones',
			'Authority Positioning — AI-powered media kit and pitch generation',
			'Outreach & Booking — Multi-channel host outreach system',
			'Interview Tracking — Pipeline management for podcast appearances',
			'Relationship Leverage — Turn interviews into partnerships and revenue',
			'Agency Operations — Multi-tenant management for podcast guesting agencies',
		],
		'creator'           => [
			'@type' => 'Organization',
			'name'  => 'Guestify',
			'url'   => home_url( '/' ),
		],
	];
}


/**
 * BreadcrumbList — auto-generated from page hierarchy.
 */
function guestify_schema_breadcrumb() {
	if ( is_front_page() ) {
		return null;
	}

	$items = [];
	$position = 1;

	// Home
	$items[] = [
		'@type'    => 'ListItem',
		'position' => $position++,
		'name'     => 'Home',
		'item'     => home_url( '/' ),
	];

	if ( is_singular() ) {
		$post = get_queried_object();

		// If page has parent, add parent to breadcrumb
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

		// For blog posts, add "Blog" as parent
		if ( 'post' === $post->post_type ) {
			$items[] = [
				'@type'    => 'ListItem',
				'position' => $position++,
				'name'     => 'Blog',
				'item'     => home_url( '/blog' ),
			];
		}

		// For case studies, add "Results" as parent
		if ( 'gfy_case_study' === $post->post_type ) {
			$items[] = [
				'@type'    => 'ListItem',
				'position' => $position++,
				'name'     => 'Results',
				'item'     => home_url( '/results' ),
			];
		}

		// Current page (no item URL — it's the current page)
		$items[] = [
			'@type'    => 'ListItem',
			'position' => $position,
			'name'     => $post->post_title,
		];
	} elseif ( is_post_type_archive( 'gfy_case_study' ) ) {
		$items[] = [
			'@type'    => 'ListItem',
			'position' => $position,
			'name'     => 'Results',
		];
	} elseif ( is_post_type_archive( 'gfy_resource' ) ) {
		$items[] = [
			'@type'    => 'ListItem',
			'position' => $position,
			'name'     => 'Resources',
		];
	} elseif ( is_home() ) {
		$items[] = [
			'@type'    => 'ListItem',
			'position' => $position,
			'name'     => 'Blog',
		];
	}

	return [
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $items,
	];
}


/**
 * Article schema — single blog posts.
 */
function guestify_schema_article() {
	$post = get_queried_object();
	if ( ! $post ) {
		return null;
	}

	$schema = [
		'@context'      => 'https://schema.org',
		'@type'         => 'Article',
		'headline'      => $post->post_title,
		'description'   => wp_trim_words( $post->post_content, 30 ),
		'url'           => get_permalink( $post ),
		'datePublished' => get_the_date( 'c', $post ),
		'dateModified'  => get_the_modified_date( 'c', $post ),
		'author'        => [
			'@type' => 'Organization',
			'name'  => 'Guestify',
			'url'   => home_url( '/' ),
		],
		'publisher'     => [
			'@type' => 'Organization',
			'name'  => 'Guestify',
			'logo'  => [
				'@type' => 'ImageObject',
				'url'   => get_template_directory_uri() . '/assets/images/guestify-logo.png',
			],
		],
		'mainEntityOfPage' => [
			'@type' => 'WebPage',
			'@id'   => get_permalink( $post ),
		],
	];

	// Add featured image if available
	if ( has_post_thumbnail( $post ) ) {
		$schema['image'] = get_the_post_thumbnail_url( $post, 'large' );
	}

	return $schema;
}


/**
 * Review schema — case studies.
 */
function guestify_schema_case_study_review() {
	$post = get_queried_object();
	if ( ! $post ) {
		return null;
	}

	return [
		'@context'    => 'https://schema.org',
		'@type'       => 'Review',
		'itemReviewed' => [
			'@type' => 'WebApplication',
			'name'  => 'Guestify',
		],
		'author'      => [
			'@type' => 'Person',
			'name'  => $post->post_title, // Case study title is typically the client name
		],
		'datePublished' => get_the_date( 'c', $post ),
		'reviewBody'    => wp_trim_words( $post->post_content, 50 ),
	];
}


/**
 * Helper — check if current page is a product page.
 */
function guestify_is_product_page() {
	if ( ! is_page() ) {
		return false;
	}

	$post = get_queried_object();
	if ( ! $post ) {
		return false;
	}

	// Check if current page or any ancestor has slug 'product'
	if ( 'product' === $post->post_name ) {
		return true;
	}

	if ( $post->post_parent ) {
		$parent = get_post( $post->post_parent );
		if ( $parent && 'product' === $parent->post_name ) {
			return true;
		}
	}

	return false;
}
