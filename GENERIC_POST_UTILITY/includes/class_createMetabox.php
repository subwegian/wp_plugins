<?php
namespace GENERIC\includes;

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


class createMetabox {

    // private static $instance = NULL;

    private $options = array();
    
    private function __construct( $options ) {
        
        $this->options = $options;

        add_action( 'load-post.php', array($this,'meta_boxes_setup') );
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
        // self::$instance = new createMetabox( $options );
        
    }

    // public static function getInstance() {
    //     if( self::$instance == NULL ) return false;
    //     return self::$instance;
    // }
    // public static function getInstance( $metabox = 'Test Metabox' ) {
    //     $options = array(
    //         'id' => self::sanitize_id( $metabox ),
    //     );
    //     return new createMetabox( $options );
    // }

    public static function getFieldOutput( $metabox = 'Default Metabox', $post = NULL ) {
        if( ! $post ) global $post;
        $options = array(
            'id' => self::sanitize_id( $metabox ),
        );
        $inst = new createMetabox( $options );

        ob_start();
        $inst->display_callback( $post );
        return ob_get_clean();
    }

    public function display_callback( $post, $callback_args = array() ) { 
        
        $metabox_id = $this->options['id'];
        
        $fields = addToMetabox::getFields( $metabox_id );
        
        $field_output = '';

        if( isset( $fields['error'] ) ) {
            echo $fields['error'];
            return;
        }
        
        foreach( $fields as $field_id => $field_array ) {
            
            if( $field_array['is_user_field'] ) continue;
            
            $value = get_post_meta( $post->ID, $field_id, true );
            //$value = maybe_unserialize( $value );

            if( is_array($value) ) {
                foreach( $value as &$value_data ) { 
                    if( ! is_array( $value_data ) ) $value_data = esc_attr($value_data); 
                }
            } else {
                $value = esc_attr($value);
            }
            
            $field_display = "<div>Display for field type '{$field_array['field_type']}' doesn't exist.</div>";
            
            if( $field_array['field_type'] == NULL ) {
                $description = $field_array['description'] ? '<p class="description">{description}</p>' : '';
                $field_display = <<<EOD
                    <div id="{field_id}" class="GENERIC-custom-field">
                        <h4 style="margin-bottom:.5em;">{field_name}</h4>
                        <input class="{field_class}" type="text" name="{field_id}" size="50" value="{$value}" />
                        {$description}
                    </div>
EOD;
            }

            // $display_function = "GENERIC_display_field_{$field_array['field_type']}";
            
            // if( function_exists( $display_function ) ) {
            //     $field_display = $display_function( $value, $field_array, false );
            // }

            $field_display = apply_filters( 'GENERIC_display_field', $field_display, $field_array['field_type'], $value, $field_array, $is_user = false );
            
            foreach( $field_array as $option_key => $option_value ) $replacement_options_key[ "{{$option_key}}" ] = $option_value;
            $field_output .= strtr( $field_display, $replacement_options_key );

        }

        $nonce = wp_nonce_field( 'GENERIC_nonce_action', "{$metabox_id}_nonce", false, false );
        echo $field_output . $nonce;

    }
    
    public function register_meta_boxes() {
        extract($this->options);

        foreach( $callback_args as $param => $value ) {
            if( $param == 'conditional_func' ) {
                global $post;
                if( ! $value( $post ) ) return;
            }
        }

        add_meta_box( $id, $title, array($this,'display_callback'), $screen, $context, $priority, $callback_args );
    }

    public function meta_boxes_setup() { 

        add_action( 'add_meta_boxes', array($this,'register_meta_boxes') );

    }

    private static function sanitize_id( $id ) {
        $id = strtolower(str_replace(' ','_',$id));
        $id = preg_replace('/[^0-9a-z_]/', '', $id);
        return $id;
    }

}

