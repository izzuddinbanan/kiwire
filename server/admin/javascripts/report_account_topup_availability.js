$(document).ready(function () {

    pull_data();

    $('#search').on("click", pull_data);

    // $('#filter-btn').on("click", function () {

    //     $('#filter_modal').modal();


    // });

    // $('#filter-data').on("click", function () {

    //     $('#filter_modal').modal("hide");
        
    // })

});



function pull_data() {

    $.ajax({
        url: "ajax/ajax_report_account_topup_availability.php?action=get_by_date",
        method: "POST",
        data: {
            "startdate": $('#startdate').val(),
            "enddate": $('#enddate').val()
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

                        table_str += "<td>" + data['data'][x]['bulk_id'] + "</td>";
                        table_str += "<td>" + data['data'][x]['creator'] + "</td>";
                        table_str += "<td>" + data['data'][x]['qty'] + "</td>";
                        table_str += "<td>" + data['data'][x]['active'] + "</td>";

                        var qty = data['data'][x]['qty'];
                        var active = data['data'][x]['active'];
                        var fresh = (qty - active);
                        table_str += "<td>" + fresh + "</td>";

                        table_str += "<td>" + data['data'][x]['remark'] + "</td>";

                        table_str += "<td><a href=\"javascript:void(0);\" onclick=\"freshTopup('" + data['data'][x]['bulk_id'] + "')\" class='btn btn-icon btn-warning btn-xs mr-1 fa fa-search'></a></td>";

                        table_str += "</tr>";
                    }


                } else {

                    table_str += '<tr><td colspan="10" align="center">No data available in table</td></tr>';

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



function freshTopup(bulk_id) {

    if (bulk_id.length) {

        $.ajax({
            url: "ajax/ajax_report_account_topup_availability.php?action=view_activatedTopup",
            method: "POST",
            data: {
                "bulk_id": bulk_id,
                "startdate": $('#startdate').val(),
                "enddate": $('#enddate').val()
            },
            success: function (data) {

                if (data['status'] === "success") {

                    let table_str_1 = "";

                    if (data['data'].length > 0) {

                        table_str_1 = "<thead><tr class='text-uppercase'><th>No</th><th>Topup Code</th><th>Create Date</th><th>Expiry Date</th><th>Unit Price(MYR)</th></tr></thead>";

                        table_str_1 += "<tbody>";

                        for (let x = 0; x < data['data'].length; x++) {

                            table_str_1 += "<tr>";

                            table_str_1 += "<td>" + (x + 1) + "</td>";
                            table_str_1 += "<td>" + data['data'][x]['code'] + "</td>";


                            var createDate = new Date(data['data'][x]['date_create']);
                            var date = createDate.getDate();
                            var month = createDate.getMonth();
                            var year = createDate.getFullYear();

                            var createDate = ("0" + date).slice(-2) + "-" + (month + 1) + "-" + year;
                            table_str_1 += "<td>" + createDate + "</td>";

        
                            var expiryDate = new Date(data['data'][x]['date_expiry']);
                            var date = expiryDate.getDate();
                            var month = expiryDate.getMonth();
                            var year = expiryDate.getFullYear();

                            var expiry = ("0" + date).slice(-2) + "-" + (month + 1) + "-" + year;
                            table_str_1 += "<td>" + expiry + "</td>";


                            table_str_1 += "<td>" + data['data'][x]['price'] + "</td>";

                            table_str_1 += "</tr>";

                        }

                        table_str_1 += "</tbody>";



                    } else {

                        table_str_1 += '<tr><td colspan="5" align="center">No data available in table</td></tr>';
                    }


                    $(".table-detail-1").html(table_str_1);

                    $("#freshTopup").modal();

                    $(".table-detail-1").DataTable();



                } else {

                    swal("Error", data['message'], "error");

                }

            },

            error: function () {

                swal("Error", "There is unexpected error. Please try again.", "error");

            }


        })


        $.ajax({
            url: "ajax/ajax_report_account_topup_availability.php?action=view_freshTopup",
            method: "POST",
            data: {
                "bulk_id": bulk_id,
                "startdate": $('#startdate').val(),
                "enddate": $('#enddate').val()
            },
            success: function (data) {

                if (data['status'] === "success") {

                    let table_str_2 = "";

                    if (data['data'].length > 0) {

                        table_str_2 = "<thead><tr class='text-uppercase'><th>No</th><th>Topup Code</th><th>Create Date</th><th>Expiry Date</th><th>Unit Price(MYR)</th></tr></thead>";

                        table_str_2 += "<tbody>";

                        for (let x = 0; x < data['data'].length; x++) {

                            table_str_2 += "<tr>";

                            table_str_2 += "<td>" + (x + 1) + "</td>";
                            table_str_2 += "<td>" + data['data'][x]['code'] + "</td>";


                            var createDate = new Date(data['data'][x]['date_create']);
                            var date = createDate.getDate();
                            var month = createDate.getMonth();
                            var year = createDate.getFullYear();

                            var createDate = ("0" + date).slice(-2) + "-" + (month + 1) + "-" + year;
                            table_str_2 += "<td>" + createDate + "</td>";


                            var expiryDate = new Date(data['data'][x]['date_expiry']);
                            var date = expiryDate.getDate();
                            var month = expiryDate.getMonth();
                            var year = expiryDate.getFullYear();

                            var expiry = ("0" + date).slice(-2) + "-" + (month + 1) + "-" + year;
                            table_str_2 += "<td>" + expiry + "</td>";


                            table_str_2 += "<td>" + data['data'][x]['price'] + "</td>";

                            table_str_2 += "</tr>";

                        }

                        table_str_2 += "</tbody>";


                    } else {

                        table_str_2 += '<tr><td colspan="5" align="center">No data available in table</td></tr>';
                    }

                    $(".table-detail-2").html(table_str_2);

                    $("#freshTopup").modal();

                    $(".table-detail-2").DataTable();




                } else {

                    swal("Error", data['message'], "error");

                }

            },

            error: function () {

                swal("Error", "There is unexpected error. Please try again.", "error");

            }


        })


        $.ajax({
            url: "ajax/ajax_report_account_topup_availability.php?action=view_expiredTopup",
            method: "POST",
            data: {
                "bulk_id": bulk_id,
                "startdate": $('#startdate').val(),
                "enddate": $('#enddate').val()
            },
            success: function (data) {

                if (data['status'] === "success") {

                    let table_str_3 = "";

                    if (data['data'].length > 0) {

                        table_str_3 = "<thead><tr class='text-uppercase'><th>No</th><th>Topup Code</th><th>Create Date</th><th>Expiry Date</th><th>Unit Price(MYR)</th></tr></thead>";

                        table_str_3 += "<tbody>";

                        for (let x = 0; x < data['data'].length; x++) {

                            table_str_3 += "<tr>";

                            table_str_3 += "<td>" + (x + 1) + "</td>";
                            table_str_3 += "<td>" + data['data'][x]['code'] + "</td>";


                            var createDate = new Date(data['data'][x]['date_create']);
                            var date = createDate.getDate();
                            var month = createDate.getMonth();
                            var year = createDate.getFullYear();

                            var createDate = ("0" + date).slice(-2) + "-" + (month + 1) + "-" + year;
                            table_str_3 += "<td>" + createDate + "</td>";


                            var expiryDate = new Date(data['data'][x]['date_expiry']);
                            var date = expiryDate.getDate();
                            var month = expiryDate.getMonth();
                            var year = expiryDate.getFullYear();

                            var expiry = ("0" + date).slice(-2) + "-" + (month + 1) + "-" + year;
                            table_str_3 += "<td>" + expiry + "</td>";


                            table_str_3 += "<td>" + data['data'][x]['price'] + "</td>";

                            table_str_3 += "</tr>";

                        }

                        table_str_3 += "</tbody>";



                    } else {

                        table_str_3 += '<tr><td colspan="5" align="center">No data available in table</td></tr>';
                    }


                    $(".table-detail-3").html(table_str_3);

                    $("#freshTopup").modal();

                    $(".table-detail-3").DataTable();



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
