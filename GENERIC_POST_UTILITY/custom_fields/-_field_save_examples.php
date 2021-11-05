<?php
// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


function DISABLED_GENERIC_save_field_text( $post_id, $post, $field_id, $metabox_id ) {

    if( 'text' != $field_type ) return;
    
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
    else update_post_meta( $post_id, $field_id, $value . '_custom_save' );
}


function DISABLED_GENERIC_save_user_field_text( $user_id, $field_id, $metabox_id ) {
        
    if ( ! current_user_can( 'edit_user', $user_id ) ) return false;

    if( 'text' != $field_type ) return;

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
    else update_user_meta( $user_id, $field_id, $value . '_custom_save' );

}




