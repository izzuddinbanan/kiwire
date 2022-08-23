
$(document).ready(function (){


    setTimeout(view_latest_system, (Math.random() * 10));
    setTimeout(view_latest_integration, (Math.random() * 10));
    setTimeout(view_latest_user, (Math.random() * 10));
    setTimeout(view_latest_service, (Math.random() * 10));
    setTimeout(view_latest_pms, (Math.random() * 10));


    $("a.card-link").on("click", function (){

        let current_click = $(this);


        if (current_click.data("action") === "refresh"){

            switch (current_click.data("type")){
                case "system" : view_latest_system(); break;
                case "user" : view_latest_user(); break;
                case "integration" : view_latest_integration(); break;
                case "service" : view_latest_service(); break;
                case "pms" : view_latest_pms(); break;
            }


        } else if (current_click.data("action") === "download"){


            switch (current_click.data("type")){
                case "system" : download_system(); break;
                case "user" : download_user(); break;
                case "integration" : download_integration(); break;
                case "service" : download_service(); break;
                case "pms" : download_pms(); break;
            }


        }


    });


});


function view_latest_user(){


    $(".space-for-user").html('<p class="card-text">Loading log file..</p>');

    $.ajax({
        url: "/admin/ajax/ajax_help_log_maintenance.php",
        method: "get",
        data: {
            action: "view-latest-user"
        },
        success: function (response){

            $(".space-for-user").html(response).scrollTop($('.space-for-user')[0].scrollHeight);

        }
    });

}


function view_latest_system(){


    $(".space-for-system").html('<p class="card-text">Loading log file..</p>');

    $.ajax({
        url: "/admin/ajax/ajax_help_log_maintenance.php",
        method: "get",
        data: {
            action: "view-latest-system"
        },
        success: function (response){

            $(".space-for-system").html(response).scrollTop($('.space-for-system')[0].scrollHeight);

        }
    });

}


function view_latest_integration(){


    $(".space-for-integration").html('<p class="card-text">Loading log file..</p>');

    $.ajax({
        url: "/admin/ajax/ajax_help_log_maintenance.php",
        method: "get",
        data: {
            action: "view-latest-integration"
        },
        success: function (response){

            $(".space-for-integration").html(response).scrollTop($('.space-for-integration')[0].scrollHeight);

        }
    });

}


function view_latest_service(){


    $(".space-for-service").html('<p class="card-text">Loading log file..</p>');

    $.ajax({
        url: "/admin/ajax/ajax_help_log_maintenance.php",
        method: "get",
        data: {
            action: "view-latest-service"
        },
        success: function (response){

            $(".space-for-service").html(response).scrollTop($('.space-for-service')[0].scrollHeight);

        }
    });

}

function view_latest_pms(){


    $(".space-for-pms").html('<p class="card-text">Loading log file..</p>');

    $.ajax({
        url: "/admin/ajax/ajax_help_log_maintenance.php",
        method: "get",
        data: {
            action: "view-latest-pms"
        },
        success: function (response){

            $(".space-for-pms").html(response).scrollTop($('.space-for-pms')[0].scrollHeight);

        }
    });

}

function download_system(){


    $.ajax({
        url: "/admin/ajax/ajax_help_log_maintenance.php",
        method: "get",
        data: {
            action: "download-system"
        },
        success: function (response){

            if (response['status'] === "success"){

                window.location.href = "/temp/" + response['data'];

            }

        }
    });


}


function download_integration(){


    $.ajax({
        url: "/admin/ajax/ajax_help_log_maintenance.php",
        method: "get",
        data: {
            action: "download-integration"
        },
        success: function (response){

            if (response['status'] === "success"){

                window.location.href = "/temp/" + response['data'];

            }

        }
    });


}


function download_user(){


    $.ajax({
        url: "/admin/ajax/ajax_help_log_maintenance.php",
        method: "get",
        data: {
            action: "download-user"
        },
        success: function (response){

            if (response['status'] === "success"){

                window.location.href = "/temp/" + response['data'];

            }

        }
    });


}


function download_service(){


    $.ajax({
        url: "/admin/ajax/ajax_help_log_maintenance.php",
        method: "get",
        data: {
            action: "download-service"
        },
        success: function (response){

            if (response['status'] === "success"){

                window.location.href = "/temp/" + response['data'];

            }

        }
    });


}


function download_pms(){


    $.ajax({
        url: "/admin/ajax/ajax_help_log_maintenance.php",
        method: "get",
        data: {
            action: "download-pms"
        },
        success: function (response){

            if (response['status'] === "success"){

                window.location.href = "/temp/" + response['data'];

            }

        }
    });


}