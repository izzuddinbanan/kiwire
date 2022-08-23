$(document).ready(function () {

    pull_data();

    $('#search').on("click", pull_data);

    // $("#filter-btn").on("click", function (){

    //     $("#filter_modal").modal();

    // });


    // $("#filter-data").on("click", function (){


    //     pull_data();

    //     $("#filter_modal").modal("hide");

    // });


    // filter by zone/project

    $('input:radio').change(function () {

        var val = $('input:radio:checked').val();

        if (val == 'Zone') {

            $('.zone').css('display', 'block')
            $('.project').css('display', 'none')


        } else {

            $('.project').css('display', 'block')
            $('.zone').css('display', 'none')

        }

    });


    // reset previous dropdown value before choose another

    $("#search").click(function (e) {
        $("select").val("");
    });

    //end

});


function pull_data() {

    $.ajax({
        url: "ajax/ajax_report_bandwidth_current_top_account.php",
        method: "GET",
        data: {
            "action": "get_all",
            "zone": $("select[name=zone]").val(),
            "project": $("select[name=project]").val()
        },
        success: function (data) {

            if (data['status'] === "success") {

                if ($.fn.dataTable.isDataTable('.table-data')) {

                    $(".table-data").DataTable().destroy();

                }

                let table_str = "";

                if (data['data'].length > 0) {

                    for (let x = 0; x < data['data'].length; x++) {


                        table_str += "<tr>";

                        table_str += "<td>" + (x + 1) + "</td>";
                        table_str += "<td>" + data['data'][x]['start_time'] + "</td>";
                        table_str += "<td>" + data['data'][x]['username'] + "</td>";
                        table_str += "<td>" + (new Date).clearTime().addSeconds(data['data'][x]['session_time']).toString('H:mm:ss') + "</td>";

                        table_str += "<td>" + data['data'][x]['mac_address'] + "</td>";
                        table_str += "<td>" + data['data'][x]['ip_address'] + "</td>";
                        table_str += "<td>" + parseFloat(data['data'][x]['quota_out']).toFixed(3) + "</td>";
                        table_str += "<td>" + parseFloat(data['data'][x]['quota_in']).toFixed(3) + "</td>";

                        if (data['data'][x]['session_time'] === '0') {
                            data['data'][x]['session_time'] = 1;

                        }

                        table_str += "<td>" + (((parseFloat(data['data'][x]['quota_out']) + parseFloat(data['data'][x]['quota_in'])) * 8) / data['data'][x]['session_time']).toFixed(3) + "</td>";

                        table_str += "</tr>";


                    }

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
                        if ($('.dataTables_filter').find('input').hasClass('form-control-sm')) {
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