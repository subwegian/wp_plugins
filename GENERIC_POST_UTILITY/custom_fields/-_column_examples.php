<?php
// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


function GENERIC_column_contents_author_name( $field_name, $post_id ) {
    $post_author_id = get_post_field( 'post_author', $post_id );
    return get_userdata( $post_author_id )->display_name;
}


function GENERIC_column_orderby_query_author_name( $field_name, $post_type, $field_query, $query ) {
    global $wpdb;

    $field_query = <<<EOD
        SELECT p.ID, u.display_name as meta_value
        FROM {$wpdb->prefix}posts p
        LEFT JOIN {$wpdb->prefix}users u ON p.post_author = u.ID
        WHERE p.post_type = '{$post_type}'
EOD;
    
    return $field_query;
}


function DISABLED_GENERIC_column_contents_field_one( $field_name, $post_id ) {
    return get_post_meta( $post_id, $field_name, true ) . ' MODDED';
}


function DISABLED_GENERIC_column_orderby_query_field_one( $field_name, $post_type, $field_query, $query ) {
    global $wpdb;

    $field_query = <<<EOD
        SELECT p.ID, pm.meta_value
        FROM {$wpdb->prefix}posts p
        LEFT JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '{$field_name}'
        WHERE p.post_type = '{$post_type}'
EOD;
    
    return $field_query;
}


function DISABLED_GENERIC_column_sort_override_field_one( $field_name, $post_type, $value_list, $query_results ) {
    return array_reverse( $value_list );
}
