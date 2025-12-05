<?php
/**
 * Main Template File
 * 
 * @package Altra
 */

get_header();
?>

<!-- Hero Section -->
<section class="hero-section" id="hero">
    <div class="hero-content">
        <h1 class="hero-logo">Altra</h1>
        <div class="hero-info">
            <p class="hero-instagram">@altraproduction</p>
            <p class="hero-contact">Contact@altraproduction.com</p>
        </div>
    </div>
    <div class="scroll-indicator">
        <span>Scroll</span>
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 5v14M19 12l-7 7-7-7"/>
        </svg>
    </div>
</section>

<main class="site-main">
    <div class="container">
        
        <?php if (have_posts()) : ?>
            
            <div class="projects-grid">
                
                <?php
                // Query for projects
                $args = array(
                    'post_type' => 'project',
                    'posts_per_page' => -1,
                    'orderby' => 'date',
                    'order' => 'DESC'
                );
                
                $projects_query = new WP_Query($args);
                
                if ($projects_query->have_posts()) :
                    while ($projects_query->have_posts()) : $projects_query->the_post();
                        
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
                    <?php wp_reset_postdata(); ?>
                <?php else : ?>
                    
                    <div class="no-projects">
                        <p><?php _e('No projects found. Add your first project in the WordPress admin.', 'altra'); ?></p>
                    </div>
                    
                <?php endif; ?>
                
            </div>
            
        <?php else : ?>
            
            <div class="no-content">
                <p><?php _e('No content found.', 'altra'); ?></p>
            </div>
            
        <?php endif; ?>
        
    </div>
</main>

<?php
get_footer();
