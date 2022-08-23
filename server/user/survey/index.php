<?php
 
require_once dirname(__FILE__, 2) . "/includes/include_general.php";
require_once dirname(__FILE__, 3) . "/admin/includes/include_connection.php";


$kiw_survey = $_REQUEST['survey_id'];


if (!empty($kiw_survey) && strlen($kiw_survey) > 0) {


    // check if survey id is valid

    $kiw_temp = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_survey_list WHERE unique_id = '{$kiw_survey}' LIMIT 1");


    if (!empty($kiw_temp) && $kiw_temp['status'] == "y") {


        // check if survey for public, if yes, the proceed

        if ($kiw_temp['public'] == "y"){

            $kiw_valid = true;

        } else {


            require_once "../includes/include_session.php";


            // if survey not for public, then check if authorize

            if ($_SESSION['system']['checked'] == true) $kiw_valid = true;
            else $kiw_valid = false;


        }


         if ($kiw_valid == true) {


            $kiw_response['tenant_id']     = $_SESSION['controller']['tenant_id'];
            $kiw_response['mac_address']   = $_SESSION['user']['mac'];
            $kiw_response['username']      = $_SESSION['user']['login']['username'];
            $kiw_response['unique_id']     = $_REQUEST['survey_id'];
            


            $kiw_question_list = $kiw_db->query_first("SELECT SQL_CACHE questions FROM kiwire_survey_list WHERE unique_id = '{$kiw_response['unique_id']}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

            $kiw_question_list = base64_decode($kiw_question_list['questions']);
            $kiw_question_list = json_decode($kiw_question_list, true);


            $kiw_question_list = count($kiw_question_list);

            
            $kiw_answer = [];


            for($kiw_x = 1; $kiw_x <= $kiw_question_list; $kiw_x++){

                if(!isset($_REQUEST['answer'][$kiw_x]) || $_REQUEST['answer'][$kiw_x] == null){

                    $kiw_answer[$kiw_x] = "No answer";

                } else {

                    $kiw_answer[$kiw_x] = $_REQUEST['answer'][$kiw_x];

                }

            }


            $kiw_response['answer'] = json_encode($kiw_answer);
             
             setcookie("smart-wifi-survey-{$kiw_response['unique_id']}", "true", strtotime("+1 Year"), "/");

            //  $kiw_db->insert("kiwire_survey_respond", $kiw_response);

             $kiw_db->query("INSERT INTO kiwire_survey_respond(tenant_id, updated_date, unique_id, username, mac_address, answer) VALUE ('{$kiw_response['tenant_id']}', NOW(), '{$kiw_response['unique_id']}', '{$kiw_response['username']}', '{$kiw_response['mac_address']}', '{$kiw_response['answer']}')");


             if (isset($_SESSION['user'])) {


                 $_SESSION['user']['current'] = next_page($_SESSION['user']['journey'], $_SESSION['user']['current'], $_SESSION['user']['default']);

                 header("Location: /user/pages/?session=" . $_REQUEST['session']);


             } else {


                 header("Location: {$_SERVER['HTTP_REFERER']}");


             }


         } else {

             header("Location: {$_SERVER['HTTP_REFERER']}");

         }


    }

}



