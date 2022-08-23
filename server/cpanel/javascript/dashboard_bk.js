$(document).ready(function(){
    mainChart();
});


function mainChart(){

    var color1 = App.color.primary;
    var color2 = tinycolor( App.color.primary ).lighten( 13 ).toString();

    var data = null;
    var data2 = null;

    var plot_statistics = $.plot("#main-chart",
        [
            {
                data: data,
                canvasRender: true
            },
            {
                data: data2,
                canvasRender: true
            }
        ], {
            series: {
                lines: {
                    show: true,
                    lineWidth: 0,
                    fill: true,
                    fillColor: { colors: [{ opacity: 1 }, { opacity: 1 }] }
                },
                fillColor: "rgba(0, 0, 0, 1)",
                shadowSize: 0,
                curvedLines: {
                    apply: true,
                    active: true,
                    monotonicFit: true
                }
            },
            legend:{
                show: false
            },
            grid: {
                show: true,
                margin: {
                    top: 20,
                    bottom: 0,
                    left: 0,
                    right: 0,
                },
                labelMargin: 0,
                minBorderMargin: 0,
                axisMargin: 0,
                tickColor: "rgba(0,0,0,0.05)",
                borderWidth: 0,
                hoverable: true,
                clickable: true
            },
            colors: [color1, color2],
            xaxis: {
                mode: 'time',
                //tickFormatter: function(){
                //   return '';
                //},
                autoscaleMargin: 0,
                ticks: 10,
                tickDecimals: 0,
                tickLength: 10
            },
            yaxis: {
                tickFormatter: function(x, y){
                    return x + '  MB';
                },
                //autoscaleMargin: 0.01,
                ticks: 3,
                tickDecimals: 0
            }
        });

    $('[data-color="main-chart-color1"]').css({'background-color':color1});
    $('[data-color="main-chart-color2"]').css({'background-color':color2});

}