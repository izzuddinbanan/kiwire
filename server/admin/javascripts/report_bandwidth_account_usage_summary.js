
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

});


var report_chart;

function pull_data() {


    if ($.fn.dataTable.isDataTable('.table-data')) {

        $(".table-data").DataTable().destroy();

    }

    if (report_chart !== undefined) {

        report_chart.destroy();

    }


    $.ajax({
        url: "ajax/ajax_report_bandwidth_account_usage_summary.php",
        method: "POST",
        data: {
            "startdate": $('#startdate').val(),
            "enddate": $('#enddate').val(),
            "username": $("#username").val(),
            "action": "get_all"
        },
        success: function (data) {

            if (data['status'] === "success") {


                $("#report_graph").css("display", "block");

                let table_str = "";

                if (data['data'] != null && data['data'].length > 0) {

                    let chart_date = [];
                    let chart_download = [];
                    let chart_upload = [];

                    for (let kindex in data['data']) {

                        if (data['data'][kindex]['session_time'] == '0') {
                            data['data'][kindex]['session_time'] = 1;

                        }
                        table_str += "<tr>";
                        table_str += "<td>" + (parseInt(kindex) + 1) + "</td>";
                        table_str += "<td>" + data['data'][kindex]['xreport_date'] + "</td>";
                        table_str += "<td>" + parseFloat(data['data'][kindex]['quota_out']).toFixed(3) + "</td>";
                        table_str += "<td>" + parseFloat(data['data'][kindex]['quota_in']).toFixed(3) + "</td>";
                        table_str += "<td>" + ((parseFloat(data['data'][kindex]['quota_out']) * 8) / parseFloat(data['data'][kindex]['session_time'])).toFixed(3) + "</td>";
                        table_str += "<td>" + ((parseFloat(data['data'][kindex]['quota_in']) * 8) / parseFloat(data['data'][kindex]['session_time'])).toFixed(3) + "</td>";

                        table_str += "</tr>";

                        chart_date.push(data['data'][kindex]['xreport_date']);
                        chart_download.push(parseFloat(data['data'][kindex]['quota_out']).toFixed(3));
                        chart_upload.push(parseFloat(data['data'][kindex]['quota_in']).toFixed(3));



                    }

                    var report_chart_option = {

                        chart: {
                            id: "bandwidth-account-usage",
                            height: 450,
                            toolbar: { show: false },
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
                            tickAmount: 5,
                            labels: {
                                style: {
                                    color: $strok_color,
                                },
                                formatter: function (val) {
                                    return val;
                                }
                            }
                        },
                        tooltip: {
                            x: { show: false }
                        },
                        series: [{
                            name: "Download",
                            data: chart_download
                        }, {
                            name: "Upload",
                            data: chart_upload
                        }],

                    };


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
