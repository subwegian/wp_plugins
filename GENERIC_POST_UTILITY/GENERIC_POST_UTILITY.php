<?php
/*
Plugin Name: w3um POST UTILITY
Description: Custom Post Types, Custom Fields, Custom Taxonomies, Custom Admin Columns, Custom Admin Filters
Version: 1.0.1
Author: JaredM
Author URI: 
License: GPLv2 or later
License URI: https://uri
*/

use w3um\includes\{
    createCustomPostType,
    createMetabox,
    createUserMetabox,
    createField,
    createTaxonomy,
    createAdminColumns,
    createAdminFilter
};

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

function w3um_dir() {return plugin_dir_path(__FILE__);}
function w3um_url() {return plugin_dir_url(__FILE__);}


require_once w3um_dir() . 'includes/class_createCustomPostType.php';
require_once w3um_dir() . 'includes/class_createMetabox.php';
require_once w3um_dir() . 'includes/class_addToMetabox.php';
require_once w3um_dir() . 'includes/class_createField.php';
require_once w3um_dir() . 'includes/class_createUserMetabox.php';
require_once w3um_dir() . 'includes/class_createTaxonomy.php';
require_once w3um_dir() . 'includes/class_createAdminColumns.php';
require_once w3um_dir() . 'includes/class_createAdminFilter.php';
require_once w3um_dir() . 'includes/class_addToFilter.php';

require_once w3um_dir() . 'shortcodes/shortcode_front_end_field.php';

foreach (glob(w3um_dir() . 'custom_fields/*.php') as $filename) {
    $basename = basename( $filename );
    if( $basename[0] != '_' )
        include $filename;
}


/****************************************************
* 
* Enqueue scripts and styles.
* 
****************************************************/

// MOVE THIS TO createMetabox? Where each post_type used gets the files enqueued?
add_action( 'admin_enqueue_scripts', function( $hook ) {

    $pages_arr = array(
        'post.php',
        'post-new.php',
//        'profile.php',
//        'user-edit.php',
    );
    
    $post_type = get_post()->post_type;
    
    // if( $post_type == 'w3um' && in_array( $hook, $pages_arr ) ){
        wp_enqueue_style( 'w3um-admin-style', w3um_url() . 'css/admin_style.css' );
        wp_enqueue_script('w3um-sortable-tinymce', w3um_url() . 'js/sortable_tinymce.js', array('jquery'), NULL, true);
        wp_enqueue_script( 'w3um-repeater-field', w3um_url() . 'js/repeater_field.js', array('jquery'), null, true );
    // }

});

add_action( 'wp_enqueue_scripts', 'w3um_enqueue_styles' );
function w3um_enqueue_styles() {

    if( 'w3um' != get_post_type() ) return;

    // Front-End Ajax
    //wp_enqueue_script( 'w3um_ajax_js', w3um_url() . 'ajax/ajax_script.js', array('jquery'), NULL, true );
    //wp_localize_script( 'w3um_ajax_js', 'ajax_js_obj', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

}



// CUSTOM POST TYPE & METABOX & FIELDS
$args = array('menu_icon' => 'dashicons-archive');
createCustomPostType::addCPT('Sample PostType', NULL, $args);
createMetabox::addMB('Sample PostType','Sample Metabox');
createField::addCF( 'Sample Metabox', 'Field One', 'text', 'This is also a description' );
createField::addCF( 'Sample Metabox', 'Field One B' );
createField::addCF( 'Sample Metabox', 'Field Two', 'textarea' );
createField::addCF( 'Sample Metabox', 'Field Three', 'textarea_wysiwyg' );
createField::addCF( 'Sample Metabox', 'Field Four', 'media_uploader' );
createField::addCF( 'Sample Metabox', 'Field Five', 'checkbox' );
createField::addCF( 'Sample Metabox', 'Field Six', 'select_list' );
createMetabox::addMB('Sample PostType','TinyMCE Metabox');
createField::addCF( 'TinyMCE Metabox', 'Field Seven', 'textarea_tinymce' );

createTaxonomy::addTax( 'Sample PostType', 'Tax One' );
$args = array('hierarchical' => false); // Tag-type taxonomy
createTaxonomy::addTax( 'Sample PostType', 'Tax Two', NULL, $args );

// SEE /custom_fields/-_column_examples.php
createAdminColumns::addAC('Sample PostType', 'Author Name', 'Author');
createAdminColumns::addAC('Sample PostType', 'Field One');
createAdminColumns::addAC('Sample PostType', 'Field One B');

// SEE /custom_fields/-_filter_examples.php
createAdminFilter::AddAF('Sample PostType', 'Author Names');
createAdminFilter::AddAF('Sample PostType', 'Field One');
createAdminFilter::AddAF('Sample PostType', 'Field One B');

// USER METABOX & FIELDS
createUserMetabox::addMB('Sample Metabox');
createField::addCF( 'Sample Metabox', 'User Field One', 'text', 'This is a description' );
createField::addCF( 'Sample Metabox', 'User Field Two' );
createField::addCF( 'Sample Metabox', 'User Field Three', 'textarea', 'This is a description' );
createField::addCF( 'Sample Metabox', 'User Field Four', 'textarea_wysiwyg', 'This is a description' );
createField::addCF( 'Sample Metabox', 'User Field Five', 'media_uploader', 'This is a description' );
createField::addCF( 'Sample Metabox', 'User Field Six', 'checkbox', 'This is a description' );
createField::addCF( 'Sample Metabox', 'User Field Seven', 'select_list', 'This is a description' );


// EXISTING POST TYPE & CUSTOM METABOX & FIELD & FILTER
createMetabox::addMB('page','Test Metabox');
createField::addCF(  'Test Metabox', 'Test Field', 'text', 'This is also a description' );
createMetabox::addMB('page','Another Metabox');
createField::addCF(  'Another Metabox', 'Adding Field', 'text', 'This is probably a description' );

createAdminFilter::AddAF('page', 'Author Names');





// CREATE FIELD TYPE //
/************** Generally stored in separate files in /custom_fields dir **************

w3um_display_field_FIELD_TYPE( 

    // The saved value for the field (can be an array) to be incorporated into the display output
    $value, 
    
    // Array of values that can be used to make variations of the field output
    // They will be automatically replaced in the output if included like this: {description}
    // Params: field_name, field_id, field_class, description, field_type, metabox_id, is_user_field
    $field_array, 
    
    // Identifies if the field is displayed on a user profile or a post_type.
    // Useful for making a version for both.
    $is_user = false
) {

    if( $is_user ) return '<pre>' . print_r( $options_array, true ) . '</pre>';
    return '<pre>' . print_r( $options_array, true ) . '</pre>';

}

*************************************************************************************/


// CREATE CUSTOM POST TYPE //
/*************************************************************************************

createCustomPostType::addCPT( 

    // $post_type_name is used as the display name and sanitized. 
    // For reference: "Post Type" becomes "post_type"
    $post_type_name = 'Default',

    // $plural_name by defaut gets an "s" suffix; use this to override
    $plural_name = NULL, 

    // $override_args all default args for 'register_post_type' can be overridden. Only include overrides.
    $override_args = NULL, 

    // $custom_slug overrides slug generated from $custom_name. Sanitized/cropped (20 char max)
    $custom_slug = NULL, 

    // $prefix appends a custom prefix to help prevent naming conflicts (must match prefix used in other objects)
    $prefix = NULL 
);

*************************************************************************************/


// CREATE METABOX //
/*************************************************************************************

createMetabox::addMB(

    // $post_type will be sanitized the same as addCPT: "Post Type" becomes "post_type"
    $post_type = 'page',

    // $metabox is used as the display name and sanitized for reference: "Default Metabox" becomes "default_metabox"
    $metabox = 'Default Metabox', 
    
    // $placement for posts can be 'normal', 'side' (sidebar), and 'advanced' (after other fields).
    $placement = 'normal', 

    // $priority can be 'default', 'low', 'high'
    $priority = 'high', 

    // $callback_args sent to the display_callback.
    // Right now it can just be used to conditionally register the metabox.
    $callback_args = NULL 
);

************************************************************************************/


// CREATE USER METABOX //
/*************************************************************************************

createUserMetabox::addMB( 

    // $metabox is used as the display name and sanitized for reference: "Default Metabox" becomes "default_metabox"
    $metabox = 'Default Metabox' 
    
);

************************************************************************************/


// CREATE CUSTOM FIELD //
/*************************************************************************************

createField::addCF(

    // $metabox will be sanitized the same as addMB: "Default Metabox" becomes "default_metabox"
    $metabox = 'Default Metabox', 

    // $field_name is used as the display name and sanitized for reference: "Default Field" becomes "default_field"
    $field_name = 'Default Field', 

    // Corresponds to field display function. See "CREATE FIELD TYPE".
    // Displays as integrated 'text' field if unspecified.
    $field_type = 'text', 

    // $description is an optional field that can be displayed in the field template
    $description = NULL, 

    // $prefix appends a custom prefix to help prevent naming conflicts (must match prefix used in other objects)
    $prefix = NULL, 
);

*************************************************************************************/




