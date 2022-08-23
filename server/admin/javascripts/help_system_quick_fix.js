$(document).ready(function () {

    $("button.btn-take-action").on("click", function () {

        $.ajax({
            url: "/admin/ajax/ajax_help_system_quick_fix.php",
            method: "post",
            data: {
                action: $(this).attr("name")
            },
            success: function (response) {

                if (response['status'] === "success"){

                    swal("Success", response['message'], "success");

                } else {

                    swal("Error", response['status'], "error");

                }


            },
            error: function (response) {

                swal("Error", "There is an error. Please retry.", "error");

            }
        });

    });

});

