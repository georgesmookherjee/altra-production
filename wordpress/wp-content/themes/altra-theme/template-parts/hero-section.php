<?php
/**
 * Template part for displaying the hero section
 *
 * @package Altra
 */
?>

<!-- Hero Section -->
<section class="hero-section" id="hero">
    <div class="hero-content">
        <nav class="hero-nav-left">
            <a href="<?php echo esc_url(home_url('/infos')); ?>">INFOS</a>
        </nav>

        <h1 class="hero-logo">Altra Production</h1>

        <nav class="hero-nav-right">
            <a href="<?php echo esc_url(home_url('/contact')); ?>">CONTACT</a>
        </nav>
    </div>
    <div class="scroll-indicator">
        <span>Scroll</span>
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 5v14M19 12l-7 7-7-7"/>
        </svg>
    </div>
</section>
