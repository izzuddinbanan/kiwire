$(document).ready(function () {

    
    if (success_msg.length > 0) {


        var error_space = $(".notification");

        if (error_space.length > 0) error_space.html(success_msg);
        else swal("Success", success_msg);


    }


    if (error_msg.length > 0) {


        var error_space = $(".notification");

        if (error_space.length > 0) error_space.html(error_msg);
        else swal("Error", error_msg);


    }


    $("form").each(function () {


        var current_form = $(this);
        var current_action = current_form.prop("action");


        current_form.parsley();


        if (current_action.indexOf("session=") === -1){

            if (current_action.indexOf("?") === -1){

                current_action += "?session=" + session_id;

            } else {

                current_action += "&session=" + session_id;

            }

            $(this).prop("action", current_action);

        }


    });


    $('.next-page-btn').on("click", function () {


        var next_page = $(this).data("next-page");


        $.ajax({
            url: "/user/next/?session=" + session_id,
            method: "post",
            data: {
                next_page: next_page
            },
            success: function (response) {

                window.location.href = "/user/pages/?session=" + session_id;

            },
            error: function (response) {

                swal("Error", "Something went wrong. Please try again.", "error");

            }
        });


    });


    $(".current-date").each(function () {


        var date_field = $(this);

        var date_format = date_field.data("format");

        if (date_format.length === 0) date_format = "d-MMM-yyyy";


        try {

            date_field.html((new Date()).toString(date_format));

        } catch (e) {

            console.error(e);

        }


    });


    var auto_login_form = $("form.auto-login");

    if (auto_login_form.length > 0) {


        var verification_fields = auto_login_form.data("verification");

        var next_page = auto_login_form.data("next-page");

        var countdown_timer = auto_login_form.data("countdown");


        if (verification_fields.length > 0) {


            var meet_required = true;


            verification_fields = verification_fields.split(",");


            for (var iindex in verification_fields) {


                var current_field = $("input[name=" + verification_fields[iindex] + "]").val();


                if (current_field === undefined || current_field == null || current_field === "") {

                    meet_required = false;

                }


            }



            if (meet_required === true) {


                if (next_page !== undefined && next_page.length > 0){


                    $.ajax({
                        url: "/user/next/?session=" + session_id,
                        method: "post",
                        data: {
                            next_page: next_page
                        },
                        success: function (response) {

                            window.location.href = "/user/pages/?session=" + session_id;

                        },
                        error: function (response) {

                            swal("Error", "Something went wrong. Please try again.", "error");

                        }
                    });


                }


                if (countdown_timer > 0){


                    setInterval(function () {


                        $(".countdown_timer").html(countdown_timer);


                        if (countdown_timer === 0){

                            $("form.auto-login").submit();

                        } else countdown_timer--;


                    }, 1000);


                } else auto_login_form.submit();


            }


        } else {


            if (next_page !== undefined && next_page.length > 0){

                $.ajax({
                    url: "/user/next/?session=" + session_id,
                    method: "post",
                    data: {
                        next_page: next_page
                    },
                    success: function (response) {

                        window.location.href = "/user/pages/?session=" + session_id;

                    },
                    error: function (response) {

                        swal("Error", "Something went wrong. Please try again.", "error");

                    }
                });

            }


            if (countdown_timer > 0){


                setInterval(function () {


                    $(".countdown_timer").html(countdown_timer);


                    if (countdown_timer === 0){

                        $("form.auto-login").submit();

                    } else countdown_timer--;


                }, 1000);


            } else auto_login_form.submit();


        }


    }


    $("form[name=register] input[name=username]").on("change", function () {


        var username_string = $(this).val();

        if (username_string.length > 2) {

            $.ajax({
                url: "/user/check/?session=" + session_id,
                method: "post",
                data: {
                    action: "available",
                    value: username_string
                },
                success: function (response) {

                    if (response['status'] === "failed"){


                        var warning = response['message'].split("|");

                        if (warning.length === 1) {

                            swal("Oops..", warning[0]);

                        } else {

                            swal(warning[0], warning[1]);

                        }


                    }

                }
            });

        }

    });


    let remember_me = $("input[name=remember_me]");

    if (remember_me.length > 0){

        $.ajax({
            url: "/user/remember/?session=" + session_id,
            method: "post",
            data: {},
            success: function (response){


                if (response['status'] === "success"){

                    $("input[name=username]").val(response['data']['username']);
                    $("input[name=password]").val(response['data']['password']);

                    $("input[name=remember_me]").attr("checked", true);


                }

            }
        });



    }



});


function check_and_redirect(next_page) {


    var required_full = true;


    $(".required_field").each(function (x,y) {

        if(y.type === "checkbox"){

            if(y.checked === false){

                required_full = false;

                if(y.dataset.warning) window.alert(y.dataset.warning);
                else window.alert("Please check all required field");

            }

        }

        if(y.type === "text" || y.type === "password"){

            if(y.value.length === 0){

                required_full = false;

                if(y.dataset.warning) window.alert(y.dataset.warning);
                else window.alert("Please fill in all required field");

            }

        }

    });


    if (required_full === true){

        if (next_page.length === 8) {


            $.ajax({
                url: "/user/next/?session=" + session_id,
                method: "post",
                data: {
                    next_page: next_page
                },
                success: function (response) {

                    window.location.href = "/user/pages/?session=" + session_id;

                },
                error: function (response) {

                    swal("Error", "Something went wrong. Please try again.", "error");

                }
            });


        } else {

            window.location.href = next_page;

        }

    }


}




