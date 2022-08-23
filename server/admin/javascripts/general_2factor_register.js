$(document).ready(function () {


    $("form#password-confirm").on("submit", function(e){


        e.preventDefault();


        $.ajax({
            url: "/admin/ajax/ajax_general_2factor_register.php",
            method: "post",
            data: $(this).serialize(),
            success: function (response) {

                if (response['status'] === "success"){


                    $("input[type=password], button[type=submit]").remove();

                    $("img.qr-factor").attr("src", response['data']['qr']);

                    $("span#key").html(response['data']['key']);

                    $("#display-qr").css("display", "block");


                    setTimeout(function () {

                        window.location.reload();

                    }, 60000);


                } else {

                    swal("Error", response['message'], "error");

                }

            },
            error: function (response) {

                swal("Error", "There is an error. Please try again.", "error");

            }
        });


    });


});