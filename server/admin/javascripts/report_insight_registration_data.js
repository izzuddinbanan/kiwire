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


var table_data = null;


function pull_data() {


    if ($.fn.dataTable.isDataTable('.table-data')) {

        $(".table-data").DataTable().destroy();

    }


    table_data = $(".table-data").dataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: "/admin/ajax/ajax_report_insight_registration_data.php",
            method: "get",
            data: {
                "start_date": $("#startdate").val(),
                "end_date": $("#enddate").val(),
                "columns_registered": columns_available
            }
        },
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


}