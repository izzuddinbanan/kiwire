<?php

$kiw['module'] = "Report -> Insight -> Social Network Analytics";
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

    $kiw_result['type'] = $kiw_db->fetch_array("SELECT source, COUNT(*) AS count FROM kiwire_account_info WHERE (updated_date BETWEEN '{$startdate}' AND '{$enddate}') AND source != 'system' AND tenant_id = '{$_SESSION['tenant_id']}' GROUP BY source");
    $kiw_result['gender'] = $kiw_db->fetch_array("SELECT gender, COUNT(*) AS count FROM kiwire_account_info WHERE (updated_date BETWEEN '{$startdate}' AND '{$enddate}') AND source != 'system' AND tenant_id = '{$_SESSION['tenant_id']}' GROUP BY gender");
    $kiw_result['age'] = $kiw_db->fetch_array("SELECT age_group, COUNT(*) AS count FROM kiwire_account_info WHERE (updated_date BETWEEN '{$startdate}' AND '{$enddate}') AND source != 'system' AND tenant_id = '{$_SESSION['tenant_id']}' GROUP BY age_group");

    echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_result));


}



