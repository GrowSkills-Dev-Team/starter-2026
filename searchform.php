<?php
/**
 * Accessible search form template
 */
?>

<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
    <label for="search-field" class="screen-reader-text">
        <?php _e( 'Zoek naar:', 'textdomain' ); ?>
    </label>
    <input type="search"
           id="search-field"
           class="search-field"
           placeholder="<?php esc_attr_e( 'Zoeken…', 'textdomain' ); ?>"
           value="<?php echo get_search_query(); ?>"
           name="s"
           aria-label="<?php esc_attr_e( 'Zoekterm', 'textdomain' ); ?>" />
    
    <button type="submit" class="search-submit">
        <?php esc_html_e( 'Zoeken', 'textdomain' ); ?>
    </button>
</form>