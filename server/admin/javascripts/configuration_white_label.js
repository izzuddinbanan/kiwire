$(document).ready(function(){


    $(".save-button").on("click", function (){


        $.ajax({
            url: "/admin/ajax/ajax_configuration_white_label.php",
            method: "post",
            data: $("form#update-form").serialize(),
            success: function (response){

                if (response['status'] === "success"){


                    swal("Success", response['message'], "success");


                } else {

                    swal("Error", response['message'], "error");

                }

            },
            error: function (response){

                swal("Error", "There is an internal error. Please retry.", "error");

            }
        });


    });


});