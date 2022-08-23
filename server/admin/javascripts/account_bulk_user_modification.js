$(document).ready(function () {


    pull_data();


    $(".btn-select-all").on("click", function () {

        $("input[type=checkbox]").prop("checked", true);

    });


    $(".btn-clear-all").on("click", function () {

        $("input[type=checkbox]").prop("checked", false);

    });


    $(".btn-extend-selected").on("click", function(){


        if ($("input[name=expiry]").val() === ""){

            swal("Error", "Please select an expiry date", "error"); return;

        }


        let data = $("form").serialize();


        $.ajax({
            url: "/admin/ajax/ajax_account_bulk_user_modification.php?action=extend",
            method: "post",
            data: data,
            success: function (response) {

                if (response['status'] === "success"){


                    table_data.ajax.reload();

                    swal("Success", response['message'], "success");


                } else {

                    swal("Error", response['message'], "error");

                }
            },
            error: function (response) {

                swal("Error", "There is an expected error. Please try again.", "error");

            }
        });



    });



    $(".btn-change-profile").on("click", function(){


        if ($("select[name=profile]").val() === "none"){

            swal("Error", "Please select a profile", "error"); return;

        }


        let data = $("form").serialize();


        $.ajax({
            url: "/admin/ajax/ajax_account_bulk_user_modification.php?action=change",
            method: "post",
            data: data,
            success: function (response) {

                if (response['status'] === "success"){


                    table_data.ajax.reload();

                    swal("Success", response['message'], "success");


                } else {

                    swal("Error", response['message'], "error");

                }
            },
            error: function (response) {

                swal("Error", "There is an expected error. Please try again.", "error");

            }
        });



    });


    $(".btn-reset-account").on("click", function(){

        let data = $("form").serialize();


        $.ajax({
            url: "/admin/ajax/ajax_account_bulk_user_modification.php?action=reset",
            method: "post",
            data: data,
            success: function (response) {

                if (response['status'] === "success"){

                    swal("Success", response['message'], "success");

                } else {

                    swal("Error", response['message'], "error");

                }
            },
            error: function (response) {

                swal("Error", "There is an expected error. Please try again.", "error");

            }
        });



    });


    $(".btn-delete-user").on("click", function(){

        let data = $("form").serialize();

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
                    url: "ajax/ajax_account_bulk_user_modification.php?action=delete",
                    method: "POST",
                    data: data,

                    success: function (data) {

                        if (data['status'] === "success") {

                            table_data.ajax.reload();

                            toastr.info("Success", data['message'], "success");

                        } else {

                            toastr.info("Error", data['message'], "error");

                        }

                    },

                });

            }

        });

    });


    $(".btn-export").on("click", function(){

        let data = $("form").serialize();


        $.ajax({
            url: "/admin/ajax/ajax_account_bulk_user_modification.php?action=export",
            method: "post",
            data: data,
            success: function (response) {

                if (response['status'] === "success"){


                    window.location.href = response['message'];


                } else {

                    swal("Error", response['message'], "error");

                }
            },
            error: function (response) {

                swal("Error", "There is an expected error. Please try again.", "error");

            }
        });


    });


});


var table_data = null;


//get all latest data

function pull_data() {


    table_data = $('.table-data').DataTable({
        "responsive": true,
        "processing": true,
        "serverSide": true,
        "ajax": {
            url: "ajax/ajax_account_bulk_user_modification.php?action=get_all",
            method: "get"
        },
        "dom": dt_position,
        "buttons": dt_btn,
        language: {
            searchPlaceholder: "Search Records",
            search: "",
        },
        "fnDrawCallback": function() {
            if($('.dataTables_filter').find('input').hasClass('form-control-sm')){
                $('.dataTables_filter').find('input').removeClass('form-control-sm')
            }


        },
        "columnDefs": [
            {
                "targets": [3],
                "render": function ( data, type, row, meta ) {

                    return Date.parse(row[3]).toString("d-MMM-yyyy");;

                }
            },
            {
                "targets": [4],
                "render": function ( data, type, row, meta ) {

                    let btn_str = "";

                    btn_str += "<div class='custom-control custom-checkbox'>";
                    btn_str += "<input type='checkbox' class='custom-control-input selection-input' name='username[]' id='" + row[1] + "' value='" + row[1] + "'>";
                    btn_str += "<label for='" + row[1] + "' class='custom-control-label'>&nbsp;</label>";
                    btn_str += "</div>";

                    return btn_str;

                }
            }
        ]
    });


}
