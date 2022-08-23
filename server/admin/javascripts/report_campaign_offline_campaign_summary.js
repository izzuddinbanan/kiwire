
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


var current_campaign = "";

$(document).ready(function () {


    get_data_for_all();

    $('#search').on("click", get_data_for_all);

    $("#filter-btn").on("click", function () {

        $("#filter_modal").modal();

    });


    $("#filter-data").on("click", function () {


        get_data_for_all();

        $("#filter_modal").modal("hide");


    });


    $(".btn-close").on("click", function () {

        $("#view-detail").modal("toggle");

    });


    // filter by zone/project

    $('input:radio').change(function () {

        var val = $('input:radio:checked').val();

        if (val == 'Zone') {

            $('.zone').css('display', 'block')
            $('.project').css('display', 'none')


        } else {

            $('.project').css('display', 'block')
            $('.zone').css('display', 'none')

        }

    });


    // reset previous dropdown value before choose another

    $("#search").click(function (e) {
        $("select").val("");
    });

    //end


});


function get_data_for_all() {

    $.ajax({
        url: "/admin/ajax/ajax_report_campaign_offline_campaign_summary.php",
        method: "post",
        data: {
            date_start: $("input[name=startdate]").val(),
            date_end: $("input[name=enddate]").val(),
            zone: $("select[name=zone]").val(),
            project: $("select[name=project]").val(),
            action: "get_all_data"
        },
        success: function (response) {

            if (response['status'] === "success") {

                let campaign_offline_list = "";

                if (response['data'] !== null) {


                    if ($.fn.DataTable.isDataTable("#campaign-offline-list")) {

                        $('#campaign-offline-list').DataTable().clear().destroy();

                    }


                    for (let kindex in response['data']) {

                        campaign_offline_list += "<tr>";
                        campaign_offline_list += "<td>" + (parseInt(kindex) + 1) + "</td>";
                        campaign_offline_list += "<td>" + response['data'][kindex]['name'] + "</td>";
                        campaign_offline_list += "<td>" + response['data'][kindex]['start'] + "</td>";
                        campaign_offline_list += "<td>" + response['data'][kindex]['end'] + "</td>";
                        campaign_offline_list += "<td>" + response['data'][kindex]['trigger'] + "</td>";
                        campaign_offline_list += "<td>" + response['data'][kindex]['target'] + "</td>";
                        campaign_offline_list += "<td>" + response['data'][kindex]['execute'] + "</td>";

                        campaign_offline_list += "<td>" + (response['data'][kindex]['status'] === "Active" ? "<label class='label label-success'>Active</label>" : "<label class='label label-danger'>Inactive</label>") + "</td>";

                        campaign_offline_list += "<td><a href='#' data-campaign='" + response['data'][kindex]['name'] + "' class='btn btn-primary btn-sm fa fa-search btn-view'></a></td>";
                        campaign_offline_list += "</tr>";

                    }


                    $(".campaign-data").html(campaign_offline_list);

                    $("#campaign-offline-list").DataTable({

                        "fnDrawCallback": function () {

                            $(".btn-view").off().on("click", function () {


                                current_campaign = $(this).data("campaign");


                                $.ajax({
                                    url: "/admin/ajax/ajax_report_campaign_offline_campaign_summary.php",
                                    method: "post",
                                    data: {
                                        date_start: $("input[name=startdate]").val(),
                                        date_end: $("input[name=enddate]").val(),
                                        zone: $("select[name=zone]").val(),
                                        project: $("select[name=project]").val(),
                                        action: "get_offline_summary",
                                        campaign: current_campaign
                                    },
                                    success: function (response) {


                                        if (response['status'] === "success") {


                                            if (response['data'] != null) {


                                                if ($.fn.DataTable.isDataTable("#data-details")) {

                                                    $('#data-details').DataTable().clear().destroy();

                                                }

                                                let campaign_offline_list = "";

                                                let date_data = [];
                                                let click_data = [];


                                                for (let kindex in response['data']['date']) {

                                                    campaign_offline_list += "<tr>";
                                                    campaign_offline_list += "<td>" + (parseInt(kindex) + 1) + "</td>";
                                                    campaign_offline_list += "<td>" + response['data']['date'][kindex] + "</td>";
                                                    campaign_offline_list += "<td>" + response['data']['execute'][kindex] + "</td>";
                                                    campaign_offline_list += "</tr>";

                                                    date_data.push(response['data']['date'][kindex]);
                                                    click_data.push(response['data']['execute'][kindex]);


                                                }


                                                $(".chart-header-campaign").html(" " + current_campaign.split(" || ")[1] + " ");


                                                var offlineCampaign_option = {

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
                                                    colors: [$primary_light, $danger_light, $success_light, $warning_light],
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
                                                        categories: date_data,
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
                                                        name: "Execute",
                                                        data: click_data
                                                    }],

                                                };


                                                var offlineCampaign = new ApexCharts(document.querySelector("#data-chart"), offlineCampaign_option);

                                                offlineCampaign.render();


                                                $("table#data-details > tbody").html(campaign_offline_list);
                                                $("table#data-details").DataTable();

                                                $("#view-detail").modal();


                                            } else {

                                                campaign_offline_list += "<tr style='text-align: center;'>";
                                                campaign_offline_list += "<td colspan='3'>No Data to Display</td>";
                                                campaign_offline_list += "</tr>";

                                                $("table#data-details > tbody").html(campaign_offline_list);


                                            }


                                        }

                                    }

                                });


                            });

                        }

                    });


                } else {


                    campaign_offline_list += "<tr style='text-align: center;'>";
                    campaign_offline_list += "<td colspan='11'>No data to display</td>";
                    campaign_offline_list += "</tr>";

                    $(".campaign-data").html(campaign_offline_list);

                }



            } else {

                swal("Error", "Please try to refresh this page", "error");

            }

        }


    });


}