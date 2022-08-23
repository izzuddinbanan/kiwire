<?php

$kiw['module'] = "Account -> Auto Reset";
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

    case "get_all": get_all(); break;
    case "create": create(); break;
    case "delete": delete(); break;
    
    default: echo "ERROR: Wrong implementation";
}


function get_all(){

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        $kiw_temp = $kiw_db->fetch_array("SELECT * FROM kiwire_auto_reset WHERE tenant_id = '{$tenant_id}'");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));
    }
}

function create()
{
    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $data['exec_when']  = $_GET['exec_when'];
        $data['profile']    = $_GET['profile'];
        $data['grace']      = $_GET['grace'];
        $data['tenant_id']  = $tenant_id;

        $data['updated_date'] = date('Y-m-d H-i-s');

        if($kiw_db->insert("kiwire_auto_reset", $data)){

            sync_logger("{$_SESSION['user_name']} create schedule {$_GET['exec_when']} for {$_GET['profile']}", $_SESSION['tenant_id']);

            echo json_encode(array("status" => "success", "message" => "SUCCESS: New Schedule for ". $_GET['profile'] ." added", "data" => null));

        }
        else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }

        
    } else

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
}


function delete()
{
    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $id = $kiw_db->escape($_POST['id']);

        if (!empty($id)) {
            
            $kiw_temp = $kiw_db->query_first("SELECT exec_when, profile FROM kiwire_auto_reset WHERE id = '$id' AND tenant_id = '$tenant_id' LIMIT 1");

            $del_schedule = changeForm($kiw_temp['exec_when']);

            $del_profile = $kiw_temp['profile'];

           
            $sql = $kiw_db->query("DELETE FROM kiwire_auto_reset WHERE id = '$id' AND tenant_id = '$tenant_id'");

        }


        sync_logger("{$_SESSION['user_name']} deleted schedule {$del_schedule} for {$del_profile} ", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Schedule : $del_schedule for $del_profile has been deleted", "data" => null));
            
 
    } else

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
}


function changeForm($exec_when){

    switch ($exec_when){

        case "ot" : return "Reached Limit";
        case "t"  : return "30 Minutes";
        case "h"  : return "Hourly";
        case "d"  : return "Daily";
        case "w"  : return "Weekly";
        case "m"  : return "Monthly";
        case "y"  : return "Yearly";  
        case "cd" : return "Custom Daily";

    }
}