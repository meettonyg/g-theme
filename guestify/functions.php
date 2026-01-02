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

// Google Tag Manager Container ID
if ( ! defined( 'GUESTIFY_GTM_ID' ) ) {
	define( 'GUESTIFY_GTM_ID', 'GTM-T4NDWXK' );
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

	// Register navigation menus
	register_nav_menus(
		array(
			'menu-1'   => esc_html__( 'Primary', 'guestify' ),
			'frontend' => esc_html__( 'Frontend Menu', 'guestify' ),
			'app-menu' => esc_html__( 'App Menu', 'guestify' ),
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

	// Conditionally enqueue the navigation script. It will not load on page with ID 46159.
	if ( ! is_page( '46159' ) ) {
		wp_enqueue_script( 'guestify-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true );
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'guestify_scripts' );

/**
 * Enqueue frontend header styles and scripts for public pages
 * Only loads on pages that are not app pages and not blank canvas pages
 */
function guestify_enqueue_frontend_header_assets() {
	// Only load on frontend pages (not app, not blank canvas)
	if ( function_exists( 'is_frontend_page' ) && is_frontend_page() ) {
		wp_enqueue_style(
			'guestify-frontend-header',
			get_template_directory_uri() . '/css/frontend-header.css',
			array(),
			filemtime( get_template_directory() . '/css/frontend-header.css' )
		);

		wp_enqueue_script(
			'guestify-frontend-header',
			get_template_directory_uri() . '/js/frontend-header.js',
			array(),
			filemtime( get_template_directory() . '/js/frontend-header.js' ),
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'guestify_enqueue_frontend_header_assets' );

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
 * Enqueue login CSS only for login page
 */
function guestify_enqueue_login_css() {
	if ( is_page( 'login' ) ) {
		$login_css_path = get_stylesheet_directory() . '/css/login.css';
		if ( file_exists( $login_css_path ) ) {
			wp_enqueue_style(
				'guestify-login',
				get_stylesheet_directory_uri() . '/css/login.css',
				array(),
				filemtime( $login_css_path )
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'guestify_enqueue_login_css' );

/**
 * Generic Login Form Shortcode
 *
 * Renders a WordPress login form with Nextend Social Login integration.
 * Nextend automatically attaches social login buttons to wp_login_form().
 *
 * Usage: [generic_login_form] or [generic_login_form redirect="https://example.com/dashboard"]
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML output for the login form.
 */
function guestify_generic_login_form_shortcode( $atts ) {
	// Check if user is already logged in
	if ( is_user_logged_in() ) {
		$current_user = wp_get_current_user();
		return '<div class="wpc-logged-in-msg">You are logged in as ' . esc_html( $current_user->display_name ) . '. <a href="' . esc_url( wp_logout_url( get_permalink() ) ) . '">Log out?</a></div>';
	}

	// Set defaults
	$atts = shortcode_atts( array(
		'redirect' => get_permalink(),
	), $atts );

	$output = '';

	// Display login errors from URL parameter
	if ( isset( $_GET['login_error'] ) ) {
		$error_code = sanitize_text_field( $_GET['login_error'] );
		$error_messages = array(
			'empty_username' => 'Please enter your username or email address.',
			'empty_password' => 'Please enter your password.',
			'invalid_username' => 'Unknown username. Please try again.',
			'invalid_email' => 'Unknown email address. Please try again.',
			'incorrect_password' => 'Incorrect password. Please try again.',
			'authentication_failed' => 'Login failed. Please check your credentials.',
		);
		$error_message = isset( $error_messages[ $error_code ] ) ? $error_messages[ $error_code ] : 'Login failed. Please try again.';
		$output .= '<div class="wpc-login-error">' . esc_html( $error_message ) . '</div>';
	}

	// Output the Standard WP Form (Nextend Social Login attaches here)
	$output .= wp_login_form( array(
		'echo'           => false,
		'redirect'       => esc_url( $atts['redirect'] ),
		'label_username' => 'Username or Email',
		'label_log_in'   => 'Log In',
	) );

	// Add "Lost Password" and "Register" links
	$output .= '<div class="wpc-login-links">';
	$output .= '<a href="' . esc_url( wp_lostpassword_url() ) . '">Lost Password?</a>';

	// Register link (only shows if enabled in WP Settings)
	if ( get_option( 'users_can_register' ) ) {
		$output .= '<span class="wpc-login-links-separator">|</span>';
		$output .= '<a href="' . esc_url( wp_registration_url() ) . '">Register</a>';
	}

	$output .= '</div>';

	return $output;
}
add_shortcode( 'generic_login_form', 'guestify_generic_login_form_shortcode' );

/**
 * Redirect failed logins back to frontend /login/ page
 *
 * Prevents users from seeing wp-login.php on authentication failure.
 */
function guestify_login_failed_redirect( $username ) {
	$referrer = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '';

	// Only redirect if coming from frontend /login/ page
	if ( ! empty( $referrer ) && strpos( $referrer, '/login' ) !== false && ! strpos( $referrer, 'wp-login.php' ) ) {
		$login_url = home_url( '/login/' );
		wp_redirect( add_query_arg( 'login_error', 'authentication_failed', $login_url ) );
		exit;
	}
}
add_action( 'wp_login_failed', 'guestify_login_failed_redirect' );

/**
 * Redirect to frontend /login/ page when username or password is empty
 *
 * Catches empty field submissions before WordPress processes them.
 */
function guestify_authenticate_empty_fields( $user, $username, $password ) {
	$referrer = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '';

	// Only redirect if coming from frontend /login/ page
	if ( ! empty( $referrer ) && strpos( $referrer, '/login' ) !== false && ! strpos( $referrer, 'wp-login.php' ) ) {
		$login_url = home_url( '/login/' );

		if ( empty( $username ) ) {
			wp_redirect( add_query_arg( 'login_error', 'empty_username', $login_url ) );
			exit;
		}

		if ( empty( $password ) ) {
			wp_redirect( add_query_arg( 'login_error', 'empty_password', $login_url ) );
			exit;
		}
	}

	return $user;
}
add_filter( 'authenticate', 'guestify_authenticate_empty_fields', 1, 3 );

/**
 * Enqueue partners CSS and JS only for partners page
 */
function guestify_enqueue_partners_assets() {
	if ( is_page( 'partners' ) ) {
		// Enqueue CSS
		$css_path = get_stylesheet_directory() . '/css/partners.css';
		if ( file_exists( $css_path ) ) {
			wp_enqueue_style(
				'guestify-partners',
				get_stylesheet_directory_uri() . '/css/partners.css',
				array(),
				filemtime( $css_path )
			);
		}

		// Enqueue JS (vanilla JS, no dependencies, loaded in footer)
		$js_path = get_stylesheet_directory() . '/js/partners.js';
		if ( file_exists( $js_path ) ) {
			wp_enqueue_script(
				'guestify-partners',
				get_stylesheet_directory_uri() . '/js/partners.js',
				array(),
				filemtime( $js_path ),
				true
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'guestify_enqueue_partners_assets' );

/**
 * Enqueue pricing CSS and JS only for pricing page
 */
function guestify_enqueue_pricing_assets() {
	if ( is_page( 'pricing' ) ) {
		// Enqueue CSS
		$css_path = get_stylesheet_directory() . '/css/pricing.css';
		if ( file_exists( $css_path ) ) {
			wp_enqueue_style(
				'guestify-pricing',
				get_stylesheet_directory_uri() . '/css/pricing.css',
				array(),
				filemtime( $css_path )
			);
		}

		// Enqueue JS (vanilla JS, no dependencies, loaded in footer)
		$js_path = get_stylesheet_directory() . '/js/pricing.js';
		if ( file_exists( $js_path ) ) {
			wp_enqueue_script(
				'guestify-pricing',
				get_stylesheet_directory_uri() . '/js/pricing.js',
				array(),
				filemtime( $js_path ),
				true
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'guestify_enqueue_pricing_assets' );

/**
 * Enqueue resources CSS only for resources page
 */
function guestify_enqueue_resources_assets() {
	if ( is_page( 'resources' ) ) {
		// Enqueue CSS
		$css_path = get_stylesheet_directory() . '/css/resources.css';
		if ( file_exists( $css_path ) ) {
			wp_enqueue_style(
				'guestify-resources',
				get_stylesheet_directory_uri() . '/css/resources.css',
				array(),
				filemtime( $css_path )
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'guestify_enqueue_resources_assets' );

/**
 * Enqueue homepage CSS only for homepage (ID: 46263)
 */
function guestify_enqueue_homepage_css() {
	if ( is_page( 46263 ) ) {
		$css_path = WP_CONTENT_DIR . '/css/homepage.css';
		$css_url  = content_url( '/css/homepage.css' );
		$version  = file_exists( $css_path ) ? filemtime( $css_path ) : '1.0.0';

		wp_enqueue_style(
			'guestify-homepage',
			$css_url,
			array(),
			$version
		);
	}
}
add_action( 'wp_enqueue_scripts', 'guestify_enqueue_homepage_css' );

/**
 * Guestify New Theme Pages CSS Enqueue
 *
 * Data-driven approach for loading section-specific CSS files.
 * Add new pages by updating the $page_styles configuration array.
 */
function guestify_enqueue_new_theme_pages_css() {
	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
	$css_dir     = WP_CONTENT_DIR . '/css/';
	$css_url     = content_url( '/css/' );

	// Configuration: URL patterns mapped to their CSS files
	// Format: 'key' => [ 'patterns' => [...], 'styles' => [ [handle, file, deps], ... ] ]
	$page_styles = array(
		'tips' => array(
			'patterns' => array( '#^/tips(/|/tip-[1-3]/?)?$#' ),
			'styles'   => array(
				array( 'base', 'base.css', array() ),
				array( 'components', 'components.css', array( 'base' ) ),
				array( 'layout', 'layout.css', array( 'base' ) ),
				array( 'tips', 'tips.css', array( 'base', 'components', 'layout' ) ),
			),
		),
		'admin' => array(
			'patterns' => array(
				'#^/app/downgrade(-confirmation)?/?$#',
				'#^/(reset|whitelist|password-reset-confirmation)/?$#',
			),
			'styles' => array(
				array( 'base', 'base.css', array() ),
				array( 'components', 'components.css', array( 'base' ) ),
				array( 'layout', 'layout.css', array( 'base' ) ),
				array( 'admin', 'admin.css', array( 'base', 'components', 'layout' ) ),
			),
		),
		'demo' => array(
			'patterns' => array( '#^/demo(/demo-[1-4])?/?$#' ),
			'styles'   => array(
				array( 'demo-core', 'demo-core.css', array() ),
			),
		),
		'landing' => array(
			'patterns' => array( '#^/app/(audience-builder|interview|message-builder|prospector|value-builder)/?$#' ),
			'styles'   => array(
				array( 'base', 'base.css', array() ),
				array( 'landing', 'landing.css', array( 'base' ) ),
			),
		),
		'onboarding' => array(
			'patterns' => array(
				'#^/app/leaderboards/walkthrough(-confirmation)?/?$#',
				'#^/demo/personalized/?$#',
			),
			'styles' => array(
				array( 'base', 'base.css', array() ),
				array( 'onboarding', 'onboarding.css', array( 'base' ) ),
			),
		),
		'training' => array(
			'patterns' => array( '#^/training/?$#' ),
			'styles'   => array(
				array( 'training', 'training.css', array() ),
			),
		),
		'workshop' => array(
			'patterns' => array( '#^/workshop-replay/?$#' ),
			'styles'   => array(
				array( 'workshop-replay', 'workshop-replay.css', array() ),
			),
		),
		'about' => array(
			'patterns' => array( '#^/about/?$#' ),
			'styles'   => array(
				array( 'about', 'about.css', array() ),
			),
		),
		'contact' => array(
			'patterns' => array( '#^/contact/?$#' ),
			'styles'   => array(
				array( 'contact', 'contact.css', array() ),
			),
		),
		'app-home' => array(
			'patterns' => array( '#^/app/?$#' ),
			'styles'   => array(
				array( 'app-home', 'app-home.css', array() ),
			),
		),
	);

	// Find matching page configuration
	$matched_styles = null;
	foreach ( $page_styles as $config ) {
		foreach ( $config['patterns'] as $pattern ) {
			if ( preg_match( $pattern, $request_uri ) === 1 ) {
				$matched_styles = $config['styles'];
				break 2;
			}
		}
	}

	// Exit if no match found
	if ( null === $matched_styles ) {
		return;
	}

	// Enqueue matched CSS files
	foreach ( $matched_styles as $style ) {
		list( $handle, $file, $deps ) = $style;
		$path = $css_dir . $file;

		if ( file_exists( $path ) ) {
			wp_enqueue_style(
				'guestify-new-theme-' . $handle,
				$css_url . $file,
				array_map( function( $d ) { return 'guestify-new-theme-' . $d; }, $deps ),
				filemtime( $path )
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'guestify_enqueue_new_theme_pages_css', 25 );

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
 * LearnPress Asset Removal (non-LearnPress pages only)
 *
 * Removes LearnPress styles, scripts, and inline code from pages
 * that don't need them. Uses early hooks to ensure removal before rendering.
 */
function guestify_learnpress_asset_stripper() {
	// If we are on any LearnPress page, do nothing.
	if ( function_exists( 'is_learnpress' ) && is_learnpress() ) {
		return;
	}

	// Dequeue LearnPress styles
	add_action( 'wp_print_styles', function() {
		$style_handles = array(
			'learnpress',
			'learnpress-widgets',
			'learnpress-profile',
			'learn-press-custom',
		);
		foreach ( $style_handles as $handle ) {
			wp_dequeue_style( $handle );
		}
	}, 1 );

	// Dequeue LearnPress scripts
	add_action( 'wp_print_scripts', function() {
		$script_handles = array(
			'learn-press-frontend',
			'lp-utils',
			'lp-profile',
			'lp-setting-courses',
			'lp-load-ajax',
		);
		foreach ( $script_handles as $handle ) {
			wp_dequeue_script( $handle );
		}
	}, 1 );

	// Remove LearnPress inline scripts from head
	if ( function_exists( 'learn_press_assets' ) && ( $lp_assets_instance = learn_press_assets() ) ) {
		remove_action( 'wp_head', array( $lp_assets_instance, 'load_scripts_styles_on_head' ), -1 );
	}
}
add_action( 'init', 'guestify_learnpress_asset_stripper', 1 );

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

/**
 * Dequeue public scripts and styles on app pages and new theme pages for a clean slate.
 * This runs after the main guestify_scripts hook to ensure it overrides it.
 * 
 * ROOT CAUSE FIX: Expanded to include 22 new theme pages that use standalone CSS
 * CHECKLIST COMPLIANCE:
 * ✅ Root Cause Fix - Controls CSS scope at source, not patching
 * ✅ Simplicity First - One function controls all exclusions
 * ✅ No Redundant Logic - Single source of truth for CSS scope
 * ✅ Maintainability - Clear documentation of excluded pages
 */
function guestify_dequeue_public_assets_on_app_pages() {
    if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
        return;
    }
    
    $request_uri = $_SERVER['REQUEST_URI'];
    
    // Pattern matching for pages that should NOT load theme style.css
    $exclude_patterns = array(
        // All /app/ pages (existing behavior)
        '#^/app/#',
        
        // Tips pages (4 pages)
        '#^/tips(/|/tip-[1-3]/?)?$#',
        
        // Admin pages (5 pages)
        '#^/app/downgrade(-confirmation)?/?$#',
        '#^/(reset|whitelist|password-reset-confirmation)/?$#',
        
        // Demo pages (5 pages)
        '#^/demo(/demo-[1-4])?/?$#',
        '#^/demo/personalized/?$#',
        
        // Landing pages (5 pages)
        '#^/app/(audience-builder|interview|message-builder|prospector|value-builder)/?$#',
        
        // Onboarding pages (2 pages)
        '#^/app/leaderboards/walkthrough(-confirmation)?/?$#',
    );
    
    // Check if current URL matches any exclude pattern
    $should_exclude = false;
    foreach ( $exclude_patterns as $pattern ) {
        if ( preg_match( $pattern, $request_uri ) === 1 ) {
            $should_exclude = true;
            break;
        }
    }
    
    if ( $should_exclude ) {
        // Remove the main stylesheet
        wp_dequeue_style( 'guestify-style' );
        wp_deregister_style( 'guestify-style' );

        // Remove the public-facing navigation script
        wp_dequeue_script( 'guestify-navigation' );
        wp_deregister_script( 'guestify-navigation' );
        
        // Debug logging (optional)
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '🚫 Guestify Theme: Dequeued style.css for: ' . $request_uri );
        }
    }
}
// Hook with a priority of 20 to run after the default enqueue action.
add_action( 'wp_enqueue_scripts', 'guestify_dequeue_public_assets_on_app_pages', 20 );

/**
 * Dequeue scripts and styles on page 46159.
 */
function guestify_dequeue_assets_on_specific_page() {
    if ( is_page( '46159' ) ) {
        // Dequeue the navigation script
        wp_dequeue_script( 'guestify-navigation' );
        wp_deregister_script( 'guestify-navigation' );

        // Dequeue jQuery scripts
        wp_dequeue_script( 'jquery-core' );
        wp_deregister_script( 'jquery-core' );
        wp_dequeue_script( 'jquery-migrate' );
        wp_deregister_script( 'jquery-migrate' );

        // Dequeue the Guestify stylesheet
        wp_dequeue_style( 'guestify-style' );
        wp_deregister_style( 'guestify-style' );

    }
}
add_action( 'wp_enqueue_scripts', 'guestify_dequeue_assets_on_specific_page', 99 );

/**
 * Disable wpautop for all pages except app and public-facing templates
 *
 * Preserves HTML structure on most pages while keeping wpautop enabled
 * for /app/ pages and public-facing content that rely on auto-paragraphs.
 *
 * Pages that KEEP wpautop (auto-paragraphs enabled):
 * - All /app/* pages
 * - Homepage, blog posts, and other public-facing content
 *
 * Pages that DISABLE wpautop:
 * - All other pages (structured HTML pages, landing pages, etc.)
 */
function guestify_disable_wpautop_selectively() {
    if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
        return;
    }

    $request_uri = $_SERVER['REQUEST_URI'];

    // Pages that should KEEP wpautop (auto-paragraphs enabled)
    $keep_wpautop_patterns = array(
        '#^/app/#',           // All app pages
        '#^/$#',              // Homepage
        '#^/blog#',           // Blog pages
        '#^/category/#',      // Category archives
        '#^/tag/#',           // Tag archives
        '#^/author/#',        // Author archives
    );

    // Check if current URL should keep wpautop
    foreach ( $keep_wpautop_patterns as $pattern ) {
        if ( preg_match( $pattern, $request_uri ) === 1 ) {
            // Keep wpautop enabled for these pages
            return;
        }
    }

    // Disable wpautop for all other pages
    remove_filter( 'the_content', 'wpautop' );
    remove_filter( 'the_excerpt', 'wpautop' );
}
add_action( 'init', 'guestify_disable_wpautop_selectively' );

/**
 * TEMPORARY: Debug Media Kit Frontend Display
 * Remove this after debugging is complete
 */
if (file_exists(WP_PLUGIN_DIR . '/mk4/debug-frontend-display.php')) {
    require_once WP_PLUGIN_DIR . '/mk4/debug-frontend-display.php';
}

/**
 * Google Tag Manager Integration
 */

/**
 * Renders the Google Tag Manager noscript tag.
 */
function guestify_render_gtm_noscript() {
	if ( ! defined( 'GUESTIFY_GTM_ID' ) ) {
		return;
	}
	echo '<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=' . esc_attr( GUESTIFY_GTM_ID ) . '"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->';
}

// GTM Head Script - Site Wide
add_action( 'wp_head', function() {
	if ( ! defined( 'GUESTIFY_GTM_ID' ) ) {
		return;
	}
	echo '<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({\'gtm.start\':
new Date().getTime(),event:\'gtm.js\'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!=\'dataLayer\'?\'&l=\'+l:\'\';j.async=true;j.src=
\'https://www.googletagmanager.com/gtm.js?id=\'+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,\'script\',\'dataLayer\',\'' . esc_js( GUESTIFY_GTM_ID ) . '\');</script>
<!-- End Google Tag Manager -->';
}, 1 );

// GTM Body Script - Site Wide
add_action( 'wp_body_open', 'guestify_render_gtm_noscript', 1 );

// Fallback for themes that don't support wp_body_open
add_action( 'wp_footer', function() {
	if ( ! did_action( 'wp_body_open' ) ) {
		guestify_render_gtm_noscript();
	}
} );

/**
 * Hide admin bar for non-administrators on frontend
 */
add_action( 'after_setup_theme', function() {
	if ( ! current_user_can( 'administrator' ) && ! is_admin() ) {
		show_admin_bar( false );
	}
});