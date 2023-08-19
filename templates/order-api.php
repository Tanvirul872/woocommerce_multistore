<div class="wrap">
        <h2>Order Migration  </h2>
        <p>Click the button below to migrate orders.</p>
        <a href="<?php echo admin_url('admin.php?page=order_migration&do_migration=true'); ?>" class="button button-primary member-inactive">Migrate Orders</a>
</div>


<h1> Connect Website </h1>
<a class="btn-edit" href="<?php echo admin_url('admin.php?page=create-order-data'); ?>">Add New Website </a>
<div class="wrap">
<table class="wp-list-table widefat fixed striped posts">
    <thead>
        <tr>
            <th>Website</th>
            <th>Api url</th>
            <th>Api consumer key</th>
            <th>Api consumer secret</th>
            <th>Action</th> 
        </tr>
    </thead>
<tbody>
<?php

 global $wpdb; 
 // Define the table name with the WordPress prefix
 $table_name = $wpdb->prefix . 'woo_migrate_api_data';
$results = $wpdb->get_results("SELECT * FROM $table_name");
// Loop through the results and display data
foreach ($results as $result) {
    $id =  $result->id;
    $website = $result->website;
    $api_url = $result->api_url;
    $consumer_key = $result->consumer_key;
    $consumer_secret = $result->consumer_secret;

    $edit_link = admin_url('admin.php?page=edit-order-data').'&id='.$id; 

    // Display the data in the HTML table format
    echo '<tr>';
    echo '<td>'.$website.'</td>';
    echo '<td>'.$api_url.'</td>';
    echo '<td>'.$consumer_key.'</td>';
    echo '<td>'.$consumer_secret.'</td>';
    echo '<td>';
    echo '<a href="'.$edit_link.'" class="btn-edit" member_id='.$id.'>Edit</a>'; 
    echo '<a href="#" class="btn-delete website-btn-delete" website_id='.$id.'>Delete</a>'; 
    echo '</td>';
     
    
    echo '</tr>';
    
}




?>

</tbody>
</table>
</div>