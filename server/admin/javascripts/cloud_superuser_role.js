String.prototype.capitalize = function() {

    return this.charAt(0).toUpperCase() + this.slice(1);

};


$(document).ready(function () {

    pull_data();


    $(".cancel-button").click(function (e) {

        $(".create-form").trigger("reset");

    });


    $(".btn-add").on("click", function(){

        $(".create-form").trigger("reset");

        $("#groupname").css("cursor", "auto");
        document.getElementById('groupname').readOnly = false;

        $(".btn-create").html("Create");
        $("#inlineForm").modal();


    });


    $(".Select-All").on("change", function () {

        let state = $(this).prop("checked");
        let section = $(this).data("section");

        if (state === false) $(".Section-" + section).prop("checked", false);
        else $(".Section-" + section).prop("checked", true);

    });


    $(".btn-select-all").on("click", function () {

        $("input[type=checkbox]").prop("checked", true);

    });


    $(".btn-clear-all").on("click", function () {

        $("input[type=checkbox]").prop("checked", false);

    });


    $('.modal').on('hidden.bs.modal', function (e) {


        $("input[type=text]").val("");

        $(".role-list input[type=checkbox]").parent().css("display", "block");

        $("input[name=reference]").val("");


    });


    $(".filter-text").on("keyup", function () {

        let filter_role = $(this).val().trim().toLowerCase();

        if (filter_role.length > 2) {

            if (filter_role !== ":title") {

                $("input[type=checkbox]").each(function (e) {

                    let current_item = $(this);

                    if (!current_item.val().toLowerCase().indexOf(filter_role)) {

                        current_item.parent().css("display", "block");

                    } else {

                        current_item.parent().css("display", "none");

                    }

                });

            } else {


                $("input[type=checkbox]").parent().css("display", "none");

                $("input[type=checkbox].Select-All").parent().css("display", "block");


            }

        } else if (filter_role.length === 0) {

            $("input[type=checkbox]").parent().css("display", "block");

        }

    });


    $(".btn-create").on("click", function (e) {


        let create_form = $(".create-form");


        if (create_form.parsley().validate()) {

            let data = create_form.serialize();

            $.ajax({
                url: "ajax/ajax_cloud_superuser_role.php?action=create",
                method: "POST",
                data: data,
                success: function (data) {


                    if (data['status'] === "success") {


                        $(".create-form").parsley().reset();

                        $("input[type=text]").val("");

                        $(".role-list input[type=checkbox]").prop("checked", false).parent().css("display", "block");


                        pull_data();


                        $("#inlineForm").modal("toggle");

                        $("input[name=reference]").val("");


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
        url: "ajax/ajax_cloud_superuser_role.php",
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

                    table_str += '<td>' + (x + 1) + "</td>";
                    table_str += '<td>' + data['data'][x]['groupname'].capitalize() + "</td>";

                    table_str += '<td>';

                    table_str += "<a href=\"javascript:void(0);\" onclick=\"getItemForForm('" + data['data'][x]['groupname'] + "')\" class='btn btn-icon btn-success btn-xs mr-1 fa fa-pencil'</a>";
                    table_str += "<a href=\"javascript:void(0);\" onclick=\"deleteItem('" + data['data'][x]['groupname'] + "')\" class='btn btn-icon btn-danger btn-xs mr-1 fa fa-times'</a>";

                    table_str += "</td> </tr>";

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


function deleteItem(groupname) {

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

                "url": "ajax/ajax_cloud_superuser_role.php",
                "method": "POST",
                "data": {
                    "action": "delete",
                    "groupname": groupname,
                    "token": $("input[name=token]").val()
                },
                success: function (data) {

                    if (data['status'] === "success") {


                        pull_data();

                        toastr.info(data['message']);


                    } else {

                        toastr.info(data['message']);

                    }

                },
                error: function () {

                    swal("Error", "There is an error", "error");

                }

            });

        }

    });

}


//get data based on id
function getItemForForm(groupname) {

    if (groupname.length > 0) {

        $.ajax({
            url: "ajax/ajax_cloud_superuser_role.php",
            method: "GET",
            data: {
                "action": "get_update",
                "groupname": groupname
            },
            success: function (data) {

                if (data['status'] === "success") {


                    $("input[name=groupname]").val(data['data'][0]['groupname'].capitalize());
                    $("input[name=reference]").val(data['data'][0]['groupname']);

                    $(".role-list input[type=checkbox]").prop("checked", false).parent().css("display", "block");


                    for (let i = 0; i < data['data'].length; i++) {

                        $("#" + data['data'][i]['moduleid']).prop("checked", true);

                    }

                    $(".btn-create").html("Update");
                    $("#inlineForm").modal();

                    $("#groupname").css("cursor", "not-allowed");
                    document.getElementById('groupname').readOnly = true;




                } else {

                    swal("Error", data['message'], "error");

                }

            },
            error: function (error) {

                swal("Error", "There is an error", "error");

            }

        });

    }

}
