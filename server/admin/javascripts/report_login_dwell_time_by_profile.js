
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


var report_chart, detail_chart;


function pull_data() {


    $.ajax({
        url: "ajax/ajax_report_login_dwell_time_by_profile.php",
        method: "POST",
        data: {
            "startdate": $('#startdate').val(),
            "enddate": $('#enddate').val(),
            "profile": $("select[name=profile]").val(),
            "zone": $("select[name=zone]").val(),
            "project": $("select[name=project]").val(),
            "action": "get_all"

        },
        success: function (data) {

            if (data['status'] === "success") {


                if ($.fn.dataTable.isDataTable('.table-data')) {

                    $(".table-data").DataTable().destroy();

                }



                let table_str = "";


                if (data['data'].length > 0) {


                    let chart_sort = [];
                    let chart_profile = [];
                    let chart_dwell = [];


                    for (let x = 0; x < data['data'].length; x++) {


                        if (data['data'][x]['dwell'] > 0) {

                            current_dwell = parseInt((data['data'][x]['dwell'] / data['data'][x]['login']));

                        } else {

                            current_dwell = parseFloat(0).toFixed(3);

                        }


                        table_str += "<tr>";
                        table_str += "<td>" + (x + 1) + "</td>";
                        table_str += "<td>" + Date.parse(data['data'][x]['xreport_date']).toString("d-MMM-yyyy") + "</td>";
                        table_str += "<td>" + data['data'][x]['profile'] + "</td>";
                        table_str += "<td>" + (new Date).clearTime().addSeconds(current_dwell).toString('H:mm:ss') + "</td>";
                        table_str += "<td><button href='#' data-report-date='" + data['data'][x]['xreport_date'] + "' class='btn btn-primary btn-sm fa fa-search btn-report-details'></button></td>";
                        table_str += "</tr>";


                        if (chart_profile.indexOf(data['data'][x]['profile']) === -1) {

                            chart_profile.push(data['data'][x]['profile']);

                        }


                        if (chart_sort[data['data'][x]['xreport_date']] === undefined) {

                            chart_sort[data['data'][x]['xreport_date']] = [];

                        }


                        if (chart_sort[data['data'][x]['xreport_date']][data['data'][x]['profile']] === undefined) {

                            chart_sort[data['data'][x]['xreport_date']][data['data'][x]['profile']] = [];

                        }


                        if (chart_sort[data['data'][x]['xreport_date']][data['data'][x]['profile']]['dwell'] === undefined) {

                            chart_sort[data['data'][x]['xreport_date']][data['data'][x]['profile']]['dwell'] = [];

                        }


                        chart_sort[data['data'][x]['xreport_date']][data['data'][x]['profile']]['dwell'] = (current_dwell / 60).toFixed(3);


                    }


                    for (let kindex in chart_sort) {


                        for (let lindex in chart_profile) {


                            if (chart_sort[kindex][chart_profile[lindex]] === undefined) {


                                chart_sort[kindex][chart_profile[lindex]] = [];

                                chart_sort[kindex][chart_profile[lindex]]['dwell'] = 0;


                            }


                        }


                    }


                    chart_profile = null;


                    for (let kindex in chart_sort) {


                        for (let lindex in chart_sort[kindex]) {


                            if (chart_dwell[lindex] === undefined) {

                                chart_dwell[lindex] = [];

                            }


                            chart_dwell[lindex].push([kindex, chart_sort[kindex][lindex]['dwell']]);


                        }


                    }


                    chart_sort = null;


                    let report_chart_option = {


                        chart: {
                            id: "login-frequency-by-profile",
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
                        colors: [$primary_light, $danger_light, $success_light, $warning_light, $info_light, $primary, $success, $danger, $warning, $info],
                        markers: {
                            size: 0,
                            hover: {
                                size: 5
                            }
                        },
                        xaxis: {
                            type: 'datetime',
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
                            axisBorder: {
                                show: false,
                            },
                            tickPlacement: 'on',
                        },
                        yaxis: [{
                            title: {
                                text: "Average Dwell Time Per Login In Minutes",
                            },
                            labels: {
                                style: {
                                    color: $strok_color,
                                },
                            }
                        }],
                        tooltip: {
                            x: { show: true }
                        },
                        series: [],

                    };


                    for (let kindex in chart_dwell) {

                        report_chart_option['series'].push({
                            name: "Dwell: " + kindex,
                            data: chart_dwell[kindex]
                        });

                    }


                    if (report_chart !== undefined) {

                        report_chart.destroy();

                    }


                    report_chart = new ApexCharts(document.querySelector("#data-chart"), report_chart_option);

                    report_chart.render();


                } else {

                    $("#data-chart").html("[ NO DATA AVAILABLE ]").css("color", "#000000");

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
                        if ($('.dataTables_filter').find('input').hasClass('form-control-sm')) {
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



function get_report_per_date(report_date) {


    if (report_date.length > 0) {

        $.ajax({
            url: "/admin/ajax/ajax_report_login_dwell_time_by_profile.php",
            method: "get",
            data: {
                report_date: report_date,
                action: "get_detail"
            },
            success: function (response) {


                if (response['status'] === "success") {


                    if ($.fn.dataTable.isDataTable('.table-detail')) {

                        $(".table-detail").DataTable().destroy();

                    }


                    let chart_sort = [];
                    let chart_profile = [];
                    let chart_dwell = [];


                    let table_str = "";


                    for (let x = 0; x < response['data'].length; x++) {


                        if (response['data'][x]['dwell'] > 0) {

                            current_dwell = parseInt((response['data'][x]['dwell'] / response['data'][x]['login']));

                        } else {

                            current_dwell = parseFloat(0).toFixed(3);

                        }


                        table_str += "<tr>";
                        table_str += "<td class='profile-date'>" + response['data'][x]['xreport_date'] + "</td>";
                        table_str += "<td>" + response['data'][x]['profile'] + "</td>";
                        table_str += "<td>" + (new Date).clearTime().addSeconds(current_dwell).toString('H:mm:ss') + "</td>";
                        table_str += "</tr>";


                        if (chart_profile.indexOf(response['data'][x]['profile']) === -1) {

                            chart_profile.push(response['data'][x]['profile']);

                        }


                        if (chart_sort[response['data'][x]['xreport_date']] === undefined) {

                            chart_sort[response['data'][x]['xreport_date']] = [];

                        }


                        if (chart_sort[response['data'][x]['xreport_date']][response['data'][x]['profile']] === undefined) {

                            chart_sort[response['data'][x]['xreport_date']][response['data'][x]['profile']] = [];

                        }


                        if (chart_sort[response['data'][x]['xreport_date']][response['data'][x]['profile']]['dwell'] === undefined) {

                            chart_sort[response['data'][x]['xreport_date']][response['data'][x]['profile']]['dwell'] = [];

                        }


                        if (current_dwell > 0) {

                            chart_sort[response['data'][x]['xreport_date']][response['data'][x]['profile']]['dwell'] = (current_dwell / 60).toFixed(3);

                        } else {

                            chart_sort[response['data'][x]['xreport_date']][response['data'][x]['profile']]['dwell'] = 0;

                        }


                    }


                    for (let kindex in chart_sort) {


                        for (let lindex in chart_profile) {


                            if (chart_sort[kindex][chart_profile[lindex]] === undefined) {


                                chart_sort[kindex][chart_profile[lindex]] = [];

                                chart_sort[kindex][chart_profile[lindex]]['dwell'] = 0;


                            }


                        }


                    }


                    chart_profile = null;


                    for (let kindex in chart_sort) {


                        for (let lindex in chart_sort[kindex]) {


                            if (chart_dwell[lindex] === undefined) {

                                chart_dwell[lindex] = [];

                            }


                            chart_dwell[lindex].push([kindex, chart_sort[kindex][lindex]['dwell']]);


                        }


                    }


                    chart_sort = null;


                    var detail_chart_option = {


                        chart: {
                            id: "login-frequency-by-profile",
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
                        colors: [$primary_light, $danger_light, $success_light, $warning_light, $info_light, $primary, $success, $danger, $warning, $info],
                        markers: {
                            size: 0,
                            hover: {
                                size: 5
                            }
                        },
                        xaxis: {
                            type: 'datetime',
                            labels: {
                                style: {
                                    colors: $strok_color,
                                },
                                formatter: function (val) {
                                    return new Date(val).toString("H");
                                }
                            },
                            axisTicks: {
                                show: false,
                            },
                            axisBorder: {
                                show: false,
                            },
                            tickPlacement: 'on',
                        },
                        yaxis: [{
                            title: {
                                text: "Average Dwell Time Per Login In Minutes",
                            },
                            labels: {
                                style: {
                                    color: $strok_color,
                                },
                            }
                        }],
                        tooltip: {
                            x: { show: false }
                        },
                        series: [],

                    };



                    for (let kindex in chart_dwell) {

                        detail_chart_option['series'].push({
                            name: "Dwell: " + kindex,
                            data: chart_dwell[kindex]
                        });

                    }


                    if (detail_chart !== undefined) {

                        detail_chart.destroy();

                    }


                    detail_chart = new ApexCharts(document.querySelector("#detail-chart"), detail_chart_option);

                    detail_chart.render();


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


                    $("#view-detail").modal();


                }


            }

        });

    }



}