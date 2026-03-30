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
     * Handles media library integration — stores items as JSON array
     * Each item: {type:'image',id:42} or {type:'video',url:'...',orientation:'landscape'}
     */

    function initGallerySortable() {
        $('.altra-gallery-sortable').sortable({
            items: '.gallery-item',
            handle: '.drag-handle',
            cursor: 'move',
            opacity: 0.7,
            placeholder: 'gallery-item-placeholder',
            update: function() {
                updateGalleryOrder();
            },
            start: function(_event, ui) {
                ui.placeholder.height(ui.item.height());
            }
        });
    }

    function updateGalleryOrder() {
        var items = [];
        $('.altra-gallery-preview .gallery-item').each(function() {
            var type = $(this).data('type');
            if (type === 'image') {
                items.push({ type: 'image', id: parseInt($(this).data('id'), 10) });
            } else if (type === 'video') {
                items.push({
                    type: 'video',
                    url: $(this).data('url'),
                    orientation: $(this).data('orientation') || 'landscape'
                });
            }
        });
        $('#altra_project_gallery_hidden').val(JSON.stringify(items));
    }

    // Add images to gallery
    $('.altra-add-gallery').on('click', function(e) {
        e.preventDefault();

        var galleryFrame = wp.media({
            title: altraAdminData.selectImages,
            button: { text: altraAdminData.addToGallery },
            multiple: true
        });

        galleryFrame.on('select', function() {
            var selection = galleryFrame.state().get('selection');

            if ($('.altra-gallery-sortable').hasClass('ui-sortable')) {
                $('.altra-gallery-sortable').sortable('destroy');
            }

            selection.each(function(attachment) {
                attachment = attachment.toJSON();
                var thumbUrl = attachment.sizes && attachment.sizes.thumbnail
                    ? attachment.sizes.thumbnail.url
                    : attachment.url;

                $('.altra-gallery-preview').append(
                    '<div class="gallery-item gallery-image" data-type="image" data-id="' + attachment.id + '">' +
                    '<span class="dashicons dashicons-move drag-handle" title="Drag to reorder"></span>' +
                    '<img src="' + thumbUrl + '">' +
                    '<button type="button" class="button button-small remove-gallery-item">\u00d7</button>' +
                    '</div>'
                );
            });

            updateGalleryOrder();
            setTimeout(function() { initGallerySortable(); }, 100);
        });

        galleryFrame.open();
    });

    // Show/hide Add Vimeo form
    $('.altra-add-video-gallery').on('click', function(e) {
        e.preventDefault();
        $('#altra-add-video-form').show();
    });

    $('.altra-cancel-add-video').on('click', function(e) {
        e.preventDefault();
        $('#altra-add-video-form').hide();
        $('#altra-video-url-input').val('');
    });

    // Confirm add Vimeo video
    $('.altra-confirm-add-video').on('click', function(e) {
        e.preventDefault();
        var url = $('#altra-video-url-input').val().trim();
        var orientation = $('input[name="altra_new_video_orientation"]:checked').val();

        if (!url || !url.match(/vimeo\.com\/\d+/)) {
            alert(altraAdminData.invalidVimeoUrl);
            return;
        }

        if ($('.altra-gallery-sortable').hasClass('ui-sortable')) {
            $('.altra-gallery-sortable').sortable('destroy');
        }

        var orientationLabel = orientation === 'landscape'
            ? altraAdminData.orientationLandscape
            : altraAdminData.orientationPortrait;

        $('.altra-gallery-preview').append(
            '<div class="gallery-item gallery-video" data-type="video" data-url="' + url + '" data-orientation="' + orientation + '">' +
            '<span class="dashicons dashicons-move drag-handle" title="Drag to reorder"></span>' +
            '<div class="video-preview-thumb">' +
            '<span class="dashicons dashicons-video-alt3"></span>' +
            '<span class="video-url-display">' + url + '</span>' +
            '<span class="video-orientation-badge">' + orientationLabel + '</span>' +
            '</div>' +
            '<button type="button" class="button button-small remove-gallery-item">\u00d7</button>' +
            '</div>'
        );

        updateGalleryOrder();
        setTimeout(function() { initGallerySortable(); }, 100);

        $('#altra-add-video-form').hide();
        $('#altra-video-url-input').val('');
    });

    // Remove gallery item (image or video)
    $(document).on('click', '.remove-gallery-item', function(e) {
        e.preventDefault();
        $(this).closest('.gallery-item').remove();
        updateGalleryOrder();
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
