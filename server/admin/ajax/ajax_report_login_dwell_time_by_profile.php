<?php

$kiw['module'] = "Report -> User Dwell Time by Profile";
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



        if (isset($_REQUEST['profile']) && !empty($_REQUEST['profile'])) {

            $profile = " AND profile = '" . $kiw_db->escape($_REQUEST['profile']) . "'";
        } else $profile = "";


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


        $kiw_temp = $kiw_db->fetch_array("SELECT DATE(CONVERT_TZ(report_date, 'UTC', '{$timezone}')) AS xreport_date, profile, SUM(IFNULL(login, 0)) AS login, SUM(IFNULL(dwell, 0)) AS dwell FROM kiwire_report_login_profile  WHERE tenant_id = '{$_SESSION['tenant_id']}' AND (report_date BETWEEN '{$startdate}' AND '{$enddate}') {$profile} {$kiw_zone} {$kiw_project} GROUP BY xreport_date,profile");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));
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


        $kiw_temp = $kiw_db->fetch_array("SELECT CONVERT_TZ(report_date, 'UTC', '{$timezone}') AS xreport_date, profile, SUM(IFNULL(login, 0)) AS login, SUM(IFNULL(dwell, 0)) AS dwell FROM kiwire_report_login_profile WHERE tenant_id = '{$_SESSION['tenant_id']}' AND (report_date BETWEEN '{$startdate}' AND '{$enddate}') GROUP BY xreport_date,profile");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));
    }
}
