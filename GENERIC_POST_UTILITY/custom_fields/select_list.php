<?php
// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


add_filter( 'GENERIC_display_field', 'GENERIC_select_list_field_display', 10, 5 );
function GENERIC_select_list_field_display( $field_display, $field_type, $value, $field_array, $is_user ) {

    if( 'select_list' != $field_type ) return $field_display;

    $description = $field_array['description'] ? '<p class="description">{description}</p>' : '';
    $field_id = $field_array['field_id'];
    $value = (array) $value;
    
    $select_list_arr = array(
        'field_six' => array(
            '' => '--SELECT--',
            'Bike' => 'I have a bike',
            'Boat' => 'I have a boat',
            'Car' => 'I have a car',
        ),
        'user_field_seven' => array(
            '' => '--SELECT--',
            'Bike' => 'I have a bike',
            'Boat' => 'I have a boat',
            'Car' => 'I have a car',
            'Wagon' => 'I have a wagon',
        ),
    );
        
    $select_list_arr = $select_list_arr[ $field_id ];
    if( ! $select_list_arr ) $select_list_arr[ 'no_options' ] = 'No options are set for this field_id';
    
    foreach( $select_list_arr as $option_val => &$option_text ) {
        $selected = in_array( $option_val, $value ) ? ' selected' : '';
        $option_text = "<option class='{field_class}' value='{$option_val}'{$selected}>{$option_text}</option>";
    }
    
    $select_list = implode( '', $select_list_arr );
    
    $select_list_field = <<<EOD
        <div id="{field_id}" class="GENERIC-custom-field">
            <h4 style="margin-bottom:.5em;">{field_name}</h4>
            <select class="{field_class}" name="{field_id}">
                {$select_list}
            </select>
            {$description}
        </div>
EOD;

    $user_select_list_field = <<<EOD
        <tr class="GENERIC-custom-field">
            <th><label for="{field_id}">{field_name}</label></th>
            <td>
                <select class="{field_class}" name="{field_id}">
                    {$select_list}
                </select>
                {$description}
            </td>
        </tr>
EOD;

    if( $is_user ) return $user_select_list_field;
    return $select_list_field;
}




