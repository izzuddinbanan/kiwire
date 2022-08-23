<?php

$kiw['module'] = "Report -> Monitoring -> Controller Bandwidth";
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
        get_all();
        break;
    case "get_detail":
        get_detail();
        break;
    default:
        echo "ERROR: Wrong implementation";

}


function get_all()
{


    global $kiw_db;


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $startdate = report_date_start($_REQUEST['startdate'], 30);

        $enddate = report_date_end($_REQUEST['enddate'], 1);


        $kiw_controller = $kiw_db->escape($_REQUEST['controller']);


        if (!empty($kiw_controller) && $kiw_controller != 'none') {


            $kiw_metric = $_SESSION['metrics'];


            if($kiw_metric == "Gb" || empty($kiw_metric)) $kiw_metric = 1024 * 1024 * 1024;
            else $kiw_metric = 1024 * 1024;


            $timezone = $_SESSION['timezone'];

            if (empty($timezone)) $timezone = "Asia/Kuala_Lumpur";


            $kiw_temp = $kiw_db->fetch_array("SELECT DATE(CONVERT_TZ(report_date, 'UTC', '{$timezone}')) AS xreport_date, (SUM(quota_upload) / {$kiw_metric}) AS quota_upload, (SUM(quota_download) / {$kiw_metric}) AS quota_download, AVG(avg_speed) AS average_speed FROM kiwire_report_controller_statistics WHERE tenant_id = '{$_SESSION['tenant_id']}' AND unique_id = '{$kiw_controller}' AND (report_date BETWEEN '{$startdate}' AND '{$enddate}') GROUP BY xreport_date");


            echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please provide a valid controller identity", "data" => null));

        }


    }


}


function get_detail(){


    global $kiw_db;


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $startdate = report_date_start($_REQUEST['report_date']);

        $enddate = date("Y-m-d H:i:s", strtotime("{$startdate} +1 Day -1 Second"));


        $kiw_controller = $kiw_db->escape($_REQUEST['controller']);


        if (!empty($kiw_controller) && $kiw_controller != 'none') {


            $kiw_metric = $_SESSION['metrics'];


            if($kiw_metric == "Gb" || empty($kiw_metric)) $kiw_metric = 1024 * 1024 * 1024;
            else $kiw_metric = 1024 * 1024;


            $timezone = $_SESSION['timezone'];

            if (empty($timezone)) $timezone = "Asia/Kuala_Lumpur";


            $kiw_temp = $kiw_db->fetch_array("SELECT HOUR(CONVERT_TZ(report_date, 'UTC', '{$timezone}')) AS xreport_date, (SUM(quota_upload) / {$kiw_metric}) AS quota_upload, (SUM(quota_download) / {$kiw_metric}) AS quota_download, AVG(avg_speed) AS average_speed FROM kiwire_report_controller_statistics WHERE tenant_id = '{$_SESSION['tenant_id']}' AND unique_id = '{$kiw_controller}' AND (report_date BETWEEN '{$startdate}' AND '{$enddate}') GROUP BY xreport_date");


            echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please provide a valid controller identity", "data" => null));

        }


    }


}