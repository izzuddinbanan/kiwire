<?php

$kiw['module'] = "Login Engine -> Notification";
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

        $data['updated_date']                        =   date('Y-m-d H-i-s');

        $data['notification_account_created']        =   $kiw_db->escape($_POST['notification_account_created']);
        $data['notification_password_reset']         =   $kiw_db->escape($_POST['notification_password_reset']);

        $data['error_no_credential']                 =   $kiw_db->escape($_POST['error_no_credential']);
        $data['error_password_verification_failed']  =   $kiw_db->escape($_POST['error_password_verification_failed']);
        $data['error_wrong_otp']                     =   $kiw_db->escape($_POST['error_wrong_otp']);
        $data['error_username_existed']              =   $kiw_db->escape($_POST['error_username_existed']);

        $data['error_future_value_date']             =   $kiw_db->escape($_POST['error_future_value_date']);
        $data['error_account_inactive']              =   $kiw_db->escape($_POST['error_account_inactive']);
        $data['error_wrong_credential']              =   $kiw_db->escape($_POST['error_wrong_credential']);
        $data['error_reached_quota_limit']           =   $kiw_db->escape($_POST['error_reached_quota_limit']);
        $data['error_reached_time_limit']            =   $kiw_db->escape($_POST['error_reached_time_limit']);
        $data['error_max_simultaneous_use']          =   $kiw_db->escape($_POST['error_max_simultaneous_use']);
        $data['error_zone_restriction']              =   $kiw_db->escape($_POST['error_zone_restriction']);
        $data['error_wrong_mac_address']             =   $kiw_db->escape($_POST['error_wrong_mac_address']);
        $data['error_zone_reached_limit']            =   $kiw_db->escape($_POST['error_zone_reached_limit']);

        $data['error_invalid_email_address']         =   $kiw_db->escape($_POST['error_invalid_email_address']);
        $data['error_invalid_phone_number']          =   $kiw_db->escape($_POST['error_invalid_phone_number']);
        $data['error_no_profile_subscribe']          =   $kiw_db->escape($_POST['error_no_profile_subscribe']);

        $data['error_wrong_captcha']                 =   $kiw_db->escape($_POST['error_wrong_captcha']);
        $data['error_country_code']                  =   $kiw_db->escape($_POST['error_country_code']);

        $data['error_device_blacklisted']            =   $kiw_db->escape($_POST['error_device_blacklisted']);

        $data['error_password_expired']              =   $kiw_db->escape($_POST['error_password_expired']);
        $data['error_password_contained_num']        =   $kiw_db->escape($_POST['error_password_contained_num']);
        $data['error_password_contained_alp']        =   $kiw_db->escape($_POST['error_password_contained_alp']);
        $data['error_password_contained_sym']        =   $kiw_db->escape($_POST['error_password_contained_sym']);
        $data['error_password_length']               =   $kiw_db->escape($_POST['error_password_length']);
        $data['error_password_not_same']             =   $kiw_db->escape($_POST['error_password_not_same']);
        $data['error_password_max_attemp']           =   $kiw_db->escape($_POST['error_password_max_attemp']);
        $data['error_pass_username_matched']         =   $kiw_db->escape($_POST['error_pass_username_matched']);
        $data['error_password_reused']               =   $kiw_db->escape($_POST['error_password_reused']);

        $data['error_user_email_mismatched']         =   $kiw_db->escape($_POST['error_user_email_mismatched']);
        $data['error_user_sms_mismatched']           =   $kiw_db->escape($_POST['error_user_sms_mismatched']);
        $data['error_user_not_found']                =   $kiw_db->escape($_POST['error_user_not_found']);
        $data['error_username_cannot_space']         =   $kiw_db->escape($_POST['error_username_cannot_space']);
        $data['error_missing_sponsor_email']         =   $kiw_db->escape($_POST['error_missing_sponsor_email']);
        $data['error_missing_credential_check']      =   $kiw_db->escape($_POST['error_missing_credential_check']);

        $data['error_empty_password']                =   $kiw_db->escape($_POST['error_empty_password']);
        $data['notification_password_changed']       =   $kiw_db->escape($_POST['notification_password_changed']);
        $data['error_inactive_account']              =   $kiw_db->escape($_POST['error_inactive_account']);
        $data['error_ot_reset_grace']                =   $kiw_db->escape($_POST['error_ot_reset_grace']);
        $data['error_password_need_to_change']       =   $kiw_db->escape($_POST['error_password_need_to_change']);
        $data['error_password_change_day']           =   $kiw_db->escape($_POST['error_password_change_day']);
        $data['error_password_too_much_retries']     =   $kiw_db->escape($_POST['error_password_too_much_retries']);

        if($kiw_db->update("kiwire_notification", $data, "tenant_id = '$tenant_id'")){

            sync_logger("{$_SESSION['user_name']} updated Notification Message setting ", $_SESSION['tenant_id']);
    
            echo json_encode(array("status" => "success", "message" => "SUCCESS: Notification Message Saved", "data" => null));
        
        }
        else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
    }
}
