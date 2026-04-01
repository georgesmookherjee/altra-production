<?php
/**
 * Template for Contact page (slug: contact)
 *
 * @package Altra
 */

add_filter('body_class', function($classes) {
    $classes[] = 'page-contact';
    return $classes;
});

get_header();
?>

<main class="site-main contact-main">
    <div class="contact-info">
        <!-- <p class="contact-name">Altra Production</p> -->
        <a href="mailto:Contact@altraproduction.com" class="contact-link">Contact@altraproduction.com</a>
        <a href="https://instagram.com/altra_production" target="_blank" rel="noopener" class="contact-link">@altraproduction</a>
    </div>
</main>

<?php get_footer(); ?>
