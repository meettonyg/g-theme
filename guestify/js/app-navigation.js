/**
 * Guestify App Navigation JavaScript
 *
 * Handles mobile menu toggle and other interactive elements.
 * (Active state is now handled server-side by PHP to prevent FOUC)
 */

(function() {
    'use strict';

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
        
        // Close all dropdowns first
        document.querySelectorAll('.app-nav__user-menu').forEach(menu => {
            menu.classList.remove('app-nav__user-menu--open');
        });
        
        // Toggle current dropdown
        if (!isOpen) {
            dropdown.classList.add('app-nav__user-menu--open');
        }
    };

    // Initialize app navigation when DOM is ready
    function initAppNavigation() {
        // Close mobile menu and user dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const mobileMenu = document.getElementById('mobileMenu');
            const mobileToggle = document.querySelector('.app-nav__mobile-toggle');
            const userDropdown = document.querySelector('.app-nav__user-dropdown');
            const userMenu = document.getElementById('userDropdownMenu');
            
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
        });

        // Handle escape key to close mobile menu and user dropdown
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const mobileMenu = document.getElementById('mobileMenu');
                const userMenu = document.getElementById('userDropdownMenu');
                
                if (mobileMenu) {
                    mobileMenu.classList.remove('is-open');
                }
                
                if (userMenu) {
                    userMenu.classList.remove('app-nav__user-menu--open');
                }
            }
        });
        
        // Handle dropdown menus for mobile
        handleMobileDropdowns();
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

    // Notification functionality (placeholder for future implementation)
    function initNotifications() {
        const notificationButton = document.querySelector('.app-nav__notifications');
        if (notificationButton) {
            notificationButton.addEventListener('click', function() {
                // Placeholder for notification panel toggle
                console.log('Notifications clicked - implement notification panel');
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

    // Initialize notifications
    initNotifications();

})();