<?php
namespace GENERIC\includes;

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

//class addToFilter {
//
//    private static $instance = NULL;
//    private static $filter_array = array();
//    private static $callback = false;
//
//    public static function addFilter( $filter_options_array ) {
//
//        if( self::$instance == NULL ) {
//            self::$instance = new addToFilter();
//        }
//        
//        $post_type = $filter_options_array['post_type'];
//
//        self::$filter_array[$post_type][] = $filter_options_array;
//    }
//
//    public static function getFilters( $post_type, $callback ) {
//
//        if (self::$instance == NULL) {
//            return array('error' => 'addToFilter Uninitialized.');
//        }
//        
//        if( self::$callback == $callback ) return false;
//        
//        if( isset(self::$filter_array[$post_type]) && ! empty(self::$filter_array[$post_type]) ) {
//            self::$callback = $callback;
//            return self::$filter_array[$post_type];
//        } else {
//            return false;
//        }
//        
//    }
//
//}

class createAdminFilter {
    
    private $options = array();
    
    private function __construct( $options ) {
        
        //$this->options[] = $options;
        addToFilter::addFilter( $options );

        add_action( 'restrict_manage_posts', array($this,'custom_fields_admin_filter'), 10, 2 );
        add_filter( 'parse_query', array($this,'custom_fields_admin_filter_parse'), 10, 3 );

    }


    public static function addAF( $post_type = 'page', $field_name = 'Default', $column_name = NULL, $field_type = 'meta' ) {
        
        $options = array(
            'post_type' => $prefix . self::sanitize_id($post_type),
            'field_name' => self::sanitize_id($field_name),
            'field_name_unfiltered' => $field_name,
            'column_name' => $column_name ?: "{$field_name} Column",
            'field_type' => $field_type,
        );
        
        new createAdminFilter( $options );
        
    }

    public static function custom_fields_admin_filter( $current_post_type, $second = 'top' ) {
        
        $options = addToFilter::getFilters( $current_post_type, 'filter' );
        if( ! $options ) return;
        
        foreach( $options as $option ) {
            $field_name = $option['field_name'];
            $field_name_unfiltered = $option['field_name_unfiltered'];
            $post_type = $option['post_type'];

            // only add filter to post type you want
            if( $post_type != $current_post_type ) continue;

            global $wpdb;

            $field_query = <<<EOD
                SELECT p.ID, pm.meta_value
                FROM {$wpdb->prefix}posts p
                LEFT JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '{$field_name}'
                WHERE p.post_type = '{$post_type}'
EOD;

            $column_filter_query_function = "GENERIC_filter_query_{$field_name}";
            if( function_exists( $column_filter_query_function ) ) {
                $field_query = $column_filter_query_function( $field_name, $post_type, $field_query );
            }

            $query_results = $wpdb->get_results( $field_query );
            $value_list = array();
            foreach( $query_results as $row ) $value_list[ $row->meta_value ] = $row->meta_value;//$row->ID;

            if( $value_list ) ksort( $value_list );
            $value_list = array_filter( $value_list );

            $filter_sort_override_function = "GENERIC_filter_sort_override_{$field_name}";
            if( function_exists( $filter_sort_override_function ) ) {
                $value_list = $filter_sort_override_function( $field_name, $post_type, $value_list, $query_results );
            }

            $option_list = '';
            foreach ($value_list as $label => $value) {

                $selected = $_GET[ $field_name ] == $value ? ' selected' : '';
                $option_list .= "<option value='{$value}'{$selected}>{$label}</option>";

            }

            $output = <<<EOD
            <select name='{$field_name}'>
                <option value="">All {$field_name_unfiltered}</option>
                {$option_list}
            </select>
EOD;

            echo $output;

        }
    }


    public function custom_fields_admin_filter_parse( $query ) {

        $current_post_type = $query->get('post_type');
        $is_admin = $query->is_admin;
        if( ! $is_admin ) return;

        global $pagenow;
        if( $pagenow != 'edit.php' ) return;

        $options = addToFilter::getFilters( $current_post_type, 'parse' );
        if( ! $options ) return;

        $meta_query = array();
        
        //https://stackoverflow.com/questions/55066721/filter-admin-post-list-with-multiple-filters-how-to-structure-query-object-with
        foreach( $options as $option ) {
            $field_name = $option['field_name'];
            $post_type = $option['post_type'];

            // only parse filter on correct post type
            if( $post_type != $current_post_type ) continue;

            $filter_value = $_GET[ $field_name ];
            if( ! $filter_value ) continue;
            
            $filter_query_override_function = "GENERIC_filter_query_override_{$field_name}";
            if( function_exists( $filter_query_override_function ) ) {
                $filter_query_override_function( $field_name, $filter_value, $post_type, $meta_query, $query );
                continue;
            }

            $meta_query[] = array(
                'key'  => $field_name,
                'value' => $filter_value,
                'compare' => '=',
                //'type' => 'CHAR',
            );
        }

        if( count( $meta_query ) > 1 ) $meta_query['relation'] = 'AND';
        $query->set( 'meta_query', $meta_query );
        
    }

    private function sanitize_id( $id ) {
        $id = strtolower(str_replace(' ','_',$id));
        $id = preg_replace('/[^0-9a-z_]/', '', $id);
        return $id;
    }

}