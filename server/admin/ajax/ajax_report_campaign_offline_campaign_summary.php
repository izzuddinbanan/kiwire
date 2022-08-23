<?php

$kiw['module'] = "Report -> Coupon -> Impression Report";
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

    case "get_all_data":
        get_by_date();
        break;
    case "get_offline_summary":
        get_clicked_summary();
        break;
    default:
        echo "ERROR: Wrong implementation";
}


function reformat_date($kiw_date)
{

    return date("Y-m-d", strtotime($kiw_date));
}


function get_by_date()
{


    global $kiw_db;


    $kiw_date_start = sync_toutctime(date("Y-m-d H:i:s", strtotime($_REQUEST['date_start'])));

    $kiw_date_end = sync_toutctime(date("Y-m-d H:i:s", strtotime($_REQUEST['date_end'])));

    $kiw_date_end = date("Y-m-d H:i:s", strtotime("{$kiw_date_end} +1 Day -1 Second"));


    // list of campaign

    $kiw_campaign_offline_list = [];

    $kiw_campaigns = $kiw_db->fetch_array("SELECT * FROM kiwire_campaign_manager WHERE c_trigger IN ('dwell', 'disconnect') AND tenant_id = '{$_SESSION['tenant_id']}'");


    foreach ($kiw_campaigns as $kiw_campaign) {

        $kiw_campaign_offline_list[md5($kiw_campaign['name'])] = array("start" => reformat_date(sync_tolocaltime($kiw_campaign['date_start'])), "end" => reformat_date(sync_tolocaltime($kiw_campaign['date_end'])), "trigger" => ucfirst($kiw_campaign['c_trigger']), "target" => ucfirst($kiw_campaign['target']), "status" => ucfirst($kiw_campaign['status']));
    }

    unset($kiw_campaign);
    unset($kiw_campaigns);


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


    // get the result

    $kiw_results = $kiw_db->fetch_array("SELECT name, SUM(IFNULL(execute, 0)) AS execute FROM kiwire_report_campaign_offline WHERE tenant_id = '{$_SESSION['tenant_id']}' AND (report_date BETWEEN '{$kiw_date_start}' AND '{$kiw_date_end}') {$kiw_zone} {$kiw_project} GROUP BY name");


    foreach ($kiw_results as $kiw_result) {

        $kiw_result_list[] = array_merge($kiw_campaign_offline_list[md5($kiw_result['name'])], array("name" => $kiw_result['name'], "execute" => $kiw_result['execute']));
    }


    echo json_encode(array("status" => "success", "message" => "", "data" => $kiw_result_list));
}


function get_clicked_summary()
{


    global $kiw_db, $kiw_tenant;


    $kiw_timezone = $_SESSION['timezone'];

    $kiw_timezone = empty($kiw_timezone) ? "Asia/Kuala_Lumpur" : $kiw_timezone;


    $kiw_date_start = sync_toutctime(date("Y-m-d H:i:s", strtotime($_REQUEST['date_start'])));

    $kiw_date_end = sync_toutctime(date("Y-m-d H:i:s", strtotime($_REQUEST['date_end'])));

    $kiw_date_end = date("Y-m-d H:i:s", strtotime("{$kiw_date_end} +1 Day -1 Second"));



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


    $kiw_campaign_name = $kiw_db->escape($_REQUEST['campaign']);


    $kiw_results = $kiw_db->fetch_array("SELECT CONVERT_TZ(report_date, 'UTC', '{$kiw_timezone}') AS xreport_date, SUM(IFNULL(execute, 0)) AS execute FROM kiwire_report_campaign_offline WHERE tenant_id = '{$_SESSION['tenant_id']}' AND (report_date BETWEEN '{$kiw_date_start}' AND '{$kiw_date_end}') AND name = '{$kiw_campaign_name}' {$kiw_zone} {$kiw_project} GROUP BY xreport_date ORDER BY xreport_date ASC");

    $kiw_result_list = [];


    foreach ($kiw_results as $kiw_result) {

        $kiw_result_list['date'][] = date("Y-m-d H:i:s", strtotime($kiw_result['xreport_date']));
        $kiw_result_list['execute'][] = $kiw_result['execute'];
    }


    echo json_encode(array("status" => "success", "message" => "", "data" => $kiw_result_list));
}
