<?php

$kiw['module'] = "Policy -> General";
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

function update(){


    global $kiw_db, $tenant_id;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $data['kick_on_simultaneous']           = (isset($_POST['kick_on_simultaneous']) ? "y" : "n");
        $data['kick_on_simultaneous_idle']      = (isset($_POST['kick_on_simultaneous_idle']) ? "y" : "n");
        $data['suspend_exhausted_account']      = (isset($_POST['suspend_exhausted_account']) ? "y" : "n");

        $data['remember_me']                    = (isset($_POST['remember_me']) ? "y" : "n");
        $data['two-factors']                    = (isset($_POST['two-factors']) ? "y" : "n");
        $data['captcha']                        = (isset($_POST['captcha']) ? "y" : "n");
        $data['delete_unverified']              = (isset($_POST['delete_unverified']) ? "y" : "n");
        $data['allow_carry_forward']            = (isset($_POST['allow_carry_forward']) ? "y" : "n");
        $data['security_block']                 = (isset($_POST['security_block']) ? "y" : "n");

        $data['mac_auto_register']              = (isset($_POST['mac_auto_register']) ? "y" : "n");
        $data['mac_max_register']               = $_POST['mac_max_register'];
        $data['mac_security']                   = (isset($_POST['mac_security']) ? "y" : "n");
        $data['cookies_login']                  = (isset($_POST['cookies_login']) ? "y" : "n");

        $data['cookies_login_validity']         = $_POST['cookies_login_validity'];
        $data['mac_auto_login']                 = $_POST['mac_auto_login'];
        $data['mac_auto_login_days']            = $_POST['mac_auto_login_days'];
        $data['mac_auto_same_zone']             = (isset($_POST['mac_auto_same_zone']) ? "y" : "n");
        $data['updated_date']                   = date('Y-m-d H-i-s');

        if($kiw_db->update("kiwire_policies", $data, "tenant_id = '$tenant_id'")){

            sync_logger("{$_SESSION['user_name']} updated Configuration setting", $_SESSION['tenant_id']);
    
            echo json_encode(array("status" => "success", "message" => "SUCCESS: Configuration setting has been updated", "data" => null));
        }
        else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }




    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}
