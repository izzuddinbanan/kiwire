var mini_column = [3, 4, 6, 7, 8, 10, 11, 14, 15]
var full_column = [];
var show = true;

$(document).ready(function () {

    pull_data();

});


var table_data = null;

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
            url: "ajax/ajax_report_login_auto_login.php",
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

            $(".btn-login-remove").off().on("click", function () {

                let device_id = $(this);

                Swal.fire({

                    // input: 'select',
                    // inputOptions: profile_deletion,
                    title: "CONFIRM DELETION?",
                    text: "Are you sure to remove auto login for this device [ "+ device_id.data("mac") +" ] ?",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, remove it!",
                    cancelButtonText: "Cancel"
            
                }).then((result) => {
            
            
                    if (result['value'] !== undefined) {
            
            
                        $.ajax({
                            url: "ajax/ajax_report_login_auto_login.php",
                            method: "POST",
                            data: {
                                "action": "delete",
                                "id": device_id.data("device"),
                            },
                            success: function (response) {
            
                                if (response['status'] === "success") {
            
            
                                    swal("Success", response['message'], "success");
            
                                    table_data.ajax.reload();
            
            
                                } else {
            
                                    swal("Error", response['message'], "error");
            
                                }
                            },
                            error: function (response) {
            
                                swal("Error", "There is unexpected error. Please try again.", "error");
            
                            }
                        });
            
            
                    }
            
            
                });
            

            });


        },
        "columnDefs": [
            {
                "targets": [8],
                "visible": false
            },
            {
                "targets": [1],
                "render": function (data, type, row, meta) {

                    return "<td>" + "<a href='javascript:void(0)' class='btn btn-danger btn-icon btn-sm fa fa-times btn-login-remove' data-device = '" + row[8] + "' data-mac = '" + row[2] + "'></a> &nbsp;" + "</td>";

                }

            },
            {
                "targets": [5],
                "render": function (data, type, row, meta) {


                    class_icon = row[5];

                    if (class_icon === "Smartphone") class_icon = '<td><span class="fa fa-mobile fa-3x"></span></td>';
                    else if (class_icon === "Tablet") class_icon = '<td><span class="fa fa-tablet fa-3x"></span></td>';
                    else class_icon = '<td><span class="fa fa-desktop fa-2x"></span></td>';

                    return class_icon;


                }
            }
        ]
    });
}

