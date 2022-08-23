<?php

$omy['module'] = "CPanel -> Verify Device";
$omy['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");

require_once '../includes/include_config.php';
require_once '../includes/include_session.php';
require_once '../includes/include_general.php';
require_once "../includes/include_connection.php";


if (!in_array($omy['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));
}


header("Content-Type: application/json");


$action = $_REQUEST['action'];


switch ($action) {

    case "get_all":
        get_all();
        break;
    case "get_detail":
        get_detail();
        break;
    case "verify_device":
        verify_device();
        break;
    case "delete":
        delete();
        break;

    default:
        echo json_encode(array("status" => "error", "message" => "ERROR: Wrong implementation", "data" => null));
}


function get_all()
{


    global $kiw_db;


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->fetch_array("SELECT * FROM kiwire_device_register WHERE tenant_id ='{$_SESSION["tenant_id"]}'");


        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));


    }

}


function get_detail()
{


    global $kiw_db;


    $id = $kiw_db->escape($_GET['id']);


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_device_register WHERE id = '{$id}' AND tenant_id ='{$_SESSION["tenant_id"]}'");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}



function verify_device()
{


    global $kiw_db;


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $id = $kiw_db->escape($_GET['id']);


        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_device_register WHERE id = '{$id}' AND tenant_id = '{$_SESSION["tenant_id"]}'");


        $mac_address = $kiw_temp['mac_address'];

        $verified  = $kiw_temp['verified'];



        if ($verified == "n") {


            $kiw_db->query("UPDATE kiwire_device_register SET updated_date = NOW(), verified = 'y' WHERE id = '{$id}' AND tenant_id = '{$_SESSION["tenant_id"]}' LIMIT 1");


            echo json_encode(array("status" => "success", "message" => "SUCCESS: User device has been approved", "data" => null));


        } else {


            $kiw_db->query("UPDATE kiwire_device_register SET updated_date = NOW(), verified = 'n' WHERE id = '{$id}' AND tenant_id = '{$_SESSION["tenant_id"]}' LIMIT 1");


            echo json_encode(array("status" => "success", "message" => "SUCCESS: User device has been unapproved", "data" => null));
        }


        sync_logger("{$_SESSION['user_name']} updated device {$mac_address}", $_SESSION["tenant_id"]);


    } else {


        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
    }

}


function delete()
{

    global $kiw_db;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $id = $kiw_db->escape($_GET['id']);

        $tenant_id =  $kiw_db->escape($_SESSION['tenant_id']);


        if (!empty($id)) {


            $kiw_device = $kiw_db->query_first("SELECT * FROM kiwire_device_register WHERE id = '{$id}' AND tenant_id = '{$tenant_id}'");

            $kiw_db->query("DELETE FROM kiwire_device_register WHERE id = '{$id}' AND tenant_id = '{$tenant_id}'");
        }


        sync_logger("{$_SESSION['user_name']} deleted device {$kiw_device['mac_address']}", "general");

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Device [ {$kiw_device['mac_address']} ] has been deleted", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));


    }
}
