$(document).ready(function () {

    pull_data();


});

function pull_data() {

    $.ajax({
        url: "ajax/history.php",
        method: "POST",
        data: {
            action: "history"
        },
        success: function (data) {

            if (data['status'] === "success") {


                // if ($.fn.dataTable.isDataTable('#table1')) {

                //     $("#table1").DataTable().destroy();

                // }


                let table_str = "";


                for (let x = 0; x < data['data'].length; x++) {


                    table_str += "<tr>";

                    table_str += "<td>" + (x + 1) + "</td>";
                    table_str += "<td>" + data['data'][x]['start_time'] + "</td>";
                    table_str += "<td>" + data['data'][x]['stop_time'] + "</td>";
                    table_str += "<td>" + convert_seconds_to_days(data['data'][x]['session_time']) + "</td>";
                    // table_str += "<td>" + new Date(data['data'][x]['session_time'] * 1000).toISOString().substr(11, 8) + "</td>";
                    table_str += "<td>" + data['data'][x]['mac_address'] + "</td>";
                    table_str += "<td>" + data['data'][x]['ip_address'] + "</td>";
                    table_str += "<td>" + ((parseInt(data['data'][x]['quota_in']) + parseInt(data['data'][x]['quota_out'])) / (1024 * 1024)).toFixed(3) + "</td>";
                    table_str += "<td>" + data['data'][x]['terminate_reason'] + "</td>";
                           
                    table_str += "</tr>";

                }

                $(".table-data > tbody").html(table_str);


                $(".table-data").dataTable();



            } else {

                swal("Error", data['message'], "error");

            }


        },
        error: function () {

            swal("Error", "There is unexpected error. Please try again.", "error");

        }

    });


}

function convert_seconds_to_days(seconds) {

    let days = Math.floor(seconds / 86400);
    seconds -= days * 86400;

    let hours = Math.floor(seconds / 3600) % 24;
    seconds -= hours * 3600;

    let minutes = Math.floor(seconds / 60) % 60;
    seconds -= minutes * 60;

    var seconds = seconds % 60;


    // formatting

    days = (days < 10 ? "0" + days : days);
    hours = (hours < 10 ? "0" + hours : hours);
    minutes = (minutes < 10 ? "0" + minutes : minutes);
    seconds = (seconds < 10 ? "0" + seconds : seconds);


    return days + ":" + hours + ":" + minutes + ":" + seconds;

}




