$(document).ready(function () {


    pull_data();

});


function pull_data() {

    $.ajax({
        url: "ajax/ajax_policy_verify_device.php",
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

                    table_str += "<td>" + data['data'][x]['username'] + "</td>";
                    table_str += "<td>" + data['data'][x]['mac_address'] + "</td>";


                    if (data['data'][x]['dtype'] === null) {

                        table_str += "<td>" + 'Unknown' + "</td>";

                    } else {

                        table_str += "<td>" + data['data'][x]['dtype'] + "</td>";

                    }



                    if (data['data'][x]['dbrand'] === null) {

                        table_str += "<td>" + 'Unknown' + "</td>";

                    } else {

                        table_str += "<td>" + data['data'][x]['dbrand'] + "</td>";

                    }


                  
                    if (data['data'][x]['dmodel'] === null) {

                        table_str += "<td>" + 'Unknown' + "</td>";

                    } else {

                        table_str += "<td>" + data['data'][x]['dmodel'] + "</td>";

                    }


                    if (data['data'][x]['dos'] === null) {

                        table_str += "<td>" + 'Unknown' + "</td>";

                    } else {
          
                        table_str += "<td>" + data['data'][x]['dos'] + "</td>";

                    }
                   

                    if (data['data'][x]['verified'] === "n") {

                        table_str += "<td><span class=\"badge badge-danger\">Unverified</span></td>";

                    } else {

                        table_str += "<td><span class=\"badge badge-success\">Verified</span></td>";

                    }


                    table_str += "<td>";


                    if (data['data'][x]['verified'] === "n") {

                        table_str += "<a href='javascript:void(0);' data-id='" + data['data'][x]['id'] + "' class='btn btn-icon btn-success btn-xs mr-1 fa fa-check-square-o btn-admin-verified'></a>";

                    } else {

                        table_str += "<a href='javascript:void(0);' data-id='" + data['data'][x]['id'] + "' class='btn btn-icon btn-warning btn-xs mr-1 fa fa-ban btn-admin-unverified'></a>";

                    }

                    table_str += "<a href='javascript:void(0);' data-id='" + data['data'][x]['id'] + "' class='btn btn-icon btn-primary btn-xs mr-1 fa fa-search btn-admin-view'></a>";
                    table_str += "<a href='javascript:void(0);' data-id='" + data['data'][x]['id'] + "' class='btn btn-icon btn-danger btn-xs mr-1 fa fa-times btn-admin-delete'></a>";

                    table_str += "</td>";

                    table_str += "</tr>";


                }


                $(".table-data > tbody").html(table_str);


                $(".table-data").dataTable({
                    dom: dt_position,
                    pageLength: dt_page,
                    buttons: dt_btn,
                    "fnDrawCallback": function() {
                        if($('.dataTables_filter').find('input').hasClass('form-control-sm')){
                            $('.dataTables_filter').find('input').removeClass('form-control-sm')
                        }

                    }
                });


                $(".btn-admin-verified").off().on("click", function () {

                    let id = $(this).data("id");

                    swal({

                        title: "Approve this device?",
                        text: "",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Yes, approve it!",
                        cancelButtonText: "Cancel"

                    }).then(function (x) {

                        if (x['value'] === true) {

                            $.ajax({
                                url: "ajax/ajax_policy_verify_device.php",
                                method: "GET",
                                data: {
                                    "action": "verify_device",
                                    "id": id
                                },

                                success: function (data) {


                                    if (data['status'] === "success") {


                                        pull_data();


                                        swal("Success", data['message'], "success");


                                    } else {


                                        swal("Error", data['message'], "error");


                                    }

                                },

                            });

                        }

                    });


                });


                $(".btn-admin-unverified").off().on("click", function () {

                    let id = $(this).data("id");

                    swal({

                        title: "Unapprove this device?",
                        text: "",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Yes, unapprove it!",
                        cancelButtonText: "Cancel"

                    }).then(function (x) {

                        if (x['value'] === true) {

                            $.ajax({
                                url: "ajax/ajax_policy_verify_device.php",
                                method: "GET",
                                data: {
                                    "action": "verify_device",
                                    "id": id
                                },

                                success: function (data) {


                                    if (data['status'] === "success") {


                                        pull_data();


                                        swal("Success", data['message'], "success");


                                    } else {


                                        swal("Error", data['message'], "error");


                                    }

                                },

                            });

                        }

                    });


                });


                $(".btn-admin-delete").off().on("click", function () {

                    let id = $(this).data("id");

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
                                url: "ajax/ajax_policy_verify_device.php",
                                method: "GET",
                                data: {
                                    "action": "delete",
                                    "id": id
                                },

                                success: function (data) {


                                    if (data['status'] === "success") {

                                        pull_data();

                                        swal("Success", data['message'], "success");

                                    } else {

                                        swal("Error", data['message'], "error");


                                    }

                                },

                            });

                        }

                    });

                });


                $(".btn-admin-view").off().on("click", function () {


                    let id = $(this).data("id");


                    $.ajax({
                        url: "ajax/ajax_policy_verify_device.php",
                        method: "GET",
                        data: {
                            "action": "get_detail",
                            "id": id
                        },
                        success: function (data) {


                            if (data['status'] === "success") {


                                $(".registered_to").html(data['data']['username']);
                                $(".mac_address").html(data['data']['mac_address']);
                                

                                if  ((data['data']['dtype'] === null) && (data['data']['dbrand'] === null) && (data['data']['dmodel'] === null) && (data['data']['dos'] === null) ) {

                                    $(".device_type").html('Unknown');
                                    $(".device_brand").html('Unknown');
                                    $(".device_model").html('Unknown');
                                    $(".device_os").html('Unknown');

                                } else {

                                    $(".device_type").html(data['data']['dtype']);
                                    $(".device_brand").html(data['data']['dbrand']);
                                    $(".device_model").html(data['data']['dmodel']);
                                    $(".device_os").html(data['data']['dos']);

                                }
                               
                               
                                if (data['data']['verified'] === "n") {


                                    $(".status").html('<span class=\"badge badge-danger\">Unverified</span>');


                                } else {

                                    $(".status").html('<span class=\"badge badge-success\">Verified</span>');


                                }

                                $("#reference").val(data['data']['id']);

                                $("#device-detail").modal();


                            }

                        },
                        error: function () {

                            swal("Error", "There is an error", "error");

                        }

                    });

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
