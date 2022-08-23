$(document).ready(function () {


    pull_data();


});


function pull_data(){


    $.ajax({
        url: "/admin/ajax/ajax_report_pms_transaction.php",
        method: "post",
        data: {
            action: "get_all"
        },
        success: function (response) {

            if (response['status'] === "success"){


                let table_html = "";

                for (let kindex in response['data']){

                    table_html += "<tr>";
                    table_html += "<td>" + (parseInt(kindex) + 1) + "</td>";
                    table_html += "<td>" + response['data'][kindex]['check_in_date'] + "</td>";

                    if (response['data'][kindex]['check_out_date'] === null){

                        table_html += "<td>N/A</td>";

                    } else {

                        table_html += "<td>" + response['data'][kindex]['check_out_date'] + "</td>";

                    }

                    table_html += "<td>" + response['data'][kindex]['room'] + "</td>";
                    table_html += "<td>" + response['data'][kindex]['first_name'] + "</td>";
                    table_html += "<td>" + response['data'][kindex]['last_name'] + "</td>";
                    table_html += "<td>" + response['data'][kindex]['vip_code'] + "</td>";



                    if (['check-in', 'move-in'].includes(response['data'][kindex]['status'])) {

                        table_html += "<td><span class='badge badge-primary'>" + response['data'][kindex]['status'] + "</span></td>";

                    } else if (response['data'][kindex]['status'] === "db-sync") {

                        table_html += "<td><span class='badge badge-warning'>" + response['data'][kindex]['status'] + "</span></td>";

                    } else {

                        table_html += "<td><span class='badge badge-success'>" + response['data'][kindex]['status'] + "</span></td>";

                    }


                    table_html += "</tr>";

                }


                $("table.table-data > tbody").html(table_html);

                $(".table-data").DataTable({
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
        error: function (response) {

            swal("Error", "There is an internal error. Please try again.", "error");

        }
    });



}