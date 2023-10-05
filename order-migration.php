<?php
/*
Plugin Name: Order Migration
Description: Migrate orders from one WooCommerce site to another.
Version: 1.0.1
Author: Tanvirul Karim
*/


// include files 
include('database.php');
include('ajax-actions.php');


require __DIR__ . '/vendor/autoload.php';

use Automattic\WooCommerce\Client;


// Load WordPress and WooCommerce functions  
require_once(dirname(__FILE__) . '/../../../wp-load.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');



// enqueue css and js files 
add_action('admin_enqueue_scripts', 'plugin_css_jsscripts');
function plugin_css_jsscripts()
{
    // CSS
    wp_enqueue_style('style-css', plugins_url('/style.css', __FILE__));
    wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css');

    // JavaScript
    wp_enqueue_script('script-js', plugins_url('/script.js', __FILE__), array('jquery'));
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js', array('jquery'), '4.5.3', true);

    // Pass ajax_url to script.js
    wp_localize_script(
        'script-js',
        'plugin_ajax_object',
        array('ajax_url' => admin_url('admin-ajax.php'))
    );
}


// echo '<div style="margin-left:450px">' ;


// test data start 

// $source_orders = array();
// $woocommerce = new Client(
//     'http://localhost/ecom_4', // Your store URL
//     'ck_6d97d21f227f33501cdcf59f40488269bad2351f', // Your consumer key
//     'cs_67465670667804faecf1ae5284842bd4ba0bf5bf', // Your consumer secret
//     [
//         'wp_api' => true, // Enable the WP REST API integration
//         'version' => 'wc/v3' // WooCommerce WP REST API version
//     ]
// );


// $orders_data = $woocommerce->get('orders');

// if (is_array($orders_data)) {
//     foreach ($orders_data as $order) {
//         echo '<pre>' ;
//         print_r($order);
//         echo '</pre>' ;
//         // You might need to transform and format the order data as needed
//         // before adding it to the source_orders array
//         $source_orders[] = $order;
//     }
// }




// test data end 

// echo '</div>' ;




// Function to create the settings table woo_migrte_orders
register_activation_hook(__FILE__, 'create_woo_migrte_orders_table');
function create_woo_migrte_orders_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'woo_migrte_orders';
    // SQL query to create the table
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_num VARCHAR(255) ,
        status INT

    )";

    // Execute the SQL query
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}



//function for create a table for save api data 
register_activation_hook(__FILE__, 'create_custom_table_on_theme_activation');
function create_custom_table_on_theme_activation()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'woo_migrate_api_data';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT AUTO_INCREMENT PRIMARY KEY,
        website VARCHAR(255) NOT NULL,
        api_url VARCHAR(255) NOT NULL,
        consumer_key VARCHAR(100) NOT NULL,
        consumer_secret VARCHAR(100) NOT NULL,
        status INT DEFAULT 0
    )";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}



// Make  active members ajax function

add_action('wp_ajax_migration_trigger', 'migration_trigger');
add_action('wp_ajax_nopriv_migration_trigger', 'migration_trigger');

function migration_trigger()
{
    migrate_orders();
    wp_send_json_success('api loaded');
}




function migrate_orders()
{

    // Fetch orders from the source website (You need to implement this)
    $source_orders = get_source_orders($source_site_url);
    // Loop through orders and insert them into the destination website
    // foreach ($source_orders as $source_order) {
    //     insert_order_into_destination($source_order);
    // }
}



function get_source_orders($source_site_url)
{

    // print_r('hello') ;
    // exit; 
    $source_orders = array();

    // get all the orders together start 
    // Initialize an array to hold the combined orders
    $combinedOrders = [];

    global $wpdb;
    $table_name = $wpdb->prefix . 'woo_migrate_api_data';
    $results = $wpdb->get_results("SELECT * FROM $table_name");





    // Initialize an array to hold the WooCommerce instances and configurations
    $woocommerce_instances = [];

    foreach ($results as $result) {
        $api_url = $result->api_url;
        $consumer_key = $result->consumer_key;
        $consumer_secret = $result->consumer_secret;

        // Add the WooCommerce instance and configuration to the array
        $woocommerce_instances[] = [
            'url' => $api_url,
            'consumer_key' => $consumer_key,
            'consumer_secret' => $consumer_secret,
        ];
    }

    // echo '<pre>';
    // print_r($woocommerce_instances);
    // exit;



    $combinedOrders = []; // Initialize the array to store orders
    $today = new DateTime(); // Get current date

    foreach ($woocommerce_instances as $instance) {
        $woocommerce = new Client(
            $instance['url'],
            $instance['consumer_key'],
            $instance['consumer_secret'],
            [
                'wp_api' => true,
                'version' => 'wc/v3'
            ]
        );


        $page = 1;
        $perPage = 100; // You can adjust this value based on your needs

        while (true) {
            $orders_data = $woocommerce->get('orders', [
                'per_page' => $perPage,
                'page' => $page,
            ]);



            if (empty($orders_data)) {
                break; // No more orders
            }

            foreach ($orders_data as $order) {
                $orderDate = new DateTime($order->date_created); // Convert order date to DateTime object

                // print_r($order) ;
                // exit; 

                // Compare order date with today's date
                if ($orderDate->format('Y-m-d') === $today->format('Y-m-d')) {
                    $combinedOrders[] = $order; // Add order to the array
                }
            }

            $page++;
        }
    }







    // check if the order already exists 
    $posts_with_meta = get_posts(array(
        'post_type'      => 'shop_order',
        'posts_per_page' => -1, // Retrieve all orders
        'post_status'   => array('processing', 'completed', 'on-hold'),
        'meta_key' => '_order_from_where_', // Replace with your specific post meta key
        'meta_compare' => 'EXISTS', // Check if the post meta key exists
    ));



    // print_r($posts_with_meta);
    // exit; 



    $order_exists = [];
    if ($posts_with_meta) {
        foreach ($posts_with_meta as $post) {

            $order_exists[] = get_post_meta($post->ID, '_order_from_where_', true);
        }
        wp_reset_postdata(); // Reset post data
    }





    // print_r($order_exists);
    // exit; 


    // $from_where_with_ids = [];

    if (is_array($combinedOrders)) {
        foreach ($combinedOrders as $order) {

            // echo '<pre>';
            // print_r($order);
            // exit;

            // global $wpdb;
            // $table_name = $wpdb->prefix . 'woo_migrte_orders';


            //  this code is for localhost 
            $link =  $order->payment_url;
            $updatelink = parse_url($link);
            $mainUrl = $updatelink['host'];

            // print_r($mainUrl);
            // exit;

            if (preg_match('/http:\/\/localhost\/(.*?)\/checkout\/order-pay/', $link, $matches)) {
                $dynamic_prefix = $matches[1];
            }

            $order_num_to_insert = $dynamic_prefix . '_' . $order->id;

            $from_where_with_id = $mainUrl . $order_num_to_insert;



            // print_r($new_order->id);
            // exit;


            if ((count($order_exists) == 0) || !in_array($from_where_with_id, $order_exists)) {

                print_r('kamrul');
                exit;


                // add order start 
                $source_order = $order;

                if (class_exists('WooCommerce')) {
                    $order_data = array(
                        'status' => $source_order->status,  // Change to desired status
                        'customer_id' => $source_order->customer_id, // Replace with appropriate customer ID
                        'customer_note' => 'customer note',
                        'parent'        => null,
                        'created_via'   => null,
                        'cart_hash'     => null,
                        'order_id'      => 0,
                    );

                    $new_order = wc_create_order($order_data);





                    update_post_meta($new_order->id, '_order_from_where_', $from_where_with_id);



                    if (is_a($new_order, 'WC_Order')) {

                        foreach ($source_order->line_items as $line_item) {

                            $args = array(
                                'name'         => $line_item->name,
                                'tax_class'    => $line_item->tax_class,
                                'variation_id' => $line_item->variation_id,
                                'variation'    => $line_item->variation,
                                'subtotal'     => $line_item->subtotal,
                                'total'        => $line_item->total,
                                'quantity'     => $line_item->quantity,
                                'product_id'   => $line_item->product_id,
                            );


                            $sku = $line_item->sku; //for test purpose 
                            // $sku = '123' ;
                            $product_id = wc_get_product_id_by_sku($sku);
                            $product = wc_get_product($product_id);
                            $new_order->add_product($product, $line_item->quantity);


                            $shipping_address = array(
                                'first_name' => $source_order->shipping->first_name,
                                'last_name' => $source_order->shipping->last_name,
                                'address_1' => $source_order->shipping->address_1,
                                'address_2' => $source_order->shipping->address_2,
                                'city' => $source_order->shipping->city,
                                'state' => $source_order->shipping->state,
                                'postcode' => $source_order->shipping->postcode,
                                'country' => $source_order->shipping->country,
                                'company'    => $source_order->shipping->company,
                                'email'      => $source_order->shipping->email,
                                'phone'      => $source_order->billing->phone,
                            );


                            $billing_address = array(

                                'first_name' => $source_order->billing->first_name,
                                'last_name' => $source_order->billing->last_name . '(' . $order_num_to_insert . ')',
                                'address_1' => $source_order->billing->address_1,
                                'address_2' => $source_order->billing->address_2,
                                'city' => $source_order->billing->city,
                                'state' => $source_order->billing->state,
                                'postcode' => $source_order->billing->postcode,
                                'country' => $source_order->billing->country,
                                'company'    => $source_order->billing->company,
                                'email'      => $source_order->billing->email,
                                'phone'      => $source_order->billing->phone,
                                'payment_method'  => $source_order->payment_method_title,
                            );

                            // Display payment method in billing address section
                            $payment_method = $source_order->payment_method_title;
                            $billing_address['payment_method'] = 'Payment Method: ' . esc_html($payment_method);

                            $payment_gateways = WC()->payment_gateways->payment_gateways();


                            $new_order->set_address($billing_address, 'billing');
                            $new_order->set_address($shipping_address, 'shipping');
                            $new_order->set_customer_id($source_order->customer_id);
                            $new_order->set_billing_email($billing_address['email']);
                            $new_order->set_billing_phone($billing_address['phone']);
                        }


                        // add to order



                        $shipping = new WC_Order_Item_Shipping();
                        $shipping->set_method_title('Free shipping');
                        $shipping->set_method_id('free_shipping:1'); // set an existing Shipping method ID
                        $shipping->set_total(0); // optional 
                        // $new_order = wc_create_order();
                        $new_order->add_item($shipping);
                    }
                }
            }



            // // Check if the order_num already exists in the table
            // $order_exists = $wpdb->get_var(
            //     $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE order_num = %s", $order_num_to_insert)
            // );


            // echo '<pre>' ;
            // print_r($order_exists) ;
            // echo '</pre>' ;


            // if (!$order_exists) {
            //     $data = array(
            //         'order_num' => $order_num_to_insert,
            //         'status' => 1,
            //     );
            //     $wpdb->insert($table_name, $data);



            //     // // add order start 
            //     // $source_order = $order;




            //     // if (class_exists('WooCommerce')) {
            //     //     $order_data = array(
            //     //         'status' => $source_order->status,  // Change to desired status
            //     //         'customer_id' => $source_order->customer_id, // Replace with appropriate customer ID
            //     //         'customer_note' => 'customer note',
            //     //         'parent'        => null,
            //     //         'created_via'   => null,
            //     //         'cart_hash'     => null,
            //     //         'order_id'      => 0,
            //     //     );

            //     //     $new_order = wc_create_order($order_data);
            //     //     if (is_a($new_order, 'WC_Order')) {

            //     //         foreach ($source_order->line_items as $line_item) {

            //     //             $args = array(
            //     //                 'name'         => $line_item->name,
            //     //                 'tax_class'    => $line_item->tax_class,
            //     //                 'variation_id' => $line_item->variation_id,
            //     //                 'variation'    => $line_item->variation,
            //     //                 'subtotal'     => $line_item->subtotal,
            //     //                 'total'        => $line_item->total,
            //     //                 'quantity'     => $line_item->quantity,
            //     //                 'product_id'   => $line_item->product_id,
            //     //             );


            //     //             $sku = $line_item->sku; //for test purpose 
            //     //             // $sku = '123' ;
            //     //             $product_id = wc_get_product_id_by_sku($sku);
            //     //             $product = wc_get_product($product_id);
            //     //             $new_order->add_product($product, $line_item->quantity);


            //     //             $shipping_address = array(
            //     //                 'first_name' => $source_order->shipping->first_name,
            //     //                 'last_name' => $source_order->shipping->last_name,
            //     //                 'address_1' => $source_order->shipping->address_1,
            //     //                 'address_2' => $source_order->shipping->address_2,
            //     //                 'city' => $source_order->shipping->city,
            //     //                 'state' => $source_order->shipping->state,
            //     //                 'postcode' => $source_order->shipping->postcode,
            //     //                 'country' => $source_order->shipping->country,
            //     //                 'company'    => $source_order->shipping->company,
            //     //                 'email'      => $source_order->shipping->email,
            //     //                 'phone'      => $source_order->billing->phone,
            //     //             );


            //     //             $billing_address = array(

            //     //                 'first_name' => $source_order->billing->first_name,
            //     //                 'last_name' => $source_order->billing->last_name . '(' . $order_num_to_insert . ')',
            //     //                 'address_1' => $source_order->billing->address_1,
            //     //                 'address_2' => $source_order->billing->address_2,
            //     //                 'city' => $source_order->billing->city,
            //     //                 'state' => $source_order->billing->state,
            //     //                 'postcode' => $source_order->billing->postcode,
            //     //                 'country' => $source_order->billing->country,
            //     //                 'company'    => $source_order->billing->company,
            //     //                 'email'      => $source_order->billing->email,
            //     //                 'phone'      => $source_order->billing->phone,
            //     //                 'payment_method'  => $source_order->payment_method_title,
            //     //             );

            //     //             // Display payment method in billing address section
            //     //             $payment_method = $source_order->payment_method_title;
            //     //             $billing_address['payment_method'] = 'Payment Method: ' . esc_html($payment_method);

            //     //             $payment_gateways = WC()->payment_gateways->payment_gateways();


            //     //             $new_order->set_address($billing_address, 'billing');
            //     //             $new_order->set_address($shipping_address, 'shipping');
            //     //             $new_order->set_customer_id($source_order->customer_id);
            //     //             $new_order->set_billing_email($billing_address['email']);
            //     //             $new_order->set_billing_phone($billing_address['phone']);
            //     //         }


            //     //         // add to order



            //     //         $shipping = new WC_Order_Item_Shipping();
            //     //         $shipping->set_method_title('Free shipping');
            //     //         $shipping->set_method_id('free_shipping:1'); // set an existing Shipping method ID
            //     //         $shipping->set_total(0); // optional 
            //     //         // $new_order = wc_create_order();
            //     //         $new_order->add_item($shipping);
            //     //     }
            //     // }
            // }
        }
    }

    // print_r($source_order) ;
    // exit; 
    return $source_orders;
}













// Add a menu item in the WordPress admin for triggering migration
add_action('admin_menu', 'add_migration_menu');

function add_migration_menu()
{
    add_menu_page(
        'Order Migration',
        'Order Migration',
        'manage_options',
        'order_migration',
        'migration_page'
    );


    // kamrul submenu start 

    add_submenu_page(
        'order_migration', // The parent menu slug
        'All Products', // The submenu page title
        'All Products', // The submenu link text
        'manage_options', // Capability required to access this submenu
        'all_products', // The submenu slug (used in URL)
        'show_all_products' // The function to display the submenu page content
    );

    // kamrul submenu end

    // Add the submenu page for edit users data
    add_submenu_page(
        '',    // Parent menu slug
        'Create Data',     // Page title
        '',     // Menu title
        'manage_options',   // Capability required to access the page
        'create-order-data',     // Menu slug
        'create_order_data' // Callback function to render the page content
    );


    add_submenu_page(
        '',    // Parent menu slug
        'Edit Data',     // Page title
        '',     // Menu title
        'manage_options',   // Capability required to access the page
        'edit-order-data',     // Menu slug
        'edit_order_data' // Callback function to render the page content
    );
}


function migration_page()
{
    include('templates/order-api.php');
}

function create_order_data()
{
    include(plugin_dir_path(__FILE__) . 'templates/create-order-api.php');
}


function edit_order_data()
{
    include(plugin_dir_path(__FILE__) . 'templates/edit-order-api.php');
}


function show_all_products()
{

    include(plugin_dir_path(__FILE__) . 'templates/get-all-products.php');
}






// Register the custom template
add_filter('theme_page_templates', 'webhook_alert_add_template');
function webhook_alert_add_template($templates)
{
    $templates['templates/webhook-receiver.php'] = 'Webhook Receiver Template';
    return $templates;
}








// extra codes starts



// Add custom column header
function custom_shop_order_column($columns)
{
    $columns['source'] = 'Source';
    return $columns;
}
add_filter('manage_edit-shop_order_columns', 'custom_shop_order_column');

// Populate custom column with content
function populate_custom_shop_order_column($column, $post_id)
{
    if ($column === 'source') {
        echo '<a href="https://example.com">Web Link' . $post_id . '</a>';
    }
}
add_action('manage_shop_order_posts_custom_column', 'populate_custom_shop_order_column', 10, 2);

// Adjust column order
function adjust_shop_order_column_order($columns)
{
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'total') {
            $new_columns['source'] = 'Source';
        }
    }
    return $new_columns;
}
add_filter('manage_edit-shop_order_columns', 'adjust_shop_order_column_order');

// extra codes end

// function for add products start kamrul

add_action('wp_ajax_get_all_products_by_api', 'get_all_products_by_api');
add_action('wp_ajax_nopriv_get_all_products_by_api', 'get_all_products_by_api');

function get_all_products_by_api()
{

    global $wpdb;
    $table_name = $wpdb->prefix . 'woo_migrate_api_data';
    $results = $wpdb->get_results("SELECT * FROM $table_name");




    $woocommerce_instances = [];

    foreach ($results as $result) {
        $api_url = $result->api_url;
        $consumer_key = $result->consumer_key;
        $consumer_secret = $result->consumer_secret;

        // Add the WooCommerce instance and configuration to the array
        $woocommerce_instances[] = [
            'url' => $api_url,
            'consumer_key' => $consumer_key,
            'consumer_secret' => $consumer_secret,
        ];
    }

    // echo '<pre>';
    // print_r($woocommerce_instances);
    // exit;




    $all_products = [];
    foreach ($woocommerce_instances as $instance) {
        $woocommerce = new Client(
            $instance['url'],
            $instance['consumer_key'],
            $instance['consumer_secret'],
            [
                'wp_api' => true,
                'version' => 'wc/v3'
            ]
        );

        $get_all_products = $woocommerce->get('products');
        foreach ($get_all_products as $products) {
            $all_products[] = $products;
        }
    }
    // exit;

    // check if the product already exists 
    $posts_with_meta = get_posts(array(
        'post_type' => 'product', // Change 'post' to your desired post type
        'posts_per_page' => -1, // To retrieve all posts
        'meta_key' => '_product_from_where_', // Replace with your specific post meta key
        'meta_compare' => 'EXISTS', // Check if the post meta key exists
    ));

    $product_exists = [];
    if ($posts_with_meta) {
        foreach ($posts_with_meta as $post) {

            $product_exists[] = get_post_meta($post->ID, '_product_from_where_', true);
        }
        wp_reset_postdata(); // Reset post data
    }


    // echo '<pre>';
    // print_r($product_exists);

    // exit;



    foreach ($all_products as $product_data) {

        $permalinkParts = parse_url($product_data->permalink);

        $domainName = $permalinkParts['host'];
        $id = $product_data->id;

        // Concatenate domain name and id
        $product_from_where = $domainName . '_' . $id;


        if ((count($product_exists) == 0) || !in_array($product_from_where, $product_exists)) {



            // create a product by function 
            $product = array(
                'post_title'    => $product_data->name,
                'post_content'  => $product_data->description,
                'post_status'   => $product_data->status,
                'post_author'   => 1, // Author ID
                'post_type'     => 'product',
            );

            $product_id = wp_insert_post($product);


            // Set product data
            update_post_meta($product_id, '_product_from_where_', $product_from_where);

            update_post_meta($product_id, '_sku', $product_data->sku);
            update_post_meta($product_id, '_price', $product_data->price);
            update_post_meta($product_id, '_regular_price', $product_data->regular_price);
            update_post_meta($product_id, '_sale_price', $product_data->sale_price);
            update_post_meta($product_id, '_manage_stock', $product_data->manage_stock);
            update_post_meta($product_id, '_stock', $product_data->stock_quantity);
            update_post_meta($product_id, '_stock_status', $product_data->stock_status);
            update_post_meta($product_id, '_product_attributes', $product_data->attributes);
            update_post_meta($product_id, '_visibility', $product_data->catalog_visibility);



            // Add product images
            $image_id = media_handle_upload('image_url', $product_id);
            set_post_thumbnail($product_id, $image_id);


            $get_all_categories = $product_data->categories;

            $cat_ids = [];
            foreach ($get_all_categories as $categories) {

                $cat_slug = $categories->slug;

                $category_term = get_term_by('slug', $cat_slug, 'product_cat');

                $cat_id = $category_term->term_id;
                $cat_ids[] = $cat_id;
            }

            $get_all_tags = $product_data->tags;

            $tag_ids = [];
            foreach ($get_all_tags as $tags) {

                $tag_slug = $tags->slug;
                $tag_term = get_term_by('slug', $tag_slug, 'product_tag');
                $tag_id = $tag_term->term_id;

                $tag_ids[] = $tag_id;
            }
            wp_set_post_terms($product_id, $cat_ids, 'product_cat');
            wp_set_post_terms($product_id, $tag_ids, 'product_tag');

            // Save the product
            wp_update_post(array('ID' => $product_id));

            //  Mark the import as completed
            update_option('product_api_import_completed', true);
        }
    }

    wp_die();
}




add_action('wp_ajax_get_all_categories_by_api', 'get_all_categories_by_api');
add_action('wp_ajax_nopriv_get_all_categories_by_api', 'get_all_categories_by_api');

function get_all_categories_by_api()
{

    global $wpdb;
    $table_name = $wpdb->prefix . 'woo_migrate_api_data';
    $results = $wpdb->get_results("SELECT * FROM $table_name");



    $woocommerce_instances = [];

    foreach ($results as $result) {
        $api_url = $result->api_url;
        $consumer_key = $result->consumer_key;
        $consumer_secret = $result->consumer_secret;

        // Add the WooCommerce instance and configuration to the array
        $woocommerce_instances[] = [
            'url' => $api_url,
            'consumer_key' => $consumer_key,
            'consumer_secret' => $consumer_secret,
        ];
    }



    foreach ($woocommerce_instances as $instance) {
        $woocommerce = new Client(
            $instance['url'],
            $instance['consumer_key'],
            $instance['consumer_secret'],
            [
                'wp_api' => true,
                'version' => 'wc/v3'
            ]
        );

        $all_categories = $woocommerce->get('products/categories');

        foreach ($all_categories as $cat) {

            wp_insert_term($cat->name, 'product_cat');
        }
    }

    wp_die();
}




add_action('wp_ajax_get_all_tags_by_api', 'get_all_tags_by_api');
add_action('wp_ajax_nopriv_get_all_tags_by_api', 'get_all_tags_by_api');

function get_all_tags_by_api()
{

    global $wpdb;
    $table_name = $wpdb->prefix . 'woo_migrate_api_data';
    $results = $wpdb->get_results("SELECT * FROM $table_name");


    $woocommerce_instances = [];

    foreach ($results as $result) {
        $api_url = $result->api_url;
        $consumer_key = $result->consumer_key;
        $consumer_secret = $result->consumer_secret;

        // Add the WooCommerce instance and configuration to the array
        $woocommerce_instances[] = [
            'url' => $api_url,
            'consumer_key' => $consumer_key,
            'consumer_secret' => $consumer_secret,
        ];
    }



    foreach ($woocommerce_instances as $instance) {
        $woocommerce = new Client(
            $instance['url'],
            $instance['consumer_key'],
            $instance['consumer_secret'],
            [
                'wp_api' => true,
                'version' => 'wc/v3'
            ]
        );

        $all_tags = $woocommerce->get('products/tags');

        foreach ($all_tags as $tag) {

            wp_insert_term($tag->name, 'product_tag');
        }
    }

    wp_die();
}



require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
    'https://github.com/Tanvirul872/woocommerce_multistore/',
    __FILE__,
    'woocommerce_multistore'
);

//Set the branch that contains the stable release.
$myUpdateChecker->setBranch('kamrul1');

//Optional: If you're using a private repository, specify the access token like this:
// $myUpdateChecker->setAuthentication('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');

// function for add products end kamrul
