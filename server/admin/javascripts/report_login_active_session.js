var mini_column = [6, 7, 8, 10, 11, 13, 14, 15]
var full_column = [];
var show = true;

$(document).ready(function () {

    pull_data();

    $('#customSwitch1').click(function () {


        if ($(this).prop("checked")) { //if true checked

            table_data.destroy();
            mini_column = [6, 7, 8, 10, 11, 13, 14, 15];
            show = true;
            pull_data()


        } else {

            table_data.destroy();
            mini_column = [];
            show = false;
            pull_data()

        }


    });



});


var table_data = null;

// $('#customSwitch1').click(function () {

//     if ($(this).val() == "mini_column") {

//         mini_column = [3, 4, 6, 7, 8, 10, 11, 14, 15];
//         show = true;


//     } else {

//         full_column = [];
//         show = false;


//     }


// });

function pull_data() {


    // var mini_column, full_column, show;


    

    // var mini_column = [3, 4, 6, 7, 8, 10, 11, 14, 15];
    // var full_column = [];

    // var show = true;


    table_data = $('.table-data').DataTable({
        "responsive": true,
        "processing": true,
        "serverSide": true,
        "ajax": {
            url: "ajax/ajax_report_login_active_session.php",
            method: "get",
            data: {
                "action": "get_all"
            }
        },


        "dom": dt_position,
        "buttons":dt_btn,
        language: {
            searchPlaceholder: "Search Records",
            search: "",
        },
        "fnDrawCallback": function () {
            if($('.dataTables_filter').find('input').hasClass('form-control-sm')){
                $('.dataTables_filter').find('input').removeClass('form-control-sm')
            }

        $(".btn-disconnect").off().on("click", function () {

            let device_id = $(this);

            swal({

                title: "Are you sure?",
                text: "You will not able to reverse this action.",
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, Disconnect it!",
                cancelButtonText: "Cancel"
        
            }).then(function(x){ 

                if (x['value'] === true) {
                
                    $.ajax({
                        url: "/admin/ajax/ajax_report_login_active_session.php",
                        method: "post",
                        data: {
                            action: "disconnect",
                            device: device_id.data("device"),
                            tenant: device_id.data("tenant")
                        },
                        success: function (response) {
    
                            if (response['status'] === "success") {
    
    
                                table_data.ajax.reload();
    
                                swal("Success", response['message'], "success");
    
    
                            } else {
    
                                swal("Error", response['message'], "error");
    
                            }
    
                        },
                        error: function () {
    
                            swal("Error", "There is unexpected error. Please retry.", "error");
    
                        }
                    });

                }
            });

        });

        $(".btn-coa").off().on("click", function () {

            let device_id = $(this);

            swal({

                title: "Are you sure?",
                text: "You will not able to reverse this action.",
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, Do it!",
                cancelButtonText: "Cancel"
        
            }).then(function(x){ 

                if (x['value'] === true) {
                
                    $.ajax({
                        url: "/admin/ajax/ajax_report_login_active_session.php",
                        method: "post",
                        data: {
                            action: "coa",
                            username: device_id.data("username"),
                            profile: device_id.data("profile"),
                            tenant: device_id.data("tenant"),
                        },
                        success: function (response) {
    
                            if (response['status'] === "success") {
    
    
                                table_data.ajax.reload();
    
                                swal("Success", response['message'], "success");
    
    
                            } else {
    
                                swal("Error", response['message'], "error");
    
                            }
    
                        },
                        error: function () {
    
                            swal("Error", "There is unexpected error. Please retry.", "error");
    
                        }
                    });

                }
            });

        });


        },
        "columnDefs": [
            {
                "targets": show ? mini_column : full_column,
                "visible": false
            },
            {
                "targets": [1],
                "render": function (data, type, row, meta) {

                    return "<td>" + 
                            "<a href='javascript:void(0)' title='Disconnect' class='btn btn-danger btn-icon btn-sm fa fa-times btn-disconnect' data-device = '" + row[4] + "' data-tenant = '" + row[16] + "'></a> &nbsp;" +
                            "<a href='javascript:void(0)' title='COA' class='btn btn-info btn-icon btn-sm fa fa-refresh btn-coa' data-username = '" + row[3] + "' data-tenant = '" + row[16] + "' data-profile='"+ row[17] +"' ></a> &nbsp;" +
                            "</td>";

                }

            },
            {
                "targets": [12],
                "render": function (data, type, row, meta) {


                    class_icon = row[12];

                    if (class_icon === "Smartphone") class_icon = '<td><span class="fa fa-mobile fa-3x"></span></td>';
                    else if (class_icon === "Tablet" || class_icon === "Phablet" ) class_icon = '<td><span class="fa fa-tablet fa-3x"></span></td>';
                    else class_icon = '<td><span class="fa fa-desktop fa-2x"></span></td>';

                    return class_icon;


                }
            }
        ]
    });

}

