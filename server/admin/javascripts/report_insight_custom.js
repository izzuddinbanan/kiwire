
$(document).ready(function () {


    pull_data();


    $module = $("select[name='main_module']");
    $sub_account = $("select[name='sub_account']");
    $sub_login = $("select[name='sub_login']");


    $('#filter-data').on("click", function () {

        pull_data();

        $('#showresult').css("display", "block");

    });




    $module.change(function () {


        if ($(this).val() == "Account") {

            $("select[name='sub_account']").show();
            $(".submodule").not("select[name='sub_account']").hide();

        } else if ($(this).val() == "Login") {

            $("select[name='sub_login']").show();
            $(".submodule").not("select[name='sub_login']").hide();


        } else if ($(this).val() == "Bandwidth") {

            $("select[name='sub_bandwidth']").show();
            $(".submodule").not("select[name='sub_bandwidth']").hide();


        } else if ($(this).val() == "Controller") {


            $("select[name='sub_controller']").show();
            $(".submodule").not("select[name='sub_controller']").hide();


        } else if ($(this).val() == "Impression") {

            $("select[name='sub_impression']").show();
            $(".submodule").not("select[name='sub_impression']").hide();


        } else if ($(this).val() == "Campaign") {

            $("select[name='sub_campaign']").show();
            $(".submodule").not("select[name='sub_campaign']").hide();


        } else if ($(this).val() == "Delivery Log") {

            $("select[name='sub_delivery_log']").show();
            $(".submodule").not("select[name='sub_delivery_log']").hide();


        } else if ($(this).val() == "Insight") {

            $("select[name='sub_insight']").show();
            $(".submodule").not("select[name='sub_insight']").hide();


        }


    });


    $sub_account.change(function () {


        if (($(this).val() == "2") || ($(this).val() == "3") || ($(this).val() == "4") || ($(this).val() == "5") || ($(this).val() == "6")) {

            $(".date_from").css("display", "block");
            $(".date_until").css("display", "block");

        } else {

            $(".date_from").css("display", "none");
            $(".date_until").css("display", "none");

        }


    });


    $sub_login.change(function () {


        if (($(this).val() == "9") || ($(this).val() == "10") || ($(this).val() == "13") || ($(this).val() == "14")) {

            $(".date_from").css("display", "block");
            $(".date_until").css("display", "block");

            $("#username").hide();
            $("#mac_address").hide();
            $("#ip_address").hide();
            $("#nas_id").hide();
            $("#tenant").hide();
            $("#data_type").hide();
            $("#profile_name").hide();
            $("#zone").hide();
            $("#type").hide();

        } else if (($(this).val() == "11") || ($(this).val() == "16")) {

            $(".date_from").css("display", "block");
            $(".date_until").css("display", "block");
            $("#profile_name").show();

            $("#username").hide();
            $("#mac_address").hide();
            $("#ip_address").hide();
            $("#nas_id").hide();
            $("#tenant").hide();
            $("#data_type").hide();
            $("#zone").hide();
            $("#type").hide();


        } else if (($(this).val() == "12") || ($(this).val() == "15") || ($(this).val() == "18")) {

            $(".date_from").css("display", "block");
            $(".date_until").css("display", "block");
            $("#zone").show();

            $("#username").hide();
            $("#mac_address").hide();
            $("#ip_address").hide();
            $("#nas_id").hide();
            $("#tenant").hide();
            $("#data_type").hide();
            $("#profile_name").hide();
            $("#type").hide();

        } else if ($(this).val() == "8") {

            $(".date_from").css("display", "block");
            $(".date_until").css("display", "block");
            $("#username").show();
            $("#mac_address").show();
            $("#ip_address").show();
            $("#nas_id").show();
            $("#tenant").show();
            $("#data_type").show();

            $("#zone").hide();
            $("#profile_name").hide();
            $("#type").hide();

        } else if ($(this).val() == "17") {

            $(".date_from").css("display", "block");
            $(".date_until").css("display", "block");
            $("#type").show();
            $("#zone").show();

            $("#username").hide();
            $("#mac_address").hide();
            $("#ip_address").hide();
            $("#nas_id").hide();
            $("#tenant").hide();
            $("#data_type").hide();
            $("#profile_name").hide();


        } else if ($(this).val() == "7") {

            $(".date_from").css("display", "none");
            $(".date_until").css("display", "none");
            $("#zone").hide();
            $("#profile_name").hide();

            $("#username").hide();
            $("#mac_address").hide();
            $("#ip_address").hide();
            $("#nas_id").hide();
            $("#tenant").hide();
            $("#data_type").hide();
            $("#type").hide();


        }


    });


});


// function pull_data() {


//     if ($.fn.dataTable.isDataTable('.table-data')) {

//         $(".table-data").DataTable().destroy();

//     }


//     if ($('#main_module').val() == "Account" && $('#sub_account').val() == "2") {


//         table_data = $('.table-data').DataTable({
//             "responsive": true,
//             "processing": true,
//             "serverSide": true,
//             "ajax": {
//                 url: "ajax/ajax_report_account_expiry.php?action=get_by_date",
//                 method: "GET",
//                 data: {
//                     "startdate": $('#startdate').val(),
//                     "enddate": $('#enddate').val()
//                 },
//             },
//             "dom": "<'row'<'col-sm-6 col-md-4'l><'col-sm-6 col-md-4 pull-right'f><'col-sm-12 col-md-4'<'pull-right'B>>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
//             "buttons": [
//                 {
//                     extend: 'copyHtml5',
//                     exportOptions: {
//                         columns: [0, ':visible']
//                     }
//                 },
//                 {
//                     extend: 'csvHtml5'
//                 },
//                 {
//                     extend: 'pdfHtml5',
//                     exportOptions: {
//                         columns: ':visible'
//                     }
//                 },
//                 {
//                     extend: 'print',
//                     exportOptions: {
//                         columns: ':visible'
//                     }
//                 }
//             ],
//             language: {
//                 searchPlaceholder: "Search Records",
//                 search: "",
//             },



//         });


//     } else {





//     }


// }


function pull_data() {


    let start_date = $("input[name=startdate]").val();
    let end_date = $("input[name=enddate]").val();


    if ($.fn.dataTable.isDataTable('.table-data')) {

        $(".table-data").DataTable().destroy();

    }


    if ($('#main_module').val() == "Account" && $('#sub_account').val() == "2") {


        $.ajax({
            url: "ajax/ajax_report_account_expiry.php?action=get_by_date",
            method: "GET",
            data: {
                "startdate": start_date,
                "enddate": end_date
            },
            success: function (response) {


                if (response['data'].length > 0) {


                    let username = [];
                    let profile = [];
                    let price = [];
                    let created_date = [];
                    let expiration_date = [];


                    for (let oindex in response['data']) {


                        username.push(response['data'][oindex]['username']);
                        profile.push(response['data'][oindex]['profile_subs']);
                        price.push(response['data'][oindex]['price']);
                        created_date.push(response['data'][oindex]['date_create']);
                        expiration_date.push(response['data'][oindex]['date_expiry']);

                    }


                    var dataObject = {

                        columns: [{
                            title: "USERNAME"
                        }, {
                            title: "PROFILE"
                        }, {
                            title: "PRICE (MYR)"
                        }, {
                            title: "CREATED DATE"
                        }, {
                            title: "EXPIRATION DATE"
                        }],
                        data: [
                            [username, profile, price, created_date, expiration_date]
                        ]
                    };

                    var columns = [];

                    $('#example').dataTable({
                        "data": dataObject.data,
                        "columns": dataObject.columns
                    });



                } else {


                    var dataObject = {

                        columns: [{
                            title: "USERNAME"
                        }, {
                            title: "PROFILE"
                        }, {
                            title: "PRICE (MYR)"
                        }, {
                            title: "CREATED DATE"
                        }, {
                            title: "EXPIRATION DATE"
                        }],
                        data: [
                            [" ", " ", "No data available in table", " ", " "]
                        ]

                    };


                    var columns = [];

                    $('#example').dataTable({
                        "data": dataObject.data,
                        "columns": dataObject.columns
                    });


                }


            },
            error: function () {

                swal("Error", "There is unexpected error. Please try again.", "error");

            }

        });


    } else {



    }



    if ($('#main_module').val() == "Account" && $('#sub_account').val() == "6") {


        $.ajax({
            url: "ajax/ajax_report_account_creation_analytics.php",
            method: "POST",
            data: {
                "startdate": start_date,
                "enddate": end_date
            },
            success: function (response) {


                if (response['data'].length >= 0) {

                    //console.log(response['data'].length);


                    let date = [];
                    let account = [];

                    let data_table = [];

                    for (let oindex in response['data']) {

                        data_table[oindex] = [response['data'][oindex]['xreport_date'], response['data'][oindex]['account']];
                        // date.push(response['data'][oindex]['xreport_date']);
                        // account.push(response['data'][oindex]['account']);


                        
                    }

                    // console.log(data_table);

           
                    var dataObject = {

                        columns: [{
                            title: "DATE"
                        }, {
                            title: "NUMBER OF ACCOUNT CREATED"
                        }],
                        data: data_table,
                    };

                    var columns = [];

                    $('#example').dataTable({
                        "data": dataObject.data,
                        "columns": dataObject.columns
                    });



                } else {


                    var dataObject = {

                        columns: [{
                            title: "DATE"
                        }, {
                            title: "NUMBER OF ACCOUNT CREATED"
                        }],
                        data: [
                            ["No data available in table", " "]
                        ]
                    };


                    var columns = [];

                    $('#example').dataTable({
                        "data": dataObject.data,
                        "columns": dataObject.columns
                    });


                }


            },
            error: function () {

                swal("Error", "There is unexpected error. Please try again.", "error");

            }

        });



    } else {

        //kiv


    }



}