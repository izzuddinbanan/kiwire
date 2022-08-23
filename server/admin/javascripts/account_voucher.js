String.prototype.capitalize = function () {

    return this.charAt(0).toUpperCase() + this.slice(1);

};


var table_data = null;

var line_chart;

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


    let view_space = $("#viewVoucher");

    view_space.find(".modal-content").height(($(window).height() * 0.95) + "px");

    view_space.on("hidden.bs.modal", function () {


        $(".user-history").css("display", "none");
        $(".user-info").css("display", "block");

        $(".btn-user-history").html("<span>Show History</span>");


    });


    $(".btn-user-history").on("click", function () {


        let toggle_button = $(this);

        let view_space = $(".user-info");
        let history_space = $(".user-history");

        let user_data = view_space.css("display");


        if (user_data !== "none") {


            toggle_button.html("<span>Please wait</span> &nbsp; <span class='spinner-grow spinner-grow-sm' role='status'></span>");


            // get user history and display

            $.ajax({
                url: "ajax/ajax_account_voucher.php",
                method: "GET",
                data: {
                    "action": "history",
                    "account": toggle_button.data("account")
                },
                success: function (response) {

                    if (response['status'] === "success") {


                        if ($.fn.dataTable.isDataTable('#table-data')) {

                            $("#table-data").DataTable().destroy();

                        }


                        let history_table = "";

                        for (let kindex = 0; kindex < response['data'].length; kindex++) {


                            if (!response['data'][kindex]['ipv6_address']) {

                                response['data'][kindex]['ipv6_address'] = "NA";

                            }

                            history_table += "<tr>";

                            history_table += "<td>" + (kindex + 1) + "</td>";
                            history_table += "<td>" + response['data'][kindex]['start_time'] + "</td>";
                            history_table += "<td>" + response['data'][kindex]['stop_time'] + "</td>";
                            history_table += "<td>" + (new Date).clearTime().addSeconds(response['data'][kindex]['session_time']).toString('H:mm:ss') + "</td>";
                            history_table += "<td>" + response['data'][kindex]['mac_address'] + "</td>";
                            history_table += "<td>" + response['data'][kindex]['class'] + "</td>";
                            history_table += "<td>" + response['data'][kindex]['brand'] + "</td>";
                            history_table += "<td>" + response['data'][kindex]['ip_address'] + "</td>";
                            history_table += "<td>" + response['data'][kindex]['ipv6_address'] + "</td>";
                            history_table += "<td>" + ((parseInt(response['data'][kindex]['quota_in']) + parseInt(response['data'][kindex]['quota_out'])) / (1024 * 1024)).toFixed(3) + "</td>";
                            history_table += "<td>" + response['data'][kindex]['terminate_reason'] + "</td>";

                            history_table += "</tr>";


                        }


                        $(".user-history-list").html(history_table);

                        $("#table-data").DataTable({ 
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


                        view_space.css("display", "none");

                        history_space.css("display", "block");

                        toggle_button.html("<span>Show Information</span>");


                    } else {

                        swal("Error", response['message'], "error");

                    }

                },
                error: function () {

                    swal("Error", "There is unexpected error. Please try again.", "error");

                }
            });


        } else {

            history_space.css("display", "none");
            view_space.css("display", "block");

            toggle_button.html("<span>Show History</span>");

        }


    });



    $('.datepick').datepicker();


    //tooltip
    $("body").tooltip({ selector: '[data-toggle=tooltip]', trigger: 'hover' });


    //reset form after cancel

    $(".cancel-button").on("click", function (e) {

        $(".create-form").trigger("reset");

        $("#inlineForm").modal("hide");

    });




    //create button

    $(".btn-create").on("click", function (e) {

        let create_form = $(".create-form");

        if (create_form.parsley().validate()) {


            let data = create_form.serialize();

            $.ajax({
                url: "ajax/ajax_account_voucher.php?action=create",
                method: "POST",
                data: data,
                success: function (data) {

                    if (data['status'] === "success") {


                        table_data.ajax.reload();

                        $("#inlineForm").modal("hide");

                        swal("Success", data['message'], "success");


                    } else {

                        swal("Error", data['message'], "failed");

                    }

                },
                error: function (data) {

                    swal("Error", "There is unexpected error. Please try again.", "error");

                }
            });

        }

    });


    $("#inlineForm").on("hidden.bs.modal", function () {


        $("form.create-form").trigger("reset").parsley().reset();

        $("#inlineForm select").val("").trigger("change");


    });


});


function pull_data() {


    if ($.fn.dataTable.isDataTable('.table-data')) {

        $(".table-data").DataTable().destroy();


    }


    table_data = $('.table-data').DataTable({ 
        "responsive": true,
        "processing": true,
        "serverSide": true,
        "ajax": {
            url: "ajax/ajax_account_voucher.php?action=get_all",
            method: "get",
            data: {
                username: $("#filter_username").val(),
                status: $("#filter_status").val(),
                profile: $("#filter_profile").val(),
                created_date: $("#filter_created_date").val(),
                expiry_from: $("#filter_expired_date").val(),
                remark: $("#filter_remark").val(),
                tenant_id: $("#filter_tenant_id").val()
            }

        },
        "dom": "<'row'<'col-sm-6 col-md-4'l><'col-sm-6 col-md-4 pull-right'f><'col-sm-12 col-md-4'<'pull-right'B>>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        "buttons": [
            {
                text: "Download All",
                className: "btn-all-data",
                action: function () {

                    $.ajax({
                        url: "ajax/ajax_account_voucher.php",
                        method: "POST",
                        data: {
                            username: $("#filter_username").val(),
                            status: $("#filter_status").val(),
                            profile: $("#filter_profile").val(),
                            created_date: $("#filter_created_date").val(),
                            expiry_from: $("#filter_expired_date").val(),
                            remark: $("#filter_remark").val(),
                            tenant_id: $("#filter_tenant_id").val(),
                            action: "get_csv"
                        },
                        success: function (response) {


                            if (response['status'] === "completed") {

                                window.location.href = response['link'];

                            } else {

                                swal("Error!", "Please re-try after couple of minutes.", "error");

                            }


                        }, error: function () {

                            swal("Error!", "There is an error occured. Please let us know about asdthis.", "error");

                        }
                    });

                }
            },
            {
                extend: 'copyHtml5',
                exportOptions: {
                    columns: [0, ':visible']
                }
            },
            {
                extend: 'csvHtml5'
            },
            {
                extend: 'pdfHtml5',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'print',
                exportOptions: {
                    columns: ':visible'
                }
            }
        ],
        language: {
            searchPlaceholder: "Search Records",
            search: "",
        },
        "fnDrawCallback": function () {
            if($('.dataTables_filter').find('input').hasClass('form-control-sm')){
                $('.dataTables_filter').find('input').removeClass('form-control-sm')
            }

            $(".btn-view").off().on("click", function () {

                viewVoucher($(this).data("account"), $(this).data("tenant"));

            });


            $(".btn-remove").off().on("click", function () {

                deleteVoucher($(this).data("account"), $(this).data("tenant"));

            });


            $(".btn-reset").off().on("click", function () {

                resetVoucher($(this).data("account"), $(this).data("tenant"));

            });


            $(".btn-reset-mac").off().on("click", function () {

                resetMACList($(this).data("account"), $(this).data("tenant"));

            });


        },
        "order": [[5, "desc"]],
        "columnDefs": [
            {
                "targets": [max_column],
                "render": function (data, type, row, meta) {

                    action_str = "";

                    action_str += "<a href='javascript:void(0);' data-account='" + row[1] + "' data-tenant='" + row[8] + "' class='btn btn-icon btn-primary btn-xs mr-1 fa fa-search btn-view' data-toggle='tooltip' data-original-title='View User'></a>";
                    action_str += "<a href='javascript:void(0);' data-account='" + row[1] + "' data-tenant='" + row[8] + "' class='btn btn-icon btn-danger btn-xs mr-1 fa fa-times btn-remove'></a>";
                    action_str += "<a href='javascript:void(0);' data-account='" + row[1] + "' data-tenant='" + row[8] + "' class='btn btn-icon btn-warning btn-xs mr-1 fa fa-refresh btn-reset' data-toggle='tooltip' data-original-title='Reset User'></a>";
                    action_str += "<a href='javascript:void(0);' data-account='" + row[1] + "' data-tenant='" + row[8] + "' class='btn btn-icon btn-warning btn-xs mr-1 fa fa-eraser btn-reset-mac' data-toggle='tooltip' data-original-title='Reset MAC Address'></a>";

                    return action_str;

                }
            },
            {
                "targets": [2],
                "render": function (data, type, row, meta) {


                    status_str = row[2];

                    if (status_str === "active") status_str = '<td><span class="badge badge-success">Active</span></td>';
                    else if (status_str === "suspend") status_str = '<td><span class="badge badge-warning">Suspended</span></td>';
                    else status_str = '<td><span class="badge badge-danger">Expired</span></td>';

                    return status_str;


                }
            },
            {
                "targets": [5],
                "render": function (data, type, row, meta) {

                    return Date.parse(row[5]).toString("d-MMM-yyyy");

                }
            },
            {
                "targets": [6],
                "render": function (data, type, row, meta) {

                    return Date.parse(row[6]).toString("d-MMM-yyyy");

                }
            }
        ]
    });


}




function viewVoucher(id, tenant) {

    $.ajax({
        url: "ajax/ajax_account_voucher.php",
        method: "GET",
        data: {
            "action": "statistics",
            "id": id,
            "tenant_id": tenant
        },
        success: function (response) {

            if (response['status'] === "success") {


                $(".user-status").html(response['data']['auth']['status'].toUpperCase());
                $(".user-integration").html(response['data']['auth']['integration'].toUpperCase());

                if (response['data']['auth']['quota_in'] > 0 && response['data']['auth']['quota_out'] > 0) {

                    $(".user-current-quota").html(numberWithCommas(((parseFloat(response['data']['auth']['quota_in']) + parseFloat(response['data']['auth']['quota_out'])) / (1024 * 1024)).toFixed(3)));

                } else {

                    $(".user-current-quota").html("0.0");

                }

                $(".user-current-session").html(convert_seconds_to_days(response['data']['auth']['session_time']));

                if (response['data']['auth']['date_activate'] !== undefined && response['data']['auth']['date_activate'] !== null && response['data']['auth']['date_activate'] !== "-" && response['data']['date_activate'] !== "0000-00-00 00:00:00") {
                // if (response['data']['auth']['date_activate'] !== undefined && response['data']['auth']['date_activate'] !== null && response['data']['auth']['date_activate'] !== "-" && response['data']['auth']['date_activate'] !== "-0001-11-30 06:46:46") {

                    $(".user-activate").html(Date.parse(response['data']['auth']['date_activate']).toString("dd-MM-yyyy"));

                } else {

                    $(".user-activate").html("NEVER USE");

                }


                $(".user-expiry").html(Date.parse(response['data']['auth']['date_expiry']).toString("dd-MM-yyyy"));

                $(".user-username").html(response['data']['auth']['username']);
                $(".user-date_create").html(Date.parse(response['data']['auth']['date_create']).toString("dd-MM-yyyy"));
                $(".user-remark").html(response['data']['auth']['remark']);

                $(".user-current-profile").html(response['data']['auth']['profile_curr'].toUpperCase());

                if (response['data']['profile']['type'] === "expiration") {

                    $(".user-profile").html(response['data']['auth']['profile_subs'].toUpperCase() + " [ " + parseInt(response['data']['profile']['attribute']['control:Access-Period'] / 60) + " MIN ]");

                } else if (response['data']['profile']['type'] === "countdown") {

                    $(".user-profile").html(response['data']['auth']['profile_subs'].toUpperCase() + " [ " + parseInt(response['data']['profile']['attribute']['control:Max-All-Session'] / 60) + " MIN ]");

                } else if (response['data']['profile']['type'] === "free") {

                    $(".user-profile").html(response['data']['auth']['profile_subs'] + " [ Unlimited Minutes ]");

                }


                $(".user-profile-type").html(response['data']['profile']['type'].capitalize());
                $(".user-profile-price").html(response['data']['profile']['price']);
                $(".user-profile-iddle").html(parseInt(response['data']['profile']['attribute']['reply:Idle-Timeout'] / 60));
                $(".user-profile-simultaneous").html(response['data']['profile']['attribute']['control:Simultaneous-Use']);
                $(".user-profile-quota").html(response['data']['profile']['attribute']['control:Kiwire-Total-Quota']);

                $(".user-profile-download").html((parseInt(response['data']['profile']['attribute']['reply:WISPr-Bandwidth-Max-Down']) / 1024).toFixed(0));
                $(".user-profile-upload").html((parseInt(response['data']['profile']['attribute']['reply:WISPr-Bandwidth-Max-Up']) / 1024).toFixed(0));

                $(".user-fields").html("");

                for (let kindex in response['data']['info']) {

                    if (response['data']['info'][kindex] !== null && response['data']['info'][kindex] !== undefined) {

                        $(".user-field-" + kindex).html(response['data']['info'][kindex]);

                    }

                }


                let device_list_str = "";

                if (response['data']['auth']['allowed_mac'] !== undefined && response['data']['auth']['allowed_mac'] !== null) {

                    let device_list_data = response['data']['auth']['allowed_mac'].split(",");

                    for (let kindex in device_list_data) {

                        if (device_list_data[kindex].length > 1) {

                            device_list_str += "<tr><td>MAC Address</td><td>" + device_list_data[kindex].toUpperCase() + "</td></tr>";

                        }

                    }

                } else {

                    device_list_str += "<tr><td>No Device Registered</td></tr>";

                }


                $(".connected-devices").html(device_list_str);



                $(".btn-user-history").data("account", response['data']['auth']['username']).data("tenant", response['data']['auth']['tenant_id']);

                $("#viewVoucher").modal();


                $.ajax({
                    url: "/admin/ajax/ajax_account_voucher.php",
                    method: "post",
                    data: {
                        "username": response['data']['auth']['username'],
                        "tenant": response['data']['auth']['tenant_id'],
                        "action": "chart_history"
                    },
                    success: function (response) {

                        if (response['status'] === "success") {

                            $("#quota-remaining").html(response['remaining_quota'] + " MB");
                            $("#quota-remaining-progress").css("width", response['percentage_quota'] + "%");

                            $("#time-remaining").html(response['remaining_time'] + " MINS");
                            $("#time-remaining-progress").css("width", response['percentage_time'] + "%");


                        }

                    }

                });


                $.ajax({
                    url: "/admin/ajax/ajax_account_voucher.php",
                    method: "post",
                    data: {
                        "username": response['data']['auth']['username'],
                        "tenant": response['data']['auth']['tenant_id'],
                        "action": "line_chart"
                    },
                    success: function (response) {

                        if (line_chart !== undefined && line_chart !== null) {

                            try {

                                line_chart.destroy();

                            } catch (er) {

                                console.log("");

                            }

                        }

                        if (response['status'] === "success") {

                            let chart_data_date = [];
                            let chart_data_quota = [];
                            let chart_data_time = [];



                            for (let kindex in response['data']) {

                                chart_data_date.push(response['data'][kindex]['xstart_time']);
                                chart_data_quota.push(parseInt(response['data'][kindex]['quota']) / (1024 * 1024));
                                chart_data_time.push(parseInt(response['data'][kindex]['session_time']) / (60));


                            }


                            var userlinechart = {
                                chart: {
                                    height: 300,
                                    type: 'area',
                                    dropShadow: {
                                        enabled: true,
                                        top: 1,
                                        left: 1,
                                        blur: 10,
                                        opacity: 0.2,
                                    },
                                    toolbar: {
                                        show: false,
                                    },
                                    sparkline: {
                                        enabled: false
                                    },
                                    grid: {
                                        show: false,
                                        padding: {
                                            left: 0,
                                            right: 0
                                        }
                                    },
                                },

                                dataLabels: {
                                    enabled: false
                                },
                                stroke: {
                                    curve: 'smooth',
                                    width: 2.5
                                },
                                grid: {
                                    borderColor: $label_color,
                                },
                                fill: {
                                    type: 'gradient',
                                    gradient: {
                                        shadeIntensity: 0.9,
                                        opacityFrom: 0.5,
                                        opacityTo: 0.3,

                                    }
                                },
                                series: [{
                                    name: 'Quota (MB)',
                                    data: chart_data_quota
                                },
                                {
                                    name: 'Session Time (Minutes)',
                                    data: chart_data_time
                                }],
                                legend: {
                                    show: false
                                },
                                xaxis: {
                                    categories: chart_data_date,
                                },
                                yaxis: [
                                    {
                                        axisTicks: {
                                            show: true
                                        },
                                        axisBorder: {
                                            show: true,
                                            color: "$primary_light"
                                        },
                                        labels: {
                                            style: {
                                                colors: "$primary_light"
                                            },
                                            formatter: function (val) {
                                                return parseInt(val);
                                            }
                                        },
                                        title: {
                                            style: {
                                                color: "$primary_light"
                                            }
                                        }
                                    },
                                    {
                                        opposite: true,
                                        axisTicks: {
                                            show: true
                                        },
                                        axisBorder: {
                                            show: true,
                                            color: "$success"
                                        },
                                        labels: {
                                            style: {
                                                colors: "$success"
                                            },
                                            formatter: function (val) {
                                                return parseInt(val);
                                            }
                                        },
                                        title: {
                                            style: {
                                                color: "$success"
                                            }
                                        }
                                    }
                                ],
                                tooltip: {
                                    x: {
                                        show: true,
                                        format: 'dd/MM/yy'
                                    }
                                },
                            }

                            line_chart = new ApexCharts(document.querySelector("#line-chart"), userlinechart);

                            line_chart.render();

                        } else {

                            $("#line-chart").html("[ NO DATA AVAILABLE ]").css("color", "#000000");

                        }

                    }

                });


            } else {

                swal("Error", response['message'], "error");

            }

        },
        error: function () {

            swal("Error", "There is unexpected error. Please try again.", "error");

        }
    });


}


function deleteVoucher(id, tenant) {

    swal({

        title: "Are you sure?",
        text: "You will not able to reverse this action.",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel"

    }).then(function (x) {

        if (x['value'] === true) {

            $.ajax({

                url: "ajax/ajax_account_voucher.php",
                method: "POST",
                data: {
                    "action": "delete",
                    "username": id,
                    "tenant_id": tenant,
                    "token": $("input[name=token]").val()
                    

                },
                success: function (data) {

                    if (data['status'] === "success") {


                        table_data.ajax.reload();

                        swal("Success", data['message'], "success");


                    } else {

                        swal("Error", data['message'], "error");

                    }
                }

            });

        }

    });

}


function resetVoucher(id, tenant) {

    if (id.length) {


        swal({

            title: "Are you sure?",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, Reset it!",
            cancelButtonText: "Cancel"

        }).then(function (x) {

            if (x['value'] === true) {

                $.ajax({

                    url: "ajax/ajax_account_voucher.php",
                    method: "POST",
                    data: {
                        "action": "reset",
                        "username": id,
                        "tenant_id": tenant

                    },
                    success: function (data) {

                        if (data['status'] === "success") {


                            table_data.ajax.reload();

                            swal("Success", data['message'], "success");


                        } else {

                            swal("Error", data['message'], "error");

                        }

                    }

                });

            }

        });


    }

}


function resetMACList(id, tenant) {

    if (id.length) {


        swal({

            title: "Are you sure?",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, Reset it!",
            cancelButtonText: "Cancel"

        }).then(function (x) {

            if (x['value'] === true) {

                $.ajax({

                    url: "ajax/ajax_account_voucher.php",
                    method: "POST",
                    data: {
                        "action": "reset-mac",
                        "username": id,
                        "tenant_id": tenant

                    },
                    success: function (data) {

                        if (data['status'] === "success") {


                            table_data.ajax.reload();

                            swal("Success", data['message'], "success");


                        } else {

                            swal("Error", data['message'], "error");

                        }

                    }

                });

            }

        });


    }

}


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

function numberWithCommas(x) {

    return x.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");

}
