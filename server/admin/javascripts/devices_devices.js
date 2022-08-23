var change_tenant, change_id;


$(document).ready(function () {

    pull_data();
    update_change_devicetype();
    update_change_vendor();

    $('.datetime').timepicker({
        timeFormat: 'HH:mm:ss',
        dropdown: true,
        scrollbar: true
    });


    $('#enabled').on('change', function(){

        if($(this).is(':checked')){
            $('.time').hide();
        }else{
            $('.time').show();
        }
    })

    //hide button update/create

    $(".btn-create, .btn-update").css("display", "none");


    $(".create-btn-device").on("click", function () {


        $(".btn-create, .btn-update").css("display", "none");

        $(".btn-create").css("display", "block");

        $("#inlineForm").modal();


    });


    //reset form after cancel

    $(".cancel-button").on("click", function (e) {

        $("#inlineForm").modal("hide");


    });


    $(".btn-create").on("click", function (e) {

        let create_form = $(".create-form");

        if (create_form.parsley().validate()) {

            let data = create_form.serialize();

            $.ajax({
                url: "ajax/ajax_devices_devices.php?action=create",
                method: "post",
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


    $("#inlineForm").on("hide.bs.modal", function (){


        $("select#tenant_id").attr("disabled", false);

        $(".create-form").trigger("reset");

        update_change_vendor();


    });


    //update button

    $(".btn-update").on("click", function (e) {


        $("select#tenant_id").attr("disabled", false);

        let create_form = $(".create-form");

        if (create_form.parsley().validate()) {

            let data = create_form.serialize();
            
            $.ajax({
                url: "ajax/ajax_devices_devices.php?action=edit_single_data",
                method: "post",
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

    $(".btn-import").on("click", function () {


        let import_account = $("form.import_account");


        if (import_account.parsley().validate() === false) {

            return;

        }


        let data = new FormData(import_account[0]);

        $.ajax({
            url: "/admin/ajax/ajax_devices_devices.php?action=import_account",
            method: "POST",
            data: data,
            processData: false,
            contentType: false,
            success: function (data) {

                if (data['status'] === "success") {


                    // table_data.ajax.reload();


                    $("form.import_account").trigger("reset").parsley().reset();

                    $(".custom-file-label").html("");

                    $("#import-modal").modal("hide");


                    swal("Success", data['message'], "success");

                    window.location.href = "/temp/" + data['data'];

                    pull_data();


                } else {

                    swal("Error", data['message'], "error");

                }
            },
            error: function (data) {

                swal("Error", "There is unexpected error. Please try again.", "error");

            }
        });

    });

});


function pull_data() {

    $.ajax({
        url: "ajax/ajax_devices_devices.php",
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


                    data['data'][x]['vendor'] = data['data'][x]['vendor'].split("_");

                    if (data['data'][x]['vendor'].length > 1){

                        data['data'][x]['vendor'][1] = data['data'][x]['vendor'][1].toUpperCase();

                    }

                    data['data'][x]['vendor'] = data['data'][x]['vendor'].join("_");


                    table_str += "<tr>";
                    table_str += "<td>" + (x + 1) + "</td>";
                    table_str += "<td>" + data['data'][x]['unique_id'] + "</td>";
                    table_str += "<td>" + data['data'][x]['device_ip'] + "</td>";

                    table_str += "<td>" + data['data'][x]['device_type'] + "</td>";
                    table_str += "<td>" + data['data'][x]['vendor'] + "</td>";
                    table_str += "<td>" + data['data'][x]['location'] + "</td>";
                    table_str += "<td>" + data['data'][x]['description'] + "</td>";

                    if (max_column === true){

                        table_str += "<td>" + data['data'][x]['tenant_id'] + "</td>";

                    }

                    table_str += "<td>";
                    table_str += "<a href=\"javascript:void(0);\" onclick=\"getItemForForm('" + data['data'][x]['id'] + "','" + data['data'][x]['tenant_id'] + "')\" class='btn btn-icon btn-success btn-xs mr-1 fa fa-pencil'></a>";
                    table_str += "<a href=\"javascript:void(0);\" onclick=\"deleteItem('" + data['data'][x]['id'] + "','" + data['data'][x]['tenant_id'] + "')\" class='btn btn-icon btn-danger btn-xs mr-1 fa fa-times'></a>";

                    if (max_column === true) {

                        table_str += "<a href=\"javascript:void(0);\" onclick=\"changeTenant('" + data['data'][x]['id'] + "','" + data['data'][x]['tenant_id'] + "')\" class='btn btn-icon btn-warning btn-xs mr-1 fa fa-arrow-right'></a>";

                    }

                    table_str += "</td></tr>";


                }

                $(".table-data>tbody").html(table_str);

                $(".table-data").DataTable({
                    dom: dt_position,
                    pageLength: dt_page,
                    buttons: [
                        {
                            text: 'Import',
                            action: function (e, d, n, c) {
            
                                $("#import-modal").modal();
            
                            }
                        },
                        {
                            extend: 'copyHtml5',
                            exportOptions: {
                                columns: [0, ':visible']
                            }
                        },
                        {
                            extend: 'csvHtml5'
                        },
                        {
                            extend: 'pdfHtml5',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'print',
                            exportOptions: {
                                columns: ':visible'
                            }
                        }
                    ],
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


function deleteItem(id, tenant) {

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
                url: "ajax/ajax_devices_devices.php",
                method: "post",
                data: {
                    "action": "delete",
                    "id": id,
                    "tenant_id": tenant,
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

function getItemForForm(id, tenant) {

    if (id.length) {

        $.ajax({
            url: "ajax/ajax_devices_devices.php",
            method: "GET",
            data: {
                "action": "get_update",
                "id": id,
                "tenant_id": tenant
            },
            success: function (data) {

                if (data) {

                  

                    $("form.create-form").trigger("reset");


                    // if(data['data']['is_24_hour'] == 1){

                    //     $('#enabled').attr('checked', true);

                    //     $('#start_time').val();
                    //     $('#stop_time').val();
                        
                    //     $('.time').hide();
                        
                    // }else{

                    //     $('#enabled').prop('checked', false);
                        
                    //     $('#start_time').val(data['data']['start_time']).trigger('change');
                    //     $('#stop_time').val(data['data']['stop_time']).trigger('change');

                    //     $('.time').show();

                    // }

                    if(data['data']['is_virtual'] == 1){

                        $('#is_virtual').attr('checked', true);
                    }else {
                        $('#is_virtual').attr('checked', false);

                    }



                    $("select#tenant_id").val(data['data']['tenant_id']).attr("disabled", true);


                    $("#device_type").val(data['data']['device_type']);
                    $("#vendor").val(data['data']['vendor']);
                    $('#vendor').trigger('change');
                    $("#unique_id").val(data['data']['unique_id']);
                    $("#device_ip").val(data['data']['device_ip']);

                    update_change_vendor();

                    $("#location").val(data['data']['location']);
                    $("#username").val(data['data']['username']);
                    $("#password").val(data['data']['password']);
                    $("#shared_secret").val(data['data']['shared_secret']);

                    $("#coa_port").val(data['data']['coa_port']);
                    $("#description").val(data['data']['description']);
                    $("#seamless_type").val(data['data']['seamless_type']);
                    $("#community").val(data['data']['community']);

                    $("#snmpv").val(data['data']['snmpv']);
                    $("#mib").val(data['data']['mib']);

                    $("#monitor_method").val(data['data']['monitor_method']).trigger("reset");

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


$(".change-device-type").on("change", function (e) {

    update_change_devicetype($(this).val());

});


$(".change-vendor").on("change", function (e) {

    update_change_vendor();

});


function update_change_devicetype(provider = "controller") {


    // let provider = $(".change-device-type").val();

    $(".provider-input").css("display", "none");


    if (provider === "controller") {

        $(".controller").css("display", "block");

    }


}


function changeTenant(id, tenant){

    change_tenant = tenant;
    change_id = id;

    $("#new-tenant").val(change_tenant);
    $("#change-tenant").modal();

}


$(".btn-change-tenant").on("click", function (){


    let new_tenant = $("#new-tenant").val();


    if (change_tenant !== new_tenant) {


        $.ajax({
            url: "/admin/ajax/ajax_devices_devices.php",
            method: "post",
            data: {
                action: "change_tenant",
                tenant: change_tenant,
                tenant_new: new_tenant,
                id: change_id
            },
            success: function (response) {


                if (response['status'] === "success") {


                    pull_data();

                    $("#change-tenant").modal("hide");

                    swal("Success", response['message'], "success");


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


function update_change_vendor() {


    let provider = $(".change-vendor").val();

    $(".provider-input").css("display", "block");


    if (provider === "wifidog") {

        $("select[name=monitor_method]").append("<option value='wifidog' data-i18n='monitor_method_wifidog'>Wifidog</option>")

        $(".wifidog").css("display", "none");
        $(".wifidog-only").css("display", "block");

    } else {

        $("select[name=monitor_method]").children('option[value="wifidog"]').remove();

        $(".wifidog-only").css("display", "none");

    }


} 