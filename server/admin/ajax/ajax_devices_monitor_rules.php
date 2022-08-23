<?php

$kiw['module'] = "Device -> Monitoring -> Rules";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_general.php";
require_once "../includes/include_connection.php";

require_once "../../libs/class.sql.helper.php";


if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}


$action = $_REQUEST['action'];

switch ($action) {

    case "create":
        create();
        break;
    case "delete":
        delete();
        break;
    case "get_all":
        get_data();
        break;
    case "get_update":
        get_single_data();
        break;
    case "edit_single_data":
        edit_single_data();
        break;
    default:
        echo "ERROR: Wrong implementation";

}


function create()
{

    global $kiw_db, $tenant_id;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $rules_name = $kiw_db->sanitize($_GET['rules_name']);

        if (empty($rules_name)) die("ERROR: Rules Name required!");


        $kiw_temp = "SELECT COUNT(`name`) AS `ccount` FROM `kiwire_nms_rules` WHERE `name` = '{$rules_name}' AND `tenant_id` = '{$tenant_id}' LIMIT 1";
        $kiw_temp = $kiw_db->query_first($kiw_temp);

        if ($kiw_temp['ccount'] == 0) {

            $data = array();

            $data['tenant_id'] = $tenant_id;
            $data['name'] = $rules_name;
            $data['warning_cpu'] = $_GET['warning_cpu'];
            $data['critical_cpu'] = $_GET['critical_cpu'];
            $data['warning_disk'] = $_GET['warning_disk'];
            $data['critical_disk'] = $_GET['critical_disk'];
            $data['warning_memory'] = $_GET['warning_memory'];
            $data['critical_memory'] = $_GET['critical_memory'];
            $data['mib'] = $_GET['mib'];
            $data['description'] = $kiw_db->sanitize($_GET['description']);

            if($kiw_db->insert("kiwire_nms_rules", $data)){

                sync_logger("{$_SESSION['user_name']} create rules {$rules_name}", $_SESSION['tenant_id']);

                echo json_encode(array("status" => "success", "message" => "SUCCESS: New Rules : $rules_name  added", "data" => null));

            }
            else {

                echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));
    
            }

        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Rules Name already existed", "data" => null));

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

            $kiw_temp = $kiw_db->query_first("SELECT name FROM kiwire_nms_rules WHERE id = '{$id}' AND tenant_id = '{$tenant_id}'");
            $rules_name = $kiw_temp['name'];

            $kiw_db->query("DELETE FROM kiwire_nms_rules WHERE id = '{$id}' AND tenant_id = '{$tenant_id}'");

        }


        sync_logger("{$_SESSION['user_name']} deleted rules {$rules_name}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Rules $rules_name has been deleted", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}


function get_data()
{


    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->fetch_array("SELECT  * FROM kiwire_nms_rules  WHERE tenant_id = '{$tenant_id}' LIMIT 1000");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }


}


function get_single_data()
{


    global $kiw_db, $tenant_id;

    $id = $kiw_db->escape($_REQUEST['id']);


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_nms_rules WHERE tenant_id = '{$tenant_id}' AND id = '{$id}' LIMIT 1");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }


}


function edit_single_data()
{

    global $kiw_db;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));
        
        $id = $kiw_db->escape($_REQUEST['reference']);

        $kiw_data['name'] = $kiw_db->sanitize($_REQUEST['rules_name']);
        $kiw_data['mib'] = $kiw_db->escape($_REQUEST['mib']);
        $kiw_data['description'] = $kiw_db->sanitize($_REQUEST['description']);
        $kiw_data['warning_cpu'] = $kiw_db->escape($_REQUEST['warning_cpu']);

        $kiw_data['critical_cpu'] = $kiw_db->escape($_REQUEST['critical_cpu']);
        $kiw_data['warning_disk'] = $kiw_db->escape($_REQUEST['warning_disk']);
        $kiw_data['critical_disk'] = $kiw_db->escape($_REQUEST['critical_disk']);
        $kiw_data['warning_memory'] = $kiw_db->escape($_REQUEST['warning_memory']);
        $kiw_data['critical_memory'] = $kiw_db->escape($_REQUEST['critical_memory']);

        $kiw_db->query(sql_update($kiw_db, "kiwire_nms_rules", $kiw_data, "id = '{$id}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1"));


        sync_logger("{$_SESSION['user_name']} updated rules {$kiw_data['name']}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Rules {$kiw_data['name']} has been updated", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}
