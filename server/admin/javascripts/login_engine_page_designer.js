$(document).ready(function () {

    pull_data();


    $(".btn-duplicate-page").on("click", function () {

        $.ajax({
            url: "/admin/ajax/ajax_login_engine_page_designer.php",
            method: "post",
            data: {
                page_id: page_id,
                page_name: $("input[name=page_name]").val(),
                action: "duplicate"
            },
            success: function (response) {

                if (response['status'] === "success"){


                    pull_data();

                    $("input[name=page_name]").val("");

                    $("#duplicate_modal").modal("hide");

                    swal("Success", response['message'], "success");


                } else {

                    swal("Error", response['message'], "error");

                }

            },
            error: function () {

                swal("Error", "There is unexpected error. Please try again.", "error");

            }
        });

    });


});

var page_id;

function pull_data() {

    $.ajax({
        url: "ajax/ajax_login_engine_page_designer.php",
        method: "GET",
        data: {
            "action": "get_all"
        },
        success: function (data) {

            if (data['status'] === "success") {


                if($.fn.dataTable.isDataTable('#itemlist')){

                    $("#itemlist").DataTable().destroy();

                }


                let table_str = "";

                for (let x = 0; x < data['data'].length; x++) {

                    table_str += "<tr>";

                    table_str += "<td>" + (x + 1) + "</td>";
                    table_str += "<td><span data-page-id='" + data['data'][x]['unique_id'] + "' class='btn-preview'>" + data['data'][x]['unique_id'] + "</span></td>";
                    table_str += "<td>" + data['data'][x]['page_name'] + "</td>";
                    table_str += "<td>" + data['data'][x]['updated_date'] + "</td>";
                    table_str += "<td>" + data['data'][x]['remark'] + "</td>";

                    if (data['data'][x]['default_page'] === "y") table_str += "<td>Yes</td>";
                    else table_str += "<td>No</td>";

                    if (data['data'][x]['purpose'] === "landing") table_str += "<td>Landing Page</td>";
                    else if (data['data'][x]['purpose'] === "landingwinfo") table_str += "<td>Landing Page + Last Account Info</td>";
                    else if (data['data'][x]['purpose'] === "campaign") table_str += "<td>Campaign Page</td>";
                    else if (data['data'][x]['purpose'] === "survey") table_str += "<td>Survey Page</td>";
                    else if (data['data'][x]['purpose'] === "nps") table_str += "<td>NPS Page</td>";
                    else if (data['data'][x]['purpose'] === "qr") table_str += "<td>QR Login Page</td>";
                    else if (data['data'][x]['purpose'] === "status") table_str += "<td>Status Page</td>";

                    table_str += "<td><a href='javascript:void(0)' data-page-id='" + data['data'][x]['unique_id'] + "' class='btn btn-icon btn-success btn-xs mr-1 fa fa-pencil btn-edit'></a><a href='javascript:void(0);' data-page-id='" + data['data'][x]['unique_id'] + "' class='btn btn-icon btn-danger btn-xs mr-1 fa fa-times btn-delete'></a><a href='javascript:void(0)' data-page-id='" + data['data'][x]['unique_id'] + "' class='btn btn-icon btn-warning btn-xs mr-1 fa fa-plus-circle btn-duplicate'></a></td>";

                    table_str += "</tr>";

                }

                $(".table-data>tbody").html(table_str);

                $(".table-data").DataTable({
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
                        
                        $(".btn-duplicate").off().on("click", function(){


                            page_id = $(this).data("page-id");


                            if (page_id.length > 0){


                                $(".duplicate_name").html(page_id);

                                $("#duplicate_modal").modal();


                            }


                        });


                        $(".btn-edit").off().on("click", function(){

                            window.location.href = "/admin/designer/?page=" + $(this).data("page-id");

                        });


                        $(".btn-delete").off().on("click", function(){

                            deleteItem($(this).data("page-id"));

                        });


                        $(".btn-preview").off().on("click", function(){


                            let page_id = $(this).data("page-id");


                            if (page_id.length > 0){


                                $.ajax({
                                    url: "/admin/ajax/ajax_login_engine_page_designer.php",
                                    method: "post",
                                    data: {
                                        action : "get_tenant"
                                    },
                                    success: function (response) {


                                        if (response['status'] === "success") {

                                            $("img.space-image").prop("src", "/custom/" + response['data'] + "/thumbnails/" + page_id + ".png");

                                            $("#preview-modal").modal();

                                        }


                                    }
                                });

                            }


                        });


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

    }).then(function(x){

        if (x['value'] === true) {
            
            $.ajax({
                url: "ajax/ajax_login_engine_page_designer.php",
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
