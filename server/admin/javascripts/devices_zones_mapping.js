
var current_rule_for, current_rule_list = [];

$(document).ready(function () {


    pull_data();

    //tooltip
    $("body").tooltip({ selector: '[data-toggle=tooltip]', trigger: 'hover' });


    //hide button update/create
    $(".btn-create, .btn-update").css("display", "none");


    $(".create-btn-zone").on("click", function () {

        $(".btn-create, .btn-update").css("display", "none");

        $(".btn-create").css("display", "block");

        $("#inlineForm").modal();

    });


    //reset form after cancel

    $(".cancel-button").on("click", function (e) {


        $(".create-form").trigger("reset");

        $("#inlineForm").modal("hide");


    });


    $("button.save-button").on("click", function (e) {

        let data = $("form").serialize();

        $.ajax({
            url: "ajax/ajax_devices_zones_mapping.php?action=update",
            method: "POST",
            data: data,
            success: function (data) {

                if (data['status'] === "success") {

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


    $(".btn-create").on("click", function (e) {

        let create_form = $(".create-form");

        if (create_form.parsley().validate()) {

            let data = create_form.serialize();

            $.ajax({
                url: "ajax/ajax_devices_zones_mapping.php?action=create",
                method: "GET",
                data: data,
                success: function (data) {

                    if (data['status'] === "success") {

                        $(".create-form").trigger("reset");

                        $("#inlineForm").modal("hide");

                        pull_data(); //get latest table

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


    //update button

    $(".btn-update").on("click", function (e) {


        let create_form = $(".create-form");

        if (create_form.parsley().validate()) {

            let data = $(".create-form").serialize();
            $.ajax({
                url: "ajax/ajax_devices_zones_mapping.php?action=edit_single_data",
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
            });
        }

    });


    $(".btn-rules-add").on("click", function () {


        let rules_space = $("table.rules-list>tbody");
        let new_rules_space = "";

        if (rules_space.children("tr").children("td").html() === "No item to display") {

            rules_space.children("tr").remove();

        }


        current_number = rules_space.children("tr").length + 1;


        for (let i = 0; i < 5000; i++) {

            if (!current_rule_list.includes(current_number)) {

                break;

            } else {

                current_number++;

            }

        }


        current_rule_list.push(current_number);

        new_rules_space += "<tr class='rules-" + current_number + "' data-rules-number='" + current_number + "'>";

        new_rules_space += "<td><input type='text' class='form-control' name='ipaddress-" + current_number + "'></td>";
        new_rules_space += "<td><input type='text' class='form-control' name='ipv6addr-" + current_number + "'></td>";
        new_rules_space += "<td><input type='text' class='form-control' name='vlan-" + current_number + "'></td>";
        new_rules_space += "<td><input type='text' class='form-control' name='ssid-" + current_number + "'></td>";
        new_rules_space += "<td><input type='text' class='form-control' name='controller_id-" + current_number + "'></td>";
        new_rules_space += "<td><input type='text' class='form-control' name='controller_zone-" + current_number + "'></td>";

        new_rules_space += "<td><button type='button' class='btn btn-success btn-sm btn-rules-save mr-1 rules-" + current_number + "' data-rules-number='" + current_number + "'><i class='fa fa-save'></i></button>";
        new_rules_space += "<button type='button' class='btn btn-danger btn-sm btn-rules-delete rules-" + current_number + "' data-rules-number='" + current_number + "' data-rules-hash=''><i class='fa fa-times'></i></button></td>";

        new_rules_space += "</tr>";

        rules_space.append(new_rules_space);


        $(".btn-rules-delete").on("click", function () {


            let rule_number = $(this);

            deleteRule(rule_number.data("rules-number"), rule_number.data("rules-hash"));


        });


        $(".btn-rules-save").on("click", function () {


            let rule_number = $(this).data("rules-number");

            let tokens = $(this).data("token");

            // saveRules($(this).data("rules-number"), $(this).data("token"));

            saveRules(rule_number, tokens);


        });


    });


});



function saveRules(rulesId, token) {



    let rules = {};

    rules.action = "save_rules";
    rules.zone = current_rule_for;
    rules.ipaddress = $("input[name=ipaddress-" + rulesId + "]").val();
    rules.ipv6addr = $("input[name=ipv6addr-" + rulesId + "]").val();
    rules.vlan = $("input[name=vlan-" + rulesId + "]").val();
    rules.ssid = $("input[name=ssid-" + rulesId + "]").val();
    rules.controller_id = $("input[name=controller_id-" + rulesId + "]").val();
    rules.controller_zone = $("input[name=controller_zone-" + rulesId + "]").val();
    rules.token = $("input[name=token]").val();


    if (rules.ipaddress.length > 0 || rules.vlan.length > 0 || rules.ssid.length > 0 || rules.controller_id.length > 0 || rules.controller_zone.length > 0) {

        $.ajax({

            url: "/admin/ajax/ajax_devices_zones_mapping.php",
            method: "post",
            data: rules, token,
            success: function (response) {

                $(".btn-rules-delete.rules-" + rulesId).data("rules-hash", response['data']);
                $(".btn-rules-save.rules-" + rulesId).prop("disabled", true).removeClass("btn-success").addClass("btn-light");

                $("input[name=ipaddress-" + rulesId + "]").prop("disabled", true);
                $("input[name=ipv6addr-" + rulesId + "]").prop("disabled", true);
                $("input[name=vlan-" + rulesId + "]").prop("disabled", true);
                $("input[name=ssid-" + rulesId + "]").prop("disabled", true);
                $("input[name=controller_id-" + rulesId + "]").prop("disabled", true);
                $("input[name=controller_zone-" + rulesId + "]").prop("disabled", true);

                toastr.info("Rule has been saved")

            },
            error: function (response) {

                swal("error", "There is an error occured. Please retry.", "Error");

            }

        });

    }


}



function pull_data() {

    $.ajax({
        url: "ajax/ajax_devices_zones_mapping.php",
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

                    table_str += "<td>" + (x + 1) + "</td>";
                    table_str += "<td>" + data['data'][x]['name'] + "</td>";
                    table_str += "<td>" + (data['data'][x]['auto_login'] === '' ? 'None' : data['data'][x]['auto_login']) + "</td>";

                    if (data['data'][x]['simultaneous'] === "0") {
                        table_str += "<td>No Limit</td>";
                    } else {
                        table_str += "<td>" + data['data'][x]['simultaneous'] + "</td>";
                    }

                    table_str += "<td>" + data['data'][x]['priority'] + "</td>";
                    table_str += "<td>" + data['data'][x]['journey'] + "</td>";


                    if (data['data'][x]['status'] === "y") {
                        table_str += "<td><span class=\"badge badge-success\">Active</span></td>";
                    } else {
                        table_str += "<td><span class=\"badge badge-danger\">Disabled</span></td>";
                    }

                    table_str += "<td><a href=\"javascript:void(0);\" onclick=\"updateRules('" + data['data'][x]['name'] + "')\" class='btn btn-icon btn-primary btn-xs mr-1 fa fa-sort-amount-asc' data-toggle=\'tooltip\' data-original-title=\'Add Zone Rules\'></a><a href=\"javascript:void(0);\" onclick=\"getItemForForm('" + data['data'][x]['id'] + "')\" class='btn btn-icon btn-success btn-xs mr-1 fa fa-pencil'></a><a href=\"javascript:void(0);\" onclick=\"deleteItem('" + data['data'][x]['id'] + "')\" class='btn btn-icon btn-danger btn-xs mr-1 fa fa-times'></a></td>";

                    table_str += "</tr>";
                }

                $(".table-data>tbody").html(table_str);

                $(".table-data").dataTable({
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

            } else {

                swal("Error", data['message'], "error");

            }

        },
        error: function () {

            swal("Error", "There is unexpected error. Please try again.", "error");

        }

    })

}


function deleteItem(id) {

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
                url: "ajax/ajax_devices_zones_mapping.php",
                method: "POST",
                data: {
                    "action": "delete",
                    id: id,
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

}

function getItemForForm(id) {

    if (id > 0) {

        $.ajax({
            url: "ajax/ajax_devices_zones_mapping.php",
            method: "GET",
            data: {
                "action": "get_update",
                "id": id
            },
            success: function (data) {

                if (data) {

                    for (let key in data['data']) {

                        if (["journey", "force_profile", "force_zone"].includes(key)) {

                            $("select[name=" + key + "]").val(data['data'][key]).trigger("change");

                        } else {

                            $("input[name=" + key + "]").val(data['data'][key]);

                        }

                    }

                    $("form.create-form").trigger("reset");

                    $("#name").val(data['data']['name']);
                    $("#auto_login").val(data['data']['auto_login']);
                    $("#simultaneous").val(data['data']['simultaneous']);
                    $("#priority").val(data['data']['priority']);
                    $("#journey").val(data['data']['journey']);
                    $("#force_profile").val(data['data']['force_profile']);
                    $("#force_zone").val(data['data']['force_allowed_zone']);

                    if ((data['data']['status']) === 'y') {
                        $('#status').prop("checked", true);
                    } else {
                        $('#status').prop("checked", false);
                    }


                    $("#reference").val(data['data']['id']);

                    $(".btn-create, .btn-update").css("display", "none");

                    $(".btn-create").css("display", "none");
                    $(".btn-update").css("display", "block");

                    $("#inlineForm").modal();

                }

            },
            error: function (error) {

                swal("Error", "There is unexpected error. Please try again.", "error");

            }

        });

    }

}


function updateRules(zoneId) {


    current_rule_for = zoneId;


    if (current_rule_for.length) {

        $.ajax({
            url: "/admin/ajax/ajax_devices_zones_mapping.php",
            method: "post",
            data: {
                "action": "get_rules",
                "zone": current_rule_for
            },
            success: function (response) {


                if (response['status'] === "success") {

                    if (response['data'].length > 0) {

                        let current_rules = "";


                        for (let i = 0; i < response['data'].length; i++) {

                            current_rules += "<tr class='rules-" + i + "' data-rules-number='" + i + "'>";

                            current_rules += "<td><input type='text' disabled='disabled' class='form-control' value='" + response['data'][i]['ipaddr'] + "'></td>";
                            current_rules += "<td><input type='text' disabled='disabled' class='form-control' value='" + response['data'][i]['ipv6addr'] + "'></td>";
                            current_rules += "<td><input type='text' disabled='disabled' class='form-control' value='" + response['data'][i]['vlan'] + "'></td>";
                            current_rules += "<td><input type='text' disabled='disabled' class='form-control' value='" + response['data'][i]['ssid'] + "'></td>";
                            current_rules += "<td><input type='text' disabled='disabled' class='form-control' value='" + response['data'][i]['nasid'] + "'></td>";
                            current_rules += "<td><input type='text' disabled='disabled' class='form-control' value='" + response['data'][i]['dzone'] + "'></td>";
                            current_rules += "<td><button type='button' class='btn btn-light btn-sm mr-1 btn-rules-save rules-" + i + "' disabled='disabled' data-rules-number='" + i + "'><i class='fa fa-save'></i></button><button type='button' class='btn btn-danger btn-sm btn-rules-delete rules-" + i + "' data-rules-number='" + i + "' data-rules-hash='" + response['data'][i]['hash'] + "'><i class='fa fa-times'></i></button></td>";

                            current_rules += "</tr>";

                        }


                        $("table.rules-list>tbody").html(current_rules);


                        $(".btn-rules-delete").on("click", function () {


                            let rule_number = $(this);

                            deleteRule(rule_number.data("rules-number"), rule_number.data("rules-hash"));


                        });


                        $(".btn-rules-save").on("click", function () {


                            // let rule_number = $(this).data("rules-number");

                            saveRules($(this).data("rules-number"), $(this).data("token"));

                            // saveRules(rule_number);


                        });


                    } else {

                        $("table.rules-list>tbody").html("<tr><td colspan='6' style='text-align: center;'>No item to display</td></tr>");

                    }


                    $("#rules-modal").modal();


                }

            },
            error: function (response) {

                $("table.rules-list>tbody").html("<tr><td colspan='6' style='text-align: center;'>No item to display</td></tr>");

            }
        });

    }



}


function deleteRule(ruleId, ruleHash) {

    if (ruleHash.length) {

        $.ajax({
            url: "/admin/ajax/ajax_devices_zones_mapping.php",
            method: "post",
            data: {
                "action": "delete_rule",
                "rules_id": ruleHash,
                "token": $("input[name=token]").val()
            },
            success: function (response) {

                toastr.info("Rule has been deleted");


            },
            error: function (response) {

                swal("Error", "There is unexpected error. Please try again.", "error");

            }
        });

    }


    $("tr.rules-" + ruleId).remove();

    let current_space = $("table.rules-list>tbody");


    if (current_space.children("tr").length === 0) {

        current_space.html("<tr><td colspan='6' style='text-align: center;'>No item to display</td></tr>");

    }


}
