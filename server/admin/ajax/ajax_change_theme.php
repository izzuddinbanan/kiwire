<?php

$kiw['module'] = "General -> Change Theme";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_connection.php";


if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));
    
}


$kiw_temp = $kiw_db->escape($_POST['theme']);


if ($_SESSION['access_level'] == "superuser") $kiw_tenant_id = "superuser";
else $kiw_tenant_id = $_SESSION['tenant_id'];

$kiw_db->query("UPDATE kiwire_admin SET updated_date = NOW(),  theme = '{$kiw_temp}' WHERE username = '{$_SESSION['user_name']}' AND tenant_id = '{$kiw_tenant_id}' LIMIT 1");

$_SESSION['theme'] = $kiw_temp;


echo json_encode(array("status" => "success", "message" => "Success: you selected theme has been saved.", "data" => null));

