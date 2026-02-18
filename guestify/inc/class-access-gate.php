<?php
/**
 * Centralized Access Gate for Virtual Pages
 *
 * Intercepts requests on `template_redirect` (priority 5) to enforce
 * authentication and tier-based access control on virtual/app pages
 * that bypass WP Fusion's normal post-meta restrictions.
 *
 * Lives in the theme (not a plugin) so it is always active regardless
 * of which plugins are enabled.
 *
 * Default rules:
 *   - /app/, /account/, /courses/, /onboarding/  → login required
 *   - /tools/, /templates/                        → public
 *
 * Plugins register additional rules via the `gfy_register_access_rules` action:
 *
 *     add_action('gfy_register_access_rules', function (GFY_Access_Gate $gate) {
 *         $gate->register_path_rule('/app/outreach/', [
 *             'auth_required' => true,
 *             'capability'    => 'edit_posts',
 *         ]);
 *     });
 *
 * @package Guestify
 * @since   1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GFY_Access_Gate {

    /** @var GFY_Access_Gate|null */
    private static ?GFY_Access_Gate $instance = null;

    /**
     * Registered path rules keyed by normalised path.
     *
     * Each value is an array with the shape:
     *   auth_required  bool   – require a logged-in user
     *   public         bool   – explicitly allow anyone (overrides parent auth rules)
     *   required_tier  string – minimum tier slug (compared via PIT_Guestify_Tier_Resolver)
     *   required_tags  array  – WP Fusion tag labels; ANY match grants access
     *   capability     string – WordPress capability the user must possess
     *   redirect_to    string – custom denied-access redirect path
     *   match_type     string – 'prefix' (default) or 'exact'
     *
     * @var array<string, array>
     */
    private array $rules = [];

    /**
     * Whether plugin rules have been collected.
     *
     * @var bool
     */
    private bool $rules_collected = false;

    /* ------------------------------------------------------------------
     * Singleton
     * ----------------------------------------------------------------*/

    public static function get_instance(): GFY_Access_Gate {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->register_default_rules();
        add_action('template_redirect', [$this, 'check_access'], 5);
    }

    /* ------------------------------------------------------------------
     * Default rules
     * ----------------------------------------------------------------*/

    private function register_default_rules(): void {
        // Auth-required paths (prefix match – covers all sub-paths)
        $this->rules['/app/']        = ['auth_required' => true, 'match_type' => 'prefix'];
        $this->rules['/account/']    = ['auth_required' => true, 'match_type' => 'prefix'];
        $this->rules['/courses/']    = ['auth_required' => true, 'match_type' => 'prefix'];
        $this->rules['/onboarding/'] = ['auth_required' => true, 'match_type' => 'prefix'];

        // Explicitly public paths (override any parent auth rule)
        $this->rules['/tools/']      = ['public' => true, 'match_type' => 'prefix'];
        $this->rules['/templates/']  = ['public' => true, 'match_type' => 'prefix'];
    }

    /* ------------------------------------------------------------------
     * Public API – rule registration
     * ----------------------------------------------------------------*/

    /**
     * Register a path rule.
     *
     * @param string $path   URL path, e.g. '/app/outreach/'
     * @param array  $config Rule configuration.
     */
    public function register_path_rule(string $path, array $config): void {
        $path = '/' . trim($path, '/') . '/';

        $defaults = [
            'auth_required' => true,
            'public'        => false,
            'required_tier' => '',
            'required_tags' => [],
            'capability'    => '',
            'redirect_to'   => '',
            'match_type'    => 'prefix',
        ];

        $this->rules[$path] = array_merge($defaults, $config);
    }

    /* ------------------------------------------------------------------
     * Main access check (template_redirect callback)
     * ----------------------------------------------------------------*/

    public function check_access(): void {
        // Skip contexts where page access control is irrelevant
        if (is_admin()) {
            return;
        }
        if (wp_doing_ajax()) {
            return;
        }
        if (wp_doing_cron()) {
            return;
        }
        if (defined('WP_CLI') && WP_CLI) {
            return;
        }
        if (defined('REST_REQUEST') && REST_REQUEST) {
            return;
        }

        $request_path = $this->get_request_path();
        if (empty($request_path)) {
            return;
        }

        // Collect plugin rules once (deferred so plugins have time to load)
        $this->maybe_collect_plugin_rules();

        // Find the most-specific matching rule
        $rule = $this->match_path($request_path);
        if ($rule === null) {
            return; // No rule matches – let WordPress handle normally
        }

        // Explicitly public path – allow through
        if (!empty($rule['public'])) {
            return;
        }

        // Auth-required path
        if (!empty($rule['auth_required'])) {
            if (!is_user_logged_in()) {
                $this->redirect_to_login();
                return;
            }

            // Logged-in user – check additional requirements
            if (!$this->user_meets_requirements(get_current_user_id(), $rule)) {
                $this->redirect_access_denied($rule);
                return;
            }
        }
    }

    /* ------------------------------------------------------------------
     * Plugin rule collection
     * ----------------------------------------------------------------*/

    private function maybe_collect_plugin_rules(): void {
        if ($this->rules_collected) {
            return;
        }
        $this->rules_collected = true;

        /**
         * Fires so plugins can register their own access rules.
         *
         * @param GFY_Access_Gate $gate The access gate instance.
         */
        do_action('gfy_register_access_rules', $this);
    }

    /* ------------------------------------------------------------------
     * Path matching
     * ----------------------------------------------------------------*/

    /**
     * Find the most-specific rule that matches the request path.
     *
     * Rules are sorted by path length descending so that
     * `/app/outreach/` beats `/app/` when both match.
     *
     * @param string $request_path Normalised request path.
     * @return array|null The matching rule, or null.
     */
    private function match_path(string $request_path): ?array {
        // Sort rules by specificity (longest path first)
        $sorted = $this->rules;
        uksort($sorted, function (string $a, string $b): int {
            return strlen($b) - strlen($a);
        });

        foreach ($sorted as $rule_path => $rule) {
            $match_type = $rule['match_type'] ?? 'prefix';

            if ($match_type === 'exact') {
                if ($request_path === $rule_path) {
                    return $rule;
                }
            } else {
                // Prefix match
                if (strpos($request_path, $rule_path) === 0) {
                    return $rule;
                }
                // Also match the path without trailing slash
                $rule_path_trimmed = rtrim($rule_path, '/');
                if ($request_path === $rule_path_trimmed) {
                    return $rule;
                }
            }
        }

        return null;
    }

    /* ------------------------------------------------------------------
     * Requirement checks
     * ----------------------------------------------------------------*/

    /**
     * Check whether a logged-in user satisfies the rule's tier, tag,
     * and capability requirements.
     *
     * @param int   $user_id WordPress user ID.
     * @param array $rule    The matched rule.
     * @return bool
     */
    private function user_meets_requirements(int $user_id, array $rule): bool {
        // WordPress capability check
        if (!empty($rule['capability'])) {
            if (!user_can($user_id, $rule['capability'])) {
                return false;
            }
        }

        // Tier-based check (uses PIT_Guestify_Tier_Resolver if available)
        if (!empty($rule['required_tier'])) {
            if (!$this->user_meets_tier($user_id, $rule['required_tier'])) {
                return false;
            }
        }

        // Direct tag check
        if (!empty($rule['required_tags']) && is_array($rule['required_tags'])) {
            if (!$this->user_has_any_tag($user_id, $rule['required_tags'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check whether the user's tier meets the minimum required tier.
     *
     * Falls back to allowing access if PIT_Guestify_Tier_Resolver is
     * not available (graceful degradation).
     *
     * @param int    $user_id       WordPress user ID.
     * @param string $required_tier Tier slug, e.g. 'velocity'.
     * @return bool
     */
    private function user_meets_tier(int $user_id, string $required_tier): bool {
        if (!class_exists('PIT_Guestify_Tier_Resolver')) {
            // Tier resolver not loaded – allow access (fail open)
            return true;
        }

        $user_tier    = PIT_Guestify_Tier_Resolver::get_user_tier($user_id);
        $required     = PIT_Guestify_Tier_Resolver::get_tier($required_tier);

        if ($required === null) {
            // Unknown tier requirement – allow access
            return true;
        }

        $user_priority     = (int) ($user_tier['priority'] ?? 0);
        $required_priority = (int) ($required['priority'] ?? 0);

        return $user_priority >= $required_priority;
    }

    /**
     * Check whether the user has ANY of the specified WP Fusion tags.
     *
     * Falls back to allowing access if PIT_Guestify_Tier_Resolver is
     * not available.
     *
     * @param int   $user_id WordPress user ID.
     * @param array $tags    Tag labels to check.
     * @return bool
     */
    private function user_has_any_tag(int $user_id, array $tags): bool {
        if (!class_exists('PIT_Guestify_Tier_Resolver')) {
            return true;
        }

        $user_tags = PIT_Guestify_Tier_Resolver::get_user_tags($user_id);
        return !empty(array_intersect($user_tags, $tags));
    }

    /* ------------------------------------------------------------------
     * Redirects
     * ----------------------------------------------------------------*/

    /**
     * Redirect the current visitor to the login page with a return URL.
     */
    private function redirect_to_login(): void {
        $return_url = home_url($_SERVER['REQUEST_URI']);
        $login_url  = add_query_arg(
            'redirect_to',
            urlencode($return_url),
            home_url('/login/')
        );

        wp_redirect($login_url, 302);
        exit;
    }

    /**
     * Redirect a logged-in user who lacks the required tier/tags/capability.
     *
     * @param array $rule The matched rule.
     */
    private function redirect_access_denied(array $rule): void {
        $default_redirect = home_url('/upgrade/');

        if (!empty($rule['redirect_to'])) {
            $redirect = home_url($rule['redirect_to']);
        } else {
            $redirect = $default_redirect;
        }

        /**
         * Filter the access-denied redirect URL.
         *
         * @param string $redirect The redirect URL.
         * @param array  $rule     The matched access rule.
         * @param int    $user_id  The current user ID.
         */
        $redirect = apply_filters('gfy_access_denied_redirect', $redirect, $rule, get_current_user_id());

        wp_redirect($redirect, 302);
        exit;
    }

    /* ------------------------------------------------------------------
     * Helpers
     * ----------------------------------------------------------------*/

    /**
     * Get the normalised request path (without query string).
     *
     * Returns the path relative to the WordPress home URL so that
     * subdirectory installs (e.g. example.com/blog/) are handled
     * correctly.
     *
     * @return string Normalised path, e.g. '/app/interview/detail/'
     */
    private function get_request_path(): string {
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        if (empty($request_uri)) {
            return '';
        }

        // Strip query string
        $path = parse_url($request_uri, PHP_URL_PATH);
        if ($path === null || $path === false) {
            return '';
        }

        // Handle subdirectory installs – strip the home path prefix
        $home_path = parse_url(home_url(), PHP_URL_PATH);
        if ($home_path && $home_path !== '/') {
            $home_path = rtrim($home_path, '/');
            if (strpos($path, $home_path) === 0) {
                $path = substr($path, strlen($home_path));
            }
        }

        // Normalise: ensure leading slash, add trailing slash
        $path = '/' . trim($path, '/');
        if ($path !== '/') {
            $path .= '/';
        }

        return $path;
    }
}

// Self-initialise
GFY_Access_Gate::get_instance();
