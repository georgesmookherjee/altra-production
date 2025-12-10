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
            <div class="container">

                <!-- Project Gallery with Click Navigation -->
                <?php
                $gallery_ids = get_post_meta(get_the_ID(), '_altra_project_gallery', true);
                if ($gallery_ids) :
                    $ids = array_filter(explode(',', $gallery_ids));
                    $total_images = count($ids);
                    ?>
                    <div class="project-gallery-viewer" data-total="<?php echo $total_images; ?>">
                        <div class="gallery-images">
                            <?php foreach ($ids as $index => $image_id) : ?>
                                <?php if ($image_id) : ?>
                                    <div class="gallery-slide <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                                        <?php echo wp_get_attachment_image($image_id, 'full', false, array(
                                            'class' => 'gallery-image',
                                            'loading' => $index === 0 ? 'eager' : 'lazy', // First image eager, rest lazy
                                            'decoding' => 'async',
                                            'alt' => sprintf(__('%s - Image %d of %d', 'altra'), get_the_title(), $index + 1, $total_images)
                                        )); ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <div class="gallery-counter">
                            <span class="current-image">1</span> / <span class="total-images"><?php echo $total_images; ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Featured Image (if no gallery) -->
                <?php if (!$gallery_ids && has_post_thumbnail()) : ?>
                    <div class="project-featured-image">
                        <?php the_post_thumbnail('project-large'); ?>
                    </div>
                <?php endif; ?>

                <!-- Project Header (after gallery) -->
                <header class="project-header">
                    <h1 class="project-title"><?php the_title(); ?></h1>

                    <?php if (get_the_content()) : ?>
                        <div class="project-description">
                            <?php the_content(); ?>
                        </div>
                    <?php endif; ?>
                </header>

                <!-- Project Details (moved after gallery) -->
                <div class="project-details">
                    <?php
                    // Get all fields, their order, and visibility
                    $all_fields = altra_get_project_fields();
                    $field_order = altra_get_field_order(get_the_ID());
                    $visibility = altra_get_field_visibility(get_the_ID());

                    // Display fields in custom order, respecting visibility
                    foreach ($field_order as $field_key) {
                        // Skip if field doesn't exist or is not visible
                        if (!isset($all_fields[$field_key]) || !isset($visibility[$field_key]) || !$visibility[$field_key]) {
                            continue;
                        }

                        $field = $all_fields[$field_key];
                        $value = get_post_meta(get_the_ID(), '_altra_project_' . $field_key, true);

                        // Only display if there's a value
                        if (empty($value)) {
                            continue;
                        }

                        // Format value based on field type
                        $display_value = $value;
                        if ($field['type'] === 'date') {
                            $display_value = date('F Y', strtotime($value));
                        }
                        ?>
                        <div class="project-detail-item">
                            <div class="project-detail-label"><?php echo strtoupper(esc_html($field['label'])); ?></div>
                            <div class="project-detail-value"><?php echo esc_html($display_value); ?></div>
                        </div>
                        <?php
                    }
                    ?>
                </div>

            </div>
        </article>
        
    <?php endwhile; ?>
</main>

<?php
get_footer();
