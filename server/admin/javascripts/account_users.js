    var $primary = '#7367F0';
var $primary_light = '#A9A2F6';
var $success = '#28C76F';
var $label_color = '#e7e7e7';
var $warning = '#FF9F43';
var $info_light = '#1edec5';


String.prototype.capitalize = function () {

    return this.charAt(0).toUpperCase() + this.slice(1);

};

var line_chart;

var current_user = null, table_data = null, current_tenant = null;

$(document).ready(function () {


    pull_data();

    $('#search').on("click", pull_data);


    $('body').on('click','.btn-user-unblock', function(){

        let username    = $(this).data('username')
        let tenant_id   = $(this).data('tenant')

        swal({

            title: "Are you sure?",
            text: "You will not able to reverse this action.",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "Cancel"
    
        }).then(function(x){
    
            if (x['value'] === true) {
                
                $.ajax({
                    url: "/admin/ajax/ajax_account_users.php",
                    method: "POST",
                    data: {
                        "username"  :   username,
                        "tenant"    :   tenant_id,
                        "action"    :   "unblock_user"
                    },
                    success: function (data) {
    
                        if (data['status'] === "success") {
    
                            pull_data();
    
                            toastr.info("Success", data['message'], "success");
                            location.reload();

    
                        } else {
    
                            toastr.info("Error", data['message'], "error");
    
                        }
    
                    },
    
                });
    
            }
    
        });
        


    });

    // $("#filter-btn").on("click", function (){

    //     $("#filter_modal").modal();

    // });


    // $("#filter-data").on("click", function (){


    //     pull_data();

    //     $("#filter_modal").modal("hide");


    // });


    let view_space = $("#viewUser");

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
                url: "ajax/ajax_account_users.php",
                method: "GET",
                data: {
                    "action": "history",
                    "account": toggle_button.data("account"),
                    "tenant_id": toggle_button.data("tenant")
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


                            if (response['data'][kindex]['class'] === "Smartphone") {

                                history_table += '<td><span class="fa fa-mobile fa-3x" text-align="center"></span></td>';


                            } else if (response['data'][kindex]['class'] === "Tablet") {

                                history_table += '<td><span class="fa fa-tablet fa-3x" text-align="center"></span></td>';

                            } else {

                                history_table += '<td><span class="fa fa-desktop fa-2x" text-align="center"></span></td>';
                            } 
                     

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


    // reset form after cancel

    $(".cancel-button").on("click", function (e) {

        $(".create-form").trigger("reset");

        $("#inlineForm").modal("hide");


    });


    // hide button update/create

    $(".btn-create, .btn-update").css("display", "none");


    $(".create-btn-user").on("click", function () {

        $(".btn-create, .btn-update").css("display", "none");
        $(".btn-create").css("display", "block");

        $("#inlineForm").modal();

    });


    // import user button

    $(".import-btn-user").on("click", function () {

        $("#importUser").modal();

    });

    $('#username').keyup(function(e) {  
            
        let string = $(this).val();
        let result = string.replace(" ", "");
        $(this).val(result);
    });


    // create button

    $(".btn-create").on("click", function (e) {

        let create_form = $(".create-form");

        if (create_form.parsley().validate()) {


            let data = create_form.serialize();

            $.ajax({

                url: "ajax/ajax_account_users.php?action=create",
                method: "POST",
                data: data,
                success: function (data) {

                    if (data['status'] === "success") {

                        table_data.ajax.reload();

                        $("#inlineForm").modal("hide");

                        swal("Success", data['message'], "success");


                    } else {

                        swal("Error", data['message'], "error");

                    }

                },
                error: function (data) {

                    swal("Error", "There is unexpected error. Please try again.", "error");

                }
            });

        }

    });


    $(".btn-update").on("click", function (e) {


        $("select[name=tenant_id]").attr("disabled", false);

        let create_form = $(".create-form");

        if (create_form.parsley().validate()) {


            let data = create_form.serialize();

            $.ajax({

                url: "ajax/ajax_account_users.php?action=edit_single_data",
                method: "POST",
                data: data,
                success: function (data) {

                    if (data['status'] === "success") {

                        // $('#itemlist').DataTable().ajax.reload();
                        table_data.ajax.reload();

                        $("#inlineForm").modal("hide");

                        swal("Success", data['message'], "success");


                    } else {

                        swal("Error", data['message'], "error");

                    }

                },
                error: function (data) {

                    swal("Error", "There is unexpected error. Please try again.", "error");

                }
            });

        }

    });




    $("#inlineForm").on("hidden.bs.modal", function () {


        $("select[name=tenant_id]").attr("disabled", false);

        $("form.create-form").trigger("reset");

        $(".btn-create").css("display", "inline-block");
        $(".btn-update").css("display", "none");

        $("#inlineForm select").val("").trigger("change");


    });


    $(".btn-import").on("click", function () {


        let import_account = $("form.import_account");


        if (import_account.parsley().validate() === false) {

            return;

        }


        let data = new FormData(import_account[0]);

        $.ajax({
            url: "/admin/ajax/ajax_account_users.php",
            method: "POST",
            data: data,
            processData: false,
            contentType: false,
            success: function (data) {

                if (data['status'] === "success") {


                    table_data.ajax.reload();


                    $("form.import_account").trigger("reset").parsley().reset();

                    $(".custom-file-label").html("");

                    $("#import_user").modal("hide");


                    swal("Success", data['message'], "success");

                    window.location.href = "/temp/" + data['data'];


                } else {

                    swal("Error", data['message'], "error");

                }
            },
            error: function (data) {

                swal("Error", "There is unexpected error. Please try again.", "error");

            }
        });

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
            url: "ajax/ajax_account_users.php?action=get_all",
            method: "get",
            data:{
                username: $("#filter_username").val(),
                status: $("#filter_status").val(),
                profile: $("#filter_profile").val(),
                expiry_from: $("#filter_expired_from").val(),
                expiry_until: $("#filter_expired_until").val(),
                tenant_id: $("#filter_tenant_id").val()
            }
        },
        "dom": "<'row'<'col-sm-6 col-md-4'l><'col-sm-12 col-md-8'<'pull-right'B>>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        "buttons": [
            {
                text: 'Import',
                action: function (e, d, n, c) {

                    $("#import_user").modal();

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


            $(".btn-edit").off().on("click", function () {

                editUser($(this).data("account"), $(this).data("tenant"));

            });

            $(".btn-view").off().on("click", function () {

                viewUser($(this).data("account"), $(this).data("tenant"));

            });

            $(".btn-delete").off().on("click", function () {

                deleteItem($(this).data("account"), $(this).data("tenant"));

            });

            $(".btn-reset").off().on("click", function () {

                resetUser($(this).data("account"), $(this).data("tenant"));

            });


        },
        "columnDefs": [
            {
                "targets": [max_column],
                "render": function (data, type, row, meta) {

                    action_str = "";

                    action_str += "<a href='javascript:void(0);' data-account='" + row[1] + "' data-tenant='" + row[6] + "' class='btn btn-icon btn-success btn-xs mr-1 fa fa-pencil btn-edit'></a>";
                    action_str += "<a href='javascript:void(0);' data-account='" + row[1] + "' data-tenant='" + row[6] + "' class='btn btn-icon btn-primary btn-xs mr-1 fa fa-search btn-view'></a>";
                    action_str += "<a href='javascript:void(0);' data-account='" + row[1] + "' data-tenant='" + row[6] + "' class='btn btn-icon btn-danger btn-xs mr-1 fa fa-times btn-delete'></a>";
                    action_str += "<a href='javascript:void(0);' data-account='" + row[1] + "' data-tenant='" + row[6] + "' class='btn btn-icon btn-warning btn-xs mr-1 fa fa-refresh btn-reset'></a>";

                    return action_str;

                }
            },
            {
                "targets": [4],
                "render": function (data, type, row, meta) {


                    status_str = row[4];

                    if (status_str === "active") status_str = '<td><span class="badge badge-success">Active</span></td>';
                    else if (status_str === "suspend") status_str = '<td><span class="badge badge-warning">Suspended</span></td>';
                    else if (status_str === "blocked") status_str = '<td><span class="badge badge-info">Blocked</span></td>';
                    else status_str = '<td><span class="badge badge-danger">Expired</span></td>';

                    return status_str;


                }
            },
            {
                "targets": [5],
                "render": function (data, type, row, meta) {

                    return Date.parse(row[5]).toString("d-MMM-yyyy");

                }
            }
        ]
    });


}



function editUser(account, tenant) {


    current_user = account;
    current_tenant = tenant;

    $.ajax({
        url: "ajax/ajax_account_users.php",
        method: "GET",
        data: {
            "action": "get_update",
            "username": current_user,
            "tenant_id": tenant
        },
        success: function (response) {

            if (response['status'] === "success") {


                for (let key in response['data']) {

                    if (["profile_subs", "integration", "allowed_zone", "status", "tenant_id"].includes(key)) {

                        $("select[name=" + key + "]").val(response['data'][key]).trigger("change");

                    } else {

                        $("input[name=" + key + "]").val(response['data'][key]);

                    }

                }


                $("select[name=tenant_id]").attr("disabled", true);

                $(".btn-create").css("display", "none");
                $(".btn-update").css("display", "inline-block");

                $("#inlineForm").modal();


            } else {

                swal("Error", response['message'], "error");

            }


        },
        error: function (error) {

            swal("Error", "There is an error", "error");

        }
    });


}




function viewUser(id, tenant) {

    $.ajax({
        url: "/admin/ajax/ajax_account_users.php",
        method: "GET",
        data: {
            "action": "statistics",
            "id": id,
            "tenant_id": tenant
        },
        success: function (response) {

            if (response['status'] === "success") {

                $('.div-security').hide();

                let str = "";

                if (response['data']['auth']['status'] === "active") {

                    str += "<i class='spinner-grow spinner-grow-sm  mr-3 text-success' style='position: absolute; top: 45px; left: 60px;'></i>";

                } else {

                    str += "<i class='fa fa-circle font-lg text-danger mr-50' style='position: absolute; top: 45px; left: 50px;'></i>";


                }

                $(".status-point > div").html(str);

                let user_status = response['data']['auth']['status'];
                $(".user-status").html(response['data']['auth']['status'].toUpperCase());
                $(".user-integration").html(response['data']['auth']['integration'].toUpperCase());

                if (response['data']['auth']['quota_in'] > 0 && response['data']['auth']['quota_out'] > 0) {

                    $(".user-current-quota").html(numberWithCommas(((parseFloat(response['data']['auth']['quota_in']) + parseFloat(response['data']['auth']['quota_out'])) / (1024 * 1024)).toFixed(3)));

                } else {

                    $(".user-current-quota").html("0.0");

                }

                $(".user-current-session").html(convert_seconds_to_days(response['data']['auth']['session_time']));


                if (response['data']['auth']['date_activate'] !== undefined && response['data']['auth']['date_activate'] !== null && response['data']['auth']['date_activate'] !== "-" && response['data']['date_activate'] !== "0000-00-00 00:00:00") {

                    $(".user-activate").html(Date.parse(response['data']['auth']['date_activate']).toString("dd-MM-yyyy"));

                } else {

                    $(".user-activate").html("NEVER USE");

                }


                $(".user-expiry").html(Date.parse(response['data']['auth']['date_expiry']).toString("dd-MM-yyyy"));

                $(".user-username").html(response['data']['auth']['username']);

                $(".user-fullname").html(response['data']['auth']['fullname']);
                $(".user-email-address").html(response['data']['auth']['email_address']);
                $(".user-phone-number").html(response['data']['auth']['phone_number']);



                $(".user-current-profile").html(response['data']['auth']['profile_curr']);

                if (response['data']['profile']['type'] === "expiration") {

                    $(".user-profile").html(response['data']['auth']['profile_subs'] + " [ " + parseInt(response['data']['profile']['attribute']['control:Access-Period'] / 60) + " Minutes ]");

                } else if (response['data']['profile']['type'] === "countdown") {

                    $(".user-profile").html(response['data']['auth']['profile_subs'] + " [ " + parseInt(response['data']['profile']['attribute']['control:Max-All-Session'] / 60) + " Minutes ]");

                } else if (response['data']['profile']['type'] === "free") {

                    $(".user-profile").html(response['data']['auth']['profile_subs'] + " [ Unlimited Minutes ]");

                } else {

                    $(".user-profile").html("Missing Profile");

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

                if (response['data']['auth']['allowed_mac'] !== undefined && response['data']['auth']['allowed_mac'] !== null && response['data']['auth']['allowed_mac'].length > 0) {

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


                $("#viewUser").modal();


                $.ajax({
                    url: "/admin/ajax/ajax_account_users.php",
                    method: "post",
                    data: {
                        "username": response['data']['auth']['username'],
                        "tenant": response['data']['auth']['tenant_id'],
                        "action": "user_chart_history"
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
                    url: "/admin/ajax/ajax_account_users.php",
                    method: "post",
                    data: {
                        "username": response['data']['auth']['username'],
                        "tenant": response['data']['auth']['tenant_id'],
                        "action": "user_line_graph"
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

                $.ajax({
                    url: "/admin/ajax/ajax_account_users.php",
                    method: "post",
                    data: {
                        "username": response['data']['auth']['username'],
                        "tenant": response['data']['auth']['tenant_id'],
                        "action": "user_security"
                    },
                    success: function (response) {

                        if (response['status'] === "success") {

                            if(response['data'].length > 0){
                                
                                let history_table = "";
        
                                for (let kindex = 0; kindex < response['data'].length; kindex++) {

                                    let severe = response['data'][kindex]['severity'];

                                    if (severe === "low")           status_str = '<td><span class="badge badge-info">Low</span></td>';
                                    else if (severe === "medium")   status_str = '<td><span class="badge badge-warning">Medium</span></td>';
                                    else if (severe === "high")     status_str = '<td><span class="badge badge-danger">High</span></td>';
                                    else if (severe === "critical") status_str = '<td><span class="badge badge-danger">Critical</span></td>';
        
                                    history_table += "<tr>";

                                    history_table += "<td>" + Date.parse(response['data'][kindex]['date_time']).toString("d-MMM-yyyy") + "</td>";
                                    history_table += status_str;
                                    history_table += "<td>" + response['data'][kindex]['vuln'] + "</td>";
        
                                    history_table += "</tr>";
        
        
                                }
        
                                $(".user-security-list").html(history_table);

                                $('.div-security').show();

        
                            }
    
                        }

                    }

                });

                if(user_status === 'blocked'){

                    $('.div-btn').html('<button class="btn btn-warning waves-effect waves-light btn-user-unblock" data-username="'+response['data']['auth']['username']+'" data-tenant="'+response['data']['auth']['tenant_id']+'">Unblock User</button>');
                }

            } else {

                swal("Error", response['message'], "error");

            }

        },
        error: function () {

            swal("Error", "There is unexpected error. Please try again.", "error");

        }
    });


}


function deleteItem(id, tenant) {


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

                url: "ajax/ajax_account_users.php",
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
                },
            });
        }
    });


}


function resetUser(account, tenant) {


    swal({

        title: "Are you sure?",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, reset it!",
        cancelButtonText: "Cancel"

    }).then(function (x) {

        if (x['value'] === true) {

            $.ajax({

                url: "ajax/ajax_account_users.php",
                method: "POST",
                data: {
                    "action": "reset",
                    "username": account,
                    "tenant_id": tenant
                },

                success: function (data) {

                    if (data['status'] === "success") {


                        swal("Success", data['message'], "success");


                    } else {

                        swal("Error", data['message'], "error");

                    }
                },
            });
        }
    });


}



function changeInt(integration) {

    switch (integration) {

        case "int": return "Internal";
        case "pms": return "Integration: PMS";
        case "bc": return "Integration: Business Center";
        case "ms_ad": return "Integration: Active Directory";
        case "ldap": return "Integration: LDAP";

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


