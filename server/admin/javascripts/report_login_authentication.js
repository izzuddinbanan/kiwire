var mini_column = [3, 4, 6, 7, 8, 10, 11, 14, 15]
var full_column = [];
var show = true;

$(document).ready(function () {

    pull_data();

    $('#search').on("click", pull_data);
});


var table_data = null;

function pull_data() {

    if ($.fn.dataTable.isDataTable('.table-data')) {

        $(".table-data").DataTable().destroy();

    }

    // var mini_column, full_column, show;


    

    // var mini_column = [3, 4, 6, 7, 8, 10, 11, 14, 15];
    // var full_column = [];

    // var show = true;


    table_data = $('.table-data').DataTable({
        "responsive": true,
        "processing": true,
        "serverSide": true,
        "ajax": {
            url: "ajax/ajax_report_login_authentication.php",
            method: "get",
            data: {
                "startdate": $('#startdate').val(),
                "enddate": $('#enddate').val(),
                "username": $('#username').val(),
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
        },
    });
}

