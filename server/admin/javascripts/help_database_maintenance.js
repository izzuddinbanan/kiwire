var table_name;

$(document).ready(function () {

    $(".btn-download-backup").on("click", function () {


        let backup_date = $(".backup-date").val();


        if (backup_date !== "none") {

            $.ajax({
                url: "/admin/ajax/ajax_help_database_maintenance.php",
                method: "post",
                data: {
                    data_date: backup_date,
                    action: "download"
                },
                success: function (response) {

                    if (response['status'] === "success") {


                        if (response['data'].length > 0) {

                            window.location.href = response['data'];

                        }


                    } else {

                        swal("Error", response['message'], "error");

                    }

                },
                error: function () {

                    swal("Error", "There is an error. Please try again.", "error");

                }
            });

        } else {

            swal("Error", "Please select a date to download.", "error");

        }


    });


    $(".btn-purge").on("click", function () {

        table_name = $(this).data("table"),

        $("#inlineForm").modal();

    }); 


    $(".cancel-button").on("click", function (e) {

        $(".create-form").trigger("reset");

        $("#inlineForm").modal("hide");

    });


    $(".btn-submit").on("click", function () {


        let create_form = $(".create-form");


        if (create_form.parsley().validate()) {


            let data = create_form.serialize();

            $.ajax({
                url: "/admin/ajax/ajax_help_database_maintenance.php",
                method: "post",
                data: {
                    table: table_name,
                    "days": $('#days').val(),
                    action: "purge"
                },
                success: function (response) {

                    if (response['status'] === "success") {

                        $(".create-form").trigger("reset");

                        $("#inlineForm").modal("hide");

                        console.log(data);

                        swal("Success", response['message'], "success");

                    } else {

                        swal("Error", response['message'], "error");

                    }

                },
                error: function (response) {

                    swal("Error", "There is an error. Please try again later.", "error");

                }


            });


        }

    });



});