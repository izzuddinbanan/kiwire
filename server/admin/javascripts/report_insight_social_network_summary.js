
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


var social_chart, gender_chart,age_chart;


function pull_data() {

    $.ajax({

        url: "ajax/ajax_report_insight_social_network_summary.php?action=get_by_date_social",
        method: "POST",
        data: {
            "startdate": $('#startdate').val(),
            "enddate": $('#enddate').val()
        },

        success: function (response) {

            if (response['status'] === "success") {

                if ($.fn.dataTable.isDataTable('.table-data-1')) {

                    $(".table-data-1").DataTable().destroy();

                }


                let social_summary = "";
                let social_type = [], social_count = [], social_gender_type = [], social_gender = [],social_age_group = [], social_age = [];

                for(let kindex in response['data']['type']){

                    social_summary += "<tr>";
                    social_summary += "<td>" + (parseInt(kindex) + 1) + "</td>";
                    social_summary += "<td>" + capitalize(response['data']['type'][kindex]['source']) + "</td>";
                    social_summary += "<td>" + response['data']['type'][kindex]['count'] + "</td>";
                    social_summary += "</tr>";

                    social_type.push(capitalize(response['data']['type'][kindex]['source']));
                    social_count.push(parseInt(response['data']['type'][kindex]['count']));

                }


                $(".table-data-1 > tbody").html(social_summary);

                $(".table-data-1").dataTable({
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



                for(let kindex in response['data']['gender']){


                    if (response['data']['gender'][kindex]['gender'] == "" || response['data']['gender'][kindex]['gender'] == null) {

                        response['data']['gender'][kindex]['gender'] = "Undefined";
                    
                    }


                    social_gender_type.push(capitalize(response['data']['gender'][kindex]['gender']));
                    social_gender.push(parseInt(response['data']['gender'][kindex]['count']));
   
                }

            

                
                for(let kindex in response['data']['age']){


                    if (response['data']['age'][kindex]['age_group'] == null || response['data']['age'][kindex]['age_group'] == "") {

                        response['data']['age'][kindex]['age_group'] = "Undefined";

                    }


                    social_age_group.push(capitalize(response['data']['age'][kindex]['age_group']));
                    social_age.push(parseInt(response['data']['age'][kindex]['count']));

                }


                var social_chart_option = {
                    chart: {
                        id: 'pie-class',
                        type: 'pie',
                        height: 450
                    },
                    colors: [$primary_light, $danger_light, $success_light, $warning_light],
                    labels: social_type,
                    series: social_count,
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


                if (social_chart !== undefined) {

                    social_chart.destroy();

                }

                social_chart = new ApexCharts(document.querySelector("#pie-chart-social"), social_chart_option);

                social_chart.render();



                var gender_chart_option = {

                    chart: {
                        id: 'pie-class',
                        type: 'pie',
                        height: 450
                    },
                    colors: [$primary_light, $danger_light, $success_light, $warning_light],
                    labels: social_gender_type,
                    series: social_gender,
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


                if (gender_chart !== undefined) {

                    gender_chart.destroy();

                }

                gender_chart = new ApexCharts(document.querySelector("#pie-chart-gender"), gender_chart_option);

                gender_chart.render();


                var age_chart_option = {
                    chart: {
                        id: 'pie-class',
                        type: 'pie',
                        height: 450
                    },
                    colors: [$primary_light, $danger_light, $success_light, $warning_light],
                    labels: social_age_group,
                    series: social_age,
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


                if (age_chart !== undefined) {

                    age_chart.destroy();

                }

                age_chart = new ApexCharts(document.querySelector("#pie-chart-age"), age_chart_option);

                age_chart.render();



            }

        }

    });

}


function capitalize(string) {
    
    if(string) return string.toUpperCase(); 

}