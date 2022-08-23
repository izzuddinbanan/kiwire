
$(document).ready(function (){

    if ($("select[name=role]").val() === "backup"){

        $("#reset_server").css("display", "inline-block");

    }

});

$(".save-button").on("click", function (e) {

    let data = $("form").serialize();

    $.ajax({
        url: "/admin/ajax/ajax_configuration_high_availability.php?action=update",
        method: "POST",
        data : data,
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


$("#key_gen").on("click", function () {

    $.ajax({
        url: "/admin/ajax/ajax_configuration_high_availability.php?action=keygen",
        method: "post",
        success: function (response) {

            if (response['status'] === "success"){


                let this_server_role = $("select[name=role]").val();

                $("input[name=" + this_server_role + "_key]").val(response['data']['key']);

                toastr.info("Key has been updated");


            } else {

                swal("Error", response['message'], "error");

            }

        },
        error: function () {

            swal("Error", "There is an error. Please retry.", "error");

        }
    });


});


$("#revoke_key").on("click", function () {

    $.ajax({
        url: "/admin/ajax/ajax_configuration_high_availability.php?action=revoke",
        method: "post",
        success: function (response) {

            if (response['status'] === "success"){

                swal("Success", response['message'], "success");

            } else {

                swal("Error", response['message'], "error");

            }

        },
        error: function () {

            swal("Error", "There is an error. Please retry.", "error");

        }
    });

});


$("#reset_error").on("click", function () {

    swal({

        title: "Reset Error Count",
        html: "<span>Reset error count will resume replication.<br><br>If your backup server was down, then it is safe to reset.<br><br>If this server was main and down, please update server role to backup before reset.<br><br></span>",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Proceed"

    }).then(function (x) {

        if (x['value'] === true) {


            $.ajax({
                url: "/admin/ajax/ajax_configuration_high_availability.php?action=reset_error",
                method: "post",
                success: function (response) {

                    if (response['status'] === "success"){

                        $("input[name=e_count]").val("0");

                        swal("Success", response['message'], "success");

                    } else {

                        swal("Error", response['message'], "error");

                    }

                },
                error: function () {

                    swal("Error", "There is an error. Please retry.", "error");

                }
            });


        }


    });

});


$("#reset_server").on("click", function () {

    swal({

        title: "Reset Server",
        html: "<span>Reset server will delete all data in this server and re-sync from main server. This may take up to 2 times interval value.</span>",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Proceed"

    }).then(function (x) {

        if (x['value'] === true) {


            swal({
                imageUrl: "/assets/images/loading1_icon.gif",
                imageWidth: 80,
                imageHeight: 80,
                title: "Please wait..",
                text: "We are collecting sending instruction to reset.",
                showCancelButton: false,
                showConfirmButton: false
            });


            $.ajax({
                url: "/admin/ajax/ajax_configuration_high_availability.php?action=reset_server",
                method: "post",
                success: function (response) {

                    if (response['status'] === "success"){

                        window.location.href = "/admin/index.php";


                    } else {

                        swal("Error", response['message'], "error");

                    }

                },
                error: function () {

                    swal("Error", "There is an error. Please retry.", "error");

                }
            });


        }


    });

});


$("select[name=role]").on("change", function (){


    swal({

        title: "Changing Server Role",
        text: "Please make sure you change your backup server as well. It might take some time to complete.",
        type: "warning",
        showCancelButton: false,
        confirmButtonText: "Ok"

    }).then(function (x) {

        if (x['value'] === true) {


            let main_server = $("input[name=master_ip_address]");
            let main_key = $("input[name=master_key]");

            let backup_server = $("input[name=backup_ip_address]");
            let backup_key = $("input[name=backup_key]");

            let temp_server = main_server.val();
            let temp_key = main_key.val();

            main_server.val(backup_server.val());
            main_key.val(backup_key.val());

            backup_server.val(temp_server);
            backup_key.val(temp_key);


            if ($("select[name=role]").val() === "backup") {
                $("#reset_server").css("display", "inline-block");
            } else $("#reset_server").css("display", "none");


        }

    });

});