
<?php

$kiw['module'] = "Integration -> Microsoft Account";
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

        csrf($kiw_db->escape($_REQUEST['token']));
        
        $data['microsoft_en']       = (isset($_POST['microsoft_en']) ? "y" : "n");
        $data['365_domain']         = $kiw_db->escape($_POST['365_domain']);
        $data['microsoft_profile']  = $kiw_db->escape($_POST['plan']);
        $data['microsoft_zone']     = $kiw_db->escape($_POST['zone_restriction']);

        if($kiw_db->update("kiwire_int_social", $data, "tenant_id = '$tenant_id'")){
            
            sync_logger("{$_SESSION['user_name']} updated Microsoft Account setting", $_SESSION['tenant_id']);
    
            echo json_encode(array("status" => "success", "message" => "SUCCESS: Microsoft Account setting has been updated", "data" => null));
        
        }
        else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }

    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}
