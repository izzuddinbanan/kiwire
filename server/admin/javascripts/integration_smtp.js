
$(function(){
    $('.datetime').timepicker({
        timeFormat: 'HH:mm:ss',
        dropdown: true,
        scrollbar: true
    });


    // $('#is_24').on('change', function(){

    //     if($(this).is(':checked')){
    //         $('.time').hide();
    //     }else{
    //         $('.time').show();
    //     }
    // })
})

$("button.save-button").on("click", function (e) {

    let data = $("form").serialize();

    $.ajax({
        url: "ajax/ajax_integration_smtp.php?action=update",
        method: "POST",
        data: data,
        success: function (data) {

            if (data['status'] === "success") {

                swal("Success", data['message'], "success");

            } else {

                swal("Error", data['message'], "error");

            }

        },
        error: function (data) {

            swal("Error", "There is unexpected error. Please try again.", "error");

        }
    });

});



$(".btn-test-smtp").on("click", function (e) {

    $("#smtp-modal").modal();

});


$(".btn-execute-test").on("click", function(){


    let recipient = $("input[name=recipient_mail]").val();


    if (recipient.length > 5) {


        $(".smtp-result-space").html("<div class=\"progress progress-bar-primary progress-lg\">\n" +
            "<div class=\"progress-bar progress-bar-striped progress-bar-animated\" role=\"progressbar\" aria-valuenow=\"100\" aria-valuemin=\"100\" aria-valuemax=\"100\" style=\"width:100%\"></div>\n" +
            "</div>");


        let data = $("form").serialize();

        data += "&emailto=" + recipient;

        $.ajax({
            url: "ajax/ajax_integration_smtp.php?action=test",
            method: "POST",
            data: data,
            success: function (data) {


                $(".smtp-result-space").html(data);

                $("#smtp-modal").modal();


            },
            error: function (data) {

                swal("Error", "There is unexpected error. Please try again.", "error");

            }

        });


    } else {

        swal("Error", "Please provide an email address.", "error");

    }


});
