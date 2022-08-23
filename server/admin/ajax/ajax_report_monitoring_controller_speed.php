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


$action = $_REQUEST['action'];

switch ($action) {

    case "get_all":
        get_data();
        break;
    default:
        echo "ERROR: Wrong implementation";
}


function get_data()
{

    global $kiw_db, $tenant_id, $cache, $file_name;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $startdate = report_date_start($_REQUEST['startdate'], 30);
        $enddate = report_date_end($_REQUEST['enddate'], 1);

        $timezone = $_SESSION['timezone'];

        if (empty($timezone)) $timezone = "Asia/Kuala_Lumpur";


        $kiw_metric = $_SESSION['metrics'];

        if ($kiw_metric == "Gb" || empty($kiw_metric)) $kiw_metric = 1024 * 1024 * 1024;
        else $kiw_metric = 1024 * 1024;


        $search_controller = $kiw_db->escape($_REQUEST['controller']);
        if (!empty($search_controller)) $search_controller = "AND unique_id = '{$search_controller}'";


        // $kiw_temp = $kiw_db->fetch_array("SELECT DATE(CONVERT_TZ(report_date, 'UTC', '{$timezone}')) AS xreport_date, (SUM(quota_download) / {$kiw_metric}) AS `quota_download`, (SUM(quota_upload) / {$kiw_metric}) AS `quota_upload`  FROM kiwire_report_controller_statistics WHERE tenant_id = '{$_SESSION['tenant_id']}' {$search_controller} GROUP BY xreport_date");
        $kiw_temp = $kiw_db->fetch_array("SELECT DATE(CONVERT_TZ(report_date, 'UTC', '{$timezone}')) AS xreport_date, (SUM(avg_speed) / {$kiw_metric}) AS `avg_speed` FROM kiwire_report_controller_statistics WHERE tenant_id = '{$_SESSION['tenant_id']}' AND (report_date BETWEEN '{$startdate}' AND '{$enddate}') {$search_controller} GROUP BY xreport_date");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));
    }
}
