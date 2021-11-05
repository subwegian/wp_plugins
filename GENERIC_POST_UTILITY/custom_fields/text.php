<?php
// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

// $field_display = apply_filters( 'GENERIC_display_field', $field_display, $field_array['field_type'], $value, $field_array, $is_user = false );

add_filter( 'GENERIC_display_field', 'GENERIC_text_field_display', 10, 5 );
function GENERIC_text_field_display( $field_display, $field_type, $value, $field_array, $is_user ) {

    if( 'text' != $field_type ) return $field_display;
    
    $description = $field_array['description'] ? '<p class="description">{description}</p>' : '';
    
    $text_field = <<<EOD
        <div id="{field_id}" class="GENERIC-custom-field">
            <h4 style="margin-bottom:.5em;">{field_name}</h4>
            <input class="{field_class}" type="{field_type}" name="{field_id}" size="50" value="{$value}" />
            {$description}
        </div>
EOD;

    $user_text_field = <<<EOD
        <tr class="GENERIC-custom-field">
            <th><label for="{field_id}">{field_name}</label></th>
            <td>
                <input type="text" name="{field_id}" id="{field_id}" value="{$value}" class="{field_class} regular-text">
                {$description}
            </td>
        </tr>
EOD;

    if( $is_user ) return $user_text_field;
    return $text_field;

}


