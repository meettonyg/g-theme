<?php
/**
 * Template Name: Connect Authorize
 *
 * OAuth authorization page for Guestify Starter plugin connections.
 * URL: guestify.com/connect/authorize
 *
 * Query parameters:
 *   site_url       — Plugin site URL (required)
 *   return_url     — Where to redirect after authorization (required)
 *   state          — CSRF state token from the plugin (required)
 *   site_name      — Human-readable site name (optional)
 *   plugin_version — Plugin version (optional)
 *
 * Flow:
 *   1. Validate required parameters.
 *   2. If user is NOT logged in → show login/register form.
 *   3. If user IS logged in → show authorization prompt.
 *   4. On authorize → generate auth code → redirect to return_url.
 *
 * @package Guestify
 * @since   2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// 1. Validate query parameters.
// ─────────────────────────────────────────────────────────────────────────────

$site_url       = isset( $_GET['site_url'] )       ? esc_url_raw( wp_unslash( $_GET['site_url'] ) )       : '';
$return_url     = isset( $_GET['return_url'] )     ? esc_url_raw( wp_unslash( $_GET['return_url'] ) )     : '';
$state          = isset( $_GET['state'] )          ? sanitize_text_field( wp_unslash( $_GET['state'] ) )  : '';
$site_name      = isset( $_GET['site_name'] )      ? sanitize_text_field( wp_unslash( $_GET['site_name'] ) )      : '';
$plugin_version = isset( $_GET['plugin_version'] ) ? sanitize_text_field( wp_unslash( $_GET['plugin_version'] ) ) : '';

// Derive a readable site name from URL if not provided.
if ( empty( $site_name ) && ! empty( $site_url ) ) {
    $site_name = wp_parse_url( $site_url, PHP_URL_HOST ) ?: $site_url;
}

$missing_params = empty( $site_url ) || empty( $return_url ) || empty( $state );

// ─────────────────────────────────────────────────────────────────────────────
// 2. Handle form submissions (login, register, authorize).
// ─────────────────────────────────────────────────────────────────────────────

$error_message   = '';
$success_message = '';

// Preserve OAuth params across form submissions.
$oauth_params = [
    'site_url'       => $site_url,
    'return_url'     => $return_url,
    'state'          => $state,
    'site_name'      => $site_name,
    'plugin_version' => $plugin_version,
];

$form_action = add_query_arg( $oauth_params, get_permalink() );

// Handle LOGIN submission.
if ( isset( $_POST['gfy_connect_login'] ) && wp_verify_nonce( $_POST['_gfy_login_nonce'] ?? '', 'gfy_connect_login' ) ) {
    $login_email    = sanitize_email( wp_unslash( $_POST['login_email'] ?? '' ) );
    $login_password = $_POST['login_password'] ?? '';

    if ( empty( $login_email ) || empty( $login_password ) ) {
        $error_message = __( 'Please enter your email and password.', 'guestify' );
    } else {
        $user = wp_signon( [
            'user_login'    => $login_email,
            'user_password' => $login_password,
            'remember'      => true,
        ] );

        if ( is_wp_error( $user ) ) {
            $error_message = $user->get_error_message();
        } else {
            wp_set_current_user( $user->ID );
            // Continue to authorization screen (below).
        }
    }
}

// Handle REGISTER submission.
if ( isset( $_POST['gfy_connect_register'] ) && wp_verify_nonce( $_POST['_gfy_register_nonce'] ?? '', 'gfy_connect_register' ) ) {
    $reg_email    = sanitize_email( wp_unslash( $_POST['reg_email'] ?? '' ) );
    $reg_password = $_POST['reg_password'] ?? '';
    $reg_name     = sanitize_text_field( wp_unslash( $_POST['reg_name'] ?? '' ) );

    if ( empty( $reg_email ) || empty( $reg_password ) ) {
        $error_message = __( 'Please fill in all required fields.', 'guestify' );
    } elseif ( strlen( $reg_password ) < 8 ) {
        $error_message = __( 'Password must be at least 8 characters.', 'guestify' );
    } elseif ( email_exists( $reg_email ) ) {
        $error_message = __( 'An account with this email already exists. Please sign in.', 'guestify' );
    } else {
        // Create the WordPress user.
        $user_id = wp_create_user( $reg_email, $reg_password, $reg_email );

        if ( is_wp_error( $user_id ) ) {
            $error_message = $user_id->get_error_message();
        } else {
            // Set display name.
            if ( ! empty( $reg_name ) ) {
                wp_update_user( [
                    'ID'           => $user_id,
                    'display_name' => $reg_name,
                    'first_name'   => explode( ' ', $reg_name )[0],
                    'last_name'    => implode( ' ', array_slice( explode( ' ', $reg_name ), 1 ) ),
                ] );
            }

            // Set role to subscriber.
            $wp_user = new WP_User( $user_id );
            $wp_user->set_role( 'subscriber' );

            // Log the user in.
            wp_set_auth_cookie( $user_id, true );
            wp_set_current_user( $user_id );

            /**
             * Fires after a new user registers via plugin OAuth.
             *
             * WP Fusion hooks user_register to create GHL contact.
             * GHL applies 'guestify-plugin-connected' tag.
             * GFY_WPFusion_Credit_Sync allocates free-tier credits.
             *
             * @param int $user_id New user ID.
             */
            do_action( 'user_register', $user_id );
            do_action( 'gfy_plugin_user_registered', $user_id, $site_url );

            // Continue to authorization screen.
        }
    }
}

// Handle AUTHORIZE submission.
if ( isset( $_POST['gfy_connect_authorize'] ) && wp_verify_nonce( $_POST['_gfy_authorize_nonce'] ?? '', 'gfy_connect_authorize' ) ) {
    if ( is_user_logged_in() && ! $missing_params ) {
        $user_id = get_current_user_id();

        // Generate authorization code.
        $code = GFY_OAuth_Server::generate_auth_code(
            $user_id,
            $site_url,
            $site_name,
            $plugin_version,
            $state
        );

        // Redirect back to the plugin with the code.
        $redirect = add_query_arg( [
            'code'  => $code,
            'state' => $state,
        ], $return_url );

        wp_redirect( $redirect );
        exit;
    }
}

// Handle DENY submission.
if ( isset( $_POST['gfy_connect_deny'] ) ) {
    $redirect = add_query_arg( [
        'error' => 'access_denied',
        'state' => $state,
    ], $return_url );

    wp_redirect( $redirect );
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// 3. Render the page.
// ─────────────────────────────────────────────────────────────────────────────

get_header();
?>

<style>
    .gfy-connect-page {
        min-height: 80vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
        background: #f0f2f5;
    }

    .gfy-connect-card {
        width: 100%;
        max-width: 440px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        padding: 40px;
    }

    .gfy-connect-logo {
        text-align: center;
        margin-bottom: 24px;
    }

    .gfy-connect-logo img {
        height: 40px;
        width: auto;
    }

    .gfy-connect-logo h2 {
        font-size: 22px;
        font-weight: 600;
        margin: 12px 0 4px;
        color: #1a1a2e;
    }

    .gfy-connect-logo p {
        color: #666;
        font-size: 14px;
        margin: 0;
    }

    .gfy-connect-site {
        background: #f8f9fa;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 24px;
        text-align: center;
    }

    .gfy-connect-site strong {
        display: block;
        font-size: 15px;
        color: #1a1a2e;
    }

    .gfy-connect-site span {
        font-size: 13px;
        color: #718096;
    }

    .gfy-connect-scopes {
        margin-bottom: 24px;
    }

    .gfy-connect-scopes h4 {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #718096;
        margin-bottom: 12px;
    }

    .gfy-connect-scopes ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .gfy-connect-scopes li {
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
        font-size: 14px;
        color: #2d3748;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .gfy-connect-scopes li::before {
        content: "✓";
        color: #48bb78;
        font-weight: 700;
    }

    .gfy-connect-actions {
        display: flex;
        gap: 12px;
    }

    .gfy-connect-actions button {
        flex: 1;
        padding: 12px 20px;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        border: none;
        transition: opacity 0.2s;
    }

    .gfy-connect-actions button:hover {
        opacity: 0.9;
    }

    .gfy-btn-authorize {
        background: #2563eb;
        color: #fff;
    }

    .gfy-btn-deny {
        background: #f1f5f9;
        color: #64748b;
    }

    .gfy-connect-error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #dc2626;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
    }

    .gfy-connect-form label {
        display: block;
        font-size: 14px;
        font-weight: 500;
        color: #374151;
        margin-bottom: 4px;
    }

    .gfy-connect-form input[type="text"],
    .gfy-connect-form input[type="email"],
    .gfy-connect-form input[type="password"] {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 15px;
        margin-bottom: 16px;
        box-sizing: border-box;
    }

    .gfy-connect-form input:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .gfy-connect-form button[type="submit"] {
        width: 100%;
        padding: 12px;
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
    }

    .gfy-connect-form button[type="submit"]:hover {
        background: #1d4ed8;
    }

    .gfy-connect-tabs {
        display: flex;
        border-bottom: 2px solid #e5e7eb;
        margin-bottom: 24px;
    }

    .gfy-connect-tab {
        flex: 1;
        padding: 12px;
        text-align: center;
        font-size: 14px;
        font-weight: 500;
        color: #6b7280;
        cursor: pointer;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        background: none;
        border-top: none;
        border-left: none;
        border-right: none;
    }

    .gfy-connect-tab.active {
        color: #2563eb;
        border-bottom-color: #2563eb;
    }

    .gfy-connect-tab-content {
        display: none;
    }

    .gfy-connect-tab-content.active {
        display: block;
    }

    .gfy-connect-divider {
        text-align: center;
        margin: 20px 0;
        color: #9ca3af;
        font-size: 13px;
    }

    .gfy-connect-user {
        text-align: center;
        margin-bottom: 20px;
        padding: 16px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 8px;
    }

    .gfy-connect-user strong {
        color: #166534;
    }
</style>

<div class="gfy-connect-page">
    <div class="gfy-connect-card">

        <div class="gfy-connect-logo">
            <h2><?php esc_html_e( 'Guestify', 'guestify' ); ?></h2>
            <p><?php esc_html_e( 'Connect your WordPress site', 'guestify' ); ?></p>
        </div>

        <?php if ( $missing_params ) : ?>
            <!-- Missing parameters error -->
            <div class="gfy-connect-error">
                <?php esc_html_e( 'Invalid authorization request. Missing required parameters (site_url, return_url, state). Please try connecting again from your WordPress plugin.', 'guestify' ); ?>
            </div>

        <?php elseif ( is_user_logged_in() ) : ?>
            <!-- ── Authorize Screen ───────────────────────────────── -->
            <?php $current_user = wp_get_current_user(); ?>

            <div class="gfy-connect-user">
                <?php printf(
                    esc_html__( 'Signed in as %s', 'guestify' ),
                    '<strong>' . esc_html( $current_user->user_email ) . '</strong>'
                ); ?>
            </div>

            <div class="gfy-connect-site">
                <strong><?php echo esc_html( $site_name ); ?></strong>
                <span><?php echo esc_html( $site_url ); ?></span>
            </div>

            <div class="gfy-connect-scopes">
                <h4><?php esc_html_e( 'This site is requesting access to:', 'guestify' ); ?></h4>
                <ul>
                    <li><?php esc_html_e( 'View your subscription tier and credit balance', 'guestify' ); ?></li>
                    <li><?php esc_html_e( 'Generate AI content using your credits', 'guestify' ); ?></li>
                    <li><?php esc_html_e( 'Transcribe podcast audio using your credits', 'guestify' ); ?></li>
                    <li><?php esc_html_e( 'Sync podcast appearance data to your profile', 'guestify' ); ?></li>
                    <li><?php esc_html_e( 'Display your Guest Authority Score', 'guestify' ); ?></li>
                </ul>
            </div>

            <form method="post" action="<?php echo esc_url( $form_action ); ?>">
                <?php wp_nonce_field( 'gfy_connect_authorize', '_gfy_authorize_nonce' ); ?>
                <div class="gfy-connect-actions">
                    <button type="submit" name="gfy_connect_deny" class="gfy-btn-deny">
                        <?php esc_html_e( 'Deny', 'guestify' ); ?>
                    </button>
                    <button type="submit" name="gfy_connect_authorize" value="1" class="gfy-btn-authorize">
                        <?php esc_html_e( 'Authorize', 'guestify' ); ?>
                    </button>
                </div>
            </form>

        <?php else : ?>
            <!-- ── Login / Register Screen ────────────────────────── -->

            <?php if ( $error_message ) : ?>
                <div class="gfy-connect-error"><?php echo esc_html( $error_message ); ?></div>
            <?php endif; ?>

            <div class="gfy-connect-site">
                <strong><?php echo esc_html( $site_name ); ?></strong>
                <span><?php esc_html_e( 'wants to connect to your Guestify account', 'guestify' ); ?></span>
            </div>

            <!-- Tabs -->
            <div class="gfy-connect-tabs">
                <button class="gfy-connect-tab active" data-tab="login" onclick="gfyConnectSwitchTab('login')">
                    <?php esc_html_e( 'Sign In', 'guestify' ); ?>
                </button>
                <button class="gfy-connect-tab" data-tab="register" onclick="gfyConnectSwitchTab('register')">
                    <?php esc_html_e( 'Create Account', 'guestify' ); ?>
                </button>
            </div>

            <!-- Login Form -->
            <div class="gfy-connect-tab-content active" id="gfy-tab-login">
                <form method="post" action="<?php echo esc_url( $form_action ); ?>" class="gfy-connect-form">
                    <?php wp_nonce_field( 'gfy_connect_login', '_gfy_login_nonce' ); ?>

                    <label for="login_email"><?php esc_html_e( 'Email', 'guestify' ); ?></label>
                    <input type="email" id="login_email" name="login_email" required
                           placeholder="you@example.com"
                           value="<?php echo esc_attr( $_POST['login_email'] ?? '' ); ?>" />

                    <label for="login_password"><?php esc_html_e( 'Password', 'guestify' ); ?></label>
                    <input type="password" id="login_password" name="login_password" required
                           placeholder="<?php esc_attr_e( 'Your password', 'guestify' ); ?>" />

                    <button type="submit" name="gfy_connect_login" value="1">
                        <?php esc_html_e( 'Sign In & Continue', 'guestify' ); ?>
                    </button>
                </form>
            </div>

            <!-- Register Form -->
            <div class="gfy-connect-tab-content" id="gfy-tab-register">
                <form method="post" action="<?php echo esc_url( $form_action ); ?>" class="gfy-connect-form">
                    <?php wp_nonce_field( 'gfy_connect_register', '_gfy_register_nonce' ); ?>

                    <label for="reg_name"><?php esc_html_e( 'Full Name', 'guestify' ); ?></label>
                    <input type="text" id="reg_name" name="reg_name"
                           placeholder="Jane Smith"
                           value="<?php echo esc_attr( $_POST['reg_name'] ?? '' ); ?>" />

                    <label for="reg_email"><?php esc_html_e( 'Email', 'guestify' ); ?> *</label>
                    <input type="email" id="reg_email" name="reg_email" required
                           placeholder="you@example.com"
                           value="<?php echo esc_attr( $_POST['reg_email'] ?? '' ); ?>" />

                    <label for="reg_password"><?php esc_html_e( 'Password', 'guestify' ); ?> *</label>
                    <input type="password" id="reg_password" name="reg_password" required
                           minlength="8"
                           placeholder="<?php esc_attr_e( 'Min 8 characters', 'guestify' ); ?>" />

                    <button type="submit" name="gfy_connect_register" value="1">
                        <?php esc_html_e( 'Create Account & Continue', 'guestify' ); ?>
                    </button>

                    <p class="gfy-connect-divider">
                        <?php esc_html_e( 'By creating an account you agree to our Terms of Service.', 'guestify' ); ?>
                    </p>
                </form>
            </div>

            <script>
                function gfyConnectSwitchTab(tab) {
                    document.querySelectorAll('.gfy-connect-tab').forEach(function(el) {
                        el.classList.toggle('active', el.dataset.tab === tab);
                    });
                    document.querySelectorAll('.gfy-connect-tab-content').forEach(function(el) {
                        el.classList.toggle('active', el.id === 'gfy-tab-' + tab);
                    });
                }
            </script>

        <?php endif; ?>

    </div>
</div>

<?php get_footer(); ?>
