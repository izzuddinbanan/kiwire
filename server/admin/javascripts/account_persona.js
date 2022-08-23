var current_action = "create";

$(document).ready(function () {


    pull_data();


    $(".create-btn-persona").on("click", function () {

        $("#inlineForm").modal();

    });


    $(".btn-add-rule").on("click", function () {

        let new_field = $(".field-data:first").clone();

        new_field.find("select").val("").trigger("change");
        new_field.find("input").val("");

        $(".field-list").append(new_field);

    });


    $("#inlineForm").on("hidden.bs.modal", function () {


        current_action = "create";

        $('.field-data').slice(1).remove();

        $("form.rule-form").trigger("reset");

        $(".btn-create").html("Create");


    });


    $(".btn-create").on("click", function () {


        let data = $("form.rule-form").serialize();

        $.ajax({
            url: "/admin/ajax/ajax_account_persona.php?action=" + current_action,
            method: "post",
            data: data,
            success: function (response) {

                if (response['status'] === "success"){


                    pull_data();

                    swal("Success", response['message'], "success");

                    $("#inlineForm").modal("toggle");


                } else {

                    swal("Error", response['message'], "error");

                }


            },
            error: function (response) {

                swal("Error", "There is unexpected error. Please try again.", "error");

            }
        });


    });




});


function pull_data() {

    $.ajax({
        url: "ajax/ajax_account_persona.php",
        method: "GET",
        data: {
            "action": "get_all"
        },
        success: function (data) {

            if (data['status'] === "success") {

                if($.fn.dataTable.isDataTable('.table-data')){

                    $(".table-data").DataTable().destroy();

                }

                let table_str = "";

                for (let x = 0; x < data['data'].length; x++) {

                    table_str += "<tr>";

                    table_str += "<td>" + (x + 1) + "</td>";
                    table_str += "<td>" + data['data'][x]['name'] + "</td>";
                    table_str += "<td>" + data['data'][x]['updated_date'] + "</td>";
                    table_str += "<td><a href='#' data-name='" + data['data'][x]['name'] + "' class='btn btn-icon btn-success btn-xs mr-1 btn-p-edit fa fa-pencil'></a>";
                    table_str += "<a href='#' data-name='" + data['data'][x]['name'] + "' class='btn btn-icon btn-danger btn-xs mr-1 btn-p-delete fa fa-times'></a></td>";

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
                    "fnDrawCallback": function() {
                        if($('.dataTables_filter').find('input').hasClass('form-control-sm')){
                            $('.dataTables_filter').find('input').removeClass('form-control-sm')
                        }

                    }
                });


                $(".btn-p-edit").on("click", function () {

                    updatePersona($(this).data("name"));

                });


                $(".btn-p-delete").on("click", function () {

                    deletePersona($(this).data("name"));

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

function deletePersona(id) {

    if (id.length) {

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
                  url: "ajax/ajax_account_persona.php",
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

}


function updatePersona(id){

    if (id.length){


        $.ajax({
            url: "/admin/ajax/ajax_account_persona.php?action=get_update",
            method: "post",
            data: {
                id: id
            },
            success: function (response) {

                if (response['status'] === "success"){


                    $("input[name=name]").val(response['data']['name']);
                    $("input[name=reference]").val(response['data']['id']);

                    let clone_element = $(".field-data:first");

                    for (let x = 0; x < response['data']['rule'].length; x++){

                        let new_field = clone_element.clone();

                        new_field.find("select.field").val(response['data']['rule'][x]['field']).trigger("change");
                        new_field.find("select.operator").val(response['data']['rule'][x]['operator']).trigger("change");
                        new_field.find("input").val(response['data']['rule'][x]['value']);

                        $(".field-list").append(new_field);


                    }


                    $(".btn-create").html("Update");

                    clone_element.remove();

                    $("#inlineForm").modal();

                    current_action = "edit_single_data";


                } else {

                    swal("Error", response['message'], "error");

                }

            },
            error: function (response) {

                swal("Error", "There is unexpected error. Please try again.", "error");

            }
        });


    }

}
