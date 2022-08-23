$(document).ready(function () {

    update_change_devicetype();

    $("button.save-button").on("click", function (e) {

        let data = $("form").serialize();

        $.ajax({
            url: "ajax/ajax_configuration_general.php?action=update",
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

});



$(".change-device-type").on("change", function (e) {

    update_change_devicetype();

});



function update_change_devicetype() {


    let provider = $(".change-device-type").val();

    $(".provider-input").css("display", "none");


    if (provider === "random") {

        $(".random").css("display", "block");

    }


}
