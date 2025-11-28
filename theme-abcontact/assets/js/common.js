/**
 * Common JavaScript
 *
 * Contains common functions and utilities for future interactions.
 * This file is loaded globally across all pages.
 *
 * @package theme-abcontact
 */

(function() {
    'use strict';

    /**
     * AbContact Common Module
     * Namespace for common theme functionality
     */
    window.AbContactCommon = window.AbContactCommon || {};

    /**
     * Initialize common functionality
     */
    AbContactCommon.init = function() {
        AbContactCommon.initImageZoom();
        AbContactCommon.initSmoothScroll();
    };

    /**
     * Initialize image zoom effect on elements with .img-zoom class
     * Adds keyboard accessibility for zoom effect
     */
    AbContactCommon.initImageZoom = function() {
        var zoomElements = document.querySelectorAll('.img-zoom');
        
        zoomElements.forEach(function(el) {
            // Add tabindex for keyboard accessibility if not already focusable
            if (!el.hasAttribute('tabindex') && el.tagName !== 'A' && el.tagName !== 'BUTTON') {
                el.setAttribute('tabindex', '0');
            }

            // Add focus styles that mimic hover
            el.addEventListener('focus', function() {
                this.classList.add('is-focused');
            });

            el.addEventListener('blur', function() {
                this.classList.remove('is-focused');
            });
        });
    };

    /**
     * Initialize smooth scrolling for anchor links
     */
    AbContactCommon.initSmoothScroll = function() {
        var smoothScrollLinks = document.querySelectorAll('a[href^="#"]:not([href="#"])');
        
        smoothScrollLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                var targetId = this.getAttribute('href').substring(1);
                var targetElement = document.getElementById(targetId);
                
                if (targetElement) {
                    e.preventDefault();
                    
                    // Get header height for offset
                    var header = document.querySelector('.site-header, .header');
                    var headerHeight = header ? header.offsetHeight : 0;
                    
                    var targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - headerHeight - 20;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });

                    // Set focus to target for accessibility
                    targetElement.setAttribute('tabindex', '-1');
                    targetElement.focus({ preventScroll: true });
                }
            });
        });
    };

    /**
     * Utility: Debounce function
     * @param {Function} func - Function to debounce
     * @param {number} wait - Wait time in milliseconds
     * @returns {Function} Debounced function
     */
    AbContactCommon.debounce = function(func, wait) {
        var timeout;
        return function() {
            var context = this;
            var args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                func.apply(context, args);
            }, wait);
        };
    };

    /**
     * Utility: Throttle function
     * @param {Function} func - Function to throttle
     * @param {number} limit - Limit time in milliseconds
     * @returns {Function} Throttled function
     */
    AbContactCommon.throttle = function(func, limit) {
        var inThrottle;
        return function() {
            var context = this;
            var args = arguments;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(function() {
                    inThrottle = false;
                }, limit);
            }
        };
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', AbContactCommon.init);
    } else {
        AbContactCommon.init();
    }

})();
