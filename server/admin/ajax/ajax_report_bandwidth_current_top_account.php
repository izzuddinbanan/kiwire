<?php

$kiw['module'] = "Report -> Top Current Bandwidth User";
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


        $kiw_timezone = $_SESSION['timezone'];

        if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


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



        // $kiw_zone = $kiw_db->escape($_REQUEST['zone']);


        // if (!empty($kiw_zone)) {

        //     $kiw_zone = explode(":", $kiw_zone);

        //     $kiw_zone = "AND zone = '{$kiw_zone[1]}'";
        // }


        $kiw_temp = $kiw_db->fetch_array("SELECT CONVERT_TZ(start_time, 'UTC', '{$kiw_timezone}') AS start_time, username, SUM(session_time) AS session_time, ip_address, mac_address, (SUM(quota_out) / {$kiw_metric}) AS quota_out, (SUM(quota_in) / {$kiw_metric}) AS quota_in, avg_speed FROM kiwire_active_session WHERE tenant_id = '{$tenant_id}' {$kiw_zone} {$kiw_project} GROUP BY username ORDER BY quota_out DESC");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp, "query" => "SELECT CONVERT_TZ(start_time, 'UTC', '{$kiw_timezone}') AS start_time, username, SUM(session_time) AS session_time, ip_address, mac_address, (SUM(quota_out) / {$kiw_metric}) AS quota_out, (SUM(quota_in) / {$kiw_metric}) AS quota_in, avg_speed FROM kiwire_active_session WHERE tenant_id = '{$tenant_id}' {$kiw_zone} {$kiw_project} GROUP BY username ORDER BY quota_out DESC"));
    }
}
