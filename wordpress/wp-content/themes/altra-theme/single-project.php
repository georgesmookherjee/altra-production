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
                    $client = get_post_meta(get_the_ID(), '_altra_project_client', true);
                    $photographer = get_post_meta(get_the_ID(), '_altra_project_photographer', true);
                    $stylist = get_post_meta(get_the_ID(), '_altra_project_stylist', true);
                    $art_director = get_post_meta(get_the_ID(), '_altra_project_art_director', true);
                    $date = get_post_meta(get_the_ID(), '_altra_project_date', true);
                    $location = get_post_meta(get_the_ID(), '_altra_project_location', true);
                    $team = get_post_meta(get_the_ID(), '_altra_project_team', true);
                    ?>

                    <?php if ($client) : ?>
                        <div class="project-detail-item">
                            <div class="project-detail-label"><?php _e('CLIENT', 'altra'); ?></div>
                            <div class="project-detail-value"><?php echo esc_html($client); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ($photographer) : ?>
                        <div class="project-detail-item">
                            <div class="project-detail-label"><?php _e('PHOTOGRAPHER', 'altra'); ?></div>
                            <div class="project-detail-value"><?php echo esc_html($photographer); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ($stylist) : ?>
                        <div class="project-detail-item">
                            <div class="project-detail-label"><?php _e('STYLIST', 'altra'); ?></div>
                            <div class="project-detail-value"><?php echo esc_html($stylist); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ($art_director) : ?>
                        <div class="project-detail-item">
                            <div class="project-detail-label"><?php _e('ART DIRECTOR', 'altra'); ?></div>
                            <div class="project-detail-value"><?php echo esc_html($art_director); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ($date) : ?>
                        <div class="project-detail-item">
                            <div class="project-detail-label"><?php _e('DATE', 'altra'); ?></div>
                            <div class="project-detail-value"><?php echo esc_html(date('F Y', strtotime($date))); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ($location) : ?>
                        <div class="project-detail-item">
                            <div class="project-detail-label"><?php _e('LOCATION', 'altra'); ?></div>
                            <div class="project-detail-value"><?php echo esc_html($location); ?></div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($team) : ?>
                    <div class="project-team">
                        <h3><?php _e('Team', 'altra'); ?></h3>
                        <div class="project-team-content">
                            <?php echo nl2br(esc_html($team)); ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </article>
        
    <?php endwhile; ?>
</main>

<?php
get_footer();
