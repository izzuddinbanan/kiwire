$("form#form_check").on("submit", function(e){


    e.preventDefault();

    let data = $(this).serialize();


    $.ajax({

        url: "/admin/ajax/ajax_help_diagnostic_account.php",
        method: "post",
        data: data,
        success: function(response){

            let diagnose_str = "";

            if (response['status'] === "success"){


                for (let kindex in response['data']) {

                    diagnose_str += kindex.split(":")[1] + " : " + response['data'][kindex] + "<br>";

                }


                $(".diagnose_result").html(diagnose_str);


            } else {

                swal("Error", response['message'], "error");

            }

        },
        error: function(){

            swal("Error", "There is unexepected error. Please try again", "error");

        }

    });


});