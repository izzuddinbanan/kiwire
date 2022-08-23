<?php

$kiw['module'] = "Policy -> Device Policy";
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

        $data['zone']       = $_GET['zone'];
        $data['name']       = $kiw_db->sanitize($_GET['name']);
        $data['type']       = $_GET['type'];
        $data['profile']    = $_GET['profile'];

        $data['value']      = $kiw_db->sanitize($_GET['value']);
        $data['priority']   = $kiw_db->sanitize($_GET['priority']);
        $data['tenant_id']  = $tenant_id;

        if (!empty($_GET['status'])) {
            $data['status'] = $_GET['status'];
        }

        if($kiw_db->insert("kiwire_device_policy", $data)){
            
            sync_logger("{$_SESSION['user_name']} create Policy {$_GET['name']}", $_SESSION['tenant_id']);
    
            echo json_encode(array("status" => "success", "message" => "SUCCESS: New Device Policy " . $_GET['name'] . "  added", "data" => null));
   
        }else {

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

            $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_device_policy WHERE id = '" . $id . "' AND tenant_id = '$tenant_id'");

            $kiw_db->query("DELETE FROM kiwire_device_policy WHERE id = '" . $id . "' AND tenant_id = '$tenant_id'");
        }


        sync_logger("{$_SESSION['user_name']} deleted Policy {$kiw_temp['name']}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Device Policy has been deleted", "data" => null));
    
    } else

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
}


function get_data()
{
    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        $kiw_temp = $kiw_db->fetch_array("SELECT  * FROM kiwire_device_policy  WHERE tenant_id = '{$tenant_id}' LIMIT 1000");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));
    }
}


function get_single_data()
{

    global $kiw_db, $tenant_id;

    $id = $kiw_db->escape($_REQUEST['id']);

    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_device_policy WHERE tenant_id = '$tenant_id' AND id = '{$id}' LIMIT 1");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));
    }
}


function edit_single_data()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));
        
        $id = $kiw_db->escape($_REQUEST['reference']);

        $new_name      = $kiw_db->sanitize($_REQUEST['name']);
        $new_zone      = $kiw_db->escape($_REQUEST['zone']);
        $new_type      = $kiw_db->escape($_REQUEST['type']);
        $new_value     = $kiw_db->sanitize($_REQUEST['value']);

        $new_profile   = $kiw_db->escape($_REQUEST['profile']);
        $new_priority  = $kiw_db->sanitize($_REQUEST['priority']);
        $new_status    = $kiw_db->escape($_REQUEST['status']);

        $kiw_db->query("UPDATE kiwire_device_policy SET updated_date = NOW(), name = '{$new_name}', zone = '{$new_zone}', type = '{$new_type}', value = '{$new_value}', type = '{$new_type}', profile = '{$new_profile}', priority = '{$new_priority}' , status = '{$new_status}' WHERE id = '{$id}' AND tenant_id = '{$tenant_id}' LIMIT 1");


        sync_logger("{$_SESSION['user_name']} updated Policy {$new_name}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Device Policy has been updated", "data" => null));
    
    } else

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
}
