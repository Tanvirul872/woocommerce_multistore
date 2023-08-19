<br>
<br>
<h2> Edit Data </h2> 
<br>


<?php 

  $user_id =  $_GET['id'] ;
  global $wpdb;
  $table_name = $wpdb->prefix . 'woo_migrate_api_data'; // Assuming the table has a prefix
  $query = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $user_id);
  $results = $wpdb->get_results($query); 

  ?> 

<a class="btn-edit" href="<?php echo admin_url('admin.php?page=order_migration'); ?>"> All the list </a>
<form action="#" id="woo_mifrate_edit_data" enctype="multipart/form-data">   

<div class="form-group">
    <input type="hidden" class="form-control" id="website" name="id" value="<?php echo $results[0]->id ; ?>">
</div>

<div class="form-group">
    <label for="name">Website:</label>
    <input type="text" class="form-control" id="website" name="website" value="<?php echo $results[0]->website ; ?>">
</div>
  
  <div class="form-group">
    <label for="designation">Api url :</label>
    <input type="text" class="form-control" id="api_url" name="api_url" value="<?php echo $results[0]->api_url ; ?>">
  </div>


  <div class="form-group">
    <label for="father">Api consumer key:</label>
    <input type="text" class="form-control" id="consumer_key" name="consumer_key" value="<?php echo $results[0]->consumer_key ; ?>">
  </div>


  <div class="form-group">
    <label for="mother">Api consumer secret:</label>
    <input type="text" class="form-control" id="consumer_secret" name="consumer_secret" value="<?php echo $results[0]->consumer_secret ; ?>">
  </div>

  <button type="submit" class="btn btn-primary manual_submit">Submit</button>


</form>