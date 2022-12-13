$ = jQuery;

$(document).ready(function ($) {
  $("#copy_button").click(function () {
    $("#access_key").select();
    document.execCommand("copy");
    $("#copy_button").text("Copied");
  });

  function get_build_progress() {
    var data = {
      action: "get_build_progress",
    };

    $("#wooapp-progressbar").removeClass("d-none");

    $.ajax({
      type: "post",
      url: wta_ajax.admin_ajax,
      data: data,
      success: function (response) {
        response = JSON.parse(response);
        if (response.status == "SUCCESS") {
          $("#wooapp-progressbar-loader").css("width", "80%");
          setTimeout(function () {
            $("#wooapp-progressbar").addClass("d-none");
            $("#wooapp-download-app-button").removeClass("d-none");
          }, 1500);
        } else {
          //show something wrong warning message
          $("#wooapp-progressbar").addClass("d-none");
          $("#wooapp-create-app-section").removeClass("d-none");
        }
      },
    });
  }

  function create_build_request() {
    $("#wooapp-create-app-section").addClass("d-none");
    $("#wooapp-progressbar").removeClass("d-none");

    var data = {
      action: "set_post_request",
    };

    $.ajax({
      type: "post",
      url: wta_ajax.admin_ajax,
      data: data,

      //   beforeSend: function () {
      //     setTimeout(function () {
      //       $("#wooapp-progressbar-loader").css("width", "50%");
      //     }, 1000);
      //   },

      success: function (response) {
        response = JSON.parse(response);
        console.log(response);
        get_build_progress();
      },
    });
  }

  $(window).bind("load", function () {
    var data = {
      action: "get_build_id",
    };
    $.ajax({
      type: "post",
      url: wta_ajax.admin_ajax,
      data: data,
      success: function (response) {
        response = JSON.parse(response);
        console.log(response);
        if (response.build_id == "") {
          $("#wooapp-create-app-section").removeClass("d-none");
        } else {
          get_build_progress();
        }
      },
    });
  });

  $("#wooapp-getappbtn").click(function () {
    create_build_request();
  });
});
