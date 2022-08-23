<?php

$kiw['module'] = "Report -> Sign Up Registration Data";
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

    case "get_by_date"  : get_by_date(); break;
    default: echo "ERROR: Wrong implementation";

}



function get_by_date()
{

    global $kiw_db, $tenant_id;


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $startdate = report_date_start($_REQUEST['startdate'], 30);

        $enddate = report_date_end($_REQUEST['enddate'], 1);


        $kiw_temp = $kiw_db->fetch_array("SELECT * FROM kiwire_account_info WHERE (updated_date BETWEEN '{$startdate}' AND '{$enddate}') AND tenant_id = '{$tenant_id}' ORDER BY updated_date ASC");


        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }

}