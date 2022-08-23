<?php

$kiw['module'] = "Configuration -> Network Setting";
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

    case "update": update(); break;
    default: echo "ERROR: Wrong implementation";

}

function update()
{

    global $kiw_db;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {
        csrf($kiw_db->escape($_REQUEST['token']));
        
        $kiw_data['action']              = "change_ip";
        $kiw_data['connection_name']     = $_REQUEST['connection_name'];
        $kiw_data['hostname']            = $_REQUEST['hostname'];
        $kiw_data['ip_address']          = $_REQUEST['ip_address'];
        $kiw_data['gateway_ip']          = $_REQUEST['gateway_ip'];
        $kiw_data['dns_one']             = $_REQUEST['dns_one'];
        $kiw_data['dns_two']             = $_REQUEST['dns_two'];


        $kiw_curl = curl_init();

        curl_setopt($kiw_curl, CURLOPT_URL, "http://127.0.0.1:9956");
        curl_setopt($kiw_curl, CURLOPT_POST, true);
        curl_setopt($kiw_curl, CURLOPT_POSTFIELDS, http_build_query($kiw_data));
        curl_setopt($kiw_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($kiw_curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($kiw_curl, CURLOPT_CONNECTTIMEOUT, 20);

        $kiw_test = curl_exec($kiw_curl);

        curl_close($kiw_curl);
    

        sync_logger("{$_SESSION['user_name']} updated Network setting", $_SESSION['tenant_id']);

        echo json_encode(json_decode($kiw_test, true));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}