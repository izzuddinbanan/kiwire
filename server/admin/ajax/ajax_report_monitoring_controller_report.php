<?php

$kiw['module'] = "Report -> Monitoring -> Controller Report";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_connection.php";
require_once "../includes/include_report.php";
require_once "../includes/include_general.php";



if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}

$action = $_REQUEST['action'];

switch ($action) {

    case "get_by_date": get_by_date(); break;
    default: echo "ERROR: Wrong implementation";

}


function get_by_date()
{


    global $kiw_db;

    $startdate = report_date_start($_REQUEST['startdate'], 30);
    $enddate = report_date_end($_REQUEST['enddate'], 1);

    $timezone = $_SESSION['timezone'];

    if (empty($timezone)) $timezone = "Asia/Kuala_Lumpur";

    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->fetch_array("SELECT DATE(CONVERT_TZ(report_date, 'UTC', '{$timezone}')) AS xreport_date, SUM(incident_count) AS incident_count, AVG(running) AS running, AVG(total) AS total, issue FROM kiwire_report_controller WHERE tenant_id = '{$_SESSION['tenant_id']}' AND (report_date BETWEEN '{$startdate}' AND '{$enddate}') GROUP BY xreport_date");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));

      

    }



}



