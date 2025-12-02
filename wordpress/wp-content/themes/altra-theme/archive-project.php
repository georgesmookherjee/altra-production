<?php
/**
 * Archive Template for Projects
 * 
 * @package Altra
 */

get_header();
?>

<main class="site-main">
    <div class="container">
        
        <header class="archive-header">
            <h1 class="archive-title"><?php _e('All Projects', 'altra'); ?></h1>
        </header>
        
        <?php if (have_posts()) : ?>
            
            <div class="projects-grid">
                
                <?php while (have_posts()) : the_post(); ?>
                    
                    <?php
                    $client = get_post_meta(get_the_ID(), '_altra_project_client', true);
                    $photographer = get_post_meta(get_the_ID(), '_altra_project_photographer', true);
                    ?>
                    
                    <article class="project-card">
                        <a href="<?php the_permalink(); ?>">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('project-thumbnail'); ?>
                            <?php else : ?>
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/placeholder.jpg" alt="<?php the_title(); ?>">
                            <?php endif; ?>
                            
                            <div class="project-info">
                                <h2 class="project-title"><?php the_title(); ?></h2>
                                <div class="project-meta">
                                    <?php if ($client) : ?>
                                        <span class="project-client"><?php echo esc_html($client); ?></span>
                                    <?php endif; ?>
                                    <?php if ($photographer) : ?>
                                        <span class="project-photographer"> • <?php echo esc_html($photographer); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    </article>
                    
                <?php endwhile; ?>
                
            </div>
            
            <?php
            // Pagination
            the_posts_pagination(array(
                'mid_size'  => 2,
                'prev_text' => __('Previous', 'altra'),
                'next_text' => __('Next', 'altra'),
            ));
            ?>
            
        <?php else : ?>
            
            <div class="no-projects">
                <p><?php _e('No projects found.', 'altra'); ?></p>
            </div>
            
        <?php endif; ?>
        
    </div>
</main>

<?php
get_footer();
