$(document).ready(function () {

    $('.dropify').dropify();

    $(".btn-save-profile").on("click", function (e) {

        let data = new FormData($("#profile-form")[0]);
    
        $.ajax({
            url: "/admin/ajax/ajax_profile.php",
            method: "post",
            data: data,
            contentType: false,
            processData: false,
            success: function (response) {

                console.log(response);
                if (response['status'] === "success") {
    
    
                    swal("Success", "Your profile has been changed.", "success");
    
    
                    setTimeout(function () {
    
                        window.location.reload();
    
                    }, 15000);
    
    
                } else {
    
                    swal("Error", response['message'], "error");
    
                }
    
            },
            
            error: function (response) {
    
                swal("Error", "There is an error occured. Please retry.", "error");
    
            }
        });
    
    
    });
});

