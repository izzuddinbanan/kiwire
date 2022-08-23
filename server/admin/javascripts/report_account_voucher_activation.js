$(document).ready(function () {

    pull_data();

    $('#search').on("click", pull_data);
    
    // $("#filter-btn").on("click", function () {

    //     $("#filter_modal").modal();

    // });


    // $("#filter-data").on("click", function () {


    //     pull_data();

    //     $("#filter_modal").modal("hide");


    // });

});




function pull_data() {


    if ($.fn.dataTable.isDataTable('.table-data')) {

        $(".table-data").DataTable().destroy();

    }


    table_data = $('.table-data').DataTable({
        "responsive": true,
        "processing": true,
        "serverSide": true,
        "ajax": {
            url: "ajax/ajax_report_account_voucher_activation.php?action=get_by_date",
            method: "GET",
            data: {
                "startdate": $('#startdate').val(),
                "enddate": $('#enddate').val(),
                "zone": $('#zone').val()
            },

        },

        "dom": dt_position,
        "buttons": [

            {
                text: "Download All",
                className: "btn-all-data",
                action: function () {

                    $.ajax({
                        url: "ajax/ajax_report_account_voucher_activation.php",
                        method: "POST",
                        data: {
                            "action": "get_csv",
                            "startdate": $('#startdate').val(),
                            "enddate": $('#enddate').val(),
                            "zone": $('#zone').val()
                        },
                        success: function (response) {


                            if (response['status'] === "completed") {

                                swal("Success", "Go to  'Reports > Generated Report' to get the report", "success");

                            } else {

                                swal("Error!", "We are facing difficulties to generate the report.\nPlease re-try after couple of minutes.", "error");

                            }


                        }, error: function () {

                            swal("Error!", "There is an error occured. Please let us know about this.", "error");

                        }
                    });

                }
            },
            {
                extend: 'copyHtml5',
                exportOptions: {
                    columns: [0, ':visible']
                }
            },
            {
                extend: 'csvHtml5'
            },
            {
                extend: 'pdfHtml5',
                orientation: 'landscape',
                pageSize: 'A3',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'print',
                exportOptions: {
                    columns: ':visible'
                }
            }
        ],
        language: {
            searchPlaceholder: "Search Records",
            search: "",
        },
        "fnDrawCallback": function(){
            if($('.dataTables_filter').find('input').hasClass('form-control-sm')){
                $('.dataTables_filter').find('input').removeClass('form-control-sm')
            }
        }



    });


}