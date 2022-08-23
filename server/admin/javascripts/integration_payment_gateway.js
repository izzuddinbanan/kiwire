
$(document).ready(function () {

    update_input()

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

    $("button.save-button").on("click", function (e) {

        let data = $("form").serialize();

        $.ajax({
            url: "ajax/ajax_integration_payment_gateway.php?action=update",
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


    if (provider === "payfast") {

        $(".payfast").css("display", "block");

    } else if (provider === "paypal") {

        $(".paypal").css("display", "block");

    } else if (provider === "wirecard") {

        $(".wirecard").css("display", "block");

    } else if (provider === "alipay") {

        $(".alipay").css("display", "block");

    } else if (provider === "stripe") {

        $(".stripe").css("display", "block");

    } else if (provider === "senangpay") {

        $(".senangpay").css("display", "block");

    } else if (provider === "adyen") {

        $(".adyen").css("display", "block");

    } else if (provider === "ipay88") {

        $(".ipay88").css("display", "block");

    } else {

        $(".sarawakpay").css("display", "block");

    }

}