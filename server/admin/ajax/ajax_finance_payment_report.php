<?php

$kiw['module'] = "Finance -> E-Payment Transaction";
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
    case "calculate_total"  : calculate_total(); break;
    default: echo "ERROR: Wrong implementation";
}


function get_by_date()
{

    global $kiw_db;


    $startdate = report_date_start($_REQUEST['startdate'], 30);

    $enddate = report_date_end($_REQUEST['enddate'], 1);


    if (isset($_REQUEST['payment_type']) && !empty($_REQUEST['payment_type'])){

        $payment_type = " AND payment_type = '" . $kiw_db->escape($_REQUEST['payment_type']) . "'";

    } else $payment_type = "";



    $timezone = $_SESSION['timezone'];

    if (empty($timezone)) $timezone = "Asia/Kuala_Lumpur";



    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        // $kiw_temp = $kiw_db->fetch_array("SELECT *, DATE(CONVERT_TZ(updated_date, 'UTC', '{$timezone}')) AS xreport_date FROM kiwire_payment_trx WHERE tenant_id = '{$_SESSION['tenant_id']}' AND (updated_date BETWEEN '{$startdate}' AND '{$enddate}') {$payment_type} GROUP BY xreport_date,payment_type");

        $kiw_temp = $kiw_db->fetch_array("SELECT *, DATE(CONVERT_TZ(updated_date, 'UTC', '{$timezone}')) AS xreport_date FROM kiwire_payment_trx WHERE tenant_id = '{$_SESSION['tenant_id']}' AND (updated_date BETWEEN '{$startdate}' AND '{$enddate}') {$payment_type}");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));
   

    }


}


function calculate_total()
{

    global $kiw_db;


    $startdate = report_date_start($_REQUEST['startdate'], 30);

    $enddate = report_date_end($_REQUEST['enddate'], 1);

    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $timezone = $_SESSION['timezone'];

        if (empty($timezone)) $timezone = "Asia/Kuala_Lumpur";


        if (isset($_REQUEST['payment_type']) && !empty($_REQUEST['payment_type'])){

            $payment_type = " AND payment_type = '" . $kiw_db->escape($_REQUEST['payment_type']) . "'";
    
        } else $payment_type = "";



        $kiw_temp = $kiw_db->query_first("SELECT COUNT(*) AS total_transaction, COALESCE(SUM(amount), 0) AS total_amount FROM kiwire_payment_trx WHERE tenant_id = '{$_SESSION['tenant_id']}' AND (updated_date BETWEEN '{$startdate}' AND '{$enddate}') {$payment_type}");

        
        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }


}