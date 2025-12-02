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
                
                <!-- Project Header -->
                <header class="project-header">
                    <h1 class="project-title"><?php the_title(); ?></h1>
                    
                    <?php if (get_the_content()) : ?>
                        <div class="project-description">
                            <?php the_content(); ?>
                        </div>
                    <?php endif; ?>
                </header>
                
                <!-- Project Details -->
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
                            <div class="project-detail-label"><?php _e('Client', 'altra'); ?></div>
                            <div class="project-detail-value"><?php echo esc_html($client); ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($photographer) : ?>
                        <div class="project-detail-item">
                            <div class="project-detail-label"><?php _e('Photographer', 'altra'); ?></div>
                            <div class="project-detail-value"><?php echo esc_html($photographer); ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($stylist) : ?>
                        <div class="project-detail-item">
                            <div class="project-detail-label"><?php _e('Stylist', 'altra'); ?></div>
                            <div class="project-detail-value"><?php echo esc_html($stylist); ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($art_director) : ?>
                        <div class="project-detail-item">
                            <div class="project-detail-label"><?php _e('Art Director', 'altra'); ?></div>
                            <div class="project-detail-value"><?php echo esc_html($art_director); ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($date) : ?>
                        <div class="project-detail-item">
                            <div class="project-detail-label"><?php _e('Date', 'altra'); ?></div>
                            <div class="project-detail-value"><?php echo esc_html(date('F Y', strtotime($date))); ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($location) : ?>
                        <div class="project-detail-item">
                            <div class="project-detail-label"><?php _e('Location', 'altra'); ?></div>
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
                
                <!-- Project Gallery -->
                <?php
                $gallery_ids = get_post_meta(get_the_ID(), '_altra_project_gallery', true);
                if ($gallery_ids) :
                    $ids = explode(',', $gallery_ids);
                    ?>
                    <div class="project-gallery">
                        <?php foreach ($ids as $image_id) : ?>
                            <?php if ($image_id) : ?>
                                <?php echo wp_get_attachment_image($image_id, 'project-large', false, array('class' => 'project-gallery-image')); ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Featured Image (if no gallery) -->
                <?php if (!$gallery_ids && has_post_thumbnail()) : ?>
                    <div class="project-featured-image">
                        <?php the_post_thumbnail('project-large'); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Navigation -->
                <nav class="project-navigation">
                    <div class="nav-links">
                        <?php
                        $prev_post = get_previous_post();
                        $next_post = get_next_post();
                        ?>
                        
                        <?php if ($prev_post) : ?>
                            <div class="nav-previous">
                                <a href="<?php echo get_permalink($prev_post); ?>">
                                    <span class="nav-label"><?php _e('Previous Project', 'altra'); ?></span>
                                    <span class="nav-title"><?php echo get_the_title($prev_post); ?></span>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($next_post) : ?>
                            <div class="nav-next">
                                <a href="<?php echo get_permalink($next_post); ?>">
                                    <span class="nav-label"><?php _e('Next Project', 'altra'); ?></span>
                                    <span class="nav-title"><?php echo get_the_title($next_post); ?></span>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </nav>
                
            </div>
        </article>
        
    <?php endwhile; ?>
</main>

<?php
get_footer();
