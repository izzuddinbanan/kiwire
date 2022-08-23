$(document).ready(function () {

    pull_data();

    $(function () {

        $('[data-toggle="tooltip"]').tooltip();

    });
    


    $(".btn-create").on("click", function (e) {


        let data = $("form.create-form").serialize();


        $.ajax({
            url: "ajax/register.php?action=register",
            method: "post",
            data: data,
            success: function (data) {

                if (data['status'] === "Success") {


                    $(".create-form").trigger("reset");


                    swal("Success", data['message'], "success");


                } else {


                    swal("Error", data['message'], "error");


                }

            },
            error: function (data) {

                swal("Error", "There is unexpected error. Please try again.", "error");

            }

        });


    });


    $(".btn-update").on("click", function () {

        let data = $(".edit-form").serialize();

        $.ajax({
            url: "ajax/register.php?action=edit_single_data",
            method: "post",
            data: data,
            success: function (data) {

                if (data['status'] === "success") {


                    $("#edit-modal").modal("hide");

                    pull_data();

                    swal("Success", data['message'], "success");


                } else {

                    swal("Error", data['message'], "error");

                }
            },
            error: function () {

                swal("Error", "There is unexpected error. Please try again.", "error");

            }

        })

    });


});


function pull_data() {


    $.ajax({
        url: "ajax/register.php",
        method: "GET",
        data: {
            "action": "get_device"
        },
        success: function (response) {

            if (response['status'] === "success") {


                if ($.fn.dataTable.isDataTable('.table-data')) {

                    $(".table-data").DataTable().destroy();

                }

                let table_str = "";


                for (let x = 0; x < response['data'].length; x++) {


                    table_str += "<tr>";

                    table_str += "<td>" + (x + 1) + "</td>";
                    table_str += "<td>" + response['data'][x]['mac_address'] + "</td>";              
                    // table_str += "<td>" + response['data'][x]['verified'] + "</td>";

                    
                    if (response['data'][x]['verified'] === "y") {

                        table_str += "<td><div class='badge badge-sm badge-success badge-sm' style='padding: 8px;'>Verified</div></td>";

                    } else table_str += "<td><div class='badge badge-danger badge-sm' style='padding: 8px;'>Unverified</div></td>";
                  
                    table_str += "<td>";
                    table_str += "<a href='javascript:void(0);' data-id='" + response['data'][x]['id'] + "' class='btn btn-space btn-success btn-xs mr-1 btn-edit'>Edit</a>";
                    table_str += "<a href='javascript:void(0);' data-id='" + response['data'][x]['id'] + "' class='btn btn-space btn-danger btn-xs mr-1 fa btn-delete'>Delete</a>";
                    table_str += "</td>";

                                            
                    table_str += "</tr>";

                }

                $(".table-data > tbody").html(table_str);


                $(".table-data").dataTable();


                $(".btn-edit").off().on("click", function () {


                    let id = $(this).data("id");


                    $.ajax({
                        url: "ajax/register.php",
                        method: "GET",
                        data: {
                            "action": "get_update",
                            "id": id
                        },
                        success: function (data) {

                            if (data) {


                                $("form.edit-form").trigger("reset");

                                $("#mac-address").val(data['data']['mac_address']);

                                $("#reference").val(data['data']['id']);

                                $(".btn-update").css("display", "block");

                                $("#edit-modal").modal();


                            }

                        },
                        error: function () {

                            swal("Error", "There is an error", "error");

                        }

                    });


                });


                $(".btn-delete").off().on("click", function () {

                    let id = $(this).data("id");

                    swal({

                        title: "Are you sure?",
                        text: "You will not able to reverse this action.",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Yes, delete it!",
                        cancelButtonText: "Cancel"

                    }).then(function (x) {

                        // if (x['value'] === true) {

                            $.ajax({
                                url: "ajax/register.php",
                                method: "GET",
                                data: {
                                    "action": "delete",
                                    "id": id
                                },

                                success: function (data) {

                                    $("form.edit-form").trigger("reset");

                                    if (data['status'] === "success") {

                                        pull_data();

                                        swal("Success", data['message'], "success");

                                    } else {

                                        swal("Error", data['message'], "error");


                                    }

                                },

                            });

                        // } else {

                        //     swal("Error", "There is an error", "error");

                        // }

                    });


                });


            } else {
               
                swal("Error", data['message'], "error");

            }


        },
        error: function () {

            swal("Error", "There is unexpected error. Please try again.", "error");

        } 


    });



}
