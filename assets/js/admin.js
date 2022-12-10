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

            // complete: function (response) {
            //     console.log(response);
            // },

            success: function (response) {
                //     console.log(response);
                //     html = ` <div class="wooapp-mail d-flex justify-content-between">
                //     <input type="email" class="form-control" placeholder="Enter your email">
                //     <button id="wooapp-downloadappbtn" class="btn btn-primary text-center">Send Download Link</button>
                // </div>`;
                //     $("#wooapp-progressbar").addClass("d-none");
                //     $("#wooapp").append(html);
                //     $("#wooapp-getappbtn").text("Done!");

                if (response != "success") {
                   



                   console.log(response);

                    // console.log(response);
                    html = ` <div class="wooapp-mail d-flex justify-content-between">
                <input type="email" class="form-control" placeholder="Enter your email">
                <button id="wooapp-downloadappbtn" class="btn btn-primary text-center">Send Download Link</button>
            </div>`;
                    $("#wooapp-progressbar").addClass("d-none");
                    $("#wooapp").append(html);
                    $("#wooapp-getappbtn").text("Done!");
                } else {
                    $("#wooapp-progressbar").addClass("d-none");
                    $("#wooapp-getappbtn").text("Error!");
                }

            },
        });

    });
});