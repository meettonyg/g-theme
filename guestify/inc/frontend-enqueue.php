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
 * Runs at priority 100 so it fires after all assets have been enqueued.
 * Frontend marketing pages use custom CSS (frontend-global.css, frontend-sections.css)
 * that fully replaces WordPress block inline styles, so we can safely remove them
 * along with other app-only and plugin assets to cut ~150KB+ of dead weight.
 *
 * @return void
 */
function guestify_dequeue_app_on_frontend() {

	if ( ! function_exists( 'is_frontend_page' ) || ! is_frontend_page() ) {
		return;
	}

	/*
	|----------------------------------------------------------------------
	| App-Only Styles
	|----------------------------------------------------------------------
	*/
	wp_dequeue_style( 'gfy-app-navigation' );
	wp_dequeue_style( 'gfy-dashboard' );
	wp_dequeue_style( 'gfy-account' );
	wp_dequeue_style( 'gfy-command-palette' );

	/*
	|----------------------------------------------------------------------
	| Font Awesome (~40KB) — not used on any frontend page.
	| Only the app-navigation uses fa- classes.
	|----------------------------------------------------------------------
	*/
	wp_dequeue_style( 'font-awesome' );

	/*
	|----------------------------------------------------------------------
	| Plugin Styles — app-only
	|----------------------------------------------------------------------
	*/
	wp_dequeue_style( 'gfy-agency-switcher' );

	/*
	|----------------------------------------------------------------------
	| WordPress Block Inline CSS — replaced by frontend-global.css
	| and frontend-sections.css with bridge rules.
	|
	| NOTE: We keep 'global-styles-inline-css' because it contains
	| critical layout primitives (.is-layout-flex, .is-layout-flow).
	|----------------------------------------------------------------------
	*/
	wp_dequeue_style( 'classic-theme-styles' );
	wp_dequeue_style( 'wp-block-button' );
	wp_dequeue_style( 'wp-block-buttons' );
	wp_dequeue_style( 'wp-block-heading' );
	wp_dequeue_style( 'wp-block-image' );
	wp_dequeue_style( 'wp-block-list' );
	wp_dequeue_style( 'wp-block-paragraph' );
	wp_dequeue_style( 'wp-block-columns' );
	wp_dequeue_style( 'wp-block-group' );

	/*
	|----------------------------------------------------------------------
	| NextEnd Social Login — not needed on marketing pages.
	|----------------------------------------------------------------------
	*/
	wp_dequeue_style( 'nsl-inline-css' );
	wp_dequeue_script( 'nextend-social-login' );

	/*
	|----------------------------------------------------------------------
	| App-Only Scripts
	|----------------------------------------------------------------------
	*/
	wp_dequeue_script( 'gfy-command-palette' );
	wp_dequeue_script( 'gfy-agency-switcher' );
	wp_dequeue_script( 'wpf-admin-bar' );

	/*
	|----------------------------------------------------------------------
	| jQuery + jQuery Migrate (~90KB) — not needed.
	| navigation.js and frontend-header.js are vanilla JS.
	| Dequeuing agency-switcher (above) removes the only dependency.
	| If another plugin re-adds jQuery as a dependency, WP will
	| re-enqueue it automatically.
	|----------------------------------------------------------------------
	*/
	wp_dequeue_script( 'jquery' );
	wp_dequeue_script( 'jquery-core' );
	wp_dequeue_script( 'jquery-migrate' );
}
add_action( 'wp_enqueue_scripts', 'guestify_dequeue_app_on_frontend', 100 );
