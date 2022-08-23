$(document).ready(function () {

    pull_data();

    $('#search').on("click", pull_data);

    // $("#filter-btn").on("click", function () {

    //     $("#filter_modal").modal();

    // });


    // $("#filter-data").on("click", function () {


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
        url: "ajax/ajax_report_login_top_accounts.php?action=get_by_date",
        method: "POST",
        data: {
            "startdate": $('#startdate').val(),
            "enddate": $('#enddate').val(),
            "type": $('#type').val(),
            "zone": $("select[name=zone]").val(),
            "project": $("select[name=project]").val()
        },
        success: function (data) {

            if (data['status'] === "success") {

                if ($.fn.dataTable.isDataTable('.table-data')) {

                    $(".table-data").DataTable().destroy();

                }


                let table_str = "";

                let counter = 1;

                for (let kindex in data['data']) {


                    table_str += "<tr>";
                    table_str += "<td>" + counter + "</td>";
                    table_str += "<td>" + kindex + "</td>";
                    table_str += "<td>" + data['data'][kindex] + "</td>";
                    table_str += "</tr>";

                    counter++;

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