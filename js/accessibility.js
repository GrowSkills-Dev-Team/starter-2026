/**
 * Pure Accessibility JavaScript for Starter Theme 2025
 * Only screen reader, ARIA, and accessibility-specific functionality
 */

(function($) {
    'use strict';

    // Initialize accessibility features when DOM is ready
    $(document).ready(function() {
        initSkipLinks();
        initAriaLiveRegions();
        initContrastChecker();
        initFocusManagement();
    });

    /**
     * Skip links functionality
     */
    function initSkipLinks() {
        $('.skip-link').on('click', function(e) {
            const target = $($(this).attr('href'));
            
            if (target.length) {
                e.preventDefault();
                
                // Make target focusable if it isn't already
                if (!target.attr('tabindex')) {
                    target.attr('tabindex', '-1');
                }
                
                // Focus the target
                target.focus();
                
                // Remove tabindex after focus (for screen readers)
                target.one('blur', function() {
                    $(this).removeAttr('tabindex');
                });
                
                // Announce skip action
                announceToScreenReader('Skipped to main content');
            }
        });
    }

    /**
     * Focus management for modal dialogs and screen readers
     */
    function initFocusManagement() {
        // Manage focus for modal dialogs and overlays
        $(document).on('focusin', function(e) {
            const focusedElement = $(e.target);
            const modal = focusedElement.closest('[role="dialog"]');
            
            if (modal.length) {
                trapFocusInModal(modal);
            }
        });
    }

    /**
     * Color contrast checker and warnings
     */
    function initContrastChecker() {
        $('.body-block').each(function() {
            const block = $(this);
            const bgColor = block.css('background-color');
            const textColor = block.css('color');
            
            if (bgColor && textColor) {
                const contrast = calculateContrastRatio(bgColor, textColor);
                
                if (contrast < 4.5) {
                    block.addClass('low-contrast');
                    
                    // Add warning for screen readers
                    if (!block.find('.contrast-warning').length) {
                        block.prepend(
                            '<span class="screen-reader-text contrast-warning">' +
                            'Warning: This content may have insufficient color contrast.' +
                            '</span>'
                        );
                    }
                }
            }
        });
    }

    /**
     * ARIA live regions for dynamic content
     */
    function initAriaLiveRegions() {
        // Create live region if it doesn't exist
        if (!$('#aria-live-region').length) {
            $('body').append('<div id="aria-live-region" aria-live="polite" aria-atomic="true" class="screen-reader-text"></div>');
        }
    }

    /**
     * Announce messages to screen readers
     */
    function announceToScreenReader(message) {
        const liveRegion = $('#aria-live-region');
        if (liveRegion.length) {
            liveRegion.text(message);
            
            // Clear message after announcement
            setTimeout(function() {
                liveRegion.empty();
            }, 1000);
        }
    }

    /**
     * Trap focus within modal dialogs
     */
    function trapFocusInModal(modal) {
        const focusableElements = modal.find('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
        const firstElement = focusableElements.first();
        const lastElement = focusableElements.last();

        modal.on('keydown', function(e) {
            if (e.key === 'Tab') {
                if (e.shiftKey) {
                    // Shift + Tab
                    if ($(document.activeElement).is(firstElement)) {
                        e.preventDefault();
                        lastElement.focus();
                    }
                } else {
                    // Tab
                    if ($(document.activeElement).is(lastElement)) {
                        e.preventDefault();
                        firstElement.focus();
                    }
                }
            }
            
            if (e.key === 'Escape') {
                modal.find('[data-dismiss]').click();
            }
        });
    }

    /**
     * Calculate color contrast ratio
     */
    function calculateContrastRatio(color1, color2) {
        const rgb1 = parseColor(color1);
        const rgb2 = parseColor(color2);
        
        if (!rgb1 || !rgb2) return 21; // Assume high contrast if can't parse
        
        const l1 = getRelativeLuminance(rgb1);
        const l2 = getRelativeLuminance(rgb2);
        
        const lighter = Math.max(l1, l2);
        const darker = Math.min(l1, l2);
        
        return (lighter + 0.05) / (darker + 0.05);
    }

    /**
     * Parse CSS color to RGB
     */
    function parseColor(color) {
        // Create temporary element to get computed color
        const temp = $('<div>').css('color', color).appendTo('body');
        const computedColor = temp.css('color');
        temp.remove();
        
        const match = computedColor.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
        
        if (match) {
            return {
                r: parseInt(match[1]),
                g: parseInt(match[2]),
                b: parseInt(match[3])
            };
        }
        
        return null;
    }

    /**
     * Calculate relative luminance for contrast ratio
     */
    function getRelativeLuminance(rgb) {
        const r = rgb.r / 255;
        const g = rgb.g / 255;
        const b = rgb.b / 255;
        
        const rLum = r <= 0.03928 ? r / 12.92 : Math.pow((r + 0.055) / 1.055, 2.4);
        const gLum = g <= 0.03928 ? g / 12.92 : Math.pow((g + 0.055) / 1.055, 2.4);
        const bLum = b <= 0.03928 ? b / 12.92 : Math.pow((b + 0.055) / 1.055, 2.4);
        
        return 0.2126 * rLum + 0.7152 * gLum + 0.0722 * bLum;
    }

    /**
     * Calculate color contrast ratio
     */
    function calculateContrastRatio(color1, color2) {
        const rgb1 = parseColor(color1);
        const rgb2 = parseColor(color2);
        
        if (!rgb1 || !rgb2) return 21; // Assume high contrast if can't parse
        
        const l1 = getRelativeLuminance(rgb1);
        const l2 = getRelativeLuminance(rgb2);
        
        const lighter = Math.max(l1, l2);
        const darker = Math.min(l1, l2);
        
        return (lighter + 0.05) / (darker + 0.05);
    }

    /**
     * Parse CSS color to RGB
     */
    function parseColor(color) {
        // Create temporary element to get computed color
        const temp = $('<div>').css('color', color).appendTo('body');
        const computedColor = temp.css('color');
        temp.remove();
        
        const match = computedColor.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
        
        if (match) {
            return {
                r: parseInt(match[1]),
                g: parseInt(match[2]),
                b: parseInt(match[3])
            };
        }
        
        return null;
    }

    /**
     * Calculate relative luminance for contrast ratio
     */
    function getRelativeLuminance(rgb) {
        const r = rgb.r / 255;
        const g = rgb.g / 255;
        const b = rgb.b / 255;
        
        const rLum = r <= 0.03928 ? r / 12.92 : Math.pow((r + 0.055) / 1.055, 2.4);
        const gLum = g <= 0.03928 ? g / 12.92 : Math.pow((g + 0.055) / 1.055, 2.4);
        const bLum = b <= 0.03928 ? b / 12.92 : Math.pow((b + 0.055) / 1.055, 2.4);
        
        return 0.2126 * rLum + 0.7152 * gLum + 0.0722 * bLum;
    }

    // Export accessibility functions for use elsewhere
    window.accessibilityHelpers = {
        announceToScreenReader: announceToScreenReader,
        trapFocusInModal: trapFocusInModal,
        calculateContrastRatio: calculateContrastRatio
    };

})(jQuery);
