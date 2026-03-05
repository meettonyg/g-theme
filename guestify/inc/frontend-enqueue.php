<?php
/**
 * Frontend Asset Enqueue
 *
 * Conditionally enqueues CSS and JS for frontend (non-app) pages.
 * Relies on the existing is_public_page() and is_app_page() helpers
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
	if ( ! function_exists( 'is_public_page' ) || ! is_public_page() ) {
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
	if ( is_front_page() || is_page( 'home' ) ) {
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
 * Uses both wp_dequeue and wp_deregister to prevent plugins from
 * re-enqueuing assets via dependency chains.
 *
 * @return void
 */
function guestify_dequeue_app_on_frontend() {

	if ( ! function_exists( 'is_public_page' ) || ! is_public_page() ) {
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
	wp_dequeue_style( 'guestify-home' );
	wp_deregister_style( 'guestify-home' );

	/*
	|----------------------------------------------------------------------
	| Font Awesome (~40KB) — not used on any frontend page.
	|----------------------------------------------------------------------
	*/
	wp_dequeue_style( 'font-awesome' );
	wp_deregister_style( 'font-awesome' );

	/*
	|----------------------------------------------------------------------
	| Plugin Styles — app-only
	|----------------------------------------------------------------------
	*/
	wp_dequeue_style( 'gfy-agency-switcher' );
	wp_deregister_style( 'gfy-agency-switcher' );

	/*
	|----------------------------------------------------------------------
	| WP Fusion admin bar — not needed on marketing pages.
	|----------------------------------------------------------------------
	*/
	wp_dequeue_style( 'wpf-admin-bar' );
	wp_deregister_style( 'wpf-admin-bar' );

	/*
	|----------------------------------------------------------------------
	| Legacy homepage CSS — superseded by frontend-home.css.
	|----------------------------------------------------------------------
	*/
	wp_dequeue_style( 'guestify-homepage' );

	/*
	|----------------------------------------------------------------------
	| WordPress Core Block Styles — KEEP these.
	|
	| The HTML uses WordPress block markup (wp-block-group, wp-block-columns,
	| wp-block-buttons) which depends on global-styles and block CSS for
	| is-layout-flex, is-layout-constrained, is-content-justification-center.
	| Removing them breaks the grid/flex layouts across all sections.
	|----------------------------------------------------------------------
	*/
	wp_dequeue_style( 'classic-theme-styles' );

	/*
	|----------------------------------------------------------------------
	| NextEnd Social Login — not needed on marketing pages.
	|----------------------------------------------------------------------
	*/
	wp_dequeue_style( 'nextend-social-login' );
	wp_deregister_style( 'nextend-social-login' );
	wp_dequeue_script( 'nextend-social-login' );
	wp_deregister_script( 'nextend-social-login' );

	/*
	|----------------------------------------------------------------------
	| App-Only Scripts
	|----------------------------------------------------------------------
	*/
	wp_dequeue_script( 'gfy-command-palette' );
	wp_dequeue_script( 'gfy-agency-switcher' );
	wp_deregister_script( 'gfy-agency-switcher' );
	wp_dequeue_script( 'wpf-admin-bar' );
	wp_deregister_script( 'wpf-admin-bar' );
	wp_dequeue_script( 'guestify-home' );
	wp_deregister_script( 'guestify-home' );

	/*
	|----------------------------------------------------------------------
	| jQuery + jQuery Migrate (~90KB) — not needed.
	| navigation.js and frontend-header.js are vanilla JS.
	| Deregister to prevent dependency-chain re-enqueuing.
	|----------------------------------------------------------------------
	*/
	wp_dequeue_script( 'jquery' );
	wp_dequeue_script( 'jquery-core' );
	wp_dequeue_script( 'jquery-migrate' );
	wp_deregister_script( 'jquery' );
	wp_deregister_script( 'jquery-core' );
	wp_deregister_script( 'jquery-migrate' );
}
add_action( 'wp_enqueue_scripts', 'guestify_dequeue_app_on_frontend', 100 );

/**
 * Strip extraneous inline CSS from wp_head on frontend pages.
 *
 * NSL prints its CSS via echo inside wp_head hooks (not through the
 * enqueue system), making it impossible to remove with wp_dequeue.
 * Output buffering captures everything printed inside wp_head and
 * strips the unwanted blocks before they reach the browser.
 *
 * Also catches global-styles-inline-css as a fallback if wp_deregister
 * didn't prevent WordPress from re-adding it.
 */
function guestify_clean_head_start() {
	if ( function_exists( 'is_public_page' ) && is_public_page() ) {
		ob_start();
	}
}
add_action( 'wp_head', 'guestify_clean_head_start', 0 );

function guestify_clean_head_end() {
	if ( ! function_exists( 'is_public_page' ) || ! is_public_page() || ! ob_get_level() ) {
		return;
	}

	$html = ob_get_clean();

	// NSL button styles  (starts with div.nsl-container)
	$html = preg_replace( '#<style[^>]*>\s*div\.nsl-container.*?</style>\s*#s', '', $html );

	// NSL notice-fallback styles  (starts with /* Notice fallback */)
	$html = preg_replace( '#<style[^>]*>\s*/\* Notice fallback \*/.*?</style>\s*#s', '', $html );

	// NSL _nslDOMReady helper script
	$html = preg_replace( '#<script[^>]*>\s*window\._nslDOMReady\s*=.*?</script>\s*#s', '', $html );

	// NOTE: global-styles-inline-css is intentionally KEPT.
	// It provides is-layout-flex, is-layout-constrained, and gap rules
	// required by the WordPress block markup used across all frontend pages.

	echo $html;
}
add_action( 'wp_head', 'guestify_clean_head_end', PHP_INT_MAX );

/**
 * Strip NSL inline JavaScript from wp_footer on frontend pages.
 */
function guestify_clean_footer_start() {
	if ( function_exists( 'is_public_page' ) && is_public_page() ) {
		ob_start();
	}
}
add_action( 'wp_footer', 'guestify_clean_footer_start', 0 );

function guestify_clean_footer_end() {
	if ( ! function_exists( 'is_public_page' ) || ! is_public_page() || ! ob_get_level() ) {
		return;
	}

	$html = ob_get_clean();

	// NSL main inline script  (starts with (function (undefined))
	$html = preg_replace( '#<script[^>]*>\s*\(function\s*\(undefined\).*?</script>\s*#s', '', $html );

	echo $html;
}
add_action( 'wp_footer', 'guestify_clean_footer_end', PHP_INT_MAX );
