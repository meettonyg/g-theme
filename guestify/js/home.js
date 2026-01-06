/**
 * Guestify Home Page JavaScript
 *
 * Handles dynamic data loading, goal selection, and interactivity
 * for the unified home page dashboard.
 *
 * @package Guestify
 * @version 1.0.0
 */

(function() {
    'use strict';

    /**
     * GuestifyHome Class
     * Main controller for the home page functionality
     */
    class GuestifyHome {
        constructor() {
            // API configuration from WordPress
            this.config = window.guestifyHomeConfig || {};
            this.apiBase = this.config.apiUrl || '/wp-json/guestify/v1/';
            this.nonce = this.config.nonce || '';

            // State
            this.currentGoal = this.config.currentGoal || 'grow_revenue';
            this.dashboardData = null;
            this.isLoading = false;

            // Initialize when DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.init());
            } else {
                this.init();
            }
        }

        /**
         * Initialize the home page
         */
        init() {
            this.bindEvents();
            this.fetchDashboardData();
            this.initGoalSelector();
            this.updateGreeting();
            this.updateDate();
        }

        /**
         * Bind event listeners
         */
        bindEvents() {
            // Goal selector buttons
            const goalButtons = document.querySelectorAll('.gfy-home__goal-btn');
            goalButtons.forEach(btn => {
                btn.addEventListener('click', (e) => this.handleGoalSelect(e));
            });

            // Pillar card clicks
            const pillarCards = document.querySelectorAll('.gfy-home__pillar');
            pillarCards.forEach(card => {
                card.addEventListener('click', (e) => this.handlePillarClick(e));
            });

            // Recent activity item clicks
            const recentItems = document.querySelectorAll('.gfy-home__recent-item');
            recentItems.forEach(item => {
                item.addEventListener('click', (e) => this.handleRecentClick(e));
            });

            // Refresh data every 60 seconds
            setInterval(() => this.fetchDashboardData(), 60000);
        }

        /**
         * Fetch dashboard data from REST API
         */
        async fetchDashboardData() {
            if (this.isLoading) return;

            this.isLoading = true;
            this.showLoadingState();

            try {
                const response = await fetch(this.apiBase + 'home/dashboard', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': this.nonce
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                this.dashboardData = data;
                this.updateUI(data);

            } catch (error) {
                console.error('Guestify Home: Error fetching dashboard data:', error);
                this.showErrorState();
            } finally {
                this.isLoading = false;
                this.hideLoadingState();
            }
        }

        /**
         * Update the UI with fetched data
         */
        updateUI(data) {
            if (!data) return;

            // Update quick stats
            if (data.stats) {
                this.updateStats(data.stats);
            }

            // Update pillar statuses
            if (data.pillars) {
                this.updatePillars(data.pillars);
            }

            // Update recent activity
            if (data.recent_activity) {
                this.updateRecentActivity(data.recent_activity);
            }

            // Update tasks badge
            if (data.tasks_due !== undefined) {
                this.updateTasksBadge(data.tasks_due);
            }
        }

        /**
         * Update quick stats section
         */
        updateStats(stats) {
            const statMap = {
                'pitches': '[data-stat="pitches"]',
                'interviews': '[data-stat="interviews"]',
                'episodes': '[data-stat="episodes"]',
                'revenue': '[data-stat="revenue"]'
            };

            Object.keys(statMap).forEach(key => {
                const el = document.querySelector(statMap[key] + ' .gfy-home__stat-value');
                if (el && stats[key] !== undefined) {
                    el.textContent = this.formatStatValue(key, stats[key]);
                    el.classList.remove('gfy-skeleton');
                }
            });
        }

        /**
         * Format stat values appropriately
         */
        formatStatValue(key, value) {
            if (key === 'revenue') {
                return '$' + this.formatNumber(value, true);
            }
            return this.formatNumber(value);
        }

        /**
         * Format number with abbreviations
         */
        formatNumber(num, isCurrency = false) {
            if (num === null || num === undefined) return '0';

            const n = parseFloat(num);
            if (isNaN(n)) return '0';

            if (n >= 1000000) {
                return (n / 1000000).toFixed(1) + 'M';
            }
            if (n >= 1000) {
                return (n / 1000).toFixed(1) + 'K';
            }
            return isCurrency ? n.toFixed(0) : n.toString();
        }

        /**
         * Update pillar card statuses
         */
        updatePillars(pillars) {
            Object.keys(pillars).forEach(pillarKey => {
                const pillarData = pillars[pillarKey];
                const pillarEl = document.querySelector(`[data-pillar="${pillarKey}"]`);

                if (pillarEl && pillarData) {
                    // Update status text
                    const statusEl = pillarEl.querySelector('.gfy-home__pillar-status');
                    if (statusEl && pillarData.status_text) {
                        // Keep the status dot if it exists
                        const statusDot = statusEl.querySelector('.status-dot');
                        statusEl.innerHTML = '';
                        if (statusDot && pillarData.show_dot) {
                            statusEl.appendChild(statusDot);
                        }
                        if (pillarData.icon) {
                            const icon = document.createElement('i');
                            icon.className = pillarData.icon;
                            statusEl.appendChild(icon);
                        }
                        statusEl.appendChild(document.createTextNode(pillarData.status_text));

                        // Apply alert class if needed
                        statusEl.classList.toggle('gfy-home__pillar-status--alert', pillarData.is_alert);
                    }
                }
            });
        }

        /**
         * Update recent activity list
         */
        updateRecentActivity(activities) {
            const listEl = document.querySelector('.gfy-home__recent-list');
            if (!listEl || !Array.isArray(activities)) return;

            if (activities.length === 0) {
                listEl.innerHTML = `
                    <div class="gfy-home__recent-empty">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <p>No recent activity yet. Start by exploring the tools above!</p>
                    </div>
                `;
                return;
            }

            listEl.innerHTML = activities.slice(0, 5).map(activity => `
                <a href="${this.escapeHtml(activity.url || '#')}" class="gfy-home__recent-item" data-activity-id="${activity.id || ''}">
                    <div class="gfy-home__recent-icon gfy-home__recent-icon--${this.escapeHtml(activity.type || 'default')}">
                        <i class="${this.escapeHtml(activity.icon || 'fa-solid fa-file')}"></i>
                    </div>
                    <div class="gfy-home__recent-info">
                        <h4>${this.escapeHtml(activity.title || 'Untitled')}</h4>
                        <p>${this.escapeHtml(activity.subtitle || '')}</p>
                    </div>
                    <div class="gfy-home__recent-arrow">
                        <i class="fa-solid fa-chevron-right"></i>
                    </div>
                </a>
            `).join('');
        }

        /**
         * Update tasks due badge
         */
        updateTasksBadge(count) {
            const badge = document.querySelector('.gfy-home__tasks-badge');
            if (badge) {
                if (count > 0) {
                    badge.innerHTML = `<i class="fa-solid fa-bell"></i> ${count} Task${count !== 1 ? 's' : ''} Due`;
                    badge.style.display = 'inline-flex';
                } else {
                    badge.style.display = 'none';
                }
            }
        }

        /**
         * Handle goal selection
         */
        handleGoalSelect(e) {
            const btn = e.currentTarget;
            const goal = btn.dataset.goal;

            if (!goal || goal === this.currentGoal) return;

            // Update UI
            document.querySelectorAll('.gfy-home__goal-btn').forEach(b => {
                b.classList.remove('active');
            });
            btn.classList.add('active');

            // Save preference
            this.currentGoal = goal;
            this.saveGoalPreference(goal);

            // Update pillars based on goal
            this.updatePillarsForGoal(goal);

            // Optionally refresh data with goal context
            this.fetchDashboardData();
        }

        /**
         * Update pillars section for the selected goal
         */
        updatePillarsForGoal(goal) {
            const pillarsSection = document.querySelector('.gfy-home__pillars');
            if (!pillarsSection) return;

            // Update goal attribute
            pillarsSection.dataset.goal = goal;

            // Goal-based pillar ordering
            const goalPillarOrder = {
                'build_authority': ['media_kit', 'showauthority', 'outreach', 'prospector'],
                'grow_revenue': ['prospector', 'showauthority', 'outreach', 'media_kit'],
                'launch_promote': ['outreach', 'prospector', 'showauthority', 'media_kit']
            };

            // Goal-based descriptions
            const goalDescriptions = {
                'build_authority': {
                    'media_kit': 'Perfect your professional image and credibility.',
                    'showauthority': 'Position yourself as the go-to expert.',
                    'outreach': 'Reach top-tier shows in your space.',
                    'prospector': 'Discover authority-building opportunities.'
                },
                'grow_revenue': {
                    'media_kit': 'Showcase your offers and conversion-focused content.',
                    'showauthority': 'Research high-converting podcast audiences.',
                    'outreach': 'Book appearances that drive sales.',
                    'prospector': 'Find shows with your ideal buyers.'
                },
                'launch_promote': {
                    'media_kit': 'Highlight your launch story and offer.',
                    'showauthority': 'Plan your interview tour strategy.',
                    'outreach': 'Execute your launch blitz campaign.',
                    'prospector': 'Build your launch show list fast.'
                }
            };

            // Goal-based CTAs
            const goalCtas = {
                'build_authority': {
                    'media_kit': 'Polish Profile',
                    'showauthority': 'Build Authority',
                    'outreach': 'Reach Out',
                    'prospector': 'Discover'
                },
                'grow_revenue': {
                    'media_kit': 'Optimize',
                    'showauthority': 'Research',
                    'outreach': 'Convert',
                    'prospector': 'Find Buyers'
                },
                'launch_promote': {
                    'media_kit': 'Prepare',
                    'showauthority': 'Plan Tour',
                    'outreach': 'Launch Blitz',
                    'prospector': 'Build List'
                }
            };

            const order = goalPillarOrder[goal] || goalPillarOrder['grow_revenue'];
            const descriptions = goalDescriptions[goal] || goalDescriptions['grow_revenue'];
            const ctas = goalCtas[goal] || goalCtas['grow_revenue'];
            const priorityPillar = order[0];

            // Get all pillar cards
            const pillars = Array.from(pillarsSection.querySelectorAll('.gfy-home__pillar'));

            // Sort pillars by goal order
            pillars.sort((a, b) => {
                const aKey = a.dataset.pillar;
                const bKey = b.dataset.pillar;
                return order.indexOf(aKey) - order.indexOf(bKey);
            });

            // Reorder in DOM and update content
            pillars.forEach((pillar, index) => {
                const key = pillar.dataset.pillar;

                // Update description
                const descEl = pillar.querySelector('.gfy-home__pillar-desc');
                if (descEl && descriptions[key]) {
                    descEl.textContent = descriptions[key];
                }

                // Update CTA
                const ctaEl = pillar.querySelector('.gfy-home__pillar-cta');
                if (ctaEl && ctas[key]) {
                    ctaEl.innerHTML = `${this.escapeHtml(ctas[key])} <i class="fa-solid fa-arrow-right"></i>`;
                }

                // Update priority state
                pillar.classList.toggle('gfy-home__pillar--priority', key === priorityPillar);

                // Add/remove badge
                let badge = pillar.querySelector('.gfy-home__pillar-badge');
                if (key === priorityPillar) {
                    if (!badge) {
                        badge = document.createElement('span');
                        badge.className = 'gfy-home__pillar-badge';
                        badge.textContent = 'Recommended';
                        pillar.insertBefore(badge, pillar.firstChild);
                    }
                } else if (badge) {
                    badge.remove();
                }

                // Reorder in DOM with animation
                pillar.style.order = index;
            });

            // Animate reorder
            pillarsSection.classList.add('gfy-home__pillars--reordering');
            setTimeout(() => {
                pillarsSection.classList.remove('gfy-home__pillars--reordering');
            }, 300);
        }

        /**
         * Save goal preference to server
         */
        async saveGoalPreference(goal) {
            try {
                await fetch(this.apiBase + 'home/goal', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': this.nonce
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ goal: goal })
                });
            } catch (error) {
                console.error('Guestify Home: Error saving goal preference:', error);
            }
        }

        /**
         * Initialize goal selector with saved preference
         */
        initGoalSelector() {
            const activeBtn = document.querySelector(`.gfy-home__goal-btn[data-goal="${this.currentGoal}"]`);
            if (activeBtn) {
                document.querySelectorAll('.gfy-home__goal-btn').forEach(b => {
                    b.classList.remove('active');
                });
                activeBtn.classList.add('active');
            }
        }

        /**
         * Handle pillar card click
         */
        handlePillarClick(e) {
            const card = e.currentTarget;
            const url = card.dataset.url;

            if (url) {
                window.location.href = url;
            }
        }

        /**
         * Handle recent activity item click
         */
        handleRecentClick(e) {
            // Let the default link behavior handle navigation
            // This handler is for potential analytics or animations
        }

        /**
         * Update greeting based on time of day
         */
        updateGreeting() {
            const greetingEl = document.querySelector('.gfy-home__greeting');
            if (!greetingEl) return;

            const hour = new Date().getHours();
            let greeting = 'Welcome back';

            if (hour >= 5 && hour < 12) {
                greeting = 'Good morning';
            } else if (hour >= 12 && hour < 17) {
                greeting = 'Good afternoon';
            } else if (hour >= 17 && hour < 21) {
                greeting = 'Good evening';
            }

            const userName = this.config.userName || 'there';
            greetingEl.textContent = `${greeting}, ${userName}`;
        }

        /**
         * Update displayed date
         */
        updateDate() {
            const dateEl = document.querySelector('.gfy-home__date-text');
            if (!dateEl) return;

            const now = new Date();
            const options = { weekday: 'long', month: 'long', day: 'numeric' };
            dateEl.textContent = now.toLocaleDateString('en-US', options);
        }

        /**
         * Show loading state
         */
        showLoadingState() {
            document.querySelectorAll('.gfy-home__stat-value').forEach(el => {
                if (!el.textContent || el.textContent === '—') {
                    el.classList.add('gfy-skeleton');
                }
            });
        }

        /**
         * Hide loading state
         */
        hideLoadingState() {
            document.querySelectorAll('.gfy-skeleton').forEach(el => {
                el.classList.remove('gfy-skeleton');
            });
        }

        /**
         * Show error state
         */
        showErrorState() {
            console.warn('Guestify Home: Dashboard data unavailable');
            // Keep existing static content visible
        }

        /**
         * Escape HTML to prevent XSS
         */
        escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }

    // Initialize the home page controller
    window.GuestifyHome = new GuestifyHome();

})();
