<?php
// Register Custom Post Type Project
function create_project_cpt() {

	$labels = array(
		'name' => _x( 'Projecten', 'Post Type General Name', 'starter-theme' ),
		'singular_name' => _x( 'Project', 'Post Type Singular Name', 'starter-theme' ),
		'menu_name' => _x( 'Projecten', 'Admin Menu text', 'starter-theme' ),
		'name_admin_bar' => _x( 'Project', 'Add New on Toolbar', 'starter-theme' ),
		'archives' => __( 'Project Archief', 'starter-theme' ),
		'attributes' => __( 'Project Attributen', 'starter-theme' ),
		'parent_item_colon' => __( 'Hoofd Project:', 'starter-theme' ),
		'all_items' => __( 'Alle Projecten', 'starter-theme' ),
		'add_new_item' => __( 'Project opstellen', 'starter-theme' ),
		'add_new' => __( 'Opstellen', 'starter-theme' ),
		'new_item' => __( 'Project opstellen', 'starter-theme' ),
		'edit_item' => __( 'Bewerken Project', 'starter-theme' ),
		'update_item' => __( 'Bijwerken Project', 'starter-theme' ),
		'view_item' => __( 'Project bekijken', 'starter-theme' ),
		'view_items' => __( 'Projecten bekijken', 'starter-theme' ),
		'search_items' => __( 'Zoeken Project', 'starter-theme' ),
		'not_found' => __( 'Geen Projecten gevonden.', 'starter-theme' ),
		'not_found_in_trash' => __( 'Geen Projecten gevonden in de prullenbak.', 'starter-theme' ),
		'featured_image' => __( 'Uitgelichte afbeelding', 'starter-theme' ),
		'set_featured_image' => __( 'Uitgelichte afbeelding instellen', 'starter-theme' ),
		'remove_featured_image' => __( 'Uitgelichte afbeelding verwijderen', 'starter-theme' ),
		'use_featured_image' => __( 'Uitgelichte afbeelding instellen', 'starter-theme' ),
		'insert_into_item' => __( 'Invoegen in Project', 'starter-theme' ),
		'uploaded_to_this_item' => __( 'Geüpload naar dit Project', 'starter-theme' ),
		'items_list' => __( 'Projecten Lijst', 'starter-theme' ),
		'items_list_navigation' => __( 'Projecten Lijst navigatie', 'starter-theme' ),
		'filter_items_list' => __( 'Filter Projecten Lijst', 'starter-theme' ),
	);
	$args = array(
		'label' => __( 'Project', 'starter-theme' ),
		'description' => __( 'Project', 'starter-theme' ),
		'labels' => $labels,
		'menu_icon' => 'dashicons-admin-page',
		'supports' => array('title', 'editor', 'thumbnail'),
		'taxonomies' => array(),
		'public' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'menu_position' => 20,
		'show_in_admin_bar' => true,
		'show_in_nav_menus' => true,
		'can_export' => true,
		'has_archive' => false,
		'hierarchical' => false,
		'exclude_from_search' => false,
		'show_in_rest' => true,
		'publicly_queryable' => true,
		'capability_type' => 'post',
	);
	register_post_type( 'project', $args );

}
add_action( 'init', 'create_project_cpt', 0 );