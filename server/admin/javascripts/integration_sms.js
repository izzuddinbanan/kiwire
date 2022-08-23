String.prototype.capitalize = function() {

    return this.charAt(0).toUpperCase() + this.slice(1);

};


$(document).ready(function () {

    pull_data();
    update_input();

    $('.datetime').timepicker({
        timeFormat: 'HH:mm:ss',
        dropdown: true,
        scrollbar: true
    });


    // $('#is_24').on('change', function(){

    //     if($(this).is(':checked')){
    //         $('.time').hide();
    //     }else{
    //         $('.time').show();
    //     }
    // })

    $("#basic-tab").on("click", function () {

        $(".save-button").css("display", "block");
        $(".create-btn-prefix").css("display", "none");

    });


    $("#prefix-tab").on("click", function () {

        $(".save-button").css("display", "none");
        $(".create-btn-prefix").css("display", "block");

    });


    $(".cancel-button").on("click", function (e) {

        $(".create-form").trigger("reset");

        $("#inlineForm").modal("hide");

    });


    $("button.save-button").on("click", function (e) {

        let data = $("form").serialize();

        $.ajax({
            url: "ajax/ajax_integration_sms.php?action=update",
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
                url: "ajax/ajax_integration_sms.php?action=create",
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


    $(".change-provider").on("change", function (e) {

        update_input();

    });



    $(".btn-send-test").on("click", function(){

        let sms_config = $("form.update-form").serialize();
        let sms_number = $("input[name=smsto]").val();

        sms_config += "&action=test";
        sms_config += "&smsto=" + sms_number;

        if (sms_number.length > 3) {

            $.ajax({

                url: "/admin/ajax/ajax_integration_sms.php",
                method: "post",
                data: sms_config,
                success: function (response) {

                    if (response['status'] === "success") {


                        $(".sms-respond-space").html(response['message']);


                    } else {

                        swal("Error", response['message'], "error");

                    }

                },
                error: function (response) {

                    swal("Error", "There is an unexpected error. Please try again.", "error");

                }

            });

        }

    });



    $("div#test-modal").on("hidden.bs.modal", function () {

        $("input[name=smsto]").val("");
        $(".sms-respond-space").html("");

    });


});


function update_input() {

    let provider = $(".change-provider").val();


    $(".provider-input").css("display", "none");


    if (provider === "twilio") {

        $(".twillio").css("display", "block");

    } else if (provider === "synsms") {

        $(".synsms").css("display", "block");

    } else if (provider === "genusis") {

        $(".genusis").css("display", "block");

    } else {

        $(".generic").css("display", "block");

    }

}


//table prefix
function pull_data() {

    $.ajax({
        url: "ajax/ajax_integration_sms.php",
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
                    table_str += "<td>" + data['data'][x]['country'].capitalize() + "</td>";
                    table_str += "<td>" + data['data'][x]['prefix'] + "</td>";

                    table_str += "<td><a href=\"javascript:void(0);\" onclick=\"deleteItem('" + data['data'][x]['id'] + "')\" class='btn btn-icon btn-danger btn-xs mr-1 fa fa-times'></a></td>";

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
                url: "ajax/ajax_integration_sms.php",
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


function checkSMS() {

    $("#test-title").html("Sending the test sms..");
    $("#progress-modal").modal({show:true});
    $("#test-progress").css("display", "block");
    $("#sms_result_space").css("display", "none");

    $.ajax({
        url: 'ajax/general_ajax.php?action=sms&' + $("[data-for=sms-test]").serialize(),
        type: 'GET',
        success: function (x) {

            $("#test-title").html("Test Result");
            $("#test-progress").css("display", "none");
            $("#sms_result_data").html(x);
            $("#sms_result_space").css("display", "block");

        }

    });

}
