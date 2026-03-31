<?php
/**
 * Single Project Template
 * 
 * @package Altra
 */

get_header();
?>

<main class="site-main">
    <?php while (have_posts()) : the_post(); ?>
        
        <article id="post-<?php the_ID(); ?>" <?php post_class('single-project'); ?>>
            <div class="container project-container">

                <!-- Project Gallery with Click Navigation -->
                <?php
                $gallery_items = altra_get_gallery_items(get_the_ID());
                $left_label = get_post_meta(get_the_ID(), '_altra_left_label', true);
                if (!empty($gallery_items)) :
                    $total = count($gallery_items);
                    ?>
                    <div class="project-gallery-viewer" data-total="<?php echo $total; ?>">
                        <?php if ($left_label) : ?>
                            <div class="gallery-left-label"><?php echo esc_html($left_label); ?></div>
                        <?php endif; ?>
                        <div class="gallery-images">
                            <?php foreach ($gallery_items as $index => $item) : ?>
                                <div class="gallery-slide <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                                    <?php if ($item['type'] === 'image') : ?>
                                        <?php echo wp_get_attachment_image($item['id'], 'full', false, array(
                                            'class'   => 'gallery-image',
                                            'loading' => $index === 0 ? 'eager' : 'lazy',
                                            'decoding' => 'async',
                                            'alt'     => sprintf(__('%s - Image %d of %d', 'altra'), get_the_title(), $index + 1, $total),
                                        )); ?>
                                    <?php elseif ($item['type'] === 'video') :
                                        preg_match('/vimeo\.com\/(\d+)/', $item['url'], $matches);
                                        $vimeo_id = isset($matches[1]) ? $matches[1] : '';
                                        if ($vimeo_id) : ?>
                                            <div class="gallery-video-wrapper gallery-video-<?php echo esc_attr($item['orientation'] ?? 'landscape'); ?>">
                                                <iframe src="https://player.vimeo.com/video/<?php echo esc_attr($vimeo_id); ?>?autoplay=<?php echo $index === 0 ? '1' : '0'; ?>&loop=1&byline=0&title=0&portrait=0"
                                                        frameborder="0"
                                                        allow="autoplay; fullscreen; picture-in-picture"
                                                        allowfullscreen
                                                        title="<?php the_title_attribute(); ?>"></iframe>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="gallery-counter">
                            <span class="current-image">1</span> / <span class="total-images"><?php echo $total; ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Image à la une si pas de galerie -->
                <?php if (empty($gallery_items) && has_post_thumbnail()) : ?>
                    <div class="project-featured-image">
                        <?php the_post_thumbnail('full', array('style' => 'max-height:85vh; width:auto; max-width:100%;')); ?>
                    </div>
                <?php endif; ?>

            </div>

            <!-- Métadonnées projet — pleine largeur, alignées sur les bords du header -->
            <div class="project-details">
                <?php
                $all_fields  = altra_get_project_fields();
                $field_order = altra_get_field_order(get_the_ID());
                $visibility  = altra_get_field_visibility(get_the_ID());

                foreach ($field_order as $field_key) {
                    if (!isset($all_fields[$field_key]) || empty($visibility[$field_key])) continue;

                    $field = $all_fields[$field_key];
                    $value = get_post_meta(get_the_ID(), '_altra_project_' . $field_key, true);
                    if (empty($value)) continue;

                    $display_value = ($field['type'] === 'date') ? date('F Y', strtotime($value)) : $value;
                    // Location : remplacer la virgule par un saut de ligne
                    $use_html = ($field_key === 'location' && strpos($display_value, ',') !== false);
                    ?>
                    <div class="project-detail-item">
                        <span class="project-detail-label"><?php echo esc_html($field['label']); ?></span><span class="project-detail-value"><?php
                            if ($use_html) {
                                echo nl2br(esc_html(str_replace(', ', ",\n", $display_value)));
                            } else {
                                echo esc_html($display_value);
                            }
                        ?></span>
                    </div>
                    <?php
                }
                ?>
            </div>
        </article>
        
    <?php endwhile; ?>
</main>

<?php
get_footer();
