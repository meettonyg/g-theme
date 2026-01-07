/**
 * Auth Modal
 * Login/Registration modal with social login options
 */

(function() {
    'use strict';

    // ==========================================================================
    // CONFIGURATION
    // ==========================================================================

    const CONFIG = {
        // Auto-trigger settings
        autoTriggerOnLoad: true,        // Show immediately on page load
        autoTriggerDelay: 500,          // Small delay for page to render (ms)
        sessionStorageKey: 'guestify_auth_modal_shown',
        // Don't show again for this many hours after dismissing
        dismissCooldownHours: 24
    };

    // ==========================================================================
    // STATE
    // ==========================================================================

    let state = {
        isOpen: false,
        hasTriggered: false,
        autoTriggerTimeout: null
    };

    // ==========================================================================
    // DOM ELEMENTS
    // ==========================================================================

    let elements = {};

    function cacheElements() {
        elements = {
            modal: document.getElementById('authModal'),
            backdrop: document.getElementById('authModalBackdrop'),
            close: document.getElementById('authModalClose'),
            title: document.getElementById('authModalTitle'),
            subtitle: document.getElementById('authModalSubtitle'),
            form: document.getElementById('authModalForm')
        };
    }

    // ==========================================================================
    // SESSION TRACKING
    // ==========================================================================

    function hasBeenShownRecently() {
        try {
            const lastShown = localStorage.getItem(CONFIG.sessionStorageKey);
            if (!lastShown) return false;

            const lastShownTime = parseInt(lastShown, 10);
            const cooldownMs = CONFIG.dismissCooldownHours * 60 * 60 * 1000;
            return (Date.now() - lastShownTime) < cooldownMs;
        } catch (e) {
            return false;
        }
    }

    function markAsShown() {
        try {
            localStorage.setItem(CONFIG.sessionStorageKey, Date.now().toString());
        } catch (e) {
            // Ignore storage errors
        }
    }

    // ==========================================================================
    // OPEN / CLOSE
    // ==========================================================================

    function open(options = {}) {
        if (!elements.modal) return;
        if (state.isOpen) return;

        state.isOpen = true;
        state.hasTriggered = true;

        // Update content if options provided
        if (options.title && elements.title) {
            elements.title.textContent = options.title;
        }
        if (options.subtitle && elements.subtitle) {
            elements.subtitle.textContent = options.subtitle;
        }

        elements.modal.classList.add('auth-modal--open');
        document.body.style.overflow = 'hidden';

        // Focus trap
        const firstFocusable = elements.modal.querySelector('a, button, input');
        if (firstFocusable) {
            setTimeout(() => firstFocusable.focus(), 100);
        }
    }

    function close() {
        if (!elements.modal) return;
        if (!state.isOpen) return;

        state.isOpen = false;
        elements.modal.classList.remove('auth-modal--open');
        document.body.style.overflow = '';

        // Mark as shown to prevent re-triggering
        markAsShown();
    }

    function toggle() {
        state.isOpen ? close() : open();
    }

    // ==========================================================================
    // AUTO-TRIGGER LOGIC
    // ==========================================================================

    function setupAutoTrigger() {
        // Don't auto-trigger if already shown recently
        if (hasBeenShownRecently()) {
            return;
        }

        // Trigger on page load
        if (CONFIG.autoTriggerOnLoad) {
            state.autoTriggerTimeout = setTimeout(() => {
                if (!state.hasTriggered) {
                    open({
                        title: 'Save your work',
                        subtitle: 'Sign in to save your progress and access all features:'
                    });
                }
            }, CONFIG.autoTriggerDelay);
        }
    }

    // ==========================================================================
    // EVENT HANDLERS
    // ==========================================================================

    function handleKeyDown(e) {
        if (!state.isOpen) return;

        if (e.key === 'Escape') {
            e.preventDefault();
            close();
        }
    }

    function handleClick(e) {
        // Close on backdrop click
        if (e.target === elements.backdrop) {
            close();
        }

        // Close button
        if (e.target === elements.close || e.target.closest('#authModalClose')) {
            close();
        }
    }

    // ==========================================================================
    // TRIGGER SETUP
    // ==========================================================================

    function setupTriggers() {
        // Find all elements with data-auth-trigger attribute
        document.querySelectorAll('[data-auth-trigger]').forEach(el => {
            el.addEventListener('click', (e) => {
                e.preventDefault();

                const title = el.dataset.authTitle || 'Save your progress';
                const subtitle = el.dataset.authSubtitle || 'Sign in to save your work and unlock all features:';

                open({ title, subtitle });
            });
        });
    }

    // ==========================================================================
    // INITIALIZATION
    // ==========================================================================

    function init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setup);
        } else {
            setup();
        }
    }

    function setup() {
        cacheElements();

        if (!elements.modal) {
            // Modal not on page, that's fine
            return;
        }

        // Event listeners
        document.addEventListener('keydown', handleKeyDown);
        elements.modal.addEventListener('click', handleClick);

        // Setup trigger elements
        setupTriggers();

        // Setup auto-trigger
        setupAutoTrigger();
    }

    // Start
    init();

    // Expose globally
    window.GuestifyAuthModal = {
        open,
        close,
        toggle
    };

})();
