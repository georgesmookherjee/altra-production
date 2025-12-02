<?php
/**
 * Altra Production Theme Functions
 * 
 * @package Altra
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme Setup
 */
function altra_theme_setup() {
    // Add theme support for various features
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));
    
    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'altra'),
        'footer' => __('Footer Menu', 'altra'),
    ));
    
    // Add image sizes
    add_image_size('project-thumbnail', 800, 600, true);
    add_image_size('project-large', 1600, 1200, false);
}
add_action('after_setup_theme', 'altra_theme_setup');

/**
 * Enqueue Scripts and Styles
 */
function altra_enqueue_assets() {
    // Main stylesheet
    wp_enqueue_style(
        'altra-style',
        get_stylesheet_uri(),
        array(),
        '1.0.0'
    );

    // Flexible Layout CSS
    wp_enqueue_style(
        'altra-flexible-layout',
        get_template_directory_uri() . '/assets/css/flexible-layout.css',
        array('altra-style'),
        '1.0.0'
    );

    // Main JavaScript
    wp_enqueue_script(
        'altra-script',
        get_template_directory_uri() . '/assets/js/main.js',
        array(),
        '1.0.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'altra_enqueue_assets');

/**
 * Register Custom Post Type: Projects
 */
function altra_register_project_post_type() {
    $labels = array(
        'name'                  => _x('Projects', 'Post Type General Name', 'altra'),
        'singular_name'         => _x('Project', 'Post Type Singular Name', 'altra'),
        'menu_name'            => __('Projects', 'altra'),
        'name_admin_bar'       => __('Project', 'altra'),
        'archives'             => __('Project Archives', 'altra'),
        'attributes'           => __('Project Attributes', 'altra'),
        'parent_item_colon'    => __('Parent Project:', 'altra'),
        'all_items'            => __('All Projects', 'altra'),
        'add_new_item'         => __('Add New Project', 'altra'),
        'add_new'              => __('Add New', 'altra'),
        'new_item'             => __('New Project', 'altra'),
        'edit_item'            => __('Edit Project', 'altra'),
        'update_item'          => __('Update Project', 'altra'),
        'view_item'            => __('View Project', 'altra'),
        'view_items'           => __('View Projects', 'altra'),
        'search_items'         => __('Search Project', 'altra'),
        'not_found'            => __('Not found', 'altra'),
        'not_found_in_trash'   => __('Not found in Trash', 'altra'),
        'featured_image'       => __('Featured Image', 'altra'),
        'set_featured_image'   => __('Set featured image', 'altra'),
        'remove_featured_image' => __('Remove featured image', 'altra'),
        'use_featured_image'   => __('Use as featured image', 'altra'),
        'insert_into_item'     => __('Insert into project', 'altra'),
        'uploaded_to_this_item' => __('Uploaded to this project', 'altra'),
        'items_list'           => __('Projects list', 'altra'),
        'items_list_navigation' => __('Projects list navigation', 'altra'),
        'filter_items_list'    => __('Filter projects list', 'altra'),
    );

    $args = array(
        'label'                => __('Project', 'altra'),
        'description'          => __('Altra Production Projects', 'altra'),
        'labels'               => $labels,
        'supports'             => array('title', 'editor', 'thumbnail', 'custom-fields'),
        'hierarchical'         => false,
        'public'               => true,
        'show_ui'              => true,
        'show_in_menu'         => true,
        'menu_position'        => 5,
        'menu_icon'            => 'dashicons-images-alt2',
        'show_in_admin_bar'    => true,
        'show_in_nav_menus'    => true,
        'can_export'           => true,
        'has_archive'          => true,
        'exclude_from_search'  => false,
        'publicly_queryable'   => true,
        'capability_type'      => 'post',
        'show_in_rest'         => false, // Désactive Gutenberg pour éviter les conflits avec les meta boxes
        'rewrite'              => array('slug' => 'projects'),
    );

    register_post_type('project', $args);
}
add_action('init', 'altra_register_project_post_type', 0);

/**
 * Add Custom Meta Boxes for Projects
 */
function altra_add_project_meta_boxes() {
    add_meta_box(
        'altra_project_details',
        __('Project Details', 'altra'),
        'altra_project_details_callback',
        'project',
        'normal',
        'high'
    );
    
    add_meta_box(
        'altra_project_gallery',
        __('Project Gallery', 'altra'),
        'altra_project_gallery_callback',
        'project',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'altra_add_project_meta_boxes');

/**
 * Project Details Meta Box Callback
 */
function altra_project_details_callback($post) {
    // Add nonce for security
    wp_nonce_field('altra_save_project_meta', 'altra_project_meta_nonce');
    
    // Retrieve existing values
    $client = get_post_meta($post->ID, '_altra_project_client', true);
    $photographer = get_post_meta($post->ID, '_altra_project_photographer', true);
    $stylist = get_post_meta($post->ID, '_altra_project_stylist', true);
    $art_director = get_post_meta($post->ID, '_altra_project_art_director', true);
    $date = get_post_meta($post->ID, '_altra_project_date', true);
    $location = get_post_meta($post->ID, '_altra_project_location', true);
    $team = get_post_meta($post->ID, '_altra_project_team', true);
    
    ?>
    <table class="form-table">
        <tr>
            <th><label for="altra_project_client"><?php _e('Client', 'altra'); ?></label></th>
            <td><input type="text" id="altra_project_client" name="altra_project_client" value="<?php echo esc_attr($client); ?>" class="widefat"></td>
        </tr>
        <tr>
            <th><label for="altra_project_photographer"><?php _e('Photographer', 'altra'); ?></label></th>
            <td><input type="text" id="altra_project_photographer" name="altra_project_photographer" value="<?php echo esc_attr($photographer); ?>" class="widefat"></td>
        </tr>
        <tr>
            <th><label for="altra_project_stylist"><?php _e('Stylist', 'altra'); ?></label></th>
            <td><input type="text" id="altra_project_stylist" name="altra_project_stylist" value="<?php echo esc_attr($stylist); ?>" class="widefat"></td>
        </tr>
        <tr>
            <th><label for="altra_project_art_director"><?php _e('Art Director', 'altra'); ?></label></th>
            <td><input type="text" id="altra_project_art_director" name="altra_project_art_director" value="<?php echo esc_attr($art_director); ?>" class="widefat"></td>
        </tr>
        <tr>
            <th><label for="altra_project_date"><?php _e('Project Date', 'altra'); ?></label></th>
            <td><input type="date" id="altra_project_date" name="altra_project_date" value="<?php echo esc_attr($date); ?>" class="widefat"></td>
        </tr>
        <tr>
            <th><label for="altra_project_location"><?php _e('Location', 'altra'); ?></label></th>
            <td><input type="text" id="altra_project_location" name="altra_project_location" value="<?php echo esc_attr($location); ?>" class="widefat"></td>
        </tr>
        <tr>
            <th><label for="altra_project_team"><?php _e('Team Members', 'altra'); ?></label></th>
            <td>
                <textarea id="altra_project_team" name="altra_project_team" rows="5" class="widefat"><?php echo esc_textarea($team); ?></textarea>
                <p class="description"><?php _e('List team members (Hair Stylist, Make-up Artist, Models, etc.)', 'altra'); ?></p>
            </td>
        </tr>
    </table>
    <?php
}

/**
 * Project Gallery Meta Box Callback
 */
function altra_project_gallery_callback($post) {
    // Add nonce for security (même si déjà dans Project Details, c'est une bonne pratique)
    wp_nonce_field('altra_save_project_meta', 'altra_project_gallery_nonce');

    $gallery_ids = get_post_meta($post->ID, '_altra_project_gallery', true);

    ?>
    <div class="altra-gallery-container">
        <input type="hidden" id="altra_project_gallery" name="altra_project_gallery" value="<?php echo esc_attr($gallery_ids); ?>" />
        <button type="button" class="button altra-add-gallery"><?php _e('Add Images to Gallery', 'altra'); ?></button>
        <div class="altra-gallery-preview" style="margin-top: 20px; display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px;">
            <?php
            if ($gallery_ids) {
                $ids = explode(',', $gallery_ids);
                foreach ($ids as $id) {
                    if ($id) {
                        echo '<div class="gallery-image" data-id="' . esc_attr($id) . '" style="position: relative;">';
                        echo wp_get_attachment_image($id, 'thumbnail');
                        echo '<button type="button" class="button button-small remove-gallery-image" style="position: absolute; top: 5px; right: 5px;">×</button>';
                        echo '</div>';
                    }
                }
            }
            ?>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        var frame;
        
        // Add images
        $('.altra-add-gallery').on('click', function(e) {
            e.preventDefault();
            
            if (frame) {
                frame.open();
                return;
            }
            
            frame = wp.media({
                title: '<?php _e('Select Gallery Images', 'altra'); ?>',
                button: {
                    text: '<?php _e('Add to Gallery', 'altra'); ?>'
                },
                multiple: true
            });
            
            frame.on('select', function() {
                var selection = frame.state().get('selection');
                var ids = $('#altra_project_gallery').val();
                var idsArray = ids ? ids.split(',') : [];
                
                selection.each(function(attachment) {
                    attachment = attachment.toJSON();
                    idsArray.push(attachment.id);
                    
                    $('.altra-gallery-preview').append(
                        '<div class="gallery-image" data-id="' + attachment.id + '" style="position: relative;">' +
                        '<img src="' + attachment.sizes.thumbnail.url + '">' +
                        '<button type="button" class="button button-small remove-gallery-image" style="position: absolute; top: 5px; right: 5px;">×</button>' +
                        '</div>'
                    );
                });

                $('#altra_project_gallery').val(idsArray.join(','));
                console.log('Altra Gallery: Updated hidden field value to:', idsArray.join(','));
            });

            frame.open();
        });

        // Remove image
        $(document).on('click', '.remove-gallery-image', function(e) {
            e.preventDefault();
            var $image = $(this).closest('.gallery-image');
            var id = $image.data('id');
            var ids = $('#altra_project_gallery').val().split(',');

            ids = ids.filter(function(item) {
                return item != id;
            });

            var newValue = ids.join(',');
            $('#altra_project_gallery').val(newValue);
            $image.remove();
            console.log('Altra Gallery: Removed image, new value:', newValue);
        });
    });
    </script>
    <?php
}

/**
 * Save ALL Project Meta Data (unified function)
 */
function altra_save_project_meta($post_id) {
    // Check if nonce is set
    if (!isset($_POST['altra_project_meta_nonce'])) {
        return;
    }
    
    // Verify nonce
    if (!wp_verify_nonce($_POST['altra_project_meta_nonce'], 'altra_save_project_meta')) {
        return;
    }
    
    // Check for autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Check user permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Save Project Details
    $text_fields = array(
        'altra_project_client',
        'altra_project_photographer',
        'altra_project_stylist',
        'altra_project_art_director',
        'altra_project_date',
        'altra_project_location',
    );

    foreach ($text_fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
        }
    }

    // Save Team (textarea field)
    if (isset($_POST['altra_project_team'])) {
        update_post_meta($post_id, '_altra_project_team', sanitize_textarea_field($_POST['altra_project_team']));
    }

    // Save Gallery (liste d'IDs séparés par des virgules)
    if (isset($_POST['altra_project_gallery'])) {
        $gallery_value = sanitize_text_field($_POST['altra_project_gallery']);

        // Debug log (à retirer après résolution du problème)
        error_log('Altra Gallery Debug - Post ID: ' . $post_id . ' | Value: ' . $gallery_value);

        // Si la valeur est vide, on supprime la meta pour nettoyer
        if (empty($gallery_value)) {
            delete_post_meta($post_id, '_altra_project_gallery');
            error_log('Altra Gallery Debug - Deleted empty gallery for post ' . $post_id);
        } else {
            update_post_meta($post_id, '_altra_project_gallery', $gallery_value);
            error_log('Altra Gallery Debug - Saved gallery for post ' . $post_id);
        }
    } else {
        // Le champ n'est pas présent dans $_POST - on ne fait rien pour ne pas supprimer la galerie existante
        error_log('Altra Gallery Debug - Field not in POST for post ' . $post_id . ' - keeping existing value');
    }
}
add_action('save_post_project', 'altra_save_project_meta');

/**
 * Enqueue admin scripts
 */
function altra_enqueue_admin_scripts($hook) {
    if ('post.php' !== $hook && 'post-new.php' !== $hook) {
        return;
    }

    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'altra_enqueue_admin_scripts');

/**
 * Remove unnecessary admin menu items
 *
 * Hides default WordPress menus that aren't needed for this project:
 * - Articles (Posts) - We only use Projects custom post type
 * - Commentaires (Comments) - Comments are disabled for this site
 */
function altra_remove_admin_menus() {
    // Remove Posts menu (Articles)
    remove_menu_page('edit.php');

    // Remove Comments menu (Commentaires)
    remove_menu_page('edit-comments.php');
}
add_action('admin_menu', 'altra_remove_admin_menus', 999);

/**
 * Remove unnecessary admin bar links
 *
 * Removes the "New Post" link from the admin bar since we don't use
 * the default Posts functionality (only Projects custom post type)
 */
function altra_remove_admin_bar_links() {
    global $wp_admin_bar;

    // Remove "New Post" link from admin bar
    $wp_admin_bar->remove_menu('new-post');
}
add_action('wp_before_admin_bar_render', 'altra_remove_admin_bar_links');

/**
 * ==========================================================================
 * FLEXIBLE LAYOUT SYSTEM
 * Permet de contrôler la largeur de chaque projet individuellement
 * Inspiré par Sheriff Projects
 * ==========================================================================
 */

/**
 * Add Project Width meta box
 */
function altra_add_project_width_metabox() {
    add_meta_box(
        'altra_project_width',
        __('Project Display Width', 'altra'),
        'altra_project_width_callback',
        'project',
        'side',  // Display in the right sidebar
        'high'   // High priority
    );
}
add_action('add_meta_boxes', 'altra_add_project_width_metabox');

/**
 * Project Width meta box callback
 */
function altra_project_width_callback($post) {
    // Nonce for security
    wp_nonce_field('altra_save_project_width', 'altra_project_width_nonce');

    // Get current value
    $width = get_post_meta($post->ID, '_altra_project_width', true);
    if (empty($width)) {
        $width = 'medium'; // Default value
    }

    ?>
    <div class="altra-width-selector">
        <p><strong><?php _e('Choose how wide this project should display on the homepage:', 'altra'); ?></strong></p>

        <div style="margin: 15px 0;">
            <label style="display: block; margin-bottom: 10px;">
                <input type="radio" name="altra_project_width" value="small" <?php checked($width, 'small'); ?>>
                <strong><?php _e('Small', 'altra'); ?></strong> - <?php _e('1/3 width (3 projects per row)', 'altra'); ?>
            </label>

            <label style="display: block; margin-bottom: 10px;">
                <input type="radio" name="altra_project_width" value="medium" <?php checked($width, 'medium'); ?>>
                <strong><?php _e('Medium', 'altra'); ?></strong> - <?php _e('1/2 width (2 projects per row)', 'altra'); ?>
            </label>

            <label style="display: block; margin-bottom: 10px;">
                <input type="radio" name="altra_project_width" value="large" <?php checked($width, 'large'); ?>>
                <strong><?php _e('Large', 'altra'); ?></strong> - <?php _e('Full width (1 project per row)', 'altra'); ?>
            </label>
        </div>

        <div style="padding: 10px; background: #f0f0f1; border-left: 4px solid #2271b1; margin-top: 15px;">
            <p style="margin: 0; font-size: 12px;">
                <strong><?php _e('Tip:', 'altra'); ?></strong>
                <?php _e('Mix different widths to create an interesting visual rhythm!', 'altra'); ?>
            </p>
        </div>

        <!-- Visual preview -->
        <div style="margin-top: 20px; padding: 10px; background: white; border: 1px solid #ddd;">
            <p style="margin: 0 0 10px 0; font-weight: bold;"><?php _e('Visual Preview:', 'altra'); ?></p>
            <div id="width-preview" style="height: 60px; background: #2271b1; transition: width 0.3s;"></div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // Real-time preview update
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

        // Initialize preview
        $('input[name="altra_project_width"]:checked').trigger('change');
    });
    </script>

    <style>
    .altra-width-selector label {
        cursor: pointer;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        transition: all 0.2s;
    }
    .altra-width-selector label:hover {
        background: #f0f0f1;
        border-color: #2271b1;
    }
    .altra-width-selector input[type="radio"] {
        margin-right: 8px;
    }
    </style>
    <?php
}

/**
 * Save project width
 */
function altra_save_project_width($post_id) {
    // Security checks
    if (!isset($_POST['altra_project_width_nonce'])) {
        return;
    }

    if (!wp_verify_nonce($_POST['altra_project_width_nonce'], 'altra_save_project_width')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save the value
    if (isset($_POST['altra_project_width'])) {
        $width = sanitize_text_field($_POST['altra_project_width']);

        // Validate that it's an accepted value
        if (in_array($width, array('small', 'medium', 'large'))) {
            update_post_meta($post_id, '_altra_project_width', $width);
        }
    }
}
add_action('save_post_project', 'altra_save_project_width');

/**
 * Add width column in projects list
 */
function altra_add_width_column($columns) {
    $new_columns = array();

    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;

        // Add column after title
        if ($key === 'title') {
            $new_columns['project_width'] = __('Display Width', 'altra');
        }
    }

    return $new_columns;
}
add_filter('manage_project_posts_columns', 'altra_add_width_column');

/**
 * Display value in the column
 */
function altra_display_width_column($column, $post_id) {
    if ($column === 'project_width') {
        $width = get_post_meta($post_id, '_altra_project_width', true);

        if (empty($width)) {
            $width = 'medium';
        }

        $labels = array(
            'small'  => 'Small (1/3)',
            'medium' => 'Medium (1/2)',
            'large'  => 'Large (Full)'
        );

        echo '<strong>' . esc_html($labels[$width]) . '</strong>';
    }
}
add_action('manage_project_posts_custom_column', 'altra_display_width_column', 10, 2);

/**
 * Make width column sortable
 */
function altra_make_width_column_sortable($columns) {
    $columns['project_width'] = 'project_width';
    return $columns;
}
add_filter('manage_edit-project_sortable_columns', 'altra_make_width_column_sortable');

/**
 * Helper function to get CSS class based on width
 */
function altra_get_project_width_class($post_id) {
    $width = get_post_meta($post_id, '_altra_project_width', true);

    if (empty($width)) {
        $width = 'medium';
    }

    $classes = array(
        'small'  => 'project-width-small',   // 1/3 width
        'medium' => 'project-width-medium',  // 1/2 width
        'large'  => 'project-width-large'    // Full width
    );

    return $classes[$width];
}