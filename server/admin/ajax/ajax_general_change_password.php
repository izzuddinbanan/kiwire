<?php

$kiw['module'] = "General -> Password";
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

$kiw_password = $_REQUEST['password'];

if (!empty($kiw_password)){

    if (strlen($kiw_password) > 7) {

        if ($kiw_password !== $_SESSION['user_name']) {

            $kiw_password = sync_encrypt($kiw_password);


            if ($_SESSION['access_level'] == "superuser") {

                $kiw_tenant = "superuser";

            } else {

                $kiw_tenant = $_SESSION['tenant_id'];

            }


            $kiw_db->query("UPDATE kiwire_admin SET updated_date = NOW(),  password = '{$kiw_password}' WHERE tenant_id = '{$kiw_tenant}' AND username = '{$_SESSION['user_name']}' LIMIT 1");

            echo json_encode(array("status" => "success", "message" => "Password has been changed.", "data" => null));


        } else {

            echo json_encode(array("status" => "error", "message" => "Your password cannot be same as your username.", "data" => null));

        }

    } else {

        echo json_encode(array("status" => "error", "message" => "Your password must at least 8 characters long.", "data" => null));

    }


} else {

    echo json_encode(array("status" => "error", "message" => "Please provide a valid password.", "data" => null));

}
