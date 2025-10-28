<?php
/**
 * The template for displaying 404 pages (Not Found)
 */
get_header();
?>

<section class="404-page with-margin">
    <div class="wrapper">
        <h1><?php _e( 'Pagina niet gevonden', 'textdomain' ); ?></h1>

        <p><?php _e( 'De pagina die je zoekt lijkt niet te bestaan. Misschien kun je hieronder zoeken?', 'textdomain' ); ?></p>

        <?php var_dump( has_filter('get_search_form') ); ?>

        <?php get_search_form(); ?>

        <a class="btn" href="<?php echo esc_url( home_url( '/' ) ); ?>">
            <?php _e( 'Terug naar de homepage', 'textdomain' ); ?>
        </a>
        
    </div>
</section>
<?php
get_footer();