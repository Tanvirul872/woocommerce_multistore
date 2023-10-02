<?php




// save manual registration data to database 
add_action('wp_ajax_woo_mifrate_create_data', 'woo_mifrate_create_data');
add_action('wp_ajax_nopriv_woo_mifrate_create_data', 'woo_mifrate_create_data');
function woo_mifrate_create_data()
{

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
  wp_die();
}





// edit website data 
add_action('wp_ajax_woo_mifrate_edit_data', 'woo_mifrate_edit_data');
add_action('wp_ajax_nopriv_woo_mifrate_edit_data', 'woo_mifrate_edit_data');
function woo_mifrate_edit_data()
{

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
  wp_die();
}

// delete  website data 
add_action('wp_ajax_woo_mifrate_delete_data', 'woo_mifrate_delete_data');
add_action('wp_ajax_nopriv_woo_mifrate_delete_data', 'woo_mifrate_delete_data');
function woo_mifrate_delete_data()
{

  global $wpdb;
  $table_name = $wpdb->prefix . 'woo_migrate_api_data';
  $delete_id = $_POST['woo_mifrate_delete_data'];
  // Delete data from the custom table where id matches
  $wpdb->delete($table_name, array('id' => $delete_id));
  wp_die();
}




require __DIR__ . '/vendor/autoload.php';

use Automattic\WooCommerce\Client;





// delete  website data 
add_action('wp_ajax_order_update_by_id', 'order_update_by_id');
add_action('wp_ajax_nopriv_order_update_by_id', 'order_update_by_id');
function order_update_by_id()
{
  $woocommerce = new Client(
    'https://bengalcoder.com/ecommerce/', // Your store URL
    'ck_c70dc5dd391cd035fa0e22fc055414fd7478fca3', // Your consumer key
    'cs_39f8c8cdd40f6aaca6b75a5e890653a2c287660d', // Your consumer secret
    [
      'wp_api' => true, // Enable the WP REST API integration
      'version' => 'wc/v3' // WooCommerce WP REST API version
    ]
  );


  // print_r($_POST);
  // exit;

  $order_update_status = $_POST['order_update_status'];

  if ($order_update_status == 'Pending payment') {
    $order_status = 'pending';
  } elseif ($order_update_status == 'Processing') {
    $order_status = 'processing';
  } elseif ($order_update_status == 'On hold') {
    $order_status = 'on-hold';
  } elseif ($order_update_status == 'Completed') {
    $order_status = 'completed';
  } elseif ($order_update_status == 'Cancelled') {
    $order_status = 'cancelled';
  } elseif ($order_update_status == 'Refunded') {
    $order_status = 'refunded';
  } elseif ($order_update_status == 'Failed') {
    $order_status = 'failed';
  }

  // print_r($_POST['_billing_first_name']);
  // exit;

  // "billing": {
  //   "first_name": "John",
  //   "last_name": "Doe",
  //   "company": "",
  //   "address_1": "969 Market",
  //   "address_2": "",
  //   "city": "San Francisco",
  //   "state": "CA",
  //   "postcode": "94103",
  //   "country": "US",
  //   "email": "john.doe@example.com",
  //   "phone": "(555) 555-5555"
  // },
  // "shipping": {
  //   "first_name": "John",
  //   "last_name": "Doe",
  //   "company": "",
  //   "address_1": "969 Market",
  //   "address_2": "",
  //   "city": "San Francisco",
  //   "state": "CA",
  //   "postcode": "94103",
  //   "country": "US"
  // },

  $data = [
    'status' => $order_status,
    'billing' => [
      'first_name' => $_POST['_billing_first_name'],
      'last_name' => $_POST['_billing_last_name'],
      'company' => $_POST['_billing_company'],
      'address_1' => $_POST['_billing_address_1'],
      'address_2' => $_POST['_billing_address_2'],
      'city' => $_POST['_billing_city'],
      'state' => $_POST['_billing_state'],
      'postcode' => $_POST['_billing_postcode'],
      'country' => $_POST['select2-_billing_country-container'],
      'email' => $_POST['_billing_email'],
      'phone' => $_POST['_shipping_phone'],
    ],
    'shipping' => [
      'first_name' => $_POST['_shipping_first_name'],
      'last_name' => $_POST['_shipping_last_name'],
      'company' => $_POST['_shipping_company'],
      'address_1' => $_POST['_shipping_address_1'],
      'address_2' => $_POST['_shipping_address_2'],
      'city' => $_POST['_shipping_city'],
      'state' => $_POST['_shipping_state'],
      'postcode' => $_POST['_shipping_postcode'],
      'country' => $_POST['select2-_shipping_country-container'],
      'phone' => $_POST['_shipping_phone'],
    ]
  ];


  $order_id =  20;



  $response = $woocommerce->put('orders/' . $order_id, $data);


  if (is_wp_error($response)) {
    wp_send_json_error(array('message' => "Something Wrong"));
  } else {
    wp_send_json_success(array('message' => 'Order updated successfully'));
  }

  exit();
}
