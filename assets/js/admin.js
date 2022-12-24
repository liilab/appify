$ = jQuery;

const delay = (ms) => new Promise((res) => setTimeout(res, ms));

$(document).ready(function ($) {

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

                $("#test").append(html);
            },

            success: function (response) {

                $("#test").empty();

                response = JSON.parse(response);
                //console.log(response);
                $.each(response['results'], function (key, value) {
                    var html = `
                        <li>
                        <div class="row">
                            <div class="col-2 pe-0">
                                <div class="wooapp-build-history-card-icon" style="background-color: rgb(249, 78, 43);">
                                    <i class="bi bi-cloud-arrow-down" style="color: white;"></i>
                                </div>
                            </div>
                            <div class="col-6">
                                <h5 class="wooapp-build-history-card-title">
                                `+ response['results'][key].config['app_name'] + `
                                </h5>
                                <p class="wooapp-build-history-card-subtitle">
                                `+ moment(response['results'][key]['created_date']).format("MMM DD, YYYY | hh:mm A") + `
                                </p>
                            </div>
                            <div class="col-4">
                                <button class="btn">
                                    <div class="wooapp-build-history-card-icon" style="background-color: rgb(244, 246, 252);">
                                    <a href="`+ response['results'][key]['preview'] + `" ><i class="bi bi-save"></i></a>
                                    </div>
                                </button>
                                <button class="btn">
                                    <div class="wooapp-build-history-card-icon" style="background-color: rgb(244, 246, 252);">
                                        <a href="`+ response['results'][key]['binary'] + `" ><i class="bi bi-app-indicator"></i></a>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </li>`;
                    $("#test").append(html);
                });
            },
            error: function (request, status, error) {
                console.log(error);
            }
        });
    }

    function create_build_request() {
        $("#wooapp-form-wrap").addClass("d-none");
        $("#wooapp-progressbar-section").removeClass("d-none");

        const data = {
            action: "create_build_request",
        };

        $.ajax({
            type: "post",
            url: wta_ajax.admin_ajax,
            data: data,
            success: function (response) {
                response = JSON.parse(response);
                get_build_progress().then();
                if (response["id"] === undefined) {
                    alert(response["detail"]);
                }
            },
            error: function (request, status, error) {
                console.log(error);
            }
        });
    }

    async function get_build_progress() {
        $("#wooapp-progressbar-section").removeClass("d-none");

        const data = {
            action: "get_build_progress",
        };

        let buildIdError = false;
        let buildStatus = "NOT_BUILT";
        let isBuilding = true;

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

            await delay(2000);
        }

        if (buildStatus === "SUCCESS") {
            $("#wooapp-progressbar-section").addClass("d-none");
            $("#wooapp-build-history-card").removeClass("d-none");
        } else {
            //show something wrong warning message
            location.reload();
        }

        if (buildIdError) {
            alert("Something went wrong. Please try again.");
        }
    }

    $("#wooapp-form").submit(function (e) {
        create_build_request();
        e.preventDefault();
        console.log("wooapp-get-app-btn");
    });

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


