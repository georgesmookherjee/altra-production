<?php
/**
 * Template part for displaying projects loop
 *
 * @package Altra
 */

// Query for projects - order by menu_order (set by Grid Manager), fallback to date
$args = array(
    'post_type' => 'project',
    'posts_per_page' => 24,
    'orderby' => 'menu_order date',
    'order' => 'ASC',
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
            get_template_part('template-parts/project-card');
        endwhile;
        ?>

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
