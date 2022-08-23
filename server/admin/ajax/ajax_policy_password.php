<?php

$kiw['module'] = "Policy -> Password";
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

        $data['password_policy']         =    (isset($_POST['password_policy']) ? "y" : "n");

        if ($data['password_policy'] == "y") {

            $data['password_character']   = (isset($_POST['password_character']) ? "y" : "n");
            $data['password_alphabet']    = (isset($_POST['password_alphabet']) ? "y" : "n");
            $data['password_numeral']     = (isset($_POST['password_numeral']) ? "y" : "n");
            $data['password_symbol']      = (isset($_POST['password_symbol']) ? "y" : "n");
            $data['password_days']        = (isset($_POST['password_days']) ? "y" : "n");
            $data['password_reused']      = (isset($_POST['password_reused']) ? "y" : "n");
            $data['password_attempts']    = (isset($_POST['password_attempts']) ? "y" : "n");
            $data['password_first_login'] = (isset($_POST['password_first_login']) ? "y" : "n");
            $data['password_same']        = (isset($_POST['password_same']) ? "y" : "n");
            $data['auto_login']           = (isset($_POST['auto_login']) ? "y" : "n");
            $data['change_passpage']      = (isset($_POST['change_passpage']) ? $_POST['change_passpage'] : "");
            $data['updated_date']         = date('Y-m-d H-i-s');

        } else {

            $data['password_character']     = "n";
            $data['password_alphabet']      = "n";
            $data['password_numeral']       = "n";
            $data['password_symbol']        = "n";
            $data['password_days']          = "n";
            $data['password_reused']        = "n";
            $data['password_attempts']      = "n";
            $data['password_first_login']   = "n";
            $data['password_same']          = "n";
            $data['auto_login']             = "n";
            $data['updated_date']           = date('Y-m-d H-i-s');

        }

        if($kiw_db->update("kiwire_policies", $data, "tenant_id = '$tenant_id'")){

            sync_logger("{$_SESSION['user_name']} updated Password Policy setting", $_SESSION['tenant_id']);
    
            echo json_encode(array("status" => "success", "message" => "SUCCESS: Password Policy setting saved", "data" => null));
        }
        else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }



    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }
}
