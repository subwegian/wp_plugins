<?php
namespace GENERIC\includes;

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


//THIS IS POINTLESS ... Actually maybe not.

class createShortcodeFrontendField {

    private $options = array();
    
    private function __construct( $options ) {
        
        $this->options = $options;

        add_shortcode( 'GENERIC_frontend_field', array($this,'frontend_field_shortcode') );
        add_action( 'load-post-new.php', array($this,'meta_boxes_setup') );
        
        add_filter( "postbox_classes_{$options['screen']}_{$options['id']}", function( $classes ) {
            array_push($classes,'GENERIC_metabox');
            return $classes;
        });

    }

    public static function addMB( $post_type = 'page', $metabox = 'Default Metabox', $placement = 'normal', $priority = 'high', $callback_args = NULL ) {
        
        $post_type = self::sanitize_id($post_type);
        $post_type = substr( $post_type, 0, 20);
        
        $options = array(
            'id' => self::sanitize_id($metabox),
            'title' => $metabox,
            'screen' => $post_type,
            'context' => $placement,
            'priority' => $priority,
            'callback_args' => (array) $callback_args,
        );
        
        new createMetabox( $options );

    }

    public function frontend_field_shortcode( $atts ) { 
        
    }

    private static function sanitize_id( $id ) {
        $id = strtolower(str_replace(' ','_',$id));
        $id = preg_replace('/[^0-9a-z_]/', '', $id);
        return $id;
    }

}

