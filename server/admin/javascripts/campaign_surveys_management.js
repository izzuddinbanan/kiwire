var current_survey;

$(document).ready(function () {


    pull_data();


    //tooltip

    $("body").tooltip({selector: '[data-toggle=tooltip]', trigger: 'hover'});


    //hide button update/create

    $(".btn-create, .btn-update").css("display", "none");

    $(".create-btn-survey").on("click", function () {

        $(".btn-create, .btn-update").css("display", "none");

        $(".btn-create").css("display", "block");

        $("#inlineForm").modal();

    });


    //reset form after cancel

    $(".cancel-button").on("click", function (e) {

        $(".create-form").trigger("reset");

        $("#inlineForm").modal("hide");

    });


    $(".btn-create").on("click", function (e) {

        let create_form = $(".create-form");

        if (create_form.parsley().validate()) {

            let data = create_form.serialize();

            $.ajax({
                url: "ajax/ajax_campaign_surveys_management.php?action=create",
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

            let data = create_form.serialize();

            $.ajax({
                url: "ajax/ajax_campaign_surveys_management.php?action=edit_single_data",
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


    $(".btn-question-add").on("click", function () {


        let question_space = $("table.question-list>tbody");
        let new_question_space = "";

        let choice_counter = $("input[name='question[]']").length;


        if (question_space.children("tr").children("td").html() === "No item to display") {

            question_space.children("tr").remove();

        }


        new_question_space += "<tr>";

        new_question_space += "<td><input type='text' class='form-control' name='question[]'></td>";
        new_question_space += "<td><select class='form-control' name='type[]'><option value='text-single'>Free Text - Single Line</option><option value='select-single'>Multiple Choice - One</option><option value='select-multi'>Multiple Choice - Multiple</option></select></td>";
        new_question_space += "<td><select class='form-control' name='required[]'><option value='true'>Yes</option><option value='false'>No</option></select></td>";
        new_question_space += "<td><div style='display: block; padding: 5px;'><input type='text' class='form-control' name='choice[" + choice_counter + "][]'></div></td>";
        new_question_space += "<td><button type='button' class='btn btn-danger btn-icon btn-sm btn-delete-question'><i class='fa fa-times'></i></button>";
        new_question_space += "&nbsp;<button type='button' class='btn btn-primary btn-icon btn-sm btn-add-choice'><i class='fa fa-plus'></i></button></td>";

        new_question_space += "</tr>";


        question_space.append(new_question_space);


        $(".btn-add-choice").off().on("click", function () {


            let current_question = $(this).parent("td").prev("td");

            let choice_number = current_question.children("div:first").children("input").attr("name");

            current_question.append("<div style='display: block; padding: 5px;'><input type='text' class='form-control' name='" + choice_number + "' value=''></div>");


        });


        $(".btn-delete-question").off().on("click", function () {


            $(this).parent("td").parent("tr").remove();

            let current_space = $("table.question-list>tbody");


            if (current_space.children("tr").length === 0) {

                current_space.html("<tr><td colspan='5' style='text-align: center;'>No item to display</td></tr>");

            }


        });


    });


    $(".btn-question-save").on("click", function () {


        let question_list = $("form#question-form").serialize();

        if (question_list.length) {


            question_list += "&action=question_save";
            question_list += "&id=" + current_survey;


            $.ajax({
                url: "/admin/ajax/ajax_campaign_surveys_management.php",
                method: "post",
                data: question_list,
                success: function (response) {

                    if (response['status'] === "success") {


                        swal("Success", response['message'], "success");

                        $("#question-modal").modal("toggle");


                    } else {

                        swal("Error", response['message'], "error");

                    }

                },
                error: function () {

                    swal("Error", "There is unexpected error. Please try again.", "error");

                }

            });


        }


    });


});


function pull_data() {

    $.ajax({
        url: "ajax/ajax_campaign_surveys_management.php",
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
                    table_str += "<td>" + data['data'][x]['description'] + "</td>";


                    if (data['data'][x]['status'] === "y") {
                        table_str += "<td><span class='badge badge-success'>Active</span></td>";
                    } else {
                        table_str += "<td><span class='badge badge-danger'>Disabled</span></td>";
                    }


                    table_str += "<td data-question-id='" + data['data'][x]['id'] + "'>";
                    table_str += "<button type='button' data-action='question' class='btn btn-icon btn-warning btn-xs mr-1 fa fa-edit btn-action-q' data-toggle='tooltip' data-original-title='Add Questions'></button>";
                    table_str += "<button type='button' data-action='edit' class='btn btn-icon btn-success btn-xs mr-1 fa fa-pencil btn-action-q'></button>";
                    table_str += "<button type='button' data-action='delete' class='btn btn-icon btn-danger btn-xs mr-1 fa fa-times btn-action-q'></button>";
                    table_str += "</td>";

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
                        if($('.dataTables_filter').find('input').hasClass('form-control-sm')){
                            $('.dataTables_filter').find('input').removeClass('form-control-sm')
                        }

                    }
                });


                $(".btn-action-q").on("click", function () {


                    let current_element = $(this);
                    let survey_id = current_element.parent().data("question-id");
                    let survey_action = current_element.data("action");

                    if (survey_action === "delete") deleteItem(survey_id);
                    else if (survey_action === "edit") getItemForForm(survey_id);
                    else manageQuestion(survey_id);

                });


            } else {

                swal("Error", data['message'], "error");

            }

        },
        error: function () {

            swal("Error", "There is unexpected error. Please try again.", "error");

        }

    });

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
                url: "ajax/ajax_campaign_surveys_management.php",
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
            url: "ajax/ajax_campaign_surveys_management.php",
            method: "GET",
            data: {
                "action": "get_update",
                "id": id
            },
            success: function (data) {

                if (data) {

                    $("form.create-form").trigger("reset");

                    $("#name").val(data['data']['name']);
                    $("#description").val(data['data']['description']);

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


function manageQuestion(id) {

    current_survey = id;

    $.ajax({
        url: "ajax/ajax_campaign_surveys_management.php",
        method: "GET",
        data: {
            "action": "get_questions",
            "id": id
        },
        success: function (data) {

            if (data['data'] != null) {

                let current_rules = "";


                data['data'] = JSON.parse(atob(data['data']));

                let choice_counter = 0;


                for (let i = 0; i < data['data'].length; i++) {

                    current_rules += "<tr>";

                    current_rules += "<td><input type='text' class='form-control' name='question[]' value='" + data['data'][i]['question'] + "'></td>";


                    current_rules += "<td>" +
                        "<select class='form-control' name='type[]'>" +
                        "<option value='text-single' " + (data['data'][i]['type'] === "text" ? "selected='selected'" : "") + ">Free Text - Single Line</option>" +
                        "<option value='select-single' " + (data['data'][i]['type'] === "select-single" ? "selected='selected'" : "") + ">Multiple Choice - One</option>" +
                        "<option value='select-multi' " + (data['data'][i]['type'] === "select-multi" ? "selected='selected'" : "") + ">Multiple Choice - Multiple</option>" +
                        "</select>" +
                        "</td>";

                    current_rules += "<td>" +
                        "<select class='form-control' name='required[]'>" +
                        "<option value='true' " + (data['data'][i]['required'] === "true" ? "selected='selected'" : "") + ">Yes</option>" +
                        "<option value='false' " + (data['data'][i]['required'] === "false" ? "selected='selected'" : "") + ">No</option>" +
                        "</select>" +
                        "</td>";


                    if (data['data'][i]['choice'].length > 0) {

                        current_rules += "<td>";

                        try {

                            let choices = $.parseJSON(data['data'][i]['choice']);

                            for (let kindex in choices) {

                                current_rules += "<div style='display: block; padding: 5px;'><input type='text' class='form-control' name='choice[" + choice_counter + "][]' value='" + choices[kindex] + "'></div>";

                            }

                        } catch (e) {

                            current_rules += "<div style='display: block; padding: 5px;'><input type='text' class='form-control' name='choice[" + choice_counter + "][]' value=''></div>";

                        }

                        current_rules += "</td>";

                    } else {

                        current_rules += "<td><div style='display: block; padding: 5px;'><input type='text' class='form-control' name='choice[" + choice_counter + "][]' value=''></div></td>";

                    }


                    current_rules += "<td><button type='button' class='btn btn-danger btn-icon btn-sm btn-delete-question'><i class='fa fa-times'></i></button>";
                    current_rules += "&nbsp;<button type='button' class='btn btn-primary btn-icon btn-sm btn-add-choice'><i class='fa fa-plus'></i></button></td>";

                    current_rules += "</tr>";

                    choice_counter += 1;


                }


                $("table.question-list > tbody").html(current_rules);


                $(".btn-add-choice").off().on("click", function () {


                    let current_question = $(this).parent("td").prev("td");

                    let choice_number = current_question.children("div:first").children("input").attr("name");

                    current_question.append("<div style='display: block; padding: 5px;'><input type='text' class='form-control' name='" + choice_number + "' value=''></div>");


                });


                $(".btn-delete-question").off().on("click", function () {


                    $(this).parent("td").parent("tr").remove();


                    let current_space = $("table.question-list > tbody");


                    if (current_space.children("tr").length === 0) {

                        current_space.html("<tr><td colspan='5' style='text-align: center;'>No item to display</td></tr>");

                    }


                });


            } else {

                $("table.question-list > tbody").html("<tr><td colspan='5' style='text-align: center;'>No item to display</td></tr>");

            }


            $("#question-modal").modal();


        },
        error: function (error) {

            $("table.question-list > tbody").html("<tr><td colspan='5' style='text-align: center;'>No item to display</td></tr>");

        }

    });


}
