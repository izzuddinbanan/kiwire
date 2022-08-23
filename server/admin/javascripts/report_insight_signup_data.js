$(document).ready(function () {

    pull_data();

    $('#search').on("click", pull_data);

});


function pull_data() {

    $.ajax({
        url: "ajax/ajax_report_insight_signup_data.php?action=get_by_date",
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

                        var createDate = new Date(data['data'][x]['updated_date']);

                        var date = createDate.getDate();
                        var month = createDate.getMonth();
                        var year = createDate.getFullYear();

                        var hour = createDate.getHours();
                        var minute = createDate.getMinutes();
                        var second = createDate.getSeconds();

                        var dateFormat = ("0" + date).slice(-2) + "-" + ("0" + (month + 1)).slice(-2) + "-" + year + " " + ("0" + hour).slice(-2) + ":" + ("0" + minute).slice(-2) + ":" + ("0" + second).slice(-2);

                        table_str += '<td>' + dateFormat + "</td>";
                        table_str += "<td>" + data['data'][x]['username'] + "</td>";
                        table_str += "<td>" + data['data'][x]['email_address'] + "</td>";
                        table_str += "<td>" + data['data'][x]['age_group'] + "</td>";
                        table_str += "<td>" + data['data'][x]['gender'] + "</td>";

                        table_str += "</tr>";

                    }
                } else {

                    table_str += '<tr><td colspan="12" align="center">No data available in table</td></tr>';
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