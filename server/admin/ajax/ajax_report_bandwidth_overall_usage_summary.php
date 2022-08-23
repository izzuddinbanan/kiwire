<?php

$kiw['module'] = "Report -> Bandwidth Usage Summary";
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


$startdate = report_date_start($_REQUEST['startdate'], 30);
$enddate = report_date_end($_REQUEST['enddate'], 1);


$timezone = $_SESSION['timezone'];

if (empty($timezone)) $timezone = "Asia/Kuala_Lumpur";


$kiw_metric = $_SESSION['metrics'];

if ($kiw_metric == "Gb" || empty($kiw_metric)) $kiw_metric = 1024 * 1024 * 1024;
else $kiw_metric = 1024 * 1024;


// filter by zone/project

$kiw_zone = $kiw_db->escape($_REQUEST['zone']);


if (!empty($kiw_zone)) {

    $kiw_zone = explode(":", $kiw_zone);

    $kiw_zone = "AND zone = '{$kiw_zone[1]}'";


} else {

    $kiw_zone = '';
}


$kiw_project = $kiw_db->escape($_REQUEST['project']);


if (!empty($kiw_project)) {

    $kiw_project = explode(":", $kiw_project);

    $kiw_project_list = $kiw_db->query_first("SELECT * FROM kiwire_project WHERE name = '{$kiw_project[1]}'");

    $kiw_array = explode(",", $kiw_project_list['zone_list']);

    $kiw_project = "AND zone IN ('" . implode("','", $kiw_array) . "')";


} else {

    $kiw_project = '';

}

// end



if (in_array($_SESSION['permission'], array("r", "rw"))) {


    // $kiw_temp = $kiw_db->fetch_array("SELECT DATE(CONVERT_TZ(report_date, 'UTC', '{$timezone}')) AS xreport_date, (((SUM(quota_in) * 8) / SUM(time)) / {$kiw_metric}) AS `quota_in`, (((SUM(quota_out) * 8) / SUM(time)) / {$kiw_metric}) AS `quota_out`  FROM kiwire_report_login_general WHERE tenant_id = '{$_SESSION['tenant_id']}' AND (report_date BETWEEN '{$startdate}' AND '{$enddate}') {$kiw_zone} GROUP BY xreport_date");
    $kiw_temp = $kiw_db->fetch_array("SELECT DATE(CONVERT_TZ(report_date, 'UTC', '{$timezone}')) AS xreport_date, (SUM(quota_in) / {$kiw_metric}) AS `quota_in`, (SUM(quota_out) / {$kiw_metric}) AS `quota_out`  FROM kiwire_report_login_general WHERE tenant_id = '{$_SESSION['tenant_id']}' AND (report_date BETWEEN '{$startdate}' AND '{$enddate}') {$kiw_zone} {$kiw_project} GROUP BY xreport_date");

    echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp, "datum" => $kiw_project, "query" => "SELECT DATE(CONVERT_TZ(report_date, 'UTC', '{$timezone}')) AS xreport_date, (SUM(quota_in) / {$kiw_metric}) AS `quota_in`, (SUM(quota_out) / {$kiw_metric}) AS `quota_out`  FROM kiwire_report_login_general WHERE tenant_id = '{$_SESSION['tenant_id']}' AND (report_date BETWEEN '{$startdate}' AND '{$enddate}') {$kiw_zone} {$kiw_project} GROUP BY xreport_date" ));
}
