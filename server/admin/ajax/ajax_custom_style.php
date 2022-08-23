<?php

$kiw['module'] = "Cloud -> Custom Style";
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

        $tenant         = $kiw_db->escape($_GET['tenant']);

        if($tenant == 'all'){

            $kiw_clouds = $kiw_db->fetch_array("SELECT  * FROM kiwire_clouds WHERE custom_style = 'n'");

            foreach($kiw_clouds as $kiw_cloud){

                $kiw_db->query("UPDATE kiwire_clouds SET updated_date = NOW(), custom_style = 'y' WHERE tenant_id = '{$kiw_cloud["tenant_id"]}'");
            
            }
        }
        else{

            $kiw_db->query("UPDATE kiwire_clouds SET updated_date = NOW(), custom_style = 'y' WHERE tenant_id = '{$tenant}'");

        }

        sync_logger("{$_SESSION['user_name']} create Authentication Key {$_GET['api_key']}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: New Authentication Key added", "data" => null));

    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}


function delete()
{
    global $kiw_db;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $id = $kiw_db->escape($_POST['id']);

        $kiw_db->query("UPDATE kiwire_clouds SET updated_date = NOW(),  custom_style = 'n' WHERE id = '{$id}'  LIMIT 1");


        sync_logger("{$_SESSION['user_name']} updated tenant info for custom style", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Custom Style for tenant has been deleted", "data" => null));

    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}


function get_data()
{
    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->fetch_array("SELECT  * FROM kiwire_clouds WHERE custom_style = 'y' LIMIT 1000");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }

}


function get_single_data()
{

    global $kiw_db;

    $id = $kiw_db->escape($_REQUEST['id']);

    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_clouds  WHERE id = '{$id}' LIMIT 1");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));

    }

}


function edit_single_data()
{

    global $kiw_db, $tenant_id;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        $id = $kiw_db->escape($_REQUEST['reference']);

        $new_enabled    = $kiw_db->escape($_REQUEST['enabled']);


        $kiw_db->query("UPDATE kiwire_cloud SET updated_date = NOW(), custom_style = '{$new_enabled}' WHERE id = '{$id}'  LIMIT 1");


        sync_logger("{$_SESSION['user_name']} updated tenant info for custom style", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Custom Style for tenant has been updated", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}