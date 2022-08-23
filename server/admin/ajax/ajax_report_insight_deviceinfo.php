<?php

$kiw['module'] = "Report -> Insight -> User Device Info";
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


$kiw_tenant_req = $kiw_db->escape($_REQUEST['tenant_id']);


if ($_SESSION['access_level'] == "superuser") {


    if (!empty($_SESSION['tenant_allowed'])){


        $kiw_tenants = explode(",", $_SESSION['tenant_allowed']);


        if (!empty($kiw_tenant_req) && in_array($kiw_tenant_req, $kiw_tenants)){

            $kiw_tenant_id = "AND tenant_id = '{$kiw_tenant_req}'";

        } else $kiw_tenant_id = "AND tenant_id IN ('" . implode("','", $kiw_tenants) . "')";


    } else {


        if (!empty($kiw_tenant_req)){

            $kiw_tenant_id = "AND tenant_id = '{$kiw_tenant_req}'";

        } else $kiw_tenant_id = "";


    }


} else $kiw_tenant_id = "AND tenant_id = '{$_SESSION['tenant_id']}'";


// get the actual data

if (in_array($_SESSION['permission'], array("r", "rw"))) {


    $startdate = report_date_start($_REQUEST['startdate'], 30);
    $enddate = report_date_end($_REQUEST['enddate'], 1);


    $kiw_result['class'] = $kiw_db->fetch_array("SELECT value, SUM(count) AS count FROM kiwire_report_login_device WHERE (report_date BETWEEN '{$startdate}' AND '{$enddate}') AND info = 'class' {$kiw_tenant_id} GROUP BY value");
    $kiw_result['brand'] = $kiw_db->fetch_array("SELECT value, SUM(count) AS count FROM kiwire_report_login_device WHERE (report_date BETWEEN '{$startdate}' AND '{$enddate}') AND info = 'brand' {$kiw_tenant_id} GROUP BY value");


    echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_result));


}