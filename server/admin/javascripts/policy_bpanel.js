$("button.save-button").on("click", function (e) {

    let data = $("form").serialize();

    $.ajax({
        url: "ajax/ajax_policy_bpanel.php?action=update",
        method: "POST",
        data : data,
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