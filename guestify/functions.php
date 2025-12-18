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
 * Enqueue homepage CSS only for homepage (ID: 46263)
 */
function guestify_enqueue_homepage_css() {
	if ( is_page( 46263 ) ) {
		$css_file = ABSPATH . 'wp-content/css/homepage.css';
		$version = file_exists( $css_file ) ? filemtime( $css_file ) : '1.0.0';

		wp_enqueue_style(
			'guestify-homepage',
			'/wp-content/css/homepage.css',
			array(),
			$version
		);
	}
}
add_action( 'wp_enqueue_scripts', 'guestify_enqueue_homepage_css' );

/**
 * Guestify New Theme Pages CSS Enqueue
 *
 * Loads section-specific CSS files for theme pages:
 * - Tips pages (4): /tips/
 * - Admin pages (5): /app/downgrade/, /reset/, /whitelist/, etc.
 * - Demo pages (5): /demo/
 * - Landing pages (5): /app/audience-builder/, /app/interview/, etc.
 * - Onboarding pages (3): /app/leaderboards/walkthrough/, /demo/personalized/
 * - Events pages (2): /training/, /workshop-replay/
 * - Site pages (2): /about/, /contact/
 * - App home (1): /app/
 */
function guestify_enqueue_new_theme_pages_css() {
	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';

	// Base CSS directory
	$css_dir = WP_CONTENT_DIR . '/css/';
	$css_url = content_url( '/css/' );

	// Pattern detection
	$is_tips_page = preg_match( '#^/tips(/|/tip-[1-3]/?)?$#', $request_uri ) === 1;

	$is_admin_downgrade = preg_match( '#^/app/downgrade(-confirmation)?/?$#', $request_uri ) === 1;
	$is_admin_other = preg_match( '#^/(reset|whitelist|password-reset-confirmation)/?$#', $request_uri ) === 1;
	$is_admin_page = $is_admin_downgrade || $is_admin_other;

	$is_demo_page = preg_match( '#^/demo(/demo-[1-4])?/?$#', $request_uri ) === 1;
	$is_demo_personalized = preg_match( '#^/demo/personalized/?$#', $request_uri ) === 1;

	$is_landing_page = preg_match( '#^/app/(audience-builder|interview|message-builder|prospector|value-builder)/?$#', $request_uri ) === 1;

	$is_onboarding_page = preg_match( '#^/app/leaderboards/walkthrough(-confirmation)?/?$#', $request_uri ) === 1;

	// New page patterns
	$is_training_page = preg_match( '#^/training/?$#', $request_uri ) === 1;
	$is_workshop_page = preg_match( '#^/workshop-replay/?$#', $request_uri ) === 1;
	$is_about_page = preg_match( '#^/about/?$#', $request_uri ) === 1;
	$is_contact_page = preg_match( '#^/contact/?$#', $request_uri ) === 1;
	$is_app_home = preg_match( '#^/app/?$#', $request_uri ) === 1;

	$is_new_theme_page = $is_tips_page || $is_admin_page || $is_demo_page ||
	                     $is_demo_personalized || $is_landing_page || $is_onboarding_page ||
	                     $is_training_page || $is_workshop_page || $is_about_page ||
	                     $is_contact_page || $is_app_home;

	// Exit early if not a theme page
	if ( ! $is_new_theme_page ) {
		return;
	}

	// Helper function to enqueue CSS with cache busting
	$enqueue_css = function( $handle, $file, $deps = array() ) use ( $css_dir, $css_url ) {
		$path = $css_dir . $file;
		if ( file_exists( $path ) ) {
			wp_enqueue_style(
				'guestify-new-theme-' . $handle,
				$css_url . $file,
				array_map( function( $d ) { return 'guestify-new-theme-' . $d; }, $deps ),
				filemtime( $path )
			);
		}
	};

	// TIPS PAGES: base + components + layout + tips.css
	if ( $is_tips_page ) {
		$enqueue_css( 'base', 'base.css' );
		$enqueue_css( 'components', 'components.css', array( 'base' ) );
		$enqueue_css( 'layout', 'layout.css', array( 'base' ) );
		$enqueue_css( 'tips', 'tips.css', array( 'base', 'components', 'layout' ) );
	}

	// ADMIN PAGES: base + components + layout + admin.css
	if ( $is_admin_page ) {
		$enqueue_css( 'base', 'base.css' );
		$enqueue_css( 'components', 'components.css', array( 'base' ) );
		$enqueue_css( 'layout', 'layout.css', array( 'base' ) );
		$enqueue_css( 'admin', 'admin.css', array( 'base', 'components', 'layout' ) );
	}

	// DEMO PAGES: demo-core.css only (standalone)
	if ( $is_demo_page && ! $is_demo_personalized ) {
		$enqueue_css( 'demo-core', 'demo-core.css' );
	}

	// LANDING PAGES: base + landing.css
	if ( $is_landing_page ) {
		$enqueue_css( 'base', 'base.css' );
		$enqueue_css( 'landing', 'landing.css', array( 'base' ) );
	}

	// ONBOARDING PAGES: base + onboarding.css
	if ( $is_onboarding_page || $is_demo_personalized ) {
		$enqueue_css( 'base', 'base.css' );
		$enqueue_css( 'onboarding', 'onboarding.css', array( 'base' ) );
	}

	// TRAINING PAGE: training.css (standalone)
	if ( $is_training_page ) {
		$enqueue_css( 'training', 'training.css' );
	}

	// WORKSHOP REPLAY PAGE: workshop-replay.css (standalone)
	if ( $is_workshop_page ) {
		$enqueue_css( 'workshop-replay', 'workshop-replay.css' );
	}

	// ABOUT PAGE: about.css (standalone)
	if ( $is_about_page ) {
		$enqueue_css( 'about', 'about.css' );
	}

	// CONTACT PAGE: contact.css (standalone)
	if ( $is_contact_page ) {
		$enqueue_css( 'contact', 'contact.css' );
	}

	// APP HOME PAGE: app-home.css (standalone)
	if ( $is_app_home ) {
		$enqueue_css( 'app-home', 'app-home.css' );
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
 * Disable wpautop for specific page templates to preserve HTML structure
 * 
 * ROOT CAUSE FIX: Template-based wpautop removal - cleaner than URL matching
 * 
 * Templates that disable wpautop:
 * - template-blank.php (Blank - No Header / No Footer)
 * 
 * Simply assign these templates to pages in WordPress admin, and wpautop
 * is automatically disabled to preserve your structured HTML.
 * 
 * CHECKLIST COMPLIANCE:
 * ✅ Root Cause Fix - Controls content filtering at template level
 * ✅ Simplicity First - One function, self-documenting via template assignment
 * ✅ No Redundant Logic - Template selection controls behavior
 * ✅ Maintainability - Easy to add new templates to the list
 */
function guestify_disable_wpautop_for_templates() {
    // Get the current page template
    $template = get_page_template_slug();
    
    // Templates that should NOT have wpautop
    $no_wpautop_templates = array(
        'template-blank.php',  // Blank template for structured HTML pages
        // Add more templates here as needed
    );
    
    // Check if current template should have wpautop disabled
    if ( in_array( $template, $no_wpautop_templates, true ) ) {
        // Remove wpautop filter from the_content
        remove_filter( 'the_content', 'wpautop' );
        
        // Also remove from excerpt
        remove_filter( 'the_excerpt', 'wpautop' );
        
        // Debug logging (optional)
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '🚫 Guestify Theme: Disabled wpautop for template: ' . $template );
        }
    }
}
// Hook early to run before content is processed
add_action( 'template_redirect', 'guestify_disable_wpautop_for_templates', 1 );

/**
 * TEMPORARY: Debug Media Kit Frontend Display
 * Remove this after debugging is complete
 */
if (file_exists(WP_PLUGIN_DIR . '/mk4/debug-frontend-display.php')) {
    require_once WP_PLUGIN_DIR . '/mk4/debug-frontend-display.php';
}

/**
 * Google Tag Manager Integration
 * Container ID: GTM-T4NDWXK
 */

// GTM Head Script - Site Wide
add_action('wp_head', function() {
    echo '<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({\'gtm.start\':
new Date().getTime(),event:\'gtm.js\'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!=\'dataLayer\'?\'&l=\'+l:\'\';j.async=true;j.src=
\'https://www.googletagmanager.com/gtm.js?id=\'+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,\'script\',\'dataLayer\',\'GTM-T4NDWXK\');</script>
<!-- End Google Tag Manager -->';
}, 1);

// GTM Body Script - Site Wide
add_action('wp_body_open', function() {
    echo '<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-T4NDWXK"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->';
}, 1);

// Fallback for themes that don't support wp_body_open
add_action('wp_footer', function() {
    if (!did_action('wp_body_open')) {
        echo '<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-T4NDWXK"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->';
    }
});

/**
 * Hide admin bar for non-administrators on frontend
 */
add_action( 'after_setup_theme', function() {
	if ( ! current_user_can( 'administrator' ) && ! is_admin() ) {
		show_admin_bar( false );
	}
});