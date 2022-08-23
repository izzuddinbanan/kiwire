$(document).ready(function () {

    $('#search').on("click", pull_data);

});


function pull_data() {

    
    $.ajax({
        url: "ajax/ajax_help_find_mac_address.php?action=get_details",
        method: "POST",
        data: {
            "mac_address": $('#mac_address').val(),
        },
        success: function (data) {

            if (data['status'] === "success") {

                $(".d-info-account").html(data['data']['info']['last_account']);
                $(".d-info-type").html(data['data']['info']['details']['class']);
                $(".d-info-brand").html(data['data']['info']['details']['brand']);
                $(".d-info-model").html(data['data']['info']['details']['model']);
                $(".d-info-os").html(data['data']['info']['details']['system']);


            } else {

                swal("Error", data['message'], "error");

            }

        },
        error: function () {

            swal("Error", "There is unexpected error. Please try again.", "error");

        }


    })



    $.ajax({
        url: "ajax/ajax_help_find_mac_address.php?action=get_login_history",
        method: "POST",
        data: {
            "mac_address": $('#mac_address').val(),
        },
        success: function (data) {

            if (data['status'] === "success") {

                if ($.fn.dataTable.isDataTable('.table-data')) {

                    $(".table-data").DataTable().destroy();

                }

                let table_str = "";

                if (data['data'] != null && data['data'].length > 0) {


                    for (let x = 0; x < data['data'].length; x++) {


                        table_str += "<tr>";

                        table_str += "<td>" + (x + 1) + "</td>";

                        table_str += "<td>" + data['data'][x]['start_time'] + "</td>";
                        table_str += "<td>" + data['data'][x]['username'] + "</td>";
                        table_str += "<td>" + data['data'][x]['mac_address'] + "</td>";
                        table_str += "<td>" + data['data'][x]['ip_address'] + "</td>";

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
