<?php
/**
 * Plugin name: Gixo Tools
 * Author: Smartcat
 * 
 * 
 * 
 */

namespace gixo;

if( !defined( 'ABSPATH' ) ) {
    die();
}

const HOOK = 'init';

include_once dirname( __FILE__ ) . '/includes/trait-Singleton.php';
include_once dirname( __FILE__ ) . '/includes/functions-api-sync.php';




register_activation_hook(__FILE__, 'gixo\activate' );
register_deactivation_hook( __FILE__, 'gixo\deactivate' );


function activate() {
    
    wp_schedule_event( time(), 'hourly', 'gixo_get_sessions' );
    
}

function deactivate() {
    
    wp_clear_scheduled_hook( 'gixo_get_sessions' );
    
}




add_action( 'init', function() {
    
    // remove this
    $labels = array(
        'name'                  => _x( 'Sessions', 'Post Type General Name', 'gixo' ),
        'singular_name'         => _x( 'Session', 'Post Type Singular Name', 'gixo' ),
        'menu_name'             => __( 'Sessions', 'gixo' ),
        'name_admin_bar'        => __( 'Session', 'gixo' ),
        'archives'              => __( 'Item Archives', 'gixo' ),
        'parent_item_colon'     => __( 'Parent Sessions:', 'gixo' ),
        'all_items'             => __( 'All Sessions', 'gixo' ),
        'add_new_item'          => __( 'Add New Session', 'gixo' ),
        'add_new'               => __( 'New Session', 'gixo' ),
        'new_item'              => __( 'New Session', 'gixo' ),
        'edit_item'             => __( 'Edit Session', 'gixo' ),
        'update_item'           => __( 'Update Session', 'gixo' ),
        'view_item'             => __( 'View Session', 'gixo' ),
        'search_items'          => __( 'Search sessions', 'gixo' ),
        'not_found'             => __( 'No sessions found', 'gixo' ),
        'not_found_in_trash'    => __( 'No sessions found in Trash', 'gixo' ),
        'featured_image'        => __( 'Featured Image', 'gixo' ),
        'set_featured_image'    => __( 'Set featured image', 'gixo' ),
        'remove_featured_image' => __( 'Remove featured image', 'gixo' ),
        'use_featured_image'    => __( 'Use as featured image', 'gixo' ),
        'insert_into_item'      => __( 'Insert into item', 'gixo' ),
        'uploaded_to_this_item' => __( 'Uploaded to this item', 'gixo' ),
        'items_list'            => __( 'Items list', 'gixo' ),
        'items_list_navigation' => __( 'Items list navigation', 'gixo' ),
        'filter_items_list'     => __( 'Filter items list', 'gixo' ),
    );
    $args = array(
        'label'                 => __( 'Session', 'gixo' ),
        'description'           => __( 'Gixo Sessions.', 'gixo' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'rewrite'               => array( 'slug' => 'session' ),
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-clock',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => false,		
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
    );
    register_post_type( 'session', $args );    

});
