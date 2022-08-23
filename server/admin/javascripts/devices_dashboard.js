var $primary = '#7367F0';
var $success = '#28C76F';
var $danger = '#EA5455';
var $warning = '#FF9F43';
var $info = '#00cfe8';
var $primary_light = '#A9A2F6';
var $danger_light = '#f29292';
var $success_light = '#55DD92';
var $warning_light = '#ffc085';
var $info_light = '#1fcadb';
var $strok_color = '#b9c3cd';
var $label_color = '#e7e7e7';
var $white = '#fff';
var colors = [$primary, $danger, '#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0', '#546E7A', '#26a69a', '#D10CE8'];
var $themeColors = [$success, $warning, $danger, $primary, $info];

var device_chart = null;


$(document).ready(function () {


    general_data();
    warning_critical();
    status_data();

    setInterval(function () {

        general_data();
        warning_critical();
        status_data();

    }, 60000);


});

function general_data() {

    $.ajax({
        url: "/admin/ajax/ajax_devices_dashboard.php",
        method: "post",
        data: {
            action: "general"
        },
        success: function (response) {


            if (response['status'] === "success") {


                // check response, make sure all variable available

                let statuses = ['running', 'warning', 'down', 'unknown'];

                for (let kindex in statuses){

                    if (response['data'][statuses[kindex]] === undefined || response['data'][statuses[kindex]] === null){

                        response['data'][statuses[kindex]] = 0;

                    }

                }


                for (let kindex in response['data']) {


                    if (kindex === "unknown") kindex = "warning";

                    $("#current-device-" + kindex).html(response['data'][kindex]);


                }


                if (response['data']['down'] > 0){

                    $("#current-device-down").parent().parent().parent().removeClass("bg-gradient-primary").addClass("bg-gradient-danger");

                } else {

                    $("#current-device-down").parent().parent().parent().removeClass("bg-gradient-danger").addClass("bg-gradient-primary");

                }


                if (response['data']['unknown'] > 0 || response['data']['warning'] > 0){

                    $("#current-device-warning").parent().parent().parent().removeClass("bg-gradient-primary").addClass("bg-gradient-warning");

                } else {

                    $("#current-device-warning").parent().parent().parent().removeClass("bg-gradient-warning").addClass("bg-gradient-primary");

                }


                // generate chart

                var device_chart_option = {
                    chart: {
                        id: 'pie-device',
                        type: 'pie',
                        height: 450
                    },
                    colors: [$success_light, $warning_light, $danger_light, $primary_light],
                    labels: [ 'Running','Warning','Down','Unknown'],
                    series: [response['data']['running'], response['data']['warning'], response['data']['down'], response['data']['unknown']],
                    legend: {
                        itemMargin: {
                            horizontal: 2
                        },
                    },
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 350
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }]
                };


                if (device_chart !== undefined && device_chart !== null) {

                    device_chart.destroy();

                }

                device_chart = new ApexCharts(document.querySelector("#pie-chart-device"), device_chart_option);

                device_chart.render();



            }


        }
    });

}


function warning_critical() {

    $.ajax({
        url: "/admin/ajax/ajax_devices_dashboard.php",
        method: "post",
        data: {
            action: "warning_critical"
        },
        success: function (response) {

            if (response['status'] === "success") {


                let event_list = "";


                if (response['data'].length > 0) {

                    for (let kindex in response['data']) {

                        if (response['data'][kindex] !== undefined) {

                            event_list += "<li>" + "<div class='timeline-icon bg-danger'>" + "<i class='feather icon-plus font-medium-2 align-middle'></i>" + "</div>" + "<div class='timeline-info'>" + "<p class='font-weight-bold mb-0'>" + response['data'][kindex]['unique_id'] + "</p>" + "</div>" + "<small class='text-muted'>" + response['data'][kindex]['last_update'] + "</small>" + "</li>";

                        }

                    }

                } else {

                    event_list = "No Event Recorded";

                }


                $("ul.activity-timeline").html(event_list);


            }

        }
    });

}


function status_data() {

    $.ajax({
        url: "/admin/ajax/ajax_devices_dashboard.php",
        method: "post",
        data: {
            action: "status"
        },
        success: function (response) {

            if (response['status'] === "success") {


                let controller_data = "";

                let kcounter = 1;

                for (let kindex in response['data']) {

                    let kstatus = response['data'][kindex]['status'];

                    if (kstatus === "running") kstatus = "<label class='badge badge-success'>Running</label>";
                    else if (kstatus === "warning") kstatus = "<label class='badge badge-warning'>Warning</label>";
                    else kstatus = "<label class='badge badge-danger'>Down</label>";

                    controller_data += "<tr>";
                    controller_data += "<td>" + kcounter + "</td>";
                    controller_data += "<td>" + response['data'][kindex]['unique_id'] + "</td>";
                    controller_data += "<td>" + response['data'][kindex]['ip_address'] + "</td>";
                    controller_data += "<td>" + (response['data'][kindex]['location'] === "" ? "Unknown" : response['data'][kindex]['location']) + "</td>";
                    controller_data += "<td>" + response['data'][kindex]['time'] + "</td>";
                    controller_data += "<td>" + kstatus + "</td>";
                    controller_data += "<td>" + response['data'][kindex]['input_vol'] + "</td>";
                    controller_data += "<td>" + response['data'][kindex]['output_vol'] + "</td>";
                    controller_data += "<td>" + response['data'][kindex]['avg_speed'] + "</td>";
                    controller_data += "</tr>";

                    kcounter++;


                }


                if ($.fn.dataTable.isDataTable('.table-data')) {

                    $(".table-data").DataTable().destroy();

                }


                $(".table-data > tbody").html(controller_data);

                $(".table-data").dataTable({
                    responsive: true,
                    dom: dt_position,
                    pageLength: dt_page,
                    buttons: dt_btn,
                    language: {
                        searchPlaceholder: "Search Records",
                        search: "",
                    },
                    "order": [[ 5, "asc" ]],
                    "fnDrawCallback": function () {
                        if($('.dataTables_filter').find('input').hasClass('form-control-sm')){
                            $('.dataTables_filter').find('input').removeClass('form-control-sm')
                        }
                        
                        $(".btn-device-detail").on("click", function () {

                            display_device($(this).data("device-id"));

                        });

                    }
                });


            }

        }
    });

}


function display_device(device_id) {


    $.ajax({
        url: "/admin/ajax/ajax_devices_dashboard.php",
        method: "post",
        data: {
            action: "detail"
        },
        success: function (response) {

            if (response['status'] === "success") {


            }

        }
    });


}
