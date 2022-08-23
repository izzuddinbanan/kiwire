
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
    pulldata_box();

    $('#search').on("click", pull_data);
    $('#search').on("click", pulldata_box);

});


function pulldata_box() {

    $.ajax({
        url: "ajax/ajax_report_campaign_impression_summary.php?action=total_inBox",
        method: "POST",
        data: {
            "startdate": $('#startdate').val(),
            "enddate": $('#enddate').val()
        },
        success: function (data) {

            if (data['status'] === "success") {

                if($.fn.dataTable.isDataTable('.table-data')){

                    $(".table-data").DataTable().destroy();

                }

                if (data['data'].length > 0) {

                    for (let x = 0; x < data['data'].length; x++) {

                        var total_unique = data['data'][x]['uniq'];
                        var avg_unique = Math.round(data['data'][x]['uniq'] / data['data'][x]['count_uniq']);
                        var total_impress = data['data'][x]['impression'];
                        var avg_impress = Math.round(data['data'][x]['impression'] / data['data'][x]['count_impress']);
                        

                    }

                    document.getElementById("totalimp").innerHTML = total_impress;
                    document.getElementById("avgimp").innerHTML = avg_impress;
                    document.getElementById("totaluniq").innerHTML = total_unique;
                    document.getElementById("avguniq").innerHTML = avg_unique;

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



function pull_data() {

    $.ajax({
        url: "ajax/ajax_report_campaign_coupon_impression_summary.php",
        method: "POST",
        data: {
            "startdate": $('#startdate').val(),
            "enddate": $('#enddate').val(),
            "action": "get_all"
        },
        success: function (data) {

            if (data['status'] === "success") {

                if($.fn.dataTable.isDataTable('.table-data')){

                    $(".table-data").DataTable().destroy();

                }
                let table_str = "";

                if (data['data'].length > 0) {

                    let chart_date = [];
                    let chart_uniq = [];
                    let chart_impression = [];

                    for (let x = 0; x < data['data'].length; x++) {

                        // table_str += "<tr>";
                        // table_str += "<td>" + (x + 1) + "</td>";
                        // table_str += "<td>" + Date.parse(data['data'][x]['xreport_date']).toString("d-MMM-yyyy") + "</td>";
                        // table_str += "<td>" + data['data'][x][''] + "</td>";
                        // table_str += "<td>" + data['data'][x][''] + "</td>";
                        // table_str += "<td><button data-report-date='" + data['data'][x]['xreport_date'] + "' class='btn btn-primary btn-sm fa fa-search btn-report-details'></button></td>";
                        // table_str += "</tr>";

                        chart_uniq.push([data['data'][x]['xreport_date'], data['data'][x]['']]);
                        chart_impression.push([data['data'][x]['xreport_date'], data['data'][x]['']]);

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
                            name: "Unique",
                            data: chart_uniq
                        },{
                            name: "Impression",
                            data: chart_impression
                        }],

                    };


                    var report_chart = new ApexCharts(document.querySelector("#data-chart"), report_chart_option);

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


function get_report_per_date(report_date){


    if (report_date.length > 0){

        $.ajax({
            url: "/admin/ajax/ajax_report_campaign_coupon_impression_summary.php",
            method: "get",
            data: {
                report_date: report_date,
                action: "get_detail"
            },
            success: function (response) {

                if (response['status'] === "success"){

                    if($.fn.dataTable.isDataTable('.table-detail')){

                        $(".table-detail").DataTable().destroy();

                    }


                    let chart_date = [];
                    let chart_uniq = [];
                    let chart_impression = [];

                    let table_str = "";

                    for (let x = 0; x < response['data'].length; x++){

                        table_str += "<tr>";
                        table_str += "<td>" + response['data'][x]['xreport_date'] + "</td>";
                        table_str += "<td>" + response['data'][x]['uniq'] + "</td>";
                        table_str += "<td>" + response['data'][x]['impression'] + "</td>";
                        table_str += "</tr>";

                        chart_date.push(response['data'][x]['xreport_date']);
                        chart_uniq.push(response['data'][x]['uniq']);
                        chart_impression.push(response['data'][x]['impression']);


                    }


                    var detail_chart_option = {

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
                            name: "Unique",
                            data: chart_uniq
                        },{
                            name: "Impression",
                            data: chart_impression
                        }],

                    };


                    var detail_chart = new ApexCharts(document.querySelector("#detail-chart"), detail_chart_option);

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
                        "fnDrawCallback": function(){
                            if($('.dataTables_filter').find('input').hasClass('form-control-sm')){
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