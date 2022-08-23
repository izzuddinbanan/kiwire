$(document).ready(function () {


    pull_data();


    //hide button update/create

    $(".btn-create, .btn-update").css("display", "none");


    $(".create-btn-tenant").on("click", function () {


        $(".btn-create, .btn-update").css("display", "none");

        $(".btn-create").css("display", "block");

        $("#inlineForm").modal();


    });


    //reset form after cancel

    $(".cancel-button").on("click", function (e) {

        $("#inlineForm").modal("hide");

    });


    $(".btn-create").on("click", function (e) {

        let create_form = $(".create-form");

        if (create_form.parsley().validate()) {

            let data = create_form.serialize();

            $.ajax({
                url: "ajax/ajax_cloud_tenant_list.php?action=create",
                method: "GET",
                data: data,
                success: function (data) {

                    if (data['status'] === "success") {


                        pull_data();

                        $("#inlineForm").modal("hide");

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
            url: "ajax/ajax_cloud_tenant_list.php?action=edit_single_data",
            method: "GET",
            data: data,
            success: function (data) {

                if (data['status'] === "success") {


                    pull_data();


                    $("#inlineForm").modal("hide");

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


    $("#inlineForm").on("hidden.bs.modal", function () {

        $("form").trigger("reset");

        $("form.create-form").parsley().reset();

        $("input[name=client_name]").prop("readonly", false);
        $("input[name=admin_id]").prop("readonly", false);
        $("input[name=admin_pass]").prop("readonly", false);
        $("input[name=admin_email]").prop("readonly", false);


    });


});


function pull_data() {

    $.ajax({
        url: "ajax/ajax_cloud_tenant_list.php",
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
                    table_str += "<td>" + data['data'][x]['tenant_id'] + "</td>";
                    table_str += "<td>" + data['data'][x]['tenant_name'] + "</td>";
                    table_str += "<td>" + data['data'][x]['admin'] + "</td>";
                    table_str += "<td>" + data['data'][x]['expiry_date'] + "</td>";

                    table_str += "<td><a href=\"javascript:void(0);\" onclick=\"getItemForForm('" + data['data'][x]['tenant_id'] + "')\" class='btn btn-icon btn-success btn-xs mr-1 fa fa-pencil'></a><a href=\"javascript:void(0);\" onclick=\"deleteItem('" + data['data'][x]['tenant_id'] + "')\" class='btn btn-icon btn-danger btn-xs mr-1 fa fa-times'></a></td>";

                    table_str += "</tr>";

                }


                $(".table-data>tbody").html(table_str);

                $(".table-data").DataTable({
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
                url: "ajax/ajax_cloud_tenant_list.php",
                method: "POST",
                data: {
                    "action": "delete",
                    id: id,
                    token: $("input[name=token]").val()

                },

                success: function (data) {


                    if (data['status'] === "success") {


                        pull_data();

                        toastr.info(data['message']);


                    } else {

                        toastr.info(data['message']);


                    }

                },

            });

        }

    });

}

function getItemForForm(id) {


    $.ajax({
        url: "ajax/ajax_cloud_tenant_list.php",
        method: "GET",
        data: {
            "action": "get_update",
            "id": id
        },
        success: function (response) {


            if (response['status'] === "success"){


                for (let key in response['data']){

                    $("#inlineForm input[name=" + key + "]").val(response['data'][key]);

                }

                $("input[name=client_name]").prop("readonly", true);
                $("input[name=admin_id]").prop("readonly", true);
                $("input[name=admin_pass]").prop("readonly", true);
                $("input[name=admin_email]").prop("readonly", true);
                $("input[name=simultaneous]").prop("readonly", false);


                $(".btn-create").css("display", "none");
                $(".btn-update").css("display", "inline-block");

                $("#inlineForm").modal();


            } else {

                swal("Error", response['message'], "error");

            }

        },
        error: function (response) {

            swal("Error", "There is an error", "error");

        }

    });


}
