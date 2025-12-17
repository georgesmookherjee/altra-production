<?php
/**
 * Template part for displaying a project card
 *
 * @package Altra
 */

$client = get_post_meta(get_the_ID(), '_altra_project_client', true);
$photographer = get_post_meta(get_the_ID(), '_altra_project_photographer', true);

// Get project width setting
$width = get_post_meta(get_the_ID(), '_altra_project_width', true);
if (empty($width)) {
    $width = 'medium'; // Default value
}
$width_class = 'project-width-' . $width;

// Get grid position if saved by Grid Manager
$grid_position = get_post_meta(get_the_ID(), '_altra_grid_position', true);
$grid_styles = '';

if (!empty($grid_position) && is_array($grid_position)) {
    // Apply CSS Grid positioning
    $x = isset($grid_position['x']) ? intval($grid_position['x']) : 0;
    $y = isset($grid_position['y']) ? intval($grid_position['y']) : 0;
    $w = isset($grid_position['w']) ? intval($grid_position['w']) : 6;
    $h = isset($grid_position['h']) ? intval($grid_position['h']) : 2;

    // CSS Grid uses 1-based indexing
    $grid_column_start = $x + 1;
    $grid_column_end = $x + $w + 1;
    $grid_row_start = $y + 1;
    $grid_row_end = $y + $h + 1;

    $grid_styles = sprintf(
        'grid-column: %d / %d; grid-row: %d / %d;',
        $grid_column_start,
        $grid_column_end,
        $grid_row_start,
        $grid_row_end
    );
}

// Get visual settings from Card Editor
$visual_settings = get_post_meta(get_the_ID(), '_altra_visual_settings', true);
$image_style = '';

if (!empty($visual_settings) && is_array($visual_settings)) {
    $focal_x = isset($visual_settings['focalPoint']['x']) ? floatval($visual_settings['focalPoint']['x']) : 50;
    $focal_y = isset($visual_settings['focalPoint']['y']) ? floatval($visual_settings['focalPoint']['y']) : 50;
    $zoom = isset($visual_settings['zoom']) ? floatval($visual_settings['zoom']) : 1.0;

    $image_style = sprintf(
        'transform-origin: %s%% %s%%; transform: scale(%s);',
        $focal_x,
        $focal_y,
        $zoom
    );
}

// Detect image orientation (landscape vs portrait)
$image_orientation = 'portrait'; // Default
$orientation_class = 'project-portrait';

if (has_post_thumbnail()) {
    $thumbnail_id = get_post_thumbnail_id();
    $image_meta = wp_get_attachment_metadata($thumbnail_id);

    if ($image_meta && isset($image_meta['width']) && isset($image_meta['height'])) {
        $width_img = $image_meta['width'];
        $height_img = $image_meta['height'];

        // If width > height, it's landscape
        if ($width_img > $height_img) {
            $image_orientation = 'landscape';
            $orientation_class = 'project-landscape';
        }
    }
}

// Build inline styles
$card_styles = $grid_styles;

// Add column span for landscape images (only if Grid Manager hasn't set position)
if ($image_orientation === 'landscape' && empty($grid_position)) {
    $card_styles .= ' grid-column: span 2;';
}
?>

<article class="project-card <?php echo esc_attr($width_class . ' ' . $orientation_class); ?>"
         data-project-id="<?php echo get_the_ID(); ?>"
         data-focal-x="<?php echo isset($visual_settings['focalPoint']['x']) ? esc_attr($visual_settings['focalPoint']['x']) : '50'; ?>"
         data-focal-y="<?php echo isset($visual_settings['focalPoint']['y']) ? esc_attr($visual_settings['focalPoint']['y']) : '50'; ?>"
         data-zoom="<?php echo isset($visual_settings['zoom']) ? esc_attr($visual_settings['zoom']) : '1.0'; ?>"
         <?php if ($card_styles) echo 'style="' . esc_attr($card_styles) . '"'; ?>>

    <a href="<?php the_permalink(); ?>" class="project-link">
        <div class="project-image">
            <?php if (has_post_thumbnail()) : ?>
                <?php
                $thumbnail_attrs = array(
                    'loading' => 'lazy',
                    'decoding' => 'async',
                    'alt' => esc_attr(get_the_title())
                );
                // Apply visual settings to image if available
                if ($image_style) {
                    $thumbnail_attrs['style'] = $image_style;
                }
                the_post_thumbnail('project-thumbnail', $thumbnail_attrs);
                ?>
            <?php else : ?>
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/placeholder.jpg"
                     alt="<?php the_title_attribute(); ?>"
                     loading="lazy"
                     decoding="async"
                     <?php if ($image_style) echo 'style="' . esc_attr($image_style) . '"'; ?>>
            <?php endif; ?>
        </div>

        <div class="project-info">
            <h2 class="project-title"><?php the_title(); ?></h2>
            <?php if ($photographer) : ?>
                <p class="project-photographer"><?php echo esc_html($photographer); ?></p>
            <?php endif; ?>
        </div>
    </a>
</article>
