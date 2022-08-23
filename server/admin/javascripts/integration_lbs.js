$("button.save-button").on("click", function (e) {

    let data = $("form").serialize();

    $.ajax({
        url: "ajax/ajax_integration_lbs.php?action=update",
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

function checkOmaya() {

    $.ajax({
        url: "ajax/check_omaya.php",
        method: "POST",
        timeout: 5000,
        data: {
            api_id: $("#api_id").val(),
            api_secret: $("#api_secret").val()

        },
        success: function (x) {

            if (x !== "ERROR: connection error") {

                try {
                    var y = JSON.parse(JSON.parse(x));
                } catch(err){
                    swal("Error", "Invalid URI", "error");
                    return;
                }

                if (y['return'] !== false) {

                    $("#link").html(y['Connection']);
                    $("#key").html(y['API Status']);
                    $("#client").html(y['Client']);
                    $("#expired").html(y['Expired Date']);

                    $("#form-modal").modal();

                } else {

                    swal("Error", y['message'], "error");

                }


            } else {
                swal("Error", "Invalid credential or URI", "error");
            }

        },
        error: function (x) {
            swal("Error", "There is an error to check your configuration: " + x.statusText, "error");
        }
    });

}
