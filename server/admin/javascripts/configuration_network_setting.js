$("button.save-button").on("click", function (e) {

    let data = $("form").serialize();

    $.ajax({
        url: "ajax/ajax_configuration_network_setting.php?action=update",
        method: "POST",
        timeout: 60000,
        data : data,
        success: function (data) {

            swal("Success", data['message'], "success");
            
        },
        error: function (data) {

            swal("Error", "There is unexpected error. Please try again.", "error");

        }
    })
});
