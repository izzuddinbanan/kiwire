<?php

$kiw['module'] = "Integration -> LBS";
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

$action = $_REQUEST['action'];

switch ($action) {

    case "update": update(); break;
    default: echo "ERROR: Wrong implementation";
    
}


function update()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        $data['status']         = (isset($_POST['status']) ? "y" : "n");
        $data['api_id']         = (!empty($_POST['api_id']) ? $_POST['api_id'] : "");
        $data['api_secret']     = (!empty($_POST['api_secret']) ? $_POST['api_secret'] : "");
        $data['updated_date']   = date('Y-m-d H-i-s');

        if($kiw_db->update("kiwire_omaya", $data, " tenant_id = '$tenant_id'")){
        
            sync_logger("{$_SESSION['user_name']} updated Omaya connection", $_SESSION['tenant_id']);
            
            echo json_encode(array("status" => "success", "message" => "SUCCESS: Omaya connection has been updated", "data" => null));
        }
        else {
                
            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }

    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
    }
}
