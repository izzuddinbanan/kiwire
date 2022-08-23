<?php

$kiw['module'] = "General -> Register 2-Factors";
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


$kiw_password = $_REQUEST['password'];


if (!empty($kiw_password)) {


    $kiw_password = sync_encrypt($kiw_password);

    $kiw_username = $_SESSION['user_name'];


    if ($_SESSION['access_level'] == "superuser") $kiw_tenant = "superuser";
    else $kiw_tenant = $_SESSION['tenant_id'];


    $kiw_user = $kiw_db->query_first("SELECT * FROM kiwire_admin WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant}' LIMIT 1");


    if (!empty($kiw_user)){


        if ($kiw_user['password'] == $kiw_password){


            require_once "../../libs/google-authenticator/PHPGangsta/GoogleAuthenticator.php";


            $kiw_authenticator = new PHPGangsta_GoogleAuthenticator();

            $kiw_result = $kiw_authenticator->createSecret(16);


            $kiw_qr = $kiw_authenticator->getQRCodeGoogleUrl("{$kiw_username} @ {$_SERVER['HTTP_HOST']}", $kiw_result);


            $kiw_db->query("UPDATE kiwire_admin SET updated_date = NOW(), mfactor_key = '{$kiw_result}' WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant}' LIMIT 1");

            echo json_encode(array("status" => "success", "message" => null, "data" => array("qr" => $kiw_qr, "key" => $kiw_result)));


        } else {

            echo json_encode(array("status" => "failed", "message" => "Wrong password provided", "data" => null));

        }


    } else {

        echo json_encode(array("status" => "failed", "message" => "Unknown user or invalid tenant", "data" => null));

    }




}