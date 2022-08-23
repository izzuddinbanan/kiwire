<?php

$kiw['module'] = "Login Engine -> Login Checks";
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

if (isset($_REQUEST['functions_list'])){


    $kiw_functions = $kiw_db->escape($_REQUEST['functions_list']);


    // mandatory for function password & concurrent user check, otherwise user wont be able to login


    if (strpos($kiw_functions, "check_password") == false) {

        $kiw_functions = "check_concurrent_user,check_password," . $kiw_functions;

    } else {

        $kiw_functions = "check_concurrent_user," . $kiw_functions;

    }


    // check for device reporting

    if (strpos($kiw_functions, "reporting_process") == false) {

        $kiw_functions .= ",reporting_process";

    }


    $kiw_db->query("UPDATE kiwire_clouds SET updated_date = NOW(), check_arrangement_login = '{$kiw_functions}' WHERE tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");


    sync_logger("{$_SESSION['user_name']} updated Login Check setting ", $_SESSION['tenant_id']);

    echo json_encode(array("status" => "success", "message" => "", "data" => null));


}
