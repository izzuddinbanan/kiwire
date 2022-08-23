<?php

$kiw['module'] = "Login Engine -> Sign up -> Sponsor";
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
        
        $data['domain']                = $_POST['domain'];
        $data['profile']               = $_POST['profile'];
        $data['validity']              = (isset($_POST['validity']) && $_POST['validity'] > 0 ? $_POST['validity'] : "1");
        $data['prefix']                = $_POST['prefix'];

        $data['verification_content']  = $_POST['verification_content'];
        $data['confirmation_content']  = $_POST['confirmation_content'];
        $data['confirmed_page']         = $_POST['confirmed_page'];

        $data['allowed_zone']          = $_POST['allowed_zone'];
        $data['enabled']               = (isset($_POST['enabled']) ? "y" : "n");
        $data['send_notification']     = $_POST['send_notification'];
        $data['updated_date']          = date('Y-m-d H-i-s');

        $data['data'] = $kiw_db->escape(implode(",", $_POST['extra_data']));

        if($kiw_db->update("kiwire_signup_visitor", $data, "tenant_id = '$tenant_id'")){

            sync_logger("{$_SESSION['user_name']} updated Sponsor Sign Up setting", $_SESSION['tenant_id']);
    
            echo json_encode(array("status" => "success", "message" => "SUCCESS: Sponsor Sign Up setting saved", "data" => null));
        }
        else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }



    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}
