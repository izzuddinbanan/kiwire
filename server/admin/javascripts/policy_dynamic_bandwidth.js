String.prototype.capitalize = function() {

    return this.charAt(0).toUpperCase() + this.slice(1);

};



$(document).ready(function () {


    pull_data();

    update_input();


    $(".btn-create, .btn-update").css("display", "none");


    $(".create-btn-bandwidth").on("click", function () {

        $(".btn-create, .btn-update").css("display", "none");

        $(".btn-create").css("display", "block");

        $("#inlineForm").modal();

    });


    $(".cancel-button").on("click", function (e) {

        $(".create-form").trigger("reset");

        $("#inlineForm").modal("hide");

    });


    $(".btn-create").on("click", function (e) {

        let create_form = $(".create-form");

        if (create_form.parsley().validate()) {

            let data = create_form.serialize();

            $.ajax({
                url: "ajax/ajax_policy_dynamic_bandwidth.php?action=create",
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


    $(".btn-update").on("click", function (e) {


        let data = $(".create-form").serialize();

        $.ajax({
            url: "ajax/ajax_policy_dynamic_bandwidth.php?action=edit_single_data",
            method: "GET",
            data: data,
            success: function (data) {

                if (data['status'] === "success") {

                    $("form.create-form").trigger("reset");

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
    });


    $(".change-provider").on("change", function (e) {

        update_input();

    });


});


function pull_data() {

    $.ajax({
        url: "ajax/ajax_policy_dynamic_bandwidth.php",
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
                    table_str += "<td>" + data['data'][x]['applied_to'].capitalize() + "</td>";
                    table_str += '<td>' + (data['data'][x]['applied_to'] == "zone" ? data['data'][x]['at_zone'] : "-" ) + "</td>";
                    table_str += '<td>' + (data['data'][x]['applied_to'] == "users" ? data['data'][x]['at_user'] : "-" ) + "</td>";

                    table_str += '<td>' + (data['data'][x]['applied_to'] == "zone" ? data['data'][x]['k_trigger'] : "-" ) + "</td>";
                    table_str += "<td>" + data['data'][x]['priority'] + "</td>";
                    table_str += "<td>" + data['data'][x]['download_speed'] + "</td>";
                    table_str += "<td>" + data['data'][x]['upload_speed'] + "</td>";

                    table_str += "<td><a href=\"javascript:void(0);\" onclick=\"getItemForForm('" + data['data'][x]['id'] + "')\" class='btn btn-icon btn-success btn-xs mr-1 fa fa-pencil'></a><a href=\"javascript:void(0);\" onclick=\"deleteItem('" + data['data'][x]['id'] + "')\" class='btn btn-icon btn-danger btn-xs mr-1 fa fa-times'></a></td>";

                    table_str += "</tr>";
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
                url: "ajax/ajax_policy_dynamic_bandwidth.php",
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
            url: "ajax/ajax_policy_dynamic_bandwidth.php",
            method: "GET",
            data: {
                "action": "get_update",
                "id": id
            },
            success: function (data) {

                if (data) {

                    $("form.create-form").trigger("reset");

                    $("#applied_to").val(data['data']['applied_to']);
                    $("#at_user").val(data['data']['at_user']);
                    $("#at_zone").val(data['data']['at_zone']);
                    $("#priority").val(data['data']['priority']);

                    $("#download_speed").val(data['data']['download_speed']);
                    $("#upload_speed").val(data['data']['upload_speed']);
                    $("#k_trigger").val(data['data']['k_trigger']);

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


function update_input() {

    let provider = $(".change-provider").val();


    $(".provider-input").css("display", "none");


    if (provider === "all") {

        $(".all").css("display", "block");

    } else if (provider === "zone") {

        $(".zone").css("display", "block");

    } else {

        $(".users").css("display", "block");

    }

}
