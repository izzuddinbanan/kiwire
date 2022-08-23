<?php

$kiw['module'] = "Configuration -> General";
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

        $data['currency']                      = (!empty($_POST['currency']) ? $kiw_db->sanitize($_POST['currency']) : "");
        $data['gst_percentage']                = (!empty($_POST['gst_percentage']) ? $kiw_db->escape($_POST['gst_percentage']) : "");
        $data['volume_metrics']                = $kiw_db->escape($_POST['volume_metrics']);
        $data['insight_reporting']             = (isset($_POST['insight_reporting']) ? "y" : "n");

        $data['default_language']              = $kiw_db->escape($_POST['default_language']);
        $data['forgot_password_method']        = $kiw_db->escape($_POST['forgot_password_method']);
        $data['forgot_password_template']      = $kiw_db->escape($_POST['forgot_password_template']);
        $data['nps_enabled']                   = (isset($_POST['nps_enabled']) ? "y" : "n");

        $data['require_mfactor']               = (isset($_POST['require_mfactor']) ? "y" : "n");
        $data['nps_template']                  = $kiw_db->escape($_POST['nps_template']);
        $data['ask_web_push']                  = (isset($_POST['ask_web_push']) ? "y" : "n");

        $data['voucher_prefix']                = (!empty($_POST['voucher_prefix']) ? $kiw_db->sanitize($_POST['voucher_prefix']) : "");
        $data['voucher_engine']                = $kiw_db->escape($_POST['voucher_engine']);
        $data['voucher_template']              = $kiw_db->escape($_POST['voucher_template']);
        $data['voucher_avoid_ambiguous']       = (isset($_POST['voucher_avoid_ambiguous']) ? "y" : "n");
        $data['voucher_limit']                 = (!empty($_POST['voucher_limit']) ? $kiw_db->escape($_POST['voucher_limit']) : "");


        $data['campaign_wait_second']          = (!empty($_POST['campaign_wait_second']) ? $kiw_db->sanitize($_POST['campaign_wait_second']) : "");
        $data['campaign_multi_ads']            = (isset($_POST['campaign_multi_ads']) ? "y" : "n");
        $data['campaign_autoplay']             = (isset($_POST['campaign_autoplay']) ? "y" : "n");
        $data['campaign_cookies']              = (isset($_POST['campaign_cookies']) ? "y" : "n");
        $data['campaign_require_verification'] = (isset($_POST['campaign_require_verification']) ? "y" : "n");
        $data['carry_forward_topup']           = (isset($_POST['carry_forward_topup']) ? "y" : "n");

        $data['topup_prefix']                 = (!empty($_POST['topup_prefix']) ? $kiw_db->sanitize($_POST['topup_prefix']) : "");
        $temp["allow_topup"][]                = (isset($_POST['allow_topup_account']) ? "account" : NULL);
        $temp["allow_topup"][]                = (isset($_POST['allow_topup_voucher']) ? "voucher" : NULL);

        $data["allow_topup_to"]               = implode(",", array_filter($temp["allow_topup"]));
        unset($temp["allow_topup"]);

        $data['timezone']     = $kiw_db->escape($_POST['timezone']);
        $data['date_format']  = $kiw_db->escape($_POST['date_format']);

        $_SESSION['timezone']    = $data['timezone'];
        $_SESSION['date_format'] = $data['date_format'];

        $data['reset_acc_and_date_password'] = (isset($_POST['reset_account_with_date_password']) ? "y" : "n");
        

        if($results = $kiw_db->update("kiwire_clouds", $data, "tenant_id = '{$tenant_id}'")) {
            
            
            sync_logger("{$_SESSION['user_name']} updated General Setting", $_SESSION['tenant_id']);
            
            die(json_encode(array("status" => "success", "message" => "SUCCESS: General Setting has been updated", "data" => $results)));
            
        }

        die(json_encode(array("status" => "error", "message" => "ERROR: Please check your input.", "data" => $data)));


    } else {


        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));


    }
}
