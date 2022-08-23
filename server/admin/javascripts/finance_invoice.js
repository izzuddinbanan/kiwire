$(document).ready(function () {

    pull_data();

    //update pay button
    $(".btn-update-pay").on("click", function (e) {

        let data = $(".pay-form").serialize();

        $.ajax({
            url: "ajax/ajax_finance_invoice.php?action=pay",
            method: "GET",
            data: data,
            success: function (data) {

                if (data['status'] === "success") {

                    $("#payForm").modal("hide");

                    pull_data();

                    swal("Success", data['message'], "success");

                } else {

                    swal("Error", data['message'], "error");

                }
            },
            error: function (data) {

                swal("Error", "There is unexpected error. Please try again.", "error");

            }

        })

    });

});


function pull_data() {

    $.ajax({
        url: "ajax/ajax_finance_invoice.php",
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

                    table_str += "<td>" + (x + 1) + "</td>";

                    table_str += "<td>" + data['data'][x]['reference'] + "</td>";
                    table_str += "<td>" + data['data'][x]['updated_date'] + "</td>";
                    table_str += "<td>" + data['data'][x]['username'] + "</td>";
                    table_str += "<td>" + data['data'][x]['profile'] + "</td>";
                    
                    table_str += "<td>" + data['data'][x]['balance'] + "</td>";


                    if (data['data'][x]['balance'] == '0.00') {
                        table_str += "<td><span class=\"badge badge-primary\">PAID</span></td>";
                    } else if (data['data'][x]['total_paid'] == '0.00') {
                        table_str += "<td><span class=\"badge badge-danger\">UNPAID</span></td>";
                    } else {
                        table_str += "<td><span class=\"badge badge-success\">PARTIALLY PAID</span></td>";
                    }


                    table_str += "<td><a href=\"javascript:void(0);\" onclick=\"pay('" + data['data'][x]['id'] + "')\" class='btn btn-icon btn-warning btn-xs mr-1 fa fa-usd'></a><a data-invoice-id='" + data['data'][x]['id'] + "' class='btn btn-icon btn-primary white btn-xs fa fa-print btn-print-invoice'></a></td>";


                    table_str += "</tr>";
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
                        
                        $(".btn-print-invoice").off().on("click", function(){

                            window.location.href = "/admin/finance_print_invoice.php?format=one&invoice=" + $(this).data("id");

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



//pay button
function pay(id) {

    if (id > 0) {

        $.ajax({
            url: "ajax/ajax_finance_invoice.php",
            method: "GET",
            data: {
                "action": "get_update",
                "id": id
            },
            success: function (data) {

                if (data) {

                    $("form.pay-form").trigger("reset");

                    $("#totalpay").val(data['data']['balance']);
                    $("#id").val(data['data']['id']);

                    $("#payForm").modal();

                }
            },
            error: function (error) {

                swal("Error", "There is an error", "error");

            }

        });

    }
    
}
