<?php

$kiw['module'] = "Report -> Bandwidth Usage User";
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


$kiw_start_date = report_date_start($_REQUEST['startdate'], 30);
$kiw_end_date = report_date_end($_REQUEST['enddate'], 1);


$kiw_metric = $_SESSION['metrics'];

if ($kiw_metric == "Gb" || empty($kiw_metric)) $kiw_metric = 1024 * 1024 * 1024;
else $kiw_metric = 1024 * 1024;


$search_username = $kiw_db->escape($_REQUEST['username']);


$kiw_timezone = $_SESSION['timezone'];

if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


if (!empty($search_username)) $search_username = "AND username = '{$search_username}'";
else die(json_encode(array("status" => "success", "message" => "ERROR: Please provide at least one username", "data" => null)));

$kiw_result = [];


foreach (range(0, 5) as $kiw_range) {


    $kiw_current_table = date("Ym", strtotime("-{$kiw_range} Month"));

    $kiw_sessions = $kiw_db->fetch_array("SELECT DATE(CONVERT_TZ(start_time, 'UTC', '{$kiw_timezone}')) AS xreport_date, SUM(session_time) AS session_time, (SUM(quota_out) / {$kiw_metric}) AS quota_out, (SUM(quota_in) / {$kiw_metric}) AS quota_in FROM kiwire_sessions_{$kiw_current_table} WHERE (start_time BETWEEN '{$kiw_start_date}' AND '{$kiw_end_date}') AND tenant_id = '{$tenant_id}' {$search_username} GROUP BY xreport_date");


    foreach ($kiw_sessions as $kiw_session) {

        if (isset($kiw_result[$kiw_session['xreport_date']])) {

            $kiw_result[$kiw_session['xreport_date']]['session_time'] += $kiw_session['session_time'];
            $kiw_result[$kiw_session['xreport_date']]['quota_in'] += $kiw_session['quota_in'];
            $kiw_result[$kiw_session['xreport_date']]['quota_out'] += $kiw_session['quota_out'];


        } else $kiw_result[$kiw_session['xreport_date']] = $kiw_session;


    }


}


foreach ($kiw_result as $kiw_key => $kiw_value) {

    $kiw_unsort[$kiw_key] = $kiw_value;

}


foreach ($kiw_unsort as $kiw_re_sort => $kiw_re_value) {

    $kiw_results[] = $kiw_result[$kiw_re_sort];

}


echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_results));

