$(document).ready(function () {


    const params = new URLSearchParams(window.location.search)

    const voucher_id = params.get("voucher");


    if (voucher_id !== null){

        $.ajax({
            url: "/admin/ajax/ajax_finance_voucher_export.php",
            method: "post",
            data: {
                voucher_id: voucher_id
            },
            success: function (response) {

                if (response['status'] === "success"){


                    let voucher_str = "";
                    let voucher_count = 1;

                    for(let kindex in response['data']){


                        voucher_str += "<tr>";
                        voucher_str += "<td>" + voucher_count + "</td>";
                        voucher_str += "<td>" + response['data'][kindex]['username'] + "</td>";
                        voucher_str += "<td>" + response['data'][kindex]['profile_subs'] + "</td>";
                        voucher_str += "<td>" + response['data'][kindex]['price'] + "</td>";
                        voucher_str += "<td>" + Date.parse(response['data'][kindex]['date_create']).toString("d-MMM-yyyy") + "</td>";
                        voucher_str += "<td>" + Date.parse(response['data'][kindex]['date_expiry']).toString("d-MMM-yyyy") + "</td>";
                        voucher_str += "</tr>";

                        voucher_count++;


                    }


                    $("table.table-data > tbody").html(voucher_str);

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

                        }
                    });



                } else {

                    swal("ERROR");

                }

            },
            error: function (response) {

                swal("ERROR");

            }
        });


    } else {

        window.location.href = "/admin/finance_voucher_slip.php";

    }



});