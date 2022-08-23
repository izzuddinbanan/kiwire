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

    // case "get_account_summary":get_account_summary();break;
    // default:echo "ERROR: Wrong implementation";
}


// function get_account_summary()
// {
  
//     global $kiw_db, $tenant_id;

//     $tenant_id = $_SESSION['tenant_id'];


//     if (in_array($_SESSION['permission'], array("r", "rw"))) {


//         $kiw_total_account = [];
        

//         $kiw_raw_datas = $kiw_db->query_first("SELECT COUNT(*) AS prepaidtotal FROM kiwire_account_auth WHERE ktype = 'voucher' AND tenant_id = '{$tenant_id}'");
//         // $total_voucher = $kiw_db->escape($kiw_raw_datas['prepaidtotal']);


//         $kiw_raw_datas = $kiw_db->query_first("SELECT COUNT(*) AS usertotal FROM kiwire_account_auth WHERE ktype = 'account' AND tenant_id = '{$tenant_id}'");
//         // $total_user = $kiw_db->escape($kiw_raw_datas['usertotal']);


//         $kiw_raw_datas = $kiw_db->query_first("SELECT COUNT(*) AS act FROM kiwire_account_auth WHERE status = 'active' AND tenant_id = '{$tenant_id}'");
//         // $total_activated = $kiw_db->escape($kiw_raw_datas['act']);


//         $kiw_raw_datas = $kiw_db->query_first("SELECT COUNT(*) AS exp FROM kiwire_account_auth WHERE status = 'expired' AND tenant_id = '{$tenant_id}'");
//         // $total_expired = $kiw_db->escape($kiw_raw_datas['exp']);


//         $kiw_raw_datas = $kiw_db->query_first("SELECT COUNT(*) AS sus FROM kiwire_account_auth WHERE status = 'suspend' AND tenant_id = '{$tenant_id}'");
//         // $total_user_suspend = $kiw_db->escape($kiw_raw_datas['sus']);


//          $kiw_total_account  = $total_voucher + $total_user;


//         echo json_encode(array("status" => "success", "message" => "", "data" => $omy_temp, "percentage" => $omy_result_percentage ));


//     }



// }
