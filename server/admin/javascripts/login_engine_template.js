
String.prototype.capitalize = function () {

    return this.charAt(0).toUpperCase() + this.slice(1);

};

$(document).ready(function () {

    pull_data();

    $(".btn-create, .btn-update").css("display", "none");


    $(".create-btn-template").on("click", function () {


        $(".btn-create, .btn-update").css("display", "none");

        $(".btn-create").css("display", "block");

        $("#inlineForm").modal();


    });


    //reset form after cancel

    $(".cancel-button").on("click", function (e) {


        $(".create-form").trigger("reset");

        $("#inlineForm").modal("hide");


    });

    $(".change-provider").on("change", function () {


        let type_selected = $(this).val();

        $(".type-select").css("display", "none");


        if (type_selected === "email"){


            $(".var-for-email").css("display", "block");


        } else if (type_selected === "sms"){


            $(".var-for-sms").css("display", "block");


        } else if (type_selected === "voucher"){


            $(".var-for-voucher").css("display", "block");


        } else if (type_selected === "status"){


            $(".var-for-status").css("display", "block");


        }


    });


    $(".btn-create").on("click", function (e) {

        let create_form = $(".create-form");

        if (create_form.parsley().validate()) {

            let data = "";

            data += "name=" + $("input[name=name]").val();
            data += "&subject=" + $("input[name=subject]").val();
            data += "&type=" + $("select[name=type]").val();
            data += "&content=" + tinyMCE.get(0).getContent();
            data += "&reference=" + $("input[name=reference]").val();
            data += "&token=" + $("input[name=token]").val();

            $.ajax({
                url: "ajax/ajax_login_engine_template.php?action=create",
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
            })
        }
    });


    $(".btn-update").on("click", function (e) {


        let create_form = $(".create-form");

        if (create_form.parsley().validate()) {

            let data = "";

            data += "name=" + $("input[name=name]").val();
            data += "&subject=" + $("input[name=subject]").val();
            data += "&type=" + $("select[name=type]").val();
            data += "&content=" + tinyMCE.get(0).getContent();
            data += "&reference=" + $("input[name=reference]").val();
            data += "&token=" + $("input[name=token]").val();


            $.ajax({
                url: "ajax/ajax_login_engine_template.php?action=edit_single_data",
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
            })
            
        }
        
    });


    tinymce.init({
        selector: "#editor",
        theme: "silver",
        relative_urls : false,
        remove_script_host : false,
        convert_urls: false,
        document_base_url: "",
        height: 400,
        forced_root_block : "",
        force_br_newlines : true,
        force_p_newlines : false,
        plugins: [
            "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
            "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
            "save table directionality emoticons template paste"
        ],
        toolbar: "insertfile undo redo | example fontselect fontsizeselect formatselect | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media fullpage | forecolor backcolor emoticons | bgimgbtn",
        font_formats: "AkrutiKndPadmini=Akpdmi-n;"+
            "Andale Mono=andale mono,times;"+
            "Arial=arial,helvetica,sans-serif;"+
            "Arial Black=arial black,avant garde;"+
            "Book Antiqua=book antiqua,palatino;"+
            "Comic Sans MS=comic sans ms,sans-serif;"+
            "Courier New=courier new,courier,monospace;"+
            "Georgia=georgia,palatino;"+
            "Helvetica=helvetica;"+
            "Impact=impact,chicago;"+
            "Symbol=symbol;"+
            "Tahoma=tahoma,arial,helvetica,sans-serif;"+
            "Terminal=terminal,monaco;"+
            "Times New Roman=times new roman,times;"+
            "Trebuchet MS=trebuchet ms,geneva;"+
            "Verdana=verdana,geneva;"+
            "Webdings=webdings;"+
            "Wingdings=wingdings,zapf dingbats",
        fontsize_formats: '8pt 10pt 12pt 14pt 18pt 24pt 36pt',
    });


});


function pull_data() {

    $.ajax({
        url: "ajax/ajax_login_engine_template.php",
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
                    table_str += "<td><span class=\"badge badge-success\">" + data['data'][x]['type'].toUpperCase() + "</td>";
                    table_str += "<td>" + data['data'][x]['subject'] + "</td>";
                    table_str += "<td>" + data['data'][x]['updated_date'] + "</td>";

                    table_str += "<td><a href=\"javascript:void(0);\" onclick=\"getItemForForm('" + data['data'][x]['id'] + "')\" class='btn btn-icon btn-success btn-xs mr-1 fa fa-pencil'></a><a href=\"javascript:void(0);\" onclick=\"deleteItem('" + data['data'][x]['id'] + "')\" class='btn btn-icon btn-danger btn-xs mr-1 fa fa-times'></a></td>";

                    table_str += "</tr>";
                }

                $("table.table-data > tbody").html(table_str);

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
                url: "ajax/ajax_login_engine_template.php",
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
            url: "ajax/ajax_login_engine_template.php",
            method: "GET",
            data: {
                "action": "get_update",
                "id": id
            },
            success: function (data) {

                if (data) {

                    $("form.create-form").trigger("reset");

                    tinyMCE.get(0).setContent(data['data']['content']);

                    $("#name").val(data['data']['name']);
                    $("#subject").val(data['data']['subject']);

                    $("#type").val(data['data']['type']);
                    $("#updated_date").val(data['data']['updated_date']);

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
