<?php

$kiw['module'] = "Integration -> Zapier";
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
    case "generate_key": generate_key(); break;
    default: echo "ERROR: Wrong implementation";

}

function update()

{
    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $data = array();
        
        $data['api_key']         = $_POST['authkey'];
        $data['api_id']          = $_POST['api_id'];
        $data['enabled']         = (isset($_POST['enabled']) ? "y" : "n");
        $data['updated_date']    = 'NOW()';

        if($kiw_db->update("kiwire_zapier_data", $data, " tenant_id = '$tenant_id'")){

            sync_logger("{$_SESSION['user_name']} updated Zapier setting ", $_SESSION['tenant_id']);
    
            echo json_encode(array("status" => "success", "message" => "SUCCESS: Zapier setting saved", "data" => null));
        
        }
        else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }

    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}


function generate_key(){


    $data_encrypted = sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );

    echo json_encode(array("status" => "success", "message" => "", "data" => $data_encrypted));


}