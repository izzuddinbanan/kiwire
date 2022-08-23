$(document).ready(function () {

    $.ajax({
        url: "ajax/ajax_monitor_scheduler.php",
        method: "get",
        success: function (data) {

            if (data['status'] === "success") {

                
                $.each(data['data'], function(key, val){

                    var status = val['status'] === 'Active' ? 'success' : 'danger';

                    var detail =    ' <div class="col-xl-3 col-md-4 col-sm-6">' +
                                        '<div class="card text-center border-'+status+'"> '+ 
                                            '<div class="card-body">'+
                                                '<h4 class="card-title"><b>'+val['name']+'</b></h4>'+
                                                '<p class="card-text">'+
                                                    '<span class="badge badge-glow bg-'+status+'">'+ val['status'] +'</span>' +
                                                '</p>' +
                                                '<p class="card-text"><b>Run :</b><br>'+val['run']+'</p> ' +
                                                '<p class="card-text"><b>Last Run :</b><br> '+val['last_run_start']+'</p>' +
                                                '<p class="card-text"><b>Scheduler Completed : </b><br>'+val['last_run_end']+'</p>' +
                                                '<p class="card-text"><b>Time Taken :</b> '+val['time_taken']+' </p>' +
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

});