<?php
/**
 * Authentication Modal Template
 *
 * Displays a modal for login/registration with social login options.
 * Include this template on pages where you want to prompt non-logged-in users.
 *
 * @package Guestify
 */

// Only show for non-logged-in users
if (is_user_logged_in()) {
    return;
}

// Get current page URL for redirect after login
$redirect_url = isset($_SERVER['REQUEST_URI']) ? home_url($_SERVER['REQUEST_URI']) : home_url('/app/');
?>

<!-- Auth Modal -->
<div class="auth-modal" id="authModal" role="dialog" aria-modal="true" aria-labelledby="authModalTitle">
    <div class="auth-modal__backdrop" id="authModalBackdrop"></div>
    <div class="auth-modal__container">
        <button class="auth-modal__close" id="authModalClose" aria-label="Close">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <!-- Logo/Icon -->
        <div class="auth-modal__logo">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/guestify-icon.png'); ?>" alt="Guestify" onerror="this.style.display='none'">
        </div>

        <!-- Header -->
        <h2 class="auth-modal__title" id="authModalTitle">Save your progress</h2>
        <p class="auth-modal__subtitle" id="authModalSubtitle">Sign in to save your work and unlock all features:</p>

        <!-- Social Login Buttons -->
        <div class="auth-modal__social">
            <?php if (function_exists('nsl_action_login')): ?>
                <!-- Nextend Social Login buttons -->
                <?php
                // Try to get Nextend buttons
                if (class_exists('NextendSocialLogin')) {
                    echo do_shortcode('[nextend_social_login redirect="' . esc_url($redirect_url) . '"]');
                } else {
                    // Fallback manual buttons if Nextend not rendering
                    ?>
                    <a href="<?php echo esc_url(home_url('/wp-login.php?loginSocial=facebook&redirect=' . urlencode($redirect_url))); ?>" class="auth-modal__social-btn auth-modal__social-btn--facebook">
                        <i class="fa-brands fa-facebook-f"></i>
                        <span>Continue with Facebook</span>
                    </a>
                    <a href="<?php echo esc_url(home_url('/wp-login.php?loginSocial=google&redirect=' . urlencode($redirect_url))); ?>" class="auth-modal__social-btn auth-modal__social-btn--google">
                        <svg viewBox="0 0 24 24" width="20" height="20"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                        <span>Continue with Google</span>
                    </a>
                    <a href="<?php echo esc_url(home_url('/wp-login.php?loginSocial=linkedin&redirect=' . urlencode($redirect_url))); ?>" class="auth-modal__social-btn auth-modal__social-btn--linkedin">
                        <i class="fa-brands fa-linkedin-in"></i>
                        <span>Continue with LinkedIn</span>
                    </a>
                    <?php
                }
                ?>
            <?php else: ?>
                <!-- Manual social login buttons -->
                <a href="<?php echo esc_url(home_url('/wp-login.php?loginSocial=facebook&redirect=' . urlencode($redirect_url))); ?>" class="auth-modal__social-btn auth-modal__social-btn--facebook">
                    <i class="fa-brands fa-facebook-f"></i>
                    <span>Continue with Facebook</span>
                </a>
                <a href="<?php echo esc_url(home_url('/wp-login.php?loginSocial=google&redirect=' . urlencode($redirect_url))); ?>" class="auth-modal__social-btn auth-modal__social-btn--google">
                    <svg viewBox="0 0 24 24" width="20" height="20"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                    <span>Continue with Google</span>
                </a>
                <a href="<?php echo esc_url(home_url('/wp-login.php?loginSocial=linkedin&redirect=' . urlencode($redirect_url))); ?>" class="auth-modal__social-btn auth-modal__social-btn--linkedin">
                    <i class="fa-brands fa-linkedin-in"></i>
                    <span>Continue with LinkedIn</span>
                </a>
            <?php endif; ?>
        </div>

        <!-- Divider -->
        <div class="auth-modal__divider">
            <span>or continue with email</span>
        </div>

        <!-- Email Form -->
        <form class="auth-modal__form" id="authModalForm" action="<?php echo esc_url(home_url('/login/')); ?>" method="get">
            <input type="hidden" name="redirect_to" value="<?php echo esc_attr($redirect_url); ?>">
            <div class="auth-modal__input-wrapper">
                <input
                    type="email"
                    name="email"
                    class="auth-modal__input"
                    placeholder="you@example.com"
                    required
                >
            </div>
            <button type="submit" class="auth-modal__submit">
                Continue with Email
            </button>
        </form>

        <!-- Benefits -->
        <ul class="auth-modal__benefits">
            <li><i class="fa-solid fa-check"></i> Your work saved forever</li>
            <li><i class="fa-solid fa-check"></i> Access all AI tools (free)</li>
            <li><i class="fa-solid fa-check"></i> Track your podcast outreach</li>
        </ul>

        <!-- Footer -->
        <p class="auth-modal__footer">
            Already have an account? <a href="<?php echo esc_url(home_url('/login/?redirect_to=' . urlencode($redirect_url))); ?>">Log in</a>
        </p>
    </div>
</div>
