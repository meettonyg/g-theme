<?php
/**
 * Custom Post Types, Taxonomies, and Post Meta
 *
 * Registers the Case Study and Resource CPTs, their associated
 * taxonomies, default taxonomy terms, and resource post meta fields.
 *
 * @package Guestify
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/*
|--------------------------------------------------------------------------
| Custom Post Types
|--------------------------------------------------------------------------
*/

/**
 * Register the Case Study and Resource custom post types.
 *
 * @return void
 */
function guestify_register_post_types() {

	/*
	 * ── Case Study ──────────────────────────────────────────────────────
	 */
	$case_study_labels = array(
		'name'                  => __( 'Case Studies', 'guestify' ),
		'singular_name'         => __( 'Case Study', 'guestify' ),
		'add_new'               => __( 'Add New Case Study', 'guestify' ),
		'add_new_item'          => __( 'Add New Case Study', 'guestify' ),
		'edit_item'             => __( 'Edit Case Study', 'guestify' ),
		'new_item'              => __( 'New Case Study', 'guestify' ),
		'view_item'             => __( 'View Case Study', 'guestify' ),
		'search_items'          => __( 'Search Case Studies', 'guestify' ),
		'not_found'             => __( 'No case studies found.', 'guestify' ),
		'not_found_in_trash'    => __( 'No case studies found in Trash.', 'guestify' ),
		'all_items'             => __( 'All Case Studies', 'guestify' ),
		'archives'              => __( 'Case Study Archives', 'guestify' ),
		'featured_image'        => __( 'Featured Image', 'guestify' ),
		'set_featured_image'    => __( 'Set featured image', 'guestify' ),
		'remove_featured_image' => __( 'Remove featured image', 'guestify' ),
		'use_featured_image'    => __( 'Use as featured image', 'guestify' ),
	);

	register_post_type( 'gfy_case_study', array(
		'labels'        => $case_study_labels,
		'public'        => true,
		'has_archive'   => true,
		'show_in_rest'  => true,
		'rewrite'       => array(
			'slug'       => 'results',
			'with_front' => false,
		),
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		'menu_icon'     => 'dashicons-awards',
		'template'      => array(
			array( 'core/paragraph', array(
				'placeholder' => __( 'Challenge — describe the client\'s situation before working with Guestify...', 'guestify' ),
			) ),
			array( 'core/heading', array(
				'level'   => 3,
				'content' => __( 'Approach', 'guestify' ),
			) ),
			array( 'core/paragraph', array(
				'placeholder' => __( 'Describe the strategy and tactics used...', 'guestify' ),
			) ),
			array( 'core/heading', array(
				'level'   => 3,
				'content' => __( 'Results', 'guestify' ),
			) ),
			array( 'core/paragraph', array(
				'placeholder' => __( 'Quantifiable outcomes and key metrics...', 'guestify' ),
			) ),
			array( 'core/quote', array(
				'value' => '',
			), array(
				array( 'core/paragraph', array(
					'placeholder' => __( 'Client quote about the experience...', 'guestify' ),
				) ),
			) ),
		),
	) );

	/*
	 * ── Resource ────────────────────────────────────────────────────────
	 */
	$resource_labels = array(
		'name'                  => __( 'Resources', 'guestify' ),
		'singular_name'         => __( 'Resource', 'guestify' ),
		'add_new'               => __( 'Add New Resource', 'guestify' ),
		'add_new_item'          => __( 'Add New Resource', 'guestify' ),
		'edit_item'             => __( 'Edit Resource', 'guestify' ),
		'new_item'              => __( 'New Resource', 'guestify' ),
		'view_item'             => __( 'View Resource', 'guestify' ),
		'search_items'          => __( 'Search Resources', 'guestify' ),
		'not_found'             => __( 'No resources found.', 'guestify' ),
		'not_found_in_trash'    => __( 'No resources found in Trash.', 'guestify' ),
		'all_items'             => __( 'All Resources', 'guestify' ),
		'archives'              => __( 'Resource Archives', 'guestify' ),
		'featured_image'        => __( 'Cover Image', 'guestify' ),
		'set_featured_image'    => __( 'Set cover image', 'guestify' ),
		'remove_featured_image' => __( 'Remove cover image', 'guestify' ),
		'use_featured_image'    => __( 'Use as cover image', 'guestify' ),
	);

	register_post_type( 'gfy_resource', array(
		'labels'        => $resource_labels,
		'public'        => true,
		'has_archive'   => true,
		'show_in_rest'  => true,
		'rewrite'       => array(
			'slug'       => 'resources',
			'with_front' => false,
		),
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
		'menu_icon'     => 'dashicons-media-document',
	) );
}
add_action( 'init', 'guestify_register_post_types' );

/*
|--------------------------------------------------------------------------
| Taxonomies
|--------------------------------------------------------------------------
*/

/**
 * Register taxonomies for Case Studies and Posts.
 *
 * @return void
 */
function guestify_register_taxonomies() {

	/*
	 * ── Case Study Persona (hierarchical) ───────────────────────────────
	 */
	register_taxonomy( 'case_study_persona', 'gfy_case_study', array(
		'labels'            => array(
			'name'          => __( 'Case Study Personas', 'guestify' ),
			'singular_name' => __( 'Case Study Persona', 'guestify' ),
			'all_items'     => __( 'All Personas', 'guestify' ),
			'edit_item'     => __( 'Edit Persona', 'guestify' ),
			'view_item'     => __( 'View Persona', 'guestify' ),
			'add_new_item'  => __( 'Add New Persona', 'guestify' ),
			'search_items'  => __( 'Search Personas', 'guestify' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_in_rest'      => true,
		'rewrite'           => array(
			'slug'       => 'results/type',
			'with_front' => false,
		),
		'show_admin_column' => true,
	) );

	/*
	 * ── Content Pillar (hierarchical, internal-only) ────────────────────
	 */
	register_taxonomy( 'content_pillar', 'post', array(
		'labels'            => array(
			'name'          => __( 'Content Pillars', 'guestify' ),
			'singular_name' => __( 'Content Pillar', 'guestify' ),
			'all_items'     => __( 'All Content Pillars', 'guestify' ),
			'edit_item'     => __( 'Edit Content Pillar', 'guestify' ),
			'view_item'     => __( 'View Content Pillar', 'guestify' ),
			'add_new_item'  => __( 'Add New Content Pillar', 'guestify' ),
			'search_items'  => __( 'Search Content Pillars', 'guestify' ),
		),
		'hierarchical'      => true,
		'public'            => false,
		'rewrite'           => false,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'show_ui'           => true,
	) );

	/*
	 * ── Content Type (hierarchical, internal-only) ──────────────────────
	 */
	register_taxonomy( 'content_type', 'post', array(
		'labels'            => array(
			'name'          => __( 'Content Types', 'guestify' ),
			'singular_name' => __( 'Content Type', 'guestify' ),
			'all_items'     => __( 'All Content Types', 'guestify' ),
			'edit_item'     => __( 'Edit Content Type', 'guestify' ),
			'view_item'     => __( 'View Content Type', 'guestify' ),
			'add_new_item'  => __( 'Add New Content Type', 'guestify' ),
			'search_items'  => __( 'Search Content Types', 'guestify' ),
		),
		'hierarchical'      => true,
		'public'            => false,
		'rewrite'           => false,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'show_ui'           => true,
	) );
}
add_action( 'init', 'guestify_register_taxonomies' );

/*
|--------------------------------------------------------------------------
| Default Taxonomy Terms
|--------------------------------------------------------------------------
*/

/**
 * Insert default terms for custom taxonomies on theme activation.
 *
 * Safe to call multiple times; wp_insert_term() silently skips duplicates.
 *
 * @return void
 */
function guestify_insert_default_terms() {

	// Case Study Persona defaults.
	$persona_terms = array(
		'Authority Builder',
		'Revenue Generator',
		'Launch Promoter',
		'Agency',
	);
	foreach ( $persona_terms as $term ) {
		if ( ! term_exists( $term, 'case_study_persona' ) ) {
			wp_insert_term( $term, 'case_study_persona' );
		}
	}

	// Content Pillar defaults.
	$pillar_terms = array(
		'Authority Building',
		'Podcast Strategy',
		'Relationship Revenue',
		'Launch Visibility',
		'Agency Operations',
	);
	foreach ( $pillar_terms as $term ) {
		if ( ! term_exists( $term, 'content_pillar' ) ) {
			wp_insert_term( $term, 'content_pillar' );
		}
	}

	// Content Type defaults.
	$type_terms = array(
		'Pillar Page',
		'Supporting Post',
		'Resource Guide',
		'Comparison Post',
	);
	foreach ( $type_terms as $term ) {
		if ( ! term_exists( $term, 'content_type' ) ) {
			wp_insert_term( $term, 'content_type' );
		}
	}
}
add_action( 'after_switch_theme', 'guestify_insert_default_terms' );

/*
|--------------------------------------------------------------------------
| Post Meta for Resources
|--------------------------------------------------------------------------
*/

/**
 * Register post meta fields for the Resource CPT.
 *
 * Exposed in the REST API so they are available in the block editor
 * via custom-fields support.
 *
 * @return void
 */
function guestify_register_resource_meta() {

	$meta_fields = array(
		'gfy_resource_gated' => array(
			'type'    => 'boolean',
			'default' => true,
		),
		'gfy_resource_download_url' => array(
			'type'    => 'string',
			'default' => '',
		),
		'gfy_resource_target_persona' => array(
			'type'    => 'string',
			'default' => '',
		),
	);

	foreach ( $meta_fields as $key => $args ) {
		register_post_meta( 'gfy_resource', $key, array(
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => $args['type'],
			'default'       => $args['default'],
			'auth_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		) );
	}
}
add_action( 'init', 'guestify_register_resource_meta' );

/*
|--------------------------------------------------------------------------
| Flush Rewrite Rules on Activation
|--------------------------------------------------------------------------
*/

/**
 * Flush rewrite rules when the theme is activated so that
 * CPT and taxonomy slugs resolve immediately.
 *
 * @return void
 */
function guestify_flush_rewrite_rules_on_activation() {
	// Ensure CPTs and taxonomies are registered first.
	guestify_register_post_types();
	guestify_register_taxonomies();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'guestify_flush_rewrite_rules_on_activation' );
