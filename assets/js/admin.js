$ = jQuery;

$(document).ready(function ($) {
  $("#copy_button").click(function () {
    $("#access_key").select();
    document.execCommand("copy");
    $("#copy_button").text("Copied");
  });

  $("#wooapp-getappbtn").click(function () {
    $("#wooapp-create-app-section").addClass("d-none");
    $("#wooapp-progressbar").removeClass("d-none");

    var data = {
      action: "set_post_request",
    };

    $.ajax({
      type: "post",
      url: wta_ajax.admin_ajax,
      data: data,

      beforeSend: function () {
        setTimeout(function () {
          $("#wooapp-progressbar-loader").css("width", "50%");
        }, 1000);
      },

      success: function (response) {
        response = JSON.parse(response);

        console.log(response);

        if (response.status == "SUCCESS") {
          $("#wooapp-progressbar-loader").css("width", "80%");
          setTimeout(function () {
            $("#wooapp-progressbar").addClass("d-none");
            $("#wooapp-layout").load(location.href + " #wooapp-layout");
          }, 1500);
        } else {
          //show something wrong warning message
          $("#wooapp-progressbar").addClass("d-none");
          $("#wooapp-create-app-section").removeClass("d-none");
        }
      },
    });
  });
});
