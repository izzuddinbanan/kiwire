
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
    
    // $('#filter-btn').on("click", function() {
      
    //     $('#filter_modal').modal();

    // });

    // $('#filter-data').on("click", function() {
      
    //     pull_data();

    //     $('#filter_modal').modal("hide");
        
    // });

});


var report_chart;


function pull_data() {

    $.ajax({
        url: "ajax/ajax_report_monitoring_controller_report.php",
        method: "POST",
        data: {
            "startdate": $('#startdate').val(),
            "enddate": $('#enddate').val(),
            "action": "get_by_date"
        },
        success: function (data) {

            if (data['status'] === "success") {

                if ($.fn.dataTable.isDataTable('.table-data')) {

                    $(".table-data").DataTable().destroy();

                }

                let table_str = "";

                if (data['data'].length > 0) {

                    let chart_incident_count = [];

                    for (let x = 0; x < data['data'].length; x++) {

                        table_str += "<tr>";
                        table_str += "<td>" + (x + 1) + "</td>";
                        table_str += "<td>" + Date.parse(data['data'][x]['xreport_date']).toString("d-MMM-yyyy") + "</td>";
                        table_str += "<td>" + data['data'][x]['total'] + "</td>";
                        table_str += "<td>" + data['data'][x]['running'] + "</td>";
                        table_str += "<td>" + data['data'][x]['incident_count'] + "</td>";
                        table_str += "<td>" + data['data'][x]['issue'] + "</td>";
                        table_str += "</tr>";

                        chart_incident_count.push([data['data'][x]['xreport_date'], parseInt(data['data'][x]['incident_count'])]);


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
                        colors: [$primary_light, $danger_light, $success_light, $warning_light],
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
                                },
                                formatter: function (val) {
                                    return new Date(val).toString("dd-MMM-yyyy");
                                }
                            },
                            axisTicks: {
                                show: false,
                            },
                            type: 'datetime',
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
                            x: {
                                show: true,
                                format: 'dd MMM yyyy'
                            }
                        },
                        series: [{
                            name: "Incident Count",
                            data: chart_incident_count
                        }]

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

