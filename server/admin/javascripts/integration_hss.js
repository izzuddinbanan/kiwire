$(function(){
     
    $('.datetime').timepicker({
        timeFormat: 'HH:mm:ss',
        dropdown: true,
        scrollbar: true
    });


    // $('#is_24').on('change', function(){

    //     if($(this).is(':checked')){
    //         $('.time').hide();
    //     }else{
    //         $('.time').show();
    //     }
    // })

})

$("button.save-button").on("click", function (e) {

    let data = $("form").serialize();

    $.ajax({
        url: "ajax/ajax_integration_hss.php?action=update",
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
    });

});

$("button.test-button").on("click", function (e) {

    let data = $("form").serialize();

    $.ajax({
        url: "ajax/ajax_integration_hss.php?action=test",
        method: "POST",
        data: data,
        success: function (data) {

            if (data['status'] === "success") {

                swal("Success", data['message'], "success");

            } else {

                swal("Error", data['message'], "error");

            }

            $("button.swal2-confirm").on("click", function (e) {
                location.reload();
            })


        },
        error: function (data) {

            swal("Error", "There is unexpected error. Please try again.", "error");

        }
    });

});

