$(document).ready(function(){

    $.ajax({
        url: "/admin/ajax/ajax_help_database_performance.php",
        method: "get",
        data: {},
        success: function (response) {

            if (response['status'] === "success") {

                $(".database-result").html("<pre class='p-5'>" + atob(response['data']) + "</pre>");

            }

        },
        error: function (response) {

            swal("error", "There is unexpected error occurred. Please try again.", "error");

        }
    });


});