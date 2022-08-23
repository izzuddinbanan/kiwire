String.prototype.capitalize = function () {

    return this.charAt(0).toUpperCase() + this.slice(1);

};


var table_data = null;

var line_chart;

var max_column = 8;


$(document).ready(function () {

    pull_data();


    //tooltip
    $("body").tooltip({ selector: '[data-toggle=tooltip]', trigger: 'hover' });


    let view_space = $("#viewVoucher");

    view_space.find(".modal-content").height(($(window).height() * 0.95) + "px");

    view_space.on("hidden.bs.modal", function () {


        $(".user-history").css("display", "none");
        $(".user-info").css("display", "block");

        $(".btn-user-history").html("<span>Show History</span>");


    });


    $('.datepick').datepicker();


    //reset form after cancel

    $(".cancel-button").on("click", function (e) {

        $(".create-form").trigger("reset");

        $("#inlineForm").modal("hide");

    });


    //create button

    $(".btn-create").on("click", function (e) {

        let create_form = $(".create-form");


            let data = create_form.serialize();

            $.ajax({
                url: "ajax/ajax_generate_voucher.php?action=create",
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

    });


    $("#inlineForm").on("hidden.bs.modal", function () {


        // $("form.create-form").trigger("reset").parsley().reset();
        $("form.create-form").trigger("reset");

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
            url: "ajax/ajax_generate_voucher.php",
            method: "get",
            data: {
                "action": "get_all"
            }
        },
        "dom": dt_position,
        "buttons": dt_btn,
        language: {
            searchPlaceholder: "Search Records",
            search: "",
        },
        "fnDrawCallback": function () {

            // if ($('.dataTables_filter').find('input').hasClass('form-control-sm')) {
            //     $('.dataTables_filter').find('input').removeClass('form-control-sm')
            // }

            $(".btn-view").on("click", function () {

                viewVoucher($(this).data("account"), $(this).data("tenant"));

            });


            $(".btn-remove").off().on("click", function () {

                deleteVoucher($(this).data("account"), $(this).data("tenant"));

            });


            $(".btn-reset").off().on("click", function () {

                resetVoucher($(this).data("account"), $(this).data("tenant"));

            });


            $(".btn-modal").on("click", function () {

                $("#test-modal").modal();

                // viewModal($(this).data("account"), $(this).data("tenant"));

            });


            // $(".btn-reset-mac").off().on("click", function () {

            //     resetMACList($(this).data("account"), $(this).data("tenant"));

            // });


        },
        "order": [[5, "desc"]],
        "columnDefs": [
            {
                "targets": [8],
                "render": function (data, type, row, meta) {

                    action_str = "";

                    action_str += "<a href='javascript:void(0);' data-account='" + row[1] + "' data-tenant='" + kiw_tenant + "' class='btn btn-icon btn-primary btn-xs mr-1 fa fa-search btn-view' data-toggle='tooltip' data-original-title='View Code'></a>";
                    action_str += "<a href='javascript:void(0);' data-account='" + row[1] + "' data-tenant='" + kiw_tenant + "' class='btn btn-icon btn-danger btn-xs mr-1 fa fa-times btn-remove' data-toggle='tooltip' data-original-title='Deactivate Code'></a>";
                    action_str += "<a href='javascript:void(0);' data-account='" + row[1] + "' data-tenant='" + kiw_tenant + "' class='btn btn-icon btn-warning btn-xs mr-1 fa fa-refresh btn-reset' data-toggle='tooltip' data-original-title='Reset Code'></a>";
                    // action_str += "<a href='javascript:void(0);' data-account='" + row[1] + "' data-tenant='" + kiw_tenant + "' class='btn btn-icon btn-warning btn-xs mr-1 fa fa-eraser btn-reset-mac' data-toggle='tooltip' data-original-title='Reset MAC Address'></a>";

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
                    // return Date.parse(row[5]);

                }
            },
            {
                "targets": [6],
                "render": function (data, type, row, meta) {

                    return Date.parse(row[6]).toString("d-MMM-yyyy");
                    // return Date.parse(row[6]);

                }
            }
        ]
    });


}



function viewVoucher(id, tenant) {


    $.ajax({
        url: "ajax/ajax_generate_voucher.php",
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

                $("#view-voucher").modal();
                // $("#import_user").modal();


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
        confirmButtonText: "Yes, deactivate it!",
        cancelButtonText: "Cancel"

    }).then(function (x) {

        if (x['value'] === true) {

            $.ajax({

                url: "ajax/ajax_generate_voucher.php",
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

                    url: "ajax/ajax_generate_voucher.php",
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