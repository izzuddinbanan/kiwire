$(document).ready(function () {


    $("#btn-get-data").on("click", function () {

        $("#get-bypass").modal();

    });


    $("#btn-add-data").on("click", function () {

        $("#create-bypass").modal();

    });


    $("#btn-get-list").on("click", function () {

        pull_data();

    });


    $("select[name=filter_nas]").on("change", function (){

        $("table.table-data > tbody").html("<tr><td colspan='6' class='text-center'>[ Please select a controller ]</td></tr>");

    });


    $(".btn-create").on("click", function (){

        $.ajax({
            url: "/admin/ajax/ajax_tool_bypass_device.php",
            method: "post",
            data: {
                action: "create_data",
                mac: $("input[name=mac_address]").val(),
                ip: $("input[name=ip_address]").val(),
                speed: $("input[name=speed]").val(),
                controller: ($("#nas").val()).toString(),
                remark: $("input[name=remark]").val(),
                "token": $("input[name=token]").val(),

            },
            success: function (response) {
                
                if (response['status'] === "success"){


                    $("#create-bypass").modal("hide");
                    $("#nas").val("");
                    $("#nas").trigger("change");
                    pull_data();

                    swal("SUCCESS", response['message'], "success");


                }else {

                    swal("ERROR", response['message'], "error");
                }

            },
            error: function (response) {

                swal("ERROR", "There is an error. Please try again.", "error");

            }
        });


    });


    $("#create-bypass").on("hidden.bs.modal", function () {


        $("form.create-form").trigger("reset");

        $("select[name=nas]").val("").trigger("change");


    });

});

function pull_data(){


    $.ajax({
        url: "/admin/ajax/ajax_tool_bypass_device.php",
        method: "post",
        data: {
            action: "get_data",
            controller: $("select[name=filter_nas]").val(),
            type: $("select[name=filter_status]").val()
        },
        success: function (response) {
            if (response['status'] === "success") {


                let table_str = "";

                let counter = 1;


                for (let kindex in response['data']) {


                    table_str += "<tr>";
                    table_str += "<td>" + counter + "</td>";
                    table_str += "<td>" + response['data'][kindex]['mac-address'] + "</td>";


                    if (response['type'] === "bound") {

                        table_str += "<td>" + response['data'][kindex]['address'] + "</td>";
                        table_str += "<td>" + response['data'][kindex]['speed'] + "</td>";
                        table_str += "<td>" + response['data'][kindex]['comment'] + "</td>";
                        table_str += "<td>";

                        table_str += "<button class='btn btn-danger btn-icon btn-bypass-remove fa fa-times mr-1' data-id='" + response['data'][kindex]['.id'] + "' data-mac='" + response['data'][kindex]['mac-address'] + "' data-ip='" + response['data'][kindex]['address'] + "'></button>";

                        table_str += "</td>";

                    } else if (response['type'] === "host") {

                        table_str += "<td>" + response['data'][kindex]['address'] + "</td>";
                        table_str += "<td>" + response['data'][kindex]['speed'] + "</td>";
                        table_str += "<td>" + response['data'][kindex]['comment'] + "</td>";
                        table_str += "<td>";

                        table_str += "<button class='btn btn-primary btn-icon btn-bypass-add fa fa-plus' data-mac='" + response['data'][kindex]['mac-address'] + "' data-ip='" + response['data'][kindex]['address'] + "'></button>";

                        table_str += "</td>";

                    }

                    table_str += "</tr>";

                    counter++;


                }


                if (table_str.length === 0) {

                    table_str = "<tr><td colspan='6' class='text-center'>No data for this controller</td></tr>";

                }


                $("table.table-data > tbody").html(table_str);


                $(".btn-bypass-add").off().on("click", function () {


                    $("input[name=mac_address]").val($(this).data("mac"));
                    $("input[name=ip_address]").val($(this).data("ip"));

                    $("select[name=nas]").val($("select[name=filter_nas]").val()).trigger("change");

                    $("#create-bypass").modal();


                });


                $(".btn-bypass-remove").off().on("click", function () {


                    $.ajax({
                        url: "/admin/ajax/ajax_tool_bypass_device.php",
                        method: "post",
                        data: {
                            action: "delete_data",
                            id: $(this).data("id"),
                            mac: $(this).data("mac"),
                            ip: $(this).data("ip"),
                            controller: $("select[name=filter_nas]").val(),
                            "token": $("input[name=token]").val(),

                        },
                        success: function (response) {

                            if (response['status'] === "success"){


                                pull_data();

                                swal("SUCCESS", response['message'], "success");


                            }

                        },
                        error: function (response) {

                            swal("ERROR", "There is an error. Please try again.", "error");

                        }
                    });


                });


                $("#get-bypass").modal("hide");


            }

        },
        error: function (resposne) {

            swal("ERROR", "There is an error. Please try again.", "error");

        }
    });


}