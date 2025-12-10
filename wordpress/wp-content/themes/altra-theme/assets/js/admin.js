/**
 * Altra Admin JavaScript
 * Handles admin functionality for meta boxes
 */

jQuery(document).ready(function($) {

    /**
     * PROJECT DETAILS FIELDS
     * Handles sortable fields with drag & drop reordering
     */
    function initFieldsSortable() {
        $('.altra-fields-sortable').sortable({
            items: '.altra-field-row',
            handle: '.field-drag-handle',
            cursor: 'move',
            opacity: 0.7,
            placeholder: 'altra-field-placeholder',
            update: function() {
                updateFieldsOrder();
            },
            start: function(_event, ui) {
                ui.placeholder.height(ui.item.height());
            }
        });
    }

    // Update field order after drag & drop
    function updateFieldsOrder() {
        var keys = [];
        $('.altra-fields-sortable .altra-field-row').each(function() {
            keys.push($(this).data('field-key'));
        });

        var newOrder = keys.join(',');
        $('#altra_fields_order').val(newOrder);

        console.log('Fields reordered:', newOrder);
    }

    // Initialize sortable on page load
    if ($('.altra-fields-sortable').length) {
        initFieldsSortable();
        // Set initial order value
        updateFieldsOrder();
    }

    /**
     * PROJECT GALLERY
     * Handles media library integration for project gallery
     */

    /**
     * DRAG & DROP REORDERING
     * Enable sortable gallery with visual feedback
     */
    function initGallerySortable() {
        $('.altra-gallery-sortable').sortable({
            items: '.gallery-image',
            handle: '.drag-handle', // Only drag by the handle icon
            cursor: 'move',
            opacity: 0.7,
            placeholder: 'gallery-image-placeholder',
            update: function() {
                // Update hidden fields with new order
                updateGalleryOrder();
            },
            start: function(_event, ui) {
                ui.placeholder.height(ui.item.height());
            }
        });
    }

    // Update gallery order after drag & drop
    function updateGalleryOrder() {
        var ids = [];
        $('.altra-gallery-preview .gallery-image').each(function() {
            ids.push($(this).data('id'));
        });

        var newValue = ids.join(',');
        $('#altra_project_gallery_hidden').val(newValue);
        $('#altra_project_gallery_display').val(newValue);

        console.log('Gallery reordered:', newValue);
    }

    // Add images to gallery
    $('.altra-add-gallery').on('click', function(e) {
        e.preventDefault();

        var galleryFrame = wp.media({
            title: altraAdminData.selectImages,
            button: {
                text: altraAdminData.addToGallery
            },
            multiple: true
        });

        galleryFrame.on('select', function() {
            var selection = galleryFrame.state().get('selection');
            var $hiddenField = $('#altra_project_gallery_hidden');
            var $displayField = $('#altra_project_gallery_display');
            var ids = $hiddenField.val();
            var idsArray = ids ? ids.split(',').filter(function(id) { return id.trim() !== ''; }) : [];

            // Destroy sortable before adding new images
            if ($('.altra-gallery-sortable').hasClass('ui-sortable')) {
                $('.altra-gallery-sortable').sortable('destroy');
            }

            selection.each(function(attachment) {
                attachment = attachment.toJSON();
                idsArray.push(attachment.id);

                $('.altra-gallery-preview').append(
                    '<div class="gallery-image" data-id="' + attachment.id + '">' +
                    '<span class="dashicons dashicons-move drag-handle" title="Drag to reorder"></span>' +
                    '<img src="' + attachment.sizes.thumbnail.url + '">' +
                    '<button type="button" class="button button-small remove-gallery-image">×</button>' +
                    '</div>'
                );
            });

            var newValue = idsArray.join(',');

            // Update both the hidden field (for form submission) and display field (for user visibility)
            $hiddenField.val(newValue);
            $displayField.val(newValue);

            console.log('Gallery updated:', newValue);

            // Re-initialize sortable after adding images
            setTimeout(function() {
                initGallerySortable();
            }, 100);
        });

        galleryFrame.open();
    });

    // Remove image from gallery
    $(document).on('click', '.remove-gallery-image', function(e) {
        e.preventDefault();
        var $image = $(this).closest('.gallery-image');
        var id = $image.data('id');
        var $hiddenField = $('#altra_project_gallery_hidden');
        var $displayField = $('#altra_project_gallery_display');
        var ids = $hiddenField.val().split(',');

        ids = ids.filter(function(item) {
            return item != id;
        });

        var newValue = ids.join(',');

        // Update both fields
        $hiddenField.val(newValue);
        $displayField.val(newValue);

        $image.remove();
    });

    // Initialize sortable on page load
    if ($('.altra-gallery-sortable').length) {
        initGallerySortable();
    }

    /**
     * PROJECT WIDTH PREVIEW
     * Real-time visual preview for width selection
     */
    $('input[name="altra_project_width"]').on('change', function() {
        var width = $(this).val();
        var preview = $('#width-preview');

        switch(width) {
            case 'small':
                preview.css('width', '33.33%');
                break;
            case 'medium':
                preview.css('width', '50%');
                break;
            case 'large':
                preview.css('width', '100%');
                break;
        }
    });

    // Initialize preview on page load
    $('input[name="altra_project_width"]:checked').trigger('change');

});
