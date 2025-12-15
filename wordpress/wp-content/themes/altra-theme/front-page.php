<?php
/**
 * Front Page Template
 * Used for the homepage
 *
 * @package Altra
 */

get_header();
?>

<!-- Grid Manager Root (only for logged in users) -->
<?php if (is_user_logged_in() && current_user_can('edit_posts')) : ?>
    <div id="altra-grid-manager-root"></div>
<?php endif; ?>

<?php get_template_part('template-parts/hero-section'); ?>

<main class="site-main">
    <div class="container">
        <?php get_template_part('template-parts/projects-loop'); ?>

<?php
get_footer();
