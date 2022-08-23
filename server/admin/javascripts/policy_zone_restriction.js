$(document).ready(function () {

    pull_data();

    $(".btn-create, .btn-update").css("display", "none");


    $(".create-btn-zone_restriction").on("click", function () {


        $(".btn-create, .btn-update").css("display", "none");

        $(".btn-create").css("display", "block");

        $("input[type=checkbox]").attr("checked", false);

        $("#inlineForm").modal();


    });


    //reset form after cancel

    $(".cancel-button").on("click", function (e) {


        $(".create-form").trigger("reset");

        $("input[type=checkbox]").attr("checked", false);

        $("#inlineForm").modal("hide");


    });


    $(".btn-create").on("click", function (e) {

        let create_form = $(".create-form");

        if (create_form.parsley().validate()) {

            let data = create_form.serialize();

            $.ajax({
                url: "ajax/ajax_policy_zone_restriction.php?action=create",
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


    $(".btn-update").on("click", function (e) {

        let create_form = $(".create-form");

        if (create_form.parsley().validate()) {

            let data = create_form.serialize();

            $.ajax({
                url: "ajax/ajax_policy_zone_restriction.php?action=edit_single_data",
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

            });

        }

    });

});


function pull_data() {

    $.ajax({

        url: "ajax/ajax_policy_zone_restriction.php",
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

                    table_str += "<td><a href='#' data-zone='" + data['data'][x]['id'] + "' class='btn btn-icon btn-success btn-xs mr-1 fa fa-pencil btn-edit'></a><a href='#' data-zone='" + data['data'][x]['id'] + "' class='btn btn-icon btn-danger btn-xs mr-1 fa fa-times btn-delete'></a></td>";

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

                        $(".btn-edit").off().on("click", function () {

                            getItemForForm($(this).data("zone"));

                        });


                        $(".btn-delete").off().on("click", function () {

                            deleteItem($(this).data("zone"));

                        });


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
                url: "ajax/ajax_policy_zone_restriction.php",
                method: "POST",
                data: {
                    "action": "delete",
                    id: id,
                    "token": $("input[name=token]").val()
                },

                success: function (data) {

                    if (data['status'] === "success") {

                        pull_data();

                        swal("Success", data['message'], "success");

                    } else {

                        swal("Error", data['message'], "error");

                    }

                },

            });

        }

    });

}

function getItemForForm(id) {

    if (id > 0) {

        $.ajax({
            url: "ajax/ajax_policy_zone_restriction.php",
            method: "GET",
            data: {
                "action": "get_update",
                "id": id
            },
            success: function (data) {

                if (data) {

                    $("form.create-form").trigger("reset");

                    $("#name").val(data['data']['name']);


                    $("input[type=checkbox]").attr("checked", false);

                    if(data['data']['zone'] != ""){

                        let zone_list = data['data']['zone'].split(",");
    
                        for (let kindex in zone_list){
    
                            $('input[type=checkbox][value="' + zone_list[kindex] + '"]').attr("checked", true);
    
                        }
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