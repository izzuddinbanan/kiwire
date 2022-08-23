$(document).ready(function () {

    pull_data();

    $('#search').on("click", pull_data);

});


function pull_data() {

    $.ajax({
        url: "ajax/ajax_report_campaign_coupon_click_summary.php?action=get_by_date",
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

                let table_str = "";

                // var chart_label = [], chart_impression_uniq = [];
                // var chart_label = [], chart_impression_impression = [];

                if (data['data'].length > 0) {

                    for (let x = 0; x < data['data'].length; x++) {

                        table_str += "<tr>";

                        table_str += "<td>" + (x + 1) + "</td>";

                        var expiryDate = new Date(data['data'][x]['report_date']);

                        var date = expiryDate.getDate();
                        var month = expiryDate.getMonth();
                        var year = expiryDate.getFullYear();

                        var date = ("0" + date).slice(-2) + "-" + (month + 1) + "-" + year;

                        table_str += '<td>' + date + "</td>";

                        table_str += "<td>" + data['data'][x]['uniq'] + "</td>";
                        table_str += "<td>" + data['data'][x]['impression'] + "</td>";

                        table_str += "</tr>";

                        chart_label.push((date == "" ? "Unknown" : date));

                        // chart_impression_uniq.push(parseInt(data['data'][x]['uniq']));
                        // chart_impression_impression.push(parseInt(data['data'][x]['impression']));
                    }

                    // Initialization
                    var myChart = echarts.init(document.getElementById('line-chart'));
                    // Chart options
                    var option = {
                        responsive: true,
                        title: {
                            text: 'Coupon Click Summary',
                            textStyle: {
                                color: '#000',
                                fontSize: '16'
                            },
                            subtextStyle: {
                                color: '#90979c',
                                fontSize: '12'
                            }
                        },
                        exportFileName: "Coupon Click Summary",  //Give any name accordingly
                        exportEnabled: true,
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {
                                label: {
                                    backgroundColor: '#6a7985'
                                }
                            }
                        },
                        legend: {
                            textStyle: {
                                color: '#000'
                            },
                            bottom: true,
                            data: [
                                {
                                    name: 'Unique',
                                    icon: 'rect'
                                }, {
                                    name: 'Total',
                                    icon: 'rect'
                                }]

                        },
                        xAxis: {
                            data: ['2020-01-03', '2020-01-05', '2020-01-06', '2020-01-07'],
                            boundaryGap: false,
                        },
                        yAxis: [
                            {
                                type: 'value',
                                axisLabel: {
                                    formatter: '{value} Users'
                                },
                                axisLine: {
                                    lineStyle: {
                                        type: 'solid',
                                        color: 'rgba(89, 102, 119, 0.6)'
                                    }
                                },
                                splitLine: {
                                    lineStyle: {
                                        type: 'dashed',
                                        color: '#455366'
                                    }
                                }
                            }
                        ],
                        series: [
                            {
                                name: 'Unique',
                                type: 'line',
                                smooth: true,
                                // areaStyle: {},
                                // color: '#6444D3',
                                data: ['0', '0', '0', '0']
                            },{
                                name: 'Total',
                                type: 'line',
                                smooth: true,
                                // areaStyle: {},
                                // color: '#6444D3',
                                data: ['0', '0', '0', '0']
                            }],
                        textStyle: {
                            color: '#000'
                        },
                        responsive: true
                    }

                    myChart.setOption(option);

                } 
                // else {
                //     table_str += '<tr><td colspan="5" align="center">No data available in table</td></tr>';
                // }


                $(".table-data>tbody").html(table_str);

                $(".table-data").dataTable({
                    dom: dt_position,
                    pageLength: dt_page,
                    buttons: dt_btn,
                    language: {
                        searchPlaceholder: "Search Records",
                        search: "",
                    },
                    "fnDrawCallback": function() {
                        if($('.dataTables_filter').find('input').hasClass('form-control-sm')){
                            $('.dataTables_filter').find('input').removeClass('form-control-sm')
                        }

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
