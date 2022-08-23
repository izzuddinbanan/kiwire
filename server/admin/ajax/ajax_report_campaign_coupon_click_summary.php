<?php

$kiw['module'] = "Report -> Coupon -> View Report";
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

    case "get_by_date": get_by_date(); break;
    case "view_couponClickSummaryHourly": view_couponClickSummaryHourly(); break;
    default: echo "ERROR: Wrong implementation";

}


function get_by_date()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        $startdate = report_date_start($_REQUEST['startdate'], 30);
        $enddate = report_date_end($_REQUEST['enddate'], 1);

        $timezone = $_SESSION['timezone'];
        if(empty($timezone)) $timezone = "Asia/Kuala_Lumpur";

        $coupon =  $kiw_db->escape($_REQUEST['coupon']);

        // $kiw_temp = $kiw_db->fetch_array("SELECT COUNT(coupon_id) AS total, COUNT(DISTINCT(mac)) AS `unique`
        //                                   FROM kiwire_coupon_click
        //                                   WHERE datetime_impression BETWEEN '{$startdate}' AND '{$enddate}' AND coupon_id = '$coupon' AND tenant_id = '{$tenant_id}'");


        $kiw_temp = $kiw_db->fetch_array("SELECT * FROM kiwire_report_campaign WHERE coupon_id = '$coupon' AND (report_date BETWEEN '{$startdate}' AND '{$enddate}') AND tenant_id = '{$tenant_id}' ORDER BY updated_date");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));

    }

}


function view_couponClickSummaryHourly()
{
    global $kiw_db, $tenant_id;

    $startdate = sync_toutctime(date("Y-m-d H:i:s", strtotime($_REQUEST['time'])));
    $enddate = date("Y-m-d H:i:s", strtotime("{$startdate} + 1 Day -1 Second"));

    $timezone = $_SESSION['timezone'];
    if(empty($timezone)) $timezone = "Asia/Kuala_Lumpur";
    $coupon = $kiw_db->escape($_GET['c']);

    if (in_array($_SESSION['permission'], array("r", "rw"))) {

         $kiw_temp = $kiw_db->fetch_array("SELECT COUNT(*) AS qty, COUNT(DISTINCT(mac)) AS qty2
                                           FROM kiwire_coupon_view
                                           WHERE tenant_id = '{$tenant_id}' AND coupon_id = '$coupon' AND (datetime_views BETWEEN '{$startdate}' AND '$enddate')");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));

    }

}
