<?php
namespace w3um\includes;

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

class createUserMetabox {
    
    private $options = array();
    
    private function __construct( $options ) {
        
        $this->options = $options;

        add_action( 'show_user_profile', array($this,'display_callback') );
        add_action( 'edit_user_profile', array($this,'display_callback') );

    }

    public static function addMB( $metabox = 'Default Metabox' ) {
        
        $options = array(
            'id' => self::sanitize_id($metabox),
            'title' => $metabox,
        );
        
        addToMetabox::setUserMetabox( $metabox );
        
        new createUserMetabox( $options );

    }

    public function display_callback( $user ) { 
        
        $metabox_id = $this->options['id'];
        $metabox_title = $this->options['title'];
        
        $fields = addToMetabox::getFields( $metabox_id );
        
        $field_output = '';

        if( isset( $fields['error'] ) ) {
            echo $fields['error'];
            return;
        }
        
        foreach( $fields as $field_id => $field_array ) {
            
            if( ! $field_array['is_user_field'] ) continue;
            
            $value = get_user_meta( $user->ID, $field_id, true );
            // $value = maybe_unserialize( $value );

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
                    <tr class="w3um-custom-field">
                        <th><label for="{field_id}">{field_name}</label></th>
                        <td>
                            <input type="text" name="{field_id}" id="{field_id}" value="{$value}" class="{field_class} regular-text">
                            {$description}
                        </td>
                    </tr>
EOD;
            }

            // $display_function = "w3um_display_field_{$field_array['field_type']}";
            
            // if( function_exists( $display_function ) ) {
            //     $field_display = $display_function( $value, $field_array, true );
            // }

            $field_display = apply_filters( 'w3um_display_field', $field_display, $field_array['field_type'], $value, $field_array, $is_user = true );
            
            foreach( $field_array as $option_key => $option_value ) $replacement_options_key[ "{{$option_key}}" ] = $option_value;
            $field_output .= strtr( $field_display, $replacement_options_key );

        }
        
        $nonce = wp_nonce_field( 'w3um_nonce_action', "{$metabox_id}_nonce", false, false );
        echo "<h3>{$metabox_title}</h3><table class='form-table' role='presentation'><tbody>{$field_output}</tbody></table>{$nonce}";
    }
    
    private static function sanitize_id( $id ) {
        $id = strtolower(str_replace(' ','_',$id));
        $id = preg_replace('/[^0-9a-z_]/', '', $id);
        return $id;
    }

}
