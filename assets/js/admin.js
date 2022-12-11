$ = jQuery;

$(document).ready(function ($) {

    $("#copy_button").click(function () {
        $("#access_key").select();
        document.execCommand("copy");
        $("#copy_button").text("Copied");
    });






    $("#wooapp-getappbtn").click(function () {
        $("#wooapp-getappbtn").text("Loading...");
        $("#wooapp-progressbar").removeClass("d-none");

        var data = {
            action: 'set_post_request',
            // app_name: wta_ajax.app_name,
            // app_logo: wta_ajax.app_logo,
            // store_name: wta_ajax.store_name,
            // store_logo: wta_ajax.store_logo,
            // base_url: wta_ajax.base_url,
        };

        var html = "";

        $.ajax({
            type: 'post',
            url: wta_ajax.admin_ajax,
            data: data,

            beforeSend: function () {
                console.log("ready hosee");
            },

            success: function (response) {
                response = JSON.parse(response);
                console.log(response);

                if (response != "success") {
                    if (response.pending == false) {
                        $("#wooapp-progressbar").addClass("d-none");

                        html = ` <div class="wooapp-mail d-flex justify-content-between">
                <input type="email" class="form-control" placeholder=" `+ response.binary + `">
                <button id="wooapp-downloadappbtn" class="btn btn-primary text-center">Send Download Link</button>
            </div>`;

                       // $("#wooapp-getappbtn").text("Download App");
                        //$("#wooapp").append(html);
                        $("#wooapp-layout").load(location.href + " #wooapp-layout") //reload the div;
                    }
                } else {
                    $("#wooapp-progressbar").addClass("d-none");
                    $("#wooapp-getappbtn").text("Error!");
                }

            },
        });

    });
});