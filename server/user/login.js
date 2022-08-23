$(document).ready(function () {


    $.ajax({
        url: "/user/login/check/?session=" + session_id,
        method: "post",
        data: {},
        success: function (response) {

            if (response['status'] === "success"){


                var login_form = $("<form action='" + response['action'] + "' method='" + response['method'] + "'></form>");


                for (x = 0; x < response['data'].length; x++){

                    login_form.append($("<input type='" + response['data'][x]['type'] + "' name='" + response['data'][x]['name'] + "' value='" + response['data'][x]['value'] + "'>"));

                }


                login_form.css("display", "none");

                $("body").append(login_form);

                if(response['type'] == "manual_trigger") {

                    $("input[name=" + response['type_name'] + "]").click();
                    
                }else {

                    login_form.submit();
                }


            } else {

                window.location.href = "/user/pages/?session=" + session_id;

            }

        },
        error: function () {

            window.location.href = "/user/pages/?session=" + session_id;

        }
    });


    setTimeout(function(){

        $.ajax({
            url: "/user/message/",
            method: "post",
            data: {
                message: "[999] There is unexpected error. Please try again.",
                source: "message"
            },
            success: function (response) {

                window.history.back();

            },
            error: function (response) {

                window.history.back();

            }
        });

    }, 10000);


});

