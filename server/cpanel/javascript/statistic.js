$(document).ready(function () {


    $(".fa-filter").on("click", function () {

        $("#filter-modal").modal();

    });

    
    $("#interval").change(function() {


        let option = $(this).val();


        if(option === 'daily') {


            $("#date-start").css("display", "block");

            $("#date-end").css("display", "block");


        }

        else if (option === 'hourly') {
            

            $("#date-start").css("display", "block");

            $("#date-label").text("Date");

            $("#date-end").css("display", "none");

        

        } else {


            $("#date-start").css("display", "none");

            $("#date-end").css("display", "none");


        }


    });



    $("input[name=start_date]").val(moment().subtract(1, 'weeks').format("YYYY-MM-DD")).pickadate({
        format: 'yyyy-mm-dd',
        formatSubmit: 'yyyy-mm-dd',
        max: moment().subtract(1, 'weeks').format("YYYY-MM-DD")
    });


    $("input[name=end_date]").val(moment().subtract(1, 'days').format("YYYY-MM-DD")).pickadate({
        format: 'yyyy-mm-dd',
        formatSubmit: 'yyyy-mm-dd',
        max: moment().subtract(1, 'days').format("YYYY-MM-DD")
    });


    $(".btn-filter").on("click", function () {

        pull_data();

        $("#filter-modal").modal("hide");


    });

    
    pull_data();


});


function pull_data() {

      
    let interval = $("select[name=interval]").val();
    let start_date = $("input[name=start_date]").val();
    let end_date = $("input[name=end_date]").val();

    console.log(interval + "::" + start_date + "::" + end_date)
    
    $.ajax({
        url: "ajax/statistic.php",
        method: "GET",
        data: {
            "action"    : "get_data",
            "interval"  : interval,
            "start_date": start_date,
            "end_date"  : end_date
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
                    table_str += "<td>" + data['data'][x]['monthly'] + "</td>";
                    table_str += "<td>" + data['data'][x]['kcount'] + "</td>";
                    table_str += "<td>" + new Date(data['data'][x]['session_time'] * 1000).toISOString().substr(11, 8) + "</td>";
                    table_str += "<td>" + ((parseInt(data['data'][x]['quota_in']) + parseInt(data['data'][x]['quota_out'])) / (1024 * 1024 * 1024)).toFixed(3) + "</td>";

                    table_str += "</tr>";

                }

                $(".table-data > tbody").html(table_str);


                $(".table-data").dataTable();



            } else {

                swal("Error", data['message'], "error");

            }


        },
        error: function () {

            swal("Error", "There is unexpected error. Please try again.", "error");

        }

    });



}


// function pull_data() {


//     $.ajax({
//         url: "ajax/statistic.php",
//         method: "GET",
//         data: {
//             "action": "get_data"
//         },
//         success: function (data) {

//             if (data['status'] === "success") {


//                 if ($.fn.dataTable.isDataTable('.table-data')) {

//                     $(".table-data").DataTable().destroy();

//                 }

//                 let table_str = "";


//                 for (let x = 0; x < data['data'].length; x++) {


//                     table_str += "<tr>";

//                     table_str += "<td>" + (x + 1) + "</td>";
//                     table_str += "<td>" + data['data'][x]['monthly'] + "</td>";
//                     table_str += "<td>" + data['data'][x]['kcount'] + "</td>";
//                     table_str += "<td>" + new Date(data['data'][x]['session_time'] * 1000).toISOString().substr(11, 8) + "</td>";
//                     table_str += "<td>" + ((parseInt(data['data'][x]['quota_in']) + parseInt(data['data'][x]['quota_out'])) / (1024 * 1024 * 1024)).toFixed(3) + "</td>";

//                     table_str += "</tr>";

//                 }

//                 $(".table-data > tbody").html(table_str);


//                 $(".table-data").dataTable();



//             } else {

//                 swal("Error", data['message'], "error");

//             }


//         },
//         error: function () {

//             swal("Error", "There is unexpected error. Please try again.", "error");

//         }

//     });


// }


