<?php
// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

function w3fi_run_fulcrum_import() {
    global $wpdb;
    $wp_prefix = $wpdb->prefix;

    $query = "SELECT * FROM {$wp_prefix}posts WHERE post_type = 'certified_supplier'";
    $rows = $wpdb->get_results( $query );

    $title_key = array();
    foreach( $rows as $row ) $title_key[ $row->post_title ] = $row->ID;


    $query = "UPDATE {$wp_prefix}posts SET post_status = 'draft' WHERE post_type = 'certified_supplier'";
    $rows = $wpdb->query( $query );


    $terms = get_terms( array(
        'taxonomy' => 'operation_type',
        'hide_empty' => false,
    ) );

    $operation_type_terms = array();
    foreach( $terms as $term ) $operation_type_terms[ $term->name ] = $term->term_id;


    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://fulcrumapp.io/share/5416115f78fe7d4531fc/csv',
        // CURLOPT_URL => 'https://watermelon03.watermelon503.com/~jaredsjaguars/_fulcrum/test.csv',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false, // Disable on live
        CURLOPT_HTTPHEADER => array(
            'User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)',
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    $fp = fopen( 'php://memory', 'r+' );
    fwrite( $fp, $response );
    rewind( $fp );
    while( ! feof( $fp ) ) {
        $csv_arr[] = fgetcsv( $fp );
    }
    fclose( $fp );

    array_walk($csv_arr, function(&$a) use ($csv_arr) {
        if( is_array( $a ) ) $a = array_combine($csv_arr[0], $a);
    });
    array_shift($csv_arr); // remove column header
    $csv_arr = array_filter($csv_arr); // remove empties
    $csv_arr = array_values($csv_arr); // reset keys

    $state_arr = w3fi_get_state_arr();

    foreach( $csv_arr as $row ) {
        $row = (object) $row;
        $post_id = $title_key[ $row->_title ] ?? 0;
        $post_categories = array();

        $website_link = $row->website ? "<a href='https://{$row->website}/'></a>" : '';

        $content_arr = array(
            $row->farm_bio,
            $row->conservation_statement,
            $website_link,
        );
        $content_arr = array_filter( $content_arr );

        $content = '<p>' . implode( '</p><p>', $content_arr ) . '</p>';

        $operation_type_lower = strtolower( $row->organic_status );
        if( $operation_type_lower ) {
            if( strpos( $operation_type_lower, 'organic' ) !== false ) $operation_type = $operation_type_terms[ 'Organic' ];
            if( strpos( $operation_type_lower, 'partial' ) !== false ) $operation_type = $operation_type_terms[ 'Organic / Conventional' ];
            if( strpos( $operation_type_lower, 'conventional' ) !== false ) $operation_type = $operation_type_terms[ 'Conventional' ];
            $post_categories['operation_type'][] = $operation_type;
        }

        $state = $state_arr[ $row->state ] ?? $row->state;

        $state_term_obj = get_term_by( 'name', $state, 'state' );
        if( $state_term_obj ) {
            $post_categories['state'][] = $state_term_obj->term_id;
        } else {
            $state_term_obj = (object) wp_insert_term( $state, 'state' );
            $post_categories['state'][] = $state_term_obj->term_id;
        }

        if( isset( $row->crops_certified ) ) {
            $crops = str_replace( ' and ', ' , ', $row->crops_certified );
            $crops = explode( ',', $crops );
            foreach( $crops as $crop ) {
                $crop = trim( $crop );
                $crop_term_obj = get_term_by( 'name', $crop, 'crops_certified' );
                if( $crop_term_obj ) {
                    $post_categories['crops_certified'][] = $crop_term_obj->term_id;
                } else {
                    $crop_term_obj = (object) wp_insert_term( $crop, 'crops_certified' );
                    echo '<pre>' . print_r( $crop_term_obj, 1 ) . '</pre>';
                    $post_categories['crops_certified'][] = $crop_term_obj->term_id;
                }
            }
        }

        $post_categories = array_filter( $post_categories );

        $post_meta = array( 
            'farm_name' => $row->supplier_name,
            'location' => $row->farm_location,
            'contact_name' => $row->contact_name,
            'contact_number' => $row->contact_number,
            'acres_certified' => $row->crop_acres,
            'supplier_name' => $row->supplier,
        );

        $post_arr = array(
            'ID' => $post_id,
            'post_author' => 12, // Communications
            'post_title' => $row->_title,
            'post_content' => $content,
            'post_status' => 'publish',
            'post_type' => 'certified_supplier',
            'tax_input' => $post_categories,
            'meta_input' => $post_meta,
        );

        $post_id = wp_insert_post( $post_arr );

        foreach( $post_categories as $tax => $term_arr ) {
            wp_set_object_terms( $post_id, $term_arr, $tax );
        }

        $title_key[ $row->_title ] = $post_id;

    }

}