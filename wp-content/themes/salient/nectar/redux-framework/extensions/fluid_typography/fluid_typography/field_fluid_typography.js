(function($) {
    "use strict";

    function validateCSSExpression(value) {
        // Empty is valid (optional field)
        if (!value || !value.trim()) {
            return true;
        }

        var cleanValue = value.trim();

        // Use browser's CSS parser to validate
        try {
            // Create a temporary element and test the CSS value
            var testEl = document.createElement('div');
            var originalFontSize = testEl.style.fontSize;
            testEl.style.fontSize = cleanValue;

            // Check if the browser actually applied the style
            var appliedValue = testEl.style.fontSize;

            // If the browser didn't apply the style (it's empty or unchanged), it's invalid
            if (!appliedValue || appliedValue === originalFontSize) {
                return false;
            }

            return true;
        } catch (e) {
            // If there's any error parsing, it's invalid
            return false;
        }
    }

    function initFluidTypographyFields() {
        $('.redux-fluid-typography-container').each(function() {
            var field = $(this);

            // Skip if already initialized
            if (field.hasClass('fluid-typography-initialized')) {
                return;
            }

            field.addClass('fluid-typography-initialized');

            // Initialize device switching
            initDeviceSwitching(field);

            // Validate existing values on page load
            field.find('.redux-fluid-typography-custom').each(function() {
                var value = $(this).val().trim();
                if (value) {
                    var isValid = validateCSSExpression(value);
                    if (!isValid) {
                        $(this).addClass('redux-fluid-typography-warning');
                    }
                }
            });

            // Add real-time preview functionality
            field.find('.redux-fluid-typography-input').on('input change', function() {
                var currentDevice = field.find('.redux-fluid-typography-device-btn.active').data('device');
                var minValue = field.find('input[name*="[' + currentDevice + '][min]"]').val();
                var maxValue = field.find('input[name*="[' + currentDevice + '][max]"]').val();
                var customValue = field.find('input[name*="[' + currentDevice + '][custom]"]').val();

                // Generate CSS preview
                var cssPreview = '';
                if (minValue && maxValue) {
                    cssPreview = `clamp(${minValue}, ${customValue || '1rem + 2vw'}, ${maxValue})`;
                }

                // No preview for now.
                // console.log('Fluid Typography CSS:', cssPreview);
            });

            // Add validation for CSS expressions
            field.find('.redux-fluid-typography-custom').on('blur input', function() {
                var value = $(this).val().trim();

                if (value) {
                    var isValid = validateCSSExpression(value);
                    if (!isValid) {
                        // Add a subtle warning if the custom value has invalid CSS syntax
                        $(this).addClass('redux-fluid-typography-warning');
                    } else {
                        // Remove warning if value becomes valid
                        $(this).removeClass('redux-fluid-typography-warning');
                    }
                } else {
                    // Remove warning if field is empty
                    $(this).removeClass('redux-fluid-typography-warning');
                }
            });
        });
    }

    function initDeviceSwitching(field) {
        // Device switcher functionality
        field.find('.redux-fluid-typography-device-btn').on('click', function() {
            var device = $(this).data('device');

            // Update button states
            field.find('.redux-fluid-typography-device-btn').removeClass('active');
            $(this).addClass('active');

            // Show/hide device sections
            field.find('.redux-fluid-typography-device-section').hide();
            field.find('.redux-fluid-typography-device-section[data-device="' + device + '"]').show();
        });
    }

    // Initialize on document ready
    $(document).ready(function() {
        initFluidTypographyFields();
    });

    // Reinitialize on section change
    $(document).on('redux-section-change', function() {
        initFluidTypographyFields();
    });

    // Reinitialize on panel change
    $(document).on('redux-panel-change', function() {
        initFluidTypographyFields();
    });

})(jQuery);
