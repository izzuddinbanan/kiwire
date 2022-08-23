$(document).ready(function () {

    pull_data();

    $("body").tooltip({selector: '[data-toggle=tooltip]', trigger: 'hover'});

});


function pull_data() {

    $.ajax({
        url: "ajax/ajax_finance_voucher_summary.php",
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
                    table_str += "<td>" + data['data'][x]['bulk_id'] + "</td>";
                    table_str += "<td>" + data['data'][x]['creator'] + "</td>";
                    table_str += "<td>" + data['data'][x]['remark'] + "</td>";

                    table_str += "<td>" + data['data'][x]['price'] + "</td>";
                    table_str += "<td>" + data['data'][x]['qty'] + "</td>";

                    table_str += "<td>" + data['data'][x]['total'] + "</td>";

                    table_str += "<td><a href='#' data-bulk_id='" + data['data'][x]['bulk_id'] + "' class='btn btn-icon btn-warning btn-xs mr-1 fa fa-search btn-view'></a><a href='#' data-bulk_id='" + data['data'][x]['bulk_id'] + "' class='btn btn-icon btn-danger btn-xs mr-1 fa fa-times btn-delete'></a></td>";

                    table_str += "</tr>";


                }

                $(".table-data>tbody").html(table_str);

                $("table.table-data").dataTable({
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

                        $(".btn-view").off().on("click", function(){


                            let bulk_id = $(this).data("bulk_id");

                            voucherSummary(bulk_id);


                        });


                        $(".btn-delete").off().on("click", function () {


                            let bulk_id = $(this).data("bulk_id");

                            deleteItem(bulk_id);


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


function deleteItem(id) {

    swal({

        title: "Are you sure?",
        text: "You will not able to reverse this action.",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel"


    }).then(function (x) {

        if (x['value'] === true) {

            $.ajax({
                url: "ajax/ajax_finance_voucher_summary.php",
                method: "POST",
                data: {
                    "action": "delete",
                    id: id,
                    "token": $("input[name=token]").val()
                },

                success: function (data) {

                    if (data['status'] === "success") {

                        pull_data();

                        toastr.info("Success", data['message'], "success");

                    } else {

                        toastr.info("Error", data['message'], "error");

                    }

                },

            });

        }

    });

}


function voucherSummary(bulk_id) {

    if (bulk_id.length) {

        $.ajax({
            url: "ajax/ajax_finance_voucher_summary.php?action=view_voucherSummary",
            method: "POST",
            data: {
                "bulk_id": bulk_id
            },
            success: function (data) {

                if (data['status'] === "success") {


                    if ($.fn.dataTable.isDataTable('table.table-detail-1')) {

                        $("table.table-detail-1").DataTable().destroy();

                    }


                    let table_str = "";


                    if (data['data'].length > 0) {


                        table_str = "";


                        for (let x = 0; x < data['data'].length; x++) {


                            table_str += "<tr>";

                            table_str += "<td>" + (x + 1) + "</td>";
                            table_str += "<td>" + data['data'][x]['username'] + "</td>";

                            var createDate = new Date(data['data'][x]['date_create']);

                            var date = createDate.getDate();
                            var month = createDate.getMonth();
                            var year = createDate.getFullYear();

                            table_str += '<td>' + ("0" + date).slice(-2) + "-" + (month + 1) + "-" + year + "</td>";


                            var expiryDate = new Date(data['data'][x]['date_expiry']);

                            var date = expiryDate.getDate();
                            var month = expiryDate.getMonth();
                            var year = expiryDate.getFullYear();

                            table_str += '<td>' + ("0" + date).slice(-2) + "-" + (month + 1) + "-" + year + "</td>";

                            table_str += "<td>" + data['data'][x]['price'] + "</td>";

                            table_str += "</tr>";


                        }


                    } else {

                        table_str += '<tr><td colspan="5" align="center">No data available in table</td></tr>';
                    }


                    $("table.table-detail-1 > tbody").html(table_str);

                    $("table.table-detail-1").DataTable({
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


                    $("#voucherSummary").modal();


                } else {

                    swal("Error", data['message'], "error");

                }

            },

            error: function () {

                swal("Error", "There is unexpected error. Please try again.", "error");

            }


        })

    }
}
