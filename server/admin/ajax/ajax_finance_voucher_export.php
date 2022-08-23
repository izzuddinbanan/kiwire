<?php

$kiw['module'] = "Finance -> Print Prepaid Slip";
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


$kiw_voucher = $_POST['voucher_id'];


if (!empty($kiw_voucher)){


    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        $kiw_timezone = $_SESSION['timezone'];

        if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


        $kiw_voucher = $kiw_db->fetch_array("SELECT username,profile_subs,price,CONVERT_TZ(date_create, 'UTC', '{$kiw_timezone}') AS date_create,CONVERT_TZ(date_expiry, 'UTC', '{$kiw_timezone}') AS date_expiry FROM kiwire_account_auth WHERE ktype = 'voucher' AND bulk_id = '{$kiw_voucher}' AND tenant_id = '{$_SESSION['tenant_id']}'");

        echo json_encode(array("status" => "success", "message" => "", "data" => $kiw_voucher));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}