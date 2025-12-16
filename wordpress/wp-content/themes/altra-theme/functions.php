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

    // Main stylesheet - Use file modification time for cache busting
    wp_enqueue_style(
        'altra-style',
        get_stylesheet_uri(),
        array(),
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
 * Enqueue Card Editor Frontend assets (frontend - homepage only)
 */
function altra_enqueue_card_editor_frontend() {
    // Only on homepage, only for logged in users with edit permissions
    if (!is_front_page() || !current_user_can('edit_posts')) {
        return;
    }

    $theme_dir = get_template_directory();
    $asset_file_path = $theme_dir . '/build/card-editor-frontend.asset.php';

    if (!file_exists($asset_file_path)) {
        return;
    }

    $asset_file = include $asset_file_path;

    // Enqueue dashicons for the edit button icon
    wp_enqueue_style('dashicons');

    wp_enqueue_script(
        'altra-card-editor-frontend',
        get_template_directory_uri() . '/build/card-editor-frontend.js',
        $asset_file['dependencies'],
        $asset_file['version'],
        true
    );

    // Enqueue card editor frontend styles
    if (file_exists($theme_dir . '/build/style-card-editor-frontend.css')) {
        wp_enqueue_style(
            'altra-card-editor-frontend',
            get_template_directory_uri() . '/build/style-card-editor-frontend.css',
            array(),
            $asset_file['version']
        );
    }
}
add_action('wp_enqueue_scripts', 'altra_enqueue_card_editor_frontend');

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

    // Project Width meta box
    add_meta_box(
        'altra_project_width',
        __('Project Display Width', 'altra'),
        'altra_project_width_callback',
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
 * Project Gallery Meta Box Callback
 */
function altra_project_gallery_callback($post) {
    // Note: Nonce is already added in Project Details meta box
    $gallery_ids = get_post_meta($post->ID, '_altra_project_gallery', true);

    ?>
    <div class="altra-gallery-container">
        <!-- Hidden field that will be submitted with the form -->
        <input type="hidden" id="altra_project_gallery_hidden" name="altra_project_gallery" value="<?php echo esc_attr($gallery_ids); ?>" />

        <p style="margin-bottom: 10px;">
            <label for="altra_project_gallery_display" style="display: block; margin-bottom: 5px;"><?php _e('Gallery IDs (comma separated):', 'altra'); ?></label>
            <input type="text" id="altra_project_gallery_display" value="<?php echo esc_attr($gallery_ids); ?>" class="widefat" readonly style="background-color: #f0f0f0;" />
            <span class="description"><?php _e('This field is automatically updated when you add/remove/reorder images below', 'altra'); ?></span>
        </p>
        <button type="button" class="button altra-add-gallery"><?php _e('Add Images to Gallery', 'altra'); ?></button>
        <p class="description" style="margin-top: 10px; font-style: italic;">
            <?php _e('💡 Tip: Drag and drop images to reorder them', 'altra'); ?>
        </p>
        <div class="altra-gallery-preview altra-gallery-sortable">
            <?php
            if ($gallery_ids) {
                $ids = explode(',', $gallery_ids);
                foreach ($ids as $id) {
                    if ($id) {
                        echo '<div class="gallery-image" data-id="' . esc_attr($id) . '">';
                        echo '<span class="dashicons dashicons-move drag-handle" title="' . esc_attr__('Drag to reorder', 'altra') . '"></span>';
                        echo wp_get_attachment_image($id, 'thumbnail');
                        echo '<button type="button" class="button button-small remove-gallery-image">×</button>';
                        echo '</div>';
                    }
                }
            }
            ?>
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

    // Save Gallery (liste d'IDs séparés par des virgules)
    if (isset($_POST['altra_project_gallery'])) {
        $gallery_value = sanitize_text_field($_POST['altra_project_gallery']);

        // Si la valeur est vide, on supprime la meta pour nettoyer
        if (empty($gallery_value)) {
            delete_post_meta($post_id, '_altra_project_gallery');
        } else {
            update_post_meta($post_id, '_altra_project_gallery', $gallery_value);
        }
    }

    // Save Project Width
    if (isset($_POST['altra_project_width'])) {
        $width = sanitize_text_field($_POST['altra_project_width']);

        // Validate that it's an accepted value
        if (in_array($width, array('small', 'medium', 'large'))) {
            update_post_meta($post_id, '_altra_project_width', $width);
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
        'selectImages' => __('Select Gallery Images', 'altra'),
        'addToGallery' => __('Add to Gallery', 'altra'),
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
 * Project Width meta box callback
 */
function altra_project_width_callback($post) {
    // Note: Nonce is already added in Project Details meta box

    // Get current value
    $width = get_post_meta($post->ID, '_altra_project_width', true);
    if (empty($width)) {
        $width = 'medium'; // Default value
    }

    ?>
    <div class="altra-width-selector">
        <p><strong><?php _e('Choose how wide this project should display on the homepage:', 'altra'); ?></strong></p>

        <label>
            <input type="radio" name="altra_project_width" value="small" <?php checked($width, 'small'); ?>>
            <strong><?php _e('Small', 'altra'); ?></strong> - <?php _e('1/3 width (3 projects per row)', 'altra'); ?>
        </label>

        <label>
            <input type="radio" name="altra_project_width" value="medium" <?php checked($width, 'medium'); ?>>
            <strong><?php _e('Medium', 'altra'); ?></strong> - <?php _e('1/2 width (2 projects per row)', 'altra'); ?>
        </label>

        <label>
            <input type="radio" name="altra_project_width" value="large" <?php checked($width, 'large'); ?>>
            <strong><?php _e('Large', 'altra'); ?></strong> - <?php _e('Full width (1 project per row)', 'altra'); ?>
        </label>

        <div class="tip-box">
            <p>
                <strong><?php _e('Tip:', 'altra'); ?></strong>
                <?php _e('Mix different widths to create an interesting visual rhythm!', 'altra'); ?>
            </p>
        </div>

        <div class="preview-box">
            <p><?php _e('Visual Preview:', 'altra'); ?></p>
            <div id="width-preview"></div>
        </div>
    </div>
    <?php
}

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

    // Get featured image for preview
    $featured_image = '';
    if (has_post_thumbnail($post->ID)) {
        $featured_image = get_the_post_thumbnail_url($post->ID, 'full');
    }

    ?>
    <div id="altra-card-editor-root"></div>
    <input type="hidden"
           id="altra_visual_settings_input"
           name="altra_visual_settings"
           value="<?php echo esc_attr(wp_json_encode($visual_settings)); ?>" />

    <script type="text/javascript">
        // Pass data to React app
        window.altraCardEditorData = {
            postId: <?php echo $post->ID; ?>,
            featuredImage: <?php echo wp_json_encode($featured_image); ?>,
            currentSettings: <?php echo wp_json_encode($visual_settings); ?>,
            projectTitle: <?php echo wp_json_encode(get_the_title($post->ID)); ?>,
            nonce: '<?php echo wp_create_nonce('wp_rest'); ?>'
        };
    </script>
    <?php
}

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

        // Get width setting
        $width = get_post_meta($project->ID, '_altra_project_width', true);
        if (empty($width)) {
            $width = 'medium';
        }

        // Get thumbnail
        $thumbnail_url = get_the_post_thumbnail_url($project->ID, 'medium');
        if (!$thumbnail_url) {
            $thumbnail_url = get_template_directory_uri() . '/assets/images/placeholder.jpg';
        }

        $result[] = array(
            'id' => $project->ID,
            'title' => $project->post_title,
            'thumbnail' => $thumbnail_url,
            'width' => $width,
            'gridPosition' => $grid_position,
            'url' => get_permalink($project->ID),
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
    $width_map = array(4 => 'small', 6 => 'medium', 12 => 'large');

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

        // Also update width meta for backward compatibility
        if (isset($width_map[$item['w']])) {
            update_post_meta($post_id, '_altra_project_width', $width_map[$item['w']]);
        }

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