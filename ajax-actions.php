<?php 




// save manual registration data to database 
add_action( 'wp_ajax_woo_mifrate_create_data', 'woo_mifrate_create_data' );
add_action( 'wp_ajax_nopriv_woo_mifrate_create_data', 'woo_mifrate_create_data' );
function woo_mifrate_create_data(){

  $formFields = [];
  wp_parse_str($_POST['woo_mifrate_create_data'], $formFields); 
//   print_r($formFields) ;  
//   exit ; 
 
// Sanitize and prepare the form data
global $wpdb;



$website = sanitize_text_field($formFields['website']);
$api_url = sanitize_text_field($formFields['api_url']);
$consumer_key = sanitize_text_field($formFields['consumer_key']);
$consumer_secret = sanitize_text_field($formFields['consumer_secret']);



// Define the table name with the WordPress prefix
$table_name = $wpdb->prefix . 'woo_migrate_api_data';

// Prepare data array for insertion
$data = array(
  'website' => $website,
  'api_url' => $api_url,
  'consumer_key' => $consumer_key,
  'consumer_secret' => $consumer_secret, 
  'status' => 1,
 
  // ... continue adding other form fields


);

// Insert data into the custom table
$wpdb->insert($table_name, $data); 
wp_die() ;

}





// edit website data 
add_action( 'wp_ajax_woo_mifrate_edit_data', 'woo_mifrate_edit_data' );
add_action( 'wp_ajax_nopriv_woo_mifrate_edit_data', 'woo_mifrate_edit_data' );
function woo_mifrate_edit_data(){

  $formFields = [];
  wp_parse_str($_POST['woo_mifrate_edit_data'], $formFields); 
// Sanitize and prepare the form data
global $wpdb;
$id = sanitize_text_field($formFields['id']);
$website = sanitize_text_field($formFields['website']);
$api_url = sanitize_text_field($formFields['api_url']);
$consumer_key = sanitize_text_field($formFields['consumer_key']);
$consumer_secret = sanitize_text_field($formFields['consumer_secret']);

// Define the table name with the WordPress prefix
$table_name = $wpdb->prefix . 'woo_migrate_api_data';

// Prepare data array for insertion
$data = array(
  'website' => $website,
  'api_url' => $api_url,
  'consumer_key' => $consumer_key,
  'consumer_secret' => $consumer_secret, 
  'status' => 1,
 
  // ... continue adding other form fields


);

// Insert data into the custom table
$wpdb->update($table_name, $data, array('id' => $id));
wp_die() ;

}

// delete  website data 
add_action( 'wp_ajax_woo_mifrate_delete_data', 'woo_mifrate_delete_data' );
add_action( 'wp_ajax_nopriv_woo_mifrate_delete_data', 'woo_mifrate_delete_data' );
function woo_mifrate_delete_data(){ 

    global $wpdb;
    $table_name = $wpdb->prefix . 'woo_migrate_api_data';
    $delete_id = $_POST['woo_mifrate_delete_data'];
    // Delete data from the custom table where id matches
    $wpdb->delete($table_name, array('id' => $delete_id));
    wp_die() ;

}