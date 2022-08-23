$(document).ready(function () {

    pull_data();

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
            url: "ajax/ajax_devices_monitor_logs.php?action=get_all",
            method: "GET",
            data: {
            },
        },
        "dom": dt_position,
        "buttons": dt_btn,
        language: {
            searchPlaceholder: "Search Records",
            search: "",
        },
        "fnDrawCallback": function(){
            if($('.dataTables_filter').find('input').hasClass('form-control-sm')){
                $('.dataTables_filter').find('input').removeClass('form-control-sm')
            }
        },
        "columnDefs": [
        {
            "targets": [2],
            "render": function (data, type, row, meta) {


                status_str = row[2];

                if (status_str === "running") status_str = '<td><span class="badge badge-success">Online</span></td>';
                else status_str = '<td><span class="badge badge-danger">Offline</span></td>';

                return status_str;


            }
        },
    ]

      
    });
}