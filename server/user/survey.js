$(document).ready(function () {


    var survey_id = $("input[name=survey_id]").val();

    if (survey_id !== null && survey_id !== undefined){


        // check cookies

        let survey_answered = Cookies.get('smart-wifi-survey-' + survey_id);

        // if (survey_answered === "true"){

        //     ajaxNextPage(session_id)
            

        // }else {

        //     $.ajax({
                
        //         url: "/user/survey/checking.php?session=" + session_id,
        //         method: "post",
        //         data: {"mac_address" : user_mac_address, "survey_id" : survey_id},
        //         success: function (response) {

        //             if(response["answered"] === "true") ajaxNextPage(session_id)

        //             else return;
        //         }
        //     });


        // }


    }


    function ajaxNextPage(session_id) {

        $.ajax({
            url: "/user/next/?session=" + session_id,
            method: "post",
            data: {},
            success: function (response) {

                window.location.href = "/user/pages/?session=" + session_id;

            },
            error: function (response) {

                swal("Error", "Something went wrong. Please try again.", "error");

            }
        });


    }

});