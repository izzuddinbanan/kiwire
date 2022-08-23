$(document).ready(function () {

    pull_data();

});


function pull_data() {

    $.ajax({
        url: "ajax/ajax_finance_voucher_slip.php",
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

                    table_str += "<td>" + data['data'][x]['creator'] + "</td>";
                    table_str += "<td>" + data['data'][x]['qty'] + "</td>";
                    table_str += "<td>" + data['data'][x]['remark'] + "</td>";

                    table_str += '<td>' + Date.parse(data['data'][x]['date_create']).toString("d-MMM-yyyy") + "</td>";
                    table_str += '<td>' + Date.parse(data['data'][x]['date_expiry']).toString("d-MMM-yyyy") + "</td>";

                    table_str += "<td><a data-voucher-id='" + data['data'][x]['bulk_id'] + "' class='btn btn-icon btn-primary white btn-xs fa fa-print btn-printone'></a></td>";
                    table_str += "<td><a data-voucher-id='" + data['data'][x]['bulk_id'] + "' class='btn btn-icon btn-primary white btn-xs fa fa-print btn-printtwo'></a></td>";
                    table_str += "<td><a data-voucher-id='" + data['data'][x]['bulk_id'] + "' class='btn btn-icon btn-primary white btn-xs fa fa-print btn-printpos'></a></td>";

                    table_str += "<td><a data-voucher-id='" + data['data'][x]['bulk_id'] + "' class='btn btn-icon btn-primary white btn-xs fa fa-qrcode btn-printqr'></a></td>";
                    table_str += "<td><a data-voucher-id='" + data['data'][x]['bulk_id'] + "' class='btn btn-icon btn-primary white btn-xs fa fa-share-square-o btn-export'></a></td>";


                    table_str += "</tr>";
                }

                $(".table-data > tbody").html(table_str);

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

                            window.location.href = "/admin/finance_print_voucher.php?format=one&voucher=" + $(this).data("voucher-id");

                        });


                        $(".btn-printtwo").off().on("click", function(){

                            window.location.href = "/admin/finance_print_voucher.php?format=two&voucher=" + $(this).data("voucher-id");

                        });


                        $(".btn-printpos").off().on("click", function(){

                            window.location.href = "/admin/finance_print_voucher.php?format=pos&voucher=" + $(this).data("voucher-id");

                        });


                        $(".btn-printqr").off().on("click", function(){

                            window.location.href = "/admin/finance_print_voucher.php?format=qr&voucher=" + $(this).data("voucher-id");

                        });


                        $(".btn-export").off().on("click", function(){

                            window.location.href = "/admin/finance_voucher_export.php?voucher=" + $(this).data("voucher-id");

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

