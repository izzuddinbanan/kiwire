$(document).ready(function(){


    $.ajax({
        url: "/admin/ajax/ajax_help_database_diagnostic.php",
        method: "post",
        success: function (response) {

            if (response['status'] === "success"){


                if($.fn.dataTable.isDataTable('.table-data')){

                    $(".table-data").DataTable().destroy();

                }



                let status_str = '', status_counter = 1;

                for(let kindex in response['data']){

                    status_str += "<tr><td>" + status_counter + "</td><td>" + response['data'][kindex][0] + "</td><td>" + response['data'][kindex][1] + "</td></tr>";

                    status_counter++;

                }


                $(".table-data > tbody").html(status_str);


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

                swal("Error", response['message'], "error");

            }

        },
        error: function () {

            swal("Error", "There is an error to display this page. Please try again later.", "error");

        }
    });


});