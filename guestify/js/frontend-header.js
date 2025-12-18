/**
 * Frontend Header Scripts
 * Mobile menu toggle functionality
 *
 * @package Guestify
 */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const menuToggle = document.querySelector('.frontend-header__menu-toggle');
        const nav = document.querySelector('.frontend-header__nav');

        if (!menuToggle || !nav) {
            return;
        }

        // Toggle menu on button click
        menuToggle.addEventListener('click', function () {
            const isOpen = menuToggle.getAttribute('aria-expanded') === 'true';
            menuToggle.setAttribute('aria-expanded', !isOpen);
            nav.setAttribute('data-menu-open', !isOpen);
        });

        // Close menu when clicking outside
        document.addEventListener('click', function (event) {
            const isClickInside = nav.contains(event.target);
            if (!isClickInside && nav.getAttribute('data-menu-open') === 'true') {
                menuToggle.setAttribute('aria-expanded', 'false');
                nav.setAttribute('data-menu-open', 'false');
            }
        });

        // Close menu on escape key
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && nav.getAttribute('data-menu-open') === 'true') {
                menuToggle.setAttribute('aria-expanded', 'false');
                nav.setAttribute('data-menu-open', 'false');
                menuToggle.focus();
            }
        });

        // Handle window resize - close mobile menu if resized to desktop
        let resizeTimer;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                if (window.innerWidth > 992) {
                    menuToggle.setAttribute('aria-expanded', 'false');
                    nav.setAttribute('data-menu-open', 'false');
                }
            }, 100);
        });
    });
})();
