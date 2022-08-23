<?php

$kiw['module'] = "Device -> Monitoring -> Maps";
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

        $data['tenant_id']          = $tenant_id;
        $data['name']               = $_GET['name'];
        $data['description']        = $_GET['description'];
        $data['updated_date']       = date('Y-m-d H-i-s');

        if($kiw_db->insert("kiwire_controller_map", $data)){

            sync_logger("{$_SESSION['user_name']} create map {$_GET['name']}", $_SESSION['tenant_id']);

            echo json_encode(array("status" => "success", "message" => "SUCCESS: New Map added", "data" => null));

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

            $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_controller_map WHERE id = '" . $id . "' AND tenant_id = '$tenant_id'");

            $sql = $kiw_db->query("DELETE FROM kiwire_controller_map WHERE id = '" . $id . "' AND tenant_id = '$tenant_id'");
        }


        sync_logger("{$_SESSION['user_name']} deleted map {$id}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Map has been deleted", "data" => null));
    
    } else

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
}


function get_data()
{
    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        $kiw_temp = $kiw_db->fetch_array("SELECT  * FROM kiwire_controller_map  WHERE tenant_id = '{$tenant_id}' LIMIT 1000");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));
    }
}


function get_single_data()
{

    global $kiw_db, $tenant_id;

    $id = $kiw_db->escape($_REQUEST['id']);

    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_controller_map WHERE tenant_id = '$tenant_id' AND id = '{$id}' LIMIT 1");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));
    }
}


function edit_single_data()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {
        
        csrf($kiw_db->escape($_REQUEST['token']));

        $id = $kiw_db->escape($_REQUEST['reference']);

        $new_name            = $kiw_db->escape($_REQUEST['name']);
        $new_description     = $kiw_db->escape($_REQUEST['description']);
        $new_update          = date('Y-m-d H-i-s');

        $kiw_db->query("UPDATE kiwire_controller_map
                        SET name = '{$new_name}', description = '{$new_description}', updated_date = '{$new_update}'
                        WHERE id = '{$id}' AND tenant_id = '{$tenant_id}' LIMIT 1");


        sync_logger("{$_SESSION['user_name']} updated map {$new_name}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Map {$new_name} has been updated", "data" => null));
    
    } else

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
}
