<?php

$kiw['module'] =  "Report -> System Log";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_connection.php";
require_once "../includes/include_report.php";

if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}

$action = $_REQUEST['action'];

switch ($action) {

    case "get_data": get_data(); break;
    default: echo "ERROR: Wrong implementation";

    
}


function get_by_date()
{
    
    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        // $log = $_GET['xid'];

        // if (empty($log)) $filename = "/var/log/kiwire_syslog.log";
        // else $filename = "/var/log/" . $log;

        // $filename = escapeshellarg($filename);

        // $x = `tail -n 100 $filename`;
        // $x = trim($x);

        // echo "<pre>{$x}</pre>";
        
        
    }
    
}