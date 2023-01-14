$ = jQuery;

const delay = (ms) => new Promise((res) => setTimeout(res, ms));

$(document).ready(function ($) {

    /**
     * Get build history
     * @function
     * @name get_build_history
     * @description Get build history
     */

    function get_build_history() {
        const data = {
            action: "get_build_history",
        };

        $.ajax({
            type: "get",
            url: wta_ajax.admin_ajax,
            data: data,
            success: function (response) {
                response = JSON.parse(response);

                $("#wooapp-loader").addClass("d-none");

                if (response["build_found"]) {
                    if (response["is_building"] === true) {
                        get_build_progress();
                    } else {
                        $("#wooapp-build-history-card").removeClass("d-none");
                        get_build_history_card();
                    }
                }

                else {
                    $("#wooapp-form-wrap").removeClass("d-none");
                }
            },
            error: function (request, status, error) {
                swal.fire("Error!", "Something went wrong!", "error");
            }
        });
    }

    /**
     * Get build history card
     * @function
     * @name get_build_history card
     * @description Get build history card
     */

    function get_build_history_card() {
        const data = {
            action: "get_build_history_card",
        };

        $.ajax({
            type: "get",
            url: wta_ajax.admin_ajax,
            data: data,

            beforeSend: function () {
                var html = `<div id="wooapp-loader" class="wooapp-loader">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading...</p>
            </div> `;

                $("#wooapp-build-history").append(html);
            },

            success: function (response) {

                $("#wooapp-build-history").empty();

                response = JSON.parse(response);
                var count = 0;
                $.each(response['results'], function (key, value) {
                    count++;
                    var build_link = `
                    <div class="ms-auto">
                        <h6 class="text-danger mt-2 fw-bold align-middle wooapp-build-error-msg">Failed!</h6>
                   </div>
                    `;

                    if (response['results'][key]['status'] === 'SUCCESS') {

                        var preview = response['results'][key]['preview'];
                        var binary = response['results'][key]['binary'];

                        if ([null, undefined, ''].includes(preview) || [null, undefined, ''].includes(binary)) {
                            build_link = `
                    <div class="ms-auto">
                        <h6 class="text-warning mt-2 fw-bold align-middle wooapp-build-error-msg">Expired!</h6>
                    </div>
                    `;
                        }
                        else {
                            build_link =
                                `<div class="ms-auto">
                        <button class="btn">
                            <div class="wooapp-build-history-card-icon"
                                style="background-color: rgb(244, 246, 252);">
                                <a href="`+ preview + `"><i class="bi bi-save"></i></a>
                            </div>
                        </button>
                        <button class="btn">
                            <div class="wooapp-build-history-card-icon"
                                style="background-color: rgb(244, 246, 252);">
                                <a href="`+ binary + `"><i class="bi bi-app-indicator"></i></a>
                            </div>
                        </button>
                    </div>`;
                        }

                    }

                    var html = `
                    <li>
                    <div class="d-flex">
                        <div class="pe-0">
                            <div class="wooapp-build-history-card-icon"
                                style="background-color: rgb(249, 78, 43);">
                                <i class="bi bi-cloud-arrow-down" style="color: white;"></i>
                            </div>
                        </div>
                        <div class="mx-2 ms-3">
                            <p class="wooapp-build-history-card-title">
                            `+ response['results'][key].config['app_name'] + `
                            </p>
                            <p class="wooapp-build-history-card-subtitle">
                            `+ moment(response['results'][key]['created_date']).format("MMM DD, YYYY | hh:mm A") + `
                            </p>
                        </div>
                        `
                        + build_link +
                        `
                    </div>
                </li>
                        `;

                    $("#wooapp-build-history").append(html);
                });

                if (count === 0) {
                    var html = `
                   <h6 class="text-center mt-5">No build history found!</h6>
                    `;
                    $("#wooapp-build-history").append(html);
                }
            },
            error: function (request, status, error) {
                swal.fire("Error!", "Something went wrong!", "error");
            }
        });
    }


    /**
     * Create build request
     * @function
     * @name create_build_request
     * @description Create build request
     * 
     * @param {*} $appname 
     * @param {*} $storename 
     * @param {*} $icon_url
     */

    function create_build_request($appname, $storename, $icon_url, $nonce) {
        const data = {
            action: "create_build_request",
            app_name: $appname,
            store_name: $storename,
            icon_url: $icon_url,
            app_create_nonce: $nonce,
        };

        $.ajax({
            type: "post",
            url: wta_ajax.admin_ajax,
            data: data,
            success: function (response) {
                response = JSON.parse(response);
                if (response['id']) {
                    get_build_progress();
                }
                else if (response["status"] == "error") {
                    return_error(response["message"]);
                }
                else {
                    return_error();
                }
            },
            error: function () {
                swal.fire("Error!", "Something went wrong", "error");
            }
        });
    }

    /**
     * Get build progress
     * @function
     * @name get_build_progress
     * @description Get build progress
     */

    async function get_build_progress() {
        $("#wooapp-progressbar-section").removeClass("d-none");

        const data = {
            action: "get_build_progress",
        };

        let buildIdError = false;
        let buildStatus = "NOT_BUILT";
        let isBuilding = true;

        let count = 0;

        while (isBuilding) {
            if (count == 20) break;
            try {
                let response = await $.ajax({
                    type: "get",
                    url: wta_ajax.admin_ajax,
                    data: data,
                });

                let jsonResponse = JSON.parse(response);

                if (jsonResponse["id"] === undefined) {
                    //Show this error when system return
                    // build id not found error in the database
                    buildIdError = true;
                    break;
                }

                isBuilding = jsonResponse["is_building"];
                buildStatus = jsonResponse["status"];

            } catch (e) {
                console.log(e);
            }

            count++;
            await delay(30000);
        }


        if (buildStatus === "SUCCESS") {
            swal.fire("Congratulations!", "Your app is successfully created!", "success");
            $("#wooapp-progressbar-section").addClass("d-none");
            $("#wooapp-build-history-card").removeClass("d-none");
            get_build_history();
        }
        else if (buildStatus === "FAILURE") {
            return_error('Build failed! Please try again.');
            $("#wooapp-progressbar-section").addClass("d-none");
            $("#wooapp-build-history-card").removeClass("d-none");
            get_build_history();
        }
        else if (buildIdError) {
            return_error('Build rrror! Please try again.');
            $("#wooapp-progressbar-section").addClass("d-none");
            $("#wooapp-build-history-card").removeClass("d-none");
            location.reload(true);
        }
        else if (count >= 20 && buildStatus != "SUCCESS") {
            return_error('Build status error! Please try again.');
            $("#wooapp-progressbar-section").addClass("d-none");
            $("#wooapp-build-history-card").removeClass("d-none");
            location.reload(true);
        }
        else {
            swal.fire("Oh no!", `${buildStatus}, Please try again.`, "error");
            $("#wooapp-progressbar-section").addClass("d-none");
            $("#wooapp-build-history-card").removeClass("d-none");
            get_build_history();
        }


    }


    /**
     * Appify form submit
     * 
     */

    $("#wooapp-form").submit(function (e) {
        e.preventDefault();
        var appname = $("#wooapp-appname").val();
        var storename = $("#wooapp-storename").val();
        var icon_url = $("#wooapp-icon").val();
        var nonce = $("#wooapp-create-app-nonce-field").val();


        if (appname.length < 3) {
            swal.fire('Wait!', 'Please enter Appname with at least 3 charecters', 'error');
            return false;
        }

        if (storename.length < 3) {
            swal.fire('Wait!', 'Please enter Storename with at least 3 charecters', 'error');
            return false;
        }

        if (icon_url == '') {
            swal.fire('Wait!', 'Please upload an icon', 'error');
            return false;
        }

        if (isValidImageUrl(icon_url) == false) {
            swal.fire('Wait!', 'Please upload a png or jpg forrmat icon', 'error');
            return false;
        }

        plugin_activation(appname, storename, icon_url, nonce);

    });

    function isValidImageUrl(url) {
        return url.match(/\.(jpeg|jpg|gif|png)$/) != null;
    }


    /**
     * Plugin activation
     * @function
     * @name plugin_activation
     * @description Plugin activation
     * @param {*} appname
     * @param {*} storename
     * @param {*} icon_url
     * @param {*} nonce
     * @returns
     */

    function plugin_activation(appname, storename, icon_url, nonce) {
        $("#wooapp-form-wrap").addClass("d-none");
        $("#wooapp-progressbar-section").removeClass("d-none");

        const data = {
            action: "plugin_activation_post_request",
        };

        $.ajax({
            type: "post",
            url: wta_ajax.admin_ajax,
            data: data,
            success: function (response) {
                response = JSON.parse(response);

                if (response["status"] == "success") {
                    create_build_request(appname, storename, icon_url, nonce);
                }
                else if (response["status"] == "error") {
                    return_error(response["message"]);
                }
                else {
                    return_error();
                }
            }
        });
    }



    /**
     * Appify get build button
     */


    $click = 0;
    $("#wooapp-get-app-rebuild-btn").click(function (e) {
        $(".wooapp-build-history-card").addClass("d-none");
        $html = `
        <div class="d-flex flex-row-reverse bd-highlight wooapp-buildhistory-btn">
            <p class="wooapp-prev">Build history<i class="bi bi-arrow-right-square ms-2"></i></p>
        </div>
        `;
        if ($click == 0) {
            $("#wooapp-form-wrap").prepend($html);
            $click++;
        }
        $("#wooapp-form-wrap").removeClass("d-none");

        $(".wooapp-prev").click(function (e) {
            $("#wooapp-form-wrap").addClass("d-none");
            $(".wooapp-build-history-card").removeClass("d-none");
        });
    });


    /**
     * Appify build history
     */

    $(window).bind("load", function () {
        get_build_history();
    });


    function return_error($message = 'Something went wrong!') {
        swal.fire('Error!', $message, 'error');
    }

    // Media Uploader for App Icon start

    if (typeof wp.media !== 'undefined') {
        var _custom_media = true,
            _orig_send_attachment = wp.media.editor.send.attachment;
        $('.menutitle-media').click(function (e) {
            var send_attachment_bkp = wp.media.editor.send.attachment;
            var button = $(this);
            var id = button.attr('id').replace('_button', '');
            _custom_media = true;
            wp.media.editor.send.attachment = function (props, attachment) {
                if (_custom_media) {
                    if ($('input#' + id).data('return') == 'url') {
                        $('input#' + id).val(attachment.url);
                    } else {
                        $('input#' + id).val(attachment.id);
                    }
                    $('div#preview' + id).css('background-image', 'url(' + attachment.url + ')');
                } else {
                    return _orig_send_attachment.apply(this, [props, attachment]);
                }
                ;
            }
            wp.media.editor.open(button);
            return false;
        });
        $('.add_media').on('click', function () {
            _custom_media = false;
        });
        $('.remove-media').on('click', function () {
            var parent = $(this).parents('td');
            parent.find('input[type="text"]').val('');
            parent.find('div').css('background-image', 'url()');
        });
    }

    // Media Uploader for App Icon end
});


