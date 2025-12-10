<?php
/**
 * Front Page Template
 * Used for the homepage
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

        <?php
        // Query for projects
        $args = array(
            'post_type' => 'project',
            'posts_per_page' => 24,
            'orderby' => 'date',
            'order' => 'DESC',
            'paged' => get_query_var('paged') ? get_query_var('paged') : 1,
            'update_post_meta_cache' => true, // Pre-cache meta data to avoid N+1 queries
            'update_post_term_cache' => false, // Disable if not using taxonomies
        );

        $projects_query = new WP_Query($args);

        if ($projects_query->have_posts()) :
            // Pre-cache all meta data for better performance
            update_meta_cache('post', wp_list_pluck($projects_query->posts, 'ID'));
        ?>

            <div class="projects-grid">

                <?php
                while ($projects_query->have_posts()) : $projects_query->the_post();

                    $client = get_post_meta(get_the_ID(), '_altra_project_client', true);
                    $photographer = get_post_meta(get_the_ID(), '_altra_project_photographer', true);

                    // Get project width setting
                    $width = get_post_meta(get_the_ID(), '_altra_project_width', true);
                    if (empty($width)) {
                        $width = 'medium'; // Default value
                    }
                    $width_class = 'project-width-' . $width;
                    ?>

                    <article class="project-card <?php echo esc_attr($width_class); ?>">
                        <a href="<?php the_permalink(); ?>">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('project-thumbnail', array(
                                    'loading' => 'lazy',
                                    'decoding' => 'async',
                                    'alt' => esc_attr(get_the_title())
                                )); ?>
                            <?php else : ?>
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/placeholder.jpg"
                                     alt="<?php the_title_attribute(); ?>"
                                     loading="lazy"
                                     decoding="async">
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
            if ($projects_query->max_num_pages > 1) :
                the_posts_pagination(array(
                    'mid_size' => 2,
                    'prev_text' => __('← Previous', 'altra'),
                    'next_text' => __('Next →', 'altra'),
                ));
            endif;
            ?>

            <?php wp_reset_postdata(); ?>

        <?php else : ?>

            <div class="no-projects">
                <p><?php _e('No projects found. Add your first project in the WordPress admin.', 'altra'); ?></p>
            </div>

        <?php endif; ?>

    </div>
</main>

<?php
get_footer();
