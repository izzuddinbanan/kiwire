<?php

$kiw['module'] = "Report -> Accounts -> Account Creation";
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


if (in_array($_SESSION['permission'], array("r", "rw"))) {


    $startdate = report_date_start($_REQUEST['startdate'], 30);

    $enddate = report_date_end($_REQUEST['enddate'], 1);


    $kiw_timezone = $_SESSION['timezone']; if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


    $kiw_temp = $kiw_db->fetch_array("SELECT DATE(CONVERT_TZ(report_date, 'UTC', '{$kiw_timezone}')) AS xreport_date, SUM(account_create) AS account FROM kiwire_report_login_general WHERE tenant_id = '{$_SESSION['tenant_id']}' AND (report_date BETWEEN '{$startdate}' AND '{$enddate}') GROUP BY xreport_date");

    echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


}

