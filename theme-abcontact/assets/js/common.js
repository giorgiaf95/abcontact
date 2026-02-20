/**
 * Common JS - Global JavaScript functions for the theme.
 *
 * This file contains minimal JavaScript functions for hover effects,
 * animations, and other common functionality used across the site.
 *
 * @package theme-abcontact
 */

(function () {
    'use strict';

    /**
     * DOM Ready wrapper
     * @param {Function} fn - Callback function to execute when DOM is ready
     */
    function domReady(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    /**
     * Throttle function for performance optimization
     * @param {Function} func - Function to throttle
     * @param {number} limit - Time limit in milliseconds
     * @returns {Function} - Throttled function
     */
    function throttle(func, limit) {
        let inThrottle;
        return function (...args) {
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(function () {
                    inThrottle = false;
                }, limit);
            }
        };
    }

    /**
     * Debounce function for performance optimization
     * @param {Function} func - Function to debounce
     * @param {number} wait - Wait time in milliseconds
     * @returns {Function} - Debounced function
     */
    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            const context = this;
            clearTimeout(timeout);
            timeout = setTimeout(function () {
                func.apply(context, args);
            }, wait);
        };
    }

    /**
     * Add hover effects to elements with data-hover attribute
     */
    function initHoverEffects() {
        var hoverElements = document.querySelectorAll('[data-hover]');

        hoverElements.forEach(function (element) {
            var hoverClass = element.getAttribute('data-hover') || 'is-hovered';

            element.addEventListener('mouseenter', function () {
                this.classList.add(hoverClass);
            });

            element.addEventListener('mouseleave', function () {
                this.classList.remove(hoverClass);
            });
        });
    }

    /**
     * Smooth scroll for anchor links
     */
    function initSmoothScroll() {
        var anchorLinks = document.querySelectorAll('a[href^="#"]');

        anchorLinks.forEach(function (link) {
            link.addEventListener('click', function (e) {
                var targetId = this.getAttribute('href');

                if (targetId === '#' || targetId === '') {
                    return;
                }

                var targetElement = document.querySelector(targetId);

                if (targetElement) {
                    e.preventDefault();

                    var headerOffset = 80;
                    var elementPosition = targetElement.getBoundingClientRect().top;
                    var offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
    }

    /**
     * Add focus visible polyfill behavior
     */
    function initFocusVisible() {
        var hadKeyboardEvent = false;

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Tab') {
                hadKeyboardEvent = true;
            }
        });

        document.addEventListener('mousedown', function () {
            hadKeyboardEvent = false;
        });

        document.addEventListener('focusin', function (e) {
            if (hadKeyboardEvent && e.target) {
                e.target.setAttribute('data-focus-visible', '');
            }
        });

        document.addEventListener('focusout', function (e) {
            if (e.target) {
                e.target.removeAttribute('data-focus-visible');
            }
        });
    }

    /**
     * Initialize all common functionality
     */
    function init() {
        initHoverEffects();
        initSmoothScroll();
        initFocusVisible();
    }

    // Initialize when DOM is ready
    domReady(init);

    // Expose utility functions globally for other scripts
    window.abcontactUtils = {
        throttle: throttle,
        debounce: debounce,
        domReady: domReady
    };
})();
