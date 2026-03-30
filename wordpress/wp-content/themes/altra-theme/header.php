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
        <a href="<?php echo esc_url(home_url('/')); ?>">
            <span class="logo-altra">Altra</span>
        </a>
    </div>

    <nav class="nav-right">
        <a href="<?php echo esc_url(home_url('/contact')); ?>">CONTACT</a>
    </nav>
</header>
