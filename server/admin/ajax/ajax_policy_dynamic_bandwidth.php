<?php

$kiw['module'] = "Policy -> Dynamic Bandwidth";
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

        $data['applied_to']     = $_GET['applied_to'];
        $data['at_user']        = !empty($_GET['at_user']) ? $kiw_db->sanitize($_GET['at_user'])  : NULL;
        $data['at_zone']        = !empty($_GET['at_zone']) ? $kiw_db->sanitize($_GET['at_zone']) : NULL;
        $data['priority']       = !empty($_GET['priority']) ? $_GET['priority'] : NULL;

        $data['k_trigger']      = !empty($_GET['k_trigger']) ? $_GET['k_trigger'] : NULL;
        $data['download_speed'] = !empty($_GET['download_speed']) ? $kiw_db->sanitize($_GET['download_speed']) : NULL;
        $data['upload_speed']   = !empty($_GET['upload_speed']) ? $kiw_db->sanitize($_GET['upload_speed']) : NULL;
        $data['updated_date']   = date('Y-m-d H-i-s');

        $data['created_by']     = $_SESSION['username'];
        $data['tenant_id']      = $tenant_id;

        if($kiw_db->insert("kiwire_bandwidth", $data)){

            sync_logger("{$_SESSION['user_name']} create Dynamic Bandwidth", $_SESSION['tenant_id']);
    
            echo json_encode(array("status" => "success", "message" => "SUCCESS: New Dynamic Bandwidth added", "data" => null));
        }
        else{
            echo json_encode(array("status" => "failed", "message" => "ERROR: Database error", "data" => null));
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

            $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_bandwidth WHERE id = '" . $id . "' AND tenant_id = '$tenant_id'");

            $sql = $kiw_db->query("DELETE FROM kiwire_bandwidth WHERE id = '" . $id . "' AND tenant_id = '$tenant_id'");
        }


        sync_logger("{$_SESSION['user_name']} deleted Dynamic Bandwidth", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Bandwidth has been deleted", "data" => null));

    } else

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

}


function get_data()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        $kiw_temp = $kiw_db->fetch_array("SELECT  * FROM kiwire_bandwidth WHERE tenant_id = '{$tenant_id}' LIMIT 1000");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));

    }

}


function get_single_data()
{

    global $kiw_db, $tenant_id;

    $id = $kiw_db->escape($_REQUEST['id']);

    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_bandwidth WHERE tenant_id = '$tenant_id' AND id = '{$id}' LIMIT 1");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));

    }

}


function edit_single_data()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));
        
        $id = $kiw_db->escape($_REQUEST['reference']);

        $new_applied_to      = $kiw_db->escape($_REQUEST['applied_to']);
        $new_at_user         = $kiw_db->sanitize($_REQUEST['at_user']);
        $new_at_zone         = $kiw_db->sanitize($_REQUEST['at_zone']);
        $new_priority        = $kiw_db->escape($_REQUEST['priority']);

        $new_download_speed  = $kiw_db->sanitize($_REQUEST['download_speed']);
        $new_upload_speed    = $kiw_db->sanitize($_REQUEST['upload_speed']);
        $new_k_trigger       = $kiw_db->escape($_REQUEST['k_trigger']);

        $kiw_db->query("UPDATE kiwire_bandwidth SET updated_date = NOW(), k_trigger = '{$new_k_trigger}', applied_to = '{$new_applied_to}', at_user = '{$new_at_user}', at_zone = '{$new_at_zone}' , priority = '{$new_priority}' ,download_speed = '{$new_download_speed}', upload_speed = '{$new_upload_speed}' 
        
        WHERE  id = '{$id}' AND tenant_id = '{$tenant_id}' LIMIT 1");


        sync_logger("{$_SESSION['user_name']} updated Dynamic Bandwidth", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Bandwidth has been updated", "data" => null));

    } else

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
}
