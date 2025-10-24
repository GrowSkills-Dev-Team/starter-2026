<?php

/**
 * This file contains default settings that apply to most websites.
 */

add_action( 'acf/init', function () {
	acf_add_options_page( [
		'page_title' => 'Site-options',
		'menu_title' => 'Site-options',
		'menu_slug'  => 'growskills-extra',
		'icon_url'   => 'dashicons-forms',
		'redirect'   => false,
		'autoload'   => true,
	] );
}, 5 );

add_filter( 'tiny_mce_before_init', function ( $init ) {
	$init['block_formats'] = 'Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4;';
	return $init;
} );

add_action( 'wp_before_admin_bar_render', function () {
	global $wp_admin_bar;

	$wp_admin_bar->remove_menu( 'new-content' );
	$wp_admin_bar->remove_menu( 'wpseo-menu' );
	$wp_admin_bar->remove_menu( 'customize' );
	$wp_admin_bar->remove_menu( 'comments' );
} );

add_filter( 'post_thumbnail_html', function ( $html ) {
	$html = preg_replace( '/(width|height)=\"\d*\"\s/', "", $html );

	return $html;
} );

add_filter( 'embed_oembed_html', function ( $html ) {
	return '<div class="embed-container">' . $html . '</div>';
} );

add_filter( 'upload_size_limit', function ( $size ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		$size = 4 * 1024 * 1024;
	}

	return $size;
}, 20 );

add_action( 'after_setup_theme', function () {
	update_option( 'image_default_link_type', 'none' );
	update_option( 'image_default_size', 'full' );

	remove_theme_support( 'widgets-block-editor' );
	remove_theme_support( 'core-block-patterns' );
	remove_theme_support( 'block-templates' );
	
	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	
	add_theme_support( 'html5', [
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'script',
		'style',
		'navigation-widgets',
	] );

	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'menus' );
} );

add_action( 'init', function () {
	unregister_taxonomy_for_object_type( 'category', 'post' );
	unregister_taxonomy_for_object_type( 'post_tag', 'post' );
} );

add_filter( 'excerpt_length', function () {
	return 20;
} );

add_filter( 'excerpt_more', function () {
	return '...';
} );