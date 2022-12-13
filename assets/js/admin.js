$ = jQuery;

$(document).ready(function ($) {

    $("#copy_button").click(function () {
        $("#access_key").select();
        document.execCommand("copy");
        $("#copy_button").text("Copied");
    });

    function get_build_progress() {
        const data = {
            action: "get_build_progress",
        };

        $("#wooapp-progressbar-section").removeClass("d-none");

        $.ajax({
            type: "post",
            url: wta_ajax.admin_ajax,
            data: data,
            success: function (response) {
                response = JSON.parse(response);
                console.log(response);
                if (response.status === "SUCCESS") {
                    $("#wooapp-progressbar-loader").css("width", "70%");
                    setTimeout(function () {
                        $("#wooapp-progressbar-section").addClass("d-none");
                        $("#wooapp-download-app-btn").removeClass("d-none");
                    }, 500);
                } else {
                    //show something wrong warning message
                    $("#wooapp-progressbar-section").addClass("d-none");
                    $("#wooapp-create-app").removeClass("d-none");
                }
            },
        });
    }

    function create_build_request() {
        $("#wooapp-create-app").addClass("d-none");
        $("#wooapp-progressbar-section").removeClass("d-none");

        const data = {
            action: "set_post_request",
        };

        $.ajax({
            type: "post",
            url: wta_ajax.admin_ajax,
            data: data,
            success: function (response) {
                response = JSON.parse(response);
                console.log(response);
                get_build_progress();
            },
        });
    }

    $(window).bind("load", function () {
        const data = {
            action: "get_build_id",
        };

        $.ajax({
            type: "post",
            url: wta_ajax.admin_ajax,
            data: data,
            success: function (response) {
                response = JSON.parse(response);
                console.log(response);
                if (!response["build_found"]) {
                    $("#wooapp-create-app").removeClass("d-none");
                } else if (response["is_building"]) {
                    get_build_progress();
                } else {
                    $("#wooapp-download-app-btn").removeClass("d-none");
                }
            },
        });
    });

    $("#wooapp-get-app-btn").click(function () {
        create_build_request();
    });
});
