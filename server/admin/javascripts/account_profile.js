$(document).ready(function () {


    pull_data();


    $(".btn-create, .btn-update").css("display", "none");


    $(".create-btn-profile").on("click", function () {


        $(".btn-create, .btn-update").css("display", "none");

        $(".grace-user").css("display", "none");

        $(".btn-create").css("display", "block");

        $(".create-form").trigger("reset");

        $("#inlineForm").modal();


    });


    $(".cancel-button").click(function (e) {

        $(".create-form").trigger("reset");

        $("#inlineForm").modal("hide");

    });


    //create button
    $(".btn-create").on("click", function (e) {

        let create_form = $(".create-form");

        if (create_form.parsley().validate()) {

            let data = create_form.serialize();

            $.ajax({
                url: "ajax/ajax_account_profile.php?action=create",
                method: "GET",
                data: data,
                success: function (data) {

                    if (data['status'] === "success") {


                        $(".create-form").trigger("reset");

                        $("#inlineForm").modal("hide");


                        pull_data();


                        swal("Success", data['message'], "success");


                    } else {

                        swal("Error", data['message'], "error");

                    }
                },
                error: function (data) {

                    swal("Error", "There is unexpected error. Please try again.", "error");

                }
            })
        }
    });


    //update button
    $(".btn-update").on("click", function (e) {

        let create_form = $(".create-form");

        if (create_form.parsley().validate()) {

            let data = create_form.serialize();

            $.ajax({
                url: "ajax/ajax_account_profile.php?action=edit_single_data",
                method: "GET",
                data: data,
                success: function (data) {

                    if (data['status'] === "success") {


                        $("#inlineForm").modal("hide");

                        pull_data();

                        swal("Success", data['message'], "success");


                    } else {

                        swal("Error", data['message'], "error");

                    }
                },
                error: function (data) {

                    swal("Error", "There is unexpected error. Please try again.", "error");

                }
            })
        }
    });


    $("select[name=advance]").on("change", function () {

        let current_element = $(this);
        let current_name = $("#inlineForm input[name=name]").val();

        if (current_element.val() === current_name) {


            current_element.val("").trigger("change");

            swal("Error", "This profile and advance profile matched.", "error");


        }


    });


});


function pull_data() {

    $.ajax({
        url: "ajax/ajax_account_profile.php",
        method: "GET",
        data: {
            "action": "get_all"
        },
        success: function (data) {

            if (data['status'] === "success") {

                if ($.fn.dataTable.isDataTable('.table-data')) {

                    $(".table-data").DataTable().destroy();

                }

                let table_str = "";

                for (let x = 0; x < data['data'].length; x++) {

                    table_str += "<tr>";

                    table_str += '<td>' + (x + 1) + "</td>";
                    table_str += '<td>' + data['data'][x]['name'] + "</td>";
                    table_str += '<td>' + data['data'][x]['price'] + "</td>";
                    table_str += '<td>' + data['data'][x]['type'] + "</td>";
                    table_str += '<td>' + data['data'][x]['minute'] + "</td>";
                    table_str += '<td>' + data['data'][x]['speed_up'] + "</td>";
                    table_str += '<td>' + data['data'][x]['speed_down'] + "</td>";

                    table_str += '<td>';

                    table_str += "<a href=\"javascript:void(0);\" onclick=\"getItemForForm('" + data['data'][x]['id'] + "')\" class='btn btn-icon btn-success btn-xs mr-1 fa fa-pencil'</a>";
                    table_str += "<a href=\"javascript:void(0);\" onclick=\"deleteItem('" + data['data'][x]['id'] + "')\" class='btn btn-icon btn-danger btn-xs mr-1 fa fa-times'</a>";

                    table_str += "</td> </tr>";

                }


                $(".table-data>tbody").html(table_str);

                $(".table-data").dataTable({
                    dom: dt_position,
                    pageLength: dt_page,
                    buttons: dt_btn,
                    language: {
                        searchPlaceholder: "Search Records",
                        search: "",
                    },
                    "fnDrawCallback": function () {
                        if($('.dataTables_filter').find('input').hasClass('form-control-sm')){
                            $('.dataTables_filter').find('input').removeClass('form-control-sm')
                        }

                    }
                });


            } else {

                swal("Error", data['message'], "error");
            }
        },
        error: function () {

            swal("Error", "There is unexpected error. Please try again.", "error");

        }
    })
}


function deleteItem(id) {


    Swal.fire({

        input: 'select',
        inputOptions: profile_deletion,
        title: "CONFIRM DELETION?",
        text: "Please select an action to take for account linked to this profile:",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel"

    }).then((result) => {


        if (result['value'] !== undefined) {


            $.ajax({
                url: "ajax/ajax_account_profile.php",
                method: "POST",
                data: {
                    "action": "delete",
                    "id": id,
                    "account": result['value'],
                    "token": $("input[name=token]").val()
                },
                success: function (response) {

                    if (response['status'] === "success") {


                        swal("Success", response['message'], "success");

                        pull_data();


                    } else {

                        swal("Error", response['message'], "error");

                    }
                },
                error: function (response) {

                    swal("Error", "There is unexpected error. Please try again.", "error");

                }
            });


        }


    });


}


//get data based on id
function getItemForForm(id) {


    $.ajax({
        url: "ajax/ajax_account_profile.php",
        method: "GET",
        data: {
            "action": "get_update",
            "id": id
        },
        success: function (response) {


            if (response['status'] === "success") {


                for (let key in response['data']) {

                    $("#inlineForm input[name=" + key + "]").val(response['data'][key]);
                    $("#inlineForm select[name=" + key + "]").val(response['data'][key]);
                    $("#inlineForm textarea[name=" + key + "]").val(response['data'][key]);

                }


                if (response['data']['name'] === "Temp_Access"){

                    $(".grace-user").css("display", "block");

                } else {

                    $(".grace-user").css("display", "none");

                }

                $(".btn-create").css("display", "none");
                $(".btn-update").css("display", "inline-block");

                $("#inlineForm select").trigger("change");

                $("#inlineForm").modal();


            } else {

                swal("Error", response['message'], "error");

            }


        },
        error: function (error) {

            swal("Error", "There is an error", "error");

        }
    });


}
