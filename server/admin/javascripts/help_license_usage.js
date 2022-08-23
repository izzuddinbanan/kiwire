$(document).ready(function () {

    pull_data();

});


function pull_data() {

    $.ajax({
        url: "ajax/ajax_help_license_usage.php",
        method: "post",
        success: function (data) {

            if (data['status'] === "success") {

                if ($.fn.dataTable.isDataTable('.table-data')) {

                    $(".table-data").DataTable().destroy();

                }


                let table_str = "";
                let kcounter = 1;

                for (let kindex in data['data']) {

                    table_str += "<tr>";
                    table_str += "<td>" + kcounter + "</td>";
                    table_str += "<td>" + data['data'][kindex]['name'] + "</td>";
                    table_str += "<td>" + data['data'][kindex]['type'] + "</td>";
                    table_str += "<td>" + (data['data'][kindex]['status'] === "Expired" ? "<span class='badge badge-danger'>Expired</span>" : "<span class='badge badge-success'>Active</span>") + "</td>";
                    table_str += "<td>" + Date.parse(data['data'][kindex]['expire']).toString("dd-MMM-yyyy") + "</td>";
                    table_str += "<td>" + data['data'][kindex]['limit'] + "</td>";
                    table_str += "<td>" + data['data'][kindex]['current'] + "</td>";
                    table_str += "<td>" + data['data'][kindex]['percent'] + "</td>";
                    table_str += "</tr>";

                    kcounter++;

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

    });

}