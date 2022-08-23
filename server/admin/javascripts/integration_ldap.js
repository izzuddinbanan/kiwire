$(document).ready(function () {

    pull_data();


       
    $('.datetime').timepicker({
        timeFormat: 'HH:mm:ss',
        dropdown: true,
        scrollbar: true
    });


    // $('#is_24').on('change', function(){

    //     if($(this).is(':checked')){
    //         $('.time').hide();
    //     }else{
    //         $('.time').show();
    //     }
    // })

    
    $("#main-tab").on("click", function () {

        $(".save-button").css("display", "block");
        $(".create-btn-ldap").css("display", "none");

    });

    $("#mapping-tab").on("click", function () {

        $(".save-button").css("display", "none");
        $(".create-btn-ldap").css("display", "block");

    });

    $(".btn-create, .btn-update").css("display", "none");


    $(".create-btn-ldap").on("click", function () {

        $(".btn-create, .btn-update").css("display", "none");

        $(".btn-create").css("display", "block");

        $("#inlineForm").modal();

    });


    $(".cancel-button").on("click", function (e) {

        $(".create-form").trigger("reset");

        $("#inlineForm").modal("hide");

    });


    $("button.save-button").on("click", function (e) {

        let data = $("form").serialize();

        $.ajax({
            url: "ajax/ajax_integration_ldap.php?action=update",
            method: "POST",
            data: data,
            success: function (data) {

                if (data['status'] === "success") {

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


    $(".btn-create").on("click", function (e) {

        let create_form = $(".create-form");

        if (create_form.parsley()) {

            let data = create_form.serialize();

            $.ajax({
                url: "ajax/ajax_integration_ldap.php?action=create",
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
            url: "ajax/ajax_integration_ldap.php?action=edit_single_data",
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
    });


    $(".btn-test").on("click", function(){


        $("#test-modal").modal();


    });


    $(".btn-test-ldap").on("click", function () {

        $.ajax({
            url: "/admin/ajax/ajax_integration_ldap.php?action=test",
            method: "post",
            data: {
                username: $("input[name=username]").val(),
                password: $("input[name=password]").val(),
                host: $("input[name=host]").val(),
                port: $("input[name=port]").val(),
                rdn: $("input[name=rdn]").val(),
            },
            success: function (response) {

                if (response['status'] === "success") {

                    swal("Success", response['message'], "success");

                } else {

                    swal("Error", response['message'], "error");

                }

            },
            error: function () {

                swal("Error", "There is unexpected error. Please try again.", "error");

            }
        });


    });


});


function pull_data() {

    $.ajax({
        url: "ajax/ajax_integration_ldap.php",
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
                    table_str += "<td>" + data['data'][x]['group_name'] + "</td>";
                    table_str += "<td>" + data['data'][x]['profile'] + "</td>";
                    table_str += "<td>" + data['data'][x]['allowed_zone'] + "</td>";
                    table_str += "<td>" + data['data'][x]['priority'] + "</td>";

                    if (data['data'][x]['status'] === "y") {
                        table_str += "<td><span class=\"badge badge-success\">Active</span></td>";
                    } else {
                        table_str += "<td><span class=\"badge badge-danger\">Disabled</span></td>";
                    }

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
                url: "ajax/ajax_integration_ldap.php",
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
            url: "ajax/ajax_integration_ldap.php",
            method: "GET",
            data: {
                "action": "get_update",
                "id": id
            },
            success: function (data) {

                if (data) {


                    $("form.create-form").trigger("reset");

                    $("#group_name").val(data['data']['group_name']);
                    $("#profile").val(data['data']['profile']);
                    $("#allowed_zone").val(data['data']['allowed_zone']);
                    $("#priority").val(data['data']['priority']);

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
