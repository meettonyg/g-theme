<?php
/**
 * Template Part: Account Credits Panel
 *
 * Displays the Authority Credits dashboard with balance gauge,
 * breakdown, usage, transaction feed, action costs, and purchase options.
 * Uses vanilla JS to fetch live data from the Guestify REST API.
 *
 * @package Guestify
 * @version 1.1.0
 *
 * @param array $args['user_id']      Current user ID
 * @param array $args['billing_data'] Billing data array
 * @param array $args['usage_data']   Usage data array
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$user_id = isset($args['user_id']) ? $args['user_id'] : get_current_user_id();

// Try to get initial balance data from the credit repository (server-side)
$initial_balance = null;
$initial_tier = null;
if (class_exists('PIT_Credit_Repository')) {
    $balance_data = PIT_Credit_Repository::get_balance($user_id);
    if ($balance_data && !is_wp_error($balance_data)) {
        $initial_balance = $balance_data;
    }
}
if (class_exists('PIT_Guestify_Tier_Resolver')) {
    $tier_data = PIT_Guestify_Tier_Resolver::get_user_tier($user_id);
    if ($tier_data && !is_wp_error($tier_data)) {
        $initial_tier = $tier_data;
    }
}

// Compute initial gauge values
$monthly_allowance = $initial_balance['monthly_allowance'] ?? 0;
$allowance = $initial_balance['allowance'] ?? 0;
$rollover = $initial_balance['rollover'] ?? 0;
$overage = $initial_balance['overage'] ?? 0;
$total_balance = $initial_balance['total'] ?? ($allowance + $rollover + $overage);
$hard_cap = $initial_balance['hard_cap'] ?? 0;
$billing_cycle_end = $initial_balance['billing_cycle_end'] ?? '';
$billing_period = $initial_balance['billing_period'] ?? 'monthly';

$used = $monthly_allowance > 0 ? $monthly_allowance - $allowance : 0;
$percent_used = $monthly_allowance > 0 ? min(100, round(($used / $monthly_allowance) * 100)) : 0;

// Gauge color
$gauge_color = '#10b981'; // green
if ($percent_used >= 100) {
    $gauge_color = '#ef4444'; // red
} elseif ($percent_used >= 80) {
    $gauge_color = '#f59e0b'; // amber
}

// Gauge SVG math
$radius = 52;
$circumference = 2 * 3.14159 * $radius; // ~326.7
$dash_offset = $circumference - ($circumference * min($percent_used, 100) / 100);

// Tier display name
$tier_name = '';
$tier_key = '';
if ($initial_tier) {
    $tier_key = is_array($initial_tier) ? ($initial_tier['tier'] ?? '') : (string) $initial_tier;
    $tier_names = [
        'accelerator' => 'Accelerator',
        'velocity'    => 'Velocity',
        'zenith'      => 'Zenith',
        'free'        => 'Free',
    ];
    $tier_name = $tier_names[$tier_key] ?? ucfirst($tier_key);
}

// Cycle refill date
$refill_date = '';
if ($billing_cycle_end) {
    $refill_date = date_i18n('M j, Y', strtotime($billing_cycle_end));
}
?>

<div id="credits" class="gfy-panel" role="tabpanel">

    <!-- Balance & Gauge Card -->
    <div class="gfy-card">
        <div class="gfy-card__header">
            <div>
                <h2 class="gfy-card__title"><?php esc_html_e('Authority Credits', 'guestify'); ?></h2>
                <p class="gfy-card__desc"><?php esc_html_e('Your credit balance and usage for the current billing cycle.', 'guestify'); ?></p>
            </div>
            <?php if ($tier_name): ?>
            <span class="gfy-badge gfy-badge--tier gfy-badge--tier-<?php echo esc_attr($tier_key); ?>"><?php echo esc_html($tier_name); ?></span>
            <?php endif; ?>
        </div>
        <div class="gfy-card__body">
            <div class="gfy-credit-gauge-layout">
                <!-- SVG Gauge -->
                <div class="gfy-credit-gauge" id="credit-gauge">
                    <svg viewBox="0 0 120 120" class="gfy-credit-gauge__svg" width="160" height="160">
                        <circle cx="60" cy="60" r="<?php echo esc_attr($radius); ?>"
                                fill="none" stroke="var(--gfy-gray-200, #e5e7eb)" stroke-width="8" />
                        <circle cx="60" cy="60" r="<?php echo esc_attr($radius); ?>"
                                fill="none"
                                stroke="<?php echo esc_attr($gauge_color); ?>"
                                stroke-width="8"
                                stroke-linecap="round"
                                stroke-dasharray="<?php echo esc_attr(round($circumference, 1)); ?>"
                                stroke-dashoffset="<?php echo esc_attr(round($dash_offset, 1)); ?>"
                                transform="rotate(-90 60 60)"
                                id="gauge-arc" />
                    </svg>
                    <div class="gfy-credit-gauge__center">
                        <span class="gfy-credit-gauge__value" id="gauge-total"><?php echo esc_html(number_format($total_balance)); ?></span>
                        <span class="gfy-credit-gauge__label"><?php esc_html_e('credits', 'guestify'); ?></span>
                    </div>
                </div>

                <!-- Balance Breakdown -->
                <div class="gfy-credit-breakdown">
                    <div class="gfy-credit-breakdown__row">
                        <span class="gfy-credit-breakdown__dot gfy-credit-breakdown__dot--allowance"></span>
                        <span class="gfy-credit-breakdown__label"><?php esc_html_e('Monthly Allowance', 'guestify'); ?></span>
                        <span class="gfy-credit-breakdown__value" id="breakdown-allowance"><?php echo esc_html(number_format($allowance)); ?></span>
                    </div>
                    <div class="gfy-credit-breakdown__row" id="row-rollover" style="<?php echo $rollover <= 0 ? 'display:none;' : ''; ?>">
                        <span class="gfy-credit-breakdown__dot gfy-credit-breakdown__dot--rollover"></span>
                        <span class="gfy-credit-breakdown__label"><?php esc_html_e('Rolled Over', 'guestify'); ?></span>
                        <span class="gfy-credit-breakdown__value" id="breakdown-rollover"><?php echo esc_html(number_format($rollover)); ?></span>
                    </div>
                    <div class="gfy-credit-breakdown__row" id="row-overage" style="<?php echo $overage <= 0 ? 'display:none;' : ''; ?>">
                        <span class="gfy-credit-breakdown__dot gfy-credit-breakdown__dot--overage"></span>
                        <span class="gfy-credit-breakdown__label"><?php esc_html_e('Purchased', 'guestify'); ?></span>
                        <span class="gfy-credit-breakdown__value" id="breakdown-overage"><?php echo esc_html(number_format($overage)); ?></span>
                    </div>
                    <hr class="gfy-divider" style="margin: var(--gfy-space-3, 0.75rem) 0;">
                    <div class="gfy-credit-breakdown__row gfy-credit-breakdown__row--total">
                        <span class="gfy-credit-breakdown__label"><?php esc_html_e('Total Available', 'guestify'); ?></span>
                        <span class="gfy-credit-breakdown__value" id="breakdown-total"><strong><?php echo esc_html(number_format($total_balance)); ?></strong> / <?php echo esc_html(number_format($monthly_allowance)); ?></span>
                    </div>
                    <?php if ($refill_date): ?>
                    <div class="gfy-credit-breakdown__cycle">
                        <i class="fa-solid fa-calendar-day"></i>
                        <?php printf(esc_html__('Refills on %s', 'guestify'), '<strong>' . esc_html($refill_date) . '</strong>'); ?>
                        <span class="gfy-badge gfy-badge--sm gfy-badge--info"><?php echo esc_html(ucfirst($billing_period)); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Upgrade Prompt (hidden by default, shown by JS when low) -->
            <div class="gfy-credit-upgrade" id="credit-upgrade-prompt" style="<?php echo $percent_used < 80 ? 'display:none;' : ''; ?>"
                 data-level="<?php echo $percent_used >= 100 ? 'danger' : ($percent_used >= 80 ? 'warning' : ''); ?>">
                <div class="gfy-credit-upgrade__content">
                    <i class="fa-solid <?php echo $percent_used >= 100 ? 'fa-circle-exclamation' : 'fa-triangle-exclamation'; ?>"></i>
                    <p id="credit-upgrade-message">
                        <?php
                        if ($total_balance <= 0 && $hard_cap > 0) {
                            esc_html_e('You have reached your credit limit for this billing cycle.', 'guestify');
                        } elseif ($percent_used >= 100) {
                            esc_html_e('Your monthly allowance is used up. Purchase additional credits or upgrade your plan.', 'guestify');
                        } elseif ($percent_used >= 80) {
                            esc_html_e("You're running low on credits. Consider upgrading your plan.", 'guestify');
                        }
                        ?>
                    </p>
                </div>
                <a href="<?php echo esc_url(home_url('/pricing/')); ?>" class="gfy-btn gfy-btn--primary gfy-btn--sm">
                    <i class="fa-solid fa-arrow-up"></i>
                    <?php esc_html_e('Upgrade Plan', 'guestify'); ?>
                </a>
            </div>
        </div>
    </div>

    <!-- This Cycle Usage Card -->
    <div class="gfy-card" id="credit-usage-card">
        <div class="gfy-card__header">
            <h2 class="gfy-card__title"><?php esc_html_e('This Cycle', 'guestify'); ?></h2>
        </div>
        <div class="gfy-card__body">
            <div class="gfy-credit-usage-stats" id="credit-usage-stats">
                <div class="gfy-credit-usage-stat">
                    <div class="gfy-credit-usage-stat__icon gfy-credit-usage-stat__icon--spent">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <div>
                        <span class="gfy-credit-usage-stat__value" id="usage-spent">--</span>
                        <span class="gfy-credit-usage-stat__label"><?php esc_html_e('Credits Used', 'guestify'); ?></span>
                    </div>
                </div>
                <div class="gfy-credit-usage-stat">
                    <div class="gfy-credit-usage-stat__icon gfy-credit-usage-stat__icon--actions">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                    </div>
                    <div>
                        <span class="gfy-credit-usage-stat__value" id="usage-actions">--</span>
                        <span class="gfy-credit-usage-stat__label"><?php esc_html_e('Actions Performed', 'guestify'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Costs Card -->
    <div class="gfy-card" id="credit-actions-card">
        <div class="gfy-card__header">
            <h2 class="gfy-card__title"><?php esc_html_e('Credit Costs', 'guestify'); ?></h2>
            <p class="gfy-card__desc"><?php esc_html_e('How many credits each action uses.', 'guestify'); ?></p>
        </div>
        <div class="gfy-card__body" style="padding: 0;">
            <div id="credit-actions-list" class="gfy-credit-actions-list">
                <div class="gfy-credit-actions-loading">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    <?php esc_html_e('Loading action costs...', 'guestify'); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity / Transactions Card -->
    <div class="gfy-card" id="credit-transactions-card">
        <div class="gfy-card__header">
            <h2 class="gfy-card__title"><?php esc_html_e('Recent Activity', 'guestify'); ?></h2>
        </div>
        <div class="gfy-card__body" style="padding: 0;">
            <div id="credit-transactions-list">
                <div class="gfy-credit-actions-loading">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    <?php esc_html_e('Loading transactions...', 'guestify'); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Buy More Credits Card (conditional on Stripe config) -->
    <div class="gfy-card" id="credit-packs-card" style="display: none;">
        <div class="gfy-card__header">
            <h2 class="gfy-card__title"><?php esc_html_e('Buy More Credits', 'guestify'); ?></h2>
            <p class="gfy-card__desc"><?php esc_html_e('Purchase additional credit packs for your account.', 'guestify'); ?></p>
        </div>
        <div class="gfy-card__body">
            <div class="gfy-credit-packs" id="credit-packs-grid">
                <!-- Populated by JS -->
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="gfy-card">
        <div class="gfy-card__body">
            <div class="gfy-btn-group" style="justify-content: center;">
                <a href="<?php echo esc_url(home_url('/pricing/')); ?>" class="gfy-btn gfy-btn--primary">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <?php esc_html_e('Compare Plans', 'guestify'); ?>
                </a>
                <a href="<?php echo esc_url(add_query_arg('panel', 'billing', get_permalink())); ?>" class="gfy-btn gfy-btn--secondary">
                    <i class="fa-solid fa-credit-card"></i>
                    <?php esc_html_e('Billing & Plan', 'guestify'); ?>
                </a>
            </div>
        </div>
    </div>

</div>
