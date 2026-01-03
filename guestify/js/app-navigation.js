/**
 * Guestify App Navigation JavaScript
 *
 * Handles mobile menu toggle, user dropdown, and notifications panel.
 * (Active state is now handled server-side by PHP to prevent FOUC)
 */

(function() {
    'use strict';

    // Store for notifications state
    let notificationsLoaded = false;
    let notificationsPanelOpen = false;

    // Mobile menu toggle functionality
    window.toggleMobileMenu = function() {
        const mobileMenu = document.getElementById('mobileMenu');
        if (mobileMenu) {
            mobileMenu.classList.toggle('is-open');
        }
    };

    // User dropdown toggle functionality
    window.toggleUserDropdown = function() {
        const dropdown = document.getElementById('userDropdownMenu');
        if (!dropdown) return;

        const isOpen = dropdown.classList.contains('app-nav__user-menu--open');

        // Close notifications panel if open
        closeNotificationsPanel();

        // Close all dropdowns first
        document.querySelectorAll('.app-nav__user-menu').forEach(menu => {
            menu.classList.remove('app-nav__user-menu--open');
        });

        // Toggle current dropdown
        if (!isOpen) {
            dropdown.classList.add('app-nav__user-menu--open');
        }
    };

    // Notifications panel toggle
    window.toggleNotificationsPanel = function() {
        const panel = document.getElementById('notificationsPanel');
        if (!panel) return;

        // Close user dropdown if open
        const userMenu = document.getElementById('userDropdownMenu');
        if (userMenu) {
            userMenu.classList.remove('app-nav__user-menu--open');
        }

        notificationsPanelOpen = !notificationsPanelOpen;

        if (notificationsPanelOpen) {
            panel.classList.add('app-nav__notifications-panel--open');
            if (!notificationsLoaded) {
                fetchNotifications();
            }
        } else {
            panel.classList.remove('app-nav__notifications-panel--open');
        }
    };

    // Close notifications panel
    function closeNotificationsPanel() {
        const panel = document.getElementById('notificationsPanel');
        if (panel) {
            panel.classList.remove('app-nav__notifications-panel--open');
            notificationsPanelOpen = false;
        }
    }

    // Fetch notifications from API
    async function fetchNotifications() {
        const list = document.getElementById('notificationsList');
        if (!list) return;

        list.innerHTML = '<div class="app-nav__notifications-loading"><span>Loading...</span></div>';

        try {
            const response = await fetch('/wp-json/guestify/v1/notifications?per_page=10', {
                credentials: 'same-origin',
                headers: {
                    'X-WP-Nonce': getWPNonce(),
                    'Content-Type': 'application/json',
                }
            });

            if (response.status === 404) {
                // Endpoint doesn't exist yet
                notificationsLoaded = true;
                renderNotifications([]);
                return;
            }

            if (!response.ok) throw new Error('Failed to fetch: ' + response.status);

            const data = await response.json();
            notificationsLoaded = true;
            renderNotifications(data.data || []);
        } catch (error) {
            console.error('Failed to fetch notifications:', error);
            // Show empty state instead of error for better UX
            notificationsLoaded = true;
            renderNotifications([]);
        }
    }

    // Render notifications list
    function renderNotifications(notifications) {
        const list = document.getElementById('notificationsList');
        if (!list) return;

        if (notifications.length === 0) {
            list.innerHTML = `
                <div class="app-nav__notifications-empty">
                    <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <p>You're all caught up!</p>
                    <span>No new notifications</span>
                </div>
            `;
            return;
        }

        list.innerHTML = notifications.map(n => `
            <div class="app-nav__notification-item ${n.is_read ? '' : 'app-nav__notification-item--unread'}"
                 data-id="${n.id}"
                 onclick="handleNotificationClick(${n.id}, '${n.action_url || ''}')">
                <div class="app-nav__notification-icon app-nav__notification-icon--${n.type}">
                    ${getNotificationIcon(n.type)}
                </div>
                <div class="app-nav__notification-content">
                    <div class="app-nav__notification-title">${escapeHtml(n.title)}</div>
                    ${n.message ? `<div class="app-nav__notification-message">${escapeHtml(n.message)}</div>` : ''}
                    <div class="app-nav__notification-time">${formatTimeAgo(n.created_at)}</div>
                </div>
                <button class="app-nav__notification-dismiss" onclick="event.stopPropagation(); dismissNotification(${n.id})" title="Dismiss">
                    <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        `).join('');
    }

    // Get notification icon based on type
    function getNotificationIcon(type) {
        const icons = {
            task_reminder: '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>',
            task_overdue: '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>',
            interview_soon: '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7 4a3 3 0 016 0v4a3 3 0 11-6 0V4zm4 10.93A7.001 7.001 0 0017 8a1 1 0 10-2 0A5 5 0 015 8a1 1 0 00-2 0 7.001 7.001 0 006 6.93V17H6a1 1 0 100 2h8a1 1 0 100-2h-3v-2.07z" clip-rule="evenodd"/></svg>',
            info: '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>',
        };
        return icons[type] || icons.info;
    }

    // Handle notification click
    window.handleNotificationClick = async function(id, actionUrl) {
        // Mark as read (fire and forget, don't block navigation)
        fetch(`/wp-json/guestify/v1/notifications/${id}/read`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-WP-Nonce': getWPNonce(),
                'Content-Type': 'application/json',
            }
        }).then(() => {
            updateNotificationBadge();
        }).catch(() => {
            // Silently fail
        });

        // Update UI immediately
        const item = document.querySelector(`.app-nav__notification-item[data-id="${id}"]`);
        if (item) {
            item.classList.remove('app-nav__notification-item--unread');
        }

        // Navigate if action URL provided
        if (actionUrl) {
            closeNotificationsPanel();
            window.location.href = actionUrl;
        }
    };

    // Dismiss notification
    window.dismissNotification = async function(id) {
        // Remove from UI immediately for responsiveness
        const item = document.querySelector(`.app-nav__notification-item[data-id="${id}"]`);
        if (item) {
            item.remove();
        }

        // Check if list is empty
        const list = document.getElementById('notificationsList');
        if (list && list.children.length === 0) {
            renderNotifications([]);
        }

        // Fire API call in background
        fetch(`/wp-json/guestify/v1/notifications/${id}/dismiss`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-WP-Nonce': getWPNonce(),
                'Content-Type': 'application/json',
            }
        }).then(() => {
            updateNotificationBadge();
        }).catch(() => {
            // Silently fail
        });
    };

    // Mark all notifications as read
    window.markAllNotificationsRead = async function() {
        // Update UI immediately
        document.querySelectorAll('.app-nav__notification-item--unread').forEach(item => {
            item.classList.remove('app-nav__notification-item--unread');
        });

        // Fire API call in background
        fetch('/wp-json/guestify/v1/notifications/read-all', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-WP-Nonce': getWPNonce(),
                'Content-Type': 'application/json',
            }
        }).then(() => {
            updateNotificationBadge();
        }).catch(() => {
            // Silently fail
        });
    };

    // Update notification badge count
    async function updateNotificationBadge() {
        const badge = document.getElementById('notificationsBadge');
        if (!badge) return;

        try {
            const response = await fetch('/wp-json/guestify/v1/notifications/count', {
                credentials: 'same-origin',
                headers: {
                    'X-WP-Nonce': getWPNonce(),
                    'Content-Type': 'application/json',
                }
            });

            // Silently handle missing endpoint or auth errors
            if (!response.ok) {
                badge.textContent = '';
                badge.classList.remove('app-nav__notifications-badge--visible');
                return;
            }

            const data = await response.json();
            const count = data.count || 0;

            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.classList.add('app-nav__notifications-badge--visible');
            } else {
                badge.textContent = '';
                badge.classList.remove('app-nav__notifications-badge--visible');
            }
        } catch (error) {
            // Silently fail - don't spam console for missing endpoints
            badge.textContent = '';
            badge.classList.remove('app-nav__notifications-badge--visible');
        }
    }

    // Get WordPress nonce
    function getWPNonce() {
        // Try to get from our localized script data first
        if (window.guestifyAppNav && window.guestifyAppNav.nonce) {
            return window.guestifyAppNav.nonce;
        }
        // Fallback to other sources
        if (window.wpApiSettings && window.wpApiSettings.nonce) {
            return window.wpApiSettings.nonce;
        }
        if (window.pitCalendarData && window.pitCalendarData.nonce) {
            return window.pitCalendarData.nonce;
        }
        if (window.pitTasksData && window.pitTasksData.nonce) {
            return window.pitTasksData.nonce;
        }
        // Look for nonce in meta tag
        const meta = document.querySelector('meta[name="wp-api-nonce"]');
        if (meta) {
            return meta.content;
        }
        return '';
    }

    // Escape HTML for safe rendering
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Format time ago
    function formatTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const seconds = Math.floor((now - date) / 1000);

        if (seconds < 60) return 'Just now';
        if (seconds < 3600) return Math.floor(seconds / 60) + 'm ago';
        if (seconds < 86400) return Math.floor(seconds / 3600) + 'h ago';
        if (seconds < 604800) return Math.floor(seconds / 86400) + 'd ago';

        return date.toLocaleDateString();
    }

    // Initialize app navigation when DOM is ready
    function initAppNavigation() {
        // Close mobile menu, user dropdown, and notifications when clicking outside
        document.addEventListener('click', function(event) {
            const mobileMenu = document.getElementById('mobileMenu');
            const mobileToggle = document.querySelector('.app-nav__mobile-toggle');
            const userDropdown = document.querySelector('.app-nav__user-dropdown');
            const userMenu = document.getElementById('userDropdownMenu');
            const notificationsWrapper = document.querySelector('.app-nav__notifications-wrapper');
            const notificationsPanel = document.getElementById('notificationsPanel');

            // Handle mobile menu
            if (mobileMenu && mobileToggle) {
                if (!mobileMenu.contains(event.target) && !mobileToggle.contains(event.target)) {
                    mobileMenu.classList.remove('is-open');
                }
            }

            // Handle user dropdown
            if (userDropdown && userMenu) {
                if (!userDropdown.contains(event.target)) {
                    userMenu.classList.remove('app-nav__user-menu--open');
                }
            }

            // Handle notifications panel
            if (notificationsWrapper && notificationsPanel) {
                if (!notificationsWrapper.contains(event.target)) {
                    notificationsPanel.classList.remove('app-nav__notifications-panel--open');
                    notificationsPanelOpen = false;
                }
            }
        });

        // Handle escape key to close all panels
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const mobileMenu = document.getElementById('mobileMenu');
                const userMenu = document.getElementById('userDropdownMenu');
                const notificationsPanel = document.getElementById('notificationsPanel');

                if (mobileMenu) {
                    mobileMenu.classList.remove('is-open');
                }

                if (userMenu) {
                    userMenu.classList.remove('app-nav__user-menu--open');
                }

                if (notificationsPanel) {
                    notificationsPanel.classList.remove('app-nav__notifications-panel--open');
                    notificationsPanelOpen = false;
                }
            }
        });

        // Handle dropdown menus for mobile
        handleMobileDropdowns();

        // Fetch notification count on load
        updateNotificationBadge();

        // Poll for new notifications every 5 minutes
        setInterval(updateNotificationBadge, 300000);
    }

    // Handle dropdown behavior on mobile
    function handleMobileDropdowns() {
        const dropdownItems = document.querySelectorAll('.app-nav__item--dropdown');

        dropdownItems.forEach(item => {
            const link = item.querySelector('.app-nav__link');
            const dropdown = item.querySelector('.app-nav__dropdown');

            if (link && dropdown) {
                // Convert dropdown to mobile-friendly accordion on small screens
                link.addEventListener('click', function(event) {
                    if (window.innerWidth <= 768) {
                        event.preventDefault();
                        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
                    }
                });
            }
        });
    }

    // Handle window resize for responsive behavior
    function handleResize() {
        if (window.innerWidth > 768) {
            const mobileMenu = document.getElementById('mobileMenu');
            const userMenu = document.getElementById('userDropdownMenu');

            if (mobileMenu) {
                mobileMenu.classList.remove('is-open');
            }

            if (userMenu) {
                userMenu.classList.remove('app-nav__user-menu--open');
            }

            // Reset dropdown displays for desktop
            const dropdowns = document.querySelectorAll('.app-nav__dropdown');
            dropdowns.forEach(dropdown => {
                dropdown.style.display = '';
            });
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAppNavigation);
    } else {
        initAppNavigation();
    }

    // Handle window resize
    window.addEventListener('resize', handleResize);

})();
