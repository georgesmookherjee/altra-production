<?php
/**
 * Main Template File
 * Fallback template for archives and other views
 *
 * @package Altra
 */

get_header();
?>

<main class="site-main">
    <div class="container">
        <?php get_template_part('template-parts/projects-loop'); ?>

<?php
get_footer();
