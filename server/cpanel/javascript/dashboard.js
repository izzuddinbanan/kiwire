var $primary = '#7367F0';
var $success = '#28C76F';
var $danger = '#EA5455';
var $warning = '#FF9F43';
var $info = '#0DCCE1';
var $primary_light = '#8F80F9';
var $warning_light = '#FFC085';
var $danger_light = '#f29292';
var $info_light = '#1edec5';
var $strok_color = '#b9c3cd';
var $label_color = '#e7eef7';
var $white = '#fff';
var $grey = '#f0e1e1';

var themeColors = [$danger_light, $success, $danger, $warning, $info];


$(document).ready(function () {



  $.ajax({
    url: "ajax/ajax_dashboard.php",
    method: "post",
    data: {
      action: "overall_usage"
    },
    success: function (response) {

      if (response['status'] === "success") {


        if (response['data']['auth']['quota_in'] > 0 && response['data']['auth']['quota_out'] > 0) {

          $(".total-quota").html(response['data']['total_quota'] + " MB");
          $(".balance-quota").html(response['remaining_quota'] + " MB");
          // $(".remaining-time").html(response['remaining_time'] + " MINS");
          $(".remaining-time").html(response['remaining_time']);


        } else {

          $(".total-quota").html("0.0");
          $(".balance-quota").html("0.0");
          $(".remaining-time").html("0.0");

        }

        quota_percentage = response['percentage_quota'];

        if (response['data']['profile']['type'] == "free") {

          quota_used = "Unlimited";

        } else {

          quota_used = response['quota_used'] + " MB Used";

        }


        var supportChartoptions = {
          chart: {
            height: 270,
            type: 'radialBar',
          },
          plotOptions: {
            radialBar: {
              size: 150,
              startAngle: -150,
              endAngle: 150,
              offsetY: 20,
              hollow: {
                size: '65%',
              },
              track: {
                background: $grey,
                strokeWidth: '100%',

              },
              dataLabels: {
                value: {
                  offsetY: 30,
                  color: '#99a2ac',
                  fontSize: '2rem'
                }
              }
            },
          },
          colors: [$danger],
          fill: {
            type: 'gradient',
            gradient: {
              enabled: true,
              shade: 'dark',
              type: 'horizontal',
              shadeIntensity: 0.5,
              gradientToColors: [$primary],
              inverseColors: true,
              opacityFrom: 1,
              opacityTo: 1,
              stops: [0, 100]
            },
          },
          stroke: {
            dashArray: 8
          },
          series: [quota_percentage],
          labels: [quota_used],

        }

        var supportChart = new ApexCharts(
          document.querySelector("#support-tracker-chart"),
          supportChartoptions
        );

        supportChart.render();


      }

    }

  });


  $.ajax({
    url: "ajax/ajax_dashboard.php",
    method: "post",
    data: {
      action: "monthly_usage"
    },
    success: function (response) {

      if (response['status'] === "success") {


        let quota_used = [];
        let month = [];


        for (let x = 0; x < response['data'].length; x++) {

          let quota_usage = parseFloat(response['data'][x]['quota_used'] / (1024 * 1024)).toFixed(2);

          quota_used.push(quota_usage);

          month.push(response['data'][x]['monthly']);

        } 


        var columnChartOptions = {
          chart: {
            height: 280,
            type: 'bar',
          },
          colors: themeColors,
          plotOptions: {
            bar: {
              horizontal: false,
              // endingShape: 'rounded',
              columnWidth: '20%',
            },
          },
          dataLabels: {
            enabled: false
          },
          stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
          },
          series: [{
            name: 'Quota Used',
            data: quota_used
          }],
          legend: {
            offsetY: -10
          },
          xaxis: {
            categories: month,
          },
          yaxis: {
            title: {
              text: ' MB'
            }
          },
          fill: {
            opacity: 1

          },
          tooltip: {
            y: {
              formatter: function (val) {
                return + val + " MB"
              }
            }
          }
        }

        var columnChart = new ApexCharts(
          document.querySelector("#column-chart"),
          columnChartOptions
        );

        columnChart.render();

      } else {

        $("#column-chart").html("[ NO DATA AVAILABLE ]").css("color", "#000000");

      }


    }

  });


  $.ajax({
    url: "ajax/ajax_dashboard.php",
    method: "post",
    data: {
      action: "login_activities"
    },
    success: function (response) {

      let event_list = "";

      if (response['status'] === "success") {


        if (response['data'].length > 0) {


          for (let kindex = response['data'].length; kindex >= 0; kindex--) {

            if (response['data'][kindex] !== undefined) {

              event_list += "<li class='latest'>" + "<div class='user-timeline-title' style='font-size:15px; font-weight:400'>" + response['data'][kindex]['message'] + "</div>" + "<div class='user-timeline-description'>" + response['data'][kindex]['date'] + "</div>" + "</li>"

            }

          }


        } else {

          event_list = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; No Event Recorded";

        }


        $("ul.user-timeline").html(event_list);


      }

    }
    
  });


});

function convert_seconds_to_days(seconds) {

  let days = Math.floor(seconds / 86400);
  seconds -= days * 86400;

  let hours = Math.floor(seconds / 3600) % 24;
  seconds -= hours * 3600;

  let minutes = Math.floor(seconds / 60) % 60;
  seconds -= minutes * 60;

  var seconds = seconds % 60;


  // formatting

  days = (days < 10 ? "0" + days : days);
  hours = (hours < 10 ? "0" + hours : hours);
  minutes = (minutes < 10 ? "0" + minutes : minutes);
  seconds = (seconds < 10 ? "0" + seconds : seconds);


  return days + ":" + hours + ":" + minutes + ":" + seconds;

}



function format_number(number) {

  if (typeof number == "string") {

    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");

  }

}

function numberWithCommas(x) {

  return x.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");

}