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


var creator_chart;


function pull_data() {

    $.ajax({
        url: "ajax/ajax_report_account_creator.php",
        method: "POST",
        data: {
            "startdate": $('input[name=startdate]').val(),
            "enddate": $('input[name=enddate]').val()
        },
        success: function (data) {

            if (data['status'] === "success") {

                if ($.fn.dataTable.isDataTable('.table-data')) {

                    $(".table-data").DataTable().destroy();

                }

                let table_str = "";


                // data for class

                let creator_value = [];
                let creator_data = [];

                if (data['data'].length >= 0) {

                    for (let x = 0; x < data['data'].length; x++) {

                        table_str += "<tr>";
                        table_str += "<td>" + (x + 1) + "</td>";
                        table_str += "<td>" + data['data'][x]['creator'] + "</td>";
                        table_str += "<td>" + data['data'][x]['account'] + "</td>";
                        table_str += "</tr>";

                        creator_value.push(data['data'][x]['creator']);
                        creator_data.push(parseInt(data['data'][x]['account']));


                    }


                } else {

                    table_str += '<tr><td colspan="3" align="center">No data available in table</td></tr>';

                }


                $(".table-data>tbody").html(table_str);

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



                var creator_chart_option = {
                    chart: {
                        id: 'pie-class',
                        type: 'pie',
                        height: 450
                    },
                    colors: [$primary_light, $danger_light, $success_light, $warning_light],
                    labels: creator_value,
                    series: creator_data,
                    legend: {
                        itemMargin: {
                            horizontal: 2
                        },
                    },
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: '100%',
                                height: '300'
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }]
                };


                if (creator_chart !== undefined) {

                    creator_chart.destroy();

                }

                creator_chart = new ApexCharts(document.querySelector("#pie-chart"), creator_chart_option);

                creator_chart.render();



            } else {

                swal("Error", data['message'], "error");

            }

        },
        error: function () {

            swal("Error", "There is unexpected error. Please try again.", "error");

        }


    })

}

