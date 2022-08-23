
$(document).ready(function () {

    pull_data();

    $('#search').on("click", pull_data);

    // $('#filter-btn').on("click", function () {
    
    //     $('#filter_modal').modal();
    // });

    // $('#filter-data').on("click", function () {

    //     pull_data();

    //     $('#filter_modal').modal("hide");
    // });

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
            url: "ajax/ajax_report_account_expiry.php?action=get_by_date",
            method: "GET",
            data: {
                "startdate": $('#startdate').val(),
                "enddate": $('#enddate').val()
            },
        },
        "dom": dt_position,
        "buttons":dt_btn,
        language: {
            searchPlaceholder: "Search Records",
            search: "",
        },
        "fnDrawCallback": function(){
            if($('.dataTables_filter').find('input').hasClass('form-control-sm')){
                $('.dataTables_filter').find('input').removeClass('form-control-sm')
            }
        }


      
    });
}