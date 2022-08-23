$(document).ready(function () {

    pull_data();

});


var table_data = null;

function pull_data() {

    if ($.fn.dataTable.isDataTable('.table-data')) {
        
        $(".table-data").DataTable().destroy();
        
    }

    table_data = $('.table-data').DataTable({
        "responsive": true,
        // "processing": true,
        // "serverSide": true,
        "ajax": {
            url: "ajax/ajax_report_generated.php",
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
            
            $(".btn-report-remove").off().on("click", function () {

                let file = $(this);

                Swal.fire({

                    // input: 'select',
                    // inputOptions: profile_deletion,
                    title: "CONFIRM DELETION?",
                    text: "Are you sure to remove this reporting [ "+ file.data("file") +" ] ?",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, remove it!",
                    cancelButtonText: "Cancel"
            
                }).then((result) => {
            
            
                    if (result['value'] !== undefined) {
            
            
                        $.ajax({
                            url: "ajax/ajax_report_generated.php",
                            method: "POST",
                            data: {
                                "action": "delete",
                                "filename": file.data("file"),
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

            $(".btn-report-download").off().on("click", function () {

                let file = $(this);

                $.ajax({
                    url: "ajax/ajax_report_generated.php",
                    method: "POST",
                    data: {
                        "action": "download",
                        "filename": file.data("file"),
                    },
                    success: function (response) {
    
                        if (response['status'] === "success") {
    
    
                            window.location.href = response['url'];
    
    
                        } 
                    },
                    error: function (response) {
    
                        swal("Error", "There is unexpected error. Please try again.", "error");
    
                    }
                });
            });

        },
        "columnDefs": [
            {
                "targets": [3],
                "render": function (data, type, row, meta) {

                    var filename, btn;

                    if(row[2] == "Completed") filename = row[1] + ".csv";
                    else filename = row[1] + ".log";

                    btn = "<td>";

                    if(row[2] == "Completed"){
                        btn += "<a href='javascript:void(0)' title='Download File' class='btn btn-success btn-icon btn-sm fa fa-download btn-report-download' data-file = '" + filename + " '></a> &nbsp;"
                    }

                    btn +=  "<a href='javascript:void(0)' title='Remove File' class='btn btn-danger btn-icon btn-sm fa fa-times btn-report-remove' data-file = '" + filename + " '></a> &nbsp;" + 
                    "</td>";

                    
                    return  btn

                }

            }
        ]
    });
}

