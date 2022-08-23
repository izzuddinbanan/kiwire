<?php

$kiw['module'] = "Report -> User Dwell Time";
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

    global $kiw_db, $tenant_id;

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


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->fetch_array("SELECT DATE(CONVERT_TZ(report_date, 'UTC', '{$timezone}')) AS xreport_date, SUM(succeed) AS total, SUM(dwell) AS dwell FROM kiwire_report_login_general WHERE tenant_id = '{$_SESSION['tenant_id']}' AND (report_date BETWEEN '{$startdate}' AND '{$enddate}') {$kiw_zone} {$kiw_project} GROUP BY xreport_date");

        $begin = new DateTime($startdate);
        $end = new DateTime($enddate);
        $end = $end->modify('+1 day');

        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($begin, $interval, $end);

        if (!$kiw_temp) {

            foreach ($period as $dt) {

                $kiw_temp[] = array(
                    'xreport_date'  => $dt->format("Y-m-d"),
                    'total'         => 0,
                    'dwell'         => 0,
                );
            }
        }

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));
    }
}





function get_detail()
{

    global $kiw_db, $tenant_id;


    $startdate = sync_toutctime(date("Y-m-d H:i:s", strtotime($_REQUEST['report_date'])));

    $enddate = date("Y-m-d H:i:s", strtotime($startdate . " +1 Day -1 Second"));


    $timezone = $_SESSION['timezone'];

    if (empty($timezone)) $timezone = "Asia/Kuala_Lumpur";


    // $kiw_zone = $kiw_db->escape($_REQUEST['zone']);

    // if (!empty($kiw_zone)) {

    //     $kiw_zone = explode(":", $kiw_zone);

    //     $kiw_zone = "AND zone = '{$kiw_zone[1]}'";
    // }


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_sorted = [];


        $kiw_temps = $kiw_db->fetch_array("SELECT type, SUM(count) AS count FROM kiwire_report_login_dwell WHERE tenant_id = '{$_SESSION['tenant_id']}' AND (report_date BETWEEN '{$startdate}' AND '{$enddate}') GROUP BY type");

        $type_details = array('60', '900', '1800', '2700', '3600', '7200', '10800', '14400', '18000', '21600', '25200');

        foreach ($kiw_temps as $kiw_temp) {

            $kiw_sorted[switch_type($kiw_temp['type'])] = $kiw_temp['count'];
        }


        ksort($kiw_sorted, SORT_NUMERIC);

        foreach ($type_details as $type) {

            if (!array_key_exists($type, $kiw_sorted)) {
                $kiw_sorted[$type] = 0;
            }
        }


        $kiw_temps = [];

        foreach ($kiw_sorted as $kiw_key => $kiw_value) {

            $kiw_temps[] = ['type' => switch_type_b($kiw_key), 'count' => $kiw_value];
        }


        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temps));
    }
}


function switch_type($kiw_type)
{

    switch ($kiw_type) {

        case "5MIN":
            return 60;
            break;
        case "15MIN":
            return 60 * 15;
            break;
        case "30MIN":
            return 60 * 30;
            break;
        case "45MIN":
            return 60 * 45;
            break;
        case "1HOUR":
            return 60 * 60;
            break;
        case "2HOUR":
            return 3600 * 2;
            break;
        case "3HOUR":
            return 3600 * 3;
            break;
        case "4HOUR":
            return 3600 * 4;
            break;
        case "5HOUR":
            return 3600 * 5;
            break;
        case "6HOUR":
            return 3600 * 6;
            break;
        case "MOREHOUR":
            return 3600 * 7;
            break;
    }
}


function switch_type_b($kiw_type)
{

    switch ($kiw_type) {

        case 60:
            return "5 MIN";
            break;
        case 900:
            return "15 MIN";
            break;
        case 1800:
            return "30 MIN";
            break;
        case 2700:
            return "45 MIN";
            break;
        case 3600:
            return "1 HOUR";
            break;
        case 7200:
            return "2 HOURS";
            break;
        case 10800:
            return "3 HOURS";
            break;
        case 14400:
            return "4 HOURS";
            break;
        case 18000:
            return "5 HOURS";
            break;
        case 21600:
            return "6 HOURS";
            break;
        case 25200:
            return "MORE THAN 6 HOURS";
            break;
    }
}
