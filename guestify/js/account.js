/**
 * Guestify Account Settings JavaScript
 *
 * Handles interactive functionality for the account settings page.
 *
 * @package Guestify
 * @version 1.0.0
 */

(function() {
    'use strict';

    /**
     * GuestifyAccount Controller
     */
    class GuestifyAccount {
        constructor() {
            this.apiBase = '/wp-json/guestify/v1/account';
            this.nonce = typeof gfyAccountData !== 'undefined' ? gfyAccountData.nonce : '';
            this.init();
        }

        /**
         * Initialize the account page
         */
        init() {
            this.bindEvents();
            this.initToggles();
            this.initForms();
        }

        /**
         * Bind event listeners
         */
        bindEvents() {
            // Form submissions
            document.querySelectorAll('.gfy-account-form').forEach(form => {
                form.addEventListener('submit', this.handleFormSubmit.bind(this));
            });

            // Avatar upload
            const avatarInput = document.getElementById('avatar-upload');
            if (avatarInput) {
                avatarInput.addEventListener('change', this.handleAvatarUpload.bind(this));
            }

            // Copy API key
            const copyBtn = document.querySelector('[data-action="copy-api-key"]');
            if (copyBtn) {
                copyBtn.addEventListener('click', this.handleCopyApiKey.bind(this));
            }

            // Regenerate API key
            const regenBtn = document.querySelector('[data-action="regenerate-api-key"]');
            if (regenBtn) {
                regenBtn.addEventListener('click', this.handleRegenerateApiKey.bind(this));
            }

            // Toggle password visibility
            document.querySelectorAll('[data-toggle-password]').forEach(btn => {
                btn.addEventListener('click', this.togglePasswordVisibility.bind(this));
            });

            // Notification toggles
            document.querySelectorAll('.gfy-toggle__input').forEach(toggle => {
                toggle.addEventListener('change', this.handleNotificationToggle.bind(this));
            });

            // Integration buttons
            document.querySelectorAll('[data-integration]').forEach(btn => {
                btn.addEventListener('click', this.handleIntegrationAction.bind(this));
            });
        }

        /**
         * Initialize toggle switches
         */
        initToggles() {
            // Load saved notification preferences
            this.loadNotificationPreferences();
        }

        /**
         * Initialize forms
         */
        initForms() {
            // Auto-save indicator
            document.querySelectorAll('.gfy-input, .gfy-textarea').forEach(input => {
                input.addEventListener('input', this.markFormDirty.bind(this));
            });
        }

        /**
         * Mark form as dirty (unsaved changes)
         */
        markFormDirty(e) {
            const form = e.target.closest('form');
            if (form) {
                form.classList.add('gfy-form--dirty');
                const saveBtn = form.querySelector('[type="submit"]');
                if (saveBtn) {
                    saveBtn.disabled = false;
                }
            }
        }

        /**
         * Handle form submission
         */
        async handleFormSubmit(e) {
            e.preventDefault();
            const form = e.target;
            const submitBtn = form.querySelector('[type="submit"]');
            const formData = new FormData(form);
            const endpoint = form.dataset.endpoint || 'profile';

            // Disable button and show loading
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
            }

            try {
                const response = await this.apiRequest(`/${endpoint}`, 'POST', Object.fromEntries(formData));

                if (response.success) {
                    this.showNotification('Changes saved successfully', 'success');
                    form.classList.remove('gfy-form--dirty');
                } else {
                    this.showNotification(response.message || 'Failed to save changes', 'error');
                }
            } catch (error) {
                console.error('Form submission error:', error);
                this.showNotification('An error occurred. Please try again.', 'error');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fa-solid fa-check"></i> Save Changes';
                }
            }
        }

        /**
         * Handle avatar upload
         */
        async handleAvatarUpload(e) {
            const file = e.target.files[0];
            if (!file) return;

            // Validate file
            const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                this.showNotification('Please upload a JPG, PNG, or GIF image', 'error');
                return;
            }

            if (file.size > 800000) { // 800KB
                this.showNotification('Image must be less than 800KB', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('avatar', file);

            try {
                const response = await fetch(`${this.apiBase}/avatar`, {
                    method: 'POST',
                    headers: {
                        'X-WP-Nonce': this.nonce
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    // Update avatar preview
                    const avatarPreview = document.querySelector('.gfy-avatar--lg');
                    if (avatarPreview && data.avatar_url) {
                        avatarPreview.innerHTML = `<img src="${data.avatar_url}" alt="Profile Avatar">`;
                        avatarPreview.classList.remove('gfy-avatar--upload-placeholder');
                    }
                    this.showNotification('Avatar updated successfully', 'success');
                } else {
                    this.showNotification(data.message || 'Failed to upload avatar', 'error');
                }
            } catch (error) {
                console.error('Avatar upload error:', error);
                this.showNotification('An error occurred uploading avatar', 'error');
            }
        }

        /**
         * Handle copy API key
         */
        async handleCopyApiKey(e) {
            const btn = e.currentTarget;
            const apiKeyInput = document.getElementById('apiKey');

            if (!apiKeyInput) return;

            try {
                // Temporarily show the API key
                const originalType = apiKeyInput.type;
                apiKeyInput.type = 'text';
                apiKeyInput.select();
                await navigator.clipboard.writeText(apiKeyInput.value);
                apiKeyInput.type = originalType;

                // Update button
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                }, 2000);
            } catch (error) {
                this.showNotification('Failed to copy API key', 'error');
            }
        }

        /**
         * Handle regenerate API key
         */
        async handleRegenerateApiKey(e) {
            if (!confirm('Are you sure you want to regenerate your API key? This will invalidate the current key.')) {
                return;
            }

            const btn = e.currentTarget;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

            try {
                const response = await this.apiRequest('/api-key/regenerate', 'POST');

                if (response.success && response.api_key) {
                    const apiKeyInput = document.getElementById('apiKey');
                    if (apiKeyInput) {
                        apiKeyInput.value = response.api_key;
                    }
                    this.showNotification('API key regenerated successfully', 'success');
                } else {
                    this.showNotification(response.message || 'Failed to regenerate API key', 'error');
                }
            } catch (error) {
                console.error('API key regeneration error:', error);
                this.showNotification('An error occurred', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-arrows-rotate"></i> Regenerate';
            }
        }

        /**
         * Toggle password visibility
         */
        togglePasswordVisibility(e) {
            const btn = e.currentTarget;
            const targetId = btn.dataset.togglePassword;
            const input = document.getElementById(targetId);

            if (!input) return;

            if (input.type === 'password') {
                input.type = 'text';
                btn.innerHTML = '<i class="fa-solid fa-eye-slash"></i>';
            } else {
                input.type = 'password';
                btn.innerHTML = '<i class="fa-solid fa-eye"></i>';
            }
        }

        /**
         * Handle notification toggle
         */
        async handleNotificationToggle(e) {
            const toggle = e.target;
            const setting = toggle.dataset.setting;
            const enabled = toggle.checked;

            try {
                const response = await this.apiRequest('/notifications', 'POST', {
                    setting: setting,
                    enabled: enabled
                });

                if (!response.success) {
                    // Revert toggle on failure
                    toggle.checked = !enabled;
                    this.showNotification(response.message || 'Failed to update setting', 'error');
                }
            } catch (error) {
                toggle.checked = !enabled;
                this.showNotification('An error occurred', 'error');
            }
        }

        /**
         * Load notification preferences
         */
        async loadNotificationPreferences() {
            try {
                const response = await this.apiRequest('/notifications', 'GET');
                if (response.success && response.preferences) {
                    Object.entries(response.preferences).forEach(([key, value]) => {
                        const toggle = document.querySelector(`.gfy-toggle__input[data-setting="${key}"]`);
                        if (toggle) {
                            toggle.checked = value;
                        }
                    });
                }
            } catch (error) {
                // Silent fail - toggles will use default values
            }
        }

        /**
         * Handle integration action
         */
        async handleIntegrationAction(e) {
            const btn = e.currentTarget;
            const integration = btn.dataset.integration;
            const action = btn.dataset.action || 'connect';

            if (action === 'disconnect') {
                if (!confirm(`Are you sure you want to disconnect ${integration}?`)) {
                    return;
                }
            }

            btn.disabled = true;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

            try {
                const response = await this.apiRequest('/integrations', 'POST', {
                    integration: integration,
                    action: action
                });

                if (response.success) {
                    if (response.redirect_url) {
                        // OAuth flow - redirect
                        window.location.href = response.redirect_url;
                    } else {
                        // Direct connection/disconnection
                        this.updateIntegrationButton(btn, action === 'connect');
                        this.showNotification(response.message || 'Integration updated', 'success');
                    }
                } else {
                    this.showNotification(response.message || 'Failed to update integration', 'error');
                    btn.innerHTML = originalHtml;
                }
            } catch (error) {
                console.error('Integration error:', error);
                this.showNotification('An error occurred', 'error');
                btn.innerHTML = originalHtml;
            } finally {
                btn.disabled = false;
            }
        }

        /**
         * Update integration button state
         */
        updateIntegrationButton(btn, connected) {
            if (connected) {
                btn.className = 'gfy-btn gfy-btn--success';
                btn.innerHTML = '<i class="fa-solid fa-check"></i> Connected';
                btn.dataset.action = 'disconnect';
            } else {
                btn.className = 'gfy-btn gfy-btn--secondary';
                btn.innerHTML = 'Connect';
                btn.dataset.action = 'connect';
            }
        }

        /**
         * Make API request
         */
        async apiRequest(endpoint, method = 'GET', data = null) {
            const options = {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': this.nonce
                }
            };

            if (data && method !== 'GET') {
                options.body = JSON.stringify(data);
            }

            const response = await fetch(this.apiBase + endpoint, options);
            return response.json();
        }

        /**
         * Show notification toast
         */
        showNotification(message, type = 'info') {
            // Remove existing notifications
            document.querySelectorAll('.gfy-toast').forEach(el => el.remove());

            // Create toast element
            const toast = document.createElement('div');
            toast.className = `gfy-toast gfy-toast--${type}`;
            toast.innerHTML = `
                <div class="gfy-toast__content">
                    <i class="fa-solid fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                    <span>${message}</span>
                </div>
                <button class="gfy-toast__close" aria-label="Close notification">
                    <i class="fa-solid fa-times"></i>
                </button>
            `;

            // Add close handler
            toast.querySelector('.gfy-toast__close').addEventListener('click', () => {
                toast.remove();
            });

            // Add to DOM
            document.body.appendChild(toast);

            // Trigger animation
            requestAnimationFrame(() => {
                toast.classList.add('gfy-toast--visible');
            });

            // Auto-remove after 5 seconds
            setTimeout(() => {
                toast.classList.remove('gfy-toast--visible');
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }
    }

    /**
     * CreditPanel Controller
     *
     * Handles the Credits & Usage panel — fetches live data from
     * the Guestify REST API and renders balance, actions, transactions,
     * and purchase packs using vanilla JS (no Vue dependency).
     */
    class CreditPanel {
        constructor() {
            // Only run on the credits panel
            if (!document.getElementById('credits')) return;

            this.apiBase = typeof gfyAccountData !== 'undefined' && gfyAccountData.creditApiBase
                ? gfyAccountData.creditApiBase
                : '/wp-json/guestify/v1/';
            this.nonce = typeof gfyAccountData !== 'undefined' ? gfyAccountData.nonce : '';
            this.circumference = 2 * Math.PI * 52; // r=52

            this.init();
        }

        async init() {
            try {
                // Fetch all data in parallel
                const [balanceRes, usageRes, actionsRes, transactionsRes, packsRes] = await Promise.allSettled([
                    this.apiFetch('credits/balance'),
                    this.apiFetch('credits/usage'),
                    this.apiFetch('credits/actions'),
                    this.apiFetch('credits/transactions?per_page=15'),
                    this.apiFetch('credits/packs'),
                ]);

                if (balanceRes.status === 'fulfilled' && balanceRes.value.success) {
                    this.updateGauge(balanceRes.value.data);
                } else {
                    this.updateGauge({ allowance: 0, rollover: 0, overage: 0, total: 0, monthly_allowance: 0 });
                }

                if (usageRes.status === 'fulfilled' && usageRes.value.success) {
                    this.renderUsage(usageRes.value.data);
                } else {
                    this.renderUsage({ total_spent: 0, total_actions: 0 });
                }

                if (actionsRes.status === 'fulfilled' && actionsRes.value.success) {
                    this.renderActions(actionsRes.value.data);
                } else {
                    this.renderActions({ actions: [] });
                }

                if (transactionsRes.status === 'fulfilled' && transactionsRes.value.success) {
                    this.renderTransactions(transactionsRes.value.data);
                } else {
                    this.renderTransactions({ items: [] });
                }

                if (packsRes.status === 'fulfilled' && packsRes.value.success) {
                    this.renderPacks(packsRes.value.data);
                }
            } catch (err) {
                console.error('CreditPanel init error:', err);
            }
        }

        async apiFetch(endpoint) {
            const resp = await fetch(this.apiBase + endpoint, {
                headers: {
                    'X-WP-Nonce': this.nonce,
                    'Content-Type': 'application/json',
                },
            });
            if (!resp.ok) throw new Error('API error: ' + resp.status);
            return resp.json();
        }

        async apiPost(endpoint, body) {
            const resp = await fetch(this.apiBase + endpoint, {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': this.nonce,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(body),
            });
            if (!resp.ok) {
                const data = await resp.json().catch(() => ({}));
                throw new Error(data.message || 'API error: ' + resp.status);
            }
            return resp.json();
        }

        /**
         * Update the gauge ring and balance breakdown with live data
         */
        updateGauge(data) {
            const allowance = data.allowance || 0;
            const rollover = data.rollover || 0;
            const overage = data.overage || 0;
            const total = data.total || (allowance + rollover + overage);
            const monthly = data.monthly_allowance || 0;

            const used = monthly > 0 ? monthly - allowance : 0;
            const pct = monthly > 0 ? Math.min(100, Math.round((used / monthly) * 100)) : 0;

            // Update gauge arc
            const arc = document.getElementById('gauge-arc');
            if (arc) {
                const offset = this.circumference - (this.circumference * Math.min(pct, 100) / 100);
                arc.setAttribute('stroke-dashoffset', offset.toFixed(1));

                let color = '#10b981';
                if (pct >= 100) color = '#ef4444';
                else if (pct >= 80) color = '#f59e0b';
                arc.setAttribute('stroke', color);
            }

            // Update center value
            const gaugeTotal = document.getElementById('gauge-total');
            if (gaugeTotal) gaugeTotal.textContent = total.toLocaleString();

            // Update breakdown
            const elAllowance = document.getElementById('breakdown-allowance');
            if (elAllowance) elAllowance.textContent = allowance.toLocaleString();

            const elRollover = document.getElementById('breakdown-rollover');
            const rowRollover = document.getElementById('row-rollover');
            if (elRollover) elRollover.textContent = rollover.toLocaleString();
            if (rowRollover) rowRollover.style.display = rollover > 0 ? '' : 'none';

            const elOverage = document.getElementById('breakdown-overage');
            const rowOverage = document.getElementById('row-overage');
            if (elOverage) elOverage.textContent = overage.toLocaleString();
            if (rowOverage) rowOverage.style.display = overage > 0 ? '' : 'none';

            const elTotal = document.getElementById('breakdown-total');
            if (elTotal) elTotal.innerHTML = '<strong>' + total.toLocaleString() + '</strong> / ' + monthly.toLocaleString();

            // Upgrade prompt
            const prompt = document.getElementById('credit-upgrade-prompt');
            if (prompt) {
                if (pct >= 80) {
                    prompt.style.display = '';
                    prompt.dataset.level = pct >= 100 ? 'danger' : 'warning';
                    const icon = prompt.querySelector('i.fa-solid');
                    if (icon) {
                        icon.className = pct >= 100
                            ? 'fa-solid fa-circle-exclamation'
                            : 'fa-solid fa-triangle-exclamation';
                    }
                    const msg = document.getElementById('credit-upgrade-message');
                    if (msg) {
                        if (total <= 0 && data.hard_cap > 0) {
                            msg.textContent = 'You have reached your credit limit for this billing cycle.';
                        } else if (pct >= 100) {
                            msg.textContent = 'Your monthly allowance is used up. Purchase additional credits or upgrade your plan.';
                        } else {
                            msg.textContent = "You're running low on credits. Consider upgrading your plan.";
                        }
                    }
                } else {
                    prompt.style.display = 'none';
                }
            }
        }

        /**
         * Render cycle usage stats
         */
        renderUsage(data) {
            const spent = document.getElementById('usage-spent');
            const actions = document.getElementById('usage-actions');
            if (spent) spent.textContent = (data.total_spent || 0).toLocaleString();
            if (actions) actions.textContent = (data.total_actions || 0).toLocaleString();
        }

        /**
         * Render action costs table
         */
        renderActions(data) {
            const container = document.getElementById('credit-actions-list');
            if (!container) return;

            const actions = data.actions || [];
            if (!actions.length) {
                container.innerHTML = '<div class="gfy-empty-state" style="padding: var(--gfy-space-6, 1.5rem);"><p>No action costs configured yet.</p></div>';
                return;
            }

            let html = '<table class="gfy-table"><thead><tr>' +
                '<th class="gfy-table__th">Action</th>' +
                '<th class="gfy-table__th" style="text-align:right;">Cost</th>' +
                '<th class="gfy-table__th" style="text-align:right;">Remaining</th>' +
                '</tr></thead><tbody>';

            actions.forEach(action => {
                const name = this.formatAction(action.action_type);
                const cost = action.cost || action.credits_per_unit || 0;
                const remaining = typeof action.remaining !== 'undefined' ? action.remaining : '--';
                html += '<tr class="gfy-table__tr">' +
                    '<td class="gfy-table__td"><i class="fa-solid fa-circle" style="font-size: 6px; color: var(--gfy-gray-400); vertical-align: middle; margin-right: 8px;"></i>' + this.escHtml(name) + '</td>' +
                    '<td class="gfy-table__td" style="text-align:right;"><span class="gfy-badge gfy-badge--sm gfy-badge--info">' + cost + ' cr</span></td>' +
                    '<td class="gfy-table__td" style="text-align:right; color: var(--gfy-gray-500);">' + (typeof remaining === 'number' ? remaining.toLocaleString() : remaining) + '</td>' +
                    '</tr>';
            });

            html += '</tbody></table>';
            container.innerHTML = html;
        }

        /**
         * Render transactions list
         */
        renderTransactions(data) {
            const container = document.getElementById('credit-transactions-list');
            if (!container) return;

            const items = data.items || [];
            if (!items.length) {
                container.innerHTML = '<div class="gfy-empty-state" style="padding: var(--gfy-space-6, 1.5rem);">' +
                    '<div class="gfy-empty-state__icon"><i class="fa-solid fa-clock-rotate-left"></i></div>' +
                    '<h3 class="gfy-empty-state__title">No Activity Yet</h3>' +
                    '<p class="gfy-empty-state__desc">Your credit transactions will appear here as you use Guestify features.</p>' +
                    '</div>';
                return;
            }

            let html = '<table class="gfy-table"><thead><tr>' +
                '<th class="gfy-table__th">Action</th>' +
                '<th class="gfy-table__th">Source</th>' +
                '<th class="gfy-table__th" style="text-align:right;">Credits</th>' +
                '<th class="gfy-table__th" style="text-align:right;">Balance</th>' +
                '<th class="gfy-table__th" style="text-align:right;">Date</th>' +
                '</tr></thead><tbody>';

            items.forEach(txn => {
                const isDebit = txn.credits_used > 0;
                const amtClass = isDebit ? 'gfy-text--danger' : 'gfy-text--success';
                const amtPrefix = isDebit ? '-' : '+';
                const absAmt = Math.abs(txn.credits_used || 0);
                const sourceIcon = this.sourceIcon(txn.source_type);
                const sourceLabel = this.formatSource(txn.source_type);

                html += '<tr class="gfy-table__tr">' +
                    '<td class="gfy-table__td">' + this.escHtml(this.formatAction(txn.action_type)) + '</td>' +
                    '<td class="gfy-table__td"><i class="fa-solid ' + sourceIcon + '" style="margin-right:4px; color: var(--gfy-gray-400);"></i>' + this.escHtml(sourceLabel) + '</td>' +
                    '<td class="gfy-table__td ' + amtClass + '" style="text-align:right; font-weight: 600;">' + amtPrefix + absAmt + '</td>' +
                    '<td class="gfy-table__td" style="text-align:right; color: var(--gfy-gray-500);">' + (txn.balance_after != null ? txn.balance_after.toLocaleString() : '--') + '</td>' +
                    '<td class="gfy-table__td" style="text-align:right; color: var(--gfy-gray-500);">' + this.formatDate(txn.created_at) + '</td>' +
                    '</tr>';
            });

            html += '</tbody></table>';
            container.innerHTML = html;
        }

        /**
         * Render purchase packs
         */
        renderPacks(data) {
            const packs = Array.isArray(data) ? data : (data.packs || []);
            if (!packs.length) return;

            const card = document.getElementById('credit-packs-card');
            const grid = document.getElementById('credit-packs-grid');
            if (!card || !grid) return;

            card.style.display = '';

            let html = '';
            packs.forEach(pack => {
                html += '<button class="gfy-credit-pack" data-pack-key="' + this.escAttr(pack.key) + '">' +
                    '<span class="gfy-credit-pack__credits">' + (pack.credits || 0).toLocaleString() + '</span>' +
                    '<span class="gfy-credit-pack__label">credits</span>' +
                    '<span class="gfy-credit-pack__price">' + this.escHtml(pack.price_display || '$' + (pack.price_cents / 100).toFixed(0)) + '</span>' +
                    '</button>';
            });

            grid.innerHTML = html;

            // Bind click events
            grid.querySelectorAll('.gfy-credit-pack').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const packKey = btn.dataset.packKey;
                    btn.disabled = true;
                    btn.classList.add('gfy-credit-pack--loading');
                    try {
                        const result = await this.apiPost('credits/purchase', { pack: packKey });
                        if (result.success && result.data && result.data.checkout_url) {
                            window.location.href = result.data.checkout_url;
                        }
                    } catch (err) {
                        console.error('Purchase error:', err);
                        if (typeof GuestifyAccount !== 'undefined') {
                            // Reuse the toast from parent class
                        }
                        alert('Failed to start checkout: ' + err.message);
                    } finally {
                        btn.disabled = false;
                        btn.classList.remove('gfy-credit-pack--loading');
                    }
                });
            });
        }

        // Helpers
        formatAction(actionType) {
            return (actionType || '')
                .replace(/_/g, ' ')
                .replace(/\b\w/g, c => c.toUpperCase());
        }

        formatSource(sourceType) {
            const map = { allowance: 'Allowance', rollover: 'Rollover', overage: 'Purchased', admin: 'Admin', refill: 'Refill', system: 'System' };
            return map[sourceType] || (sourceType || 'Unknown');
        }

        sourceIcon(sourceType) {
            const icons = { allowance: 'fa-circle-check', rollover: 'fa-rotate-right', overage: 'fa-credit-card', admin: 'fa-user-shield', refill: 'fa-arrows-rotate', system: 'fa-gear' };
            return icons[sourceType] || 'fa-circle';
        }

        formatDate(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        }

        escHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        escAttr(str) {
            return (str || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            new GuestifyAccount();
            new CreditPanel();
        });
    } else {
        new GuestifyAccount();
        new CreditPanel();
    }
})();
