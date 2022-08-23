<?php

$kiw['module'] = "CPanel -> Setting";
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

        $data['enabled']                        = (isset($_POST['enabled']) ? "y" : "n");
        $data['dashboard']                      = (isset($_POST['dashboard']) ? "y" : "n");
        $data['information']                    = (isset($_POST['information']) ? "y" : "n");

        $data['profile']                        = (isset($_POST['profile']) ? "y" : "n");
        $data['statistics']                     = (isset($_POST['statistics']) ? "y" : "n");
        $data['history']                        = (isset($_POST['history']) ? "y" : "n");
        $data['voucher']                        = (isset($_POST['generate_voucher']) ? "y" : "n");

        $data['recharge']                       = (isset($_POST['recharge']) ? "y" : "n");
        $data['register']                       = (isset($_POST['register']) ? "y" : "n");
        $data['login']                          = (isset($_POST['auto_login']) ? "y" : "n");
        $data['allow_inactive']                 = (isset($_POST['allow_inactive']) ? "y" : "n");

        $data['login_type']                     = $_POST['login_type'];
        $data['label_username']                 = $kiw_db->sanitize($_POST['username']);

        $data['label_password']                 = $kiw_db->sanitize($_POST['password']);
        $data['label_tenant']                   = $kiw_db->sanitize($_POST['tenant']);

        $data['label_welcome']                  = $kiw_db->sanitize($_POST['welcome']);
        $data['label_title']                    = $kiw_db->sanitize($_POST['title']);

        $data['label_logout']                   = $kiw_db->sanitize($_POST['logout']);
        $data['label_wrong_credential']         = $kiw_db->sanitize($_POST['wrong_credential']);
        $data['history_month']                  = $kiw_db->sanitize($_POST['history_month']);



        if($kiw_db->update("kiwire_cpanel_template", $data, "tenant_id = '$tenant_id'")){
           
            sync_logger("{$_SESSION['user_name']} updated Policy User Panel", $_SESSION['tenant_id']);
            echo json_encode(array("status" => "success", "message" => "SUCCESS: Policy User Panel has been updated", "data" => null));
       
        }
        else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }

    } else {


        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));


    }


}
