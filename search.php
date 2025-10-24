<?php
get_header();
?>

<main id="content" role="main">
    <header class="search-header">
        <h1>
            <?php printf( __( 'Zoekresultaten voor: %s', 'textdomain' ), '<span>' . get_search_query() . '</span>' ); ?>
        </h1>
    </header>

    <?php if ( have_posts() ) : ?>
        <?php while ( have_posts() ) : the_post(); ?>
            <article <?php post_class(); ?>>
                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                <?php the_excerpt(); ?>
            </article>
        <?php endwhile; ?>
    <?php else : ?>
        <p><?php _e('Geen resultaten gevonden.', 'textdomain'); ?></p>
        <?php get_search_form(); ?>
    <?php endif; ?>
</main>

<?php
get_footer();