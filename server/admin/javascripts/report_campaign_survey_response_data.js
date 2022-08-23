var table_data = null;

$(document).ready(function () {

    // $("button#search").on("click", function () {
    $("#search").on("click", function () {

        

        let report_form = $("<form method='post' action='/admin/report_campaign_survey_response_data.php'></form>");

        let survey_id = $("select[name=survey_id]").val();

        if (survey_id !== undefined && survey_id.length > 0) {

            report_form.append($("<input type='hidden' name='startdate' value='" + $("input[name=startdate]").val() + "'>"));
            report_form.append($("<input type='hidden' name='enddate' value='" + $("input[name=enddate]").val() + "'>"));
            report_form.append($("<input type='hidden' name='survey_id' value='" + survey_id + "'>"));

            $("body").append(report_form);

            report_form.submit();

        }


    });


    if (survey_id !== "") {

        table_data = $('.table-data').DataTable({
            "responsive": true,
            "processing": true,
            "serverSide": true,
            "ajax": {
                url: "ajax/ajax_report_campaign_survey_response_data.php",
                method: "get",
                data: {
                    action: "datatable",
                    start_date: $("#startdate").val(),
                    end_date: $("#enddate").val(),
                    survey_id: survey_id
                }
            },
            "dom": "<'row'<'col-sm-6 col-md-4'l><'col-sm-12 col-md-8'<'pull-right'B>>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            "buttons": [
                {
                    // Add Download All button
                    text: "Download All",
                    className: "btn-all-data",
                    action: function () {
                        // Ajax script
                        $.ajax({
                            url: "/admin/ajax/ajax_report_campaign_survey_response_data.php",
                            method: "post",
                            data:{
                                // the data to send to the server when performing this Ajax request
                                action: "download_all",             // Assign value to the action
                                start_date: $("#startdate").val(),  // Assign value to start_date variable with the value in the "startdate" ID
                                end_date: $("#enddate").val(),      // Assign value to end_date variable with the value in the "enddate" ID
                                survey_id: survey_id                // Assign value to survey_id with survey_id value

                            },
                            // if successful, run this function
                            success: function (response) {
                                // check if the status value has same value and type as the string "success"
                                if (response['status'] === "success"){

                                    // Updates the file path
                                    window.location.href = "/temp/" + response['data'];

                                } else {
                                    // show the error message
                                    swal("Error", response['message'], "error");

                                }

                            },
                            // if the request fails, run this function
                            error: function (response) {
                                // show the error message
                                swal("Error", "There is an error. Please try again.", "error");

                            }
                        });

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

            },
            "columnDefs": []
        });

    }


});
