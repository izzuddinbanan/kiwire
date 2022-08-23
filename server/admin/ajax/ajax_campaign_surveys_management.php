<?php

$kiw['module'] = "Campaign -> Survey Management";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_general.php";
require_once "../includes/include_connection.php";

if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}


$action = $_REQUEST['action'];

switch ($action) {

    case "create": create(); break;
    case "delete": delete(); break;
    case "get_all": get_data(); break;
    case "get_update": get_single_data(); break;
    case "edit_single_data": edit_single_data(); break;
    case "get_questions": get_questions($kiw_db); break;
    case "question_save": question_save($kiw_db); break;
    default: echo "ERROR: Wrong implementation";

}


function create()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));


        // get a unique id

        $kiw_unique = "";

        while ($kiw_unique == ""){


            $kiw_unique = substr(md5(time()), rand(0, 4), 8);

            $kiw_check = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_survey_list WHERE unique_id = '{$kiw_unique}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");

            if ($kiw_check['kcount'] > 0) $kiw_unique = "";


        }


        $data['unique_id']      = $kiw_unique;
        $data['name']           = $kiw_db->sanitize($_GET['name']);
        $data['description']    = $kiw_db->sanitize($_GET['description']);
        $data['status']         = ($_GET['status'] == "" ? "n" : "y");

        $data['tenant_id'] = $tenant_id;
        $data['updated_date'] = "NOW()";

        if($kiw_db->insert("kiwire_survey_list", $data)){

            sync_logger("{$_SESSION['user_name']} create surveys {$_GET['name']}", $_SESSION['tenant_id']);

            echo json_encode(array("status" => "success", "message" => "SUCCESS: New Survey " . $_GET['name'] . "  added", "data" => null));

        }
        else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }  


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}


function delete()
{
    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));


        $id = $kiw_db->escape($_POST['id']);

        if (!empty($id)) {

            $kiw_temp = $kiw_db->query_first("SELECT name FROM kiwire_survey_list WHERE id = '" . $id . "' AND tenant_id = '$tenant_id'");
            $survey_name = $kiw_temp['name'];

            $kiw_db->query("DELETE FROM kiwire_survey_list WHERE id = '" . $id . "' AND tenant_id = '$tenant_id'");

        }


        sync_logger("{$_SESSION['user_name']} deleted surveys {$survey_name}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Survey {$survey_name} has been deleted", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}


function get_data()
{

    global $kiw_db, $tenant_id;


    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        $kiw_temp = $kiw_db->fetch_array("SELECT  * FROM kiwire_survey_list  WHERE tenant_id = '{$tenant_id}' LIMIT 1000");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }


}


function get_single_data()
{

    global $kiw_db, $tenant_id;


    $id = $kiw_db->escape($_REQUEST['id']);


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_survey_list WHERE tenant_id = '{$tenant_id}' AND id = '{$id}' LIMIT 1");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }


}


function edit_single_data()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $id = $kiw_db->escape($_REQUEST['reference']);

        $new_survey_name    = $kiw_db->sanitize($_REQUEST['name']);
        $new_description    = $kiw_db->sanitize($_REQUEST['description']);
        $new_status         = $kiw_db->escape($_REQUEST['status']);

        $new_updated_date   = date('Y-m-d H:i:s');

        $kiw_db->query("UPDATE kiwire_survey_list SET name = '{$new_survey_name}', description = '{$new_description}', status = '{$new_status}', updated_date = '{$new_updated_date}' WHERE id = '{$id}' AND tenant_id = '{$tenant_id}' LIMIT 1");


        sync_logger("{$_SESSION['user_name']} updated surveys {$new_survey_name}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Survey {$new_survey_name} has been updated", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}



function get_questions($kiw_db){


    $kiw_id = (int)$_REQUEST['id'];

    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->query_first("SELECT `questions` FROM kiwire_survey_list WHERE id = '{$kiw_id}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp['questions']));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}


function question_save($kiw_db){


    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        $kiw_question   = $_REQUEST['question'];
        $kiw_type       = $_REQUEST['type'];
        $kiw_required   = $_REQUEST['required'];
        $kiw_choice     = $_REQUEST['choice'];

        $kiw_total_question = count($kiw_question);
        $kiw_id = (int)$_REQUEST['id'];


        if ($kiw_total_question > 0) {


            for ($i = 0; $i < $kiw_total_question; $i++) {

                if (!empty($kiw_question[$i])){

                    if (count($kiw_choice[$i]) > 1) $kiw_choice[$i] = array_filter($kiw_choice[$i]);

                    $kiw_question_data[] = array(
                        "question" => $kiw_question[$i],
                        "type" => $kiw_type[$i],
                        "required" => $kiw_required[$i],
                        "choice" => json_encode($kiw_choice[$i])
                    );

                }


            }


            $kiw_question_data = base64_encode(json_encode($kiw_question_data));

            $kiw_db->query("UPDATE kiwire_survey_list SET updated_date = NOW(), questions = '{$kiw_question_data}' WHERE tenant_id = '{$_SESSION['tenant_id']}' AND id = '{$kiw_id}' LIMIT 1");


            sync_logger("{$_SESSION['user_name']} updated surveys {$kiw_question}", $_SESSION['tenant_id']);
 
            echo json_encode(array("status" => "success", "message" => "SUCCESS: Questions has been saved.", "data" => null));



        } else {

            echo json_encode(array("status" => "error", "message" => "Missing data from the request", "data" => null));

        }

    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}


