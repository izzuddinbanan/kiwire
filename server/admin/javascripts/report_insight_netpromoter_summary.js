$(document).ready(function () {

    getSum();

    pull_data();
    pull_chart();
    positive_count();
    negative_count();

    $('#search').on("click", pull_data);
    $('#search').on("click", pull_chart);
    $('#search').on("click", positive_count);
    $('#search').on("click", negative_count);
   
});




function getSum(){
    
    $.ajax({

        url: "ajax/ajax_report_insight_netpromoter_summary.php",
        method: "GET",
        data: { action: "getSum", 
                "startdate" : $('#startdate').val(),
                "enddate" : $('#enddate').val() },

    }).done(function(data){

        var is_json = true;

        try{

            data = JSON.parse(data);

        }catch(e){

            is_json = false;

        }

        if(is_json) {

            // for(var key in data) updateCounterUp('#count-'+key, data[key]);
            
            // updGenerateChart({pos: data.positive, neg:data.negative});





            //----------- Pie chart feedback ---------//

            var pieChart = echarts.init(document.getElementById('pie-chart-feedback'));

            var pieChartoption = {
                tooltip : {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                    data:  {pos: data.positive, neg:data.negative}
                },
                series : [
                    {
                        name: 'Feedback',
                        type: 'pie',
                        radius : '55%',
                        center: ['50%', '60%'],
                        color: ['#57167E', '#9B3192', '#EA5F89', '#F7B7A3', '#FFF1C9'],
                        data:  {pos: data.positive, neg:data.negative},

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

            //------------ End pie chart -----------//

        }
       

    });
}






function pull_data() {

    $.ajax({
        url: "ajax/ajax_report_insight_netpromoter_summary.php?action=get_by_date",
        method: "POST",
        data: {
            "startdate" : $('#startdate').val(),
            "enddate" : $('#enddate').val()
        },
        success: function (data) {

            if (data['status'] === "success") {

                if($.fn.dataTable.isDataTable('.table-data')){

                    $(".table-data").DataTable().destroy();

                }

                let table_str = "";

                if (data['data'].length > 0) {

                    for (let x = 0; x < data['data'].length; x++) {

                        table_str += "<tr>";

                        table_str += "<td>" + (x + 1) + "</td>";
                        table_str += "<td>" + data['data'][x]['username'] + "</td>";
                        table_str += "<td>" + data['data'][x]['score'] + "</td>";
                        table_str += "<td>" + (data['data'][x]['score_type']=="detractor" ? "<span class='badge badge-danger'><i class='fa fa-frown-o font-medium-5'></i></span>":"<span class='badge badge-success'><i class='fa fa-smile-o font-medium-5'></i></span>") + "</td>";

                        table_str += "<td>" + data['data'][x]['comment'] + "</td>";
    
                        var createDate = new Date(data['data'][x]['created_at']);
    
                        var date = createDate.getDate();
                        var month = createDate.getMonth();
                        var year = createDate.getFullYear();
    
                        var dateFormat = ("0" + date).slice(-2) + "-" + (month + 1)  + "-" + year;
    
                        table_str += '<td>' + dateFormat + "</td>";

                        table_str += "<td>" + data['data'][x]['magnitude'] + "</td>";
    
                        table_str += "</tr>";

                    }
                }
                // else {

                //     table_str += '<tr><td colspan="12" align="center">No data available in table</td></tr>';
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


function pull_chart() {

    $.ajax({
        url: "ajax/ajax_report_insight_netpromoter_summary.php?action=get_by_date_feedback",
        method: "POST",
        data: {
            "startdate" : $('#startdate').val(),
            "enddate" : $('#enddate').val()
        },
        success: function (data) {

            if (data['status'] === "success") {

                let table_str = "";

                var chart_data_dbrand = [];

                if (data['data'].length > 0) {

                    for (let x = 0; x < data['data'].length; x++) {

                        chart_data_dbrand.push({"value": parseInt(data['data'][x]['count_score_type']), "name": capitalizeFirstLetter((data['data'][x]['score_type']=="" ? "Unknown" : data['data'][x]['score_type']))});


                    }

                    //----------- Pie chart feedback ---------//

                    var pieChart = echarts.init(document.getElementById('pie-chart-feedback'));

                    var pieChartoption = {
                        tooltip : {
                            trigger: 'item',
                            formatter: "{a} <br/>{b} : {c} ({d}%)"
                        },
                        legend: {
                            orient: 'vertical',
                            left: 'left',
                            data:  chart_data_dbrand
                        },
                        series : [
                            {
                                name: 'Feedback',
                                type: 'pie',
                                radius : '55%',
                                center: ['50%', '60%'],
                                color: ['#57167E', '#9B3192', '#EA5F89', '#F7B7A3', '#FFF1C9'],
                                data:  chart_data_dbrand,

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

                }
                else {

                    table_str += '<tr><td colspan="12" align="center">No data available in table</td></tr>';
                }

                $(".table-data-1>tbody").html(table_str);
                $(".table-data-1").DataTable();

            } else {

                swal("Error", data['message'], "error");

            }

        },
        
        error: function () {

            swal("Error", "There is unexpected error. Please try again.", "error");

        }
    })
}


function positive_count() {

    $.ajax({
        url: "ajax/ajax_report_insight_netpromoter_summary.php?action=positive_count",
        method: "POST",
        data: {
            "startdate" : $('#startdate').val(),
            "enddate" : $('#enddate').val()
        },
        success: function (data) {

            if (data['status'] === "success") {

                if (data['data'].length > 0) {

                    for (let x = 0; x < data['data'].length; x++) {

                        var positive_count = data['data'][x]['positive_count'];

                    }

                    document.getElementById("count-positive").innerHTML = positive_count;

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


function negative_count() {

    $.ajax({
        url: "ajax/ajax_report_insight_netpromoter_summary.php?action=negative_count",
        method: "POST",
        data: {
            "startdate" : $('#startdate').val(),
            "enddate" : $('#enddate').val()
        },
        success: function (data) {

            if (data['status'] === "success") {

                if (data['data'].length > 0) {

                    for (let x = 0; x < data['data'].length; x++) {

                        var negative_count = data['data'][x]['negative_count'];
                        
                    }

                    document.getElementById("count-negative").innerHTML = negative_count;
``
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


function capitalizeFirstLetter(string) {

    return string.charAt(0).toUpperCase() + string.slice(1);
}