String.prototype.capitalize = function() {

    return this.charAt(0).toUpperCase() + this.slice(1);

};


$(document).ready(function () {

    pull_data();

    //tooltip
    $("body").tooltip({ selector: '[data-toggle=tooltip]', trigger: 'hover' });

    //hide button update/create
    $(".btn-create, .btn-update").css("display", "none");

    $(".create-btn-admin").on("click", function () {

        $(".create-form").trigger("reset");

        $("#username").css("cursor", "auto");
        document.getElementById('username').readOnly = false;

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
                url: "ajax/ajax_configuration_administrator.php?action=create",
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
            url: "ajax/ajax_configuration_administrator.php?action=edit_single_data",
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
    });


    //update topup button
    $(".btn-update-topup").on("click", function (e) {

        let data = $(".topup-form").serialize();

        $.ajax({
            url: "ajax/ajax_configuration_administrator.php?action=topup",
            method: "GET",
            data: data,
            success: function (data) {

                if (data['status'] === "success") {

                    $("#topupForm").modal("hide");

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


//get all latest data
function pull_data() {

    $.ajax({
        url: "ajax/ajax_configuration_administrator.php",
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
                    table_str += '<td>' + data['data'][x]['username'] + "</td>";
                    table_str += '<td>' + data['data'][x]['fullname'] + "</td>";
                    table_str += '<td>' + data['data'][x]['email'] + "</td>";
                    table_str += '<td>' + data['data'][x]['groupname'].capitalize() + "</td>";


                    if (data['data'][x]['permission'] == "r") {
                        table_str += "<td><span class=\"badge badge-warning\">Read</span></td>";
                    } else if (data['data'][x]['permission'] == "w") {
                        table_str += "<td><span class=\"badge badge-success\">Write</span></td>";
                    } else {
                        table_str += "<td><span class=\"badge badge-danger\">Read + Write</span></td>";
                    }

                    table_str += '<td>' + (data['data'][x]['monitor'] == "n" ? "<span>No</span>" : "<span>Yes</span>") + "</td>";
                    // table_str += '<td>' + data['data'][x]['balance_credit'] + "</td>";
                    table_str += "<td>" + data['data'][x]['lastlogin'] + "</td>";

                    table_str += "<td>";
                    // table_str += "<a href='javascript:void(0);' data-username='" + data['data'][x]['id'] + "' class='btn btn-icon btn-warning btn-xs mr-1 fa fa-usd btn-admin-charge'></a>";
                    table_str += "<a style='margin-right: 4px;' href='javascript:void(0);' data-username='" + data['data'][x]['id'] + "' class='btn btn-icon btn-success btn-sm fa fa-pencil btn-admin-edit'></a>";
                    table_str += "<a style='margin-right: 4px;' href='javascript:void(0);' data-username='" + data['data'][x]['id'] + "' class='btn btn-icon btn-danger btn-sm fa fa-times btn-admin-delete'></a>";
                    table_str += "<a style='margin-right: 4px;' href='javascript:void(0);' data-username='" + data['data'][x]['username'] + "' class='btn btn-icon btn-warning btn-sm fa fa-key btn-admin-reset' data-toggle='tooltip' data-original-title='Reset 2-Factor Key'></a>";
                    
                    if(data['data'][x]['user_permission'] == 'rw'){
                        if(data['data'][x]['is_active'] == 0){
                            table_str += "<button type='button' title='Unblock User' data-username='" + data['data'][x]['id'] + "' class='btn btn-icon btn-info btn-sm btn-unblock'><span class='fa fa-ban'></span></button>";
                        }
                    }
                    
                    table_str += "</td>";

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
                    "fnDrawCallback": function () {
                        if($('.dataTables_filter').find('input').hasClass('form-control-sm')){
                            $('.dataTables_filter').find('input').removeClass('form-control-sm')
                        }

                        $(".btn-admin-charge").on("click", function () {

                            topUp($(this).data("username"));

                        });


                        $(".btn-admin-edit").on("click", function () {

                            getItemForForm($(this).data("username"));

                        });


                        $(".btn-admin-delete").on("click", function () {

                            deleteItem($(this).data("username"));

                        });


                        $(".btn-admin-reset").on("click", function () {

                            resetUsernameKey($(this).data("username"));

                        });

                        $(".btn-unblock").on("click", function(){

                            let id = $(this).data('username');
                    
                            swal({
                    
                                title: "Are you sure?",
                                text: "You will not able to reverse this action.",
                                type: "warning",
                                showCancelButton: true,
                                confirmButtonText: "Yes, unblock it!",
                                cancelButtonText: "Cancel"
                        
                            }).then(function (x) {
                        
                                if (x['value'] === true) {
                        
                                    if (id > 0) {
                    
                                        $.ajax({
                                            url: "ajax/ajax_configuration_administrator.php",
                                            method: "POST",
                                            data: {
                                                "action": "unblock_user",
                                                "id": id
                                            },
                                            success: function (data) {
                                
                                                location.reload();
                                
                                            },
                                            error: function (error) {
                                
                                                swal("Error", "There is an error", "error");
                                
                                            }
                                        });
                                    }
                    
                                }
                            });
                    
                           
                        })


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
              url: "/admin/ajax/ajax_configuration_administrator.php",
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
                url: "ajax/ajax_configuration_administrator.php",
                method: "POST",
                data: {
                    "action": "delete",
                    id: id,
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
            });
        }
    });
}


//get data based on id
function getItemForForm(id) {

    if (id > 0) {

        $.ajax({
            url: "ajax/ajax_configuration_administrator.php",
            method: "GET",
            data: {
                "action": "get_update",
                "id": id
            },
            success: function (data) {

                if (data) {

                    $("form.create-form").trigger("reset");

                    $("#username").val(data['data']['username']);
                    $("#password").val(data['data']['password']);
                    $("#fullname").val(data['data']['fullname']);
                    $("#email").val(data['data']['email']);

                    if ((data['data']['monitor']) === 'y') {
                        $('#monitor').prop("checked", true);
                    } else {
                        $('#monitor').prop("checked", false);
                    }

                    $("#permission").val(data['data']['permission']);
                    $("#groupname").val(data['data']['groupname']);


                    $("#reference").val(data['data']['id']);

                    $(".btn-create, .btn-update").css("display", "none");

                    $(".btn-create").css("display", "none");
                    $(".btn-update").css("display", "block");


                    $("#inlineForm").modal();

                    $("#username").css("cursor", "not-allowed");
                    document.getElementById('username').readOnly = true;

                }

            },
            error: function (error) {

                swal("Error", "There is an error", "error");

            }
        });
    }
}

//topup button
function topUp(id) {

    if (id > 0) {

        $.ajax({
            url: "ajax/ajax_configuration_administrator.php",
            method: "GET",
            data: {
                "action": "get_update",
                "id": id
            },
            success: function (data) {

                if (data) {

                    console.log(data['data']['id']);

                    $("form.topup-form").trigger("reset");

                    // $("#balance_credit").val(data['data']['balance_credit']);
                    $("#id").val(data['data']['id']);

                    $("#topupForm").modal();

                }
            },
            error: function (error) {

                swal("Error", "There is an error", "error");

            }
        });
    }
}
