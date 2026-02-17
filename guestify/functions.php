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
 * Enqueue Guestify Design Tokens (Global)
 *
 * This function registers and enqueues the unified design token system.
 * All Guestify plugins should depend on this stylesheet.
 *
 * The tokens are registered with handle 'guestify-tokens' which plugins
 * can check for using wp_style_is('guestify-tokens', 'registered').
 *
 * @since 1.0.0
 */
function guestify_enqueue_design_tokens() {
	$tokens_path = get_template_directory() . '/css/guestify-tokens.css';

	if ( file_exists( $tokens_path ) ) {
		wp_register_style(
			'guestify-tokens',
			get_template_directory_uri() . '/css/guestify-tokens.css',
			array(),
			filemtime( $tokens_path )
		);
		wp_enqueue_style( 'guestify-tokens' );
	}
}
// Priority 1 ensures tokens load before any other styles
add_action( 'wp_enqueue_scripts', 'guestify_enqueue_design_tokens', 1 );
add_action( 'admin_enqueue_scripts', 'guestify_enqueue_design_tokens', 1 );

/**
 * Filter to indicate design tokens are available
 *
 * Plugins can check: apply_filters('guestify_design_tokens_available', false)
 * Returns true if the theme is active and tokens are registered.
 *
 * @since 1.0.0
 */
add_filter( 'guestify_design_tokens_available', '__return_true' );

/**
 * Enqueue scripts and styles.
 */
function guestify_scripts() {
	wp_enqueue_style( 'guestify-style', get_stylesheet_uri(), array( 'guestify-tokens' ), _S_VERSION );
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
		$wp_css_dir = WP_CONTENT_DIR . '/css/';
		$wp_css_url = content_url( '/css/' );
		$theme_css_dir = get_stylesheet_directory() . '/css/';
		$theme_css_url = get_stylesheet_directory_uri() . '/css/';

		// Enqueue base CSS files from wp-content/css/
		$wp_content_styles = array(
			array( 'base', 'base.css', array() ),
			array( 'layout', 'layout.css', array( 'base' ) ),
		);

		foreach ( $wp_content_styles as $style ) {
			list( $handle, $file, $deps ) = $style;
			$path = $wp_css_dir . $file;

			if ( file_exists( $path ) ) {
				wp_enqueue_style(
					'guestify-login-' . $handle,
					$wp_css_url . $file,
					array_map( function( $d ) { return 'guestify-login-' . $d; }, $deps ),
					filemtime( $path )
				);
			}
		}

		// Enqueue components.css from theme directory
		$components_path = $theme_css_dir . 'components.css';
		if ( file_exists( $components_path ) ) {
			wp_enqueue_style(
				'guestify-login-components',
				$theme_css_url . 'components.css',
				array( 'guestify-login-base' ),
				filemtime( $components_path )
			);
		}

		// Enqueue login-specific CSS
		$login_css_path = $theme_css_dir . 'login.css';
		if ( file_exists( $login_css_path ) ) {
			wp_enqueue_style(
				'guestify-login',
				$theme_css_url . 'login.css',
				array( 'guestify-login-base', 'guestify-login-components', 'guestify-login-layout' ),
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
		'redirect' => home_url( '/app/' ),
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
	$output .= '<a href="' . esc_url( home_url( '/reset/' ) ) . '">Lost Password?</a>';

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
 * Password Reset Form Shortcode
 *
 * Displays a password reset form that uses WordPress's built-in reset functionality.
 * Uses existing theme CSS classes from admin.css/components.css.
 *
 * Usage: [guestify_reset_password]
 *
 * @return string HTML output for the password reset form.
 */
function guestify_reset_password_shortcode() {
	// If user is already logged in, show message
	if ( is_user_logged_in() ) {
		$current_user = wp_get_current_user();
		return '<div class="form-message form-message--info">You are already logged in as ' . esc_html( $current_user->display_name ) . '. <a href="' . esc_url( home_url( '/login/' ) ) . '">Go to Login</a></div>';
	}

	$output = '';

	// Handle form submission
	if ( isset( $_POST['guestify_reset_submit'] ) && isset( $_POST['guestify_reset_nonce'] ) ) {
		if ( wp_verify_nonce( $_POST['guestify_reset_nonce'], 'guestify_reset_password' ) ) {
			$user_login = sanitize_text_field( $_POST['user_login'] );

			if ( empty( $user_login ) ) {
				$output .= '<div class="form-message form-message--error">Please enter your username or email address.</div>';
			} else {
				// Check if user exists
				$user = get_user_by( 'email', $user_login );
				if ( ! $user ) {
					$user = get_user_by( 'login', $user_login );
				}

				if ( $user ) {
					// Generate reset key and send email
					$reset_key = get_password_reset_key( $user );

					if ( ! is_wp_error( $reset_key ) ) {
						// Send custom reset email
						$reset_url = home_url( '/set-new-password/?key=' . $reset_key . '&login=' . rawurlencode( $user->user_login ) );

						$subject = 'Password Reset Request - Guestify';
						$message = "Hi " . $user->display_name . ",\n\n";
						$message .= "Someone requested a password reset for your Guestify account.\n\n";
						$message .= "If this was you, click the link below to reset your password:\n\n";
						$message .= $reset_url . "\n\n";
						$message .= "This link will expire in 24 hours.\n\n";
						$message .= "If you didn't request this, you can safely ignore this email.\n\n";
						$message .= "- The Guestify Team";

						$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

						wp_mail( $user->user_email, $subject, $message, $headers );
					}
				}

				// Redirect to confirmation page (security: don't reveal if user exists)
				wp_redirect( home_url( '/password-reset-confirmation/' ) );
				exit;
			}
		} else {
			$output .= '<div class="form-message form-message--error">Security check failed. Please try again.</div>';
		}
	}

	// Display form
	$output .= '<form method="post" class="reset-form">';
	$output .= wp_nonce_field( 'guestify_reset_password', 'guestify_reset_nonce', true, false );

	$output .= '<div class="form-group">';
	$output .= '<label for="user_login" class="form-label">Username or Email Address <span class="required">*</span></label>';
	$output .= '<div class="input-wrapper">';
	$output .= '<span class="input-icon"><i class="fas fa-user" aria-hidden="true"></i></span>';
	$output .= '<input type="text" name="user_login" id="user_login" class="form-input form-input--with-icon" placeholder="Enter your username or email" required autocomplete="username" />';
	$output .= '</div>';
	$output .= '</div>';

	$output .= '<div class="form-group">';
	$output .= '<button type="submit" name="guestify_reset_submit" class="submit-button submit-button--secondary"><i class="fas fa-paper-plane" aria-hidden="true"></i> Get New Password</button>';
	$output .= '</div>';

	$output .= '<div class="form-footer">';
	$output .= '<a href="' . esc_url( home_url( '/login/' ) ) . '" class="back-link"><i class="fas fa-arrow-left" aria-hidden="true"></i> <span>Back to Login</span></a>';
	$output .= '</div>';

	$output .= '</form>';

	return $output;
}
add_shortcode( 'guestify_reset_password', 'guestify_reset_password_shortcode' );

/**
 * Password Reset Confirmation Shortcode
 *
 * Handles the password reset link and allows user to set new password.
 * Outputs full page structure matching the /reset/ page design.
 *
 * Usage: [guestify_reset_confirmation]
 *
 * @return string HTML output for the password reset confirmation form.
 */
function guestify_reset_confirmation_shortcode() {
	$logo_url = 'https://guestify.ai/wp-content/uploads/2024/01/guestify-logo_500px.png';
	$form_content = '';
	$show_form = true;

	// Get key and login from URL
	$reset_key = isset( $_GET['key'] ) ? sanitize_text_field( $_GET['key'] ) : '';
	$user_login = isset( $_GET['login'] ) ? sanitize_text_field( $_GET['login'] ) : '';

	// Handle form submission for new password
	if ( isset( $_POST['guestify_newpass_submit'] ) && isset( $_POST['guestify_newpass_nonce'] ) ) {
		if ( wp_verify_nonce( $_POST['guestify_newpass_nonce'], 'guestify_new_password' ) ) {
			$reset_key = sanitize_text_field( $_POST['reset_key'] );
			$user_login = sanitize_text_field( $_POST['user_login'] );
			$new_password = $_POST['new_password'];
			$confirm_password = $_POST['confirm_password'];

			// Validate passwords
			if ( empty( $new_password ) || empty( $confirm_password ) ) {
				$form_content .= '<div class="form-message form-message--error">Please enter and confirm your new password.</div>';
			} elseif ( $new_password !== $confirm_password ) {
				$form_content .= '<div class="form-message form-message--error">Passwords do not match. Please try again.</div>';
			} elseif ( strlen( $new_password ) < 8 ) {
				$form_content .= '<div class="form-message form-message--error">Password must be at least 8 characters long.</div>';
			} else {
				// Verify the reset key
				$user = check_password_reset_key( $reset_key, $user_login );

				if ( is_wp_error( $user ) ) {
					$form_content .= '<div class="form-message form-message--error">This password reset link is invalid or has expired. <a href="' . esc_url( home_url( '/reset/' ) ) . '">Request a new one</a>.</div>';
					$show_form = false;
				} else {
					// Reset the password
					reset_password( $user, $new_password );

					$form_content .= '<div class="form-message form-message--success">Your password has been reset successfully!</div>';
					$form_content .= '<p class="reset-description"><a href="' . esc_url( home_url( '/login/' ) ) . '">Log in with your new password <i class="fas fa-arrow-right" aria-hidden="true"></i></a></p>';
					$show_form = false;
				}
			}
		} else {
			$form_content .= '<div class="form-message form-message--error">Security check failed. Please try again.</div>';
		}
	}

	// If no key/login, show error
	if ( empty( $reset_key ) || empty( $user_login ) ) {
		$form_content .= '<div class="form-message form-message--error">Invalid password reset link. <a href="' . esc_url( home_url( '/reset/' ) ) . '">Request a new one</a>.</div>';
		$show_form = false;
	} elseif ( $show_form ) {
		// Verify the reset key
		$user = check_password_reset_key( $reset_key, $user_login );

		if ( is_wp_error( $user ) ) {
			$form_content .= '<div class="form-message form-message--error">This password reset link is invalid or has expired. <a href="' . esc_url( home_url( '/reset/' ) ) . '">Request a new one</a>.</div>';
			$show_form = false;
		}
	}

	// Build the form if needed
	if ( $show_form ) {
		$form_content .= '<form method="post" class="reset-form">';
		$form_content .= wp_nonce_field( 'guestify_new_password', 'guestify_newpass_nonce', true, false );
		$form_content .= '<input type="hidden" name="reset_key" value="' . esc_attr( $reset_key ) . '" />';
		$form_content .= '<input type="hidden" name="user_login" value="' . esc_attr( $user_login ) . '" />';

		$form_content .= '<div class="form-group">';
		$form_content .= '<label for="new_password" class="form-label">New Password <span class="required">*</span></label>';
		$form_content .= '<div class="input-wrapper">';
		$form_content .= '<span class="input-icon"><i class="fas fa-lock" aria-hidden="true"></i></span>';
		$form_content .= '<input type="password" name="new_password" id="new_password" class="form-input form-input--with-icon" placeholder="Enter new password" required minlength="8" autocomplete="new-password" />';
		$form_content .= '</div>';
		$form_content .= '</div>';

		$form_content .= '<div class="form-group">';
		$form_content .= '<label for="confirm_password" class="form-label">Confirm Password <span class="required">*</span></label>';
		$form_content .= '<div class="input-wrapper">';
		$form_content .= '<span class="input-icon"><i class="fas fa-lock" aria-hidden="true"></i></span>';
		$form_content .= '<input type="password" name="confirm_password" id="confirm_password" class="form-input form-input--with-icon" placeholder="Confirm new password" required minlength="8" autocomplete="new-password" />';
		$form_content .= '</div>';
		$form_content .= '</div>';

		$form_content .= '<div class="form-group">';
		$form_content .= '<button type="submit" name="guestify_newpass_submit" class="submit-button submit-button--secondary"><i class="fas fa-check" aria-hidden="true"></i> Reset Password</button>';
		$form_content .= '</div>';

		$form_content .= '</form>';
	}

	// Build full page structure
	$output = '<div class="page-container page-container--gradient">';
	$output .= '<main id="main-content" class="reset-container">';

	// Logo
	$output .= '<div class="logo-section">';
	$output .= '<img decoding="async" class="reset-logo" src="' . esc_url( $logo_url ) . '" alt="Guestify Logo">';
	$output .= '</div>';

	// Lock Icon
	$output .= '<div class="icon-wrapper">';
	$output .= '<i class="fas fa-lock" aria-hidden="true"></i>';
	$output .= '</div>';

	// Title
	$output .= '<h1 class="reset-title">Set New Password</h1>';

	// Description
	$output .= '<p class="reset-description">Enter your new password below. Make sure it\'s at least 8 characters long.</p>';

	// Form content (form or error messages)
	$output .= $form_content;

	// Security Notice
	$output .= '<div class="security-notice">';
	$output .= '<p><i class="fas fa-shield-alt" aria-hidden="true"></i> Your new password will be securely saved</p>';
	$output .= '</div>';

	$output .= '</main>';
	$output .= '</div>';

	return $output;
}
add_shortcode( 'guestify_reset_confirmation', 'guestify_reset_confirmation_shortcode' );

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
	$wp_css_dir     = WP_CONTENT_DIR . '/css/';
	$wp_css_url     = content_url( '/css/' );
	$theme_css_dir  = get_stylesheet_directory() . '/css/';
	$theme_css_url  = get_stylesheet_directory_uri() . '/css/';

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
				'#^/(reset|whitelist|password-reset-confirmation|set-new-password)/?$#',
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

		// components.css is in theme directory, others in wp-content/css/
		if ( 'components.css' === $file ) {
			$path = $theme_css_dir . $file;
			$url  = $theme_css_url . $file;
		} else {
			$path = $wp_css_dir . $file;
			$url  = $wp_css_url . $file;
		}

		if ( file_exists( $path ) ) {
			wp_enqueue_style(
				'guestify-new-theme-' . $handle,
				$url,
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
 * Disable admin email verification prompt
 * Prevents WordPress from showing the "Administration email verification" screen
 */
add_filter( 'admin_email_check_interval', '__return_false' );

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
        
        // Admin pages (6 pages)
        '#^/app/downgrade(-confirmation)?/?$#',
        '#^/(reset|whitelist|password-reset-confirmation|set-new-password)/?$#',
        
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

/**
 * Add dynamic Login/Logout menu item to frontend menu
 *
 * Replaces the need for "Login or Logout Menu Item" plugin.
 * Shows "Log In" link when logged out, "Log Out" link when logged in.
 */
function guestify_add_login_logout_menu_item( $items, $args ) {
	// Only add to the frontend menu
	if ( $args->theme_location !== 'frontend' ) {
		return $items;
	}

	if ( is_user_logged_in() ) {
		$items .= '<li class="menu-item menu-item-logout"><a href="' . esc_url( wp_logout_url( home_url( '/login/' ) ) ) . '">' . esc_html__( 'Log Out', 'guestify' ) . '</a></li>';
	} else {
		$items .= '<li class="menu-item menu-item-login"><a href="' . esc_url( home_url( '/login/' ) ) . '">' . esc_html__( 'Log In', 'guestify' ) . '</a></li>';
	}

	return $items;
}
add_filter( 'wp_nav_menu_items', 'guestify_add_login_logout_menu_item', 10, 2 );

/**
 * ============================================
 * GUESTIFY HOME PAGE FUNCTIONS
 * ============================================
 */

/**
 * Check if current page is the app home page
 *
 * @return bool
 */
function is_gfy_home_page() {
	if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
		return false;
	}

	$request_uri = $_SERVER['REQUEST_URI'];
	$url_path = parse_url( $request_uri, PHP_URL_PATH );
	$url_path = rtrim( $url_path, '/' );

	// Match /app or /app/ exactly (not subpages)
	return $url_path === '/app' || $url_path === '';
}

/**
 * Enqueue home page assets
 */
function guestify_enqueue_home_assets() {
	// Only load on app home page or pages using the App Home Dashboard template
	if ( ! is_gfy_home_page() && ! is_page_template( 'page-app-home.php' ) ) {
		return;
	}

	$theme_dir = get_template_directory();
	$theme_url = get_template_directory_uri();

	// Enqueue home page CSS
	$css_path = $theme_dir . '/css/home.css';
	if ( file_exists( $css_path ) ) {
		wp_enqueue_style(
			'guestify-home',
			$theme_url . '/css/home.css',
			array( 'guestify-tokens' ),
			filemtime( $css_path )
		);
	}

	// Enqueue home page JavaScript
	$js_path = $theme_dir . '/js/home.js';
	if ( file_exists( $js_path ) ) {
		wp_enqueue_script(
			'guestify-home',
			$theme_url . '/js/home.js',
			array(),
			filemtime( $js_path ),
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'guestify_enqueue_home_assets', 15 );

/**
 * Get dashboard data for the home page
 *
 * Aggregates data from all Guestify plugins with graceful fallbacks.
 *
 * @param int $user_id The user ID to get data for
 * @return array Dashboard data
 */
function guestify_get_home_dashboard_data( $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( ! $user_id ) {
		return guestify_get_default_dashboard_data();
	}

	// Initialize with defaults
	$data = guestify_get_default_dashboard_data();

	// Get data from Media Kit Builder (mk4)
	$data['pillars']['media_kit'] = guestify_get_media_kit_status( $user_id );

	// Get data from Podcast Prospector
	$data['pillars']['prospector'] = guestify_get_prospector_status( $user_id );

	// Get data from ShowAuthority
	$data['pillars']['showauthority'] = guestify_get_showauthority_status( $user_id );
	$data['stats'] = array_merge( $data['stats'], guestify_get_showauthority_stats( $user_id ) );

	// Get data from Email Outreach
	$data['pillars']['outreach'] = guestify_get_outreach_status( $user_id );
	$data['stats']['pitches'] = guestify_get_outreach_pitches_count( $user_id );

	// Get tasks due count
	$data['tasks_due'] = guestify_get_tasks_due_count( $user_id );

	// Get recent activity
	$data['recent_activity'] = guestify_get_recent_activity( $user_id );

	return $data;
}

/**
 * Get default dashboard data structure
 *
 * @return array
 */
function guestify_get_default_dashboard_data() {
	return array(
		'stats' => array(
			'pitches'    => 0,
			'interviews' => 0,
			'episodes'   => 0,
			'revenue'    => 0,
		),
		'pillars' => array(
			'media_kit' => array(
				'status_text' => 'Get Started',
				'is_alert'    => false,
				'show_dot'    => false,
				'icon'        => '',
			),
			'prospector' => array(
				'status_text' => '0 Saved Shows',
				'is_alert'    => false,
				'show_dot'    => false,
				'icon'        => '',
			),
			'showauthority' => array(
				'status_text' => '0 Ready to Pitch',
				'is_alert'    => false,
				'show_dot'    => false,
				'icon'        => '',
			),
			'outreach' => array(
				'status_text' => 'No Messages',
				'is_alert'    => false,
				'show_dot'    => false,
				'icon'        => '',
			),
		),
		'tasks_due' => 0,
		'recent_activity' => array(),
	);
}

/**
 * Get Media Kit status for home page
 *
 * @param int $user_id
 * @return array
 */
function guestify_get_media_kit_status( $user_id ) {
	$status = array(
		'status_text' => 'Get Started',
		'is_alert'    => false,
		'show_dot'    => false,
		'icon'        => '',
	);

	// Check if mk4 plugin is active and class exists
	if ( class_exists( 'GMKB_Onboarding_Repository' ) ) {
		try {
			$repo = new GMKB_Onboarding_Repository();

			// Check if the method exists before calling it
			if ( method_exists( $repo, 'get_user_progress' ) ) {
				$progress = $repo->get_user_progress( $user_id );

				if ( isset( $progress['completion_percent'] ) ) {
					$percent = intval( $progress['completion_percent'] );
					$status['status_text'] = 'Profile Ready (' . $percent . '%)';
					$status['show_dot'] = $percent >= 80;
				}
			} else {
				// Method doesn't exist, fallback to user meta
				$cached_progress = get_user_meta( $user_id, 'guestify_onboarding_progress_percent', true );
				if ( $cached_progress ) {
					$status['status_text'] = 'Profile Ready (' . intval( $cached_progress ) . '%)';
					$status['show_dot'] = intval( $cached_progress ) >= 80;
				}
			}
		} catch ( Exception $e ) {
			// Fallback to user meta
			$cached_progress = get_user_meta( $user_id, 'guestify_onboarding_progress_percent', true );
			if ( $cached_progress ) {
				$status['status_text'] = 'Profile Ready (' . intval( $cached_progress ) . '%)';
				$status['show_dot'] = intval( $cached_progress ) >= 80;
			}
		}
	} else {
		// Check for cached progress in user meta
		$cached_progress = get_user_meta( $user_id, 'guestify_onboarding_progress_percent', true );
		if ( $cached_progress ) {
			$status['status_text'] = 'Profile Ready (' . intval( $cached_progress ) . '%)';
			$status['show_dot'] = intval( $cached_progress ) >= 80;
		}
	}

	return $status;
}

/**
 * Get Prospector status for home page
 *
 * @param int $user_id
 * @return array
 */
function guestify_get_prospector_status( $user_id ) {
	$status = array(
		'status_text' => '0 Saved Shows',
		'is_alert'    => false,
		'show_dot'    => false,
		'icon'        => '',
	);

	global $wpdb;

	// Check if prospector tables exist
	$table_name = $wpdb->prefix . 'pit_podcasts';
	$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name;

	if ( $table_exists ) {
		// Count podcasts saved by user
		$count = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}pit_podcasts WHERE user_id = %d",
			$user_id
		) );

		if ( $count !== null ) {
			$count = intval( $count );
			$status['status_text'] = $count . ' Saved Show' . ( $count !== 1 ? 's' : '' );
		}
	}

	return $status;
}

/**
 * Get ShowAuthority status for home page
 *
 * @param int $user_id
 * @return array
 */
function guestify_get_showauthority_status( $user_id ) {
	$status = array(
		'status_text' => '0 Ready to Pitch',
		'is_alert'    => false,
		'show_dot'    => false,
		'icon'        => '',
	);

	global $wpdb;

	// Check if ShowAuthority opportunities table exists
	$table_name = $wpdb->prefix . 'pit_opportunities';
	$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name;

	if ( $table_exists ) {
		// Count opportunities in "ready to pitch" stage
		$count = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}pit_opportunities WHERE user_id = %d AND status = 'ready'",
			$user_id
		) );

		if ( $count !== null ) {
			$count = intval( $count );
			$status['status_text'] = $count . ' Ready to Pitch';
		}
	}

	return $status;
}

/**
 * Get ShowAuthority stats for home page
 *
 * @param int $user_id
 * @return array
 */
function guestify_get_showauthority_stats( $user_id ) {
	$stats = array(
		'interviews' => 0,
		'episodes'   => 0,
		'revenue'    => 0,
	);

	global $wpdb;

	// Check if appearances table exists
	$table_name = $wpdb->prefix . 'pit_appearances';
	$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name;

	if ( $table_exists ) {
		// Count booked interviews
		$interviews = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}pit_appearances WHERE user_id = %d AND status IN ('scheduled', 'confirmed')",
			$user_id
		) );
		$stats['interviews'] = intval( $interviews );

		// Count aired episodes
		$episodes = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}pit_appearances WHERE user_id = %d AND status = 'aired'",
			$user_id
		) );
		$stats['episodes'] = intval( $episodes );

		// Sum revenue (if tracked)
		$revenue = $wpdb->get_var( $wpdb->prepare(
			"SELECT COALESCE(SUM(revenue), 0) FROM {$wpdb->prefix}pit_appearances WHERE user_id = %d",
			$user_id
		) );
		$stats['revenue'] = floatval( $revenue );
	}

	return $stats;
}

/**
 * Get Outreach status for home page
 *
 * @param int $user_id
 * @return array
 */
function guestify_get_outreach_status( $user_id ) {
	$status = array(
		'status_text' => 'No Messages',
		'is_alert'    => false,
		'show_dot'    => false,
		'icon'        => '',
	);

	global $wpdb;

	// Check if messages table exists
	$table_name = $wpdb->prefix . 'guestify_messages';
	$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name;

	if ( $table_exists ) {
		// Count unread replies
		$unread = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}guestify_messages WHERE user_id = %d AND is_reply = 1 AND is_read = 0",
			$user_id
		) );

		if ( $unread !== null && intval( $unread ) > 0 ) {
			$count = intval( $unread );
			$status['status_text'] = $count . ' Unread Repl' . ( $count !== 1 ? 'ies' : 'y' );
			$status['is_alert'] = true;
			$status['icon'] = 'fa-solid fa-envelope';
		}
	}

	return $status;
}

/**
 * Get outreach pitches count for current month
 *
 * @param int $user_id
 * @return int
 */
function guestify_get_outreach_pitches_count( $user_id ) {
	global $wpdb;

	$table_name = $wpdb->prefix . 'guestify_messages';
	$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name;

	if ( ! $table_exists ) {
		return 0;
	}

	$first_of_month = date( 'Y-m-01 00:00:00' );

	$count = $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(*) FROM {$wpdb->prefix}guestify_messages WHERE user_id = %d AND created_at >= %s AND is_reply = 0",
		$user_id,
		$first_of_month
	) );

	return intval( $count );
}

/**
 * Get tasks due count
 *
 * @param int $user_id
 * @return int
 */
function guestify_get_tasks_due_count( $user_id ) {
	global $wpdb;

	// Check ShowAuthority tasks
	$table_name = $wpdb->prefix . 'pit_appearance_tasks';
	$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name;

	if ( ! $table_exists ) {
		return 0;
	}

	$today = date( 'Y-m-d' );

	$count = $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(*) FROM {$wpdb->prefix}pit_appearance_tasks WHERE user_id = %d AND due_date <= %s AND status != 'completed'",
		$user_id,
		$today
	) );

	return intval( $count );
}

/**
 * Get recent activity for home page
 *
 * @param int $user_id
 * @return array
 */
function guestify_get_recent_activity( $user_id ) {
	$activities = array();

	global $wpdb;

	// Get recent outreach messages
	$messages_table = $wpdb->prefix . 'guestify_messages';
	if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $messages_table ) ) === $messages_table ) {
		$messages = $wpdb->get_results( $wpdb->prepare(
			"SELECT id, subject, created_at, is_reply FROM {$messages_table} WHERE user_id = %d ORDER BY created_at DESC LIMIT 3",
			$user_id
		) );

		foreach ( $messages as $msg ) {
			$activities[] = array(
				'id'       => 'msg_' . $msg->id,
				'type'     => $msg->is_reply ? 'message' : 'draft',
				'icon'     => $msg->is_reply ? 'fa-solid fa-envelope-open' : 'fa-solid fa-pen-to-square',
				'title'    => $msg->is_reply ? 'Reply: ' . $msg->subject : 'Draft: ' . $msg->subject,
				'subtitle' => guestify_format_relative_time( $msg->created_at ),
				'url'      => home_url( '/app/outreach/?message=' . $msg->id ),
				'timestamp' => strtotime( $msg->created_at ),
			);
		}
	}

	// Get recent interviews/appearances
	$appearances_table = $wpdb->prefix . 'pit_appearances';
	if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $appearances_table ) ) === $appearances_table ) {
		$appearances = $wpdb->get_results( $wpdb->prepare(
			"SELECT a.id, a.podcast_id, a.scheduled_date, a.status, p.title as podcast_title
			 FROM {$appearances_table} a
			 LEFT JOIN {$wpdb->prefix}pit_podcasts p ON a.podcast_id = p.id
			 WHERE a.user_id = %d
			 ORDER BY a.scheduled_date DESC LIMIT 3",
			$user_id
		) );

		foreach ( $appearances as $app ) {
			$activities[] = array(
				'id'       => 'app_' . $app->id,
				'type'     => 'calendar',
				'icon'     => 'fa-solid fa-calendar-check',
				'title'    => 'Interview: ' . ( $app->podcast_title ?: 'Podcast' ),
				'subtitle' => date( 'M j', strtotime( $app->scheduled_date ) ) . ' - ' . ucfirst( $app->status ),
				'url'      => home_url( '/app/interviews/?id=' . $app->id ),
				'timestamp' => strtotime( $app->scheduled_date ),
			);
		}
	}

	// Get recent prospector searches
	$searches_table = $wpdb->prefix . 'podcast_prospector_searches';
	if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $searches_table ) ) === $searches_table ) {
		$searches = $wpdb->get_results( $wpdb->prepare(
			"SELECT id, search_term, result_count, created_at FROM {$searches_table} WHERE user_id = %d ORDER BY created_at DESC LIMIT 2",
			$user_id
		) );

		foreach ( $searches as $search ) {
			$activities[] = array(
				'id'       => 'search_' . $search->id,
				'type'     => 'search',
				'icon'     => 'fa-solid fa-magnifying-glass',
				'title'    => 'Search: "' . $search->search_term . '"',
				'subtitle' => guestify_format_relative_time( $search->created_at ) . ' - ' . $search->result_count . ' results',
				'url'      => home_url( '/app/prospector/?q=' . urlencode( $search->search_term ) ),
				'timestamp' => strtotime( $search->created_at ),
			);
		}
	}

	// Sort by timestamp (most recent first) and limit to 5
	usort( $activities, function( $a, $b ) {
		return ( $b['timestamp'] ?? 0 ) - ( $a['timestamp'] ?? 0 );
	} );

	return array_slice( $activities, 0, 5 );
}

/**
 * Format relative time string
 *
 * @param string $datetime
 * @return string
 */
function guestify_format_relative_time( $datetime ) {
	$now = time();
	$diff = $now - strtotime( $datetime );

	if ( $diff < 60 ) {
		return 'Just now';
	} elseif ( $diff < 3600 ) {
		$mins = floor( $diff / 60 );
		return $mins . ' min' . ( $mins > 1 ? 's' : '' ) . ' ago';
	} elseif ( $diff < 86400 ) {
		$hours = floor( $diff / 3600 );
		return $hours . ' hour' . ( $hours > 1 ? 's' : '' ) . ' ago';
	} elseif ( $diff < 172800 ) {
		return 'Yesterday';
	} else {
		$days = floor( $diff / 86400 );
		return $days . ' day' . ( $days > 1 ? 's' : '' ) . ' ago';
	}
}

/**
 * Load Home Dashboard API
 */
require_once get_template_directory() . '/inc/class-home-dashboard-api.php';

/**
 * Clear home dashboard cache when relevant data changes
 *
 * @param int $user_id The user ID whose cache should be cleared
 */
function guestify_clear_home_cache( $user_id = 0 ) {
    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }

    if ( $user_id ) {
        delete_transient( 'gfy_home_dashboard_' . $user_id );
        delete_transient( 'gfy_home_stats_' . $user_id );
    }
}

// Hook into relevant actions to clear cache
add_action( 'save_post', function( $post_id ) {
    $post = get_post( $post_id );
    if ( $post && $post->post_author ) {
        guestify_clear_home_cache( $post->post_author );
    }
} );

add_action( 'updated_user_meta', function( $meta_id, $user_id, $meta_key ) {
    if ( strpos( $meta_key, 'guestify' ) !== false || strpos( $meta_key, 'gmkb' ) !== false ) {
        guestify_clear_home_cache( $user_id );
    }
}, 10, 3 );

add_action( 'guestify_outreach_message_sent', 'guestify_clear_home_cache' );
add_action( 'pit_appearance_updated', 'guestify_clear_home_cache' );
add_action( 'prospector_search_completed', 'guestify_clear_home_cache' );

/**
 * ============================================
 * GUESTIFY ACCOUNT PAGE FUNCTIONS
 * ============================================
 */

/**
 * Check if current page is the account page
 *
 * @return bool
 */
function is_gfy_account_page() {
	if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
		return false;
	}

	$request_uri = $_SERVER['REQUEST_URI'];
	$url_path = parse_url( $request_uri, PHP_URL_PATH );
	$url_path = rtrim( $url_path, '/' );

	return $url_path === '/account' || strpos( $url_path, '/account/' ) === 0;
}

/**
 * Enqueue account page assets
 */
function guestify_enqueue_account_assets() {
	if ( ! is_gfy_account_page() && ! is_page_template( 'page-account.php' ) ) {
		return;
	}

	$theme_dir = get_template_directory();
	$theme_url = get_template_directory_uri();

	// Enqueue account page CSS
	$css_path = $theme_dir . '/css/account.css';
	if ( file_exists( $css_path ) ) {
		wp_enqueue_style(
			'guestify-account',
			$theme_url . '/css/account.css',
			array( 'guestify-tokens' ),
			filemtime( $css_path )
		);
	}

	// Enqueue account page JavaScript
	$js_path = $theme_dir . '/js/account.js';
	if ( file_exists( $js_path ) ) {
		wp_enqueue_script(
			'guestify-account',
			$theme_url . '/js/account.js',
			array(),
			filemtime( $js_path ),
			true
		);

		// Pass data to JavaScript
		wp_localize_script( 'guestify-account', 'gfyAccountData', array(
			'nonce'         => wp_create_nonce( 'wp_rest' ),
			'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
			'apiBase'       => rest_url( 'guestify/v1/account' ),
			'creditApiBase' => rest_url( 'guestify/v1/' ),
		) );
	}
}
add_action( 'wp_enqueue_scripts', 'guestify_enqueue_account_assets', 15 );

/**
 * Get user data for the account page
 *
 * @param int $user_id User ID
 * @return array User data
 */
function guestify_get_account_user_data( $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( ! $user_id ) {
		return array();
	}

	$user = get_userdata( $user_id );
	if ( ! $user ) {
		return array();
	}

	$first_name = $user->first_name ?: '';
	$last_name = $user->last_name ?: '';
	$initials = '';
	if ( $first_name ) {
		$initials .= strtoupper( substr( $first_name, 0, 1 ) );
	}
	if ( $last_name ) {
		$initials .= strtoupper( substr( $last_name, 0, 1 ) );
	}
	if ( empty( $initials ) ) {
		$initials = strtoupper( substr( $user->display_name, 0, 2 ) );
	}

	return array(
		'id'           => $user_id,
		'first_name'   => $first_name,
		'last_name'    => $last_name,
		'display_name' => $user->display_name,
		'email'        => $user->user_email,
		'job_title'    => get_user_meta( $user_id, 'job_title', true ),
		'avatar_url'   => get_avatar_url( $user_id, array( 'size' => 160 ) ),
		'initials'     => $initials,
	);
}

/**
 * Get billing data for the account page
 *
 * Integrates with PMPro if available.
 *
 * @param int $user_id User ID
 * @return array Billing data
 */
function guestify_get_account_billing_data( $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	$billing_data = array(
		'membership_name'   => 'Free Plan',
		'subscription_date' => '',
		'renewal_date'      => '',
		'search_cap'        => 10, // Free tier default
		'payment_method'    => null,
		'invoices'          => array(),
	);

	// Check PMPro integration
	if ( function_exists( 'pmpro_getMembershipLevelForUser' ) ) {
		$level = pmpro_getMembershipLevelForUser( $user_id );

		if ( $level ) {
			$billing_data['membership_name'] = $level->name;

			// Get subscription date
			if ( ! empty( $level->startdate ) ) {
				$billing_data['subscription_date'] = date_i18n( get_option( 'date_format' ), strtotime( $level->startdate ) );
			}

			// Get renewal date
			if ( ! empty( $level->enddate ) && $level->enddate !== '0000-00-00 00:00:00' ) {
				$billing_data['renewal_date'] = date_i18n( get_option( 'date_format' ), strtotime( $level->enddate ) );
			}

			// Set search cap based on membership level
			$level_caps = array(
				1 => 50,   // Starter
				2 => 200,  // Growth
				3 => 500,  // Pro
				4 => 1000, // Agency
			);
			$billing_data['search_cap'] = isset( $level_caps[ $level->id ] ) ? $level_caps[ $level->id ] : 50;
		}

		// Get payment method from Stripe if available
		if ( function_exists( 'pmpro_get_customer_for_user' ) ) {
			$customer_id = get_user_meta( $user_id, 'pmpro_stripe_customerid', true );
			if ( $customer_id ) {
				$billing_data['payment_method'] = array(
					'brand' => 'Visa', // Would fetch from Stripe API
					'last4' => '4242', // Would fetch from Stripe API
				);
			}
		}

		// Get invoices from PMPro orders
		if ( function_exists( 'pmpro_getMemberOrdersByUser' ) ) {
			$orders = pmpro_getMemberOrdersByUser( $user_id, 'success', null, 10 );
			if ( $orders ) {
				foreach ( $orders as $order ) {
					$billing_data['invoices'][] = array(
						'id'      => $order->id,
						'date'    => date_i18n( get_option( 'date_format' ), strtotime( $order->timestamp ) ),
						'amount'  => pmpro_formatPrice( $order->total ),
						'status'  => 'paid',
						'pdf_url' => home_url( '/membership-invoice/?invoice=' . $order->code ),
					);
				}
			}
		}
	}

	return $billing_data;
}

/**
 * Get usage data for the account page
 *
 * Aggregates data from all Guestify plugins.
 *
 * @param int $user_id User ID
 * @return array Usage data
 */
function guestify_get_account_usage_data( $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	global $wpdb;

	// Get billing data for caps
	$billing_data = guestify_get_account_billing_data( $user_id );

	$usage_data = array(
		'ai_credits' => array(
			'used'  => 0,
			'total' => 500, // Default AI credit cap
		),
		'prospector' => array(
			'used'  => 0,
			'total' => $billing_data['search_cap'],
		),
		'outreach' => array(
			'used'  => 0,
			'total' => 300, // Default outreach cap
		),
		'activity' => array(
			'ai_generations'   => 0,
			'podcast_searches' => 0,
			'emails_sent'      => 0,
		),
		'resets_date' => date_i18n( 'M j', strtotime( 'last day of this month' ) ),
	);

	$first_of_month = date( 'Y-m-01 00:00:00' );

	// Get AI credits usage from ShowAuthority
	$ai_usage = get_user_meta( $user_id, 'guestify_ai_credits_used', true );
	if ( $ai_usage ) {
		$usage_data['ai_credits']['used'] = intval( $ai_usage );
	}

	// Get AI generations this month
	$ai_table = $wpdb->prefix . 'pit_ai_generations';
	if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $ai_table ) ) === $ai_table ) {
		$ai_count = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$ai_table} WHERE user_id = %d AND created_at >= %s",
			$user_id,
			$first_of_month
		) );
		$usage_data['activity']['ai_generations'] = intval( $ai_count );
		$usage_data['ai_credits']['used'] = intval( $ai_count );
	}

	// Get prospector searches from the searches table
	$searches_table = $wpdb->prefix . 'podcast_prospector_searches';
	if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $searches_table ) ) === $searches_table ) {
		$searches_count = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$searches_table} WHERE user_id = %d AND created_at >= %s",
			$user_id,
			$first_of_month
		) );
		$usage_data['prospector']['used'] = intval( $searches_count );
		$usage_data['activity']['podcast_searches'] = intval( $searches_count );
	}

	// Get outreach sends from email outreach plugin
	$messages_table = $wpdb->prefix . 'guestify_messages';
	if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $messages_table ) ) === $messages_table ) {
		$emails_count = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$messages_table} WHERE user_id = %d AND created_at >= %s AND is_reply = 0",
			$user_id,
			$first_of_month
		) );
		$usage_data['outreach']['used'] = intval( $emails_count );
		$usage_data['activity']['emails_sent'] = intval( $emails_count );
	}

	// Calculate credit caps based on membership level
	if ( function_exists( 'pmpro_getMembershipLevelForUser' ) ) {
		$level = pmpro_getMembershipLevelForUser( $user_id );
		if ( $level ) {
			// Adjust caps based on level
			$ai_caps = array( 1 => 500, 2 => 1500, 3 => 5000, 4 => 15000 );
			$outreach_caps = array( 1 => 300, 2 => 1000, 3 => 5000, 4 => 15000 );

			$usage_data['ai_credits']['total'] = isset( $ai_caps[ $level->id ] ) ? $ai_caps[ $level->id ] : 500;
			$usage_data['outreach']['total'] = isset( $outreach_caps[ $level->id ] ) ? $outreach_caps[ $level->id ] : 300;
		}
	}

	return $usage_data;
}

/**
 * Get team members for a user
 *
 * @param int $user_id User ID
 * @return array Team members
 */
function guestify_get_team_members( $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	// Placeholder - would integrate with team functionality
	$team_members = get_user_meta( $user_id, 'guestify_team_members', true );

	if ( ! is_array( $team_members ) ) {
		return array();
	}

	$members = array();
	foreach ( $team_members as $member_id ) {
		$member = get_userdata( $member_id );
		if ( $member ) {
			$members[] = array(
				'id'         => $member_id,
				'name'       => $member->display_name,
				'email'      => $member->user_email,
				'avatar_url' => get_avatar_url( $member_id, array( 'size' => 80 ) ),
				'role'       => $member_id === $user_id ? 'Owner' : 'Member',
				'initials'   => strtoupper( substr( $member->first_name, 0, 1 ) . substr( $member->last_name, 0, 1 ) ),
			);
		}
	}

	return $members;
}

/**
 * Get pending team invitations
 *
 * @param int $user_id User ID
 * @return array Pending invitations
 */
function guestify_get_pending_invitations( $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	// Placeholder - would integrate with invitation system
	$invitations = get_user_meta( $user_id, 'guestify_pending_invitations', true );

	if ( ! is_array( $invitations ) ) {
		return array();
	}

	return $invitations;
}

/**
 * Load Account API
 */
require_once get_template_directory() . '/inc/class-account-api.php';

/**
 * ============================================
 * GUESTIFY DASHBOARD PAGE FUNCTIONS
 * ============================================
 */

/**
 * Check if current page is the dashboard page
 *
 * @return bool
 */
function is_gfy_dashboard_page() {
	if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
		return false;
	}

	$request_uri = $_SERVER['REQUEST_URI'];
	$url_path = parse_url( $request_uri, PHP_URL_PATH );
	$url_path = rtrim( $url_path, '/' );

	return $url_path === '/app/dashboard' || $url_path === '/app/reports';
}

/**
 * Enqueue dashboard page assets
 */
function guestify_enqueue_dashboard_assets() {
	if ( ! is_gfy_dashboard_page() && ! is_page_template( 'page-dashboard.php' ) ) {
		return;
	}

	$theme_dir = get_template_directory();
	$theme_url = get_template_directory_uri();

	// Enqueue dashboard page CSS
	$css_path = $theme_dir . '/css/dashboard.css';
	if ( file_exists( $css_path ) ) {
		wp_enqueue_style(
			'guestify-dashboard',
			$theme_url . '/css/dashboard.css',
			array( 'guestify-tokens' ),
			filemtime( $css_path )
		);
	}

	// Enqueue dashboard page JavaScript
	$js_path = $theme_dir . '/js/dashboard.js';
	if ( file_exists( $js_path ) ) {
		wp_enqueue_script(
			'guestify-dashboard',
			$theme_url . '/js/dashboard.js',
			array(),
			filemtime( $js_path ),
			true
		);

		// Pass data to JavaScript
		wp_localize_script( 'guestify-dashboard', 'gfyDashboardData', array(
			'nonce'   => wp_create_nonce( 'wp_rest' ),
			'apiBase' => rest_url( 'guestify/v1/dashboard' ),
		) );
	}
}
add_action( 'wp_enqueue_scripts', 'guestify_enqueue_dashboard_assets', 15 );

/**
 * Get date range for time period
 *
 * @param string $period Time period (30days, 90days, ytd, all)
 * @return array Array with 'start' and 'end' dates
 */
function guestify_get_date_range( $period ) {
	$end = date( 'Y-m-d 23:59:59' );

	switch ( $period ) {
		case '90days':
			$start = date( 'Y-m-d 00:00:00', strtotime( '-90 days' ) );
			break;
		case 'ytd':
			$start = date( 'Y-01-01 00:00:00' );
			break;
		case 'all':
			$start = '2020-01-01 00:00:00';
			break;
		case '30days':
		default:
			$start = date( 'Y-m-d 00:00:00', strtotime( '-30 days' ) );
			break;
	}

	return array(
		'start' => $start,
		'end'   => $end,
	);
}

/**
 * Get pipeline data for the dashboard
 *
 * Aggregates data from Prospector, ShowAuthority, and Outreach plugins.
 *
 * @param int    $user_id User ID
 * @param string $period  Time period
 * @return array Pipeline data
 */
function guestify_get_pipeline_data( $user_id = 0, $period = '30days' ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	global $wpdb;

	$dates = guestify_get_date_range( $period );
	$start = $dates['start'];
	$end = $dates['end'];

	$data = array(
		'shows_found'        => 0,
		'shows_researched'   => 0,
		'pitches_sent'       => 0,
		'interviews_booked'  => 0,
		'episodes_aired'     => 0,
		'vetted_rate'        => 0,
		'pitched_rate'       => 0,
		'booked_rate'        => 0,
		'aired_rate'         => 0,
		'ready_to_pitch'     => 0,
		'insight'            => '',
	);

	// Get shows found from Prospector
	$podcasts_table = $wpdb->prefix . 'pit_podcasts';
	if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $podcasts_table ) ) === $podcasts_table ) {
		$data['shows_found'] = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$podcasts_table} WHERE user_id = %d AND created_at >= %s AND created_at <= %s",
			$user_id,
			$start,
			$end
		) );
	}

	// Get shows researched (vetted) from ShowAuthority opportunities
	$opportunities_table = $wpdb->prefix . 'pit_opportunities';
	if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $opportunities_table ) ) === $opportunities_table ) {
		$data['shows_researched'] = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$opportunities_table} WHERE user_id = %d AND created_at >= %s AND created_at <= %s",
			$user_id,
			$start,
			$end
		) );

		// Get ready to pitch count
		$data['ready_to_pitch'] = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$opportunities_table} WHERE user_id = %d AND status = 'ready'",
			$user_id
		) );
	}

	// Get pitches sent from Outreach
	$messages_table = $wpdb->prefix . 'guestify_messages';
	if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $messages_table ) ) === $messages_table ) {
		$data['pitches_sent'] = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$messages_table} WHERE user_id = %d AND is_reply = 0 AND created_at >= %s AND created_at <= %s",
			$user_id,
			$start,
			$end
		) );
	}

	// Get interviews booked and episodes aired from ShowAuthority appearances
	$appearances_table = $wpdb->prefix . 'pit_appearances';
	if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $appearances_table ) ) === $appearances_table ) {
		$data['interviews_booked'] = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$appearances_table} WHERE user_id = %d AND status IN ('scheduled', 'confirmed', 'completed', 'aired') AND created_at >= %s AND created_at <= %s",
			$user_id,
			$start,
			$end
		) );

		$data['episodes_aired'] = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$appearances_table} WHERE user_id = %d AND status = 'aired' AND created_at >= %s AND created_at <= %s",
			$user_id,
			$start,
			$end
		) );
	}

	// Calculate conversion rates
	if ( $data['shows_found'] > 0 ) {
		$data['vetted_rate'] = round( ( $data['shows_researched'] / $data['shows_found'] ) * 100, 1 );
	}
	if ( $data['shows_researched'] > 0 ) {
		$data['pitched_rate'] = round( ( $data['pitches_sent'] / $data['shows_researched'] ) * 100, 1 );
	}
	if ( $data['pitches_sent'] > 0 ) {
		$data['booked_rate'] = round( ( $data['interviews_booked'] / $data['pitches_sent'] ) * 100, 1 );
	}
	if ( $data['interviews_booked'] > 0 ) {
		$data['aired_rate'] = round( ( $data['episodes_aired'] / $data['interviews_booked'] ) * 100, 1 );
	}

	// Generate insight
	$data['insight'] = guestify_generate_pipeline_insight( $data );

	return $data;
}

/**
 * Generate insight message based on pipeline data
 *
 * @param array $data Pipeline data
 * @return string Insight HTML
 */
function guestify_generate_pipeline_insight( $data ) {
	$pitches = $data['pitches_sent'];
	$bookings = $data['interviews_booked'];

	if ( $pitches >= 20 ) {
		$multiplier = $bookings > 0 ? round( $pitches / $bookings, 1 ) : 0;
		return sprintf(
			__( '<strong>Effort-to-Results Insight:</strong> Users who pitch 20+ shows per month see <strong>3x more bookings</strong>. You\'re at %d pitches this month.', 'guestify' ),
			$pitches
		);
	} elseif ( $pitches > 0 ) {
		$needed = 20 - $pitches;
		return sprintf(
			__( '<strong>Tip:</strong> Send %d more pitches to reach the 20+ threshold for 3x booking rates.', 'guestify' ),
			$needed
		);
	} else {
		return __( '<strong>Get Started:</strong> Begin by searching for podcasts and sending your first pitch.', 'guestify' );
	}
}

/**
 * Get outcomes data for the dashboard
 *
 * @param int    $user_id User ID
 * @param string $period  Time period
 * @return array Outcomes data
 */
function guestify_get_outcomes_data( $user_id = 0, $period = '30days' ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	global $wpdb;

	$dates = guestify_get_date_range( $period );
	$start = $dates['start'];
	$end = $dates['end'];

	$data = array(
		'revenue'        => 0,
		'revenue_change' => '',
		'audience'       => 0,
		'partners'       => 0,
	);

	// Get revenue from ShowAuthority appearances
	$appearances_table = $wpdb->prefix . 'pit_appearances';
	if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $appearances_table ) ) === $appearances_table ) {
		// Current period revenue
		$data['revenue'] = (float) $wpdb->get_var( $wpdb->prepare(
			"SELECT COALESCE(SUM(revenue), 0) FROM {$appearances_table} WHERE user_id = %d AND created_at >= %s AND created_at <= %s",
			$user_id,
			$start,
			$end
		) );

		// Previous period revenue for comparison
		$prev_dates = guestify_get_date_range( $period );
		$period_length = strtotime( $end ) - strtotime( $start );
		$prev_start = date( 'Y-m-d H:i:s', strtotime( $start ) - $period_length );
		$prev_end = $start;

		$prev_revenue = (float) $wpdb->get_var( $wpdb->prepare(
			"SELECT COALESCE(SUM(revenue), 0) FROM {$appearances_table} WHERE user_id = %d AND created_at >= %s AND created_at < %s",
			$user_id,
			$prev_start,
			$prev_end
		) );

		if ( $prev_revenue > 0 ) {
			$change = ( ( $data['revenue'] - $prev_revenue ) / $prev_revenue ) * 100;
			$data['revenue_change'] = ( $change >= 0 ? '+' : '' ) . round( $change ) . '% vs last period';
		}

		// Get total audience reach
		$data['audience'] = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COALESCE(SUM(audience_size), 0) FROM {$appearances_table} WHERE user_id = %d AND status = 'aired'",
			$user_id
		) );

		// Get active partners (unique podcasts with collaboration)
		$data['partners'] = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(DISTINCT podcast_id) FROM {$appearances_table} WHERE user_id = %d AND status IN ('scheduled', 'confirmed', 'completed', 'aired')",
			$user_id
		) );
	}

	return $data;
}

/**
 * Get revenue attribution data for the dashboard
 *
 * @param int    $user_id User ID
 * @param string $period  Time period
 * @return array Attribution data
 */
function guestify_get_attribution_data( $user_id = 0, $period = '30days' ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	global $wpdb;

	$dates = guestify_get_date_range( $period );
	$start = $dates['start'];
	$end = $dates['end'];

	$data = array();

	// Get attribution data from ShowAuthority links table
	$links_table = $wpdb->prefix . 'pit_tracking_links';
	$appearances_table = $wpdb->prefix . 'pit_appearances';
	$podcasts_table = $wpdb->prefix . 'pit_podcasts';

	if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $links_table ) ) === $links_table ) {
		$results = $wpdb->get_results( $wpdb->prepare(
			"SELECT
				l.id,
				l.slug,
				l.clicks,
				l.conversions as leads,
				COALESCE(a.revenue, 0) as revenue,
				COALESCE(p.title, 'Unknown Podcast') as name
			 FROM {$links_table} l
			 LEFT JOIN {$appearances_table} a ON l.appearance_id = a.id
			 LEFT JOIN {$podcasts_table} p ON a.podcast_id = p.id
			 WHERE l.user_id = %d
			 ORDER BY l.clicks DESC
			 LIMIT 5",
			$user_id
		), ARRAY_A );

		if ( $results ) {
			foreach ( $results as $row ) {
				$data[] = array(
					'name'    => $row['name'],
					'link'    => '/go/' . $row['slug'],
					'clicks'  => (int) $row['clicks'],
					'leads'   => (int) $row['leads'],
					'revenue' => (float) $row['revenue'],
				);
			}
		}
	}

	// If no link tracking data, get from appearances directly
	if ( empty( $data ) && $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $appearances_table ) ) === $appearances_table ) {
		$results = $wpdb->get_results( $wpdb->prepare(
			"SELECT
				a.id,
				COALESCE(p.title, 'Podcast Appearance') as name,
				COALESCE(a.revenue, 0) as revenue
			 FROM {$appearances_table} a
			 LEFT JOIN {$podcasts_table} p ON a.podcast_id = p.id
			 WHERE a.user_id = %d AND a.revenue > 0
			 ORDER BY a.revenue DESC
			 LIMIT 5",
			$user_id
		), ARRAY_A );

		if ( $results ) {
			foreach ( $results as $row ) {
				$data[] = array(
					'name'    => $row['name'],
					'link'    => '',
					'clicks'  => 0,
					'leads'   => 0,
					'revenue' => (float) $row['revenue'],
				);
			}
		}
	}

	return $data;
}

/**
 * Get user journey stage
 *
 * @param int $user_id User ID
 * @return array Journey stage data
 */
function guestify_get_user_journey_stage( $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	// Get pipeline data to determine stage
	$pipeline = guestify_get_pipeline_data( $user_id, 'all' );

	$stages = array(
		'identity' => array(
			'id'    => 'identity',
			'label' => __( 'Identity Phase', 'guestify' ),
			'cta'   => __( 'Complete your Media Kit profile.', 'guestify' ),
		),
		'discovery' => array(
			'id'    => 'discovery',
			'label' => __( 'Discovery Phase', 'guestify' ),
			'cta'   => __( 'Find podcasts that match your expertise.', 'guestify' ),
		),
		'intelligence' => array(
			'id'    => 'intelligence',
			'label' => __( 'Intelligence Phase', 'guestify' ),
			'cta'   => __( 'Research and vet potential shows.', 'guestify' ),
		),
		'action' => array(
			'id'    => 'action',
			'label' => __( 'Action Phase', 'guestify' ),
			'cta'   => __( 'Focus on increasing pitch volume.', 'guestify' ),
		),
	);

	// Determine current stage based on activity
	if ( $pipeline['pitches_sent'] > 0 ) {
		return $stages['action'];
	} elseif ( $pipeline['shows_researched'] > 0 ) {
		return $stages['intelligence'];
	} elseif ( $pipeline['shows_found'] > 0 ) {
		return $stages['discovery'];
	} else {
		return $stages['identity'];
	}
}

/**
 * Load Dashboard API
 */
require_once get_template_directory() . '/inc/class-dashboard-api.php';

/**
 * Load Command Palette API
 */
require_once get_template_directory() . '/inc/class-command-palette-api.php';

/**
 * Auth Modal for non-logged-in users
 *
 * Enqueues assets and outputs modal HTML on public tool pages.
 */
function guestify_auth_modal_setup() {
    // Only for non-logged-in users
    if (is_user_logged_in()) {
        return;
    }

    // Only on /tools/ pages (or other public pages that need auth prompts)
    $current_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $url_path = parse_url($current_url, PHP_URL_PATH);

    $auth_modal_paths = ['/tools'];

    $should_show = false;
    foreach ($auth_modal_paths as $path) {
        if (strpos($url_path, $path) === 0) {
            $should_show = true;
            break;
        }
    }

    if (!$should_show) {
        return;
    }

    // Enqueue styles
    wp_enqueue_style(
        'guestify-auth-modal',
        get_template_directory_uri() . '/css/auth-modal.css',
        [],
        filemtime(get_template_directory() . '/css/auth-modal.css')
    );

    // Enqueue scripts
    wp_enqueue_script(
        'guestify-auth-modal',
        get_template_directory_uri() . '/js/auth-modal.js',
        [],
        filemtime(get_template_directory() . '/js/auth-modal.js'),
        true
    );
}
add_action('wp_enqueue_scripts', 'guestify_auth_modal_setup');

/**
 * Output auth modal HTML in footer for non-logged-in users
 */
function guestify_auth_modal_output() {
    // Only for non-logged-in users
    if (is_user_logged_in()) {
        return;
    }

    // Only on /tools/ pages
    $current_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $url_path = parse_url($current_url, PHP_URL_PATH);

    $auth_modal_paths = ['/tools'];

    $should_show = false;
    foreach ($auth_modal_paths as $path) {
        if (strpos($url_path, $path) === 0) {
            $should_show = true;
            break;
        }
    }

    if (!$should_show) {
        return;
    }

    get_template_part('template-parts/auth-modal');
}
add_action('wp_footer', 'guestify_auth_modal_output');