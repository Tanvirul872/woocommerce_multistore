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


});

    
    