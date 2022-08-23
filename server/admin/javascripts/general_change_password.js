$(".btn-save-password").on("click", function (e) {

    let data = $("form#password-change").serialize();

    $.ajax({
        url: "/admin/ajax/ajax_general_change_password.php",
        method: "post",
        data: data,
        success: function (response) {

            if (response['status'] === "success") {


                swal("Success", "Your password has been change.", "success");


                setTimeout(function () {

                    window.location.reload();

                }, 15000);


            } else {

                swal("Error", response['message'], "error");

            }

        },
        error: function (response) {

            swal("Error", "There is an error occured. Please retry.", "error");

        }
    });


});