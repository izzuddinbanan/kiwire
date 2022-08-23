<?php

$kiw['module'] = "Configuration -> License";
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


$kiw_cloud_key = $kiw_db->escape($_REQUEST['cloud_key']);
$kiw_multi_key = $kiw_db->escape($_REQUEST['multitenant_key']);


if (!in_array($_SESSION['permission'], array("w", "rw"))) {

    die(json_encode(array("status" => "error", "message" => "ERROR: You are not allowed to access this module.", "data" => null)));

}


if (!empty($kiw_cloud_key)){

    if (sync_license_decode($kiw_cloud_key)){


        @file_put_contents(dirname(__FILE__, 3) . "/custom/{$tenant_id}/tenant.license", $kiw_cloud_key);


    }


}


if (!empty($kiw_multi_key)){

    if (sync_license_decode($kiw_multi_key)){


        @file_put_contents(dirname(__FILE__, 3) . "/custom/cloud.license", $kiw_multi_key);


    }


}


echo json_encode(array("status" => "success", "message" => "SUCCESS: Key has been saved.", "data" => null));



