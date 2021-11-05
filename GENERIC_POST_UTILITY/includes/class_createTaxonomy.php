<?php
namespace GENERIC\includes;

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

class createTaxonomy {
    
    private function __construct( $options ) {
        
        extract( $options );
    
        $labels = array(
            'name'              => $plural_name,
            'singular_name'     => $taxonomy_name,
            'search_items'      => "Search $post_type_name $plural_name",
            'all_items'         => "All $plural_name",
            'parent_item'       => "Parent $post_type_name $taxonomy_name",
            'parent_item_colon' => "Parent $post_type_name $taxonomy_name:",
            'edit_item'         => "Edit $post_type_name $taxonomy_name'",
            'update_item'       => "Update $post_type_name $taxonomy_name",
            'add_new_item'      => "Add New $taxonomy_name",
            'new_item_name'     => "New $post_type_name $taxonomy_name",
            'menu_name'         => $menu_name,
        );

        $args = array(
            'labels' => $labels,
            'hierarchical' => true,
        );

        if( $override_args ) $args = array_replace_recursive( $args, $override_args );

        $screen_post_type = substr( $post_type, 0, 20);
        
        add_action( 'init', function() use ( $custom_slug, $screen_post_type, $args ) {
            register_taxonomy( $custom_slug, $screen_post_type, $args );
        });

    }

    public static function addTax( $post_type = 'page', $taxonomy_name = 'Default Category', $plural_name = NULL, $override_args = NULL, $custom_slug = NULL, $menu_name = NULL ) {
        
        $plural_name = $plural_name ?: $taxonomy_name . 's';
        $menu_name = $menu_name ?: $taxonomy_name;
        
        $options = array(
            'taxonomy_name' => $taxonomy_name,
            'post_type_name' => $post_type,
            'post_type' => self::sanitize_id($post_type),
            'plural_name' => $plural_name,
            'override_args' => $override_args,
            'custom_slug' => $custom_slug ?: self::sanitize_id($plural_name),
            'menu_name' => $menu_name,
        );
        
        new createTaxonomy( $options );
    }

    private function sanitize_id( $id ) {
        $id = strtolower(str_replace(' ','_',$id));
        $id = preg_replace('/[^0-9a-z_]/', '', $id);
        return $id;
    }

}