<?php

header("Content-Type: application/json");


require_once "../includes/include_general.php";
require_once "../includes/include_session.php";



$action = $_REQUEST['action'];

switch ($action) {

    case "register":register(); break;
    case "get_device":get_device(); break;
    case "get_update":get_update(); break;
    case "edit_single_data":edit_single_data(); break;
    case "delete":delete(); break;
 

    default:
        echo "ERROR: Wrong implementation";
}


function register()
{


    global $kiw_db, $kiw_tenant;


    $kiw_timezone = $_SESSION['timezone'];


    if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


    $kiw_username      = $_SESSION['cpanel']['username'];
    $kiw_tenant        =  $_SESSION['cpanel']['tenant_id'];


    $kiw_mac_address = $kiw_db->escape($_REQUEST['mac_address']);


    $kiw_existed = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_device_register WHERE tenant_id = '{$kiw_tenant}' AND mac_address = '{$kiw_mac_address}' LIMIT 1");


    if ($kiw_existed['kcount'] < 1) {


        $data['tenant_id']       = $kiw_tenant;
        $data['username']        = $kiw_username;
        $data['mac_address']     = $kiw_mac_address;


        if($kiw_db->insert("kiwire_device_register", $data)){

            sync_logger("{$kiw_username} register MAC Address {$_REQUEST['mac_address']}", "general");
            
            echo json_encode(array("status" => "Success", "message" => "SUCCESS: New device registered", "data" => null));

        }
        else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }
        
    } else {

        echo json_encode(array("status" => "Error", "message" => "ERROR: Device [{$kiw_mac_address}] already existed!", "data" => null));

    }


}


function get_device()
{


    global $kiw_db, $kiw_tenant, $kiw_username;


    if (strlen($kiw_username) > 0) {


        $kiw_device = $kiw_db->fetch_array("SELECT * FROM kiwire_device_register WHERE tenant_id ='{$kiw_tenant}' AND username = '{$kiw_username}'");


        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_device));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: Missing account details", "data" => null));

    }


}


function get_update() {

    
    global $kiw_db, $kiw_tenant, $kiw_username;


    $id = $kiw_db->escape($_GET['id']);


    if ($id !== null) {


        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_device_register WHERE id = '{$id}' AND tenant_id = '{$kiw_tenant}' AND username = '{$kiw_username}' LIMIT 1");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: There is an unexpected error", "data" => null));

    }


}


function edit_single_data() {


    global $kiw_db, $kiw_tenant, $kiw_username;


    $id = (int)$kiw_db->escape($_REQUEST['reference']);

    $new_mac_address  = $kiw_db->escape($_REQUEST['mac-address']);


    $kiw_db->query("UPDATE kiwire_device_register SET updated_date = NOW(), mac_address = '{$new_mac_address}' WHERE id = '{$id}' AND tenant_id = '{$kiw_tenant}' AND username = '{$kiw_username}' LIMIT 1");


    sync_logger("{$kiw_username} updated device {$new_mac_address}", "general");

    echo json_encode(array("status" => "success", "message" => "SUCCESS: Device has been updated", "data" => null));


}


function delete() {


    global $kiw_db, $kiw_tenant, $kiw_username;
    
    $id = $kiw_db->escape($_GET['id']);


    if (!empty($id)) {

        $kiw_device = $kiw_db->query_first("SELECT * FROM kiwire_device_register WHERE id = '{$id}' AND tenant_id = '{$kiw_tenant}' AND username = '{$kiw_username}'");

        $kiw_db->query("DELETE FROM kiwire_device_register WHERE id = '{$id}' AND tenant_id = '{$kiw_tenant}' AND username = '{$kiw_username}'");

    }

    sync_logger("{$kiw_username} deleted device {$kiw_device['mac_address']}", "general");

    echo json_encode(array("status" => "success", "message" => "SUCCESS: Device [ {$kiw_device['mac_address']} ] has been deleted", "data" => null));

    
}