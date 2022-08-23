<?php

$kiw['module'] = "Finance -> Print Prepaid Slip";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_connection.php";

if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}

$action = $_REQUEST['action'];

switch ($action) {

    case "get_all": get_data(); break;
    default: echo "ERROR: Wrong implementation";
}


function get_data()
{


    global $kiw_db, $tenant_id;


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_timezone = $_SESSION['timezone'];

        if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


        $kiw_temp = $kiw_db->fetch_array("SELECT creator, remark, bulk_id, count(bulk_id) as qty, CONVERT_TZ(date_create, 'UTC', '{$kiw_timezone}') AS date_create, CONVERT_TZ(date_expiry, 'UTC', '{$kiw_timezone}') AS date_expiry FROM kiwire_account_auth WHERE ktype = 'voucher' AND bulk_id <> '' AND tenant_id = '{$tenant_id}' GROUP BY bulk_id");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }

}
