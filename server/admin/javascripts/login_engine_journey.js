var current_state = "create";

$(document).ready(function () {


    pull_data();


    $('#journey-modal').on('hidden.bs.modal', function (e) {


        $("input[name=name]").val("").attr("disabled", false);
        $("input[name=lang]").val("");

        $("select[name=pre_login]").val("default").trigger("change");
        $("select[name=post_login]").val("default").trigger("change");

        $("input[name=pre_login_url]").val("");
        $("input[name=post_login_url]").val("");

        $("input[name=status]").prop("checked", false);

        $("#multiple-list-group-a li").each(function () {

            $("#multiple-list-group-b").prepend($(this));

        });


        $(".btn-save-journey").html("Save");

        current_state = "create";


    });


    $("#pre_login, #post_login").on("change", function () {

        let current_item = $(this);
        let current_journey = current_item.data("journey");


        if (current_item.val() === "custom") {

            $("." + current_journey).css("display", "block");

        } else {

            $("." + current_journey).css("display", "none");

        }

    });


    $(".btn-save-journey").off().on("click", function () {


        let zone_pages = [];

        $("ul#multiple-list-group-a").find("li").each(function () {

            zone_pages.push($(this).data("page-id"));

        });


        if (zone_pages.length > 0) {

            let journey_name = $("input[name=name]").val();

            let journey_pre = $("select[name=pre_login]").val();
            let journey_pre_url = $("input[name=pre_login_url]").val();
            let journey_post = $("select[name=post_login]").val();
            let journey_post_url = $("input[name=post_login_url]").val();

            let journey_lang = $("input[name=lang]").val();
            let journey_status = $("input[name=status]").prop("checked");

            if (journey_name.trim().length === 0) {

                swal("Error", "Please provide a name or select a zone for this journey", "error");

                return;

            }

            $.ajax({
                url: "/admin/ajax/ajax_login_engine_journey.php",
                method: "post",
                data: {
                    "zone_pages": zone_pages.join(","),
                    "journey_name": journey_name,
                    "journey_lang": journey_lang,
                    "journey_status": journey_status,
                    "journey_pre": journey_pre,
                    "journey_pre_url": journey_pre_url,
                    "journey_post": journey_post,
                    "journey_post_url": journey_post_url,
                    "journey_action": current_state
                },
                success: function (response) {

                    current_state = "create";

                    toastr.info(response['message']);

                    $("#journey-modal").modal("toggle");


                    pull_data();


                },
                error: function () {

                    swal("error", "There is an error. Please retry.", "error");

                }
            });

        }

    });


});


//table journey
function pull_data() {

    $.ajax({
        url: "ajax/ajax_login_engine_journey.php",
        method: "GET",
        data: {
            "journey_action": "get_all"
        },
        success: function (data) {

            if (data['status'] === "success") {

                if ($.fn.dataTable.isDataTable('.table-data')) {

                    $(".table-data").DataTable().destroy();

                }

                let table_str = "";

                for (let x = 0; x < data['data'].length; x++) {

                    let page_list = data['data'][x]['page_list'].split(",");
                    let page_list_string = "";

                    for (let x = 0; x < page_list.length; x++) {

                        if (page_list[x] !== "") {

                            page_list_string += "<div class='chip chip-primary mr-1 chip-select-page' data-page-id='" + page_list[x] + "'><div class='chip-body'><div class='chip-text'>" + page_list[x] + "</div></div></div>";

                        }

                    }


                    table_str += "<tr>";

                    table_str += "<td>" + (x + 1) + "</td>";
                    table_str += "<td>" + data['data'][x]['journey_name'] + "</td>";
                    table_str += "<td>" + data['data'][x]['pre_login'] + "</td>";
                    table_str += "<td>" + data['data'][x]['post_login'] + "</td>";
                    table_str += "<td>" + page_list_string + "</td>";
                    table_str += "<td>" + data['data'][x]['lang'] + "</td>";

                    if (data['data'][x]['status'] === "y") {
                        table_str += "<td><span class=\"badge badge-success\">Active</span></td>";
                    } else {
                        table_str += "<td><span class=\"badge badge-danger\">Disabled</span></td>";
                    }

                    table_str += "<td><a href=\"javascript:void(0);\" onclick=\"getItemForForm('" + data['data'][x]['journey_name'] + "')\" class='btn btn-icon btn-success btn-xs mr-1 fa fa-pencil'></a><a href=\"javascript:void(0);\" onclick=\"deleteItem('" + data['data'][x]['journey_name'] + "')\" class='btn btn-icon btn-danger btn-xs mr-1 fa fa-times'></a></td>";

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


                $(".chip-select-page").on("click", function () {

                    $.ajax({
                        url: "/admin/ajax/ajax_login_engine_journey.php",
                        method: "post",
                        data: {
                            "journey_action": "get_path",
                            "page_id": $(this).data("page-id")
                        },
                        success: function (response) {

                            if (response['status'] === "success") {


                                $("img.space-image").prop("src", response['data']['url']);

                                $("#preview-modal").modal();


                            }

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

    })

}


function deleteItem(journey_name) {

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
                url: "ajax/ajax_login_engine_journey.php",
                method: "POST",
                data: {
                    "journey_action": "delete",
                    "journey_name": journey_name,
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


function getItemForForm(zone_name) {

    if (zone_name.length > 0) {

        $.ajax({
            url: "/admin/ajax/ajax_login_engine_journey.php",
            method: "post",
            data: {
                "journey_name": zone_name,
                "journey_action": "get_update"
            },
            success: function (response) {

                if (response['status'] === "success") {

                    current_state = "update";

                    $("input[name=name]").val(response['data']['journey_name']).attr("disabled", true);
                    $("input[name=lang]").val(response['data']['lang']);

                    $("select[name=pre_login]").val(response['data']['pre_login']).trigger("change");
                    $("input[name=pre_login_url]").val(decodeURIComponent(response['data']['pre_login_url']));

                    $("select[name=post_login]").val(response['data']['post_login']).trigger("change");
                    $("input[name=post_login_url]").val(decodeURIComponent(response['data']['post_login_url']));


                    if (response['data']['status'] === "y") $("input[name=status]").prop("checked", true);
                    else $("input[name=status]").prop("checked", false);


                    let page_selected = response['data']['page_list'].split(",");


                    for (let x = 0; x < page_selected.length; x++) {

                        $("#multiple-list-group-b li").each(function () {

                            let current_item = $(this);

                            if (current_item.data("page-id") === page_selected[x]) {

                                $("#multiple-list-group-a").append(current_item);

                            }

                        });

                    }


                    $(".btn-save-journey").html("Update").off().on("click", function () {

                        let zone_pages = [];

                        $("ul#multiple-list-group-a").find("li").each(function () {

                            zone_pages.push($(this).data("page-id"));

                        });


                        let journey_name = $("input[name=name]").val();

                        let journey_pre = $("select[name=pre_login]").val();
                        let journey_pre_url = $("input[name=pre_login_url]").val();
                        let journey_post = $("select[name=post_login]").val();
                        let journey_post_url = $("input[name=post_login_url]").val();

                        let journey_lang = $("input[name=lang]").val();
                        let journey_status = $("input[name=status]").prop("checked");

                        if (journey_name.trim().length === 0) {

                            swal("Error", "Please provide a name or select a zone for this journey", "error");

                            return;

                        }

                        $.ajax({
                            url: "/admin/ajax/ajax_login_engine_journey.php",
                            method: "post",
                            data: {
                                "zone_pages": zone_pages.join(","),
                                "journey_name": journey_name,
                                "journey_lang": journey_lang,
                                "journey_status": journey_status,
                                "journey_pre": journey_pre,
                                "journey_pre_url": journey_pre_url,
                                "journey_post": journey_post,
                                "journey_post_url": journey_post_url,
                                "journey_action": current_state
                            },
                            success: function (response) {

                                current_state = "create";

                                toastr.info(response['message']);

                                $("#journey-modal").modal("toggle");


                                pull_data();


                            },
                            error: function () {

                                swal("error", "There is an error. Please retry.", "error");

                            }
                        });


                    });


                    $("#journey-modal").modal();


                } else {

                    swal("error", response['message'], "error");

                }


            },
            error: function () {

                swal("error", "There is an error pccured. Please try again.", "error");

            }
        });


    }

}