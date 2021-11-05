<?php
// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


function GENERIC_filter_query_author_names( $field_name, $post_type, $field_query ) {
    global $wpdb;

    $field_query = <<<EOD
        SELECT p.post_author as ID, u.display_name as meta_value
        FROM {$wpdb->prefix}posts p
        LEFT JOIN {$wpdb->prefix}users u ON p.post_author = u.ID
        WHERE p.post_type = '{$post_type}'
EOD;
    
    return $field_query;
}


function GENERIC_filter_sort_override_author_names( $field_name, $post_type, $value_list, $query_results ) {
    foreach( $query_results as $row ) $value_list[ $row->meta_value ] = $row->ID;

    if( $value_list ) ksort( $value_list );
    return array_filter( $value_list );
}


function GENERIC_filter_query_override_author_names( $field_name, $filter_value, $post_type, $meta_query, $query ) {
    $author_arr = $query->get( 'author__in' );
    $author_arr[] = $filter_value;
    $query->set( 'author__in', $author_arr );
}

