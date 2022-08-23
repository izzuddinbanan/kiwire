$(document).ready(function () {

    pull_data();

    $('#search').on("click", pull_data);

});


function pull_data() {

    $.ajax({
        url: "ajax/ajax_report_account_inactive_summary.php?action=get_by_date",
        method: "POST",
        data: {
            "startdate": $('#startdate').val(),
            "enddate": $('#enddate').val()
        },
        success: function (data) {

            if (data['status'] === "success") {

                if ($.fn.dataTable.isDataTable('.table-data')) {

                    $(".table-data").DataTable().destroy();

                }

                let table_str = "";

                if (data['data'].length > 0) {

                    for (let x = 0; x < data['data'].length; x++) {

                        table_str += "<tr>";

                        table_str += "<td>" + (x + 1) + "</td>";
                        table_str += "<td>" + data['data'][x]['username'] + "</td>";
                        table_str += "<td>" + data['data'][x]['profile_subs'] + "</td>";

                        var expiryDate = new Date(data['data'][x]['date_expiry']);

                        var date = expiryDate.getDate();
                        var month = expiryDate.getMonth();
                        var year = expiryDate.getFullYear();

                        var dateString = ("0" + date).slice(-2) + "-" + (month + 1) + "-" + year;

                        table_str += '<td>' + dateString + "</td>";


                        table_str += "</tr>";
                    }

                } else {
                    table_str += '<tr><td colspan="4" align="center">No data available in table</td></tr>';
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