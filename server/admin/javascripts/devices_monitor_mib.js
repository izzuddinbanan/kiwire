$(document).ready(function () {

    pull_data();


    $(".btn-create, .btn-update").css("display", "none");


    $(".create-btn-mib").on("click", function () {


        $(".btn-create, .btn-update").css("display", "none");

        $(".btn-create").css("display", "block");

        $("#inlineForm").modal();


    });


    $(".cancel-button").on("click", function (e) {


        $(".create-form").trigger("reset");

        $("#inlineForm").modal("hide");


    });


    $("#inlineForm").on("hidden.bs.modal", function () {

        $(".create-form").trigger("reset");

        $("#inlineForm").modal("hide");

    });


    $(".btn-create").on("click", function (e) {

        let create_form = $(".create-form");

        if (create_form.parsley().validate()) {

            let data = create_form.serialize();

            $.ajax({
                url: "ajax/ajax_devices_monitor_mib.php?action=create",
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

        let create_form = $(".create-form");

        if (create_form.parsley().validate()) {

            let data = create_form.serialize();

            $.ajax({
                url: "ajax/ajax_devices_monitor_mib.php?action=edit_single_data",
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
});


function pull_data() {

    $.ajax({
        url: "ajax/ajax_devices_monitor_mib.php",
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
                    table_str += "<td>" + data['data'][x]['mib_name'] + "</td>";
                    table_str += "<td>" + data['data'][x]['description'] + "</td>";

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
                url: "ajax/ajax_devices_monitor_mib.php",
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
            url: "ajax/ajax_devices_monitor_mib.php",
            method: "GET",
            data: {
                "action": "get_update",
                "id": id
            },
            success: function (data) {

                if (data) {

                    $("form.create-form").trigger("reset");

                    $("#mib_name").val(data['data']['mib_name']);
                    $("#description").val(data['data']['description']);
                    $("#system_name").val(data['data']['system_name']);
                    $("#dev_loc").val(data['data']['dev_loc']);

                    $("#cpu_load").val(data['data']['cpu_load']);
                    $("#memory_total").val(data['data']['memory_total']);
                    $("#memory_used").val(data['data']['memory_used']);
                    $("#disk_total").val(data['data']['disk_total']);

                    $("#disk_used").val(data['data']['disk_used']);
                    $("#device_count").val(data['data']['device_count']);
                    $("#input_vol").val(data['data']['input_vol']);
                    $("#output_vol").val(data['data']['output_vol']);

                    $("#uptime").val(data['data']['uptime']);
                    $("#if_desc").val(data['data']['if_desc']);
                    $("#if_total").val(data['data']['if_total']);
                    $("#if_status").val(data['data']['if_status']);

                    $("#if_speed").val(data['data']['if_speed']);
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
