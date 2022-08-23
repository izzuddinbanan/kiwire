
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


var report_chart, detail_chart;


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


function pull_data() {

    $.ajax({
        url: "ajax/ajax_report_login_logins_freq_profile.php",
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

                    let chart_total = [];
                    let chart_unique = [];


                    for (let x = 0; x < data['data'].length; x++) {


                        table_str += "<tr>";
                        table_str += "<td>" + (x + 1) + "</td>";
                        table_str += "<td>" + Date.parse(data['data'][x]['xreport_date']).toString("d-MMM-yyyy") + "</td>";
                        table_str += "<td>" + data['data'][x]['profile'] + "</td>";
                        table_str += "<td>" + data['data'][x]['login'] + "</td>";
                        table_str += "<td>" + data['data'][x]['ulogin'] + "</td>";
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


                        if (chart_sort[data['data'][x]['xreport_date']][data['data'][x]['profile']]['login'] === undefined) {

                            chart_sort[data['data'][x]['xreport_date']][data['data'][x]['profile']]['login'] = [];

                        }

                        if (chart_sort[data['data'][x]['xreport_date']][data['data'][x]['profile']]['ulogin'] === undefined) {

                            chart_sort[data['data'][x]['xreport_date']][data['data'][x]['profile']]['ulogin'] = [];

                        }


                        chart_sort[data['data'][x]['xreport_date']][data['data'][x]['profile']]['login'] = data['data'][x]['login'];

                        chart_sort[data['data'][x]['xreport_date']][data['data'][x]['profile']]['ulogin'] = data['data'][x]['ulogin'];


                    }


                    for (let kindex in chart_sort) {


                        for (let lindex in chart_profile) {


                            if (chart_sort[kindex][chart_profile[lindex]] === undefined) {

                                chart_sort[kindex][chart_profile[lindex]] = [];

                                chart_sort[kindex][chart_profile[lindex]]['login'] = "0";
                                chart_sort[kindex][chart_profile[lindex]]['ulogin'] = "0";


                            }


                        }


                    }


                    chart_profile = null;


                    for (let kindex in chart_sort) {


                        for (let lindex in chart_sort[kindex]) {


                            if (chart_total[lindex] === undefined) {

                                chart_total[lindex] = [];
                                chart_unique[lindex] = [];

                            }


                            chart_total[lindex].push([kindex, chart_sort[kindex][lindex]['login']]);
                            chart_unique[lindex].push([kindex, chart_sort[kindex][lindex]['ulogin']]);


                        }

                    }


                    chart_sort = null;


                    var report_chart_option = {

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
                            x: { show: true }
                        },
                        series: [],
                        responsive: [{
                            breakpoint: 480,
                            options: {
                                chart: {
                                    height: '700'
                                }
                            }
                        }]
                    };


                    for (let kindex in chart_total) {

                        report_chart_option['series'].push({
                            name: "Total: " + kindex,
                            data: chart_total[kindex]
                        });

                    }


                    for (let kindex in chart_unique) {

                        report_chart_option['series'].push({
                            name: "Unique Daily: " + kindex,
                            data: chart_unique[kindex]
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
            url: "/admin/ajax/ajax_report_login_logins_freq_profile.php",
            method: "get",
            data: {
                report_date: report_date,
                profile: $("select[name=profile]").val(),
                zone: $("select[name=zone]").val(),
                project: $("select[name=project]").val(),
                action: "get_detail"
            },
            success: function (response) {


                if (response['status'] === "success") {


                    if ($.fn.dataTable.isDataTable('.table-detail')) {

                        $(".table-detail").DataTable().destroy();

                    }


                    let table_str = "";


                    let chart_sort = [];
                    let chart_profile = [];

                    let chart_total = [];
                    let chart_unique = [];


                    for (let x = 0; x < response['data'].length; x++) {


                        table_str += "<tr>";
                        table_str += "<td>" + response['data'][x]['xreport_date'] + "</td>";
                        table_str += "<td>" + response['data'][x]['profile'] + "</td>";
                        table_str += "<td>" + response['data'][x]['login'] + "</td>";
                        table_str += "<td>" + response['data'][x]['ulogin'] + "</td>";
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


                        if (chart_sort[response['data'][x]['xreport_date']][response['data'][x]['profile']]['login'] === undefined) {

                            chart_sort[response['data'][x]['xreport_date']][response['data'][x]['profile']]['login'] = [];

                        }

                        if (chart_sort[response['data'][x]['xreport_date']][response['data'][x]['profile']]['ulogin'] === undefined) {

                            chart_sort[response['data'][x]['xreport_date']][response['data'][x]['profile']]['ulogin'] = [];

                        }


                        chart_sort[response['data'][x]['xreport_date']][response['data'][x]['profile']]['login'] = response['data'][x]['login'];

                        chart_sort[response['data'][x]['xreport_date']][response['data'][x]['profile']]['ulogin'] = response['data'][x]['ulogin'];


                    }


                    for (let kindex in chart_sort) {


                        for (let lindex in chart_profile) {


                            if (chart_sort[kindex][chart_profile[lindex]] === undefined) {

                                chart_sort[kindex][chart_profile[lindex]] = [];

                                chart_sort[kindex][chart_profile[lindex]]['login'] = "0";
                                chart_sort[kindex][chart_profile[lindex]]['ulogin'] = "0";


                            }


                        }


                    }


                    chart_profile = null;


                    for (let kindex in chart_sort) {


                        for (let lindex in chart_sort[kindex]) {


                            if (chart_total[lindex] === undefined) {

                                chart_total[lindex] = [];
                                chart_unique[lindex] = [];

                            }


                            chart_total[lindex].push([kindex, chart_sort[kindex][lindex]['login']]);
                            chart_unique[lindex].push([kindex, chart_sort[kindex][lindex]['ulogin']]);


                        }

                    }


                    chart_sort = null;


                    var report_chart_option = {

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


                    for (let kindex in chart_total) {

                        report_chart_option['series'].push({
                            name: "Total: " + kindex,
                            data: chart_total[kindex]
                        });

                    }


                    for (let kindex in chart_unique) {

                        report_chart_option['series'].push({
                            name: "Unique Daily: " + kindex,
                            data: chart_total[kindex]
                        });

                    }


                    if (detail_chart !== undefined) {

                        detail_chart.destroy();

                    }

                    detail_chart = new ApexCharts(document.querySelector("#detail-chart"), report_chart_option);

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