
var type = null, id = null;

$(document).ready(function () {

    pull_data();
    update_input();


    //hide button update/create

    $(".btn-create, .btn-update").css("display", "none");


    $(".create-btn-ads").on("click", function () {


        $(".btn-create, .btn-update").css("display", "none");

        $(".btn-create").css("display", "block");

        $("#inlineForm").modal();


    });


    //reset form after cancel

    $(".cancel-button").on("click", function (e) {


        $(".create-form").trigger("reset");

        $(".custom-file-label").html("");


        $("#inlineForm").modal("hide");


    });


    $(".btn-create").on("click", function (e) {


        let create_form = $(".create-form");

        if (create_form.parsley().validate()) {


            let data = new FormData($("form.create-form")[0]);


            $.ajax({
                url: "ajax/ajax_campaign_ads_management.php?action=create",
                method: "post",
                enctype: 'multipart/form-data',
                processData: false,
                cache: false,
                contentType: false,
                data: data,
                success: function (data) {

                    if (data['status'] === "success") {


                        $(".create-form").trigger("reset");

                        $(".custom-file-label").html("");

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


    //update button

    $(".btn-update").on("click", function (e) {

        let create_form = $(".create-form");

        if (create_form.parsley().validate()) {

            let data = new FormData($("form.create-form")[0]);
    
            $.ajax({
                url: "ajax/ajax_campaign_ads_management.php?action=edit_single_data",
                method: "post",
                enctype: 'multipart/x-www-form-urlencoded',
                processData: false,
                cache: false,
                contentType: false,
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


    $("#inlineForm").on("hidden.bs.modal", function () {


        $("form.create-form").trigger("reset");

        $("#type").val("img").trigger("change");

        $("input[name=fn_desktop]").parent().parent().css("display", "block");

        $("input[name=fn_tablet]").parent().parent().css("display", "block");

        $("input[name=fn_phone]").parent().parent().css("display", "block");

        $(".custom-file-label").html("");

        $(".current-file").css("display", "none").html("");

        $("#random").attr("checked", false);


    });


});


$(".change-provider").on("change", function (e) {

    update_input();

});


function update_input() {

    let provider = $(".change-provider").val();


    $(".provider-input").css("display", "none");


    if (provider === "img") {

        $(".img").css("display", "block");

    } else if (provider === "vid") {

        $(".vid").css("display", "block");

    } else if (provider === "youtube") {

        $(".youtube").css("display", "block");

    } else if (provider === "msg") {

        $(".msg").css("display", "block");

    } else {

        $(".json").css("display", "block");

    }

}

function pull_data() {


    $.ajax({
        url: "ajax/ajax_campaign_ads_management.php",
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
                    table_str += "<td>" + data['data'][x]['remark'] + "</td>";

                    if (data['data'][x]['type'] === "img") {

                        table_str += "<td>Image</td>";

                    } else if (data['data'][x]['type'] === "vid") {

                        table_str += "<td>Video</td>";

                    } else if (data['data'][x]['type'] === "youtube") {

                        table_str += "<td>Youtube</td>";

                    } else if (data['data'][x]['type'] === "msg") {

                        table_str += "<td>Message</td>";

                    } else if (data['data'][x]['type'] === "json") {

                        table_str += "<td>External</td>";

                    }


                    table_str += "<td>" + data['data'][x]['updated_date'] + "</td>";

                    table_str += "<td><a href='javascript:void(0);' onclick=\"getItemForForm('" + data['data'][x]['id'] + "')\" class='btn btn-icon btn-success btn-xs mr-1 fa fa-pencil'></a><a href='javascript:void(0);' onclick=\"deleteItem('" + data['data'][x]['id'] + "')\" class='btn btn-icon btn-danger btn-xs mr-1 fa fa-times'></a></td>";

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
                url: "ajax/ajax_campaign_ads_management.php",
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

    $.ajax({
        url: "ajax/ajax_campaign_ads_management.php",
        method: "GET",
        data: {
            "action": "get_update",
            "id": id
        },
        success: function (data) {

            if (data['status'] === "success") {

                $("form.create-form").trigger("reset");

                $(".custom-file-label").html("");

                $("#adsname").val(data['data']['name']);
                $("#msg").val(data['data']['msg']);
                $("#remark").val(data['data']['remark']);

                $("#type").val(data['data']['type']).trigger("change");

                $("#captcha_txt").val(data['data']['captcha_txt']);
                $("#link").val(data['data']['link']);


                if (data['data']['type'] === "img") {


                    if (data['data']['fn_desktop'].length > 0) {

                        $(".current-file.fn_desktop").html("<img src='/custom/" + data['data']['tenant_id'] + "/images/" + data['data']['fn_desktop'] + "' style='height: 100%; width: 100%; padding: 10px; border: #c5c5c5 thin solid' /><label>Desktop</label><button type='button' class='btn btn-danger btn-sm btn-icon pull-right btn-delete-img my-50' data-type='fn_desktop'><i class='fa fa-times'></i></button>").css("display", "block");

                        $("input[name=fn_desktop]").parent().parent().css("display", "none");

                    }

                    if (data['data']['fn_tablet'].length > 0) {

                        $(".current-file.fn_tablet").html("<img src='/custom/" + data['data']['tenant_id'] + "/images/" + data['data']['fn_tablet'] + "' style='height: 100%; width: 100%; padding: 10px; border: #c5c5c5 thin solid' /><label>Tablet</label><button type='button' class='btn btn-danger btn-sm btn-icon pull-right btn-delete-img my-50' data-type='fn_tablet'><i class='fa fa-times'></i></button>").css("display", "block");

                        $("input[name=fn_tablet]").parent().parent().css("display", "none");

                    }

                    if (data['data']['fn_phone'].length > 0) {

                        $(".current-file.fn_phone").html("<img src='/custom/" + data['data']['tenant_id'] + "/images/" + data['data']['fn_phone'] + "' style='height: 100%; width: 100%; padding: 10px; border: #c5c5c5 thin solid' /><label>Phone</label><button type='button' class='btn btn-danger btn-sm btn-icon pull-right btn-delete-img my-50' data-type='fn_phone'><i class='fa fa-times'></i></button>").css("display", "block");

                        $("input[name=fn_phone]").parent().parent().css("display", "none");

                    }


                } else if (data['data']['type'] === "vid") {


                    if (data['data']['fn_desktop'].length > 0) {

                        $(".current-file.fn_desktop").html("<video src='/custom/" + data['data']['tenant_id'] + "/images/" + data['data']['fn_desktop'] + "' style='height: 100%; width: 100%;' /><label>Desktop</label><button type='button' class='btn btn-danger btn-sm btn-icon pull-right my-50' data-type='fn_desktop'><i class='fa fa-times'></i></button>").css("display", "block");

                        $("input[name=fn_desktop]").parent().parent().css("display", "none");

                    }

                    if (data['data']['fn_tablet'].length > 0) {

                        $(".current-file.fn_tablet").html("<video src='/custom/" + data['data']['tenant_id'] + "/images/" + data['data']['fn_tablet'] + "' style='height: 100%; width: 100%;' /><label>Tablet</label><button type='button' class='btn btn-danger btn-sm btn-icon pull-right my-50' data-type='fn_tablet'><i class='fa fa-times'></i></button>").css("display", "block");

                        $("input[name=fn_tablet]").parent().parent().css("display", "none");

                    }

                    if (data['data']['fn_phone'].length > 0) {

                        $(".current-file.fn_phone").html("<video src='/custom/" + data['data']['tenant_id'] + "/images/" + data['data']['fn_phone'] + "' style='height: 100%; width: 100%;' /><label>Phone</label><button type='button' class='btn btn-danger btn-sm btn-icon pull-right my-50' data-type='fn_phone'><i class='fa fa-times'></i></button>").css("display", "block");

                        $("input[name=fn_phone]").parent().parent().css("display", "none");

                    }


                }


                $(".btn-delete-img").off().on("click", function () {

                    type = $(this).data("type");

                    id = $("#reference").val();


                    if (type.length > 0 && id.length > 0){


                        $.ajax({
                            url: "/admin/ajax/ajax_campaign_ads_management.php",
                            method: "post",
                            data:{
                                action: "delete_image",
                                ads: id,
                                type: type
                            },
                            success: function (response) {

                                if (response['status'] === "success"){


                                    swal("Success", response['message'], "success");

                                    $("input[name=" + type + "]").parent().parent().css("display", "block");

                                    $(".current-file." + type).html("").css("display", "none");


                                } else {

                                    swal("Error", response['status'], "error");

                                }

                            },
                            error: function (response) {

                                swal("Error", "There is an error. Please try again later.", "error");

                            }
                        });


                    }


                });


                $(".current-file > img").off().on("click", function () {

                    let click_url = $(this).attr("src");

                    if (click_url.length > 0) {

                        window.open(click_url, '_blank', 'height=480,width=600,resizable=yes,scrollbars=no,toolbar=no,menubar=no,location=no,directories=no,status=yes');

                    }

                });


                $("#viewport").val(data['data']['viewport']);
                $("#json_url").val(data['data']['json_url']);
                $("#json_path").val(data['data']['json_path']);

                if (data['data']['random'] === "y") {

                    $("#random").attr("checked", true);

                }

                $("#ads_max_no").val(data['data']['ads_max_no']);


                // if (data['data']['mapping'].length > 2) {

                //     $("#mapping").val(JSON.stringify($.parseJSON(atob(data['data']['mapping']))));

                // }

                if(data['data']['mapping']) {

                    if (data['data']['mapping'].length > 2) {
                        
                        $("#mapping").val(JSON.stringify($.parseJSON(atob(data['data']['mapping']))));
                        
                    }
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

$('#current_image').css("display", "none");
