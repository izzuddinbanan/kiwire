$(document).ready(function () {

    pull_data();

    
    $('#search').on("click", pull_data);
    

    // $('#filter-btn').on("click", function() {
       
    //     $('#filter_modal').modal();
    // });

    // $('#filter-data').on("click", function() {

    //     pull_data();
       
    //     $('#filter_modal').modal("hide");

    // });
   
});


function pull_data() {

    $.ajax({
        url: "ajax/ajax_report_account_creation_analytics.php",
        method: "POST",
        data: {
            "startdate": $('input[name=startdate]').val(),
            "enddate": $('input[name=enddate]').val()
        },
        success: function (data) {

            if (data['status'] === "success") {

                if ($.fn.dataTable.isDataTable('.table-data')) {

                    $(".table-data").DataTable().destroy();

                }

                let table_str = "";

                if (data['data'].length >= 0) {

                    for (let x = 0; x < data['data'].length; x++) {

                        table_str += "<tr>";
                        table_str += "<td>" + (x + 1) + "</td>";
                        table_str += "<td>" + data['data'][x]['xreport_date'] + "</td>";
                        table_str += "<td>" + data['data'][x]['account'] + "</td>";
                        table_str += "</tr>";

                    }


                } else {

                    table_str += '<tr><td colspan="3" align="center">No data available in table</td></tr>';

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

