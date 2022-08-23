$(document).ready(function () {

    pull_data();

});


function pull_data() {

    $.ajax({
        url: "ajax/ajax_help_database_disk_usage.php",
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

                    // table_str += '<td>' + (x + 1) + "</td>";
                    table_str += '<td>' + data['data'][x]['stats'] + "</td>";
                    table_str += '<td>' + data['data'][x]['data'] + "</td>";
                    table_str += '<td>' + data['data'][x]['index'] + "</td>";
                    table_str += '<td>' + data['data'][x]['total'] + "</td>";

                    table_str += '</tr>';
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
