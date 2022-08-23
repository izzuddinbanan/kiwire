<?php

$kiw['module'] = "Report -> Top Historic Bandwidth User";
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


$kiw_type = $kiw_db->escape($_REQUEST['type']);


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


$kiw_result = [];


foreach (range(0, 5) as $kiw_range) {


    $kiw_current_table = date("Ym", strtotime("-{$kiw_range} Month"));

    $kiw_sessions = $kiw_db->fetch_array("SELECT start_time,username,SUM(session_time) AS session_time,ip_address,mac_address, (SUM(quota_out) / {$kiw_metric}) AS quota_out, (SUM(quota_in) / {$kiw_metric}) AS quota_in FROM kiwire_sessions_{$kiw_current_table} WHERE (start_time BETWEEN '{$kiw_start_date}' AND '{$kiw_end_date}') AND tenant_id = '{$tenant_id}' {$kiw_zone} {$kiw_project} GROUP BY username");


    foreach ($kiw_sessions as $kiw_session) {

        if (isset($kiw_result[$kiw_session['username']])) {

            $kiw_result[$kiw_session['username']]['session_time'] += $kiw_session['session_time'];
            $kiw_result[$kiw_session['username']]['quota_in'] += $kiw_session['quota_in'];
            $kiw_result[$kiw_session['username']]['quota_out'] += $kiw_session['quota_out'];
        } else $kiw_result[$kiw_session['username']] = $kiw_session;
    }
}


foreach ($kiw_result as $kiw_key => $kiw_value) {

    if ($kiw_type == "download") {

        $kiw_unsort[$kiw_key] = $kiw_value['quota_out'];
    } else {

        $kiw_unsort[$kiw_key] = $kiw_value['quota_in'];
    }
}

arsort($kiw_unsort, 1);


foreach ($kiw_unsort as $kiw_re_sort => $kiw_re_value) {

    $kiw_results[] = $kiw_result[$kiw_re_sort];
}



echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_results));
