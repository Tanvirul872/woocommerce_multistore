<?php 

//function for create a table for save api data 
register_activation_hook( __FILE__, 'create_custom_table_on_theme_activation' );
function create_custom_table_on_theme_activation() {
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

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}






?> 