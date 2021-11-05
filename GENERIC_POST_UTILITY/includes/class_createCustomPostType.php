<?php
namespace GENERIC\includes;

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

class createCustomPostType {
    
    private function __construct( $options ) {
        
        extract( $options );
    
        $labels = array(
            'name'               => $plural_name,
            'singular_name'      => $post_type_name,
            'menu_name'          => $plural_name,
            'name_admin_bar'     => $post_type_name,
            'add_new'            => "Add New $post_type_name",
            'add_new_item'       => "Add New $post_type_name",
            'new_item'           => "New $post_type_name",
            'edit_item'          => "Edit $post_type_name",
            'view_item'          => "View $post_type_name",
            'all_items'          => "All $plural_name",
            'search_items'       => "Search $plural_name",
            'parent_item_colon'  => "Parent $post_type_name",
            'not_found'          => "No $plural_name Found",
            'not_found_in_trash' => "No $plural_name Found in Trash",
        );

        $args = array(
            'labels'              => $labels,
            'public'              => false,
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'show_ui'             => true,
            'show_in_nav_menus'   => true,
            'show_in_menu'        => true,
            'show_in_admin_bar'   => false,
            'menu_position'       => 5,
            'menu_icon'           => 'dashicons-admin-generic',
//            'capability_type'     => array($post_type, $post_type_plural), // These create custom capabilities that must be assigned
//            'map_meta_cap'        => true,
            'hierarchical'        => false,
            'supports'            => array( 'title', 'editor', 'revisions' ),
            'has_archive'         => false,
            'rewrite'             => array( 'slug' => $custom_slug ),
            'query_var'           => true,
            //'taxonomies'          => array( 'category' ), // Use class instead, I think
        );

        if( $override_args ) $args = array_replace_recursive( $args, $override_args );

        $screen_post_type = substr( $post_type, 0, 20);
        
        add_action( 'init', function() use ( $screen_post_type, $args ) {
            register_post_type( $screen_post_type, $args );
        });

    }

    public static function addCPT( $post_type_name = 'Default', $plural_name = NULL, $override_args = NULL, $custom_slug = NULL, $prefix = NULL ) {
       
        $plural_name = $plural_name ?: $post_type_name . 's';
        
        $options = array(
            'post_type_name' => $post_type_name,
            'plural_name' => $plural_name,
            'override_args' => $override_args,
            'custom_slug' => $custom_slug ?: self::sanitize_id($plural_name),
            'prefix' => $prefix,
            'post_type' => $prefix . self::sanitize_id($post_type_name),
            'post_type_plural' => $prefix . self::sanitize_id($plural_name),
        );
        
        new createCustomPostType( $options );
    }

    private function sanitize_id( $id ) {
        $id = strtolower(str_replace(' ','_',$id));
        $id = preg_replace('/[^0-9a-z_]/', '', $id);
        return $id;
    }

}
