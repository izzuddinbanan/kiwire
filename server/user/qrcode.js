$(document).ready(function () {

    var unique_id = null, qr_count = 0, qr_checker = null;

    $.ajax({
        url: "/user/qr/?session=" + session_id,
        method: "post",
        data: {
            action: "request"
        },
        success: function (response) {

            if (response['status'] === "success"){


                $(".qr-pls-wait").html("Please show this QR to your network admin.");

                $("img.qrcode-img").attr("src", response['data']['path']);


                unique_id = response['data']['unique-id'];


                qr_checker = setInterval(function () {


                    if (qr_count < 60){


                        $.ajax({
                            url: "/user/qr/?session=" + session_id,
                            method: "post",
                            data: {
                                action: "check",
                                random: unique_id
                            },
                            success: function (response) {

                                if (response['status'] === "success"){


                                    $(".qr-pls-wait").html("Confirmed. Log you in now.");


                                    clearInterval(qr_checker);

                                    login_form = $("<form></form>");
                                    login_form.attr("action", "/user/login/?session=" + session_id);
                                    login_form.attr("method", "post");

                                    login_form.append($("<input type='hidden' name='username' value='" + response['data']['username'] + "'>"));
                                    login_form.append($("<input type='hidden' name='password' value='" + response['data']['password'] + "'>"));

                                    $("body").append(login_form);

                                    login_form.submit();


                                }

                            },
                            error: function (response) {

                                swal("Error", "There is an unexpected error. Please try again.", "error");

                            }
                        });


                    } else qr_count++;


                }, 5000);


            }

        },
        error: function () {

            $(".qr-pls-wait").html("There is an error to generate your QR code.");

        }
    });


});