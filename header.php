<!DOCTYPE html>
<html <?php language_attributes() ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ) ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    
    <title><?= get_bloginfo('name'); ?> | <?php the_title(); ?></title>
    <?php wp_head(); ?>
</head>

<body <?= body_class(); ?>>

<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#content">Direct naar inhoud</a>

<div id="site-container"><?php // Style Gravity Forms more easy ?> 

<header role="banner">
    <div class="wrapper">
        <a class="header-logo" href="/" aria-label="<?php bloginfo('name'); ?>">
            <?= svg('logo', 'header-logo-logo'); ?>
        </a>
    
        <button class="header-burger" aria-expanded="false" aria-controls="mobile-menu" aria-label="Menu openen">
            <?= svg('menu-open', 'header-burger-open') ?>
            <?= svg('menu-close', 'header-burger-close') ?>
        </button>
        <?php wp_nav_menu([
            'theme_location' => 'headermenu',
            'container'      => 'nav',
            'container_aria_label' => __( 'Hoofdmenu', 'textdomain' ),
            'container_class' => '',
            'menu_class' => 'header-menu-large',
            'depth' => 2
        ]); ?>
    </div>
</header>

<div id="mobile-menu" class="header-menu-small" hidden>
    <div class="wrapper">
        <?php wp_nav_menu([
            'theme_location' => 'headermenu',
            'container'      => 'nav',
            'container_aria_label' => __( 'Hoofdmenu', 'textdomain' ),
            'container_class' => '',
            'depth' => 2
        ]); ?>
    </div>
</div>

<main id="content" role="main" aria-label="Hoofdinhoud">