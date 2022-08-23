

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

    // $("#filter-btn").on("click", function () {

    //     $("#filter_modal").modal();

    // });


    // $("#filter-data").on("click", function () {


    //     pull_data();

    //     $("#filter_modal").modal("hide");


    // });


    // filter by zone/project

    $('input:radio').change(function () {

        var val = $('input:radio:checked').val();

        if (val == 'Zone') {

            $('.zone').css('display', 'block')
            $('.project').css('display', 'none')


        } else {

            $('.project').css('display', 'block')
            $('.zone').css('display', 'none')

        }

    });


    // reset previous dropdown value before choose another

    $("#search").click(function (e) {
        $("select").val("");
    });

    //end


});


var dwell_chart, report_chart;

function pull_data() {


    if ($.fn.dataTable.isDataTable('.table-data')) {

        $(".table-data").DataTable().destroy();

    }


    if (report_chart !== undefined) {

        report_chart.destroy();

    }


    $.ajax({
        url: "ajax/ajax_report_login_dwell_time_sum.php",
        method: "POST",
        data: {
            "startdate": $('#startdate').val(),
            "enddate": $('#enddate').val(),
            "zone": $("select[name=zone]").val(),
            "project": $("select[name=project]").val(),
            "action": "get_all",
          
        },
        success: function (data) {

            if (data['status'] === "success") {


                let table_str = "";

                if (data['data'].length > 0) {

                    let chart_dwell = [], chart_total = [];

                    for (let x = 0; x < data['data'].length; x++) {


                        if (data['data'][x]['dwell'] > 0) {

                            avg_dwell = (parseFloat(data['data'][x]['dwell']) / parseFloat(data['data'][x]['total'])).toFixed(0);

                        } else {

                            avg_dwell = 0;

                            data['data'][x]['total'] = 0;

                        }


                        table_str += "<tr>";
                        table_str += "<td>" + (x + 1) + "</td>";
                        table_str += "<td>" + data['data'][x]['xreport_date'] + "</td>";
                        table_str += "<td>" + data['data'][x]['total'] + "</td>";
                        table_str += "<td>" + (new Date).clearTime().addSeconds(avg_dwell).toString("H:mm:ss") + "</td>";
                        table_str += "<td><button data-report-date='" + data['data'][x]['xreport_date'] + "' class='btn btn-primary btn-sm fa fa-search btn-report-details'></button></td>";
                        table_str += "</tr>";

                        chart_dwell.push([data['data'][x]['xreport_date'], parseFloat(avg_dwell / 60).toFixed(3)]);
                        chart_total.push([data['data'][x]['xreport_date'], data['data'][x]['total']]);


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
                            title: {
                                text: "Average Dwell Time Per Login In Minutes",
                            },
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
                            name: "Average Dwell",
                            data: chart_dwell
                        }, {
                            name: "Total",
                            data: chart_total
                        }]
                    };


                    report_chart = new ApexCharts(document.querySelector("#data-chart"), report_chart_option);

                    report_chart.render();


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
                            if ($('.dataTables_filter').find('input').hasClass('form-control-sm')) {
                                $('.dataTables_filter').find('input').removeClass('form-control-sm')
                            }

                            $(".btn-report-details").on("click", function () {


                                get_report_per_date($(this).data("report-date"))


                            });

                        }
                    });


                } else {

                    $("#data-chart").html("[ NO DATA AVAILABLE ]").css("color", "#000000");

                }


            } else {

                swal("Error", data['message'], "error");

            }

        },
        error: function () {

            swal("Error", "There is unexpected error. Please try again.", "error");

        }


    })

}



function get_report_per_date(report_date) {


    if (dwell_chart !== undefined) {

        try {

            dwell_chart.destroy();

        } catch (e) {

            console.log(e);

        }

    }


    if ($.fn.dataTable.isDataTable('.table-detail')) {

        $(".table-detail").DataTable().destroy();

    }

    $(".table-detail > tbody").html("<tr><td>No data to display</td></tr>");


    $.ajax({
        url: "ajax/ajax_report_login_dwell_time_sum.php",
        method: "POST",
        data: {
            "report_date": report_date,
            "action": "get_detail"
        },
        success: function (data) {

            if (data['status'] === "success") {


                let table_str = "";

                if (data['data'].length > 0) {

                    let chart_type = [];
                    let chart_dwell = [];

                    chart_count = 1;

                    for (let kindex in data['data']) {

                        table_str += "<tr>";
                        table_str += "<td>" + chart_count + "</td>";
                        table_str += "<td>" + data['data'][kindex]['type'] + "</td>";
                        table_str += "<td>" + data['data'][kindex]['count'] + "</td>";
                        table_str += "</tr>";

                        chart_type.push(data['data'][kindex]['type']);
                        chart_dwell.push(parseInt(data['data'][kindex]['count']));

                        chart_count++;

                    }



                    $(".table-detail > tbody").html(table_str);

                    $(".table-detail").dataTable({
                        dom: dt_position,
                        pageLength: dt_page,
                        buttons: dt_btn,
                        language: {
                            searchPlaceholder: "Search Records",
                            search: "",
                        },
                        "fnDrawCallback": function () {
                            if ($('.dataTables_filter').find('input').hasClass('form-control-sm')) {
                                $('.dataTables_filter').find('input').removeClass('form-control-sm')
                            }
                        }
                    });


                    if (chart_type.length > 0) {

                        var dwell_chart_option = {
                            chart: {
                                id: 'pie-dwell',
                                type: 'pie',
                                height: 450
                            },
                            colors: [$primary_light, $danger_light, $success_light, $warning_light],
                            labels: chart_type,
                            series: chart_dwell,
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


                        dwell_chart = new ApexCharts(document.querySelector("#detail-chart"), dwell_chart_option);

                        dwell_chart.render();

                    }


                }




                $("#view-detail").modal();


            }


        }

    });


}


