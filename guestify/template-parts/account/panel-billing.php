<?php
/**
 * Template Part: Account Billing Panel
 *
 * Displays the billing & plan settings panel with Credits & Usage from plugins.
 *
 * @package Guestify
 * @version 1.0.0
 *
 * @param array $args['billing_data'] Billing data array
 * @param array $args['usage_data'] Usage data array
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$billing_data = isset($args['billing_data']) ? $args['billing_data'] : array();
$usage_data = isset($args['usage_data']) ? $args['usage_data'] : array();

// Get membership info
$membership_name = $billing_data['membership_name'] ?? 'Free Plan';
$subscription_date = $billing_data['subscription_date'] ?? '';
$renewal_date = $billing_data['renewal_date'] ?? '';
$search_cap = $billing_data['search_cap'] ?? 50;
$payment_method = $billing_data['payment_method'] ?? null;
$invoices = $billing_data['invoices'] ?? array();

// Get usage data
$ai_credits_used = $usage_data['ai_credits']['used'] ?? 0;
$ai_credits_total = $usage_data['ai_credits']['total'] ?? 500;
$ai_credits_percent = $ai_credits_total > 0 ? round(($ai_credits_total - $ai_credits_used) / $ai_credits_total * 100) : 0;

$searches_used = $usage_data['prospector']['used'] ?? 0;
$searches_total = $usage_data['prospector']['total'] ?? $search_cap;
$searches_remaining = $searches_total - $searches_used;
$searches_percent = $searches_total > 0 ? round($searches_remaining / $searches_total * 100) : 0;

$outreach_used = $usage_data['outreach']['used'] ?? 0;
$outreach_total = $usage_data['outreach']['total'] ?? 300;
$outreach_remaining = $outreach_total - $outreach_used;
$outreach_percent = $outreach_total > 0 ? round($outreach_remaining / $outreach_total * 100) : 0;

// Activity this month
$ai_generations = $usage_data['activity']['ai_generations'] ?? 0;
$podcast_searches = $usage_data['activity']['podcast_searches'] ?? 0;
$emails_sent = $usage_data['activity']['emails_sent'] ?? 0;

// Resets date
$resets_date = $usage_data['resets_date'] ?? date('M j', strtotime('last day of this month'));
?>

<div id="billing" class="gfy-panel" role="tabpanel">
    <!-- Subscription Card -->
    <div class="gfy-card">
        <div class="gfy-card__header">
            <div>
                <h2 class="gfy-card__title"><?php esc_html_e('Subscription', 'guestify'); ?></h2>
                <p class="gfy-card__desc"><?php esc_html_e('Manage your Guestify workspace plan.', 'guestify'); ?></p>
            </div>
            <span class="gfy-badge gfy-badge--primary"><?php echo esc_html($membership_name); ?></span>
        </div>
        <div class="gfy-card__body">
            <!-- Plan Details Grid -->
            <div class="gfy-plan-details">
                <div class="gfy-plan-details__row">
                    <span class="gfy-plan-details__label"><?php esc_html_e('Membership', 'guestify'); ?></span>
                    <span class="gfy-plan-details__value"><?php echo esc_html($membership_name); ?></span>
                </div>
                <?php if ($subscription_date): ?>
                <div class="gfy-plan-details__row">
                    <span class="gfy-plan-details__label"><?php esc_html_e('Subscription Date', 'guestify'); ?></span>
                    <span class="gfy-plan-details__value"><?php echo esc_html($subscription_date); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($renewal_date): ?>
                <div class="gfy-plan-details__row">
                    <span class="gfy-plan-details__label"><?php esc_html_e('Renewal Date', 'guestify'); ?></span>
                    <span class="gfy-plan-details__value"><?php echo esc_html($renewal_date); ?></span>
                </div>
                <?php endif; ?>
                <div class="gfy-plan-details__row">
                    <span class="gfy-plan-details__label"><?php esc_html_e('Prospector Search Cap', 'guestify'); ?></span>
                    <span class="gfy-plan-details__value"><?php echo esc_html(number_format($search_cap)); ?> / <?php esc_html_e('month', 'guestify'); ?></span>
                </div>
            </div>

            <div class="gfy-btn-group">
                <a href="<?php echo esc_url(home_url('/app/upgrade/')); ?>" class="gfy-btn gfy-btn--primary">
                    <i class="fa-solid fa-arrow-up"></i>
                    <?php esc_html_e('Upgrade Plan', 'guestify'); ?>
                </a>
                <a href="<?php echo esc_url(home_url('/pricing/')); ?>" class="gfy-btn gfy-btn--secondary">
                    <i class="fa-solid fa-scale-balanced"></i>
                    <?php esc_html_e('Compare Plans', 'guestify'); ?>
                </a>
                <?php if (function_exists('pmpro_hasMembershipLevel') && pmpro_hasMembershipLevel()): ?>
                <a href="<?php echo esc_url(pmpro_url('account')); ?>" class="gfy-btn gfy-btn--ghost">
                    <?php esc_html_e('Manage Subscription', 'guestify'); ?>
                </a>
                <?php endif; ?>
            </div>

            <hr class="gfy-divider">

            <h4 style="font-weight: var(--gfy-font-semibold); margin-bottom: var(--gfy-space-4);"><?php esc_html_e('Payment Method', 'guestify'); ?></h4>
            <?php if ($payment_method): ?>
            <div class="gfy-payment-method">
                <div class="gfy-payment-method__info">
                    <div class="gfy-payment-method__icon">
                        <i class="fa-brands fa-cc-<?php echo esc_attr(strtolower($payment_method['brand'] ?? 'visa')); ?> fa-lg"></i>
                    </div>
                    <span><?php echo esc_html($payment_method['brand'] ?? 'Card'); ?> <?php esc_html_e('ending in', 'guestify'); ?> <strong><?php echo esc_html($payment_method['last4'] ?? '****'); ?></strong></span>
                </div>
                <button class="gfy-btn gfy-btn--secondary gfy-btn--sm"><?php esc_html_e('Edit', 'guestify'); ?></button>
            </div>
            <?php else: ?>
            <div class="gfy-empty-state" style="padding: var(--gfy-space-4);">
                <p><?php esc_html_e('No payment method on file', 'guestify'); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Credits & Usage Card -->
    <div class="gfy-card">
        <div class="gfy-card__header">
            <h2 class="gfy-card__title"><?php esc_html_e('Credits & Usage', 'guestify'); ?></h2>
            <span class="gfy-badge gfy-badge--info"><?php printf(esc_html__('Resets %s', 'guestify'), $resets_date); ?></span>
        </div>
        <div class="gfy-card__body">
            <div class="gfy-credits-grid">
                <!-- AI Credits -->
                <div class="gfy-credit-card">
                    <div class="gfy-credit-card__icon gfy-credit-card__icon--ai">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                    </div>
                    <div class="gfy-credit-card__content">
                        <div class="gfy-credit-card__label"><?php esc_html_e('AI Credits', 'guestify'); ?></div>
                        <div class="gfy-credit-card__value">
                            <?php echo esc_html($ai_credits_total - $ai_credits_used); ?>
                            <span class="gfy-credit-card__total">/ <?php echo esc_html($ai_credits_total); ?></span>
                        </div>
                        <div class="gfy-progress">
                            <div class="gfy-progress__bar gfy-progress__bar--purple" style="width: <?php echo esc_attr($ai_credits_percent); ?>%;"></div>
                        </div>
                    </div>
                </div>

                <!-- Prospector Searches -->
                <div class="gfy-credit-card">
                    <div class="gfy-credit-card__icon gfy-credit-card__icon--search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <div class="gfy-credit-card__content">
                        <div class="gfy-credit-card__label"><?php esc_html_e('Prospector Searches', 'guestify'); ?></div>
                        <div class="gfy-credit-card__value">
                            <?php echo esc_html($searches_remaining); ?>
                            <span class="gfy-credit-card__total">/ <?php echo esc_html($searches_total); ?></span>
                        </div>
                        <div class="gfy-credit-card__subtext"><?php printf(esc_html__('%d used this period', 'guestify'), $searches_used); ?></div>
                        <div class="gfy-progress">
                            <div class="gfy-progress__bar gfy-progress__bar--blue" style="width: <?php echo esc_attr($searches_percent); ?>%;"></div>
                        </div>
                    </div>
                </div>

                <!-- Outreach Sends -->
                <div class="gfy-credit-card">
                    <div class="gfy-credit-card__icon gfy-credit-card__icon--outreach">
                        <i class="fa-solid fa-paper-plane"></i>
                    </div>
                    <div class="gfy-credit-card__content">
                        <div class="gfy-credit-card__label"><?php esc_html_e('Outreach Sends', 'guestify'); ?></div>
                        <div class="gfy-credit-card__value">
                            <?php echo esc_html($outreach_remaining); ?>
                            <span class="gfy-credit-card__total">/ <?php echo esc_html($outreach_total); ?></span>
                        </div>
                        <div class="gfy-progress">
                            <div class="gfy-progress__bar gfy-progress__bar--orange" style="width: <?php echo esc_attr($outreach_percent); ?>%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="gfy-divider">

            <h4 style="font-weight: var(--gfy-font-semibold); margin-bottom: var(--gfy-space-4);"><?php esc_html_e("This Month's Activity", 'guestify'); ?></h4>
            <div class="gfy-usage-list">
                <div class="gfy-usage-row">
                    <div class="gfy-usage-row__label">
                        <i class="fa-solid fa-robot" style="color: #805AD5;"></i>
                        <?php esc_html_e('AI Message Generations', 'guestify'); ?>
                    </div>
                    <div class="gfy-usage-row__value"><?php printf(esc_html__('%d used', 'guestify'), $ai_generations); ?></div>
                </div>
                <div class="gfy-usage-row">
                    <div class="gfy-usage-row__label">
                        <i class="fa-solid fa-podcast" style="color: #3b82f6;"></i>
                        <?php esc_html_e('Podcast Searches', 'guestify'); ?>
                    </div>
                    <div class="gfy-usage-row__value"><?php printf(esc_html__('%d searches', 'guestify'), $podcast_searches); ?></div>
                </div>
                <div class="gfy-usage-row">
                    <div class="gfy-usage-row__label">
                        <i class="fa-solid fa-envelope" style="color: #ED8936;"></i>
                        <?php esc_html_e('Emails Sent', 'guestify'); ?>
                    </div>
                    <div class="gfy-usage-row__value"><?php printf(esc_html__('%d sent', 'guestify'), $emails_sent); ?></div>
                </div>
            </div>

            <div style="text-align: center; margin-top: var(--gfy-space-4);">
                <a href="<?php echo esc_url(home_url('/account/?panel=usage-report')); ?>" class="gfy-btn gfy-btn--secondary gfy-btn--sm">
                    <i class="fa-solid fa-chart-line"></i>
                    <?php esc_html_e('View Full Usage Report', 'guestify'); ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Invoice History Card -->
    <div class="gfy-card">
        <div class="gfy-card__header">
            <h2 class="gfy-card__title"><?php esc_html_e('Invoice History', 'guestify'); ?></h2>
        </div>
        <div class="gfy-card__body" style="padding: 0;">
            <?php if (!empty($invoices)): ?>
            <table class="gfy-table">
                <thead>
                    <tr>
                        <th class="gfy-table__th"><?php esc_html_e('Date', 'guestify'); ?></th>
                        <th class="gfy-table__th"><?php esc_html_e('Amount', 'guestify'); ?></th>
                        <th class="gfy-table__th"><?php esc_html_e('Status', 'guestify'); ?></th>
                        <th class="gfy-table__th"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $invoice): ?>
                    <tr class="gfy-table__tr">
                        <td class="gfy-table__td"><?php echo esc_html($invoice['date']); ?></td>
                        <td class="gfy-table__td"><?php echo esc_html($invoice['amount']); ?></td>
                        <td class="gfy-table__td">
                            <?php if ($invoice['status'] === 'paid'): ?>
                            <span style="color: var(--gfy-success); font-weight: var(--gfy-font-medium);">
                                <i class="fa-solid fa-check-circle"></i> <?php esc_html_e('Paid', 'guestify'); ?>
                            </span>
                            <?php else: ?>
                            <span style="color: var(--gfy-warning); font-weight: var(--gfy-font-medium);">
                                <?php echo esc_html(ucfirst($invoice['status'])); ?>
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="gfy-table__td" style="text-align: right;">
                            <?php if (!empty($invoice['pdf_url'])): ?>
                            <a href="<?php echo esc_url($invoice['pdf_url']); ?>" class="gfy-table__link">
                                <i class="fa-solid fa-download"></i>
                                <?php esc_html_e('PDF', 'guestify'); ?>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="gfy-empty-state">
                <div class="gfy-empty-state__icon">
                    <i class="fa-solid fa-file-invoice"></i>
                </div>
                <h3 class="gfy-empty-state__title"><?php esc_html_e('No Invoices Yet', 'guestify'); ?></h3>
                <p class="gfy-empty-state__desc"><?php esc_html_e('Your invoice history will appear here once you make a payment.', 'guestify'); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Downgrade Link -->
    <div style="padding: var(--gfy-space-6) 0;">
        <a href="<?php echo esc_url(home_url('/app/downgrade/')); ?>" class="gfy-link--muted">
            <?php esc_html_e('Downgrade to free plan', 'guestify'); ?>
        </a>
    </div>
</div>
