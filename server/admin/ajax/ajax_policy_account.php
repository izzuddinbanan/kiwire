<?php

$kiw['module'] = "Policy -> Account Policy";
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
    default: echo "ERROR: Wrong implementation";
}


function create()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $data['name']                = $kiw_db->sanitize($_GET['name']);
        $data['username']            = $kiw_db->sanitize($_GET['username']);
        $data['frequency']           = $_GET['frequency'];
        $data['exec_action']         = $_GET['exec_action'];
        $data['action_value']        = $_GET['action_value'];
        $data['policy_status']       = $_GET['policy_status'];
        $data['policy_integration']  = $_GET['policy_integration'];

        $data['tenant_id']          = $tenant_id;

        if (isset($_GET['status'])) $data['status'] = "y";
        else $data['status'] = "n";


        if($kiw_db->insert("kiwire_account_policy", $data)){
            
            sync_logger("{$_SESSION['user_name']} create Policy {$_GET['name']}", $_SESSION['tenant_id']);

            echo json_encode(array("status" => "success", "message" => "SUCCESS: New Account Policy " . $_GET['name'] . "  added", "data" => null));

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

            $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_account_policy WHERE id = '{$id}' AND tenant_id = '{$tenant_id}'");

            $kiw_db->query("DELETE FROM kiwire_account_policy WHERE id = '{$id}' AND tenant_id = '{$tenant_id}'");

        }


        sync_logger("{$_SESSION['user_name']} deleted Policy {$kiw_temp['name']}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Account Policy has been deleted", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}


function get_data()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        $kiw_temp = $kiw_db->fetch_array("SELECT  * FROM kiwire_account_policy  WHERE tenant_id = '{$tenant_id}' LIMIT 1000");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));

    }

}


function get_single_data()
{

    global $kiw_db, $tenant_id;

    $id = $kiw_db->escape($_REQUEST['id']);

    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_account_policy WHERE tenant_id = '{$tenant_id}' AND id = '{$id}' LIMIT 1");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));

    }

}


function edit_single_data()
{


    global $kiw_db, $tenant_id;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $id = $kiw_db->escape($_REQUEST['reference']);

        $new_name                = $kiw_db->sanitize($_REQUEST['name']);
        $new_username            = $kiw_db->sanitize($_REQUEST['username']);
        $new_frequency           = $kiw_db->escape($_REQUEST['frequency']);
        $new_exec_action         = $kiw_db->escape($_REQUEST['exec_action']);
        $new_action_value        = $kiw_db->escape($_REQUEST['action_value']);
        $new_policy_status       = $kiw_db->escape($_REQUEST['policy_status']);
        $new_policy_integration  = $kiw_db->escape($_REQUEST['policy_integration']);

        if (isset($_REQUEST['status'])) $new_status = "y";
        else $new_status = "n";


        $kiw_db->query("UPDATE kiwire_account_policy SET updated_date = NOW(), name = '{$new_name}', frequency = '{$new_frequency}', username = '{$new_username}', exec_action = '{$new_exec_action}', action_value = '{$new_action_value}', status = '{$new_status}', policy_status = '{$new_policy_status}', policy_integration = '{$new_policy_integration}' WHERE id = '{$id}' AND tenant_id = '{$tenant_id}' LIMIT 1");


        sync_logger("{$_SESSION['user_name']} updated Policy {$new_name}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Account Policy has been updated", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

        
}
