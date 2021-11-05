<?php
// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/************************************************************

NOTE: Apparently it isn't safe to put a TinyMCE WYSIWYG in a movable Metabox, 
BUT I'M DOING IT ANYWAY.

https://developer.wordpress.org/reference/functions/wp_editor/

    Once initialized the TinyMCE editor cannot be safely moved in the DOM. For that 
    reason running wp_editor() inside of a meta box is not a good idea unless only 
    Quicktags is used. On the post edit screen several actions can be used to include 
    additional editors containing TinyMCE: ‘edit_page_form’, ‘edit_form_advanced’ and 
    ‘dbx_post_sidebar’. See https://core.trac.wordpress.org/ticket/19173 for 
    more information.

************************************************************/

add_filter( 'GENERIC_display_field', 'GENERIC_textarea_tinymce_field_display', 10, 5 );
function GENERIC_textarea_tinymce_field_display( $field_display, $field_type, $value, $field_array, $is_user ) {

    if( 'textarea_tinymce' != $field_type ) return $field_display;

    $description = $field_array['description'] ? '<p class="description">{description}</p>' : '';
    $field_id = $field_array['field_id'];
    
    $fake_value = <<<EOD
<h2>What I want</h2>
To do is see <strong>how this</strong> works.
<blockquote>quote section goes here.</blockquote>
<ul>
 	<li>first in a list</li>
 	<li>second</li>
 	<li>now it's done.</li>
</ul>
<p style="text-align: center;">Let's center this</p>
How about a <a href="https://google.com">link</a> in this spot?
EOD;
    
    $value = html_entity_decode($value);
    // $value = html_entity_decode($fake_value);

    ob_start();
    wp_editor( $value, $field_id, array(
        'wpautop'       => true,
        'media_buttons' => true,
        'textarea_name' => $field_id,
        'textarea_rows' => 15,
        'tinymce'       => true,
        'teeny'         => false,
        'editor_class'  => 'GENERIC_tinymce',
    ) );
    $wysiwyg = ob_get_clean();
    
    $textarea_field = <<<EOD
        <h4 style="margin-bottom:.5em;">{field_name}</h4>
        {$wysiwyg}
        {$description}
EOD;

    $user_textarea_field = <<<EOD
        <tr class="GENERIC-custom-field">
            <th><label for="{field_id}">{field_name}</label></th>
            <td>
            <div style='max-width:800px;'>
                {$wysiwyg}
                {$description}
            </div>
            </td>
        </tr>
EOD;
    
    $user_textarea_field = str_replace( 'cols="40"', 'cols="40" style="width:100%;"', $user_textarea_field );

    if( $is_user ) return $user_textarea_field;
    return $textarea_field;

}


add_action( 'GENERIC_save_field', 'GENERIC_textarea_tinymce_save_field', 10, 5 );
function GENERIC_textarea_tinymce_save_field( $post_id, $post, $field_type, $field_id, $metabox_id ) {

    if( 'textarea_tinymce' != $field_type ) return;

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



