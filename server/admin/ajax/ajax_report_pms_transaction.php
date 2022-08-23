<?php

$kiw['module'] = "Report -> Monitoring -> Controller Session";
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
        get_all($kiw_db);
        break;
    default:
        echo "ERROR: Wrong implementation";

}


function get_all($kiw_db)
{


    global $kiw_db;


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_timezone = $_SESSION['timezone'];

        if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


        $kiw_start_date = report_date_start("", 7);

        $kiw_end_date = report_date_end("", 0);


        $kiw_data = $kiw_db->fetch_array("SELECT CONVERT_TZ(check_in_date, 'UTC', '{$kiw_timezone}') AS check_in_date, CONVERT_TZ(check_out_date, 'UTC', '{$kiw_timezone}') AS check_out_date, room, first_name, last_name, status, vip_code FROM kiwire_int_pms_transaction WHERE tenant_id = '{$_SESSION['tenant_id']}' AND (check_in_date BETWEEN '{$kiw_start_date}' AND '{$kiw_end_date}') ORDER BY check_in_date DESC LIMIT 5000");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_data));


    } else {

        echo json_encode(array("status" => "error", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}