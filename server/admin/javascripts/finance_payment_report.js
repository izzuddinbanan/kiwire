$(document).ready(function () {

    pull_data();

    $('#search').on("click", function () {

        pull_data();

    })

});




function pull_data() {


    $.ajax({
        url: "ajax/ajax_finance_payment_report.php",
        method: "POST",
        data: {
            "startdate": $('#startdate').val(),
            "enddate": $('#enddate').val(),
            "payment_type": $('#payment_type').val(),
            "action": "calculate_total"
        },
     

        success: function (data) {

            if (data['status'] === "success") {


                $("#total-transaction").html(data['data']['total_transaction']);

                $("#total-amount").html(data['data']['total_amount']);


            } else {

                swal("Error", data['message'], "error");

            }

        },


        error: function () {

            swal("Error", "There is unexpected error. Please try again.", "error");

        }

    })




    $.ajax({
        url: "ajax/ajax_finance_payment_report.php",
        method: "POST",
        data: {
            "startdate": $('#startdate').val(),
            "enddate": $('#enddate').val(),
            "payment_type": $('#payment_type').val(),
            "action": "get_by_date"
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
                        table_str += "<td>" + data['data'][x]['xreport_date'] + "</td>";
                        table_str += "<td>" + data['data'][x]['ref_no'] + "</td>";
                        table_str += "<td>" + data['data'][x]['username'] + "</td>";
                        table_str += "<td>" + data['data'][x]['user_name'] + "</td>";
                        table_str += "<td>" + data['data'][x]['user_email'] + "</td>";
                        table_str += "<td>" + data['data'][x]['user_phone_no'] + "</td>";
                        table_str += "<td>" + data['data'][x]['amount'] + "</td>";
                     
                        if (data['data'][x]['status'] == "settlement" || data['data'][x]['status'] == "capture") {

                            table_str += "<td><span class=\"badge badge-success\">Success</span></td>";
                        
                        } else {
                            
                            table_str += "<td><span class=\"badge badge-danger\">Pending</span></td>";
                        
                        }

                        if(data['data'][x]['status'] == "settlement") {

                            // table_str += "<td><a href='javascript:void(0)' title='Download Invoice' class='btn btn-success btn-icon btn-sm fa fa-download btn-report-download'></a></td>";
                            table_str += "<td><a data-payment-id='" + data['data'][x]['id'] + "' title='Print Invoice' class='btn btn-icon btn-primary white btn-xs fa fa-print btn-printone'></a></td>";
                        
                        } else {

                            table_str += "<td></td>";

                        }

                        table_str += "</tr>";

                        
                    }


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


                        $(".btn-printone").off().on("click", function(){

                            window.location.href = "/admin/finance_payment_invoice.php?id=" + $(this).data("payment-id");

                        });

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

