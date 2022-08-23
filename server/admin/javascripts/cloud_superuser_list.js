$(document).ready(function () {


    pull_data();

    //tooltip
    $("body").tooltip({ selector: '[data-toggle=tooltip]', trigger: 'hover' });


    //hide button update/create

    $(".btn-create, .btn-update").css("display", "none");

    $(".create-btn-superuser").on("click", function () {

        $(".create-form").trigger("reset");

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
                url: "ajax/ajax_cloud_superuser_list.php?action=create",
                method: "post",
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

        let data = $(".create-form").serialize();

        $.ajax({
            url: "ajax/ajax_cloud_superuser_list.php?action=edit_single_data",
            method: "post",
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
    });

});


function resetUsernameKey(username) {

    swal({

        title: "Are you sure?",
        text: "You will not able to reverse this action.",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, reset it!",
        cancelButtonText: "Cancel"

    }).then(function (x) {

        if (x['value'] === true) {

          $.ajax({
              url: "/admin/ajax/ajax_cloud_superuser_list.php",
              method: "post",
              data: {
                  "username": username,
                  "action": "reset"
              },
              success: function (response) {

                  if (response['status'] === "success"){

                      swal("Success", response['message'], "success");


                  } else {

                      swal("Error", response['message'], "error");

                  }

              },
              error: function (response) {

                  swal("Error", "There is an error. Please try again.", "error");

              }
          });
        }
    });

}

function pull_data() {

    $.ajax({
        url: "ajax/ajax_cloud_superuser_list.php",
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
                    table_str += "<td>" + data['data'][x]['username'] + "</td>";
                    table_str += "<td>" + data['data'][x]['fullname'] + "</td>";
                    table_str += "<td>" + data['data'][x]['tenant_default'] + "</td>";


                    if (data['data'][x]['permission'] === "r") {
                        table_str += "<td><span class=\"badge badge-success\">Read</span></td>";
                    } else {
                        table_str += "<td><span class=\"badge badge-danger\">Read + Write</span></td>";
                    }

                    table_str += "<td>" + data['data'][x]['lastlogin'] + "</td>";

                    table_str += "<td>";
                    table_str += "<a href='javascript:void(0);' data-username='" + data['data'][x]['username'] + "' class='btn btn-icon btn-success btn-xs mr-1 fa fa-pencil btn-admin-edit'></a>";
                    table_str += "<a href='javascript:void(0);' data-username='" + data['data'][x]['username'] + "' class='btn btn-icon btn-danger btn-xs mr-1 fa fa-times btn-admin-delete'></a>";
                    table_str += "<a href='javascript:void(0);' data-username='" + data['data'][x]['username'] + "' class='btn btn-icon btn-warning btn-xs mr-1 fa fa-key btn-admin-reset' data-toggle='tooltip' data-original-title='Reset 2-Factor Key'></a>";
                    table_str += "</td>";

                    table_str += "</tr>";


                }

                $(".table-data>tbody").html(table_str);

                $(".table-data").DataTable({
                    responsive: true,
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

                        $(".btn-admin-edit").on("click", function () {

                            getItemForForm($(this).data("username"));

                        });


                        $(".btn-admin-delete").on("click", function () {

                            deleteItem($(this).data("username"));

                        });


                        $(".btn-admin-reset").on("click", function () {

                            resetUsernameKey($(this).data("username"));

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
                url: "ajax/ajax_cloud_superuser_list.php",
                method: "GET",
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
        url: "ajax/ajax_cloud_superuser_list.php",
        method: "GET",
        data: {
            "action": "get_update",
            "id": id,
            "token": $("input[name=token]").val()
        },
        success: function (data) {

            if (data) {

                $("form.create-form").trigger("reset");

                $("#tenant_default").val(data['data']['tenant_default']).trigger("change");
                $("#username").val(data['data']['username']);
                $("#password").val(data['data']['password']);
                $("#fullname").val(data['data']['fullname']);
                $("#email").val(data['data']['email']);

                if((data['data']['monitor']) === 'y') $('#monitor').prop("checked", true).trigger("change");
                else $('#monitor').prop("checked", false).trigger("change");

                if((data['data']['require_mfactor']) === 'y') $('#2-factors').prop("checked", true).trigger("change");
                else $('#2-factors').prop("checked", false).trigger("change");

                $("#permission").val(data['data']['permission']).trigger("change");
                $("#groupname").val(data['data']['groupname']);

                if (data['data']['tenant_allowed'] !== undefined && data['data']['tenant_allowed'] !== null){

                    if (data['data']['tenant_allowed'].length > 0) {

                        let allowed_list = data['data']['tenant_allowed'].split(",");

                        for (let kindex in allowed_list) {

                            $("#" + allowed_list[kindex]).prop("checked", true);

                        }

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
