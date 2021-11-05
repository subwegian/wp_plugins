<?php
namespace GENERIC\includes;

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


class addToMetabox {

    private static $instance = NULL;
    
    private static $fields_array = array();

    public static function addField( $metabox, $field_id, $field_options_array ) {

        if( self::$instance == NULL ) {
            self::$instance = new addToMetabox();
        }
        
        $metabox_id = self::sanitize_id( $metabox );

        self::$fields_array[$metabox_id][$field_id] = $field_options_array;
    }

    public static function getAllFields() {
        if (self::$instance == NULL) {
            return array('error' => 'addToMetabox Uninitialized.');
        }
        if( ! empty(self::$fields_array) ) {
            return self::$fields_array;
        } else {
            return array('error' => 'No fields added.');
        }
    }

    public static function getFields( $metabox ) {

        if (self::$instance == NULL) {
            return array('error' => 'addToMetabox Uninitialized.');
        }
        
        $metabox_id = self::sanitize_id( $metabox );
        
        if( isset(self::$fields_array[$metabox_id]) && ! empty(self::$fields_array[$metabox_id]) ) {
            return self::$fields_array[$metabox_id];
        } else {
            return array('error' => 'No fields added.');
        }
        
    }
    
    public static function setUserMetabox( $metabox ) {

        if( self::$instance == NULL ) {
            self::$instance = new addToMetabox();
        }
        
        $metabox_id = self::sanitize_id( $metabox );

        self::$fields_array["{$metabox_id}_user"] = true;

    }

    public static function isUserMetabox( $metabox ) {

        if (self::$instance == NULL) {
            return false;
        }
        
        $metabox_id = self::sanitize_id( $metabox );

        return self::$fields_array["{$metabox_id}_user"];

    }

    private static function sanitize_id( $id ) {
        $id = strtolower(str_replace(' ','_',$id));
        $id = preg_replace('/[^0-9a-z_]/', '', $id);
        return $id;
    }

}

