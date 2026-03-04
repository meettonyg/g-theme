<?php
/**
 * Frontend Asset Enqueue
 *
 * Conditionally enqueues CSS and JS for frontend (non-app) pages.
 * Relies on the existing is_frontend_page() and is_app_page() helpers
 * and the 'guestify-tokens' stylesheet handle already registered at priority 1.
 *
 * @package Guestify
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue frontend-specific stylesheets based on the current page context.
 *
 * Hooked to wp_enqueue_scripts at default priority (10).
 *
 * @return void
 */
function guestify_frontend_page_assets() {

	// Bail early if not a frontend page.
	if ( ! function_exists( 'is_frontend_page' ) || ! is_frontend_page() ) {
		return;
	}

	$theme_uri = get_template_directory_uri();
	$theme_dir = get_template_directory();

	/*
	|----------------------------------------------------------------------
	| Global Frontend Styles (all frontend pages)
	|----------------------------------------------------------------------
	*/
	wp_enqueue_style(
		'gfy-frontend-global',
		$theme_uri . '/css/frontend-global.css',
		array( 'guestify-tokens' ),
		file_exists( $theme_dir . '/css/frontend-global.css' )
			? filemtime( $theme_dir . '/css/frontend-global.css' )
			: null
	);

	wp_enqueue_style(
		'gfy-frontend-sections',
		$theme_uri . '/css/frontend-sections.css',
		array( 'gfy-frontend-global' ),
		file_exists( $theme_dir . '/css/frontend-sections.css' )
			? filemtime( $theme_dir . '/css/frontend-sections.css' )
			: null
	);

	/*
	|----------------------------------------------------------------------
	| Homepage
	|----------------------------------------------------------------------
	*/
	if ( is_front_page() ) {
		wp_enqueue_style(
			'gfy-frontend-home',
			$theme_uri . '/css/frontend-home.css',
			array( 'gfy-frontend-sections' ),
			file_exists( $theme_dir . '/css/frontend-home.css' )
				? filemtime( $theme_dir . '/css/frontend-home.css' )
				: null
		);
	}

	/*
	|----------------------------------------------------------------------
	| Persona Pages
	|----------------------------------------------------------------------
	*/
	if ( is_page_template( 'templates/template-persona.php' ) ) {
		wp_enqueue_style(
			'gfy-frontend-persona',
			$theme_uri . '/css/frontend-persona.css',
			array( 'gfy-frontend-sections' ),
			file_exists( $theme_dir . '/css/frontend-persona.css' )
				? filemtime( $theme_dir . '/css/frontend-persona.css' )
				: null
		);
	}

	/*
	|----------------------------------------------------------------------
	| Product Pages
	|----------------------------------------------------------------------
	*/
	if ( is_page_template( 'templates/template-product.php' ) ) {
		wp_enqueue_style(
			'gfy-frontend-product',
			$theme_uri . '/css/frontend-product.css',
			array( 'gfy-frontend-sections' ),
			file_exists( $theme_dir . '/css/frontend-product.css' )
				? filemtime( $theme_dir . '/css/frontend-product.css' )
				: null
		);
	}

	/*
	|----------------------------------------------------------------------
	| Blog Pages (single post, blog index, category, tag archives)
	|----------------------------------------------------------------------
	*/
	if ( is_singular( 'post' ) || is_home() || is_category() || is_tag() ) {
		wp_enqueue_style(
			'gfy-frontend-blog',
			$theme_uri . '/css/frontend-blog.css',
			array( 'gfy-frontend-sections' ),
			file_exists( $theme_dir . '/css/frontend-blog.css' )
				? filemtime( $theme_dir . '/css/frontend-blog.css' )
				: null
		);
	}

	/*
	|----------------------------------------------------------------------
	| CPT Pages (Case Studies & Resources)
	|----------------------------------------------------------------------
	*/
	if (
		is_singular( 'gfy_case_study' )
		|| is_post_type_archive( 'gfy_case_study' )
		|| is_singular( 'gfy_resource' )
		|| is_post_type_archive( 'gfy_resource' )
	) {
		wp_enqueue_style(
			'gfy-frontend-resources',
			$theme_uri . '/css/frontend-resources.css',
			array( 'gfy-frontend-sections' ),
			file_exists( $theme_dir . '/css/frontend-resources.css' )
				? filemtime( $theme_dir . '/css/frontend-resources.css' )
				: null
		);
	}
}
add_action( 'wp_enqueue_scripts', 'guestify_frontend_page_assets' );

/**
 * Dequeue app-only and unnecessary styles/scripts on frontend pages.
 *
 * Runs at priority 100 so it fires after the assets have been enqueued.
 *
 * @return void
 */
function guestify_dequeue_app_on_frontend() {

	if ( ! function_exists( 'is_frontend_page' ) || ! is_frontend_page() ) {
		return;
	}

	// App-only styles.
	wp_dequeue_style( 'gfy-app-navigation' );
	wp_dequeue_style( 'gfy-dashboard' );
	wp_dequeue_style( 'gfy-account' );
	wp_dequeue_style( 'gfy-command-palette' );

	// App-only scripts.
	wp_dequeue_script( 'gfy-command-palette' );

	// Keep wp-block-library-theme and classic-theme-styles —
	// frontend pages use block markup and need these.
}
add_action( 'wp_enqueue_scripts', 'guestify_dequeue_app_on_frontend', 100 );
