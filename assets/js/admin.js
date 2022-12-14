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
                        $("#wooapp-download-app-btn").removeClass("d-none");
                    }
                } else {
                    $("#wooapp-create-app").removeClass("d-none");
                }
            },
            error: function (request, status, error) {
                console.log(error);
            }
        });
    }

    function create_build_request() {
        $("#wooapp-create-app").addClass("d-none");
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
                    alert(response["message"]);
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
            $("#wooapp-download-app-btn").removeClass("d-none");
        } else {
            //show something wrong warning message
            $("#wooapp-progressbar-section").addClass("d-none");
            $("#wooapp-create-app").removeClass("d-none");
        }

        if (buildIdError) {
            alert("Something went wrong. Please try again.");
        }
    }

    $("#wooapp-get-app-btn").click(function () {
        create_build_request();
    });

    $(window).bind("load", function () {
        get_build_history();
    });
});
