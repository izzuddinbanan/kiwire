$(window).on("load", function () {


    var pieChart = echarts.init(document.getElementById('pie-chart-nps'));

    var pieChartoption = {
        tooltip: {
            trigger: 'item',
            formatter: "{a} <br/>{b} : {c} ({d}%)"
        },
        legend: {
            orient: 'vertical',
            left: 'left',
            data: ['Negative', 'Neutral', 'Positive']
        },
        series: [
            {
                name: 'Social Network',
                type: 'pie',
                radius: '55%',
                center: ['50%', '60%'],
                colors: [$primary_light, $danger_light, $success_light, $warning_light],
                data: [{ value: 3, name: 'Negative' },  { value: 2, name: 'Neutral' }, { value: 5, name: 'Positive' }],
                itemStyle: {
                    emphasis: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                    }
                }
            }
        ],
    };

    pieChart.setOption(pieChartoption);



    var pieChart = echarts.init(document.getElementById('pie-chart-fb'));

    var pieChartoption = {
        tooltip: {
            trigger: 'item',
            formatter: "{a} <br/>{b} : {c} ({d}%)"
        },
        legend: {
            orient: 'vertical',
            left: 'left',
            data: ['Negative', 'Positive']
        },
        series: [
            {
                name: 'Social Network',
                type: 'pie',
                radius: '55%',
                center: ['50%', '60%'],
                colors: [$primary_light, $danger_light, $success_light, $warning_light],
                data: [{ value: 3, name: 'Negative' }, { value: 5, name: 'Positive' }],
                itemStyle: {
                    emphasis: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                    }
                }
            }
        ],
    };

    pieChart.setOption(pieChartoption);


})