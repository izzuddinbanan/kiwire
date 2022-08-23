
$(document).ready(function () {

    pull_data();


    //hide button update/create
    $(".btn-create, .btn-update").css("display", "none");

    $(".create-btn-firewall").on("click", function () {

        $(".btn-create, .btn-update").css("display", "none");

        $(".btn-create").css("display", "block");

        $("#inlineForm").modal();

    });

    //reset form after cancel
    $(".cancel-button").on("click", function (e) {

        $(".create-form").trigger("reset");

        $("#inlineForm").modal("hide");

    });

    //create button
    $(".btn-create").on("click", function (e) {

        let create_form = $(".create-form");

        if (create_form.parsley().validate()) {

            let data = create_form.serialize();

            $.ajax({
                url: "ajax/ajax_policy_firewall.php?action=create",
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
                url: "ajax/ajax_policy_firewall.php?action=edit_single_data",
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


//get all latest data (table)
function pull_data() {

    $.ajax({
        url: "ajax/ajax_policy_firewall.php",
        method: "GET",
        data: {
            "action": "get_all"
        },
        success: function (data) {

            if (data['status'] === "success") {

                if($.fn.dataTable.isDataTable('.table-data')){

                    $(".table-data").DataTable().destroy();

                }

                let table_str = "";

                for (let x = 0; x < data['data'].length; x++) {

                    table_str += "<tr>";

                    table_str += "<td>" + (x + 1) + "</td>";
                    table_str += "<td>" + data['data'][x]['nasid'] + "</td>";
                    table_str += "<td>" + data['data'][x]['dest'] + "</td>";


                    if (data['data'][x]['type'] === "fwip") {
                        table_str += "<td>Block this Ip addr</td>";
                    }
                    else if (data['data'][x]['type'] === "fwmac") {
                        table_str += "<td>Block this Mac addr</td>";
                    }
                    else if (data['data'][x]['type'] === "fwtcp") {
                        table_str += "<td>Block this Tcp Port</td>";
                    }
                    else {
                        table_str += "<td>Block this Udp Port</td>";
                    }


                    table_str += "<td>" + data['data'][x]['remark'] + "</td>";

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
                    "fnDrawCallback": function() {
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
                url: "ajax/ajax_policy_firewall.php",
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


//get data based on id (for update)
function getItemForForm(id) {

    if (id > 0) {

        $.ajax({
            url: "ajax/ajax_policy_firewall.php",
            method: "GET",
            data: {
                "action": "get_update",
                "id": id
            },
            success: function (data) {

                if (data) {

                    $("form.create-form").trigger("reset");

                    $("#nasid").val(data['data']['nasid']);
                    $("#dest").val(data['data']['dest']);
                    $("#type").val(data['data']['type']);
                    $("#remark").val(data['data']['remark']);

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