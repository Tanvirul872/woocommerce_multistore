jQuery(document).ready(function($) {



    function loadApi() {
        var ajax_url = plugin_ajax_object.ajax_url;
        var data = {
            'action': 'migration_trigger',
            'formData': 1
        };
        $.ajax({
            url: ajax_url,
            type: 'post',
            data: data,
            success: function(response) {
                console.log('API loaded successfully');
                // alert('API loaded successfully');
            }
        });
    }

    // Load API function immediately on page load
    loadApi();

    // Load API function every 10 seconds
    setInterval(function() {
        loadApi();
    }, 10000); // 10000 milliseconds = 10 seconds







     // manual registration from admin dashboard 
  $('#woo_mifrate_create_data').submit(function (event) {
    event.preventDefault();
 

    alert('hello vaijan') ;

    
    var ajax_url = plugin_ajax_object.ajax_url;     
    // Get the educational certificate files
    var form = $('#woo_mifrate_create_data').serialize();

    var formData = new FormData ;  
    formData.append('action','woo_mifrate_create_data') ;;  
    formData.append('woo_mifrate_create_data', form ) ;

    $.ajax({
        url: ajax_url,
        data: formData,
        processData:false,
        contentType:false,
        type:'post',
        // data: data,
        
        success: function(response){
          // console.log(coupon_code);
            alert('successfully store data') ;
            location.reload() ;
        }
    });

  
  });




      // manual registration from admin dashboard 
      $('#woo_mifrate_edit_data').submit(function (event) {
        event.preventDefault();
     
    
        alert('woo_mifrate_edit_data') ;
    
        
        var ajax_url = plugin_ajax_object.ajax_url;     
        // Get the educational certificate files
        var form = $('#woo_mifrate_edit_data').serialize();
    
        var formData = new FormData ;  
        formData.append('action','woo_mifrate_edit_data') ;;  
        formData.append('woo_mifrate_edit_data', form ) ;
    
        $.ajax({
            url: ajax_url,
            data: formData,
            processData:false,
            contentType:false,
            type:'post',
            // data: data,
            
            success: function(response){
              // console.log(coupon_code);
                alert('Successfully update data') ;
                // location.reload() ;
            }
        });
    
      
      });
    


  



           // manual registration from admin dashboard 
           $('.website-btn-delete').click(function (event) {
            event.preventDefault();
         
        
            alert('website-btn-delete') ;
        
            
            var ajax_url = plugin_ajax_object.ajax_url;    
            var website_id = $(this).attr('website_id') ; 
            var formData = new FormData ;  
            formData.append('action','woo_mifrate_delete_data') ;;  
            formData.append('woo_mifrate_delete_data', website_id ) ;
        
            $.ajax({
                url: ajax_url,
                data: formData,
                processData:false,
                contentType:false,
                type:'post',
                // data: data,
                
                success: function(response){ 
                    alert('Successfully delete data') ;
                    location.reload() ;
                }
            });
        
          
          });






});




    
    