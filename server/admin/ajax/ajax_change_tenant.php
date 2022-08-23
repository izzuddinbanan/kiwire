<?php

$kiw['module'] = "General -> Change Tenant";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_connection.php";
require_once "../includes/include_general.php";


$kiw_tenant_id = $kiw_db->escape(preg_replace('/[^A-Za-z0-9_-]/', '', $_REQUEST['tenant_id']));


if (strlen($kiw_tenant_id) > 0) {


    $_SESSION['tenant_id'] = $kiw_tenant_id;


    $kiw_tenant_data = $kiw_db->query_first("SELECT * FROM kiwire_clouds WHERE tenant_id = '{$kiw_tenant_id}' LIMIT 1");

    $_SESSION['company_name']   = $kiw_tenant_data['name'];
    $_SESSION['metrics']        = (empty($kiw_tenant_data['volume_metrics']) ? "Gb" : $kiw_tenant_data['volume_metrics']);
    $_SESSION['timezone']       = (empty($kiw_tenant_data['timezone']) ? "Asia/Kuala_Lumpur" : $kiw_tenant_data['timezone']);


    sync_logger("User: {$_SESSION['user_name']} change tenant to system [ {$_SERVER['REMOTE_ADDR']} ]", $kiw_tenant_data['tenant_id']);

    echo json_encode(array("status" => "success", "message" => null, "data" => null));


} else {

    echo json_encode(array("status" => "error", "message" => "Error: Invalid tenant identity", "data" => null));

}