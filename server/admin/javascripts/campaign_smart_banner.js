$(document).ready(function () {

    $(".save-button").on("click", function (e) {

        let data = new FormData($("form.update-form")[0]);

        $.ajax({
            url: "ajax/ajax_campaign_smart_banner.php?action=update",
            method: "POST",
            enctype: 'multipart/x-www-form-urlencoded',
            processData: false,
            cache: false,
            contentType: false,
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


//preview image

function showImage(input) {

       if (input.files && input.files[0]) {

            var reader = new FileReader();

            reader.onload = function (e) {
               $('#banner_logo').attr('src', e.target.result);
                $(".badge-not-upload").hide();
            };

            reader.readAsDataURL(input.files[0]);

       }

   }
