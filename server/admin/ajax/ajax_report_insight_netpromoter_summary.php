<?php

$kiw['module'] = "Report -> Insight -> Net Promoter Summary";
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

    case "get_by_date"  : get_by_date(); break;
    case "get_by_date_feedback"  : get_by_date_feedback(); break;
    case "positive_count"  : positive_count(); break;
    case "negative_count"  : negative_count(); break;
    case "getSum"  : getSum(); break;

    default: echo "ERROR: Wrong implementation";

}


function getSum()
{

    global $kiw_db, $tenant_id;


    $startdate = report_date_start($_REQUEST['startdate'], 30);
    $enddate = report_date_end($_REQUEST['enddate'], 1);


    $nps_score = "SELECT score_type FROM kiwire_nps_score WHERE DATE(created_at) BETWEEN '{$startdate}' AND '{$enddate}' AND status = 'responded' AND tenant_id = '{$tenant_id}'";


    $nps_score = $kiw_db->fetch_array($nps_score);

    $nps_count = array_count_values(array_column($nps_score, 'score_type'));


    if (empty($nps_count['promoter'])) $nps_count['promoter'] = 0;

    if (empty($nps_count['passive'])) $nps_count['passive'] = 0;

    if (empty($nps_count['detractor'])) $nps_count['detractor'] = 0;

    echo json_encode(Array(

        "positive" => $nps_count['promoter'] + $nps_count['passive'],
        "negative" => $nps_count['detractor']

    ));
}






function get_by_date() {

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        $startdate = report_date_start($_REQUEST['startdate'], 30);
        $enddate = report_date_end($_REQUEST['enddate'], 1);

        $kiw_temp = $kiw_db->fetch_array("SELECT * FROM kiwire_nps_score WHERE DATE(created_at) BETWEEN '{$startdate}' AND '{$enddate}' AND status = 'responded' AND tenant_id = '{$tenant_id}'");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));

    }
}


function get_by_date_feedback() {

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        $startdate = report_date_start($_REQUEST['startdate'], 30);
        $enddate = report_date_end($_REQUEST['enddate'], 1);

        $kiw_temp = $kiw_db->fetch_array("SELECT score_type, COUNT(score_type) AS count_score_type FROM kiwire_nps_score WHERE DATE(created_at) BETWEEN '{$startdate}' AND '{$enddate}' AND status = 'responded' AND tenant_id = '{$tenant_id}' GROUP BY score_type");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));

    }
}


function positive_count() {

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        $startdate = report_date_start($_REQUEST['startdate'], 30);
        $enddate = report_date_end($_REQUEST['enddate'], 1);

        $kiw_temp = $kiw_db->fetch_array("SELECT COUNT(score_type) AS positive_count FROM kiwire_nps_score WHERE DATE(created_at) BETWEEN '{$startdate}' AND '{$enddate}' AND tenant_id = '{$tenant_id}' AND score_type != 'detractor'");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));

    }
}


function negative_count() {

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        $startdate = report_date_start($_REQUEST['startdate'], 30);
        $enddate = report_date_end($_REQUEST['enddate'], 1);

        $kiw_temp = $kiw_db->fetch_array("SELECT COUNT(score_type) AS negative_count FROM kiwire_nps_score WHERE DATE(created_at) BETWEEN '{$startdate}' AND '{$enddate}' AND tenant_id = '{$tenant_id}' AND score_type = 'detractor'");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));

    }
}
