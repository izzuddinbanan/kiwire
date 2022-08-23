var current_field_name = null;
var data_string = {};

$(document).ready(function () {


    $(".btn-update-field").on("click", function () {


        current_field_name = $(this).data("field-id");


        let variable_input = $("input[name=variable]");


        if (["fullname", "email_address", "phone_number", "gender", "age_group", "location", "birthday"].includes(current_field_name)) {

            variable_input.prop("disabled", true);

        } else {

            variable_input.prop("disabled", false);

        }

        let current_var = $(".variable-" + current_field_name).html();
        let current_dis = $(".display-" + current_field_name).html();

        if (current_var === "[empty]") current_var = "";
        if (current_dis === "[empty]") current_dis = "";

        variable_input.val(current_var);
        $("input[name=display]").val(current_dis);

        if ($(".required-" + current_field_name).html() === "Yes") $("input[name=required]").prop("checked", true);
        else $("input[name=required]").prop("checked", false);


        $("#inlineForm").modal();


    });


    $(".btn-delete-field").on("click", function () {

        current_field_name = $(this).data("field-id");

        swal({

            title: "Are you sure?",
            text: "You will not able to reverse this action.",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "Cancel"

        }).then(function (x) {

            if (x['value'] === true) {

                $.ajax({
                    url: "ajax/ajax_login_engine_additional_field.php",
                    method: "POST",
                    data: {
                        "action": "delete",
                        "field": current_field_name
                    },

                    success: function (data) {

                        if (data['status'] === "success") {

                            $("td.variable-" + current_field_name).html("[empty]");
                            $("td.display-" + current_field_name).html("[empty]");
                            $("td.required-" + current_field_name).html("No");

                            toastr.info("Success", data['message'], "success");

                        } else {

                            toastr.info("Error", data['message'], "error");

                        }

                    },

                });

            }

        });

    });


    $(".btn-update").on("click", function () {

        let variable_value = $("input[name=variable]");
        let display_value = $("input[name=display]");
        let required_value = $("input[name=required]");


        $.ajax({
            "url": "ajax/ajax_login_engine_additional_field.php",
            "method": "post",
            "data": {
                "field": current_field_name,
                "variable": variable_value.val(),
                "display": display_value.val(),
                "required": required_value.prop("checked"),
                "token": $("input[name=token]").val()
            },
            "success": function (response) {

                if (response['status'] === "success") {

                    $(".variable-" + current_field_name).html(response['data']['variable']);
                    $(".display-" + current_field_name).html(response['data']['display']);
                    $(".required-" + current_field_name).html(response['data']['required']);

                    swal("Success", response['message'], "success");

                } else {

                    swal("Error", response['message'], "error");


                }

            },
            "error": function () {

                swal("Error", "There is unexpected error. Please try again.", "error");

            }
        });


        $("#inlineForm").modal("toggle");

    });


    $("#inlineForm").on("hidden.bs.modal", function () {

        $("input[name=variable]").val("");
        $("input[name=display]").val("");

        $("input[name=required]").prop("checked", false);

    });



    $(".cancel-button").on("click", function (e) {


        $(".create-form").trigger("reset");

        $("#inlineForm").modal("hide");


    });


});
