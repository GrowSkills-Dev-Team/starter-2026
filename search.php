<?php
get_header();
?>
    
        <section class="search-results with-margin">
            <div class="wrapper">
            <h1><?php printf( __( 'Zoekresultaten voor: %s', 'textdomain' ), '<span>' . get_search_query() . '</span>' ); ?></h1>
            <?php if ( have_posts() ) : ?>
                <?php while ( have_posts() ) : the_post(); ?>
                    <article class="search-result-item">
                        <h2><?php the_title(); ?></h2>
                        <?php the_excerpt(); ?>
                        <a class="btn" href="<?php the_permalink(); ?>">Lees meer</a>
                    </article>
                <?php endwhile; ?>
            <?php else : ?>
                <p><?php _e('Geen resultaten gevonden.', 'textdomain'); ?></p>
                <?php get_search_form(); ?>
            <?php endif; ?>
        </div>
    </section>

<?php
get_footer();