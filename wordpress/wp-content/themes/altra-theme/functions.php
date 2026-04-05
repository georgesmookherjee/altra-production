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
    
    // Add image sizes - No crop to preserve original proportions
    add_image_size('project-thumbnail', 1200, 900, false); // No crop
    add_image_size('project-thumbnail-2x', 2400, 1800, false); // Retina display
    add_image_size('project-large', 1600, 1200, false);
    add_image_size('project-large-2x', 3200, 2400, false); // Retina display

    // Enable responsive embeds
    add_theme_support('responsive-embeds');
}
add_action('after_setup_theme', 'altra_theme_setup');

/**
 * Enable lazy loading for images (WordPress 5.5+)
 */
add_filter('wp_lazy_loading_enabled', '__return_true');

/**
 * Add WebP support
 */
function altra_add_webp_support($mimes) {
    $mimes['webp'] = 'image/webp';
    return $mimes;
}
add_filter('mime_types', 'altra_add_webp_support');

/**
 * Enqueue Scripts and Styles
 */
function altra_enqueue_assets() {
    $theme_dir = get_template_directory();

    // Lora (Google Fonts) — pour le logotype "Altra"
    wp_enqueue_style(
        'altra-font-lora',
        'https://fonts.googleapis.com/css2?family=Lora:wght@700&display=swap',
        array(),
        null
    );

    // Main stylesheet - Use file modification time for cache busting
    wp_enqueue_style(
        'altra-style',
        get_stylesheet_uri(),
        array('altra-font-lora'),
        filemtime(get_stylesheet_directory() . '/style.css')
    );

    // Flexible Layout CSS
    $flexible_layout_path = $theme_dir . '/assets/css/flexible-layout.css';
    wp_enqueue_style(
        'altra-flexible-layout',
        get_template_directory_uri() . '/assets/css/flexible-layout.css',
        array('altra-style'),
        file_exists($flexible_layout_path) ? filemtime($flexible_layout_path) : '1.0.0'
    );

    // Vimeo Player SDK — uniquement sur les pages projet (pour contrôle autoplay)
    if (is_singular('project')) {
        wp_enqueue_script('vimeo-player-sdk', 'https://player.vimeo.com/api/player.js', array(), null, true);
    }

    // Main JavaScript - Defer loading for better performance
    $main_js_path = $theme_dir . '/assets/js/main.js';
    wp_enqueue_script(
        'altra-script',
        get_template_directory_uri() . '/assets/js/main.js',
        array(),
        file_exists($main_js_path) ? filemtime($main_js_path) : '1.0.0',
        true // Load in footer
    );
}
add_action('wp_enqueue_scripts', 'altra_enqueue_assets');

/**
 * Enqueue Grid Manager assets (frontend - homepage only)
 */
function altra_enqueue_grid_manager() {
    // Only on homepage, only for logged in users with edit permissions
    if (!is_front_page() || !current_user_can('edit_posts')) {
        return;
    }

    $theme_dir = get_template_directory();
    $asset_file_path = $theme_dir . '/build/grid-manager.asset.php';

    if (!file_exists($asset_file_path)) {
        return;
    }

    $asset_file = include $asset_file_path;

    wp_enqueue_script(
        'altra-grid-manager',
        get_template_directory_uri() . '/build/grid-manager.js',
        $asset_file['dependencies'],
        $asset_file['version'],
        true
    );

    // Enqueue GridStack CSS (bundled with grid-manager)
    if (file_exists($theme_dir . '/build/grid-manager.css')) {
        wp_enqueue_style(
            'altra-gridstack',
            get_template_directory_uri() . '/build/grid-manager.css',
            array(),
            $asset_file['version']
        );
    }

    // Enqueue custom Grid Manager styles
    if (file_exists($theme_dir . '/build/style-grid-manager.css')) {
        wp_enqueue_style(
            'altra-grid-manager',
            get_template_directory_uri() . '/build/style-grid-manager.css',
            array('altra-gridstack'),
            $asset_file['version']
        );
    }

    // Pass REST API data to JavaScript
    wp_localize_script('altra-grid-manager', 'altraGridData', array(
        'restUrl' => rest_url('altra/v1/'),
        'nonce' => wp_create_nonce('wp_rest'),
    ));
}
add_action('wp_enqueue_scripts', 'altra_enqueue_grid_manager');

/**
 * Enqueue Card Editor assets in admin for project edit screen
 */
function altra_enqueue_card_editor() {
    $screen = get_current_screen();

    // Only on project edit screen
    if (!$screen || $screen->post_type !== 'project' || $screen->base !== 'post') {
        return;
    }

    $theme_dir = get_template_directory();
    $asset_file_path = $theme_dir . '/build/card-editor.asset.php';

    if (!file_exists($asset_file_path)) {
        return;
    }

    $asset_file = include $asset_file_path;

    wp_enqueue_script(
        'altra-card-editor',
        get_template_directory_uri() . '/build/card-editor.js',
        $asset_file['dependencies'],
        $asset_file['version'],
        true
    );

    // Enqueue card editor styles
    if (file_exists($theme_dir . '/build/style-card-editor.css')) {
        wp_enqueue_style(
            'altra-card-editor',
            get_template_directory_uri() . '/build/style-card-editor.css',
            array(),
            $asset_file['version']
        );
    }

    // No need for wp_localize_script - data is passed via window.altraCardEditorData in meta box callback
}
add_action('admin_enqueue_scripts', 'altra_enqueue_card_editor');

/**
 * Enqueue Inline Card Editor assets (frontend - homepage only)
 */
function altra_enqueue_card_editor_inline() {
    // Only on homepage, only for logged in users with edit permissions
    if (!is_front_page() || !current_user_can('edit_posts')) {
        return;
    }

    $theme_dir = get_template_directory();
    $asset_file_path = $theme_dir . '/build/card-editor-inline.asset.php';

    if (!file_exists($asset_file_path)) {
        return;
    }

    $asset_file = include $asset_file_path;

    // Enqueue dashicons for the buttons
    wp_enqueue_style('dashicons');

    wp_enqueue_script(
        'altra-card-editor-inline',
        get_template_directory_uri() . '/build/card-editor-inline.js',
        $asset_file['dependencies'],
        $asset_file['version'],
        true
    );

    // Pass REST API data to JavaScript
    wp_localize_script('altra-card-editor-inline', 'altraCardEditorData', array(
        'restUrl' => rest_url('altra/v1/'),
        'nonce' => wp_create_nonce('wp_rest'),
    ));

    // Enqueue inline card editor styles
    if (file_exists($theme_dir . '/build/style-card-editor-inline.css')) {
        wp_enqueue_style(
            'altra-card-editor-inline',
            get_template_directory_uri() . '/build/style-card-editor-inline.css',
            array(),
            $asset_file['version']
        );
    }
}
add_action('wp_enqueue_scripts', 'altra_enqueue_card_editor_inline');

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
        'show_in_rest'         => true, // Active REST API pour Grid Manager
        'rewrite'              => array('slug' => 'projects'),
    );

    register_post_type('project', $args);
}
add_action('init', 'altra_register_project_post_type', 0);

/**
 * Add Custom Meta Boxes for Projects
 */
function altra_add_project_meta_boxes() {
    // Project Details meta box
    add_meta_box(
        'altra_project_details',
        __('Project Details', 'altra'),
        'altra_project_details_callback',
        'project',
        'normal',
        'high'
    );

    // Project Gallery meta box
    add_meta_box(
        'altra_project_gallery',
        __('Project Gallery', 'altra'),
        'altra_project_gallery_callback',
        'project',
        'advanced',
        'high'
    );

    // Cover Media meta box (image vs video toggle)
    add_meta_box(
        'altra_cover_media',
        __('Cover Media', 'altra'),
        'altra_cover_media_callback',
        'project',
        'side',
        'high'
    );

    // Visual Card Editor meta box
    add_meta_box(
        'altra_visual_card_editor',
        __('Visual Card Settings', 'altra'),
        'altra_visual_card_editor_callback',
        'project',
        'normal',
        'default'
    );

    // Left label meta box (displayed left of gallery image on project page)
    add_meta_box(
        'altra_left_label',
        __('Left Label', 'altra'),
        'altra_left_label_callback',
        'project',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'altra_add_project_meta_boxes');

/**
 * Get all available project detail fields definition
 */
function altra_get_project_fields() {
    return array(
        'client' => array(
            'key' => 'client',
            'label' => __('Client', 'altra'),
            'type' => 'text'
        ),
        'project' => array(
            'key' => 'project',
            'label' => __('Project', 'altra'),
            'type' => 'text'
        ),
        'photographer' => array(
            'key' => 'photographer',
            'label' => __('Photographer', 'altra'),
            'type' => 'text'
        ),
        'stylist' => array(
            'key' => 'stylist',
            'label' => __('Stylist', 'altra'),
            'type' => 'text'
        ),
        'hair_stylist' => array(
            'key' => 'hair_stylist',
            'label' => __('Hair Stylist', 'altra'),
            'type' => 'text'
        ),
        'set_design' => array(
            'key' => 'set_design',
            'label' => __('Set Design', 'altra'),
            'type' => 'text'
        ),
        'casting' => array(
            'key' => 'casting',
            'label' => __('Casting', 'altra'),
            'type' => 'text'
        ),
        'models' => array(
            'key' => 'models',
            'label' => __('Models', 'altra'),
            'type' => 'text'
        ),
        'location' => array(
            'key' => 'location',
            'label' => __('Location', 'altra'),
            'type' => 'text'
        ),
        'makeup_artist' => array(
            'key' => 'makeup_artist',
            'label' => __('Make up Artist', 'altra'),
            'type' => 'text'
        ),
        'director' => array(
            'key' => 'director',
            'label' => __('Director', 'altra'),
            'type' => 'text'
        ),
        'art_director' => array(
            'key' => 'art_director',
            'label' => __('Art Director', 'altra'),
            'type' => 'text'
        ),
        'art_direction' => array(
            'key' => 'art_direction',
            'label' => __('Art Direction', 'altra'),
            'type' => 'text'
        ),
        'date' => array(
            'key' => 'date',
            'label' => __('Project Date', 'altra'),
            'type' => 'date'
        ),
    );
}

/**
 * Get field order for a project
 */
function altra_get_field_order($post_id) {
    $saved_order = get_post_meta($post_id, '_altra_project_fields_order', true);

    if ($saved_order && is_array($saved_order)) {
        return $saved_order;
    }

    // Default order
    return array_keys(altra_get_project_fields());
}

/**
 * Get field visibility settings for a project
 */
function altra_get_field_visibility($post_id) {
    $saved_visibility = get_post_meta($post_id, '_altra_project_fields_visibility', true);

    if ($saved_visibility && is_array($saved_visibility)) {
        return $saved_visibility;
    }

    // Default: all fields visible
    $all_fields = altra_get_project_fields();
    $visibility = array();
    foreach ($all_fields as $key => $field) {
        $visibility[$key] = true;
    }

    return $visibility;
}

/**
 * Get gallery items for a project (supports both old comma-separated and new JSON format)
 * Returns array of items: [['type'=>'image','id'=>42], ['type'=>'video','url'=>'...','orientation'=>'landscape']]
 */
function altra_get_gallery_items($post_id) {
    $gallery_value = get_post_meta($post_id, '_altra_project_gallery', true);

    if (empty($gallery_value)) {
        return array();
    }

    // Try new JSON format first
    $decoded = json_decode($gallery_value, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        return $decoded;
    }

    // Migrate old comma-separated IDs format
    $ids = array_filter(array_map('intval', explode(',', $gallery_value)));
    return array_map(function($id) {
        return array('type' => 'image', 'id' => $id);
    }, array_values($ids));
}

/**
 * Project Details Meta Box Callback
 */
function altra_project_details_callback($post) {
    // Add nonce for security
    wp_nonce_field('altra_save_project_meta', 'altra_project_meta_nonce');

    $all_fields = altra_get_project_fields();
    $field_order = altra_get_field_order($post->ID);
    $visibility = altra_get_field_visibility($post->ID);

    ?>
    <div class="altra-project-details-container">
        <p class="description" style="margin-bottom: 15px;">
            <strong><?php _e('💡 Tip:', 'altra'); ?></strong>
            <?php _e('Drag fields to reorder them, and use checkboxes to show/hide fields on the frontend.', 'altra'); ?>
        </p>

        <!-- Hidden field to store the order -->
        <input type="hidden" id="altra_fields_order" name="altra_fields_order" value="" />

        <div class="altra-fields-sortable">
            <?php
            // Display fields in saved order
            foreach ($field_order as $field_key) {
                if (!isset($all_fields[$field_key])) continue; // Skip if field doesn't exist anymore

                $field = $all_fields[$field_key];
                $value = get_post_meta($post->ID, '_altra_project_' . $field_key, true);
                $is_visible = isset($visibility[$field_key]) ? $visibility[$field_key] : true;
                ?>
                <div class="altra-field-row" data-field-key="<?php echo esc_attr($field_key); ?>">
                    <label for="altra_project_<?php echo esc_attr($field_key); ?>" class="field-label">
                        <?php echo esc_html($field['label']); ?>
                    </label>

                    <?php if ($field['type'] === 'date') : ?>
                        <input type="date"
                               id="altra_project_<?php echo esc_attr($field_key); ?>"
                               name="altra_project_<?php echo esc_attr($field_key); ?>"
                               value="<?php echo esc_attr($value); ?>"
                               class="field-input" />
                    <?php else : ?>
                        <input type="text"
                               id="altra_project_<?php echo esc_attr($field_key); ?>"
                               name="altra_project_<?php echo esc_attr($field_key); ?>"
                               value="<?php echo esc_attr($value); ?>"
                               class="field-input" />
                    <?php endif; ?>

                    <label class="field-visibility-toggle">
                        <input type="checkbox"
                               name="altra_field_visible[<?php echo esc_attr($field_key); ?>]"
                               value="1"
                               <?php checked($is_visible, true); ?> />
                        <span class="visibility-label"><?php _e('Show', 'altra'); ?></span>
                    </label>

                    <span class="dashicons dashicons-move field-drag-handle" title="<?php esc_attr_e('Drag to reorder', 'altra'); ?>"></span>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
    <?php
}

/**
 * Cover Media Meta Box Callback
 * Toggle between featured image and Vimeo video for homepage card
 */
function altra_cover_media_callback($post) {
    $media_type        = get_post_meta($post->ID, '_altra_featured_media_type', true) ?: 'image';
    $video_url         = get_post_meta($post->ID, '_altra_featured_video_url', true) ?: '';
    $video_orientation = get_post_meta($post->ID, '_altra_featured_video_orientation', true) ?: 'portrait';
    ?>
    <div class="altra-cover-media">
        <p style="margin-bottom: 10px; font-weight: 600;"><?php _e('Média affiché sur la page d\'accueil :', 'altra'); ?></p>

        <label style="display: block; margin-bottom: 8px; cursor: pointer;">
            <input type="radio" name="altra_featured_media_type" value="image"
                   <?php checked($media_type, 'image'); ?> />
            <?php _e('Image (Featured Image)', 'altra'); ?>
        </label>

        <label style="display: block; margin-bottom: 12px; cursor: pointer;">
            <input type="radio" name="altra_featured_media_type" value="video"
                   <?php checked($media_type, 'video'); ?> />
            <?php _e('Vidéo Vimeo', 'altra'); ?>
        </label>

        <div id="altra-video-cover-fields" style="<?php echo $media_type === 'video' ? '' : 'display:none;'; ?>border-top: 1px solid #ddd; padding-top: 12px;">
            <label for="altra_featured_video_url" style="display: block; font-weight: 600; margin-bottom: 5px;">
                <?php _e('URL Vimeo :', 'altra'); ?>
            </label>
            <input type="url"
                   id="altra_featured_video_url"
                   name="altra_featured_video_url"
                   value="<?php echo esc_attr($video_url); ?>"
                   class="widefat"
                   placeholder="https://vimeo.com/123456789" />

            <p style="margin-top: 12px; margin-bottom: 6px; font-weight: 600;"><?php _e('Orientation :', 'altra'); ?></p>
            <label style="display: block; margin-bottom: 6px; cursor: pointer;">
                <input type="radio" name="altra_featured_video_orientation" value="portrait"
                       <?php checked($video_orientation, 'portrait'); ?> />
                <?php _e('Portrait (1 colonne)', 'altra'); ?>
            </label>
            <label style="display: block; cursor: pointer;">
                <input type="radio" name="altra_featured_video_orientation" value="landscape"
                       <?php checked($video_orientation, 'landscape'); ?> />
                <?php _e('Paysage (4 colonnes)', 'altra'); ?>
            </label>
        </div>
    </div>
    <script>
    jQuery(document).ready(function($) {
        $('input[name="altra_featured_media_type"]').on('change', function() {
            if ($(this).val() === 'video') {
                $('#altra-video-cover-fields').show();
            } else {
                $('#altra-video-cover-fields').hide();
            }
        });
    });
    </script>
    <?php
}

/**
 * Project Gallery Meta Box Callback
 */
function altra_project_gallery_callback($post) {
    $items = altra_get_gallery_items($post->ID);
    $gallery_json = wp_json_encode($items);
    ?>
    <div class="altra-gallery-container">
        <input type="hidden" id="altra_project_gallery_hidden" name="altra_project_gallery" value="<?php echo esc_attr($gallery_json); ?>" />

        <div style="display: flex; gap: 8px; margin-bottom: 12px;">
            <button type="button" class="button altra-add-gallery"><?php _e('Add Images', 'altra'); ?></button>
            <button type="button" class="button altra-add-video-gallery"><?php _e('Add Vimeo Video', 'altra'); ?></button>
        </div>

        <div id="altra-add-video-form" style="display:none; margin-bottom: 12px; background: #f9f9f9; padding: 12px; border: 1px solid #ddd; border-radius: 4px;">
            <label style="display: block; font-weight: 600; margin-bottom: 5px;"><?php _e('Vimeo URL :', 'altra'); ?></label>
            <input type="url" id="altra-video-url-input" class="widefat" placeholder="https://vimeo.com/123456789" style="margin-bottom: 10px;" />
            <p style="font-weight: 600; margin-bottom: 6px;"><?php _e('Orientation :', 'altra'); ?></p>
            <label style="margin-right: 15px; cursor: pointer;">
                <input type="radio" name="altra_new_video_orientation" value="landscape" checked />
                <?php _e('Paysage (4 colonnes)', 'altra'); ?>
            </label>
            <label style="cursor: pointer;">
                <input type="radio" name="altra_new_video_orientation" value="portrait" />
                <?php _e('Portrait (1 colonne)', 'altra'); ?>
            </label>
            <div style="margin-top: 10px; display: flex; gap: 8px;">
                <button type="button" class="button button-primary altra-confirm-add-video"><?php _e('Ajouter', 'altra'); ?></button>
                <button type="button" class="button altra-cancel-add-video"><?php _e('Annuler', 'altra'); ?></button>
            </div>
        </div>

        <p class="description" style="margin-bottom: 10px; font-style: italic;">
            <?php _e('💡 Tip: Drag and drop to reorder', 'altra'); ?>
        </p>

        <div class="altra-gallery-preview altra-gallery-sortable">
            <?php foreach ($items as $item) : ?>
                <?php if ($item['type'] === 'image') : ?>
                    <div class="gallery-item gallery-image" data-type="image" data-id="<?php echo esc_attr($item['id']); ?>">
                        <span class="dashicons dashicons-move drag-handle" title="<?php esc_attr_e('Drag to reorder', 'altra'); ?>"></span>
                        <?php echo wp_get_attachment_image($item['id'], 'thumbnail'); ?>
                        <button type="button" class="button button-small remove-gallery-item">×</button>
                    </div>
                <?php elseif ($item['type'] === 'video') : ?>
                    <div class="gallery-item gallery-video"
                         data-type="video"
                         data-url="<?php echo esc_attr($item['url']); ?>"
                         data-orientation="<?php echo esc_attr($item['orientation'] ?? 'landscape'); ?>">
                        <span class="dashicons dashicons-move drag-handle" title="<?php esc_attr_e('Drag to reorder', 'altra'); ?>"></span>
                        <div class="video-preview-thumb">
                            <span class="dashicons dashicons-video-alt3"></span>
                            <span class="video-url-display"><?php echo esc_html($item['url']); ?></span>
                            <span class="video-orientation-badge"><?php echo $item['orientation'] === 'landscape' ? __('Paysage', 'altra') : __('Portrait', 'altra'); ?></span>
                        </div>
                        <button type="button" class="button button-small remove-gallery-item">×</button>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

/**
 * Save ALL Project Meta Data (unified function)
 * Handles: Project Details, Gallery, and Width
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
    if (!current_user_can('edit_post', $post_id) || get_post_type($post_id) !== 'project') {
        return;
    }

    // Save all project detail fields dynamically
    $all_fields = altra_get_project_fields();
    foreach ($all_fields as $field_key => $field) {
        $post_key = 'altra_project_' . $field_key;
        if (isset($_POST[$post_key])) {
            $value = sanitize_text_field($_POST[$post_key]);
            update_post_meta($post_id, '_' . $post_key, $value);
        }
    }

    // Save field order
    if (isset($_POST['altra_fields_order'])) {
        $order = sanitize_text_field($_POST['altra_fields_order']);
        $order_array = array_filter(explode(',', $order));
        update_post_meta($post_id, '_altra_project_fields_order', $order_array);
    }

    // Save field visibility
    $visibility = array();
    foreach ($all_fields as $field_key => $field) {
        // Checkbox: if not set, it's unchecked (false)
        $is_visible = isset($_POST['altra_field_visible'][$field_key]);
        $visibility[$field_key] = $is_visible;
    }
    update_post_meta($post_id, '_altra_project_fields_visibility', $visibility);

    // Save featured media type (image or video)
    if (isset($_POST['altra_featured_media_type'])) {
        $media_type = sanitize_text_field($_POST['altra_featured_media_type']);
        if (in_array($media_type, array('image', 'video'))) {
            update_post_meta($post_id, '_altra_featured_media_type', $media_type);
        }
    }

    // Save featured video URL
    if (isset($_POST['altra_featured_video_url'])) {
        $video_url = esc_url_raw(trim($_POST['altra_featured_video_url']));
        update_post_meta($post_id, '_altra_featured_video_url', $video_url);
    }

    // Save featured video orientation
    if (isset($_POST['altra_featured_video_orientation'])) {
        $video_orientation = sanitize_text_field($_POST['altra_featured_video_orientation']);
        if (in_array($video_orientation, array('portrait', 'landscape'))) {
            update_post_meta($post_id, '_altra_featured_video_orientation', $video_orientation);
        }
    }

    // Save Gallery (new JSON format, with auto-migration from old comma-separated format)
    if (isset($_POST['altra_project_gallery'])) {
        $gallery_value = stripslashes($_POST['altra_project_gallery']);

        if (empty($gallery_value)) {
            delete_post_meta($post_id, '_altra_project_gallery');
        } else {
            $decoded = json_decode($gallery_value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // New JSON format — sanitize each item
                $sanitized = array();
                foreach ($decoded as $item) {
                    if (!isset($item['type'])) continue;
                    if ($item['type'] === 'image' && isset($item['id'])) {
                        $sanitized[] = array('type' => 'image', 'id' => intval($item['id']));
                    } elseif ($item['type'] === 'video' && isset($item['url'])) {
                        $orientation = isset($item['orientation']) && in_array($item['orientation'], array('portrait', 'landscape'))
                            ? $item['orientation'] : 'landscape';
                        $sanitized[] = array(
                            'type'        => 'video',
                            'url'         => esc_url_raw($item['url']),
                            'orientation' => $orientation,
                        );
                    }
                }
                update_post_meta($post_id, '_altra_project_gallery', wp_json_encode($sanitized));
            } else {
                // Old comma-separated format — migrate to JSON
                $ids = array_filter(array_map('intval', explode(',', $gallery_value)));
                $migrated = array_map(function($id) {
                    return array('type' => 'image', 'id' => $id);
                }, array_values($ids));
                update_post_meta($post_id, '_altra_project_gallery', wp_json_encode($migrated));
            }
        }
    }

    // Save Visual Card Settings
    if (isset($_POST['altra_visual_settings'])) {
        $visual_settings_json = stripslashes($_POST['altra_visual_settings']);
        $visual_settings = json_decode($visual_settings_json, true);

        // Validate JSON structure
        if (json_last_error() === JSON_ERROR_NONE && is_array($visual_settings)) {
            // Sanitize values
            $sanitized = array();

            // Focal point
            if (isset($visual_settings['focalPoint'])) {
                $sanitized['focalPoint'] = array(
                    'x' => floatval($visual_settings['focalPoint']['x']),
                    'y' => floatval($visual_settings['focalPoint']['y']),
                );
            }

            // Zoom
            if (isset($visual_settings['zoom'])) {
                $sanitized['zoom'] = floatval($visual_settings['zoom']);
            }

            // Text layers
            if (isset($visual_settings['textLayers']) && is_array($visual_settings['textLayers'])) {
                $sanitized['textLayers'] = array_map(function($layer) {
                    return array(
                        'id' => sanitize_text_field($layer['id']),
                        'visible' => (bool)$layer['visible'],
                        'size' => sanitize_text_field($layer['size']),
                        'position' => array(
                            'x' => floatval($layer['position']['x']),
                            'y' => floatval($layer['position']['y']),
                        ),
                    );
                }, $visual_settings['textLayers']);
            }

            update_post_meta($post_id, '_altra_visual_settings', $sanitized);
        }
    }
}
add_action('save_post_project', 'altra_save_project_meta');

/**
 * Left Label metabox callback
 */
function altra_left_label_callback($post) {
    $value = get_post_meta($post->ID, '_altra_left_label', true);
    ?>
    <p style="margin-bottom:6px;font-size:12px;color:#666;">Texte affiché à gauche de la galerie sur la page projet.</p>
    <input type="text"
           name="altra_left_label"
           value="<?php echo esc_attr($value); ?>"
           style="width:100%;"
           placeholder="Ex: HTSI, Harper's Bazaar…">
    <?php
}

/**
 * Save left label
 */
function altra_save_left_label($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id) || get_post_type($post_id) !== 'project') return;

    if (isset($_POST['altra_left_label'])) {
        update_post_meta($post_id, '_altra_left_label', sanitize_text_field($_POST['altra_left_label']));
    }
}
add_action('save_post_project', 'altra_save_left_label');

/**
 * Enqueue admin scripts and styles
 */
function altra_enqueue_admin_scripts($hook) {
    if ('post.php' !== $hook && 'post-new.php' !== $hook) {
        return;
    }

    // Enqueue media library
    wp_enqueue_media();

    // Enqueue jQuery UI CSS (required for sortable to work properly)
    wp_enqueue_style('jquery-ui-core');

    // Enqueue admin CSS with file modification time for cache busting
    $admin_css_path = get_template_directory() . '/assets/css/admin.css';
    wp_enqueue_style(
        'altra-admin-style',
        get_template_directory_uri() . '/assets/css/admin.css',
        array('jquery-ui-core'),
        file_exists($admin_css_path) ? filemtime($admin_css_path) : '1.0.0'
    );

    // Enqueue jQuery UI Sortable for drag & drop
    wp_enqueue_script('jquery-ui-sortable');

    // Enqueue admin JavaScript
    wp_enqueue_script(
        'altra-admin-script',
        get_template_directory_uri() . '/assets/js/admin.js',
        array('jquery', 'jquery-ui-sortable'),
        '1.0.0',
        true
    );

    // Localize script for translations
    wp_localize_script('altra-admin-script', 'altraAdminData', array(
        'selectImages'       => __('Select Gallery Images', 'altra'),
        'addToGallery'       => __('Add to Gallery', 'altra'),
        'invalidVimeoUrl'    => __('Veuillez entrer une URL Vimeo valide (ex: https://vimeo.com/123456789)', 'altra'),
        'orientationLandscape' => __('Paysage', 'altra'),
        'orientationPortrait'  => __('Portrait', 'altra'),
    ));
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
 * Visual Card Editor Callback
 * Renders React app for visual card customization
 */
function altra_visual_card_editor_callback($post) {
    // Get current visual settings
    $visual_settings = get_post_meta($post->ID, '_altra_visual_settings', true);

    // Default settings
    if (empty($visual_settings)) {
        $visual_settings = array(
            'focalPoint' => array('x' => 50, 'y' => 50),
            'zoom' => 1.0,
            'textLayers' => array()
        );
    }

    // Get featured media type and video info
    $media_type              = get_post_meta($post->ID, '_altra_featured_media_type', true) ?: 'image';
    $featured_video_url      = get_post_meta($post->ID, '_altra_featured_video_url', true) ?: '';
    $featured_video_orientation = get_post_meta($post->ID, '_altra_featured_video_orientation', true) ?: 'landscape';

    // Get featured image for preview
    $featured_image = '';
    $image_orientation = 'portrait'; // Default

    if (has_post_thumbnail($post->ID)) {
        $featured_image = get_the_post_thumbnail_url($post->ID, 'full');

        // Detect orientation
        $thumbnail_id = get_post_thumbnail_id($post->ID);
        $image_meta = wp_get_attachment_metadata($thumbnail_id);

        if ($image_meta && isset($image_meta['width']) && isset($image_meta['height'])) {
            if ($image_meta['width'] > $image_meta['height']) {
                $image_orientation = 'landscape';
            }
        }
    }

    ?>
    <div id="altra-card-editor-root"></div>
    <input type="hidden"
           id="altra_visual_settings_input"
           name="altra_visual_settings"
           value="<?php echo esc_attr(wp_json_encode($visual_settings)); ?>" />

    <script type="text/javascript">
        window.altraCardEditorData = {
            postId: <?php echo $post->ID; ?>,
            featuredImage: <?php echo wp_json_encode($featured_image); ?>,
            currentSettings: <?php echo wp_json_encode($visual_settings); ?>,
            projectTitle: <?php echo wp_json_encode(get_the_title($post->ID)); ?>,
            imageOrientation: <?php echo wp_json_encode($image_orientation); ?>,
            mediaType: <?php echo wp_json_encode($media_type); ?>,
            featuredVideoUrl: <?php echo wp_json_encode($featured_video_url); ?>,
            featuredVideoOrientation: <?php echo wp_json_encode($featured_video_orientation); ?>,
            featuredVideoThumbnail: <?php echo wp_json_encode($media_type === 'video' && !empty($featured_video_url) ? altra_get_vimeo_thumbnail($featured_video_url) : null); ?>,
            nonce: '<?php echo wp_create_nonce('wp_rest'); ?>'
        };
    </script>
    <?php
}


/**
 * Move content editor below custom meta boxes for projects
 */
function altra_move_project_editor_below_metaboxes() {
    remove_post_type_support('project', 'editor');
}
add_action('init', 'altra_move_project_editor_below_metaboxes');

/**
 * Add content editor back after meta boxes with reduced height
 */
function altra_add_editor_after_metaboxes() {
    add_meta_box(
        'altra_project_editor',
        __('Project Description', 'altra'),
        'altra_project_editor_callback',
        'project',
        'normal',
        'low'  // 'low' priority puts it at the bottom
    );
}
add_action('add_meta_boxes', 'altra_add_editor_after_metaboxes');

/**
 * Callback to display the editor
 */
function altra_project_editor_callback($post) {
    wp_editor(
        $post->post_content,
        'content',
        array(
            'textarea_rows' => 8,
            'media_buttons' => true,
            'teeny' => false,
            'tinymce' => true,
            'quicktags' => true,
            'drag_drop_upload' => true
        )
    );
}

/**
 * =============================================================================
 * REST API ENDPOINTS FOR VISUAL PAGE BUILDER
 * =============================================================================
 */

/**
 * Register custom REST API endpoints
 */
function altra_register_rest_api_endpoints() {
    // GET /wp-json/altra/v1/projects
    // Returns all published projects with grid data
    register_rest_route('altra/v1', '/projects', array(
        'methods' => 'GET',
        'callback' => 'altra_get_projects_with_grid',
        'permission_callback' => 'altra_check_edit_permission',
    ));

    // POST /wp-json/altra/v1/grid-positions
    // Saves grid positions for all projects
    register_rest_route('altra/v1', '/grid-positions', array(
        'methods' => 'POST',
        'callback' => 'altra_save_grid_positions',
        'permission_callback' => 'altra_check_edit_permission',
        'args' => array(
            'positions' => array(
                'required' => true,
                'type' => 'array',
                'description' => 'Array of project positions with id, x, y, w, h, order',
            ),
        ),
    ));

    // GET /wp-json/altra/v1/project/{id}/visual-settings
    // Returns visual settings for a specific project
    register_rest_route('altra/v1', '/project/(?P<id>\d+)/visual-settings', array(
        'methods' => 'GET',
        'callback' => 'altra_get_project_visual_settings',
        'permission_callback' => 'altra_check_edit_permission',
        'args' => array(
            'id' => array(
                'required' => true,
                'type' => 'integer',
                'description' => 'Project ID',
            ),
        ),
    ));

    // POST /wp-json/altra/v1/project/{id}/visual-settings
    // Saves visual settings for a specific project
    register_rest_route('altra/v1', '/project/(?P<id>\d+)/visual-settings', array(
        'methods' => 'POST',
        'callback' => 'altra_save_project_visual_settings',
        'permission_callback' => 'altra_check_edit_permission',
        'args' => array(
            'id' => array(
                'required' => true,
                'type' => 'integer',
                'description' => 'Project ID',
            ),
            'visualSettings' => array(
                'required' => true,
                'type' => 'object',
                'description' => 'Visual settings object with focalPoint, zoom, textLayers',
            ),
        ),
    ));
}
add_action('rest_api_init', 'altra_register_rest_api_endpoints');

/**
 * Permission check for REST API endpoints
 * Requires user to be logged in and able to edit posts
 */
function altra_check_edit_permission() {
    return current_user_can('edit_posts');
}

/**
 * Fetch Vimeo thumbnail via oEmbed API (works for unlisted/domain-restricted videos).
 * Result is cached as a WordPress transient for 24 hours.
 */
function altra_get_vimeo_thumbnail($video_url) {
    $cache_key = 'altra_vimeo_thumb_' . md5($video_url);
    $cached = get_transient($cache_key);
    if ($cached !== false) {
        return $cached;
    }

    $oembed_url = 'https://vimeo.com/api/oembed.json?url=' . urlencode($video_url) . '&width=400';
    $response   = wp_remote_get($oembed_url, array('timeout' => 5));
    $placeholder = get_template_directory_uri() . '/assets/images/placeholder.jpg';

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        set_transient($cache_key, $placeholder, HOUR_IN_SECONDS);
        return $placeholder;
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    $thumbnail_url = isset($data['thumbnail_url']) ? $data['thumbnail_url'] : $placeholder;

    set_transient($cache_key, $thumbnail_url, DAY_IN_SECONDS);
    return $thumbnail_url;
}

/**
 * GET /wp-json/altra/v1/projects
 * Returns all published projects with grid data and metadata
 */
function altra_get_projects_with_grid() {
    $projects = get_posts(array(
        'post_type' => 'project',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
        'post_status' => 'publish',
    ));

    $result = array();

    foreach ($projects as $project) {
        // Get grid position
        $grid_pos = get_post_meta($project->ID, '_altra_grid_position', true);
        $grid_position = null;

        if ($grid_pos) {
            // Check if it's already an array or a JSON string
            if (is_string($grid_pos)) {
                $decoded = json_decode($grid_pos, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $grid_position = $decoded;
                }
            } elseif (is_array($grid_pos)) {
                $grid_position = $grid_pos;
            }
        }

        // Get featured media type — must be read BEFORE computing the thumbnail
        $media_type             = get_post_meta($project->ID, '_altra_featured_media_type', true) ?: 'image';
        $featured_video_url     = get_post_meta($project->ID, '_altra_featured_video_url', true) ?: '';
        $featured_video_orientation = get_post_meta($project->ID, '_altra_featured_video_orientation', true) ?: 'portrait';

        // Get thumbnail — use Vimeo oEmbed thumbnail for video projects
        if ($media_type === 'video' && !empty($featured_video_url)) {
            $thumbnail_url = altra_get_vimeo_thumbnail($featured_video_url);
        } else {
            $thumbnail_url = get_the_post_thumbnail_url($project->ID, 'medium');
            if (!$thumbnail_url) {
                $thumbnail_url = get_template_directory_uri() . '/assets/images/placeholder.jpg';
            }
        }

        // Detect orientation
        $image_orientation = 'portrait';
        if ($media_type === 'video') {
            $image_orientation = $featured_video_orientation;
        } elseif (has_post_thumbnail($project->ID)) {
            $thumbnail_id = get_post_thumbnail_id($project->ID);
            $image_meta = wp_get_attachment_metadata($thumbnail_id);
            if ($image_meta && isset($image_meta['width']) && isset($image_meta['height'])) {
                if ($image_meta['width'] > $image_meta['height']) {
                    $image_orientation = 'landscape';
                }
            }
        }

        $result[] = array(
            'id'                      => $project->ID,
            'title'                   => $project->post_title,
            'thumbnail'               => $thumbnail_url,
            'gridPosition'            => $grid_position,
            'url'                     => get_permalink($project->ID),
            'orientation'             => $image_orientation,
            'mediaType'               => $media_type,
            'featuredVideoUrl'        => $featured_video_url,
            'featuredVideoOrientation' => $featured_video_orientation,
        );
    }

    return new WP_REST_Response($result, 200);
}

/**
 * POST /wp-json/altra/v1/grid-positions
 * Saves grid positions for all projects
 */
function altra_save_grid_positions($request) {
    $positions = $request->get_param('positions');

    if (!is_array($positions)) {
        return new WP_Error(
            'invalid_data',
            'Positions must be an array',
            array('status' => 400)
        );
    }

    // Get all project IDs that are in the grid
    $grid_project_ids = array();
    foreach ($positions as $item) {
        if (isset($item['id'])) {
            $grid_project_ids[] = intval($item['id']);
        }
    }

    // Get ALL projects to clear grid data from projects not in grid
    $all_projects = get_posts(array(
        'post_type' => 'project',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'fields' => 'ids',
    ));

    // Clear grid data from projects NOT in the grid
    foreach ($all_projects as $project_id) {
        if (!in_array($project_id, $grid_project_ids)) {
            delete_post_meta($project_id, '_altra_grid_position');
            wp_update_post(array(
                'ID' => $project_id,
                'menu_order' => 0,
            ));
        }
    }

    $saved_count = 0;

    foreach ($positions as $item) {
        if (!isset($item['id'])) {
            continue;
        }

        $post_id = intval($item['id']);

        // Verify post exists and is a project
        if (get_post_type($post_id) !== 'project') {
            continue;
        }

        // Build position data
        $position = array(
            'x' => isset($item['x']) ? intval($item['x']) : 0,
            'y' => isset($item['y']) ? intval($item['y']) : 0,
            'w' => isset($item['w']) ? intval($item['w']) : 6,
            'h' => isset($item['h']) ? intval($item['h']) : 2,
            'order' => isset($item['order']) ? intval($item['order']) : 0,
        );

        // Save grid position as JSON
        update_post_meta($post_id, '_altra_grid_position', wp_json_encode($position));

        // Update menu_order for fallback sorting
        wp_update_post(array(
            'ID' => $post_id,
            'menu_order' => $position['order'],
        ));

        $saved_count++;
    }

    return new WP_REST_Response(
        array(
            'success' => true,
            'message' => sprintf('Grid positions saved for %d projects', $saved_count),
            'saved_count' => $saved_count,
        ),
        200
    );
}

/**
 * GET /wp-json/altra/v1/project/{id}/visual-settings
 * Returns visual settings for a specific project
 */
function altra_get_project_visual_settings($request) {
    $project_id = $request->get_param('id');

    // Verify post exists and is a project
    if (get_post_type($project_id) !== 'project') {
        return new WP_Error(
            'invalid_project',
            'Invalid project ID',
            array('status' => 404)
        );
    }

    // Get visual settings
    $visual_settings = get_post_meta($project_id, '_altra_visual_settings', true);

    // Default settings if none exist
    if (empty($visual_settings)) {
        $visual_settings = array(
            'focalPoint' => array('x' => 50, 'y' => 50),
            'zoom' => 1.0,
            'textLayers' => array()
        );
    }

    // Get featured image
    $featured_image = '';
    if (has_post_thumbnail($project_id)) {
        $featured_image = get_the_post_thumbnail_url($project_id, 'full');
    }

    // Get project title
    $project_title = get_the_title($project_id);

    return new WP_REST_Response(
        array(
            'projectId' => $project_id,
            'projectTitle' => $project_title,
            'featuredImage' => $featured_image,
            'currentSettings' => $visual_settings,
        ),
        200
    );
}

/**
 * POST /wp-json/altra/v1/project/{id}/visual-settings
 * Saves visual settings for a specific project
 */
function altra_save_project_visual_settings($request) {
    $project_id = $request->get_param('id');
    $visual_settings = $request->get_param('visualSettings');

    // Verify post exists and is a project
    if (get_post_type($project_id) !== 'project') {
        return new WP_Error(
            'invalid_project',
            'Invalid project ID',
            array('status' => 404)
        );
    }

    // Sanitize values
    $sanitized = array();

    // Focal point
    if (isset($visual_settings['focalPoint'])) {
        $sanitized['focalPoint'] = array(
            'x' => floatval($visual_settings['focalPoint']['x']),
            'y' => floatval($visual_settings['focalPoint']['y']),
        );
    }

    // Zoom
    if (isset($visual_settings['zoom'])) {
        $sanitized['zoom'] = floatval($visual_settings['zoom']);
    }

    // Text layers
    if (isset($visual_settings['textLayers']) && is_array($visual_settings['textLayers'])) {
        $sanitized['textLayers'] = array_map(function($layer) {
            return array(
                'id' => sanitize_text_field($layer['id']),
                'visible' => (bool)$layer['visible'],
                'size' => sanitize_text_field($layer['size']),
                'position' => array(
                    'x' => floatval($layer['position']['x']),
                    'y' => floatval($layer['position']['y']),
                ),
            );
        }, $visual_settings['textLayers']);
    }

    // Update post meta
    update_post_meta($project_id, '_altra_visual_settings', $sanitized);

    return new WP_REST_Response(
        array(
            'success' => true,
            'message' => 'Visual settings saved successfully',
            'settings' => $sanitized,
        ),
        200
    );
}

// =============================================================================
// SÉCURITÉ : URL de login personnalisée
// Remplace wp-login.php par une URL secrète
// Pour changer l'URL : modifier la constante ALTRA_LOGIN_SLUG ci-dessous
// =============================================================================
define('ALTRA_LOGIN_SLUG', 'altra-acces');

add_action('init', function() {
    if (isset($_GET[ALTRA_LOGIN_SLUG])) {
        // Accès autorisé via l'URL secrète — laisser passer
        return;
    }

    $request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $request = rtrim($request, '/');

    // Bloquer l'accès direct à wp-login.php (sauf POST, déconnexion et actions légitimes WP)
    if (preg_match('#/wp-login\.php$#', $request)) {
        // Laisser passer les soumissions de formulaire (POST) et les actions légitimes
        if ($_SERVER['REQUEST_METHOD'] === 'POST') return;
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
        $allowed_actions = ['logout', 'lostpassword', 'rp', 'resetpass', 'postpass'];
        if (!in_array($action, $allowed_actions)) {
            wp_redirect(home_url('/404'));
            exit;
        }
    }
}, 1);

// =============================================================================
// MAINTENANCE : site non accessible aux visiteurs non connectés
// Pour désactiver : commenter ou supprimer ce bloc
// =============================================================================
add_action('template_redirect', function() {
    if (!is_user_logged_in()) {
        // Laisser passer les requêtes wp-login et wp-admin
        if (is_admin() || $GLOBALS['pagenow'] === 'wp-login.php') return;

        header('HTTP/1.1 503 Service Unavailable');
        header('Retry-After: 3600');
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Altra Production — Bientôt en ligne</title><style>body{margin:0;display:flex;align-items:center;justify-content:center;min-height:100vh;font-family:sans-serif;background:#fff}p{font-size:1rem;color:#000;letter-spacing:0.05em}</style></head><body><p>Altra Production — Bientôt en ligne</p></body></html>';
        exit;
    }
});