<?php
/**
 * Template for Infos page (slug: infos)
 *
 * @package Altra
 */

add_filter('body_class', function($classes) {
    $classes[] = 'page-infos';
    return $classes;
});

get_header();
?>

<main class="site-main infos-main">
    <div class="infos-content">
        <?php while (have_posts()) : the_post(); ?>
            <?php the_content(); ?>
        <?php endwhile; ?>
    </div>
</main>

<?php get_footer(); ?>
