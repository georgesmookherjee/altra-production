<?php
/**
 * Front Page Template (Homepage) - FLEXIBLE LAYOUT VERSION
 * This file displays the projects grid on the homepage with flexible widths
 *
 * @package Altra
 */

get_header();
?>

<main class="site-main">
    <div class="container">

        <?php
        // Query for projects
        $args = array(
            'post_type' => 'project',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        );

        $projects_query = new WP_Query($args);

        if ($projects_query->have_posts()) : ?>

            <div class="projects-grid">

                <?php while ($projects_query->have_posts()) : $projects_query->the_post();

                    // Get metadata
                    $client = get_post_meta(get_the_ID(), '_altra_project_client', true);
                    $photographer = get_post_meta(get_the_ID(), '_altra_project_photographer', true);

                    // NEW: Get width class for flexible layout
                    $width_class = altra_get_project_width_class(get_the_ID());
                    ?>

                    <article class="project-card <?php echo esc_attr($width_class); ?>">
                        <a href="<?php the_permalink(); ?>">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('large'); ?>
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

            </div>

        <?php else : ?>

            <div class="no-projects">
                <p><?php _e('No projects found. Add your first project in the WordPress admin.', 'altra'); ?></p>
            </div>

        <?php endif; ?>

    </div>
</main>

<?php
get_footer();
