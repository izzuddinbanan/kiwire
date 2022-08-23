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
        url: "ajax/ajax_report_bandwidth_top_usage_summary.php",
        method: "POST",
        data: {
            "startdate": $('#startdate').val(),
            "enddate": $('#enddate').val(),
            "zone": $("select[name=zone]").val(),
            "project": $("select[name=project]").val(),
            "type": $("select[name=type]").val()
        },
        success: function (data) {

            if (data['status'] === "success") {

                if ($.fn.dataTable.isDataTable('.table-data')) {

                    $(".table-data").DataTable().destroy();

                }

                let table_str = "";

                for (let kindex in data['data']) {

                    table_str += "<tr>";
                    table_str += "<td>" + (parseInt(kindex) + 1) + "</td>";
                    table_str += "<td>" + data['data'][kindex]['start_time'] + "</td>";
                    table_str += "<td>" + data['data'][kindex]['username'] + "</td>";
                    table_str += "<td>" + (new Date).clearTime().addSeconds(data['data'][kindex]['session_time']).toString('H:mm:ss') + "</td>";
                    table_str += "<td>" + data['data'][kindex]['ip_address'] + "</td>";
                    table_str += "<td>" + data['data'][kindex]['mac_address'] + "</td>";
                    table_str += "<td>" + parseFloat(data['data'][kindex]['quota_out']).toFixed(3) + "</td>";
                    table_str += "<td>" + parseFloat(data['data'][kindex]['quota_in']).toFixed(3) + "</td>";
                    table_str += "<td>" + ((parseFloat(data['data'][kindex]['quota_out']) * 8) / parseFloat(data['data'][kindex]['session_time'])).toFixed(3) + "</td>";
                    table_str += "<td>" + ((parseFloat(data['data'][kindex]['quota_in']) * 8) / parseFloat(data['data'][kindex]['session_time'])).toFixed(3) + "</td>";
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
