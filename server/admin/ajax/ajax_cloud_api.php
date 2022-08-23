<?php

$kiw['module'] = "Cloud -> API";
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
    case "genkey": genkey(); break;
    case "create": create(); break;
    case "delete": delete(); break;
    case "get_all": get_data(); break;
    case "get_update": get_single_data(); break;
    case "edit_single_data": edit_single_data(); break;
    default: echo "ERROR: Wrong implementation";
}


function create()
{
    global $kiw_db;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $kiw_row = $kiw_db->query_first("SELECT COUNT(*) AS ccount FROM kiwire_int_api_setting WHERE api_key = '" . $kiw_db->escape($_GET['api_key']) . "' AND tenant_id = 'superuser'");


        if ($kiw_row['ccount'] < 1) {


            $data['api_key']       = $kiw_db->escape($_GET['api_key']);
            $data['permission']    = $kiw_db->escape($_GET['permission']);
            $data['enabled']       = ($_GET['enabled'] == "" ? "n" : "y");
            $data['tenant_id']     = "superuser";

            $data['updated_date']  = date('Y-m-d H-i-s');
            $data['module']        = (empty($_GET['groupname']) ? "" : $_GET['groupname']);

            unset($kiw_module);

            if($kiw_db->insert("kiwire_int_api_setting", $data)){

                sync_logger("{$_SESSION['user_name']} create Authentication Key {$_GET['api_key']}", "superuser");

                echo json_encode(array("status" => "success", "message" => "SUCCESS: New Authentication Key added", "data" => null));


            }
            else {

                echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

            }


            

        } else {
            
            echo json_encode(array("status" => "error", "message" => "ERROR: Authentication Key already exists!", "data" => null));

        }


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

        if (!empty($id)) {

            $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_int_api_setting WHERE id = '" . $id . "' AND tenant_id = 'superuser'");

            $sql = $kiw_db->query("DELETE FROM kiwire_int_api_setting WHERE id = '" . $id . "' AND tenant_id = 'superuser'");
        }


        sync_logger("{$_SESSION['user_name']} deleted Authentication Key {$kiw_temp['api_key']}", "superuser");

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Authentication Key has been deleted", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}




function get_data()
{
    global $kiw_db;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->fetch_array("SELECT  * FROM kiwire_int_api_setting WHERE tenant_id = 'superuser' LIMIT 1000");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }

}


function get_single_data()
{

    global $kiw_db;

    $id = $kiw_db->escape($_REQUEST['id']);

    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_int_api_setting  WHERE tenant_id = 'superuser' AND id = '{$id}' LIMIT 1");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));

    }

}



function edit_single_data()
{

    global $kiw_db;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $id = $kiw_db->escape($_REQUEST['reference']);

        $new_enabled    = $kiw_db->escape($_REQUEST['enabled']);
        $new_permission = $kiw_db->escape($_REQUEST['permission']);
        $new_apikey     = $kiw_db->escape($_REQUEST['api_key']);

        $new_groupname    = $kiw_db->escape(empty($_REQUEST['groupname']) ? "" : $_GET['groupname']);
       

        $kiw_db->query("UPDATE kiwire_int_api_setting SET updated_date = NOW(),  enabled = '{$new_enabled}', api_key = '{$new_apikey}',  permission = '{$new_permission}', module = '{$new_groupname}' WHERE id = '{$id}' AND tenant_id = 'superuser' LIMIT 1");


        sync_logger("{$_SESSION['user_name']} updated Authentication Key {$new_apikey}", "superuser");

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Authentication has been updated", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}


function genkey(){


    $data_encrypted = sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );

    echo json_encode(array("status" => "success", "message" => "", "data" => $data_encrypted));


}