<?php
// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


add_filter( 'GENERIC_display_field', 'GENERIC_checkbox_field_display', 10, 5 );
function GENERIC_checkbox_field_display( $field_display, $field_type, $value, $field_array, $is_user ) {

    if( 'checkbox' != $field_type ) return $field_display;
    
    $description = $field_array['description'] ? '<p class="description">{description}</p>' : '';
    $field_id = $field_array['field_id'];
    $value = (array) $value;
    
    $checkbox_options_arr = array(
        'field_five' => array(
            'Bike' => 'I have a bike',
            'Boat' => 'I have a boat',
            'Car' => 'I have a car',
        ),
        'user_field_six' => array(
            'Bike' => 'I have a bike',
            'Boat' => 'I have a boat',
            'Car' => 'I have a car',
            'Wagon' => 'I have a wagon',
        ),
    );
        
    $checkbox_options_arr = $checkbox_options_arr[ $field_id ];
    if( ! $checkbox_options_arr ) $checkbox_options_arr[ 'no_options' ] = 'No options are set for this field_id';
    
    foreach( $checkbox_options_arr as $option_val => &$option_text ) {
        $checked = in_array( $option_val, $value ) ? ' checked' : '';
        $option_text = "<input class='{field_class}' type='checkbox' name='{field_id}[]' value='{$option_val}'{$checked}>{$option_text}";
    }
    
    $checkboxes = implode( '<br>', $checkbox_options_arr );
    
    $checkbox_field = <<<EOD
        <div id="{field_id}" class="GENERIC-custom-field">
            <h4 style="margin-bottom:.5em;">{field_name}</h4>
            {$checkboxes}
            {$description}
        </div>
EOD;

    $user_checkbox_field = <<<EOD
        <tr class="GENERIC-custom-field">
            <th><label for="{field_id}">{field_name}</label></th>
            <td>
                {$checkboxes}
                {$description}
            </td>
        </tr>
EOD;

    if( $is_user ) return $user_checkbox_field;
    return $checkbox_field;
}

