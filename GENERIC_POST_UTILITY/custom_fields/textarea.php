<?php
// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


add_filter( 'GENERIC_display_field', 'GENERIC_textarea_field_display', 10, 5 );
function GENERIC_textarea_field_display( $field_display, $field_type, $value, $field_array, $is_user ) {

    if( 'textarea' != $field_type ) return $field_display;

    $description = $field_array['description'] ? '<p class="description">{description}</p>' : '';
    
    $textarea_field = <<<EOD
        <div id="{field_id}" class="GENERIC-custom-field">
            <h4 style="margin-bottom:.5em;">{field_name}</h4>
            <textarea class="{field_class}" name="{field_id}" rows="8" style="width:100%;">{$value}</textarea>
            {$description}
        </div>
EOD;

    $user_textarea_field = <<<EOD
        <tr class="GENERIC-custom-field">
            <th><label for="{field_id}">{field_name}</label></th>
            <td>
                <textarea class="{field_class}" name="{field_id}" rows="5" cols="30">{$value}</textarea>
                {$description}
            </td>
        </tr>
EOD;

    if( $is_user ) return $user_textarea_field;
    return $textarea_field;

}


add_action( 'GENERIC_save_field', 'GENERIC_textarea_save_field', 10, 5 );
function GENERIC_textarea_save_field( $post_id, $post, $field_type, $field_id, $metabox_id ) {

    if( 'textarea' != $field_type ) return;

    /* Verify the nonce before proceeding. */
    $nonce = isset( $_POST["{$metabox_id}_nonce"] ) ? $_POST["{$metabox_id}_nonce"] : false;
    if ( ! $nonce || ! wp_verify_nonce( $nonce, 'GENERIC_nonce_action' ) ) return $post_id;

    /* Get the post type object. */
    $post_type = get_post_type_object( $post->post_type );

    /* Check if the current user has permission to edit the post. */
    if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) )
        return $post_id;

    if( ! isset( $_POST[$field_id] ) ) return $post_id;

    $value = $_POST[$field_id];

    $value = $value ? wp_kses_post( $value ) : NULL; // THIS IS WHY

    $curr_value = get_post_meta( $post_id, $field_id, true );

    if( $value == $curr_value ) return $post_id;

    if ( $value === NULL ) delete_post_meta( $post_id, $field_id );
    else update_post_meta( $post_id, $field_id, $value );
}

