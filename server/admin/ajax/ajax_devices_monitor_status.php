<?php


$kiw['module'] = "Device -> Monitoring -> Status";
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

    case "get_all": get_data(); break;

    default: echo "ERROR: Wrong implementation";
}


function get_data(){


    global $kiw_db, $tenant_id;


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_timezone = $_SESSION['timezone'];

        if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


        $kiw_temp = $kiw_db->fetch_array("SELECT unique_id,device_ip,device_type,status, CONVERT_TZ(last_update, 'UTC', '{$kiw_timezone}') AS last_update FROM kiwire_controller WHERE monitor_method IN ('ping', 'snmp', 'wifidog') AND tenant_id = '{$_SESSION['tenant_id']}' ORDER BY status DESC");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }


}
