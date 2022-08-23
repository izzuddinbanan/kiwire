
var current_action = "create_data_vip", current_reference = "";

$(document).ready(function () {

    $('.datetime').timepicker({
        timeFormat: 'HH:mm:ss',
        dropdown: true,
        scrollbar: true
    });


    $('#is_24').on('change', function(){

        if($(this).is(':checked')){
            $('.time').hide();
        }else{
            $('.time').show();
        }
    })

    $("#pass_mode").change(function() {

        
        if ($("#pass_mode").val() == "2" || $("#pass_mode").val()  == "3" || $("#pass_mode").val()  == "4") {

            $("#pass_first_login").css("display", "block");

        } else {

            $("#pass_first_login").css("display", "none");

        }

    });    



    $(".save-button").on("click", function () {


        let data = $("form.update-form").serialize();

        data += "&action=update_pms";


        $.ajax({
            url: "/admin/ajax/ajax_integration_pms.php",
            method: "post",
            data: data,
            success: function (response) {

                if (response['status'] === "success"){

                    swal("Success", response['message'], "success");

                } else {

                    swal("Error", response['message'], "error");

                }


            },
            error: function (response) {

                swal("Error", "There is an error. Please try again.", "error");

            }
        });


    });


    $("#vip_code-tab").on("click", function () {

        pull_data();

    });


    $("#main-tab").on("click", function () {

        $(".save-button").css("display", "block");
        $(".create-btn-vip").css("display", "none");

    });


    $(".create-btn-vip").on("click", function () {

        $("#inlineForm").modal();

    });



    $("button.btn-create").on("click", function () {


        let data_vip = $(".create-form").serialize();

        data_vip += "&action=" + current_action;
        data_vip += "&reference=" + current_reference;

        $.ajax({
            url : "/admin/ajax/ajax_integration_pms.php",
            method: "post",
            data: data_vip,
            success: function (response) {

                if (response['status'] === "success"){

                    pull_data();

                    $("#inlineForm").modal("hide");

                    swal("Success", response['message'], "success");

                } else {

                    swal("Error", response['message'], "error");

                }

            },
            error: function (response) {

                swal("Error", "There is an internal error. Please try again. ", "error");

            }
        });


    });


    $(".btn-db-swap").on("click", function () {

        $.ajax({
            url : "/admin/ajax/ajax_integration_pms.php",
            method: "post",
            data: {
                action: "dbswap"
            },
            success: function (response) {

                if (response['status'] === "success"){

                    swal("Success", response['message'], "success");

                } else {

                    swal("Error", response['message'], "error");

                }

            },
            error: function (response) {

                swal("Error", "There is an internal error. Please try again. ", "error");

            }
        });

    });


    $("#inlineForm").on("hide.bs.modal", function () {


        $("button.btn-create").html("Create");

        $("form.create-form").trigger("reset");

        current_action = "create_data_vip";


    });


    $("select.change-provider").on("change", function () {


        let pms_vendor = $(this).val();


        if (pms_vendor !== "idb"){


            if (pms_vendor === "ezee") {


                $(".idb-fields").css("display", "none");

                $(".idb-remove").css("display", "none");

                $(".ezee-fields").css("display", "block");

                $(".ezee-remove").css("display", "none");


            } else if (pms_vendor === "json"){


                $(".idb-fields").css("display", "none");

                $(".idb-remove").css("display", "none");

                $(".ezee-remove").css("display", "none");

                $(".pms-credential").css("display", "block");
                
                $(".pms-keys").css("display", "none");



            } else if (pms_vendor === "rhealta") {

                
                $(".idb-fields").css("display", "none");

                $(".idb-remove").css("display", "none");

                $(".ezee-remove").css("display", "none");

                $(".pms-credential").css("display", "none");

                $(".pms-keys").css("display", "block");


                
            } else {


                $(".pms-credential").css("display", "none");

                $(".idb-fields").css("display", "none");

                $(".idb-remove, .ezee-remove").css("display", "block");

            }


        } else {


            $(".pms-credential").css("display", "none");

            $(".idb-fields").css("display", "block");

            $(".idb-remove").css("display", "none");


        }



    });


    $(".change-provider").trigger("change");


});


function pull_data() {

    $.ajax({
        url: "/admin/ajax/ajax_integration_pms.php",
        method: "get",
        data: {
            action: "get_vip_list"
        },
        success: function (response) {

            if (response['status'] === "success"){


                if($.fn.dataTable.isDataTable('.table-data')){

                    $(".table-data").DataTable().destroy();

                }


                let table_data = '';

                if (response['data'].length > 0) {

                    for (let kindex in response['data']) {

                        table_data += "<tr>";
                        table_data += "<td>" + (parseInt(kindex) + 1) + "</td>";
                        table_data += "<td>" + response['data'][kindex]['code'] + "</td>";
                        table_data += "<td>" + response['data'][kindex]['profile'] + "</td>";
                        table_data += "<td>" + response['data'][kindex]['price'] + "</td>";
                        table_data += "<td>"
                        table_data += "<a href='#' data-reference='" + response['data'][kindex]['id'] + "' class='btn btn-icon btn-success btn-xs btn-edit'><i class='fa fa-pencil'></i></a>";
                        table_data += "<a href='#' data-reference='" + response['data'][kindex]['id'] + "' class='btn btn-icon btn-danger btn-xs btn-delete ml-1'><i class='fa fa-times'></i></a>";
                        table_data += "</td>";
                        table_data += "</tr>";

                    }

                }


                $("table.table-data > tbody").html(table_data);


                $("table.table-data").dataTable({
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

                        $(".btn-edit").off().on("click", function () {


                            let reference = $(this).data("reference");

                            $.ajax({
                                url : "/admin/ajax/ajax_integration_pms.php",
                                method: "post",
                                data: {
                                    action: "update_data_vip",
                                    reference: reference
                                },
                                success: function (response) {


                                    if (response['status'] === "success"){


                                        $("input[name=code]").val(response['data']['code']);
                                        $("select[name=profile]").val(response['data']['profile']).trigger("change");
                                        $("input[name=price]").val(response['data']['price']);


                                        current_reference = response['data']['id'];

                                        current_action = "save_data_vip";


                                        $("button.btn-create").html("Update");

                                        $("#inlineForm").modal();


                                    } else {

                                        swal("Error", response['message'], "error");

                                    }

                                },
                                error: function (response) {

                                    swal("Error", "There is an internal error. Please try again. ", "error");

                                }
                            });


                        });


                        $(".btn-delete").off().on("click", function () {

                          let reference = $(this).data("reference");

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
                                      url: "ajax/ajax_integration_pms.php",
                                      method: "POST",
                                      data: {
                                          "action": "delete_vip_list",
                                          reference: reference,
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


                    }
                });


                $(".save-button").css("display", "none");

                $(".create-btn-vip").css("display", "block");


            } else {

                console.log(response);

            }

        },
        error: function (response) {

            console.log(response);

        }
    });


}
