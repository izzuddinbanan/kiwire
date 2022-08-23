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


    var class_chart_1, class_chart_2;


    if ((total_voucher + total_user + total_simcard) > 0) {

        var class_chart_1_option = {
            chart: {
                id: 'pie-class-1',
                type: 'pie',
                height: 375
            },
            colors: [$primary_light, $danger_light, $success_light, $warning_light],
            labels: ['Number of Vouchers', 'Number of Users', 'Number of Sim Card'],
            series: [total_voucher, total_user, total_simcard],
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


        if (class_chart_1 !== undefined) {

            class_chart_1.destroy();

        }

        class_chart_1 = new ApexCharts(document.querySelector("#pie-chart-1"), class_chart_1_option);

        class_chart_1.render();


        var class_chart_2_option = {
            chart: {
                id: 'pie-class-2',
                type: 'pie',
                height: 375
            },
            colors: [$primary_light, $danger_light, $success_light, $warning_light],
            labels: ['Active Accounts', 'Expired Accounts', 'Suspended Accounts'],
            series: [total_activated, total_expired, total_user_suspend],
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


        if (class_chart_2 !== undefined) {

            class_chart_2.destroy();

        }

        class_chart_2 = new ApexCharts(document.querySelector("#pie-chart-2"), class_chart_2_option);

        class_chart_2.render();


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


    } else {


        swal("No Accounts", "You have no account in your system", "error");

    }


});