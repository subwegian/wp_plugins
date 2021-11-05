<?php
// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


add_action( 'admin_enqueue_scripts', function( $hook ) {

    $pages_arr = array(
        'post.php',
        'post-new.php',
//        'profile.php',
//        'user-edit.php',
    );
    
    $post_type = get_post()->post_type;
    
    if( $post_type == 'itinerary' && in_array( $hook, $pages_arr ) ){
        wp_enqueue_editor();
//        wp_enqueue_script( 'GENERIC-general-repeater', GENERIC_url() . 'js/general_repeater.js', array('jquery'), null, true );
    }

});


/************************************************************

NOTE: Apparently it isn't safe to put a TinyMCE WYSIWYG in a movable Metabox, so
this one just uses the simple editor thing.

https://developer.wordpress.org/reference/functions/wp_editor/

    Once initialized the TinyMCE editor cannot be safely moved in the DOM. For that 
    reason running wp_editor() inside of a meta box is not a good idea unless only 
    Quicktags is used. On the post edit screen several actions can be used to include 
    additional editors containing TinyMCE: ‘edit_page_form’, ‘edit_form_advanced’ and 
    ‘dbx_post_sidebar’. See https://core.trac.wordpress.org/ticket/19173 for 
    more information.

************************************************************/


function GENERIC_display_field_details_repeater( $value, $field_array, $is_user = false ) {
    
    $description = $field_array['description'] ? '<p class="description">{description}</p>' : '';
    $field_id = $field_array['field_id'];
    
    if( is_array( $value ) ) {
        foreach($value as $a=>$b) foreach($b as $k=>$v) $o[$k][$a]=$v;
        $value = $o;
    } else{ $value = (array) $value; }

    
    $field_num = 1;
    foreach( $value as $contents ){
        
        ob_start();
        wp_editor( $contents['textarea'], "{$field_id}_orig_{$field_num}", array(
            'wpautop'       => true,
            'media_buttons' => false,
            'textarea_name' => "{$field_id}[textarea][]",
            'textarea_rows' => 5,
            'tinymce'       => true,
            'teeny'         => true
        ) );
        $wysiwyg = ob_get_clean();
        $field_num++;

        $field_output .= <<<EOD
            <div class='GENERIC_repeater GENERIC_clearfix' data-repeat_id='details_repeat'>
                <h3 class="GENERIC_sort_handle" style="margin-bottom:.5em;">Detail</h3>
                <h4 style="margin-bottom:.5em;">Detail Title</h4>
                <input class="{field_class}" type="{field_type}" name="{field_id}[title][]" size="50" value="{$contents['title']}" />
                <h4 style="margin-bottom:.5em;">Detail Text</h4>
                {$wysiwyg}
                {$description}
                <span class='GENERIC_remove_button'>Remove Detail</span>
            </div>
EOD;

    }
    
    
    $textarea_field = <<<EOD
        <div class='GENERIC_repeater_wrap GENERIC_sortable details_repeat'>
            <div class='GENERIC_repeater hidden GENERIC_clearfix' data-repeat_id='details_repeat' style='display:none;'>
                <h3 class="GENERIC_sort_handle" style="margin-bottom:.5em;">Detail</h3>
                <h4 style="margin-bottom:.5em;">Detail Title</h4>
                <input class="{field_class}" type="{field_type}" name="{field_id}[title][]" size="50" value="" />
                <h4 style="margin-bottom:.5em;">Detail Text</h4>
                <div class="wp-editor-wrap">
                    <textarea name="{$field_id}[textarea][]" id="{field_id}"></textarea>
                </div>
                {$description}
                <span class='GENERIC_remove_button'>Remove Detail</span>
            </div>
            {$field_output}
        </div>
        <span class='GENERIC_repeater_button' data-repeat_id='details_repeat'>Add Another Detail</span>
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
    return $extra.$textarea_field;

}


function GENERIC_save_field_details_repeater( $post_id, $post, $field_id, $metabox_id ) {

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
    
    if( ! is_array( $value ) ) {
        $value = $value ? sanitize_text_field( $value ) : NULL;
    } else {
        foreach( $value['title'] as &$val ) {
            $val = sanitize_text_field( $val );
        }
        foreach( $value['textarea'] as &$val ) {
            $val = wp_kses_post( $val );
        }
    }

    //$value = $value ? wp_kses_post( $value ) : NULL; // THIS IS WHY

    $curr_value = get_post_meta( $post_id, $field_id, true );

    if( $value == $curr_value ) return $post_id;

    if ( $value === NULL ) delete_post_meta( $post_id, $field_id );
    else update_post_meta( $post_id, $field_id, $value );
}








