<?php
function gs_theme_setup_cleanup_safe() {
    if ( get_option( 'gs_cleanup_done' ) ) {
        return;
    }

    $pages = get_posts( [
        'post_type'   => 'page',
        'numberposts' => -1,
        'post_status' => 'any',
    ] );

    foreach ( $pages as $page ) {
        wp_delete_post( $page->ID, true );
    }

    $home_page_id = wp_insert_post( [
        'post_title'   => 'Home',
        'post_content' => '',
        'post_status'  => 'publish',
        'post_type'    => 'page',
    ] );

    if ( $home_page_id && ! is_wp_error( $home_page_id ) ) {
        update_option( 'show_on_front', 'page' );
        update_option( 'page_on_front', $home_page_id );
    }

    update_option( 'gs_cleanup_done', true );
}
add_action( 'after_switch_theme', 'gs_theme_setup_cleanup_safe' );