<?php
namespace GENERIC\includes;

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

class createAdminColumns {
    
    private $options = array();
    
    private function __construct( $options ) {
        
        $this->options = $options;
        $post_type = $options['post_type'];
        
        add_filter( "manage_{$post_type}_posts_columns", array($this,'set_custom_columns') );
        add_action( "manage_{$post_type}_posts_custom_column" , array($this,'set_custom_column_contents'), 10, 2 );
        add_filter( "manage_edit-{$post_type}_sortable_columns", array($this,'sortable_custom_column') );
        add_action( 'pre_get_posts', array($this,'custom_column_orderby') );

    }

    public static function addAC( $post_type = 'page', $field_name = 'Default', $column_name = NULL, $field_type = 'meta' ) {
        
        $options = array(
            'post_type' => $prefix . self::sanitize_id($post_type),
            'field_name' => self::sanitize_id($field_name),
            'column_name' => $column_name ?: "{$field_name} Column",
            'field_type' => $field_type,
        );
        
        new createAdminColumns( $options );
    }

    public function set_custom_columns($columns) {
        
        $field_name = $this->options['field_name'];
        $column_name = $this->options['column_name'];
        
        $columns[ $field_name ] = $column_name;
        
        $date = $columns['date'];
        unset($columns['date']);
        $columns['date'] = $date;

        return $columns;
    }


    public function set_custom_column_contents( $column, $post_id ) {

        $field_name = $this->options['field_name'];

        if( $column != $field_name ) return;
        
        $column_contents_function = "GENERIC_column_contents_{$field_name}";
        if( function_exists( $column_contents_function ) ) {
            echo $column_contents_function( $field_name, $post_id );
            return;
        }

        echo get_post_meta( $post_id, $field_name, true );

    }

    public function sortable_custom_column( $columns ) {

        $field_name = $this->options['field_name'];

        $columns[ $field_name ] = $field_name;
        return $columns;
    }


    public function custom_column_orderby( $query ) {

        $orderby = $query->get( 'orderby' );
        
        $field_name = $this->options['field_name'];
        $post_type = $this->options['post_type'];
        
        if( $orderby != $field_name ) return;
        
        global $wpdb;

        $field_query = <<<EOD
            SELECT p.ID, pm.meta_value
            FROM {$wpdb->prefix}posts p
            LEFT JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '{$field_name}'
            WHERE p.post_type = '{$post_type}'
EOD;

        $column_orderby_query_function = "GENERIC_column_orderby_query_{$field_name}";
        if( function_exists( $column_orderby_query_function ) ) {
            $field_query = $column_orderby_query_function( $field_name, $post_type, $field_query, $query );
        }

        $query_results = $wpdb->get_results( $field_query );
        foreach( $query_results as $row ) $value_list[ $row->meta_value . '_' . $row->ID ] = $row->ID;

        if( $value_list ) ksort( $value_list );

        $column_sort_override_function = "GENERIC_column_sort_override_{$field_name}";
        if( function_exists( $column_sort_override_function ) ) {
            $value_list = $column_sort_override_function( $field_name, $post_type, $value_list, $query_results );
        }

        if( $query->get( 'order' ) == 'desc' ) $value_list = array_reverse( $value_list );

        $query->set( 'post__in', $value_list );
        $query->set( 'orderby', 'post__in' );
    }

    private function sanitize_id( $id ) {
        $id = strtolower(str_replace(' ','_',$id));
        $id = preg_replace('/[^0-9a-z_]/', '', $id);
        return $id;
    }


}