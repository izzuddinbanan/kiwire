$(document).ready(function () {

    pull_data();

    $('#search').on("click", pull_data);

});


function pull_data() {

    if ($.fn.dataTable.isDataTable('.table-data')) {

        $(".table-data").DataTable().destroy();

    }


    table_data = $('.table-data').DataTable({
        "responsive": true,
        "processing": true,
        "serverSide": true,
        "deferRender": true,
        "ajax": {
            url: "ajax/ajax_report_login_scanner.php?action=get_by_date",
            method: "get",
            data: {
                "startdate": $('#startdate').val(),
                "enddate": $('#enddate').val(),
                "type": $('#type').val(),
                "username": $('#username').val(),
                "ip_address": $('#ip_address').val(),
                "tenant_id": $('#tenant_id').val(),
                "severity": $('#severity').val()
            },
            
        },
        "dom": dt_position,
        "buttons": dt_btn,
        "language": {
            searchPlaceholder: "Search Records",
            search: "",
            infoFiltered: ""
        },
        "fnDrawCallback": function () {
            if($('.dataTables_filter').find('input').hasClass('form-control-sm')){
                $('.dataTables_filter').find('input').removeClass('form-control-sm')
            }

        },
        "columnDefs": [
            {
                "targets": [2],
                "render": function (data, type, row, meta) {

                    let severe = row[2];

                    status_str = "-";
                    if (severe === "low")                   status_str = '<td><span class="badge badge-info">Low</span></td>';
                    else if (severe === "informational")    status_str = '<td><span class="badge badge-info">Informational</span></td>';
                    else if (severe === "medium")           status_str = '<td><span class="badge badge-warning">Medium</span></td>';
                    else if (severe === "high")             status_str = '<td><span class="badge badge-danger">High</span></td>';
                    else if (severe === "critical")         status_str = '<td><span class="badge badge-danger">Critical</span></td>';

                    return status_str;


                }
            },
        ]
    });




}