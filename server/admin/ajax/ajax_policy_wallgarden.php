<?php

$kiw['module'] = "Policy -> Wallgarden";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
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

        $data['dest']              = $_GET['dest'];
        $data['nasid']             = $_GET['nasid'];
        $data['remark']             = $_GET['remark'];
        $data['username']           = $_SESSION['username'];

        $data['tenant_id']           = $tenant_id;

        if($kiw_db->insert("kiwire_wallgarden", $data)){
            
            echo json_encode(array("status" => "success", "message" => "SUCCESS: New Wallgarden : " . $_GET['dest'] . " added", "data" => null));

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

            $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_wallgarden WHERE id = '" . $id . "' AND tenant_id = '$tenant_id'");
            $del_wallgarden = $kiw_temp['nasid'];

            $sql = $kiw_db->query("DELETE FROM kiwire_wallgarden WHERE id = '" . $id . "' AND tenant_id = '$tenant_id'");
        }

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Wallgarden : $del_wallgarden has been deleted", "data" => null));
    } else

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
}


function get_data()
{
    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        $kiw_temp = $kiw_db->fetch_array("SELECT  * FROM kiwire_wallgarden WHERE tenant_id = '{$tenant_id}' LIMIT 1000");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));
    }
}


function get_single_data()
{

    global $kiw_db, $tenant_id;

    $id = $kiw_db->escape($_REQUEST['id']);

    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_wallgarden WHERE tenant_id = '$tenant_id' AND id = '{$id}' LIMIT 1");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));
    }
}



function edit_single_data()
{
    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));
        
        $id = $kiw_db->escape($_REQUEST['reference']);

        $new_dest   = $kiw_db->escape($_REQUEST['dest']);
        $new_nasid   = $kiw_db->escape($_REQUEST['nasid']);
        $new_remark   = $kiw_db->escape($_REQUEST['remark']);

        $kiw_db->query("UPDATE kiwire_wallgarden SET updated_date = NOW(), nasid = '{$new_nasid}', dest = '{$new_dest}', remark = '{$new_remark}' WHERE id = '{$id}' AND tenant_id = '{$tenant_id}' LIMIT 1");
        echo json_encode(array("status" => "success", "message" => "SUCCESS: Wallgarden has been updated", "data" => null));
    } else

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
}
