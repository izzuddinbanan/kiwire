$(document).ready(function () {

    pull_data();


});


function pull_data() {

    $.ajax({
        url: "ajax/ajax_report_logs_email_delivery_sum.php",
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

                    table_str += "<td>" + data['data'][x]['cdate'] + "</td>";
                    table_str += "<td>" + data['data'][x]['sdate'] + "</td>";

                    if (data['data'][x]['status'] == "y") {
                        table_str += "<td><span class=\"badge badge-success\">Success</span></td>";                 
                    } else {
                        table_str += "<td><span class=\"badge badge-danger\">Failed</span></td>";
                    }
                  
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