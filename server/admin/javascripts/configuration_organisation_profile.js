
$(".save-button").on("click", function (e) {

    let data = new FormData($("#update-form")[0]);

    $.ajax({
        url: "ajax/ajax_configuration_organisation_profile.php?action=update",
        method: "POST",
        data : data,
        processData: false,
        contentType: false,
        success: function (data) {

            if (data['status'] === "success") {

                // $("#current_logo_label").html("<img src='" + data['data'] + "' width='379' height='94' />");

                swal("Success", data['message'], "success");
                // let src_img = $('#profile_logo').attr('src');

                // if(src_img != undefined || src_img != null) {

                //     let d = new Date();
                //     $("#profile_logo").attr("src", src_img + "?" + d.getTime());

                // }

            } else {

                swal("Error", data['message'], "error");

            }
        },
        error: function (data) {

            swal("Error", "There is unexpected error. Please try again.", "error");

        }
    })
});

//preview image
function showImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            $('#profile_logo').attr('src', e.target.result);
            $(".badge-not-upload").hide();
        };

        reader.readAsDataURL(input.files[0]);
    }
}
