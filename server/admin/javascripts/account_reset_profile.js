$(document).ready(function () {

    pull_data();

    $(".cancel-button").click(function (e) {

        $(".create-form").trigger("reset");
        
    });

    $(".btn-create").on("click", function(e) {

        let create_form = $(".create-form");

        if (create_form.parsley().validate()) {

            let data = create_form.serialize();

            $.ajax({
                url: "ajax/ajax_account_reset_profile.php?action=create",
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
           })
        }
    });


    $("#exec_when").on('change', function (x) {
        if($(this).val() == "ot"){
            $("#grace_space").css("display", "block");
        } else {
            $("#grace_space").css("display", "none");
        }
    });


});


function pull_data() {

    $.ajax({
        url: "ajax/ajax_account_reset_profile.php",
        method: "GET",
        data: {
            "action" : "get_all"
        },
        success: function (data) {

            if (data['status'] === "success") {

                if($.fn.dataTable.isDataTable('.table-data')){

                    $(".table-data").DataTable().destroy();

                }

                let table_str = "";

                for (let x = 0; x < data['data'].length; x++){

                    table_str += "<tr>";

                    table_str += '<td>' + (x + 1) + "</td>";

                    table_str += '<td>' + changeForm(data['data'][x]['exec_when']) + "</td>";

                    table_str += '<td>' + data['data'][x]['profile'] + "</td>";
                    //table_str += '<td>' + (data['data'][x]['exec_when'] != "ot" ? "Not Applicable" : data['data'][x]['grace']) + "</td>";

                    table_str += '<td>';

                    table_str += "<a href=\"javascript:void(0);\" onclick=\"deleteItem('" + data['data'][x]['id'] + "')\" class='btn btn-icon btn-danger btn-xs mr-1 fa fa-times'</a>";
                    
                    table_str += "</td> </tr>";  
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
                url: "ajax/ajax_account_reset_profile.php",
                method: "POST",
                data: {
                    "action": "delete",
                    id: id,
                    "token": $("input[name=token]").val()
                },

                success: function (data) {

                    if (data['status'] === "success") {

                        pull_data();

                        toastr.info(data['message']);

                    } else {

                        toastr.info(data['message']);

                    }
                },
            });
        }
    });
}


function changeForm($exec_when){

    switch ($exec_when){

        case "ot" : return "Reached Limit";
        case "t" : return "30 Minutes";
        case "h" : return "Hourly";
        case "d" : return "Daily";
        case "w" : return "Weekly";
        case "m" : return "Monthly";
        case "y" : return "Yearly";
        case "cd": return "Custom Daily";
        case "cw": return "Custom Weekly";

    }
}