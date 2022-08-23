$(document).ready(function () {


    pull_data();


    $(".btn-update").on("click", function (e) {


        let data = $("form.create-form").serialize();

        $.ajax({
            url: "ajax/information.php?action=update_data",
            data: data,
            success: function (response) {

                if (response['status'] === "success") {


                    $(".create-form").trigger("reset");


                    swal("Success", response['message'], "success");


                    pull_data();


                } else {

                    swal("Error", response['message'], "error");


                }

            },
            error: function (response) {


                swal("Error", "There is unexpected error. Please try again.", "error");

            }


        });


    });


});


function pull_data() {


    $.ajax({
        url: "ajax/information.php",
        method: "GET",
        data: {
            "action": "get_data"
        },
        success: function (data) {

            if (data) {

                $("#username").val(data['data']['username']);
                $("#password").val(data['data']['password']);

                $("#fullname").val(data['data']['fullname']);
                $("#email_address").val(data['data']['email_address']);

                $("#phone_no").val(data['data']['phone_number']);
                $("#status").val(data['data']['status']);

                $("#created_date").val(data['data']['date_create']);
                $("#expired_date").val(data['data']['date_expiry']);
                
                $("#profile_sub").val(data['data']['profile_subs']);
                $("#profile_curr").val(data['data']['profile_curr']);


            }

        },
        error: function () {

            swal("Error", "There is an error", "error");

        }

    });


}