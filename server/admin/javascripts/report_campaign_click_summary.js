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


var impressionvslogin;


function get_data_for_all() {

    $.ajax({
        url: "/admin/ajax/ajax_report_campaign_click_summary.php",
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

                let campaign_list = "";

                if (response['data'] !== null) {

                    if ($.fn.DataTable.isDataTable("#campaign-list")) {

                        $('#campaign-list').DataTable().clear().destroy();

                    }

                    for (let kindex in response['data']) {

                        campaign_list += "<tr>";
                        campaign_list += "<td>" + (parseInt(kindex) + 1) + "</td>";
                        campaign_list += "<td>" + response['data'][kindex]['name'] + "</td>";
                        campaign_list += "<td>" + response['data'][kindex]['start'] + "</td>";
                        campaign_list += "<td>" + response['data'][kindex]['end'] + "</td>";
                        campaign_list += "<td>" + response['data'][kindex]['trigger'] + "</td>";
                        campaign_list += "<td>" + response['data'][kindex]['target'] + "</td>";
                        campaign_list += "<td>" + response['data'][kindex]['source'] + "</td>";
                        campaign_list += "<td>" + response['data'][kindex]['click'] + "</td>";
                        campaign_list += "<td>" + response['data'][kindex]['u_click'] + "</td>";

                        if (response['data'][kindex]['status'] === "Active") {
                            campaign_list += "<td><span class=\"badge badge-success\">Active</span></td>";
                        } else {
                            campaign_list += "<td><span class=\"badge badge-danger\">Disabled</span></td>";
                        }

                        campaign_list += "<td><a href='#' data-campaign='" + response['data'][kindex]['reference'] + "' class='btn btn-primary btn-sm fa fa-search  btn-view'></a></td>";
                        campaign_list += "</tr>";

                    }


                    $(".campaign-data").html(campaign_list);


                    $("#campaign-list").DataTable({
                        dom: dt_position,
                        pageLength: dt_page,
                        buttons: dt_btn,
                        language: {
                            searchPlaceholder: "Search Records",
                            search: "",
                        },
                        "fnDrawCallback": function () {
                            if ($('.dataTables_filter').find('input').hasClass('form-control-sm')) {
                                $('.dataTables_filter').find('input').removeClass('form-control-sm')
                            }

                            $(".btn-view").off().on("click", function () {


                                current_campaign = $(this).data("campaign");


                                if ($.fn.DataTable.isDataTable("#data-details")) {

                                    $('#data-details').DataTable().clear().destroy();

                                }

                                if (impressionvslogin !== undefined) {

                                    impressionvslogin.destroy();

                                }


                                $.ajax({
                                    url: "/admin/ajax/ajax_report_campaign_click_summary.php",
                                    method: "post",
                                    data: {
                                        date_start: $("input[name=startdate]").val(),
                                        date_end: $("input[name=enddate]").val(),
                                        zone: $("select[name=zone]").val(),
                                        project: $("select[name=project]").val(),
                                        action: "get_impress_summary",
                                        campaign: current_campaign
                                    },
                                    success: function (response) {


                                        if (response['status'] === "success") {


                                            if (response['data'] != null) {


                                                let campaign_list = "";

                                                let click_data = [];
                                                let uclick_data = [];


                                                for (let kindex in response['data']['date']) {

                                                    campaign_list += "<tr>";
                                                    campaign_list += "<td>" + (parseInt(kindex) + 1) + "</td>";
                                                    campaign_list += "<td>" + response['data']['date'][kindex] + "</td>";
                                                    campaign_list += "<td>" + response['data']['click'][kindex] + "</td>";
                                                    campaign_list += "<td>" + response['data']['u_click'][kindex] + "</td>";
                                                    campaign_list += "</tr>";


                                                    click_data.push([response['data']['date'][kindex], parseInt(response['data']['click'][kindex]).toFixed(0)]);
                                                    uclick_data.push([response['data']['date'][kindex], parseInt(response['data']['u_click'][kindex]).toFixed(0)]);


                                                }


                                                $(".chart-header-campaign").html(" " + current_campaign.split(" || ")[1] + " ");


                                                var impressionvslogin_option = {

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
                                                            },
                                                            formatter: function (val) {
                                                                return new Date(val).toString("dd-MMM-yyyy H:00:00");
                                                            }
                                                        },
                                                        axisTicks: {
                                                            show: false,
                                                        },
                                                        type: 'datetime',
                                                        axisBorder: {
                                                            show: false,
                                                        },
                                                        tickPlacement: 'on'
                                                    },
                                                    yaxis: {
                                                        labels: {
                                                            style: {
                                                                color: $strok_color,
                                                            },
                                                        }
                                                    },
                                                    tooltip: {
                                                        x: {
                                                            show: true,
                                                            format: 'dd MMM yyyy - H:00:00'
                                                        }
                                                    },
                                                    series: [{
                                                        name: "Clicked",
                                                        data: click_data
                                                    }, {
                                                        name: "Unique Clicked",
                                                        data: uclick_data
                                                    }],

                                                };


                                                impressionvslogin = new ApexCharts(document.querySelector("#data-chart"), impressionvslogin_option);

                                                impressionvslogin.render();


                                                $("table#data-details > tbody").html(campaign_list);
                                                $("table#data-details").DataTable({
                                                    dom: dt_position,
                                                    pageLength: dt_page,
                                                    buttons: dt_btn,
                                                    language: {
                                                        searchPlaceholder: "Search Records",
                                                        search: "",
                                                    },
                                                    "fnDrawCallback": function () {
                                                        if ($('.dataTables_filter').find('input').hasClass('form-control-sm')) {
                                                            $('.dataTables_filter').find('input').removeClass('form-control-sm')
                                                        }

                                                    }
                                                });

                                                $("#view-detail").modal();


                                            } else {

                                                campaign_list += "<tr style='text-align: center;'>";
                                                campaign_list += "<td colspan='3'>No Data to Display</td>";
                                                campaign_list += "</tr>";

                                                $("table#data-details > tbody").html(campaign_list);


                                            }


                                        }

                                    }

                                });


                            });

                        }

                    });


                } else {


                    campaign_list += "<tr style='text-align: center;'>";
                    campaign_list += "<td colspan='11'>No data to display</td>";
                    campaign_list += "</tr>";

                    $(".campaign-data").html(campaign_list);

                }



            } else {

                swal("Error", "Please try to refresh this page", "error");

            }

        }


    });


}


