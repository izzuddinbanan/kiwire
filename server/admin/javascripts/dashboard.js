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


var yesterday_date = Date.parse("yesterday").toString("dd/MM/yyyy");
var today_date = Date.parse("today").toString("dd/MM/yyyy");

$(document).ready(function () {


    $.ajax({
        url: "/admin/ajax/ajax_dashboard.php",
        method: "post",
        data: {
            data: "general"
        },
        success: function (response) {

            if (response['status'] === "success") {

                $("#session-active").html(response['data']['active']);
                $("#session-connected").html(response['data']['connected']);
                $("#session-disconnect").html(response['data']['disconnected']);

                $("#current-view").html(response['data']['page_impression']);
                $("#current-campaign").html(response['data']['campaign_impress']);
                $("#current-clicked").html(response['data']['campaign_click']);
                $("#current-login").html(response['data']['login']);


            }

        }
    });


    $.ajax({
        url: "/admin/ajax/ajax_dashboard.php",
        method: "post",
        data: {
            data: "impressionvslogin"
        },
        success: function (response) {

            if (response['status'] === "success") {

                let chart_data_date = [];
                let chart_data_login = [];
                let chart_data_impression = [];

                for (let kindex in response['data']) {

                    chart_data_date.push(Date.parse(response['data'][kindex]['ddate']).toString("dd-MMM HH:00"));
                    chart_data_login.push(response['data'][kindex]['login']);
                    chart_data_impression.push(response['data'][kindex]['impression']);

                }


                var impressionvslogin_option = {
                    chart: {
                        id: "login-vs-impression",
                        height: 300,
                        toolbar: {show: false},
                        type: 'line',
                    },
                    stroke: {
                        curve: 'smooth',
                        dashArray: [0, 8],
                        width: [4, 2],
                    },
                    grid: {
                        borderColor: $label_color,
                    },
                    legend: {
                        show: false,
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
                            rotate: 0,
                            style: {
                                colors: $strok_color,
                            },
                            formatter: function (val) {
                                return new Date(val).toString("dd-MMM HH:00");
                            }
                        },
                        type: 'datetime',
                        axisTicks: {
                            show: false,
                        },
                        categories: chart_data_date,
                        axisBorder: {
                            show: false,
                        },
                        tickPlacement: 'on',
                    },
                    yaxis: {
                        min: 0,
                        tickAmount: 5,
                        labels: {
                            style: {
                                color: $strok_color,
                            },
                            formatter: function (val) {
                                return parseInt(val);
                            }
                        }
                    },
                    tooltip: {
                        x: {show: false}
                    },
                    series: [{
                        name: "Login",
                        data: chart_data_login
                    }, {
                        name: "Impression",
                        data: chart_data_impression
                    }],

                };


                var impressionvslogin = new ApexCharts(document.querySelector("#login-vs-impression"), impressionvslogin_option);

                impressionvslogin.render();


            }

        }
    });


    $.ajax({
        url: "/admin/ajax/ajax_dashboard.php",
        method: "post",
        data: {
            data: "dwell"
        },
        success: function (response) {

            if (response['status'] === "success") {

                let chart_data_date = [];
                let chart_data_dwell = [];


                for (let kindex in response['data']) {

                    chart_data_date.push(Date.parse(response['data'][kindex]['xreport_date']).toString("HH:00   "));

                    if (response['data'][kindex]['login'] === 0 || response['data'][kindex]['dwell'] === 0) {

                        chart_data_dwell.push(0);

                    } else {

                        chart_data_dwell.push(((parseInt(response['data'][kindex]['dwell']) / parseInt(response['data'][kindex]['login'])) / 60).toFixed(0));

                    }

                }


                if (chart_data_date[0] !== undefined && chart_data_date[chart_data_date.length - 1] !== undefined) {


                    var dwellchart_option = {
                        chart: {
                            height: 300,
                            type: 'bar',
                            stacked: false,
                        },
                        colors: $themeColors,
                        plotOptions: {
                            bar: {
                                columnWidth: '50%'
                            }
                        },
                        series: [{
                            name: 'Average Dwell ( Minutes )',
                            type: 'column',
                            data: chart_data_dwell
                        }],
                        fill: {
                            opacity: [0.85, 0.25, 1],
                            gradient: {
                                inverseColors: false,
                                shade: 'light',
                                type: "vertical",
                                opacityFrom: 0.85,
                                opacityTo: 0.55,
                                stops: [0, 100, 100, 100]
                            }
                        },
                        labels: chart_data_date,
                        dataLabels: {
                            enabled: false
                        },
                        markers: {
                            size: 0
                        },
                        legend: {
                            offsetY: -10
                        },
                        xaxis: {
                            labels: {
                                rotate: 0,
                            },
                            type: 'category',
                            title: {
                                text: 'Hour ( Start From ' + chart_data_date[0] + ' ' + yesterday_date + ' Until ' + chart_data_date[chart_data_date.length - 1] + ' ' + today_date + ' )'
                            },
                        },
                        yaxis: {
                            min: 0,
                            tickAmount: 5,
                            title: {
                                text: 'Minutes'
                            },
                        },
                    };


                    var dwellchart = new ApexCharts(document.querySelector("#dwell-chart"), dwellchart_option);

                    dwellchart.render();


                } else {


                    $("#dwell-chart").html("No data to display").css("color", "#000000");

                    
                }


            }

        }
    });


    $.ajax({
        url: "/admin/ajax/ajax_dashboard.php",
        method: "post",
        data: {
            data: "actions"
        },
        success: function (response) {

            let event_list = "";

            if (response['status'] === "success") {


                if (response['data'].length > 0) {


                    for (let kindex = response['data'].length; kindex >= 0; kindex--) {

                        if (response['data'][kindex] !== undefined) {

                            event_list += "<li>" + "<div class='timeline-icon bg-primary'>" + "<i class='feather icon-plus font-medium-2 align-middle'></i>" + "</div>" + "<div class='timeline-info'>" + "<p class='font-weight-bold mb-0'>" + response['data'][kindex]['message'] + "</p>" + "</div>" + "<small class='text-muted'>" + response['data'][kindex]['date'] + "</small>" + "</li>"

                        }

                    }


                } else {

                    event_list = "No Event Recorded";

                }


                $("ul.activity-timeline").html(event_list);


            }


        }
    });


});
