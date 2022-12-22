$ = jQuery;

$(document).ready(function(){
    $("#copy").click(function(){
        $("#text-area").select();
      document.execCommand("copy"); 
    alert("Copied On clipboard");
    });
});