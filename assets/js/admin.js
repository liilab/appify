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
            type: "post",
            url: wta_ajax.admin_ajax,
            data: data,
            success: function (response) {
                response = JSON.parse(response);

                $("#wooapp-loader").addClass("d-none");

                if (response["build_found"]) {
                    if (response["is_building"] === true) {
                        get_build_progress().then();
                    } else {
                        $("#wooapp-build-history-card").removeClass("d-none");
                        get_build_history_card();
                    }
                } else {
                    $("#wooapp-form-wrap").removeClass("d-none");
                }
            },
            error: function (request, status, error) {
                console.log(error);
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
            type: "post",
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
                //console.log(response);
                $.each(response['results'], function (key, value) {
                    var build_link = `
                    <div class="ms-auto">
                        <h6 class="text-danger fw-bold align-middle wooapp-build-error-msg">Build error!</h6>
                </div>
                    `;

                    if (response['results'][key]['status'] === 'SUCCESS') {
                        build_link = `<div class="ms-auto">
                        <button class="btn">
                            <div class="wooapp-build-history-card-icon"
                                style="background-color: rgb(244, 246, 252);">
                                <a href="`+ response['results'][key]['preview'] + `"><i class="bi bi-save"></i></a>
                            </div>
                        </button>
                        <button class="btn">
                            <div class="wooapp-build-history-card-icon"
                                style="background-color: rgb(244, 246, 252);">
                                <a href="`+ response['results'][key]['binary'] + `"><i class="bi bi-app-indicator"></i></a>
                            </div>
                        </button>
                    </div>`;

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
            },
            error: function (request, status, error) {
                console.log(error);
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

    function create_build_request($appname, $storename, $icon_url) {
        $("#wooapp-form-wrap").addClass("d-none");
        $("#wooapp-progressbar-section").removeClass("d-none");

        const data = {
            action: "create_build_request",
            app_name: $appname,
            store_name: $storename,
            icon_url: $icon_url,
        };

        $.ajax({
            type: "post",
            url: wta_ajax.admin_ajax,
            data: data,
            success: function (response) {
                response = JSON.parse(response);
                //console.log(response);
                get_build_progress();
                if (response["id"] === undefined) {
                    swal(
                        'Good job!',
                        response["detail"],
                        'success'
                    )
                }

                location.reload(true);
            },
            error: function (request, status, error) {
                console.log(error);
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

        let cnt = 0;

        while (isBuilding) {
            try {
                let response = await $.ajax({
                    type: "post",
                    url: wta_ajax.admin_ajax,
                    data: data,
                });

                let jsonResponse = JSON.parse(response);

                if (jsonResponse["id"] === undefined) {
                    //Show this error when system return
                    // build id not found error in the database
                    buildIdError = true;
                    break;
                } else {
                    isBuilding = jsonResponse["is_building"];
                    buildStatus = jsonResponse["status"];
                }
            } catch (e) {
                console.log(e);
            }

            cnt++;
            if (cnt == 100) break;
            await delay(2000);
        }


        if (buildStatus === "SUCCESS") {
            swal("Good job!", "Your app is successfully created!", "success");
            $("#wooapp-progressbar-section").addClass("d-none");
            $("#wooapp-build-history-card").removeClass("d-none");
            get_build_history();
        } else if (buildIdError) {
            swal("Oh noes!", "Build Error. Please try again.", "error");
        }
        else if (cnt > 99 && buildStatus != "SUCCESS") {
            swal("Oh noes!", "Something went wrong. Please try again.", "error");
            $("#wooapp-progressbar-section").addClass("d-none");
            $("#wooapp-build-history-card").removeClass("d-none");
        }


    }


    /**
     * Wooapp form submit
     * 
     */

    $("#wooapp-form").submit(function (e) {
        e.preventDefault();
        var $appname = $("#wooapp-appname").val();
        var $storename = $("#wooapp-storename").val();
        var $icon_url = $("#wooapp-icon").val();

        console.log($appname, $storename, $icon_url);
        create_build_request($appname, $storename, $icon_url)
    });


    /**
     * wooapp-get-app-rebuild-btn
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
     * Wooapp build history
     */

    $(window).bind("load", function () {
        get_build_history();
    });


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


