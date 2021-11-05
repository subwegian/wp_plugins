<?php
// exit if accessed directly

use GENERIC\includes\addToMetabox;
use GENERIC\includes\createField;
use GENERIC\includes\createMetabox;

if( ! defined( 'ABSPATH' ) ) exit;

add_shortcode( 'GENERIC_frontend_field', 'GENERIC_frontend_field_shortcode' );
function GENERIC_frontend_field_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'metabox' => '',
        'field' => '',
    ), $atts);

    global $post;

    $metabox = 'Test Metabox';
    $field = 'Test Field';


    $new = createField::getInstance( $metabox, $field );
    $field_output = createMetabox::getFieldOutput( $metabox );

    if( isset( $_POST['test_field'] ) ) {
        $fields_arr = addToMetabox::getFields( 'Test Metabox' );
        // $fields_arr = addToMetabox::getAllFields();
        $arr = '<pre>' . print_r($fields_arr,true) . '</pre>';

        $field_data = $fields_arr['test_field'];
        extract( $field_data );

    }


    // return '<pre>' . print_r($new,true) . '</pre>';


// [field_name] => Test Field
// [field_id] => test_field
// [field_class] => test-field
// [description] => This is also a description
// [field_type] => text
// [metabox_id] => test_metabox
// [is_user_field] => 

    // $this->save_post_meta($post_id, $post, $field_id, $metabox_id);
    // do_action( 'GENERIC_save_field', $post_id, $post, $field_type, $field_id, $metabox_id );


    // $test->display_callback( $post );
    // ob_start();

    

    $output = <<<EOD
        {$arr}
        <form method='post'>
            {$field_output}
            <input type="submit" value="Submit">
        </form>
EOD;

    return $output;

}


