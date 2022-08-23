<?php

$kiw['module'] = "Policy -> Zone Restriction";
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

        $data['name']           = $kiw_db->sanitize($_REQUEST['name']);
        $data['tenant_id']      = $tenant_id;
        $data['updated_date']   = "NOW()";
        $data['zone']           = implode(",", $_REQUEST['zone']);

        if($kiw_db->insert("kiwire_allowed_zone", $data)){
    
            sync_logger("{$_SESSION['user_name']} create Zone Restriction {$_GET['name']}", $_SESSION['tenant_id']);
    
            echo json_encode(array("status" => "success", "message" => "SUCCESS: New Zone Restriction [{$_GET["name"]}] added"  , "data" => null));

        }
        else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }

    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}


function get_data()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->fetch_array("SELECT  * FROM kiwire_allowed_zone WHERE tenant_id = '{$tenant_id}' LIMIT 1000");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }

}


function get_single_data()
{

    global $kiw_db, $tenant_id;

    $id = $kiw_db->escape($_REQUEST['id']);

    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_allowed_zone WHERE tenant_id = '{$tenant_id}' AND id = '{$id}' LIMIT 1");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }

}


function delete()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $id = $kiw_db->escape($_POST['id']);

        if (!empty($id)) {

            $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_allowed_zone WHERE id = '" . $id . "' AND tenant_id = '$tenant_id'");
            $del_groupname = $kiw_temp['name'];

            $kiw_db->query("DELETE FROM kiwire_allowed_zone WHERE id = '" . $id . "' AND tenant_id = '$tenant_id'");

        }


        sync_logger("{$_SESSION['user_name']} delete Zone Restriction {$del_groupname}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Zone Restriction [{$del_groupname}] has been deleted", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}


function edit_single_data()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $id = $kiw_db->escape($_REQUEST['reference']);

        $new_name   = $kiw_db->sanitize($_REQUEST['name']);
        $new_zone   = $kiw_db->escape(implode(",", $_REQUEST['zone']));

        $kiw_db->query("UPDATE kiwire_allowed_zone SET updated_date = NOW(), name = '{$new_name}', zone = '{$new_zone}' WHERE id = '{$id}' AND tenant_id = '{$tenant_id}' LIMIT 1");


        sync_logger("{$_SESSION['user_name']} update Zone Restriction {$new_name}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Zone Restriction has been updated", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}
