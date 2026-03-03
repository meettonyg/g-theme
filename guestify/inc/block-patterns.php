<?php
/**
 * Block Patterns Registration
 *
 * Registers Guestify block pattern categories and auto-loads
 * pattern files from the theme's /patterns/ directory.
 *
 * @package Guestify
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register Guestify block pattern categories and auto-load patterns.
 *
 * Each .php file inside /patterns/ must return an associative array with keys:
 *   - title       (string) Human-readable pattern name.
 *   - categories  (array)  One or more registered category slugs.
 *   - description (string) Short description shown in the inserter.
 *   - content     (string) Block markup.
 *
 * Patterns are registered under the 'guestify/' namespace using the
 * filename (without extension) as the slug.
 *
 * @return void
 */
function guestify_register_block_patterns() {

	/*
	|--------------------------------------------------------------------------
	| Pattern Categories
	|--------------------------------------------------------------------------
	*/
	$categories = array(
		'guestify-hero'    => __( 'Guestify — Hero Sections', 'guestify' ),
		'guestify-sections' => __( 'Guestify — Page Sections', 'guestify' ),
		'guestify-cta'     => __( 'Guestify — CTAs', 'guestify' ),
		'guestify-persona' => __( 'Guestify — Persona Sections', 'guestify' ),
		'guestify-product' => __( 'Guestify — Product Sections', 'guestify' ),
		'guestify-blog'    => __( 'Guestify — Blog Elements', 'guestify' ),
	);

	foreach ( $categories as $slug => $label ) {
		register_block_pattern_category( $slug, array( 'label' => $label ) );
	}

	/*
	|--------------------------------------------------------------------------
	| Auto-load Patterns from /patterns/ Directory
	|--------------------------------------------------------------------------
	*/
	$patterns_dir = get_template_directory() . '/patterns';

	if ( ! is_dir( $patterns_dir ) ) {
		return;
	}

	$pattern_files = glob( $patterns_dir . '/*.php' );

	if ( empty( $pattern_files ) ) {
		return;
	}

	foreach ( $pattern_files as $file ) {
		$pattern = include $file;

		// Validate that the file returned the expected array structure.
		if ( ! is_array( $pattern ) || empty( $pattern['title'] ) || empty( $pattern['content'] ) ) {
			continue;
		}

		$slug = 'guestify/' . basename( $file, '.php' );

		register_block_pattern( $slug, array(
			'title'       => $pattern['title'],
			'categories'  => isset( $pattern['categories'] ) ? (array) $pattern['categories'] : array(),
			'description' => isset( $pattern['description'] ) ? $pattern['description'] : '',
			'content'     => $pattern['content'],
		) );
	}
}
add_action( 'init', 'guestify_register_block_patterns' );
