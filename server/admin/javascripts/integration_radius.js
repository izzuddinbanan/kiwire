

String.prototype.capitalize = function() {

    return this.charAt(0).toUpperCase() + this.slice(1);

};


$(document).ready(function () {

    pull_data();

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

    $(".btn-create, .btn-update").css("display", "none");


    $("#forward_profile").on("change", function () {

        if ($(this).val() === "link"){

            $(".profile-space").css("display", "block");

        } else {

            $(".profile-space").css("display", "none");

        }

    });


    $(".create-btn-radius").on("click", function () {

        $(".btn-create, .btn-update").css("display", "none");

        $(".btn-create").css("display", "block");

        $("#inlineForm").modal();

    });


    $(".cancel-button").on("click", function (e) {

        $(".create-form").trigger("reset");

        $("#inlineForm").modal("hide");

    });


    $(".btn-create").on("click", function (e) {


        let create_form = $(".create-form");


        if (create_form.parsley().validate()) {


            let data = create_form.serialize();


            $.ajax({
                url: "ajax/ajax_integration_radius.php?action=create",
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


            });


        }

    });


    $(".btn-update").on("click", function (e) {

        let create_form = $(".create-form");

        if (create_form.parsley().validate()) {

            let data = create_form.serialize();
            $.ajax({
                url: "ajax/ajax_integration_radius.php?action=edit_single_data",
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
    
    
            });
    
        }

       
    });

});


function pull_data() {

    $.ajax({
        url: "ajax/ajax_integration_radius.php",
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
                    table_str += "<td>" + data['data'][x]['domain'] + "</td>";
                    table_str += "<td>" + data['data'][x]['host'] + "</td>";
                    table_str += "<td>" + data['data'][x]['nasid'] + "</td>";
                    table_str += "<td>" + data['data'][x]['forward_profile'].capitalize() + "</td>";
                    table_str += "<td>" + data['data'][x]['profile'] + "</td>";


                    if (data['data'][x]['enabled'] === "y") {
                        table_str += "<td><span class=\"badge badge-success\">Active</span></td>";
                    } else {
                        table_str += "<td><span class=\"badge badge-danger\">Disabled</span></td>";
                    }

                    table_str += "<td><a href=\"javascript:void(0);\" onclick=\"getItemForForm('" + data['data'][x]['id'] + "')\" class='btn btn-icon btn-success btn-xs mr-1 fa fa-pencil'></a><a href=\"javascript:void(0);\" onclick=\"deleteItem('" + data['data'][x]['id'] + "')\" class='btn btn-icon btn-danger btn-xs mr-1 fa fa-times'></a></td>";

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
                url: "ajax/ajax_integration_radius.php",
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
            url: "ajax/ajax_integration_radius.php",
            method: "GET",
            data: {
                "action": "get_update",
                "id": id
            },
            success: function (data) {

                if (data) {

                    $("form.create-form").trigger("reset");


                    // if(data['data']['is_24_hour'] == 1){

                    //     $('#is_24').attr('checked', true);

                    //     $('#start_time').val();
                    //     $('#stop_time').val();
                        
                    //     $('.time').hide();
                        
                    // }else{

                    //     $('#is_24').prop('checked', false);
                        
                    //     $('#start_time').val(data['data']['start_time']).trigger('change');
                    //     $('#stop_time').val(data['data']['stop_time']).trigger('change');

                    //     $('.time').show();

                    // }

                    $("#domain").val(data['data']['domain']);
                    $("#host").val(data['data']['host']);
                    $("#secret").val(data['data']['secret']);

                    $("#nasid").val(data['data']['nasid']);

                    if ((data['data']['use_domain']) === 'y') {
                        $('#use_domain').prop("checked", true);
                    } else {
                        $('#use_domain').prop("checked", false);
                    }

                    $("#forward_profile").val(data['data']['forward_profile']);

                    $("#profile").val(data['data']['profile']).trigger("change");

                    $("#validity").val(data['data']['validity']);
                    $("#keyword_str").val(data['data']['keyword_str']);

                    $("#data_type").val(data['data']['data_type']);
                    $("#allowed_zone").val(data['data']['allowed_zone']);

                    if ((data['data']['enabled']) === 'y') {
                        $('#enabled').prop("checked", true);
                    } else {
                        $('#enabled').prop("checked", false);
                    }

                    $("#reference").val(data['data']['id']);

                    $(".btn-create, .btn-update").css("display", "none");

                    $(".btn-create").css("display", "none");
                    $(".btn-update").css("display", "block");


                    $("#inlineForm").modal();

                }

            },
            error: function (error) {

                swal("Error", "There is an error", "error");

            }

        });

    }

}

