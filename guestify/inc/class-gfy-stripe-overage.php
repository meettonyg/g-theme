<?php
/**
 * Stripe Overage Integration (Feature-flagged)
 *
 * Handles purchasing additional credits via Stripe Checkout when a user
 * exceeds their tier allowance. Creates one-time checkout sessions for
 * credit packs and processes the checkout.session.completed webhook to
 * grant overage credits.
 *
 * NOTE: This is an optional feature, only active when Stripe is configured
 * (guestify_stripe_config option has a secret_key). Subscription lifecycle
 * is managed via GoHighLevel -> WP Fusion tags (see GFY_WPFusion_Credit_Sync).
 * This class handles ONLY one-time credit pack purchases.
 *
 * @package Guestify
 * @since   2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GFY_Stripe_Overage {

    /**
     * Default credit packs available for purchase
     */
    const DEFAULT_PACKS = [
        'small'  => ['credits' => 100, 'price_cents' => 999,  'label' => '100 Credits'],
        'medium' => ['credits' => 300, 'price_cents' => 2499, 'label' => '300 Credits'],
        'large'  => ['credits' => 1000, 'price_cents' => 6999, 'label' => '1,000 Credits'],
    ];

    /**
     * wp_options key for admin-editable credit packs
     */
    const PACKS_OPTION = 'guestify_credit_packs';

    /**
     * wp_options key for Stripe config
     */
    const STRIPE_CONFIG_OPTION = 'guestify_stripe_config';

    // -------------------------------------------------------------------------
    // Configuration
    // -------------------------------------------------------------------------

    /**
     * Get available credit packs (admin-editable via wp_options)
     *
     * @return array
     */
    public static function get_packs(): array {
        $stored = get_option(self::PACKS_OPTION);
        if (is_array($stored) && !empty($stored)) {
            return $stored;
        }
        return self::DEFAULT_PACKS;
    }

    /**
     * Save credit packs config
     *
     * @param array $packs
     * @return bool
     */
    public static function save_packs(array $packs): bool {
        return update_option(self::PACKS_OPTION, $packs, false);
    }

    /**
     * Get Stripe configuration
     *
     * @return array
     */
    public static function get_stripe_config(): array {
        $config = get_option(self::STRIPE_CONFIG_OPTION, []);
        return wp_parse_args($config, [
            'secret_key'     => '',
            'webhook_secret' => '',
            'currency'       => 'usd',
        ]);
    }

    // -------------------------------------------------------------------------
    // Checkout Session
    // -------------------------------------------------------------------------

    /**
     * Create a Stripe Checkout session for credit purchase
     *
     * @param int    $user_id  WP user ID
     * @param string $pack_key Pack key (small/medium/large)
     * @return array|WP_Error  ['checkout_url' => '...'] or WP_Error
     */
    public static function create_checkout_session(int $user_id, string $pack_key) {
        $packs = self::get_packs();

        if (!isset($packs[$pack_key])) {
            return new WP_Error('invalid_pack', 'Invalid credit pack selected.', ['status' => 400]);
        }

        $pack   = $packs[$pack_key];
        $config = self::get_stripe_config();

        if (empty($config['secret_key'])) {
            return new WP_Error('stripe_not_configured', 'Stripe is not configured.', ['status' => 500]);
        }

        // Get or create Stripe customer ID for user
        $customer_id = self::get_or_create_customer($user_id, $config['secret_key']);
        if (is_wp_error($customer_id)) {
            return $customer_id;
        }

        $success_url = add_query_arg([
            'credits_purchased' => $pack['credits'],
        ], home_url('/dashboard/'));

        $cancel_url = home_url('/pricing/');

        $payload = [
            'customer'             => $customer_id,
            'mode'                 => 'payment',
            'payment_method_types' => ['card'],
            'line_items'           => [
                [
                    'price_data' => [
                        'currency'     => $config['currency'],
                        'unit_amount'  => (int) $pack['price_cents'],
                        'product_data' => [
                            'name'        => $pack['label'] . ' — Authority Credits',
                            'description' => sprintf('%d additional credits for your Guestify account', $pack['credits']),
                        ],
                    ],
                    'quantity' => 1,
                ],
            ],
            'metadata'             => [
                'user_id'    => $user_id,
                'pack_key'   => $pack_key,
                'credits'    => $pack['credits'],
                'type'       => 'credit_overage',
            ],
            'success_url'          => $success_url,
            'cancel_url'           => $cancel_url,
        ];

        $response = self::stripe_request('POST', '/v1/checkout/sessions', $payload, $config['secret_key']);

        if (is_wp_error($response)) {
            return $response;
        }

        return [
            'checkout_url' => $response['url'],
            'session_id'   => $response['id'],
        ];
    }

    // -------------------------------------------------------------------------
    // Webhook Handling
    // -------------------------------------------------------------------------

    /**
     * Handle Stripe webhook events
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public static function handle_webhook(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $config = self::get_stripe_config();

        if (empty($config['webhook_secret'])) {
            return new WP_Error('webhook_not_configured', 'Webhook secret not configured.', ['status' => 500]);
        }

        // Verify webhook signature
        $payload   = $request->get_body();
        $signature = $request->get_header('stripe-signature');

        $event = self::verify_webhook_signature($payload, $signature, $config['webhook_secret']);
        if (is_wp_error($event)) {
            return $event;
        }

        $event_type = $event['type'] ?? '';

        switch ($event_type) {
            case 'checkout.session.completed':
                return self::handle_checkout_completed($event['data']['object']);

            default:
                return rest_ensure_response(['received' => true, 'handled' => false]);
        }
    }

    /**
     * Handle checkout.session.completed — grant overage credits
     *
     * @param array $session Stripe checkout session object
     * @return WP_REST_Response
     */
    private static function handle_checkout_completed(array $session): WP_REST_Response {
        $metadata = $session['metadata'] ?? [];

        // Only process credit overage purchases
        if (($metadata['type'] ?? '') !== 'credit_overage') {
            return rest_ensure_response(['received' => true, 'handled' => false, 'reason' => 'not_credit_overage']);
        }

        $user_id = (int) ($metadata['user_id'] ?? 0);
        $credits = (int) ($metadata['credits'] ?? 0);
        $pack_key = $metadata['pack_key'] ?? '';

        if (!$user_id || !$credits) {
            return rest_ensure_response(['received' => true, 'handled' => false, 'reason' => 'missing_metadata']);
        }

        // Grant overage credits
        $success = self::grant_overage_credits($user_id, $credits, [
            'stripe_session_id' => $session['id'] ?? '',
            'pack_key'          => $pack_key,
            'amount_paid'       => $session['amount_total'] ?? 0,
        ]);

        return rest_ensure_response([
            'received' => true,
            'handled'  => true,
            'credits_granted' => $credits,
            'user_id'  => $user_id,
            'success'  => $success,
        ]);
    }

    // -------------------------------------------------------------------------
    // Credit Granting
    // -------------------------------------------------------------------------

    /**
     * Grant overage credits to a user
     *
     * @param int   $user_id  WP user ID
     * @param int   $credits  Number of credits to grant
     * @param array $metadata Additional context
     * @return bool
     */
    public static function grant_overage_credits(int $user_id, int $credits, array $metadata = []): bool {
        global $wpdb;
        $table_alloc = $wpdb->prefix . 'pit_credit_allocations';
        $table_txn   = $wpdb->prefix . 'pit_credit_transactions';

        $alloc = GFY_Credit_Repository::get_allocation($user_id);
        if (!$alloc) {
            $alloc = GFY_Credit_Repository::get_or_create_allocation($user_id);
        }

        if (!$alloc) {
            return false;
        }

        $new_overage = (int) $alloc->overage_balance + $credits;

        $wpdb->update($table_alloc, [
            'overage_balance' => $new_overage,
        ], ['id' => $alloc->id]);

        $new_total = (int) $alloc->current_balance + (int) $alloc->rollover_balance + $new_overage;

        // Log the transaction
        $wpdb->insert($table_txn, [
            'user_id'        => $user_id,
            'allocation_id'  => $alloc->id,
            'action_type'    => 'overage_purchase',
            'credits_used'   => -$credits, // Negative = credits added
            'balance_after'  => $new_total,
            'source_type'    => 'overage',
            'reference_id'   => $metadata['stripe_session_id'] ?? null,
            'reference_type' => 'stripe_checkout',
            'metadata'       => wp_json_encode($metadata),
            'created_at'     => current_time('mysql'),
        ]);

        do_action('guestify_overage_credits_granted', $user_id, $credits, $metadata);

        return true;
    }

    // -------------------------------------------------------------------------
    // Stripe Customer Management
    // -------------------------------------------------------------------------

    /**
     * Get or create a Stripe customer for a WP user
     *
     * @param int    $user_id    WP user ID
     * @param string $secret_key Stripe secret key
     * @return string|WP_Error   Customer ID or error
     */
    private static function get_or_create_customer(int $user_id, string $secret_key) {
        // Check if user already has a Stripe customer ID
        $customer_id = get_user_meta($user_id, '_stripe_customer_id', true);
        if (!empty($customer_id)) {
            return $customer_id;
        }

        // Also check the allocation table
        global $wpdb;
        $table = $wpdb->prefix . 'pit_credit_allocations';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") === $table) {
            $stored_id = $wpdb->get_var($wpdb->prepare(
                "SELECT stripe_customer_id FROM {$table} WHERE user_id = %d AND stripe_customer_id IS NOT NULL",
                $user_id
            ));
            if (!empty($stored_id)) {
                update_user_meta($user_id, '_stripe_customer_id', $stored_id);
                return $stored_id;
            }
        }

        // Create new customer
        $user = get_user_by('ID', $user_id);
        if (!$user) {
            return new WP_Error('user_not_found', 'User not found.', ['status' => 404]);
        }

        $response = self::stripe_request('POST', '/v1/customers', [
            'email'    => $user->user_email,
            'name'     => trim($user->first_name . ' ' . $user->last_name) ?: $user->display_name,
            'metadata' => [
                'wp_user_id' => $user_id,
                'site_url'   => home_url(),
            ],
        ], $secret_key);

        if (is_wp_error($response)) {
            return $response;
        }

        $new_customer_id = $response['id'];

        // Store for future use
        update_user_meta($user_id, '_stripe_customer_id', $new_customer_id);

        // Also update the allocation table
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") === $table) {
            $wpdb->update($table, [
                'stripe_customer_id' => $new_customer_id,
            ], ['user_id' => $user_id]);
        }

        return $new_customer_id;
    }

    // -------------------------------------------------------------------------
    // Stripe API Helpers
    // -------------------------------------------------------------------------

    /**
     * Make a request to the Stripe API
     *
     * @param string $method     HTTP method
     * @param string $endpoint   API endpoint
     * @param array  $params     Request parameters
     * @param string $secret_key Stripe secret key
     * @return array|WP_Error    Decoded response or error
     */
    private static function stripe_request(string $method, string $endpoint, array $params, string $secret_key) {
        $url = 'https://api.stripe.com' . $endpoint;

        $args = [
            'method'  => $method,
            'headers' => [
                'Authorization' => 'Bearer ' . $secret_key,
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ],
            'timeout' => 30,
        ];

        if ($method === 'POST' || $method === 'PUT') {
            $args['body'] = self::flatten_params($params);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($code >= 400) {
            $message = $body['error']['message'] ?? 'Stripe API error';
            return new WP_Error('stripe_error', $message, ['status' => $code, 'stripe_error' => $body['error'] ?? []]);
        }

        return $body;
    }

    /**
     * Flatten nested arrays into Stripe's bracket notation
     *
     * @param array  $params
     * @param string $prefix
     * @return array
     */
    private static function flatten_params(array $params, string $prefix = ''): array {
        $flat = [];

        foreach ($params as $key => $value) {
            $full_key = $prefix ? "{$prefix}[{$key}]" : $key;

            if (is_array($value)) {
                $flat = array_merge($flat, self::flatten_params($value, $full_key));
            } else {
                $flat[$full_key] = $value;
            }
        }

        return $flat;
    }

    /**
     * Verify Stripe webhook signature
     *
     * @param string $payload        Raw request body
     * @param string $signature      Stripe-Signature header
     * @param string $webhook_secret Webhook endpoint secret
     * @return array|WP_Error        Decoded event or error
     */
    private static function verify_webhook_signature(string $payload, string $signature, string $webhook_secret) {
        if (empty($signature)) {
            return new WP_Error('missing_signature', 'Missing Stripe-Signature header.', ['status' => 400]);
        }

        // Parse the signature header
        $parts = [];
        foreach (explode(',', $signature) as $part) {
            $kv = explode('=', trim($part), 2);
            if (count($kv) === 2) {
                $parts[$kv[0]] = $kv[1];
            }
        }

        $timestamp = $parts['t'] ?? '';
        $v1_sig    = $parts['v1'] ?? '';

        if (empty($timestamp) || empty($v1_sig)) {
            return new WP_Error('invalid_signature', 'Invalid signature format.', ['status' => 400]);
        }

        // Check timestamp tolerance (5 minutes)
        if (abs(time() - (int) $timestamp) > 300) {
            return new WP_Error('signature_expired', 'Webhook timestamp too old.', ['status' => 400]);
        }

        // Compute expected signature
        $signed_payload    = $timestamp . '.' . $payload;
        $expected_signature = hash_hmac('sha256', $signed_payload, $webhook_secret);

        if (!hash_equals($expected_signature, $v1_sig)) {
            return new WP_Error('signature_mismatch', 'Webhook signature verification failed.', ['status' => 400]);
        }

        $event = json_decode($payload, true);
        if (!$event) {
            return new WP_Error('invalid_payload', 'Could not decode webhook payload.', ['status' => 400]);
        }

        return $event;
    }

    // -------------------------------------------------------------------------
    // REST API Routes
    // -------------------------------------------------------------------------

    /**
     * Register REST API routes for overage purchases
     */
    public static function register_routes() {
        $namespace = 'guestify/v1';

        // Get available credit packs
        register_rest_route($namespace, '/credits/packs', [
            'methods'             => 'GET',
            'callback'            => [__CLASS__, 'rest_get_packs'],
            'permission_callback' => function () {
                return is_user_logged_in();
            },
        ]);

        // Create checkout session
        register_rest_route($namespace, '/credits/purchase', [
            'methods'             => 'POST',
            'callback'            => [__CLASS__, 'rest_create_checkout'],
            'permission_callback' => function () {
                return is_user_logged_in();
            },
        ]);

        // Admin: update credit packs config
        register_rest_route($namespace, '/credits/packs', [
            'methods'             => 'PUT',
            'callback'            => [__CLASS__, 'rest_update_packs'],
            'permission_callback' => function () {
                return current_user_can(GFY_Constants::CAPABILITY_MANAGE);
            },
        ]);

        // Stripe webhook (no auth — signature-verified)
        register_rest_route($namespace, '/stripe/webhook', [
            'methods'             => 'POST',
            'callback'            => [__CLASS__, 'handle_webhook'],
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * GET /credits/packs
     */
    public static function rest_get_packs(): WP_REST_Response {
        $packs = self::get_packs();

        $formatted = [];
        foreach ($packs as $key => $pack) {
            $formatted[] = array_merge($pack, [
                'key'            => $key,
                'price_display'  => '$' . number_format($pack['price_cents'] / 100, 2),
                'per_credit'     => round($pack['price_cents'] / $pack['credits'], 1),
            ]);
        }

        return rest_ensure_response([
            'success' => true,
            'data'    => $formatted,
        ]);
    }

    /**
     * POST /credits/purchase
     */
    public static function rest_create_checkout(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $user_id  = get_current_user_id();
        $pack_key = sanitize_key($request->get_param('pack'));

        if (empty($pack_key)) {
            return new WP_Error('missing_pack', 'Please specify a credit pack.', ['status' => 400]);
        }

        $result = self::create_checkout_session($user_id, $pack_key);

        if (is_wp_error($result)) {
            return $result;
        }

        return rest_ensure_response([
            'success' => true,
            'data'    => $result,
        ]);
    }

    /**
     * PUT /credits/packs (admin)
     */
    public static function rest_update_packs(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $packs = $request->get_json_params();

        if (!is_array($packs) || empty($packs)) {
            return new WP_Error('invalid_packs', 'Packs must be a non-empty object.', ['status' => 400]);
        }

        // Validate each pack
        foreach ($packs as $key => $pack) {
            if (!isset($pack['credits'], $pack['price_cents'], $pack['label'])) {
                return new WP_Error('invalid_pack', "Pack '{$key}' must have credits, price_cents, and label.", ['status' => 400]);
            }
        }

        self::save_packs($packs);

        return rest_ensure_response([
            'success' => true,
            'data'    => self::get_packs(),
            'message' => 'Credit packs updated.',
        ]);
    }
}
