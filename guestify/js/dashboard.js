/**
 * Guestify Performance Dashboard JavaScript
 *
 * Handles interactive functionality for the dashboard.
 *
 * @package Guestify
 * @version 1.0.0
 */

(function() {
    'use strict';

    /**
     * GuestifyDashboard Controller
     */
    class GuestifyDashboard {
        constructor() {
            this.apiBase = '/wp-json/guestify/v1/dashboard';
            this.nonce = typeof gfyDashboardData !== 'undefined' ? gfyDashboardData.nonce : '';
            this.currentGoal = 'revenue';
            this.currentPeriod = '30days';
            this.init();
        }

        /**
         * Initialize the dashboard
         */
        init() {
            this.bindEvents();
            this.initFromUrl();
        }

        /**
         * Initialize state from URL parameters
         */
        initFromUrl() {
            const urlParams = new URLSearchParams(window.location.search);

            if (urlParams.has('goal')) {
                this.currentGoal = urlParams.get('goal');
            }

            if (urlParams.has('period')) {
                this.currentPeriod = urlParams.get('period');
            }

            // Update UI to match URL state
            this.highlightOutcomeCard(this.currentGoal);
        }

        /**
         * Bind event listeners
         */
        bindEvents() {
            // Goal toggle buttons
            document.querySelectorAll('.gfy-goal-btn').forEach(btn => {
                btn.addEventListener('click', (e) => this.handleGoalChange(e));
            });

            // Time period selector
            const timeSelect = document.getElementById('timePeriodSelect');
            if (timeSelect) {
                timeSelect.addEventListener('change', (e) => this.handlePeriodChange(e));
            }

            // Pipeline cards (actionable items)
            document.querySelectorAll('.gfy-pipe-card.actionable').forEach(card => {
                card.addEventListener('click', (e) => this.handlePipelineClick(e));
            });
        }

        /**
         * Handle goal toggle change
         */
        handleGoalChange(e) {
            const btn = e.currentTarget;
            const goal = btn.dataset.goal;

            // Update button states
            document.querySelectorAll('.gfy-goal-btn').forEach(b => {
                b.classList.remove('active');
            });
            btn.classList.add('active');

            this.currentGoal = goal;

            // Highlight corresponding outcome card
            this.highlightOutcomeCard(goal);

            // Update journey CTA
            this.updateJourneyCta(goal);

            // Save preference
            this.saveGoalPreference(goal);

            // Update URL without reload
            this.updateUrl();
        }

        /**
         * Highlight the outcome card for selected goal
         */
        highlightOutcomeCard(goal) {
            // Reset all outcome cards
            document.querySelectorAll('.gfy-outcome-card').forEach(card => {
                card.classList.remove('highlight');
            });

            // Highlight the relevant card
            const cardMap = {
                'authority': 'card-authority',
                'revenue': 'card-revenue',
                'launch': 'card-launch'
            };

            const cardId = cardMap[goal];
            if (cardId) {
                const card = document.getElementById(cardId);
                if (card) {
                    card.classList.add('highlight');
                }
            }
        }

        /**
         * Update journey CTA based on goal
         */
        updateJourneyCta(goal) {
            const ctaEl = document.getElementById('journeyCta');
            if (!ctaEl) return;

            const ctaMap = {
                'authority': {
                    icon: 'fa-podcast',
                    text: 'Find High-Authority Shows'
                },
                'revenue': {
                    icon: 'fa-paper-plane',
                    text: 'Send Pitches'
                },
                'launch': {
                    icon: 'fa-rocket',
                    text: 'Plan Launch Campaign'
                }
            };

            const cta = ctaMap[goal] || ctaMap['revenue'];
            ctaEl.innerHTML = `<i class="fa-solid ${cta.icon}"></i> ${cta.text}`;
        }

        /**
         * Handle time period change
         */
        handlePeriodChange(e) {
            this.currentPeriod = e.target.value;
            this.updateUrl();
            this.refreshDashboardData();
        }

        /**
         * Handle click on pipeline card
         */
        handlePipelineClick(e) {
            const card = e.currentTarget;
            const step = card.dataset.step;

            // Navigate to appropriate section based on step
            const stepUrls = {
                'discovery': '/app/prospector/',
                'intel': '/app/crm/',
                'action': '/app/outreach/',
                'result': '/app/interviews/',
                'success': '/app/appearances/'
            };

            if (stepUrls[step]) {
                window.location.href = stepUrls[step];
            }
        }

        /**
         * Update URL with current state
         */
        updateUrl() {
            const url = new URL(window.location);
            url.searchParams.set('goal', this.currentGoal);
            url.searchParams.set('period', this.currentPeriod);
            window.history.replaceState({}, '', url);
        }

        /**
         * Save goal preference to server
         */
        async saveGoalPreference(goal) {
            try {
                await fetch(`${this.apiBase}/goal`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': this.nonce
                    },
                    body: JSON.stringify({ goal: goal })
                });
            } catch (error) {
                // Silent fail - preference saving is not critical
                console.log('Failed to save goal preference');
            }
        }

        /**
         * Refresh dashboard data from server
         */
        async refreshDashboardData() {
            try {
                // Show loading state
                this.setLoadingState(true);

                const response = await fetch(
                    `${this.apiBase}/data?period=${this.currentPeriod}&goal=${this.currentGoal}`,
                    {
                        headers: {
                            'X-WP-Nonce': this.nonce
                        }
                    }
                );

                const data = await response.json();

                if (data.success) {
                    this.updatePipelineCards(data.pipeline);
                    this.updateOutcomeCards(data.outcomes);
                    this.updateAttributionTable(data.attribution);
                    this.updateInsightBanner(data.insight);
                }
            } catch (error) {
                console.error('Failed to refresh dashboard data:', error);
            } finally {
                this.setLoadingState(false);
            }
        }

        /**
         * Set loading state on dashboard elements
         */
        setLoadingState(loading) {
            const elements = document.querySelectorAll('.gfy-pipe-val, .gfy-outcome-val');
            elements.forEach(el => {
                if (loading) {
                    el.style.opacity = '0.5';
                } else {
                    el.style.opacity = '1';
                }
            });
        }

        /**
         * Update pipeline cards with new data
         */
        updatePipelineCards(data) {
            if (!data) return;

            const cardUpdates = {
                'discovery': { val: data.shows_found, label: 'Shows Found' },
                'intel': { val: data.shows_researched, label: 'Shows Researched' },
                'action': { val: data.pitches_sent, label: 'Pitches Sent' },
                'result': { val: data.interviews_booked, label: 'Interviews Booked' },
                'success': { val: data.episodes_aired, label: 'Episodes Aired' }
            };

            Object.entries(cardUpdates).forEach(([step, update]) => {
                const card = document.querySelector(`.gfy-pipe-card[data-step="${step}"]`);
                if (card) {
                    const valEl = card.querySelector('.gfy-pipe-val');
                    if (valEl) {
                        valEl.textContent = this.formatNumber(update.val);
                    }
                }
            });

            // Update rate badges
            if (data.rates) {
                Object.entries(data.rates).forEach(([step, rate]) => {
                    const card = document.querySelector(`.gfy-pipe-card[data-step="${step}"]`);
                    if (card) {
                        const badge = card.querySelector('.gfy-rate-badge');
                        if (badge) {
                            badge.textContent = rate;
                        }
                    }
                });
            }
        }

        /**
         * Update outcome cards with new data
         */
        updateOutcomeCards(data) {
            if (!data) return;

            const updates = {
                'card-revenue': {
                    val: this.formatCurrency(data.revenue),
                    sub: data.revenue_change
                },
                'card-authority': {
                    val: this.formatAudience(data.audience),
                    sub: 'Total Audience'
                },
                'card-launch': {
                    val: data.partners,
                    sub: 'Active Partners'
                }
            };

            Object.entries(updates).forEach(([cardId, update]) => {
                const card = document.getElementById(cardId);
                if (card) {
                    const valEl = card.querySelector('.gfy-outcome-val');
                    const subEl = card.querySelector('.gfy-outcome-sub');

                    if (valEl) valEl.textContent = update.val;
                    if (subEl) subEl.textContent = update.sub;
                }
            });
        }

        /**
         * Update attribution table with new data
         */
        updateAttributionTable(data) {
            if (!data || !data.length) return;

            const tbody = document.querySelector('.gfy-table tbody');
            if (!tbody) return;

            tbody.innerHTML = data.map(row => `
                <tr>
                    <td>
                        <div class="gfy-table__name">${this.escapeHtml(row.name)}</div>
                        <span class="gfy-link-cell">${this.escapeHtml(row.link)}</span>
                    </td>
                    <td>${this.formatNumber(row.clicks)}</td>
                    <td>${this.formatNumber(row.leads)}</td>
                    <td class="gfy-table__revenue">${this.formatCurrency(row.revenue)}</td>
                </tr>
            `).join('');
        }

        /**
         * Update insight banner with new data
         */
        updateInsightBanner(insight) {
            const banner = document.querySelector('.gfy-insight-text');
            if (banner && insight) {
                banner.innerHTML = insight;
            }
        }

        /**
         * Format number with commas
         */
        formatNumber(num) {
            if (num === null || num === undefined) return '0';
            return num.toLocaleString();
        }

        /**
         * Format currency
         */
        formatCurrency(amount) {
            if (amount === null || amount === undefined) return '$0';
            if (amount >= 1000) {
                return '$' + (amount / 1000).toFixed(1) + 'k';
            }
            return '$' + amount.toLocaleString();
        }

        /**
         * Format audience numbers
         */
        formatAudience(num) {
            if (num === null || num === undefined) return '0';
            if (num >= 1000000) {
                return (num / 1000000).toFixed(1) + 'M';
            }
            if (num >= 1000) {
                return (num / 1000).toFixed(0) + 'K';
            }
            return num.toLocaleString();
        }

        /**
         * Escape HTML to prevent XSS
         */
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => new GuestifyDashboard());
    } else {
        new GuestifyDashboard();
    }
})();
