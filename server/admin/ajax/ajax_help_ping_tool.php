<?php

$kiw['module'] = "Campaign -> Company Apps";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_connection.php";

if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}


$kiw_dest_ip = $_REQUEST['ip_address'];

if (!empty($kiw_dest_ip)) {


    $kiw_dest_ip = preg_replace("/[^A-Za-z0-9-.]+$/", "", $kiw_dest_ip);

    $kiw_temp = `ping -c 5 -W 5 {$kiw_dest_ip}`;

    echo json_encode(array("status" => "success", "message" => null, "data" => "<pre class='p-3 progress-space'>{$kiw_temp}</pre>"));


} else {

    echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

}