String.prototype.capitalize = function () {

    return this.charAt(0).toUpperCase() + this.slice(1);

};


var table_data = null;

var line_chart;

$(document).ready(function () {


    pull_data();

    $('#search').on("click", pull_data);


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


                            if (response['data'][kindex]['ipv6_address'].length === 0) {

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
                url: "ajax/ajax_account_tejas.php?action=create",
                method: "POST",
                data: data,
                success: function (data) {
                    console.log(data);

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
            url: "ajax/ajax_account_tejas.php?action=get_all",
            method: "get",
            data: {
                username: $("#filter_username").val(),
                status: $("#filter_status").val(),
                profile: $("#filter_profile").val(),
                created_date: $("#filter_created_date").val(),
                expiry_from: $("#filter_expired_date").val()

            }

        },
        "dom": dt_position,
        "buttons":dt_btn,
        language: {
            searchPlaceholder: "Search Records",
            search: "",
        },
        "fnDrawCallback": function () {
            if($('.dataTables_filter').find('input').hasClass('form-control-sm')){
                $('.dataTables_filter').find('input').removeClass('form-control-sm')
            }

            $(".btn-edit").off().on("click", function () {

                editUser($(this).data("account"), $(this).data("tenant"));

            });


            $(".btn-view").off().on("click", function () {

                viewVoucher($(this).data("account"));

            });


            $(".btn-remove").off().on("click", function () {

                deleteVoucher($(this).data("account"));

            });


            $(".btn-reset").off().on("click", function () {

                resetVoucher($(this).data("account"));

            });


            $(".btn-reset-mac").off().on("click", function () {

                resetMACList($(this).data("account"));

            });


        },
        // "order": [[5, "desc"]],
        "columnDefs": [
            {
                "targets": [5],
                "render": function (data, type, row, meta) {

                    action_str = "";

                    action_str += "<a href='javascript:void(0);' data-account='" + row[1] + "' data-tenant='" + row[7] + "' class='btn btn-icon btn-success btn-xs mr-1 fa fa-pencil btn-edit'></a>";
                    // action_str += "<a href='javascript:void(0);' data-account='" + row[1] + "' class='btn btn-icon btn-primary btn-xs mr-1 fa fa-search btn-view' data-toggle='tooltip' data-original-title='View User'></a>";
                    // action_str += "<a href='javascript:void(0);' data-account='" + row[1] + "' class='btn btn-icon btn-danger btn-xs mr-1 fa fa-times btn-remove'></a>";
                    // action_str += "<a href='javascript:void(0);' data-account='" + row[1] + "' class='btn btn-icon btn-warning btn-xs mr-1 fa fa-refresh btn-reset' data-toggle='tooltip' data-original-title='Reset User'></a>";

                    return action_str;

                }
            },
            {
                "targets": [2],
                "render": function (data, type, row, meta) {


                    status_str = row[4];

                    if (status_str === "y") status_str = '<td><span class="badge badge-success">Sync</span></td>';
                    else if (status_str === "n") status_str = '<td><span class="badge badge-warning">Not Sync</span></td>';

                    return status_str;


                }
            },
            {
                "targets": [3],
                "render": function (data, type, row, meta) {

                    return Date.parse(row[5]).toString("d-MMM-yyyy");

                }
            },
            {
                "targets": [4],
                "render": function (data, type, row, meta) {

                    return Date.parse(row[6]).toString("d-MMM-yyyy");

                }
            }
        ]
    });


}


function editUser(account, tenant) {


    let current_user    = account;
    let current_tenant  = tenant;

    $.ajax({
        url: "ajax/ajax_account_tejas.php",
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
