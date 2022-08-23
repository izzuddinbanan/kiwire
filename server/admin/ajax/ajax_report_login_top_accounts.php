<?php

$kiw['module'] = "Report -> Top Account";
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

    $timezone = $_SESSION['timezone'];

    if (empty($timezone)) $timezone = "Asia/Kuala_Lumpur";


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


    $search_type = $kiw_db->escape($_REQUEST['type']);

    $kiw_result = [];


    if ($search_type == "username") {

        foreach (range(0, 5) as $kiw_range) {


            $kiw_current_table = date("Ym", strtotime("-{$kiw_range} Month"));

            $kiw_records = $kiw_db->fetch_array("SELECT username, COUNT(*) AS count FROM kiwire_sessions_{$kiw_current_table} WHERE tenant_id = '{$_SESSION['tenant_id']}' AND (start_time BETWEEN '{$startdate}' AND '{$enddate}') {$kiw_zone} {$kiw_project} GROUP BY username");


            foreach ($kiw_records as $kiw_record) {

                $kiw_result[" {$kiw_record['username']}"] += $kiw_record['count'];
            }
        }
    
    } else {


        foreach (range(0, 5) as $kiw_range) {


            $kiw_current_table = date("Ym", strtotime("-{$kiw_range} Month"));

            $kiw_records = $kiw_db->fetch_array("SELECT mac_address, COUNT(*) AS count FROM kiwire_sessions_{$kiw_current_table} WHERE tenant_id = '{$_SESSION['tenant_id']}' AND (start_time BETWEEN '{$startdate}' AND '{$enddate}') {$kiw_zone} {$kiw_project} GROUP BY mac_address");


            foreach ($kiw_records as $kiw_record) {

                $kiw_result[$kiw_record['mac_address']] += $kiw_record['count'];
            }
        }
    }


    arsort($kiw_result, 1);


    $kiw_result = array_slice($kiw_result, 0, 50);


    echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_result));
}
