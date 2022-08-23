
$(document).ready(function () {


    summary_update();

    setInterval(summary_update, 60000);


});



function summary_update() {


    $(".last-update").html("Reloading..");

    $.ajax({

        url: "/admin/ajax/ajax_help_services_summary.php",
        method: "post",
        success: function (response) {

            if (response['status'] === "success"){


                $(".last-update").html(response['data']['last_update']);

                $("#current-cpu-usage").html(parseFloat(response['data']['cpu_used'][0]).toFixed(2) + " &nbsp; " + parseFloat(response['data']['cpu_used'][1]).toFixed(2) + " &nbsp; " + parseFloat(response['data']['cpu_used'][2]).toFixed(2));
                $("#current-memory-usage").html(response['data']['memory_percent']);
                $("#current-disk-usage").html(response['data']['disk_percent']);


                if (response['data']['service'].length > 0){

                    for (let i = 0; i < response['data']['service'].length; i++) {

                        let current_element = $("tr.service-" + response['data']['service'][i]['service']);

                        if (response['data']['service'][i]['status'] === "up") {


                            current_element.children("td").next().html("<label class='badge badge-success'>Running</label>");

                            current_element.children("td").next().next().html("<label class='badge badge-success'>" + response['data']['service'][i]['last_running'] + "</label>");


                        } else {


                            current_element.children("td").next().html("<label class='badge badge-danger'>Stop</label>");

                            current_element.children("td").next().next().html("<label class='badge badge-danger'>" + response['data']['service'][i]['last_running'] + "</label>");


                        }


                    }

                }


                $("td").each( function () {

                    let current_space = $(this);

                    if (current_space.html() === ""){

                        current_space.html("<label class='badge badge-warning'>Unknown</label>");

                    }

                });


            } else {

                console.log("ERROR: " + response['message']);

            }

        },
        error: function (response) {

            console.log("ERROR: Unknown");

        }

    });


}