<?php
namespace GENERIC\includes;

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

class addToFilter {

    private static $instance = NULL;
    private static $filter_array = array();
    private static $callback = false;

    public static function addFilter( $filter_options_array ) {

        if( self::$instance == NULL ) {
            self::$instance = new addToFilter();
        }
        
        $post_type = $filter_options_array['post_type'];

        self::$filter_array[$post_type][] = $filter_options_array;
    }

    public static function getFilters( $post_type, $callback ) {

        if (self::$instance == NULL) {
            return array('error' => 'addToFilter Uninitialized.');
        }
        
        if( self::$callback == $callback ) return false;
        
        if( isset(self::$filter_array[$post_type]) && ! empty(self::$filter_array[$post_type]) ) {
            self::$callback = $callback;
            return self::$filter_array[$post_type];
        } else {
            return false;
        }
        
    }

}
