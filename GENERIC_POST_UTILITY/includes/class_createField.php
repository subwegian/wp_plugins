<?php
namespace GENERIC\includes;

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


class createField {
    
    // private static $instance = NULL;

    private $field_type = NULL;
    private $field_id = NULL;
    private $metabox_id = NULL;
    
    public function __construct( $options ) {
        
        $this->field_type = $options['field_type'];
        $this->field_id = $options['field_id'];
        $this->metabox_id = $options['metabox_id'];
        
        // Store the field data in an object array to be retrieved by createMetabox()
        addToMetabox::addField( $options['metabox_id'], $options['field_id'], $options );
        
        // Register the save_post function
        $this->addSavePost();
        
    }

    public static function addCF($metabox = 'Default Metabox', $field_name = 'Default Field', $field_type = NULL, $description = NULL, $prefix = NULL ) {
        
        $options = array(
            'field_name' => $field_name,
            'field_id' => $prefix . self::sanitize_id($field_name),
            'field_class' => $prefix . self::sanitize_class($field_name),
            'description' => $description,
            'field_type' => $field_type,
            'metabox_id' => self::sanitize_id($metabox),
            'is_user_field' => addToMetabox::isUserMetabox( $metabox ),
        );
        
        new createField( $options );
        // self::$instance = new createField( $options );
        
    }

    // public static function getInstance() {
    //     if( self::$instance == NULL ) return false;
    //     return self::$instance;
    // }

    public static function getInstance( $metabox = 'Default Metabox', $field_name = 'Default Field' ) {
        $options = array(
            'field_id' => self::sanitize_id($field_name),
            'metabox_id' => self::sanitize_id($metabox),
        );
        return new createField( $options );
    }

    private function addSavePost() {

        $field_type = $this->field_type;
        $field_id = $this->field_id;
        $metabox_id = $this->metabox_id;
        $is_user_metabox = addToMetabox::isUserMetabox( $metabox_id );
        
        // $save_function = "GENERIC_save_field_{$field_type}";
        $user_save_function = "GENERIC_save_user_field_{$field_type}";

        add_action( 'save_post', function ($post_id, $post) use ($field_id, $metabox_id) {
            return $this->save_post_meta($post_id, $post, $field_id, $metabox_id);
        }, 10, 2 );

        add_action( 'save_post', function ( $post_id, $post ) use ( $field_type, $field_id, $metabox_id ) {
            do_action( 'GENERIC_save_field', $post_id, $post, $field_type, $field_id, $metabox_id );
        }, 20, 2 );

        // return;
        
        // if( ! $is_user_metabox && function_exists( $save_function ) ) {
        //     add_action( 'save_post', function ($post_id, $post) use ($field_id, $metabox_id, $save_function) {
        //         return $save_function($post_id, $post, $field_id, $metabox_id);
        //     }, 10, 2 );
            
        //     return;            
        // }

        if( $is_user_metabox && function_exists( $user_save_function ) ) {
            add_action( 'edit_user_profile_update', function ($user_id) use ($field_id, $metabox_id, $user_save_function) {
                return $user_save_function($user_id, $field_id, $metabox_id);
            }, 10, 1 );
            add_action( 'personal_options_update', function ($user_id) use ($field_id, $metabox_id, $user_save_function) {
                return $user_save_function($user_id, $field_id, $metabox_id);
            }, 10, 1 );
            
            return;            
        }
        
        if( $is_user_metabox ) {
            add_action( 'edit_user_profile_update', function ($user_id) use ($field_id, $metabox_id) {
                return $this->save_user_meta($user_id, $field_id, $metabox_id);
            }, 10, 1 );
            add_action( 'personal_options_update', function ($user_id) use ($field_id, $metabox_id) {
                return $this->save_user_meta($user_id, $field_id, $metabox_id);
            }, 10, 1 );
            
            return;
        }
    }

    // This should just be registered as add_action( 'GENERIC_save_field' ... ) like the rest (but still earlier)
    public function save_post_meta( $post_id, $post, $field_id, $metabox_id ) {

        // This should probably be here and on the individual ones. Or maybe it should be earlier.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id;

        /* Verify the nonce before proceeding. */
        $nonce = isset( $_POST["{$metabox_id}_nonce"] ) ? $_POST["{$metabox_id}_nonce"] : false;
        if ( ! $nonce || ! wp_verify_nonce( $nonce, 'GENERIC_nonce_action' ) ) return $post_id;

        /* Get the post type object. */
        $post_type = get_post_type_object( $post->post_type );

        /* Check if the current user has permission to edit the post. */
        if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) )
            return $post_id;

        if( ! isset( $_POST[$field_id] ) && ! isset( $_FILES[$field_id] ) ) return $post_id;
        
        $value = $_POST[$field_id];
        
        if( ! is_array( $value ) ) {
            $value = $value ? sanitize_text_field( $value ) : NULL;
        } else {
            foreach( $value as &$val ) {
                $val = sanitize_text_field( $val );
            }
        }

        $curr_value = get_post_meta( $post_id, $field_id, true );

        if( $value == $curr_value ) return $post_id;

        if ( $value === NULL ) delete_post_meta( $post_id, $field_id );
        else update_post_meta( $post_id, $field_id, $value );

    }

    public function save_user_meta( $user_id, $field_id, $metabox_id ) {
        
        if ( ! current_user_can( 'edit_user', $user_id ) ) return false;
        
        /* Verify the nonce before proceeding. */
        $nonce = isset( $_POST["{$metabox_id}_nonce"] ) ? $_POST["{$metabox_id}_nonce"] : false;
        if ( ! $nonce || ! wp_verify_nonce( $nonce, 'GENERIC_nonce_action' ) ) return $user_id;

        if( ! isset( $_POST[$field_id] ) && ! isset( $_FILES[$field_id] ) ) return $user_id;
        
        $value = $_POST[$field_id];
        
        if( ! is_array( $value ) ) {
            $value = $value ? sanitize_text_field( $value ) : NULL;
        } else {
            foreach( $value as &$val ) {
                $val = sanitize_text_field( $val );
            }
        }

        $curr_value = get_user_meta( $user_id, $field_id, true );

        if( $value == $curr_value ) return $user_id;

        if ( $value === NULL ) delete_user_meta( $user_id, $field_id );
        else update_user_meta( $user_id, $field_id, $value );
        
    }

    private static function sanitize_id( $id ) {
        $id = strtolower(str_replace(' ','_',$id));
        $id = preg_replace('/[^0-9a-z_]/', '', $id);
        return $id;
    }

    private static function sanitize_class( $id ) {
        $id = strtolower(str_replace(' ','-',$id));
        $id = preg_replace('/[^0-9a-z-]/', '', $id);
        return $id;
    }

}

