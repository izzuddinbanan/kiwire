<?php

$kiw['module'] = "Integration -> Mail";
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
            
            $data['madmini_email']    = $kiw_db->escape($_POST['madmini_email']);
            $data['madmini_api']      = $kiw_db->escape($_POST['madmini_api']);
            $data['madmini_list']     = $kiw_db->escape($_POST['madmini_list']);
            $data['madmini_en']       = $kiw_db->escape($_POST['madmini_en']);

            $data['mailchimp_api']    = $kiw_db->escape($_POST['mailchimp_api']);
            $data['mailchimp_lid']    = $kiw_db->escape($_POST['mailchimp_lid']);
            $data['mailchimp_en']     = $kiw_db->escape($_POST['mailchimp_en']);
 
            $data['updated_date']     = date('Y-m-d H-i-s');

           if($kiw_db->update("kiwire_int_marketing_email", $data, "tenant_id = '{$tenant_id}'")){

               sync_logger("{$_SESSION['user_name']} updated Marketing Email setting ", $_SESSION['tenant_id']);
           
               echo json_encode(array("status" => "success", "message" => "SUCCESS: Marketing Email setting updated", "data" => null));
           
            }
            else {

                echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

            }
        
        } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }
}
