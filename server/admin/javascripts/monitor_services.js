$(document).ready(function () {

    $.ajax({
        url: "ajax/ajax_monitor_services.php",
        method: "get",
        data: {
            'action': 'get_service'
        },
        success: function (data) {

            if (data['status'] === "success") {

                
                $.each(data['data'], function(key, val){

                    var status, button

                    if(val['status'] === 'Active'){

                        status = 'success'

                    }
                    else{
                        status = 'danger'

                    }


                    var detail =    ' <div class="col-xl-3 col-md-4 col-sm-6">' +
                                        '<div class="card text-center border-'+status+'">' +
                                            '<div class="card-body">' +
                                                '<h4 class="card-title">'+val['name']+'</h4>' +
                                                '<p class="card-text">' +
                                                    '<span class="badge badge-glow bg-'+status+'">'+val['status']+'</span>' +
                                                '</p>' +
                                                '<p class="card-text"><b>Last Active: '+val['since']+' </b><br> '+val['days']+' </p>' +
                                                '<div class="text-center">'+ 
                                                    '<button type="button" class="btn btn-sm btn-warning btn-restart" data-service="'+ val['service_name'] +'">Restart</button>'+
                                                '</div>'+
                                            '</div>' +
                                        '</div>' +
                                    '</div>';

                    $('.append').append(detail)
                })
                


            } else {

                swal("Error", data['message'], "error");

            }

        },
        error: function () {

            swal("Error", "There is unexpected error. Please try again.", "error");

        }

    });

    $('body').on('click', '.btn-restart',  function(){
        
        var service = $(this).data('service');

        $.ajax({
            url: "ajax/ajax_monitor_services.php",
            method: "get",
            data: {
                'action': 'restart_service',
                'service': service,
            },
            success: function (data) {

                if(data['status'] === 'success')  location.reload();

            },
            error: function () {

                swal("Error", "There is unexpected error. Please try again.", "error");

            }

        });

    })



});