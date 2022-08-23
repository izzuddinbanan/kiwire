
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

    // $("#filter-btn").on("click", function (){

    //     $("#filter_modal").modal();

    // });


    // $("#filter-data").on("click", function (){

    //     pull_data();

    //     $("#filter_modal").modal("hide");


    // });


});


var class_chart, brand_chart;


function pull_data() {

    $.ajax({
        url: "ajax/ajax_report_insight_deviceinfo.php",
        method: "POST",
        data: {
            "startdate": $('#startdate').val(),
            "enddate": $('#enddate').val(),
            "action": "get_all"
        },
        success: function (data) {

            if (data['status'] === "success") {

                if($.fn.dataTable.isDataTable('.table-data-1')){

                    $(".table-data-1").DataTable().destroy();

                }

                if($.fn.dataTable.isDataTable('.table-data-2')){

                    $(".table-data-2").DataTable().destroy();

                }

                let table_str = "";


                // data for class

                let class_value = [];
                let class_data = [];

                for (let kindex in data['data']['class']){

                    table_str += "<tr>";
                    table_str += "<td>" + (parseInt(kindex) + 1) + "</td>";
                    table_str += "<td>" + data['data']['class'][kindex]['value'] + "</td>";
                    table_str += "<td>" + data['data']['class'][kindex]['count'] + "</td>";
                    table_str += "</tr>";

                    class_value.push(data['data']['class'][kindex]['value']);
                    class_data.push(parseInt(data['data']['class'][kindex]['count']));

                }

                $(".table-data-1 > tbody").html(table_str);


                $(".table-data-1").dataTable({
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



                // data for brand

                table_str = "";

                let brand_value = [];
                let brand_data = [];

                for (let kindex in data['data']['brand']){

                    table_str += "<tr>";
                    table_str += "<td>" + (parseInt(kindex) + 1) + "</td>";
                    table_str += "<td>" + data['data']['brand'][kindex]['value'] + "</td>";
                    table_str += "<td>" + data['data']['brand'][kindex]['count'] + "</td>";
                    table_str += "</tr>";

                    brand_value.push(data['data']['brand'][kindex]['value']);
                    brand_data.push(parseInt(data['data']['brand'][kindex]['count']));

                }


                $(".table-data-2 > tbody").html(table_str);

                $(".table-data-2").dataTable({
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


                var class_chart_option = {
                    chart: {
                        id: 'pie-class',
                        type: 'pie',
                        height: 450
                    },
                    colors: [$primary_light, $danger_light, $success_light, $warning_light],
                    labels: class_value,
                    series: class_data,
                    legend: {
                        itemMargin: {
                            horizontal: 2
                        },
                    },
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 350,
                                height: 350
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }]
                };


                if (class_chart !== undefined) {

                    class_chart.destroy();

                }

                class_chart = new ApexCharts(document.querySelector("#pie-chart-dtype"), class_chart_option);

                class_chart.render();


                var brand_chart_option = {
                    chart: {
                        id: 'pie-brand',
                        type: 'pie',
                        height: 450
                    },
                    colors: [$primary_light, $danger_light, $success_light, $warning_light],
                    labels: brand_value,
                    series: brand_data,
                    legend: {
                        itemMargin: {
                            horizontal: 2
                        },
                    },
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 350,
                                height: 350
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }]
                };


                if (brand_chart !== undefined) {

                    brand_chart.destroy();

                }

                brand_chart = new ApexCharts(document.querySelector("#pie-chart-dbrand"), brand_chart_option);

                brand_chart.render();



            } else {

                swal("Error", data['message'], "error");

            }

        },
        error: function () {

            swal("Error", "There is unexpected error. Please try again.", "error");

        }


    })

}


