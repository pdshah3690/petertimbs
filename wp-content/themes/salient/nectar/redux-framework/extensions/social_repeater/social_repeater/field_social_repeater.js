jQuery(document).ready(function($) {

    // Social Repeater functionality - only initialize once per field
    $('.redux-container-social_repeater > .redux-social-repeater-accordion').each(function() {
        var accordion = $(this);

        // Skip if already initialized
        if (accordion.data('initialized')) {
            return;
        }

        // Skip if this accordion is nested inside another accordion
        if (accordion.parents('.redux-social-repeater-accordion').length > 0) {
            return;
        }

        var maxItems = accordion.data('max-items') || 20;

                        // Add new social item - only bind once per accordion
        // Ensure this is the main accordion container, not a nested one
        if (accordion.parent().hasClass('redux-container-social_repeater')) {
            var accordionId = accordion.attr('id') || 'accordion-' + Math.random().toString(36).substr(2, 9);
            if (!accordion.attr('id')) {
                accordion.attr('id', accordionId);
            }

            if (!accordion.data('events-bound')) {
            accordion.find('> .redux-social-repeater-add > a.redux-social-repeater-add').off('click').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            // Get current count dynamically
            var currentCount = accordion.find('.redux-social-repeater-accordion-group').length;

            if (currentCount >= maxItems) {
                alert('Maximum number of social networks reached (' + maxItems + ')');
                return;
            }

        var addButton = $(this);
        var relId = addButton.attr('rel-id');
        var relName = addButton.attr('rel-name');
        var newContentTitle = accordion.data('new-content-title');

        // Clone the first accordion group
        var firstGroup = accordion.find('.redux-social-repeater-accordion-group').first();
        var newGroup = firstGroup.clone();

        // Remove any add buttons that might have been cloned
        newGroup.find('.redux-social-repeater-add').remove();

        // Update IDs and names
        newGroup.find('input, textarea').each(function() {
            var input = $(this);
            var oldId = input.attr('id');
            var oldName = input.attr('name');
            var inputType = input.attr('type');

            if (oldId) {
                input.attr('id', oldId.replace(/\d+$/, currentCount));
            }

            if (oldName) {
                input.attr('name', oldName.replace(/\[\d+\]/, '[' + currentCount + ']'));
            }

            // Clear values but preserve radio button values
            if (inputType !== 'radio') {
                input.val('');
            }
        });

        // Update ul ID to be unique
        newGroup.find('.redux-social-repeater-list').attr('id', 'custom_social_networks-ul-' + currentCount);

        // Update image elements
        newGroup.find('img').each(function() {
            var img = $(this);
            var oldId = img.attr('id');
            if (oldId) {
                img.attr('id', oldId.replace(/\d+$/, currentCount));
            }
            img.attr('src', '');
        });

        // Update button IDs
        newGroup.find('.media_upload_button').each(function() {
            var button = $(this);
            var oldId = button.attr('id');
            if (oldId) {
                button.attr('id', oldId.replace(/\d+$/, currentCount));
            }
        });

        newGroup.find('.remove-image').each(function() {
            var button = $(this);
            var oldId = button.attr('id');
            if (oldId) {
                button.attr('id', oldId.replace(/\d+$/, currentCount));
            }
        });

        // Update label 'for' attributes to match radio button IDs
        newGroup.find('label[for*="icon_type"]').each(function() {
            var label = $(this);
            var oldFor = label.attr('for');
            if (oldFor) {
                var newFor = oldFor.replace(/\d+$/, currentCount);
                label.attr('for', newFor);
            }
        });

        // Update header text
        newGroup.find('.redux-social-repeater-header').text(newContentTitle);

        // Hide screenshot initially
        newGroup.find('.screenshot').addClass('hide');
        newGroup.find('.remove-image').addClass('hide');

                        // Reset icon type selector to default (Icon Library)
        newGroup.find('input[name*="[icon_type]"]').prop('checked', false);
        newGroup.find('input[value="icon"]').prop('checked', true);

        // Ensure radio buttons are properly set up
        newGroup.find('input[name*="[icon_type]"]').each(function() {
            var radio = $(this);
            var value = radio.val();
            if (value === 'icon') {
                radio.prop('checked', true);
            } else {
                radio.prop('checked', false);
            }
        });

        // Set initial UI state for Icon Library (default)
        newGroup.find('.redux-social-repeater-icon-selector').removeClass('hide');
        newGroup.find('.redux_social_repeater_add_remove').addClass('hide');
        newGroup.find('.screenshot').addClass('hide');
        newGroup.find('.icon-option').removeClass('selected');
        newGroup.find('input[name*="[selected_icon]"]').val('');

        // Update button set styling to show Icon Library as active
        newGroup.find('.button-set-label').removeClass('active');
        newGroup.find('input[value="icon"]').next('.button-set-label').addClass('active');

        // Insert before the add button container (inside the accordion)
        accordion.find('> .redux-social-repeater-add').before(newGroup);

        // Initialize accordion on new group
        if (typeof $.fn.accordion !== 'undefined') {
            newGroup.accordion({
                collapsible: true,
                active: 0
            });
        }

        // Initialize scroll indicators for the new group
        setTimeout(function() {
            initializeScrollIndicators(newGroup);
        }, 100);

        currentCount++;

        // Hide add button if max reached
        if (currentCount >= maxItems) {
            addButton.hide();
        }
    });

    // Mark events as bound
    accordion.data('events-bound', true);
}

        // Remove social item
        accordion.on('click', '.redux-social-repeater-remove', function(e) {
            e.preventDefault();

            var removeButton = $(this);
            var group = removeButton.closest('.redux-social-repeater-accordion-group');

            // Confirm deletion
            if (confirm('Are you sure you want to delete this social network?')) {
                group.remove();
                currentCount--;

                // Show add button if we're below max
                if (currentCount < maxItems) {
                    accordion.find('.redux-social-repeater-add').show();
                }

                // Reindex remaining items
                reindexItems(accordion);
            }
        });

        // Media upload functionality
        accordion.on('click', '.media_upload_button', function(e) {
            e.preventDefault();

            var button = $(this);
            var group = button.closest('.redux-social-repeater-accordion-group');
            var imageId = group.find('.upload-id').attr('id');
            var imageUrl = group.find('.upload').attr('id');
            var imageThumb = group.find('.upload-thumbnail').attr('id');
            var imageHeight = group.find('.upload-height').attr('id');
            var imageWidth = group.find('.upload-width').attr('id');
            var screenshot = group.find('.screenshot');
            var removeButton = group.find('.remove-image');
            var imageElement = group.find('.redux-social-repeater-image');

            // Create media frame
            var frame = wp.media({
                title: 'Select Social Network Icon',
                button: {
                    text: 'Use this icon'
                },
                multiple: false
            });

            // When image selected
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();

                // Update hidden fields
                $('#' + imageId).val(attachment.id);
                $('#' + imageUrl).val(attachment.url);
                $('#' + imageThumb).val(attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url);
                $('#' + imageHeight).val(attachment.height);
                $('#' + imageWidth).val(attachment.width);

                // Update image display
                imageElement.attr('src', attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url);
                screenshot.removeClass('hide');
                removeButton.removeClass('hide');
            });

            frame.open();
        });

        // Click on uploaded image to open media library
        accordion.on('click', '.redux-social-repeater-image', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var imageElement = $(this);
            var group = imageElement.closest('.redux-social-repeater-accordion-group');
            var imageId = group.find('.upload-id').attr('id');
            var imageUrl = group.find('.upload').attr('id');
            var imageThumb = group.find('.upload-thumbnail').attr('id');
            var imageHeight = group.find('.upload-height').attr('id');
            var imageWidth = group.find('.upload-width').attr('id');
            var screenshot = group.find('.screenshot');
            var removeButton = group.find('.remove-image');

            // Create media frame
            var frame = wp.media({
                title: 'Select Social Network Icon',
                button: {
                    text: 'Use this icon'
                },
                multiple: false
            });

            // When image selected
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();

                // Update hidden fields
                $('#' + imageId).val(attachment.id);
                $('#' + imageUrl).val(attachment.url);
                $('#' + imageThumb).val(attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url);
                $('#' + imageHeight).val(attachment.height);
                $('#' + imageWidth).val(attachment.width);

                // Update image display
                imageElement.attr('src', attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url);
                screenshot.removeClass('hide');
                removeButton.removeClass('hide');
            });

            frame.open();
        });

        // Remove image
        accordion.on('click', '.remove-image', function(e) {
            e.preventDefault();

            var button = $(this);
            var group = button.closest('.redux-social-repeater-accordion-group');
            var imageId = group.find('.upload-id').attr('id');
            var imageUrl = group.find('.upload').attr('id');
            var imageThumb = group.find('.upload-thumbnail').attr('id');
            var imageHeight = group.find('.upload-height').attr('id');
            var imageWidth = group.find('.upload-width').attr('id');
            var screenshot = group.find('.screenshot');
            var imageElement = group.find('.redux-social-repeater-image');

            // Clear values
            $('#' + imageId).val('');
            $('#' + imageUrl).val('');
            $('#' + imageThumb).val('');
            $('#' + imageHeight).val('');
            $('#' + imageWidth).val('');

            // Hide image
            imageElement.attr('src', '');
            screenshot.addClass('hide');
            button.addClass('hide');
        });

                        // Accordion toggle functionality
        accordion.on('click', '.redux-social-repeater-accordion-group h3', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var header = $(this);
            var group = header.closest('.redux-social-repeater-accordion-group');
            var content = group.find('fieldset > div');

            // Toggle the content
            content.slideToggle(300, function() {
                // After animation completes, check scroll indicators and scroll selected icon into view
                if (content.is(':visible')) {
                    setTimeout(function() {
                        initializeScrollIndicators(group);
                    }, 100);
                }
            });

            // Toggle classes for dashicons and styling
            header.toggleClass('ui-accordion-header-active');
            content.toggleClass('ui-accordion-content-active');
            group.toggleClass('active');
        });

                                                                // Icon type selector functionality - using event delegation
        accordion.on('change', 'input[name*="[icon_type]"]', function() {
            var radio = $(this);
            var group = radio.closest('.redux-social-repeater-accordion-group');
            var iconType = radio.val();

            // Update button set styling
            group.find('.button-set-label').removeClass('active');
            radio.next('.button-set-label').addClass('active');

            if (iconType === 'custom') {
                group.find('.redux_social_repeater_add_remove').removeClass('hide');
                group.find('.redux-social-repeater-icon-selector').addClass('hide');

                // Only show screenshot if there's an actual image uploaded
                var imageUrl = group.find('input[name*="[icon]"]').val();
                var imageThumb = group.find('input[name*="[thumb]"]').val();
                if (imageUrl && imageThumb) {
                    group.find('.screenshot').removeClass('hide');
                }
            } else if (iconType === 'icon') {
                group.find('.redux_social_repeater_add_remove').addClass('hide');
                group.find('.redux-social-repeater-icon-selector').removeClass('hide');
                group.find('.screenshot').addClass('hide');

                // Initialize scroll indicators when icon selector becomes visible
                setTimeout(function() {
                    initializeScrollIndicators(group);
                }, 200);
            }
        });

                                        // Fallback click handler for icon type selector
        accordion.on('click', 'input[name*="[icon_type]"]', function() {
            var radio = $(this);
            var group = radio.closest('.redux-social-repeater-accordion-group');
            var iconType = radio.val();

            // Update button set styling
            group.find('.button-set-label').removeClass('active');
            radio.next('.button-set-label').addClass('active');

            if (iconType === 'custom') {
                group.find('.redux_social_repeater_add_remove').removeClass('hide');
                group.find('.redux-social-repeater-icon-selector').addClass('hide');

                // Only show screenshot if there's an actual image uploaded
                var imageUrl = group.find('input[name*="[icon]"]').val();
                var imageThumb = group.find('input[name*="[thumb]"]').val();
                if (imageUrl && imageThumb) {
                    group.find('.screenshot').removeClass('hide');
                }
            } else if (iconType === 'icon') {
                group.find('.redux_social_repeater_add_remove').addClass('hide');
                group.find('.redux-social-repeater-icon-selector').removeClass('hide');
                group.find('.screenshot').addClass('hide');

                // Initialize scroll indicators when icon selector becomes visible
                setTimeout(function() {
                    initializeScrollIndicators(group);
                }, 200);
            }
        });

        // Click handler for button set labels
        accordion.on('click', '.button-set-label', function(e) {
            e.preventDefault();
            var label = $(this);
            var group = label.closest('.redux-social-repeater-accordion-group');
            var forId = label.attr('for');

            if (forId) {
                var radio = group.find('#' + forId);
                if (radio.length) {
                    radio.prop('checked', true).trigger('change');
                }
            }
        });

                        // Initialize selected icons on page load and ensure all content starts hidden
        accordion.find('.redux-social-repeater-accordion-group').each(function() {
            var group = $(this);
            var content = group.find('fieldset > div');

            // Hide all content initially
            content.hide();

            // Set up initial state based on icon type
            var iconType = group.find('input[name*="[icon_type]"]:checked').val();
            if (iconType === 'custom') {
                group.find('.redux_social_repeater_add_remove').removeClass('hide');
                group.find('.redux-social-repeater-icon-selector').addClass('hide');
            } else {
                group.find('.redux_social_repeater_add_remove').addClass('hide');
                group.find('.redux-social-repeater-icon-selector').removeClass('hide');
            }

            var selectedIconValue = group.find('input[name*="[selected_icon]"]').val();

            if (selectedIconValue) {
                var selectedOption = group.find('.icon-option[data-icon-class="' + selectedIconValue + '"]');
                if (selectedOption.length) {
                    selectedOption.addClass('selected');
                }
            }

            // Initialize scroll indicators for icon grids
            initializeScrollIndicators(group);
        });

                                // Function to initialize scroll indicators
        function initializeScrollIndicators(group) {
            var iconGrid = group.find('.icon-grid');
            if (iconGrid.length) {
                // Check if icon selector is visible
                var iconSelector = group.find('.redux-social-repeater-icon-selector');
                if (!iconSelector.hasClass('hide')) {
                    // Icon selector is visible, check scrollability and scroll selected icon into view
                    setTimeout(function() {
                        updateScrollIndicator(iconGrid);
                        scrollSelectedIconIntoView(iconGrid);
                    }, 100);
                }

                // Update on scroll
                iconGrid.on('scroll', function() {
                    updateScrollIndicator($(this));
                });

                // Update on window resize
                $(window).on('resize', function() {
                    updateScrollIndicator(iconGrid);
                });
            }
        }

                        // Function to update scroll indicator
        function updateScrollIndicator(iconGrid) {
            var scrollTop = iconGrid.scrollTop();
            var scrollHeight = iconGrid[0].scrollHeight;
            var clientHeight = iconGrid[0].clientHeight;

            // Check if content is scrollable
            var isScrollable = scrollHeight > clientHeight;

            if (isScrollable && scrollTop === 0) {
                // Content is scrollable and at top - show gradient
                iconGrid.addClass('scrollable');
            } else {
                // Either not scrollable or scrolled - hide gradient
                iconGrid.removeClass('scrollable');
            }
        }

        // Function to scroll selected icon into view
        function scrollSelectedIconIntoView(iconGrid) {
            var selectedIcon = iconGrid.find('.icon-option.selected');
            if (selectedIcon.length) {
                var iconGridElement = iconGrid[0];
                var selectedIconElement = selectedIcon[0];

                // Calculate the position of the selected icon
                var iconTop = selectedIconElement.offsetTop;
                var iconHeight = selectedIconElement.offsetHeight;
                var gridTop = iconGridElement.scrollTop;
                var gridHeight = iconGridElement.clientHeight;

                // Check if the icon is not fully visible
                if (iconTop < gridTop || iconTop + iconHeight > gridTop + gridHeight) {
                    // Scroll to center the icon in the viewport
                    var scrollTo = iconTop - (gridHeight / 2) + (iconHeight / 2);

                    // Ensure we don't scroll beyond the bounds
                    scrollTo = Math.max(0, Math.min(scrollTo, iconGridElement.scrollHeight - gridHeight));

                    // Smooth scroll to the position
                    iconGridElement.scrollTo({
                        top: scrollTo,
                        behavior: 'smooth'
                    });
                }
            }
        }

        // Icon selection functionality
        accordion.on('click', '.icon-option', function() {
            var option = $(this);
            var group = option.closest('.redux-social-repeater-accordion-group');
            var iconValue = option.data('icon');
            var iconClass = option.data('icon-class');
            var iconName = option.find('span').text();

            // Remove selected class from all options in this group
            group.find('.icon-option').removeClass('selected');

            // Add selected class to clicked option
            option.addClass('selected');

            // Update hidden input
            group.find('input[name*="[selected_icon]"]').val(iconClass);

            // Auto-populate the Network Name field
            var nameInput = group.find('.social-name');
            nameInput.val(iconName);

            // Update header text
            group.find('.redux-social-repeater-header').text(iconName);
        });

        // Update header text when name changes
        accordion.on('input', '.social-name', function() {
            var input = $(this);
            var group = input.closest('.redux-social-repeater-accordion-group');
            var header = group.find('.redux-social-repeater-header');
            var value = input.val();

            if (value) {
                header.text(value);
            } else {
                header.text(accordion.data('new-content-title'));
            }
        });

        // Initialize accordion if jQuery UI is available
        if (typeof $.fn.accordion !== 'undefined') {
            accordion.accordion({
                collapsible: true,
                active: false
            });
        }

        // Initialize sortable functionality for reordering
        if (typeof $.fn.sortable !== 'undefined') {
            accordion.sortable({
                items: '.redux-social-repeater-accordion-group',
                handle: 'h3',
                placeholder: 'redux-social-repeater-placeholder',
                tolerance: 'pointer',
                start: function(event, ui) {
                    // Sortable started
                },
                update: function(event, ui) {
                    // Reindex items after reordering
                    setTimeout(function() {
                        reindexItems(accordion);
                    }, 100);
                }
            });
        } else {
            // Custom sortable implementation
            accordion.find('.redux-social-repeater-accordion-group h3').on('mousedown', function(e) {
                if (e.which !== 1) return; // Only left mouse button

                var header = $(this);
                var group = header.closest('.redux-social-repeater-accordion-group');
                var accordion = group.parent();
                var startY = e.pageY;
                var startIndex = group.index();

                // Create helper
                var helper = group.clone();
                helper.css({
                    position: 'fixed',
                    top: group.offset().top,
                    left: group.offset().left,
                    width: group.outerWidth(),
                    zIndex: 1000,
                    opacity: 0.8,
                    transform: 'rotate(2deg)',
                    boxShadow: '0 5px 15px rgba(0,0,0,0.2)'
                });
                $('body').append(helper);

                // Hide original
                group.css('opacity', 0.3);

                // Mouse move handler
                var moveHandler = function(e) {
                    helper.css('top', e.pageY - startY + group.offset().top);

                    // Find drop position
                    var currentY = e.pageY;
                    var groups = accordion.find('.redux-social-repeater-accordion-group');
                    var newIndex = startIndex;

                    groups.each(function(index) {
                        var groupTop = $(this).offset().top;
                        var groupHeight = $(this).outerHeight();
                        if (currentY > groupTop + groupHeight / 2) {
                            newIndex = index;
                        }
                    });

                    // Update placeholder
                    accordion.find('.redux-social-repeater-placeholder').remove();
                    if (newIndex !== startIndex) {
                        groups.eq(newIndex).after('<div class="redux-social-repeater-placeholder"></div>');
                    }
                };

                // Mouse up handler
                var upHandler = function(e) {
                    $(document).off('mousemove', moveHandler).off('mouseup', upHandler);

                    // Remove helper and placeholder
                    helper.remove();
                    accordion.find('.redux-social-repeater-placeholder').remove();
                    group.css('opacity', 1);

                    // Move to new position
                    var groups = accordion.find('.redux-social-repeater-accordion-group');
                    var newIndex = groups.index(group);
                    if (newIndex !== startIndex) {
                        if (newIndex > startIndex) {
                            groups.eq(newIndex).after(group);
                        } else {
                            groups.eq(newIndex).before(group);
                        }
                        setTimeout(function() {
                            reindexItems(accordion);
                        }, 100);
                    }
                };

                $(document).on('mousemove', moveHandler).on('mouseup', upHandler);
            });
        }

        // Mark as initialized
        accordion.data('initialized', true);

        // Final initialization after everything is set up
        setTimeout(function() {
            accordion.find('.redux-social-repeater-accordion-group').each(function() {
                var group = $(this);
                var content = group.find('fieldset > div');
                if (content.is(':visible')) {
                    initializeScrollIndicators(group);
                }
            });
        }, 500);

        // Ensure proper reindexing before form submission
        accordion.closest('form').on('submit', function(e) {
            // Reindex items one more time before submission to ensure proper order
            setTimeout(function() {
                reindexItems(accordion);
            }, 50);
        });
        }
    });

        // Function to reindex items after deletion
    function reindexItems(accordion) {
        // Store all form data before reindexing
        var allFormData = {};

        accordion.find('.redux-social-repeater-accordion-group').each(function(groupIndex) {
            var group = $(this);
            allFormData[groupIndex] = {};

            group.find('input, textarea').each(function() {
                var input = $(this);
                var inputType = input.attr('type');
                var inputName = input.attr('name');
                var inputValue = input.val();

                if (inputType === 'radio') {
                    if (input.is(':checked')) {
                        // Extract the field name without the index
                        var fieldName = inputName.replace(/\[\d+\]/, '').split('[').pop().replace(']', '');
                        allFormData[groupIndex][fieldName] = inputValue;
                    }
                } else {
                    // Extract the field name without the index
                    var fieldName = inputName.replace(/\[\d+\]/, '').split('[').pop().replace(']', '');
                    allFormData[groupIndex][fieldName] = inputValue;
                }
            });
        });

        // Now reindex and restore the data
        accordion.find('.redux-social-repeater-accordion-group').each(function(index) {
            var group = $(this);
            var groupData = allFormData[index];

            // Update input names and IDs
            group.find('input, textarea').each(function() {
                var input = $(this);
                var oldName = input.attr('name');
                var oldId = input.attr('id');

                if (oldName) {
                    input.attr('name', oldName.replace(/\[\d+\]/, '[' + index + ']'));
                }

                if (oldId) {
                    input.attr('id', oldId.replace(/\d+$/, index));
                }
            });

            // Restore form data
            if (groupData) {
                group.find('input, textarea').each(function() {
                    var input = $(this);
                    var inputType = input.attr('type');
                    var inputName = input.attr('name');

                    // Extract the field name
                    var fieldName = inputName.replace(/\[\d+\]/, '').split('[').pop().replace(']', '');

                    if (inputType === 'radio') {
                        if (groupData[fieldName] && input.val() === groupData[fieldName]) {
                            input.prop('checked', true);
                        } else {
                            input.prop('checked', false);
                        }
                    } else {
                        if (groupData[fieldName] !== undefined) {
                            input.val(groupData[fieldName]);
                        }
                    }
                });
            }

            // Update image elements
            group.find('img').each(function() {
                var img = $(this);
                var oldId = img.attr('id');

                if (oldId) {
                    img.attr('id', oldId.replace(/\d+$/, index));
                }
            });

            // Update button IDs
            group.find('.media_upload_button').each(function() {
                var button = $(this);
                var oldId = button.attr('id');

                if (oldId) {
                    button.attr('id', oldId.replace(/\d+$/, index));
                }
            });

            group.find('.remove-image').each(function() {
                var button = $(this);
                var oldId = button.attr('id');

                if (oldId) {
                    button.attr('id', oldId.replace(/\d+$/, index));
                }
            });

            // Update label 'for' attributes to match radio button IDs
            group.find('label[for*="icon_type"]').each(function() {
                var label = $(this);
                var oldFor = label.attr('for');
                if (oldFor) {
                    var newFor = oldFor.replace(/\d+$/, index);
                    label.attr('for', newFor);
                }
            });

            // Update ul ID to be unique
            group.find('.redux-social-repeater-list').attr('id', 'custom_social_networks-ul-' + index);

            // Update button set styling based on current radio button state
            var checkedRadio = group.find('input[name*="[icon_type]"]:checked');
            if (checkedRadio.length) {
                group.find('.button-set-label').removeClass('active');
                checkedRadio.next('.button-set-label').addClass('active');
            }

            // Update icon selector visibility based on current state
            var iconType = group.find('input[name*="[icon_type]"]:checked').val();
            if (iconType === 'custom') {
                group.find('.redux_social_repeater_add_remove').removeClass('hide');
                group.find('.redux-social-repeater-icon-selector').addClass('hide');

                // Show screenshot if there's an image
                var imageUrl = group.find('input[name*="[icon]"]').val();
                var imageThumb = group.find('input[name*="[thumb]"]').val();
                if (imageUrl && imageThumb) {
                    group.find('.screenshot').removeClass('hide');
                }
            } else if (iconType === 'icon') {
                group.find('.redux_social_repeater_add_remove').addClass('hide');
                group.find('.redux-social-repeater-icon-selector').removeClass('hide');
                group.find('.screenshot').addClass('hide');
            }

            // Update selected icon styling
            var selectedIconValue = group.find('input[name*="[selected_icon]"]').val();
            if (selectedIconValue) {
                group.find('.icon-option').removeClass('selected');
                var selectedOption = group.find('.icon-option[data-icon-class="' + selectedIconValue + '"]');
                if (selectedOption.length) {
                    selectedOption.addClass('selected');
                }
            }
        });
    }
});