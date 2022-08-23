<?php

$kiw['module'] = "Report -> Impression vs Login Report";
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

    case "get_all": get_all(); break;
    case "get_detail": get_detail(); break;
    case "calculate_totalAvg"  : calculate_totalAvg(); break;
    default: echo "ERROR: Wrong implementation";

}


function calculate_totalAvg()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $startdate = report_date_start($_REQUEST['startdate'], 30);

        $enddate = report_date_end($_REQUEST['enddate'], 1);


        $timezone = $_SESSION['timezone'];


        if(empty($timezone)) $timezone = "Asia/Kuala_Lumpur";


        $kiw_result = [];


        $kiw_temps = $kiw_db->query_first("SELECT CONVERT_TZ(report_date, 'UTC', '{$timezone}') AS xreport_date, SUM(IFNULL(impress, 0)) AS total_impression, round(AVG(IFNULL(impress, 0))) AS avg_impress FROM kiwire_report_campaign_general WHERE (report_date BETWEEN '{$startdate}' AND '{$enddate}') AND tenant_id = '{$tenant_id}' ");

        $kiw_result['total_impression'] = $kiw_temps['total_impression'];

        $kiw_result['avg_impress'] = $kiw_temps['avg_impress'];


        $kiw_temps = $kiw_db->query_first("SELECT CONVERT_TZ(report_date, 'UTC', '{$timezone}') AS xreport_date, SUM(IFNULL(succeed, 0)) AS total_login, round(AVG(IFNULL(succeed, 0))) AS avg_login FROM kiwire_report_login_general WHERE (report_date BETWEEN '{$startdate}' AND '{$enddate}') AND tenant_id = '{$tenant_id}' ");

        $kiw_result['total_login'] = $kiw_temps['total_login'];

        $kiw_result['avg_login'] = $kiw_temps['avg_login'];


        echo json_encode(array("status" => "success", "message" => null, "data" => [$kiw_result]));


    }


}


function get_all()
{

    global $kiw_db, $tenant_id;


    $startdate = report_date_start($_REQUEST['startdate'], 30);

    $enddate = report_date_end($_REQUEST['enddate'], 1);


    $timezone = $_SESSION['timezone'];

    if (empty($timezone)) $timezone = "Asia/Kuala_Lumpur";


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_results = [];


        $kiw_temps = $kiw_db->fetch_array("SELECT DATE(CONVERT_TZ(report_date, 'UTC', '{$timezone}')) AS xreport_date, SUM(impress) AS impression FROM kiwire_report_campaign_general WHERE tenant_id = '{$_SESSION['tenant_id']}' AND (report_date BETWEEN '{$startdate}' AND '{$enddate}')  GROUP BY xreport_date");

        foreach ($kiw_temps as $kiw_temp){

            $kiw_results[$kiw_temp['xreport_date']]['impression'] = $kiw_temp['impression'];

        }


        $kiw_temps = $kiw_db->fetch_array("SELECT DATE(CONVERT_TZ(report_date, 'UTC', '{$timezone}')) AS xreport_date, SUM(succeed) AS login FROM kiwire_report_login_general WHERE tenant_id = '{$_SESSION['tenant_id']}' AND (report_date BETWEEN '{$startdate}' AND '{$enddate}')  GROUP BY xreport_date");

        foreach ($kiw_temps as $kiw_temp){

            $kiw_results[$kiw_temp['xreport_date']]['login'] = $kiw_temp['login'];

        }


        $kiw_response = [];

        foreach ($kiw_results as $kiw_date => $kiw_value){

            $kiw_response[] = array(
                "xreport_date" => $kiw_date,
                "login" => ($kiw_value['login'] > 0) ? $kiw_value['login'] : 0,
                "impression" => ($kiw_value['impression'] > 0) ? $kiw_value['impression'] : 0
            );

        }


        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_response));


    }


}


function get_detail()
{

    global $kiw_db;


    $startdate = sync_toutctime(date("Y-m-d H:i:s", strtotime($_REQUEST['report_date'])));

    $enddate = date("Y-m-d H:i:s", strtotime($startdate . " +1 Day -1 Second"));


    $timezone = $_SESSION['timezone'];


    if (empty($timezone)) $timezone = "Asia/Kuala_Lumpur";


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_results = [];


        $kiw_temps = $kiw_db->fetch_array("SELECT HOUR(CONVERT_TZ(report_date, 'UTC', '{$timezone}')) AS xreport_date, SUM(impress) AS impression FROM kiwire_report_campaign_general WHERE tenant_id = '{$_SESSION['tenant_id']}' AND (report_date BETWEEN '{$startdate}' AND '{$enddate}') GROUP BY xreport_date");

        foreach ($kiw_temps as $kiw_temp){

            $kiw_results[$kiw_temp['xreport_date']]['impression'] = $kiw_temp['impression'];

        }


        $kiw_temps = $kiw_db->fetch_array("SELECT HOUR(CONVERT_TZ(report_date, 'UTC', '{$timezone}')) AS xreport_date, SUM(succeed) AS login FROM kiwire_report_login_general WHERE tenant_id = '{$_SESSION['tenant_id']}' AND (report_date BETWEEN '{$startdate}' AND '{$enddate}') GROUP BY xreport_date");

        foreach ($kiw_temps as $kiw_temp){

            $kiw_results[$kiw_temp['xreport_date']]['login'] = $kiw_temp['login'];

        }


        $kiw_response = [];

        foreach ($kiw_results as $kiw_date => $kiw_value){

            $kiw_response[] = array(
                "xreport_date" => $kiw_date,
                "login" => ($kiw_value['login'] > 0) ? $kiw_value['login'] : 0,
                "impression" => ($kiw_value['impression'] > 0) ? $kiw_value['impression'] : 0
            );

        }


        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_response));




    }


}