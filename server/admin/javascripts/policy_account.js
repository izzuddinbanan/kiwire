String.prototype.capitalize = function() {

    return this.charAt(0).toUpperCase() + this.slice(1);

};


$(document).ready(function () {

    pull_data();
    update_change_execAction();

    $("#inlineForm").on("hidden.bs.modal", function () {

        $(".create-form").trigger("reset");

    });

    //hide button update/create
    $(".btn-create, .btn-update").css("display", "none");

    $(".create-btn-account").on("click", function () {

        $(".btn-create, .btn-update").css("display", "none");

        $(".btn-create").css("display", "block");

        $("#inlineForm").modal();

    });


    //reset form after cancel
    $(".cancel-button").on("click", function (e) {

        $(".create-form").trigger("reset");

        $("#inlineForm").modal("hide");

    });


    $(".btn-create").on("click", function (e) {

        let create_form = $(".create-form");

        if (create_form.parsley().validate()) {

            let data = create_form.serialize();

            $.ajax({
                url: "ajax/ajax_policy_account.php?action=create",
                method: "GET",
                data: data,
                success: function (data) {

                    if (data['status'] === "success") {


                        $(".create-form").trigger("reset");

                        $("#inlineForm").modal("hide");


                        pull_data(); //get latest table


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

        let data = $(".create-form").serialize();

        $.ajax({
            url: "ajax/ajax_policy_account.php?action=edit_single_data",
            method: "GET",
            data: data,
            success: function (data) {

                if (data['status'] === "success") {


                    $("#inlineForm").modal("hide");

                    $(".create-form").trigger("reset");

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

    });

});



function pull_data() {

    $.ajax({
        url: "ajax/ajax_policy_account.php",
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

                    table_str += "<td>" + (x + 1) + "</td>";

                    table_str += "<td>" + data['data'][x]['name'] + "</td>";
                   
                    
                    if (data['data'][x]['frequency'] === 'daily') {

                        table_str += "<td>Daily</td>";

                    } else if (data['data'][x]['frequency'] === 'weekly') {

                        table_str += "<td>Weekly</td>";

                    } else if (data['data'][x]['frequency'] === 'monthly') {

                        table_str += "<td>Monthly</td>";

                    } else {

                        table_str += "<td>Yearly</td>";

                    }


                    if (data['data'][x]['exec_action'] === 'update_password') table_str += "<td>Update Password</td>";
                    else if (data['data'][x]['exec_action'] === 'update_status') table_str += "<td>Update Status</td>";
                    else {

                        table_str += "<td>Delete Account</td>";

                    }


                    if (data['data'][x]['policy_status'] !== null && data['data'][x]['policy_status'] !== "") {

                        table_str += "<td>" + data['data'][x]['policy_status'].capitalize() + "</td>";

                    } else table_str += "<td>All</td>";


                    if (data['data'][x]['policy_integration'] !== null && data['data'][x]['policy_integration'] !== "") {

                        table_str += "<td>" + data['data'][x]['policy_integration'].toUpperCase() + "</td>";

                    } else table_str += "<td>All</td>";


                    table_str += "<td>" + data['data'][x]['username'] + "</td>";


                    if (data['data'][x]['status'] === "y") {

                        table_str += "<td><span class='badge badge-success'>Active</span></td>";

                    } else table_str += "<td><span class='badge badge-danger'>Disabled</span></td>";


                    table_str += "<td><a href='javascript:void(0);' onclick=\"getItemForForm('" + data['data'][x]['id'] + "')\" class='btn btn-icon btn-success btn-xs mr-1 fa fa-pencil'></a><a href=\"javascript:void(0);\" onclick=\"deleteItem('" + data['data'][x]['id'] + "')\" class='btn btn-icon btn-danger btn-xs mr-1 fa fa-times'></a></td>";

                    table_str += "</tr>";


                }


                $(".table-data > tbody").html(table_str);

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
                url: "ajax/ajax_policy_account.php",
                method: "POST",
                data: {
                    "action": "delete",
                    id: id,
                    "token": $("input[name=token]").val()
                },

                success: function (data) {

                    if (data['status'] === "success") {


                        pull_data();


                        toastr.info("Success", data['message'], "success");

                    } else {

                        toastr.info("Error", data['message'], "error");

                    }

                },

            });

        }

    });

}



function getItemForForm(id) {

    if (id > 0) {

        $.ajax({
            url: "ajax/ajax_policy_account.php",
            method: "GET",
            data: {
                "action": "get_update",
                "id": id
            },
            success: function (data) {

                if (data) {

                    $("form.create-form").trigger("reset");

                    $("#name").val(data['data']['name']);
                    $("#frequency").val(data['data']['frequency']);
                    $("#exec_action").val(data['data']['exec_action']).trigger("change");
                    $("#policy_status").val(data['data']['policy_status']).trigger("change");
                    $("#policy_integration").val(data['data']['policy_integration']).trigger("change");
                    $("#username").val(data['data']['username']);
                    $("#action_value").val(data['data']['action_value']);


                    if ((data['data']['status']) === 'y') {
                        $('#status').prop("checked", true);
                    } else {
                        $('#status').prop("checked", false);
                    }


                    $("#reference").val(data['data']['id']);

                    $(".btn-create, .btn-update").css("display", "none");

                    $(".btn-create").css("display", "none");
                    $(".btn-update").css("display", "block");

                    $("#inlineForm").modal();

                }

            },
            error: function (error) {

                swal("Error", "There is an error", "error");

            }

        });

    }

}


$(".change-exec-action").on("change", function (e) {

    update_change_execAction();

});



function update_change_execAction() {


    let provider = $(".change-exec-action").val();

    $(".provider-input").css("display", "block");


    if (provider === "delete_account") {

        $(".delete_account").css("display", "none");

    }


}