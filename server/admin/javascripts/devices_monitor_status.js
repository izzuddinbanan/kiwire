String.prototype.capitalize = function() {

    return this.charAt(0).toUpperCase() + this.slice(1);

};


var countdown = 60;


$(document).ready(function () {


    pull_data();


    setInterval(function () {


        if (countdown !== 1){


            countdown--;

            $(".remaining").html(countdown);


        } else {


            pull_data();

            countdown = 60;

        }


    }, 1000);


});


function pull_data() {

    $.ajax({
        url: "ajax/ajax_devices_monitor_status.php",
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
                    table_str += "<td>" + data['data'][x]['unique_id'] + "</td>";
                    table_str += "<td>" + data['data'][x]['device_ip'] + "</td>";
                    table_str += "<td>" + data['data'][x]['device_type'].capitalize() + "</td>";


                    if (data['data'][x]['status'] === "running") {

                        table_str += "<td><span class='badge badge-success'>Online</span></td>";

                    } else if (data['data'][x]['status'] === "down") {

                        table_str += "<td><span class='badge badge-danger'>Offline</span></td>";

                    } else {

                        table_str += "<td><span class='badge badge-warning'>Unknown</span></td>";

                    }


                    if (data['data'][x]['last_update'] == null){

                        table_str += "<td>Never</td>";

                    } else {

                        table_str += "<td>" + data['data'][x]['last_update'] + "</td>";

                    }


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

                    }
                });

            } else {

                swal("Error", data['message'], "error");

            }

        },
        error: function () {

            swal("Error", "There is unexpected error. Please try again.", "error");

        }

    });


}
