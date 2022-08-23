
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
            categories: ['2020-01-01', '2020-01-03', '2020-01-06', '2020-01-07', '2020-01-10'],
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
            name: "Bandwidth Usage",
            data: ['10', '20', '20', '10', '10'],
        }],

    };


    var report_chart = new ApexCharts(document.querySelector("#data-chart"), report_chart_option);

    report_chart.render();


})