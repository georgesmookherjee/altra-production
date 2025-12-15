<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
    <nav class="nav-left">
        <a href="<?php echo esc_url(home_url('/infos')); ?>">INFOS</a>
    </nav>

    <div class="site-logo">
        <?php if (has_custom_logo()) : ?>
            <?php the_custom_logo(); ?>
        <?php else : ?>
            <a href="<?php echo esc_url(home_url('/')); ?>">
                <?php bloginfo('name'); ?>
            </a>
        <?php endif; ?>
    </div>

    <nav class="nav-right">
        <a href="<?php echo esc_url(home_url('/contact')); ?>">CONTACT</a>
    </nav>
</header>
