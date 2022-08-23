<?php

$kiw['module'] = "Report -> Accounts -> Voucher Availibility";
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

    case "get_by_date": get_by_date(); break;
    case "view_activatedPrepaid": view_activatedPrepaid(); break;
    case "view_freshPrepaid"  : view_freshPrepaid(); break;
    default: echo "ERROR: Wrong implementation";
}


function get_by_date()
{

    global $kiw_db, $tenant_id;


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $startdate = report_date_start($_REQUEST['startdate'], 30);
        $enddate = report_date_end($_REQUEST['enddate'], 1);


        $kiw_temp = $kiw_db->fetch_array("SELECT *,bulk_id, creator, COUNT(*) AS qty, COUNT(date_activate) AS active, remark FROM kiwire_account_auth WHERE ktype = 'voucher' AND (date_create BETWEEN '{$startdate}' AND '{$enddate}') AND tenant_id = '{$tenant_id}' GROUP BY bulk_id LIMIT 1000");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));

       
    }

}


function view_activatedPrepaid()
{

    global $kiw_db, $tenant_id;


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $startdate = report_date_start($_REQUEST['startdate'], 30);
        $enddate = report_date_end($_REQUEST['enddate'], 1);

        $id = $kiw_db->escape($_POST['bulk_id']);

        $kiw_temp = $kiw_db->fetch_array("SELECT * FROM kiwire_account_auth WHERE bulk_id = '{$id}' AND (date_activate IS NOT NULL AND date_activate != '0000-00-00 00:00:00') AND ktype = 'voucher' AND tenant_id = '{$tenant_id}' AND (date_create BETWEEN '{$startdate}' AND '{$enddate}') ");


        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));

    }

}


function view_freshPrepaid()
{

    global $kiw_db, $tenant_id;


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $startdate = report_date_start($_REQUEST['startdate'], 30);
        $enddate = report_date_end($_REQUEST['enddate'], 1); 

        $id = $kiw_db->escape($_POST['bulk_id']);

        $kiw_temp = $kiw_db->fetch_array("SELECT * FROM kiwire_account_auth WHERE bulk_id = '{$id}' AND (date_activate = '0000-00-00 00:00:00' OR date_activate IS NULL) AND ktype = 'voucher' AND tenant_id = '{$tenant_id}' AND (date_create BETWEEN '{$startdate}' AND '{$enddate}') ");


        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));

    }
    
}
