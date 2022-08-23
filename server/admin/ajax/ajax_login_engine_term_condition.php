<?php

$kiw['module'] = "Integration -> SMS";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_connection.php";
require_once "../includes/include_general.php";

if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}

$action = $_REQUEST['action'];

switch ($action) {

    case "update": update(); break;
    default: echo "ERROR: Wrong implementation";

}


function update()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));
        
        $data['tnc'] = $_POST['tnc'];
        $data['tnc_alwayson'] = !empty($_POST['tnc_alwayson']) ? $_POST['tnc_alwayson'] : "";
        $data['cdate'] = date('Y-m-d H-i-s');

        if($kiw_db->update("kiwire_tnc", $data, "tenant_id = '$tenant_id'")){

            echo json_encode(array("status" => "success", "message" => "SUCCESS: Term & Condition has been saved", "data" => null));
        }
        else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }


    } else

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
}