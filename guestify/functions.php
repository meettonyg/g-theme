<?php
/**
 * Guestify functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Guestify
 */

if ( ! defined( '_S_VERSION' ) ) {
	// Replace the version number of the theme on each release.
	define( '_S_VERSION', '1.0.0' );
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function guestify_setup() {
	/*
		* Make theme available for translation.
		* Translations can be filed in the /languages/ directory.
		* If you're building a theme based on Guestify, use a find and replace
		* to change 'guestify' to the name of your theme in all the template files.
		*/
	load_theme_textdomain( 'guestify', get_template_directory() . '/languages' );

	// DO NOT add automatic RSS feed links - we're removing these manually
	// Removed: add_theme_support( 'automatic-feed-links' );

	/*
		* Let WordPress manage the document title.
		* By adding theme support, we declare that this theme does not use a
		* hard-coded <title> tag in the document head, and expect WordPress to
		* provide it for us.
		*/
	add_theme_support( 'title-tag' );

	/*
		* Enable support for Post Thumbnails on posts and pages.
		*
		* @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		*/
	add_theme_support( 'post-thumbnails' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus(
		array(
			'menu-1' => esc_html__( 'Primary', 'guestify' ),
		)
	);

	/*
		* Switch default core markup for search form, comment form, and comments
		* to output valid HTML5.
		*/
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Set up the WordPress core custom background feature.
	add_theme_support(
		'custom-background',
		apply_filters(
			'guestify_custom_background_args',
			array(
				'default-color' => 'ffffff',
				'default-image' => '',
			)
		)
	);

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	/**
	 * Add support for core custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);
}
add_action( 'after_setup_theme', 'guestify_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function guestify_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'guestify_content_width', 640 );
}
add_action( 'after_setup_theme', 'guestify_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function guestify_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'guestify' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'guestify' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'guestify_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function guestify_scripts() {
	wp_enqueue_style( 'guestify-style', get_stylesheet_uri(), array(), _S_VERSION );
	wp_style_add_data( 'guestify-style', 'rtl', 'replace' );

	// Enqueue Font Awesome
	wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0' );

	wp_enqueue_script( 'guestify-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'guestify_scripts' );

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}

/**
 * Load App Navigation functions.
 */
require get_template_directory() . '/inc/app-navigation-functions.php';

/**
 * Enqueue login CSS only for login page (ID: 34270)
 */
function guestify_enqueue_login_css() {
	if ( is_page( 34270 ) ) {
		wp_enqueue_style(
			'guestify-login',
			get_stylesheet_directory_uri() . '/css/login.css',
			array(),
			filemtime( get_stylesheet_directory() . '/css/login.css' )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'guestify_enqueue_login_css' );

/**
 * Remove WordPress block library CSS and other unnecessary styles
 */
function guestify_remove_block_styles() {
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'wc-blocks-style' ); // WooCommerce blocks if present
	wp_dequeue_style( 'global-styles' ); // Global styles
	wp_dequeue_style( 'classic-theme-styles' ); // Classic theme styles
	wp_deregister_style( 'classic-theme-styles' );
}
add_action( 'wp_enqueue_scripts', 'guestify_remove_block_styles', 100 );

/**
 * Remove block library CSS from admin
 */
function guestify_remove_block_styles_admin() {
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
}
add_action( 'admin_enqueue_scripts', 'guestify_remove_block_styles_admin', 100 );

/**
 * Remove WordPress head bloat - runs early to catch all actions
 */
function guestify_remove_head_bloat() {
	// Remove RSS feed links
	remove_action( 'wp_head', 'feed_links', 2 );
	remove_action( 'wp_head', 'feed_links_extra', 3 );
	
	// Remove WordPress emoji scripts and styles
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	
	// Remove other WordPress head bloat
	remove_action( 'wp_head', 'wp_generator' ); // WordPress version
	remove_action( 'wp_head', 'wlwmanifest_link' ); // Windows Live Writer
	remove_action( 'wp_head', 'rsd_link' ); // Really Simple Discovery
	remove_action( 'wp_head', 'wp_shortlink_wp_head' ); // Shortlink
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' ); // Prev/next links
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' ); // oEmbed discovery links
	remove_action( 'wp_head', 'wp_oembed_add_host_js' ); // oEmbed host JS
	remove_action( 'wp_head', 'rest_output_link_wp_head' ); // REST API link
}
add_action( 'after_setup_theme', 'guestify_remove_head_bloat' );

/**
 * Remove emoji from TinyMCE editor
 */
function guestify_remove_emoji_tinymce( $plugins ) {
	if ( is_array( $plugins ) ) {
		return array_diff( $plugins, array( 'wpemoji' ) );
	} else {
		return array();
	}
}
add_filter( 'tiny_mce_plugins', 'guestify_remove_emoji_tinymce' );

/**
 * Remove emoji DNS prefetch
 */
function guestify_remove_emoji_dns_prefetch( $urls, $relation_type ) {
	if ( 'dns-prefetch' === $relation_type ) {
		$emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );
		$urls = array_diff( $urls, array( $emoji_svg_url ) );
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'guestify_remove_emoji_dns_prefetch', 10, 2 );

/**
 * Disable XML-RPC for security
 */
add_filter( 'xmlrpc_enabled', '__return_false' );

/**
 * Remove query strings from static resources for better caching
 */
function guestify_remove_query_strings( $src ) {
	$parts = explode( '?ver', $src );
	return $parts[0];
}
add_filter( 'script_loader_src', 'guestify_remove_query_strings', 15, 1 );
add_filter( 'style_loader_src', 'guestify_remove_query_strings', 15, 1 );

/**
 * Disable WordPress heartbeat on frontend (keeps it for admin)
 */
function guestify_disable_heartbeat() {
	if ( ! is_admin() ) {
		wp_deregister_script( 'heartbeat' );
	}
}
add_action( 'init', 'guestify_disable_heartbeat', 1 );

function exclude_scripts_from_app() {
    // Check if we are on a page that is 'app' or a descendant of 'app'
    if ( is_page( 'app' ) || get_post_ancestors( get_the_ID() ) ) {
        $app_page = get_page_by_path( 'app' );
        if ( $app_page && in_array( $app_page->ID, get_post_ancestors( get_the_ID() ) ) ) {
             // Dequeue the script if it's an app page.
             // Replace 'guestify-navigation' with the actual script handle if it's different.
             wp_dequeue_script( 'guestify-navigation' );
        }
    }
}
// Run this function after the theme has enqueued its scripts, with a priority of 20.
add_action( 'wp_enqueue_scripts', 'exclude_scripts_from_app', 20 );