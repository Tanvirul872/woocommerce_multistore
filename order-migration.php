<?php
/*
Plugin Name: Order Migration
Description: Migrate orders from one WooCommerce site to another.
Version: 1.0
Author: Your Name
*/


// include files 
include('database.php');




require __DIR__ . '/vendor/autoload.php';
use Automattic\WooCommerce\Client;



// Load WordPress and WooCommerce functions  
require_once(dirname(__FILE__) . '/../../../wp-load.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');



// enqueue css and js files 
add_action('admin_enqueue_scripts','plugin_css_jsscripts');
function plugin_css_jsscripts() {
    // CSS
    wp_enqueue_style( 'style-css', plugins_url( '/style.css', __FILE__ ));
    wp_enqueue_style( 'bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css' );

    // JavaScript
    wp_enqueue_script( 'script-js', plugins_url( '/script.js', __FILE__ ),array('jquery'));
    wp_enqueue_script( 'bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js', array( 'jquery' ), '4.5.3', true );

    // Pass ajax_url to script.js
    wp_localize_script( 'script-js', 'plugin_ajax_object',
        array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}






echo '<div style="margin-left:450px">' ;



// test data start 

$source_orders = array();
$woocommerce = new Client(
    'http://localhost/ecom_4', // Your store URL
    'ck_6d97d21f227f33501cdcf59f40488269bad2351f', // Your consumer key
    'cs_67465670667804faecf1ae5284842bd4ba0bf5bf', // Your consumer secret
    [
        'wp_api' => true, // Enable the WP REST API integration
        'version' => 'wc/v3' // WooCommerce WP REST API version
    ]
);


$orders_data = $woocommerce->get('orders') ;

if (is_array($orders_data)) {
    foreach ($orders_data as $order) {
        // echo '<pre>' ;
        // print_r($order);
        // echo '</pre>' ;
        // // You might need to transform and format the order data as needed
        // before adding it to the source_orders array
        $source_orders[] = $order;
    }
}




// test data end 

echo '</div>' ;




// Function to create the settings table woo_migrte_orders
register_activation_hook( __FILE__, 'create_woo_migrte_orders_table' );
function create_woo_migrte_orders_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'woo_migrte_orders';
    // SQL query to create the table
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_num INT,
        status INT
                     
    )";

    // Execute the SQL query
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}





// Make  active members ajax function

add_action( 'wp_ajax_migration_trigger', 'migration_trigger' );
add_action( 'wp_ajax_nopriv_migration_trigger', 'migration_trigger' );

function migration_trigger() {
    // Check if the migration trigger is set
    // if (isset($_GET['do_migration']) && $_GET['do_migration'] === 'true') {
    //     migrate_orders();
        
    // }
    migrate_orders();
    wp_send_json_success('api loaded') ;
}




function migrate_orders() {
  

    // Source website URL (http://localhost/ecom_4/)
    $source_site_url = 'http://localhost/ecom_4/';
    
    // Fetch orders from the source website (You need to implement this)
    $source_orders = get_source_orders($source_site_url);

    // Loop through orders and insert them into the destination website
    foreach ($source_orders as $source_order) {
        insert_order_into_destination($source_order);
    }
}



function get_source_orders($source_site_url) {

    $source_orders = array();
    $woocommerce = new Client(
        'http://localhost/ecom_4', // Your store URL
        'ck_6d97d21f227f33501cdcf59f40488269bad2351f', // Your consumer key
        'cs_67465670667804faecf1ae5284842bd4ba0bf5bf', // Your consumer secret
        [
            'wp_api' => true, // Enable the WP REST API integration
            'version' => 'wc/v3' // WooCommerce WP REST API version
        ]
    );
    

    $orders_data = $woocommerce->get('orders') ;
    if (is_array($orders_data)) {
        foreach ($orders_data as $order) { 

        global $wpdb;
        $table_name = $wpdb->prefix . 'woo_migrte_orders';
        $order_num_to_insert = $order->id; 

        // Check if the order_num already exists in the table
        $order_exists = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE order_num = %d", $order_num_to_insert)
        );

        if (!$order_exists) {
            $data = array(
                'order_num' => $order_num_to_insert,
                'status' => 1,
            );
            $wpdb->insert($table_name, $data);



// add order start 
            $source_order = $order ;
            if (class_exists('WooCommerce')) {
                $order_data = array(
                    'status' => 'processing', // Change to desired status
                    'customer_id' => $source_order->customer_id, // Replace with appropriate customer ID
                    'customer_note' => 'customer note',
                    'parent'        => null,
                    'created_via'   => null,
                    'cart_hash'     => null,
                    'order_id'      => 0,
                );
        
                $new_order = wc_create_order($order_data);
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
                         
                        $sku = '123'; //for test purpose 
                        $product_id = wc_get_product_id_by_sku( $sku );
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
                    'phone'      => $source_order->shipping->phone,
                );


                $billing_address = array(

                    'first_name' => $source_order->billing->first_name,
                    'last_name' => $source_order->billing->last_name,
                    'address_1' => $source_order->billing->address_1,
                    'address_2' => $source_order->billing->address_2,
                    'city' => $source_order->billing->city,
                    'state' => $source_order->billing->state,
                    'postcode' => $source_order->billing->postcode,
                    'country' => $source_order->billing->country,
                    'company'    => $source_order->shipping->company,
                    'email'      => $source_order->shipping->email,
                    'phone'      => $source_order->shipping->phone,   
                );


                $new_order->set_address( $billing_address, 'billing' );
                $new_order->set_address( $shipping_address, 'shipping' );
                        
                    }
    
                    $new_order->calculate_totals();
                    // $new_order->save();
                }
        
            }
            // add order end 
            // $source_orders[] = $order;
        } 

        }
    }
    return $source_orders;
}



function insert_order_into_destination($source_order) {

    // echo '<pre>' ;
    // print_r($source_order);
    // echo '</pre>' ;

    // Check if WooCommerce is active


    // if (class_exists('WooCommerce')) {
    //     $order_data = array(
    //         'status' => 'processing', // Change to desired status
    //         'customer_id' => $source_order->customer_id, // Replace with appropriate customer ID
    //     );

    //     $new_order = wc_create_order($order_data);
    //     if (is_a($new_order, 'WC_Order')) {

    //         foreach ($source_order->line_items as $line_item) {

    //             print_r($line_item) ;
                
    //             $product = wc_get_product($line_item->product_id);
    //             $new_order->add_product($product, $line_item->quantity);
    //         }

    //         $new_order->calculate_totals();
    //     }
    // }







}


// Add a menu item in the WordPress admin for triggering migration
add_action('admin_menu', 'add_migration_menu');

function add_migration_menu() {
    add_menu_page(
        'Order Migration',
        'Order Migration',
        'manage_options',
        'order_migration',
        'migration_page'
    );
}

function migration_page() {
    ?>
    <div class="wrap">
        <h2>Order Migration</h2>
        <p>Click the button below to migrate orders.</p>
        <a href="<?php echo admin_url('admin.php?page=order_migration&do_migration=true'); ?>" class="button button-primary member-inactive">Migrate Orders</a>
    </div>
    <?php
}







// extra codes starts



// Add custom column header
function custom_shop_order_column($columns) {
    $columns['source'] = 'Source';
    return $columns;
}
add_filter('manage_edit-shop_order_columns', 'custom_shop_order_column');

// Populate custom column with content
function populate_custom_shop_order_column($column, $post_id) {
    if ($column === 'source') {
        echo '<a href="https://example.com">Web Link</a>';
    }
}
add_action('manage_shop_order_posts_custom_column', 'populate_custom_shop_order_column', 10, 2);

// Adjust column order
function adjust_shop_order_column_order($columns) {
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