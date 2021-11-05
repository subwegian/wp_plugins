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
        wp_enqueue_script( 'GENERIC-image-upload', GENERIC_url() . 'js/image_upload.js', array('jquery','GENERIC-repeater-field'), null, true );
    }

});


add_filter( 'GENERIC_display_field', 'GENERIC_image_upload_field_display', 10, 5 );
function GENERIC_image_upload_field_display( $field_display, $field_type, $value, $field_array, $is_user ) {

    if( 'image_upload' != $field_type ) return $field_display;
    
    $description = $field_array['description'] ? '<div class="description">{description}</div>' : '';
    
    $existing_images = '';
    
//    if( is_array($value) && ! empty( $value ) ) {
//        foreach( $value as $img_id ) {
//            $img_url = wp_get_attachment_image_src( $img_id, 'medium' )[0];
//            $existing_images .= <<<EOD
//                <div class='GENERIC_image_repeater'>
//                    <input type="hidden" name="{field_id}[]" value="{$img_id}" />
//                    <span class='GENERIC_display_image'>
//                        <img class="GENERIC_image" src="{$img_url}" style="" />
//                    </span>
//                    <span class='GENERIC_remove_image' style='margin-top:10px;'>Remove Image</span>
//                </div>
//EOD;
//        }
//    }

    if( is_array($value) && ! empty( $value ) ) {
        foreach( $value as $img_id ) {
            $img_url = wp_get_attachment_image_src( $img_id, 'medium' )[0];
            $existing_images .= <<<EOD
                <div class='GENERIC_repeater GENERIC_clearfix'>
                    <input class='GENERIC_image_id' type="hidden" name="{field_id}[]" value="{$img_id}" />
                    <span class='GENERIC_display_image GENERIC_sort_handle'>
                        <img class="GENERIC_image" src="{$img_url}" style="" />
                    </span>
                    <span class='GENERIC_remove_button' style='margin-top:10px;'>Remove Image</span>
                </div>
EOD;
        }
    }

    $existing_images = $existing_images ?: '<div style="background:#ddd;width:300px;height:200px;"></div>';

    $GENERIC_uploader_field = <<<EOD
        <div id="{field_id}" class="GENERIC_upload_field {field_class}">
            <h4 style="margin-bottom:.5em;">{field_name}</h4>
            {$description}
            <input type="hidden" name="{field_id}" value="" />
            <div class='GENERIC_repeater_wrap {field_id} GENERIC_sortable'>
                <div class='GENERIC_repeater hidden GENERIC_clearfix' data-repeat_id='{field_id}' style='display:none;'>
                    <input class='GENERIC_image_id' type="hidden" name="{field_id}[]" value="" />
                    <span class='GENERIC_display_image GENERIC_sort_handle'></span>
                    <span class='GENERIC_remove_button' style='margin-top:10px;'>Remove Image</span>
                </div>
                {$existing_images}
            </div>
            <span class='GENERIC_image_button' data-repeat_id='{field_id}'>Add Image(s)</span>
        </div>
        <style>
            .GENERIC_image {
                max-height: 200px;
                display: block;
                margin: 0;
            }

            .GENERIC_upload_field .GENERIC_display_image {
                background: none;
                border: none;
                margin: 0;
                padding: 0;
                display: block;
            }

            .GENERIC_upload_field .GENERIC_repeater_wrap {
                /*max-width: 300px;*/
                display: flex;
                flex-wrap: wrap;
                align-items: flex-end;
            }

            .GENERIC_upload_field .GENERIC_repeater {
                position: relative;
                margin-bottom: 10px;
                margin-right: 10px;
                cursor: move;
                background: none;
                border: none;
                padding: 0;
            }

            .GENERIC_upload_field .GENERIC_remove_button {
                position: absolute;
                bottom: 0;
                right: 0;
                background-color: #ff0000aa;
                color: #fff;
                padding: 2px 6px;
                cursor: pointer;
                border: none;
                border-radius: 0;
                float: none;
            }

            .GENERIC_upload_field .GENERIC_remove_button:hover {
                background-color: #ff0000;
            }

            .GENERIC_upload_field .GENERIC_image_button {
                background-color: #0bab26;
                color: #fff;
                font-weight: 500;
                font-size: .9rem;
                padding: 2px 6px 4px;
                cursor: pointer;
            }
        </style>
EOD;

//.GENERIC_repeater_wrap .GENERIC_repeater {
//  background: none;
//  border: none;
//  padding: 0;
//}
//
//
//
//.GENERIC_upload_field .GENERIC_remove_button {
//  border: none;
//  border-radius:0;
//  float: none;
//}
    if( $is_user ) return $user_GENERIC_uploader_field;
    return $GENERIC_uploader_field;
}