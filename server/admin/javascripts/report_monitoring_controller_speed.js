
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
var $themeColors = [$primary, $success, $danger, $warning, $info];

$(document).ready(function () {

    pull_data();

    $('#search').on("click", pull_data);

});

var report_chart;

function pull_data() {

    $.ajax({
        url: "ajax/ajax_report_monitoring_controller_speed.php",
        method: "GET",
        data: {
            "startdate": $('#startdate').val(),
            "enddate": $('#enddate').val(),
            "action": "get_all",
            "controller": $("#controller").val()
        },
        success: function (data) {

            if (data['status'] === "success") {

                if($.fn.dataTable.isDataTable('.table-data')){

                    $(".table-data").DataTable().destroy();

                }

                let table_str = "";

                if (data['data'].length > 0) {

                    let chart_date = [];
                    let chart_speed = [];

                    for (let x = 0; x < data['data'].length; x++) {

                        table_str += "<tr>";
                        table_str += "<td>" + (x + 1) + "</td>";
                        table_str += "<td>" + Date.parse(data['data'][x]['xreport_date']).toString("d-MMM-yyyy") + "</td>";
                        table_str += "<td>" + parseFloat(data['data'][x]['avg_speed']).toFixed(3) + "</td>";
                        table_str += "</tr>";

                        chart_speed.push([data['data'][x]['xreport_date'],parseFloat(data['data'][x]['avg_speed']).toFixed(3)]);
                

                    }

                    var report_chart_option = {

                        chart: {
                            height: 450,
                            toolbar: { show: true },
                            type: 'line',
                        },
                        stroke: {
                            curve: 'smooth',
                            dashArray: 0,
                            width: 4,
                        },
                        grid: {
                            borderColor: $label_color,
                        },
                        legend: {
                            show: true,
                        },
                        colors: [ $primary_light, $danger_light,  $success_light, $warning_light],

                        markers: {
                            size: 0,
                            hover: {
                                size: 5
                            }
                        },
                        xaxis: {
                            labels: {
                                style: {
                                    colors: $strok_color,
                                }
                            },
                            axisTicks: {
                                show: false,
                            },
                            categories: chart_date,
                            axisBorder: {
                                show: false,
                            },
                            tickPlacement: 'on',
                        },
                        yaxis: {
                            labels: {
                                style: {
                                    color: $strok_color,
                                },
                            }
                        },
                        tooltip: {
                            x: { show: false }
                        },
                        series: [{
                            name: "Speed",
                            data: chart_speed
                        }],

                    };


                    if (report_chart !== undefined) {

                        report_chart.destroy();
    
                    }


                    report_chart = new ApexCharts(document.querySelector("#data-chart"), report_chart_option);

                    report_chart.render();


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
                        
                        $(".btn-report-details").on("click", function () {


                            get_report_per_date($(this).data("report-date"))


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