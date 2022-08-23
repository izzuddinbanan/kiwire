$(document).ready(function () {

    $(".save-button").on("click", function (e) {

        let data = $("form").serialize();

        $.ajax({
            url: "ajax/ajax_integration_zapier.php?action=update",
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

});



$(document).ready(function () {

    $('#copyAuthKey').copyToClipboard('#authkey');

});



$(".btn-generate-key").on("click", function (e) {


    e.preventDefault();


    $.ajax({

        method: "POST",
        url: "/admin/ajax/ajax_integration_zapier.php",
        data: {
            "action": "generate_key"
        },
        success: function (response) {

            if (response['status'] === "success") {

                $("#authkey").val(response['data']);

            } else {

                swal("Error", response['message'], "error");

            }

        }

    });


});

