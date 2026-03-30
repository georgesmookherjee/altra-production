<?php
/**
 * Template part for displaying a project card
 *
 * @package Altra
 */

$client       = get_post_meta(get_the_ID(), '_altra_project_client', true);
$photographer = get_post_meta(get_the_ID(), '_altra_project_photographer', true);

// Featured media type (image or video)
$media_type             = get_post_meta(get_the_ID(), '_altra_featured_media_type', true) ?: 'image';
$featured_video_url     = get_post_meta(get_the_ID(), '_altra_featured_video_url', true) ?: '';
$featured_video_orientation = get_post_meta(get_the_ID(), '_altra_featured_video_orientation', true) ?: 'portrait';

// Get grid position if saved by Grid Manager
$grid_position = get_post_meta(get_the_ID(), '_altra_grid_position', true);
$grid_styles = '';

if (!empty($grid_position) && is_array($grid_position)) {
    $x = isset($grid_position['x']) ? intval($grid_position['x']) : 0;
    $y = isset($grid_position['y']) ? intval($grid_position['y']) : 0;
    $w = isset($grid_position['w']) ? intval($grid_position['w']) : 1;
    $h = isset($grid_position['h']) ? intval($grid_position['h']) : 2;

    $grid_styles = sprintf(
        'grid-column: %d / %d; grid-row: %d / %d;',
        $x + 1,
        $x + $w + 1,
        $y + 1,
        $y + $h + 1
    );
}

// Get visual settings from Card Editor
$visual_settings = get_post_meta(get_the_ID(), '_altra_visual_settings', true);
$media_style = '';

if (!empty($visual_settings) && is_array($visual_settings)) {
    $focal_x = isset($visual_settings['focalPoint']['x']) ? floatval($visual_settings['focalPoint']['x']) : 50;
    $focal_y = isset($visual_settings['focalPoint']['y']) ? floatval($visual_settings['focalPoint']['y']) : 50;
    $zoom    = isset($visual_settings['zoom']) ? floatval($visual_settings['zoom']) : 1.0;

    $media_style = sprintf(
        'transform-origin: %s%% %s%%; transform: scale(%s);',
        $focal_x,
        $focal_y,
        $zoom
    );
}

// Determine orientation and CSS class
if ($media_type === 'video') {
    $orientation_class = $featured_video_orientation === 'landscape' ? 'project-landscape project-video-landscape' : 'project-portrait project-video-portrait';
} else {
    $orientation_class = 'project-portrait';
    if (has_post_thumbnail()) {
        $thumbnail_id = get_post_thumbnail_id();
        $image_meta   = wp_get_attachment_metadata($thumbnail_id);
        if ($image_meta && isset($image_meta['width'], $image_meta['height']) && $image_meta['width'] > $image_meta['height']) {
            $orientation_class = 'project-landscape';
        }
    }
}

// Build card inline styles
$card_styles = $grid_styles;

// Default span if no Grid Manager position set
if (empty($grid_position)) {
    if ($media_type === 'video' && $featured_video_orientation === 'landscape') {
        $card_styles .= ' grid-column: span 4;';
    } elseif ($media_type === 'image' && strpos($orientation_class, 'project-landscape') !== false) {
        $card_styles .= ' grid-column: span 2;';
    }
}

// Extract Vimeo ID from URL for background embed
$vimeo_id = '';
if ($media_type === 'video' && $featured_video_url) {
    preg_match('/vimeo\.com\/(\d+)/', $featured_video_url, $matches);
    $vimeo_id = isset($matches[1]) ? $matches[1] : '';
}
?>

<article class="project-card <?php echo esc_attr($orientation_class); ?>"
         data-project-id="<?php echo get_the_ID(); ?>"
         data-focal-x="<?php echo isset($visual_settings['focalPoint']['x']) ? esc_attr($visual_settings['focalPoint']['x']) : '50'; ?>"
         data-focal-y="<?php echo isset($visual_settings['focalPoint']['y']) ? esc_attr($visual_settings['focalPoint']['y']) : '50'; ?>"
         data-zoom="<?php echo isset($visual_settings['zoom']) ? esc_attr($visual_settings['zoom']) : '1.0'; ?>"
         <?php if ($card_styles) echo 'style="' . esc_attr(trim($card_styles)) . '"'; ?>>

    <a href="<?php the_permalink(); ?>" class="project-link">
        <div class="project-image">
            <?php if ($media_type === 'video' && $vimeo_id) : ?>
                <div class="project-video-wrapper" <?php if ($media_style) echo 'style="' . esc_attr($media_style) . '"'; ?>>
                    <iframe src="https://player.vimeo.com/video/<?php echo esc_attr($vimeo_id); ?>?background=1&autoplay=1&loop=1&muted=1&byline=0&title=0"
                            frameborder="0"
                            allow="autoplay"
                            allowfullscreen
                            loading="lazy"
                            title="<?php the_title_attribute(); ?>"></iframe>
                </div>
            <?php elseif (has_post_thumbnail()) : ?>
                <?php
                $thumbnail_attrs = array(
                    'loading'  => 'lazy',
                    'decoding' => 'async',
                    'alt'      => esc_attr(get_the_title()),
                );
                if ($media_style) {
                    $thumbnail_attrs['style'] = $media_style;
                }
                the_post_thumbnail('project-thumbnail', $thumbnail_attrs);
                ?>
            <?php else : ?>
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/placeholder.jpg"
                     alt="<?php the_title_attribute(); ?>"
                     loading="lazy"
                     decoding="async">
            <?php endif; ?>
        </div>

        <div class="project-info">
            <p class="project-caption">
                <strong class="project-title"><?php the_title(); ?></strong><?php if ($photographer) : ?> — <span class="project-photographer"><?php echo esc_html($photographer); ?></span><?php endif; ?>
            </p>
        </div>
    </a>
</article>
