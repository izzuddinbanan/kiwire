
$(document).ready(function () {

    $('#user-name').focus();



    if (mfactors_pending === true) {

        $("#2factor").modal();

    }


    $("#forget-password").on("hidden.bs.modal", function () {

        $("form.reset-password").trigger("reset");

    })



    $(".forgot-password").on("click", function () {


        $.ajax({
            url: "/admin/ajax/ajax_index.php",
            method: "post",
            data: {
                "action": "captcha"
            },
            success: function (response) {

                if (response['status'] === "success") {


                    $("img.captcha-img").prop("src", response['data']);

                    $("#forget-password").modal();


                } else {

                    swal("Error", response['message'], "error");

                }

            },
            error: function () {

                swal("Error", "There is unexpected error. Please try again.", "error");

            }
        });

    });


    $("form.reset-password").on("submit", function (e) {


        e.preventDefault();

        let data = $(this).serialize();

        data += "&action=submit";


        $.ajax({
            url: "/admin/ajax/ajax_index.php",
            method: "post",
            data: data,
            success: function (response) {

                if (response['status'] === "success") {

                    swal("Success", response['message'], "success");

                } else {

                    swal("Error", response['message'], "error");

                }

            },
            error: function () {

                swal("Error", "There is unexpected error. Please try again.", "error");

            }
        });



    });


    $("form.login").on("submit", function (e) {

        e.preventDefault();

        let credential = $(this).serialize();

        $.ajax({
            url: "/admin/ajax/ajax_index.php?action=login",
            method: "post",
            data: credential,
            success: function (response) {

                if (response['status'] === "success") {


                    if (response['data']['next'] === "2factor") {


                        $("form.login").trigger("reset");

                        $("#2factor").modal();


                    } else {


                        if (response['data']['page'].length > 0) {

                            window.location.href = response['data']['page'];

                        } else {

                            window.location.href = "/admin/dashboard.php";

                        }


                    }



                } else {

                    swal("Error", response['message'], "error");

                }

            },
            error: function (response) {

                swal("Error", "There is an error occurred, please try again.", "error");

            }
        });


    });



    $("form.mfactor").on("submit", function (e) {


        e.preventDefault();

        let credential = $(this).serialize();


        $.ajax({
            url: "/admin/ajax/ajax_index.php?action=mfactor-check",
            method: "post",
            data: credential,
            success: function (response) {

                if (response['status'] === "success") {


                    if (response['data']['page'].length > 0) {

                        window.location.href = response['data']['page'];

                    } else {

                        window.location.href = "/admin/dashboard.php";

                    }


                } else {

                    swal("Error", response['message'], "error");

                }

            },
            error: function (response) {

                swal("Error", "There is an error occurred, please try again.", "error");

            }
        });


    });


    if (/^((?!chrome|android).)*safari/i.test(navigator.userAgent)) {

        $("#browser-warning").css("display", "block");

    }



});