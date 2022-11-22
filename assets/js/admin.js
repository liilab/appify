$ = jQuery;

$(document).ready(function ($) {

    $("#copy_button").click(function () {
        $("#access_key").select();
        document.execCommand("copy");
        $("#copy_button").text("Copied");
    });
});