$(document).ready(function () {


    pull_data();

    let view_space = $("#viewTopup");

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
                url: "ajax/ajax_account_topup.php",
                method: "GET",
                data: {
                    "action": "history",
                    "id": toggle_button.data("id")
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


    $(".create-btn-profile").on("click", function () {


        $(".create-form").trigger("reset");

        $("#inlineForm").modal();


    });


    $(".cancel-button").click(function (e) {

        $(".create-form").trigger("reset");

        $("#inlineForm").modal("hide");

    });


    //tooltip
    $("body").tooltip({ selector: '[data-toggle=tooltip]', trigger: 'hover' });


    //create button

    $(".btn-create").on("click", function (e) {

        let create_form = $(".create-form");

        if (create_form.parsley().validate()) {

            let data = create_form.serialize();

            $.ajax({
                url: "ajax/ajax_account_topup.php?action=create",
                method: "GET",
                data: data,
                success: function (data) {

                    if (data['status'] === "success") {


                        $(".create-form").trigger("reset");

                        $("#inlineForm").modal("hide");


                        pull_data();


                        swal("Success", data['message'], "success");


                    } else {

                        swal("Error", data['message'], "error");

                    }
                },
                error: function (data) {

                    swal("Error", "There is unexpected error. Please try again.", "error");

                }
            })
        }
    });


    //view button
    $(".btn-view").on("click", function (e) {

        let data = $(".create-form").serialize();

        $.ajax({
            url: "ajax/ajax_account_profile.php?action=edit_single_data",
            method: "GET",
            data: data,
            success: function (data) {

                if (data['status'] === "success") {


                    $("#inlineForm").modal("hide");

                    pull_data();

                    swal("Success", data['message'], "success");


                } else {

                    swal("Error", data['message'], "error");

                }
            },
            error: function (data) {

                swal("Error", "There is unexpected error. Please try again.", "error");

            }
        })
    });


    //update button
    $(".btn-update").on("click", function (e) {

        let data = $(".create-form").serialize();

        $.ajax({
            url: "ajax/ajax_account_topup.php?action=edit_single_data",
            method: "GET",
            data: data,
            success: function (data) {

                if (data['status'] === "success") {


                    $("#inlineForm").modal("hide");

                    pull_data();

                    swal("Success", data['message'], "success");


                } else {

                    swal("Error", data['message'], "error");

                }
            },
            error: function (data) {

                swal("Error", "There is unexpected error. Please try again.", "error");

            }
        })
    });


    $("select[name=advance]").on("change", function () {

        let current_element = $(this);
        let current_name = $("#inlineForm input[name=name]").val();

        if (current_element.val() === current_name) {


            current_element.val("").trigger("change");

            swal("Error", "This profile and advance profile matched.", "error");


        }


    });


});



function pull_data() {

    $.ajax({
        url: "ajax/ajax_account_topup.php",
        method: "GET",
        data: {
            "action": "get_all"
        },
        success: function (data) {

            if (data['status'] === "success") {

                if ($.fn.dataTable.isDataTable('.table-data')) {

                    $(".table-data").DataTable().destroy();

                }

                let table_str = "";

                for (let x = 0; x < data['data'].length; x++) {

                    table_str += "<tr>";

                    table_str += '<td>' + (x + 1) + "</td>";
                    table_str += '<td>' + data['data'][x]['price'] + "</td>";
                    table_str += '<td>' + data['data'][x]['code'] + "</td>";
                    table_str += '<td>' + data['data'][x]['quota'] + "</td>";
                    table_str += '<td>' + data['data'][x]['time'] + "</td>";


                    if (data['data'][x]['status'] == 'n') {

                        table_str += "<td><span class=\"badge badge-success\">Unused</span></td>";

                    } else {

                        table_str += "<td><span class=\"badge badge-danger\">Used</span></td>";

                    }


                    if (data['data'][x]['username'] == null) {

                        table_str += '<td>' + 'None' + "</td>";

                    } else {

                        table_str += '<td>' + data['data'][x]['username'] + "</td>";

                    }



                    if (data['data'][x]['date_activate'] == null) {

                        table_str += '<td>' + 'Never' + "</td>";

                    } else {

                        table_str += '<td>' + data['data'][x]['date_activate'] + "</td>";

                    }


                    table_str += '<td>';

                    table_str += "<a href=\"javascript:void(0);\" onclick=\"viewTopup('" + data['data'][x]['code'] + "')\" class='btn btn-icon btn-primary btn-xs mr-1 fa fa-search' data-toggle='tooltip' data-original-title='View Topup Information'</a>";
                    table_str += "<a href=\"javascript:void(0);\" onclick=\"resetCode('" + data['data'][x]['id'] + "')\" class='btn btn-icon btn-warning btn-xs mr-1 fa fa-refresh' data-toggle='tooltip' data-original-title='Reset Code'</a>";

                    table_str += "<a href=\"javascript:void(0);\" onclick=\"deleteItem('" + data['data'][x]['id'] + "')\" class='btn btn-icon btn-danger btn-xs mr-1 fa fa-times'  data-toggle='tooltip' data-original-title='Delete Code'</a>";



                }


                $(".table-data>tbody").html(table_str);

                $('.table-data').DataTable({
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


            } else {

                swal("Error", data['message'], "error");
            }
        },
        error: function () {

            swal("Error", "There is unexpected error. Please try again.", "error");

        }
    })
}


function resetCode(id) {


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

                    url: "ajax/ajax_account_topup.php",
                    method: "POST",
                    data: {
                        "action": "reset_code",
                        "id": id

                    },
                    success: function (data) {

                        if (data['status'] === "success") {


                            pull_data();

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


function deleteItem(id) {

    Swal.fire({

        // input: 'select',
        // inputOptions: profile_deletion,
        title: "CONFIRM DELETION?",
        text: "Are you sure want to delete this topup code:",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel"

    }).then((result) => {


        if (result['value'] !== undefined) {


            $.ajax({
                url: "ajax/ajax_account_topup.php",
                method: "POST",
                data: {
                    "action": "delete",
                    "id": id,
                    "account": result['value'],
                    "token": $("input[name=token]").val()
                },
                success: function (response) {

                    if (response['status'] === "success") {


                        swal("Success", response['message'], "success");

                        pull_data();


                    } else {

                        swal("Error", response['message'], "error");

                    }
                },
                error: function (response) {

                    swal("Error", "There is unexpected error. Please try again.", "error");

                }
            });


        }


    });


}


//get data based on id
function getItemForForm(id) {


    $.ajax({
        url: "ajax/ajax_account_topup.php",
        method: "GET",
        data: {
            "action": "get_update",
            "id": id
        },
        success: function (response) {


            if (response['status'] === "success") {


                for (let key in response['data']) {

                    $("#inlineForm input[name=" + key + "]").val(response['data'][key]);
                    $("#inlineForm select[name=" + key + "]").val(response['data'][key]);
                    $("#inlineForm textarea[name=" + key + "]").val(response['data'][key]);

                }


                if (response['data']['name'] === "Temp_Access") {

                    $(".grace-user").css("display", "block");

                } else {

                    $(".grace-user").css("display", "none");

                }

                $(".btn-create").css("display", "none");
                $(".btn-update").css("display", "inline-block");

                $("#inlineForm select").trigger("change");

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


function viewTopup(id) {

    if (id.length > 0) {

        $.ajax({
            url: "ajax/ajax_account_topup.php",
            method: "GET",
            data: {
                "action": "statistics",
                "id": id
            },
            success: function (response) {

                if (response['status'] === "success") {

                    // alert(id);


                    if (response['data']['topup']['status'] === "n") {

                        $(".user-status").html("UNUSED");

                    } else {

                        response['data']['topup']['status'] = "Used";

                        $(".user-status").html("USED");


                    }

                    // console.log(response['data']['creator']);

                    $(".user-creator").html(response['data']['creator'].toUpperCase());


                    if (response['data']['topup']['username'] !== undefined && response['data']['topup']['username'] !== null && response['data']['topup']['username'] !== "-") {

                        $(".user-username").html(response['data']['topup']['username'].toUpperCase());


                    } else {

                        $(".user-username").html("NONE");

                    }



                    // if (response['data']['topup']['status'] == "y") {

                    if (response['data']['auth']['quota_in'] > 0 && response['data']['auth']['quota_out'] > 0) {

                        $(".user-current-quota").html(numberWithCommas(((parseFloat(response['data']['auth']['quota_in']) + parseFloat(response['data']['auth']['quota_out'])) / (1024 * 1024)).toFixed(3)));


                    } else {

                        $(".user-current-quota").html("0.0");

                    }

                    if (response['data']['auth']['session_time'] > 0) {

                    $(".user-current-session").html(convert_seconds_to_days(response['data']['auth']['session_time']));

                    } else {

                        $(".user-current-session").html("00:00:00:00");
                    
                    }

                    
                    var createdDate = new Date(response['data']['topup']['date_create']);
                    var date  = createdDate.getDate();
                    var month = createdDate.getMonth();
                    var year  = createdDate.getFullYear();

                    var create = ("0" + date).slice(-2) + "-" + (month + 1) + "-" + year;
                    $(".user-date-create").html(create);

                    
                    if (response['data']['topup']['date_activate'] !== undefined && response['data']['topup']['date_activate'] !== null && response['data']['topup']['date_activate'] !== "-") {

                        // $(".user-activate").html(Date.parse(response['data']['topup']['date_activate']).toString("dd-MM-yyyy"));

                        var activateDate = new Date(response['data']['topup']['date_activate']);
                        var date  = activateDate.getDate();
                        var month = activateDate.getMonth();
                        var year  = activateDate.getFullYear();
    
                        var activate = ("0" + date).slice(-2) + "-" + (month + 1) + "-" + year;
                        $(".user-activate").html(activate);
                        

                    } else {

                        $(".user-activate").html("NEVER USE");

                    }

        

                    $(".topup-price").html(response['data']['topup']['price']);
                    $(".topup-code").html(response['data']['topup']['code'].toUpperCase());
                    $(".topup-quota").html(response['data']['topup']['quota']);
                    $(".topup-time").html(response['data']['topup']['time']);
                    $(".topup-date-create").html(response['data']['topup']['date_create'].toString("dd-MM-yyyy"));


                    $(".topup-username").html(response['data']['auth']['username']);
                    $(".topup-fullname").html(response['data']['auth']['fullname']);
                    $(".topup-email").html(response['data']['auth']['email_address']);
                    $(".topup-phone").html(response['data']['auth']['phone_number']);



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

                    

                    $("#viewTopup").modal();


                } else {

                    swal("Error", response['message'], "error");

                }


            },
            error: function () {

                swal("Error", "There is unexpected error. Please try again.", "error");

            }

        });


    } else {

        swal("Error", "There is unexpected error. Please try again.", "error");


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
