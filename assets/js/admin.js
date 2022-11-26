$ = jQuery;

$(document).ready(function ($) {

    $("#copy_button").click(function () {
        $("#access_key").select();
        document.execCommand("copy");
        $("#copy_button").text("Copied");
    });






    $("#getapp_button").click(function () {
        $("#getapp_button").text("Loading...");

        var data = {
            action: 'set_post_request',
            app_name : wta_ajax.app_name,
            app_logo : wta_ajax.app_logo,
            store_name : wta_ajax.store_name,
            store_logo : wta_ajax.store_logo,
            base_url : wta_ajax.base_url,
        };

        $.ajax({
            type: 'post',
            url: wta_ajax.admin_ajax,
            data : data,

            complete: function (response) {
                console.log(response);
                $("#getapp_button").text("Get App");
            },
            success: function (response) {
                console.log(response);
                $("#getapp_button").text("Ok");
            },
        });

    });
});