$(".btn-search").on("click", function(){

    let ip_address = $("input[name=ip_address]").val();

    if (ip_address.length > 0){

        $(".progress-space").html("<div class=\"progress progress-bar-warning progress-lg\">\n" +
                                  "<div class=\"progress-bar progress-bar-striped progress-bar-animated\" role=\"progressbar\" aria-valuenow=\"100\" aria-valuemin=\"100\" aria-valuemax=\"100\" style=\"width:100%\"></div>\n" +
                                  "</div>");


        $.ajax({

            url: "/admin/ajax/ajax_help_ping_tool.php",
            method: "post",
            data: {
                ip_address: ip_address
            },
            success: function (response) {

                if (response['status'] === "success"){

                    $(".ping_result").html(response['data']);


                } else {

                    swal("Error", response['message'], "error");

                }


            },
            error: function () {

                swal("Error", "There is unexpected error occured. Please retry.", "error");

            }

        });


    } else {

        swal("Error", "Please provide IP address to ping.", "error");

    }


});