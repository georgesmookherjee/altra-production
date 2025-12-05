/**
 * Altra Admin JavaScript
 * Handles admin functionality for meta boxes
 */

jQuery(document).ready(function($) {

    /**
     * PROJECT GALLERY
     * Handles media library integration for project gallery
     */
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

            selection.each(function(attachment) {
                attachment = attachment.toJSON();
                idsArray.push(attachment.id);

                $('.altra-gallery-preview').append(
                    '<div class="gallery-image" data-id="' + attachment.id + '">' +
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
