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

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => new GuestifyAccount());
    } else {
        new GuestifyAccount();
    }
})();
