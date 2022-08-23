<?php


header("Content-Type: application/json");


require_once "../includes/include_general.php";
require_once "../includes/include_session.php";


global $kiw_db;


$action = $_REQUEST['action'];

switch ($action) {

    case "topup_history":
        topup_history($kiw_db);
        break;

    default:
        echo "ERROR: Wrong implementation";

}

function topup_history($kiw_db) {

    
    $kiw_username = $_SESSION['cpanel']['username'];
    $kiw_tenant   = $_SESSION['cpanel']['tenant_id'];

    $kiw_timezone = $_SESSION['timezone'];

    if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


    $kiw_history  = $kiw_db->fetch_array("SELECT * FROM kiwire_topup_code WHERE tenant_id = '{$kiw_tenant}' AND username = '{$kiw_username}'");


    echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_history));


}