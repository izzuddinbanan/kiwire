<?php

$kiw['module'] = "Help -> Database Disk Usage";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_connection.php";


if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}

$kiw_temp = dirname(__FILE__, 3) . "/libs/";

$kiw_data = `sudo /usr/bin/perl {$kiw_temp}/mysqltuner.pl`;


echo json_encode(array("status" => "success", "message" => "sudo perl {$kiw_temp}/mysqltuner.pl", "data" => base64_encode($kiw_data)));