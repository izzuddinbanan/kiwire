var current_action = "create";
var current_id = "";

$(document).ready(function () {

    // collect data for the list

    pull_data();


    $('.datepick').datepicker();

    // hide all field on load

    $(".space-zone, .space-persona, .space-custom-zone, .space-time-frame").css("display", "none");
    $(".space-trigger-info, .space-dwell, .space-every, .space-last").css("display", "none");
    $(".space-ads, .space-notification, .space-redirection, .space-template, .space-nontemplate").css("display", "none");


    $("input[name=target]").on("change", function () {


        let selected_item = $(this).val();


        $(".space-zone, .space-persona").css("display", "none");

        $(".space-custom-zone").css("display", "none");


        if (selected_item === "persona"){


            $(".space-persona").css("display", "block");

            $("select[name=target_value_zone]").val("all").trigger("change");


        } else if (selected_item === "zone"){

            $(".space-zone").css("display", "block");

        }


    });



    $("select[name=target_value_zone]").on("change", function(){

        if ($(this).val() === "custom"){

            $(".space-custom-zone").css("display", "block");

        } else {

            $(".space-custom-zone").css("display", "none");

        }

    });



    $("input[name=c_interval]").on("change", function(){


        let selected_item = $(this).val();


        if(selected_item === "timeframe"){

            $(".space-time-frame").css("display", "block");

        } else {

            $(".space-time-frame").css("display", "none");

        }


    });



    $("input[name=c_trigger]").on("change", function (){


        let selected_item = $(this).val();
        let display_ads = $("input[type=radio][value=ads]");

        $(".space-trigger-info").css("display", "none");

        display_ads.parent().parent().css("display", "inline-block");


        if (selected_item === "dwell"){

            $(".space-dwell").css("display", "block");
            display_ads.parent().parent().attr("style", "display: none !important");


        } else if (selected_item === "recurring"){

            $(".space-every>label:first").html("Every");
            $(".space-every").css("display", "block");


        } else if (selected_item === "milestone"){

            $(".space-every>label:first").html("Reached");
            $(".space-every").css("display", "block");


        } else if (selected_item === "lastvisit"){

            $(".space-last").css("display", "block");
            display_ads.parent().parent().attr("style", "display: none !important");


        } else if (selected_item === "disconnect"){

            display_ads.parent().parent().attr("style", "display: none !important");

        }


    });



    $("input[name=c_action]").on("change", function(){


        let selected_item = $(this).val();


        $(".space-ads,.space-notification,.space-redirection").css("display", "none");


        if (selected_item === "ads"){


            $(".space-ads").css("display", "block");


        } else if (selected_item === "notification"){

            $(".space-notification").css("display", "block");
            $(".space-template").css("display", "block");


        } else if (selected_item === "redirect"){

            $(".space-redirection").css("display", "block");


        }


    });



    $("select[name=notification_type]").on("change", function(){


        $(".space-template, .space-nontemplate").css("display", "none");


        let selected_item = $(this).val();



        if (["sms", "email"].includes(selected_item)){

            $(".space-template").css("display", "block");


        } else {

            $(".space-nontemplate").css("display", "block");


        }



    });



    $("#inlineForm").on("hidden.bs.modal", function () {


        $(".space-zone, .space-persona, .space-custom-zone, .space-time-frame").css("display", "none");
        $(".space-trigger-info, .space-dwell, .space-every, .space-last").css("display", "none");
        $(".space-ads, .space-notification, .space-redirection, .space-template, .space-nontemplate").css("display", "none");

        $("form.create-form").trigger("reset");

        current_action = "create";

        $(".btn-save").html("Create");

        $("input[type=radio][value=ads]").parent().parent().css("display", "inline-block");


    });


    $(".btn-save").on("click", function(){


        let form_data = $("form.create-form");


        // if (form_data.parsley().validate()) {

            form_data = form_data.serialize();

            form_data += "&id=" + current_id;


            $.ajax({
                url: "/admin/ajax/ajax_campaign_management.php?action=" + current_action,
                method: "get",
                data: form_data,
                success: function (response) {

                    if (response['status'] === "success") {


                        $("#inlineForm").modal("toggle");

                        pull_data();

                        swal("Success", response['message'], "success");


                    } else {

                        $("#inlineForm").modal("hide");
                        swal("Error", response['message'], "error");

                    }


                },
                error: function (response) {

                    swal("Error", "There is an internal error. Please retry.", "error");

                }
            });


        // }

    });


});


function pull_data() {

    $.ajax({
        url: "/admin/ajax/ajax_campaign_management.php?action=get_all",
        method: "post",
        data: {},
        success: function (response) {

            if (response['status'] === "success") {

                if($.fn.dataTable.isDataTable('.table-data')){

                    $(".table-data").DataTable().destroy();

                }

                let table_data = "";

                if (response['data'].length > 0) {

                    for (let x = 0; x < response['data'].length; x++) {

                        table_data += "<tr>";

                        table_data += "<td>" + (x + 1) + "</td>";
                        table_data += "<td>" + response['data'][x]['name'] + "</td>";

                        if (response['data'][x]['status'] === "Active") {
                            table_data += '<td><span class="badge badge-success">Active</span></td>';
                        } else {
                            table_data += '<td><span class="badge badge-danger">Disabled</span></td>';
                        }

                        table_data += "<td>" + response['data'][x]['date_start'] + "</td>";
                        table_data += "<td>" + response['data'][x]['date_end'] + "</td>";

                        if (response['data'][x]['target'] === "Zone") {

                            table_data += "<td>Zone: " + response['data'][x]['target_value'] + "</td>";

                        } else if (response['data'][x]['target'] === "Persona"){

                            table_data += "<td>Persona: " + response['data'][x]['target_value'] + "</td>";

                        } else {

                            table_data += "<td>All</td>";

                        }

                        table_data += "<td>" + response['data'][x]['c_interval'] + "</td>";
                        table_data += "<td>" + response['data'][x]['c_trigger'] + "</td>";
                        table_data += "<td>" + response['data'][x]['action'] + "</td>";
                        table_data += "<td>" + response['data'][x]['c_order'] + "</td>";
                        table_data += "<td>";
                        table_data += "<a href='#' class='btn btn-icon btn-success btn-xs mr-1 fa fa-pencil btn-campaign-edit' title='Edit' data-campaign-id='" + response['data'][x]['id'] + "'></a>";
                        table_data += "<a href='#' class='btn btn-icon btn-danger btn-xs mr-1 fa fa-times btn-campaign-delete' title='Delete' data-campaign-id='" + response['data'][x]['id'] + "'></a>";
                        table_data += "<a href='#' class='btn btn-icon btn-" + (response['data'][x]['status'] === "Active" ? "danger fa-minus" : "warning fa-check") + " btn-xs mr-1 fa btn-campaign-verify' title='Verify' data-campaign-id='" + response['data'][x]['id'] + "'></a>";
                        table_data += "</td>";

                        table_data += "</tr>";

                    }

                } else {

                    table_data = "";

                }


                $("table.table-data>tbody").html(table_data);

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
                })


            } else {

                $("table.table-data>tbody").html("<tr><td class='text-center' colspan='8'>No data to display</td></tr>");

            }



            $(".btn-campaign-edit").on("click", function (e) {


                e.preventDefault();


                let current_element = $(this).data("campaign-id");


                current_action = "update";

                current_id = current_element;


                $.ajax({
                    url: "/admin/ajax/ajax_campaign_management.php?action=get_update",
                    method: "post",
                    data: {
                        "id": current_element
                    },
                    success: function (response) {


                        $(".btn-save").html("Update");


                        if (response['status'] === "success"){


                            $("input[name=name]").val(response['data']['name']);
                            $("input[name=c_order]").val(response['data']['c_order']);
                            $("input[name=remark]").val(response['data']['remark']);
                            $("input[name=date_start]").val(response['data']['date_start']);

                            $("input[name=date_end]").val(response['data']['date_end']);
                            $("input[name=expire_click]").val(response['data']['expired_click']);
                            $("input[name=expire_impression]").val(response['data']['expired_impress']);


                            $("input[name=target][value=" + response['data']['target'] + "]").click();

                            if (response['data']['target'] === "persona") {

                                $("select[name=target_value_persona]").val(response['data']['target_value']).trigger("change");

                            } else if (response['data']['target'] === "zone") {

                                $("select[name=target_value_zone]").val(response['data']['target_value']).trigger("change");

                            }


                            if (response['data']['target_value'] === "custom"){

                                $("input[name=c_zone]").val(response['data']['target_option']);

                            }


                            if (response['data']['c_trigger'].length === 0) response['data']['c_trigger'] = "connect";


                            $("input[name=c_trigger][value=" + response['data']['c_trigger'] + "]" ).click();


                            if (response['data']['c_trigger'] === "dwell"){

                                $("input[name=dwell]").val(response['data']['c_trigger_value']);

                            } else if ( ["recurring", "milestone"].includes(response['data']['c_trigger'])){

                                $("input[name=recurring]").val(response['data']['c_trigger_value']);

                            } else if (response['data']['c_trigger'] === "lastvisit"){

                                $("input[name=lastvisit]").val(response['data']['c_trigger_value']);

                            }

                            $("input[name=c_interval][value=" + response['data']['c_interval'] + "]").click();

                            if (response['data']['c_interval'] === "timeframe") {

                                $("input[name=shour]").val(response['data']['c_interval_time_start']);
                                $("input[name=thour]").val(response['data']['c_interval_time_stop']);

                            }

                            if(response['data']['action'])
                            $("input[name=c_action][value=" + response['data']['action'] + "]").click();

                            if (response['data']['action'] === "ads"){

                                $("select[name=ads_id]").val(response['data']['action_value']).trigger("change");
                                $("select[name=c_space]").val(response['data']['c_space']).trigger("change");

                            } else if (response['data']['action'] === "notification"){

                                $("select[name=notification_type]").val(response['data']['action_method']).trigger("change");
                                $("select[name=notification_template]").val(response['data']['action_value']).trigger("change");

                            } else if (response['data']['action'] === "redirect"){

                                $("input[name=redirection]").val(response['data']['action_value']);

                            }


                            $("#inlineForm").modal();


                        } else {

                            swal("Error", response['message'], "error");

                        }


                    },
                    error: function (response) {

                        swal("Error", "There is unexpected error. Please try again.", "error");

                    }
                });



            });


            $(".btn-campaign-delete").on("click", function () {


                let current_element = $(this);

                let campaign_id = current_element.data("campaign-id");

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
                            url: "ajax/ajax_campaign_management.php",
                            method: "POST",
                            data: {
                                "action": "delete",
                                "id": campaign_id,
                                "token": $("input[name=token]").val()
                            },

                            success: function (data) {

                                if (data['status'] === "success") {

                                    pull_data();

                                    toastr.info("Success", data['message'], "success");

                                } else {

                                    toastr.info("Error", data['message'], "error");

                                }

                            },

                        });

                    }

                });

            });



            $(".btn-campaign-verify").on("click", function(){

                let campaign_id = $(this).data("campaign-id");

                $.ajax({
                    url: "/admin/ajax/ajax_campaign_management.php?action=verify",
                    method: "post",
                    data: {
                        "id": campaign_id
                    },
                    success: function (response) {

                        if (response['status'] === "success"){


                            pull_data();

                            swal("Success", response['message'], "success");


                        } else {

                            swal("Error", response['message'], "error");

                        }

                    },
                    error: function (response) {

                        swal("Error", "There is unexpected error. Please try again.", "error");

                    }
                });



            });



        },
        error: function (response) {

            swal("Error", "There is an internal error. Please retry.", "error");

        }
    });

}
