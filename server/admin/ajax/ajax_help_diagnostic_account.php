<?php

$kiw['module'] = "Help -> User Account Diagnostic";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_general.php";
require_once "../includes/include_connection.php";


if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}

csrf($kiw_db->escape($_REQUEST['token']));

$kiw_user['username'] = $kiw_db->escape($_REQUEST['username']);

if (empty($kiw_user['username'])){

    die(json_encode(array("status" => "failed", "message" => "ERROR: Please provide an account to test.")));

}


$kiw_user['password'] = $kiw_db->escape($_REQUEST['password']);

if (empty($kiw_user['password'])){


    $kiw_temp = $kiw_db->query_first("SELECT password,allowed_zone FROM kiwire_account_auth WHERE tenant_id = '{$_SESSION['tenant_id']}' AND username = '{$kiw_user['username']}' LIMIT 1");

    $kiw_user['password'] = sync_decrypt($kiw_temp['password']);


    if ($kiw_temp['allowed_zone'] != "none"){


        $kiw_temp = $kiw_db->query_first("SELECT zone FROM kiwire_allowed_zone WHERE name = '{$kiw_temp['allowed_zone']}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");

        $kiw_user['zone'] = explode(",", $kiw_temp['zone'])[0];


    }


}


$kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_controller WHERE tenant_id = '{$_SESSION['tenant_id']}' AND device_type = 'controller' LIMIT 1");

if (empty($kiw_temp)){

    die(json_encode(array("status" => "failed", "message" => "ERROR: Please make sure you have at least one controller to test the account.")));

}


$kiw_user['nasid']      = $kiw_temp['unique_id'];
$kiw_user['tenant_id']  = $_SESSION['tenant_id'];
$kiw_user['action']     = "authorize";
$kiw_user['diagnose']   = "true";


$kiw_temp = curl_init();

curl_setopt($kiw_temp, CURLOPT_URL, "http://127.0.0.1:9955");
curl_setopt($kiw_temp, CURLOPT_POST, true);
curl_setopt($kiw_temp, CURLOPT_POSTFIELDS, http_build_query($kiw_user));
curl_setopt($kiw_temp, CURLOPT_RETURNTRANSFER, true);
curl_setopt($kiw_temp, CURLOPT_TIMEOUT, 5);
curl_setopt($kiw_temp, CURLOPT_CONNECTTIMEOUT, 5);


$kiw_login_status = curl_exec($kiw_temp);

curl_close($kiw_temp);


$kiw_login_status = json_decode($kiw_login_status, true);


if ($kiw_login_status) {


    unset($kiw_login_status['control:Cleartext-Password']);

    echo json_encode(array("status" => "success", "message" => "", "data" => $kiw_login_status));


} else {

    echo json_encode(array("status" => "failed", "message" => "ERROR: There is an unexpected error. Please try again.", "data" => null));

}
