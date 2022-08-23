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

     $('input:radio').change(function(){

        var val = $('input:radio:checked').val();

        if(val == 'Zone'){

            $('.zone').css('display','block')
            $('.project').css('display','none')

       
        } else {

            $('.project').css('display','block')
            $('.zone').css('display','none')

        }      
    
    });

    
    // reset previous dropdown value before choose another

    $("#search").click(function (e) { 
        $("select").val(""); 
    }); 

    //end


});


function pull_data() {

    if ($.fn.dataTable.isDataTable('.table-data')) {

        $(".table-data").DataTable().destroy();

    }


    table_data = $('.table-data').DataTable({
        "responsive": true,
        "processing": true,
        "serverSide": true,
        "deferRender": true,
        "ajax": {
            url: "ajax/ajax_report_login_logins_record.php?action=get_by_date",
            method: "get",
            data: {
                "startdate": $('#startdate').val(),
                "enddate": $('#enddate').val(),
                "type": $('#type').val(),
                "username": $('#username').val(),
                "mac_address": $('#mac_address').val(),
                "ip_address": $('#ip_address').val(),
                "tenant_id": $('#tenant_id').val(),
                "controller": $('#controller').val(),
                "profile": $('#profile').val(),
                "zone": $("select[name=zone]").val(),
                "project": $("select[name=project]").val()
            },
            error: function(){
                swal("Warning!", "Record is to heavy! <br> Please click 'Download All' to view the data", "warning");
            }
            
        },
        "dom": dt_position,
        "buttons": [

            {
                text: "Download All",
                className: "btn-all-data",
                action: function () {

                    $.ajax({
                        url: "ajax/ajax_report_login_logins_record.php",
                        method: "GET",
                        data: {
                            "action": "get_csv",
                            "startdate": $('#startdate').val(),
                            "enddate": $('#enddate').val(),
                            "type": $('#type').val(),
                            "username": $('#username').val(),
                            "mac_address": $('#mac_address').val(),
                            "ip_address": $('#ip_address').val(),
                            "tenant_id": $('#tenant_id').val(),
                            "controller": $('#controller').val(),
                            "zone": $("select[name=zone]").val(),
                            "project": $("select[name=project]").val()
                        },
                        success: function (response) {


                            if (response['status'] === "completed") {

                                swal("Success", "Go to  'Reports > Generated Report' to get the report", "success");

                            } else {

                                swal("Error!", "We are facing difficulties to generate the report.\nPlease re-try after couple of minutes.", "error");

                            }


                        }, error: function () {

                            swal("Error!", "There is an error occured. Please let us know about this.", "error");

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
                orientation: 'landscape',
                pageSize: 'A3',
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
        "language": {
            searchPlaceholder: "Search Records",
            search: "",
            infoFiltered: ""
        },
        "fnDrawCallback": function () {
            if($('.dataTables_filter').find('input').hasClass('form-control-sm')){
                $('.dataTables_filter').find('input').removeClass('form-control-sm')
            }

        },
        "columnDefs": [
            {
                "targets": [14],
                "render": function (data, type, row, meta) {


                    class_icon = row[14];

                    if (class_icon === "Smartphone") class_icon = '<td><span class="fa fa-mobile fa-3x"></span></td>';
                    else if (class_icon === "Tablet") class_icon = '<td><span class="fa fa-tablet fa-3x"></span></td>';
                    else class_icon = '<td><span class="fa fa-desktop fa-2x"></span></td>';

                    return class_icon;


                }
            }
        ]
    });




}