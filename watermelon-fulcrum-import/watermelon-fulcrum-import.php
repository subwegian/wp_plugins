<?php
/*
Plugin Name: Watermelon Fulcrum Import
Description: Watermelon Fulcrum Import
Version: 1.0.0
Author: Watermelon Web Works
Author URI: https://watermelonwebworks.com
License: GPLv2 or later
License URI: https://uri
*/

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

function w3fi_dir() {return plugin_dir_path(__FILE__);}
function w3fi_url() {return plugin_dir_url(__FILE__);}

// require_once w3fi_dir() . 'w3fi_functions.php';
require_once w3fi_dir() . 'w3fi_import_function.php';


// Check to see if already scheduled
$is_scheduled = wp_next_scheduled( 'w3fi_run_fulcrum_import_hook' );

// // If it isn't, schedule them (this creates a hook)
if( ! $is_scheduled ) {
    $date = new DateTime( 'Tomorrow 00:01:00', new DateTimeZone( 'America/Los_Angeles' ));
    $timestamp = $date->format('U');
    wp_schedule_event( $timestamp, 'daily', 'w3fi_run_fulcrum_import_hook' );
}

// Hook this function into the created scheduled hook
add_action( 'w3fi_run_fulcrum_import_hook', function() {
    w3fi_run_fulcrum_import();
} );


add_action( 'init', function() {
    if( isset( $_GET['run_fulcrum_import'] ) && current_user_can( 'administrator' ) ) {
        w3fi_run_fulcrum_import();
    }
});


function w3fi_get_state_arr() {
    return array(
        'Alabama' => 'AL',
        'Alaska' => 'AK',
        'Arizona' => 'AZ',
        'Arkansas' => 'AR',
        'California' => 'CA',
        'Colorado' => 'CO',
        'Connecticut' => 'CT',
        'Delaware' => 'DE',
        'Florida' => 'FL',
        'Georgia' => 'GA',
        'Hawaii' => 'HI',
        'Idaho' => 'ID',
        'Illinois' => 'IL',
        'Indiana' => 'IN',
        'Iowa' => 'IA',
        'Kansas' => 'KS',
        'Kentucky' => 'KY',
        'Louisiana' => 'LA',
        'Maine' => 'ME',
        'Maryland' => 'MD',
        'Massachusetts' => 'MA',
        'Michigan' => 'MI',
        'Minnesota' => 'MN',
        'Mississippi' => 'MS',
        'Missouri' => 'MO',
        'Montana' => 'MT',
        'Nebraska' => 'NE',
        'Nevada' => 'NV',
        'New Hampshire' => 'NH',
        'New Jersey' => 'NJ',
        'New Mexico' => 'NM',
        'New York' => 'NY',
        'North Carolina' => 'NC',
        'North Dakota' => 'ND',
        'Ohio' => 'OH',
        'Oklahoma' => 'OK',
        'Oregon' => 'OR',
        'Pennsylvania' => 'PA',
        'Rhode Island' => 'RI',
        'South Carolina' => 'SC',
        'South Dakota' => 'SD',
        'Tennessee' => 'TN',
        'Texas' => 'TX',
        'Utah' => 'UT',
        'Vermont' => 'VT',
        'Virginia' => 'VA',
        'Washington' => 'WA',
        'West Virginia' => 'WV',
        'Wisconsin' => 'WI',
        'Wyoming' => 'WY',
        'Virgin Islands' => 'V.I.',
        'Guam' => 'GU',
        'Puerto Rico' => 'PR',
    );
}

