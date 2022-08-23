$(document).ready(function () {


    $("button.save-button").on("click", function (e) {

        let data = $("form").serialize();

        $.ajax({
            url: "ajax/ajax_login_engine_term_condition.php?action=update",
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


    $(document).ready(function () {

        if($("#tnc").length > 0){

            tinymce.init({

                selector: "textarea#tnc",
                theme: "modern",
                height:400,
                plugins: [

                    "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
                    "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                    "save table contextmenu directionality emoticons template paste textcolor"

                ],

                toolbar: "undo redo | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media fullpage | forecolor backcolor emoticons"
            
            });

        }
        
    });

});