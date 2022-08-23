$(document).ready(function () {


    update_input();


    $("button.save-button").on("click", function (e) {

        let data = $("form").serialize();

        $.ajax({
            url: "ajax/ajax_login_engine_one_click_login.php?action=update",
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
        })

    });


    $(".change-provider").on("change", function (e) {

        update_input();

    });

});



function update_input() {

    let provider = $(".change-provider").val();

    $(".provider-input").css("display", "none");


    if (provider === "MAC") {

        $(".MAC").css("display", "block");

    } else if (provider === "username") {

        $(".username").css("display", "block");

    }

}
