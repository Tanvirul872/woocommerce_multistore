jQuery(document).ready(function ($) {
  function loadApi() {
    var ajax_url = plugin_ajax_object.ajax_url;
    var data = {
      action: "migration_trigger",
      formData: 1,
    };
    $.ajax({
      url: ajax_url,
      type: "post",
      data: data,
      success: function (response) {
        console.log("API loaded successfully");
        // alert('API loaded successfully');
      },
    });
  }

  // Load API function immediately on page load
  loadApi();

  // Load API function every 10 seconds
  setInterval(function () {
    loadApi();
  }, 10000); // 10000 milliseconds = 10 seconds

  // manual registration from admin dashboard
  $("#woo_mifrate_create_data").submit(function (event) {
    event.preventDefault();

    alert("hello vaijan");

    var ajax_url = plugin_ajax_object.ajax_url;
    // Get the educational certificate files
    var form = $("#woo_mifrate_create_data").serialize();

    var formData = new FormData();
    formData.append("action", "woo_mifrate_create_data");
    formData.append("woo_mifrate_create_data", form);

    $.ajax({
      url: ajax_url,
      data: formData,
      processData: false,
      contentType: false,
      type: "post",
      // data: data,

      success: function (response) {
        // console.log(coupon_code);
        alert("successfully store data");
        location.reload();
      },
    });
  });

  // manual registration from admin dashboard
  $("#woo_mifrate_edit_data").submit(function (event) {
    event.preventDefault();

    alert("woo_mifrate_edit_data");

    var ajax_url = plugin_ajax_object.ajax_url;
    // Get the educational certificate files
    var form = $("#woo_mifrate_edit_data").serialize();

    var formData = new FormData();
    formData.append("action", "woo_mifrate_edit_data");
    formData.append("woo_mifrate_edit_data", form);

    $.ajax({
      url: ajax_url,
      data: formData,
      processData: false,
      contentType: false,
      type: "post",
      // data: data,

      success: function (response) {
        // console.log(coupon_code);
        alert("Successfully update data");
        // location.reload() ;
      },
    });
  });

  // manual registration from admin dashboard
  $(".website-btn-delete").click(function (event) {
    event.preventDefault();

    alert("website-btn-delete");

    var ajax_url = plugin_ajax_object.ajax_url;
    var website_id = $(this).attr("website_id");
    var formData = new FormData();
    formData.append("action", "woo_mifrate_delete_data");
    formData.append("woo_mifrate_delete_data", website_id);

    $.ajax({
      url: ajax_url,
      data: formData,
      processData: false,
      contentType: false,
      type: "post",
      // data: data,

      success: function (response) {
        alert("Successfully delete data");
        location.reload();
      },
    });
  });

  $(".save_order").click(function (event) {
    event.preventDefault();

    // alert("Order Update");

    var ajax_url = plugin_ajax_object.ajax_url;
    // var val = $(this).val();
    var currentURL = window.location.href;
    var postIdPattern = /post=(\d+)/;
    var postIdMatch = currentURL.match(postIdPattern);
    var postId = postIdMatch[1];
    var titleValue = $("#select2-order_status-container").attr("title");


    var billingFirstName = $("#_billing_first_name").val();
    var billingLastName = $("#_billing_last_name").val();
    var billingCompany = $("#_billing_company").val();
    var billingAddressOne = $("#_billing_address_1").val();
    var billingAddressTwo = $("#_billing_address_2").val();
    var billingCity = $("#_billing_city").val();
    var billingState = $("#_billing_state").val();
    var billingPostcode = $("#_billing_postcode").val();
    var billingCountry = $("#select2-_billing_country-container").text();
    var billingEmail = $("#_billing_email").val();
    var billingPhone = $("#_billing_phone").val();


    var shippingFirstName = $("#_shipping_first_name").val();
    var shippingLastName = $("#_shipping_last_name").val();   
    var shippingCompany = $("#_shipping_company").val();
    var shippingAddressOne = $("#_shipping_address_1").val();
    var shippingAddressTwo = $("#_shipping_address_2").val();
    var shippingCity = $("#_shipping_city").val();
    var shippingState = $("#_shipping_state").val();
    var shippingPostcode = $("#_shipping_postcode").val();
    var shippingCountry = $("#select2-_shipping_country-container").text();
    var shippingPhone = $("#_shipping_phone").val();

    // alert(titleValue);

    var formData = new FormData();
    formData.append("action", "order_update_by_id");
    formData.append("order_update_by_id", postId);
    formData.append("order_update_status", titleValue);

    formData.append("_billing_first_name", billingFirstName);
    formData.append("_billing_last_name", billingLastName);
    formData.append("_billing_company", billingCompany);
    formData.append("_billing_address_1", billingAddressOne);
    formData.append("_billing_address_2", billingAddressTwo);
    formData.append("_billing_city", billingCity);
    formData.append("_billing_state", billingState);
    formData.append("_billing_postcode", billingPostcode);
    formData.append("select2-_billing_country-container", billingCountry);
    formData.append("_billing_email", billingEmail);
    formData.append("_billing_phone", billingPhone);
    formData.append("_shipping_first_name", shippingFirstName);
    formData.append("_shipping_last_name", shippingLastName);
    formData.append("_shipping_company", shippingCompany);
    formData.append("_shipping_address_1", shippingAddressOne);
    formData.append("_shipping_address_2", shippingAddressTwo);
    formData.append("_shipping_city", shippingCity);
    formData.append("_shipping_state", shippingState);
    formData.append("_shipping_postcode", shippingPostcode);
    formData.append("select2-_shipping_country-container", shippingCountry);
    formData.append("_shipping_phone", shippingPhone);

    $.ajax({
      url: ajax_url,
      data: formData,
      processData: false,
      contentType: false,
      type: "post",
      // data: data,

      success: function (response) {
        alert("Order Updated !!");
        // location.reload();
      },
    });
  });
});
