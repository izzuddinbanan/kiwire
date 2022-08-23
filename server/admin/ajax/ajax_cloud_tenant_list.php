<?php

$kiw['module'] = "Cloud -> Manage Client";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_connection.php";
require_once "../includes/include_general.php";

if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));
}


$action = $_REQUEST['action'];

switch ($action) {

    case "create": create(); break;
    case "delete": delete(); break;
    case "get_all": get_data(); break;
    case "get_update": get_update(); break;
    case "edit_single_data": edit_single_data(); break;
    default: echo "ERROR: Wrong implementation";

}


function create()
{
    global $kiw_db;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));
        $kiw_temp['tenant_id'] = $kiw_db->escape(preg_replace('/[^A-Za-z0-9_-]/', '', $_REQUEST['tenant_id']));


        if (strlen($kiw_temp['tenant_id']) > 4) {


            if (in_array(strtolower($kiw_temp['tenant_id']), array("superuser", "general", "system", "nms", "log", "api"))) {


                echo json_encode(array("status" => "failed", "message" => "ERROR: Invalid tenant id [ {$kiw_temp['tenant_id']} ]. Please use other tenant id.", "data" => null));

                die();


            }


            $kiw_existed = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_clouds WHERE tenant_id = '{$kiw_temp['tenant_id']}'");

            if ($kiw_existed['kcount'] == 0) {


                // create admin

                $kiw_temp['username'] = $kiw_db->sanitize($_REQUEST['admin_id']);
                $kiw_temp['password'] = sync_encrypt($_REQUEST['admin_pass']);
                $kiw_temp['email'] = $kiw_db->sanitize($_REQUEST['admin_email']);
                $kiw_temp['fullname'] = "Administrator";
                $kiw_temp['monitor'] = "y";


                $kiw_temp['temp_pass'] = "y";
                $kiw_temp['groupname'] = "operator";
                $kiw_temp['permission'] = "rw";


                $kiw_db->insert("kiwire_admin", $kiw_temp);

                $kiw_tenant_id = $kiw_temp['tenant_id'];

                unset($kiw_temp);


                // create admin group access

                $kiw_db->query("INSERT INTO kiwire_admin_group (SELECT NULL, 'operator', moduleid, '{$kiw_tenant_id}', NOW() FROM kiwire_moduleid ORDER BY moduleid ASC)");


                // create clouds

                $kiw_temp['tenant_id'] = $kiw_tenant_id;
                $kiw_temp['name'] = $kiw_db->sanitize($_REQUEST['client_name']);

                $kiw_temp['voucher_prefix'] = strtoupper(substr(md5(time()), 6, 3) . "_");
                $kiw_temp['voucher_limit'] = 5;
                $kiw_temp['campaign_wait_second'] = 15;
                $kiw_temp['campaign_multi_ads'] = "y";
                $kiw_temp['campaign_require_verification'] = "y";

                $kiw_temp['currency'] = "MYR";
                $kiw_temp['timezone'] = "Asia/Kuala_Lumpur";

                $kiw_temp['concurrent_user'] = $kiw_db->sanitize($_REQUEST['simultaneous']) == "" ? '500' : $kiw_db->sanitize($_REQUEST['simultaneous']);
                
                $kiw_temp['check_arrangement_login'] = "check_concurrent_user,check_active,check_password,check_allow_simultaneous,check_allow_quota,check_allow_credit,check_zone_limit,check_allow_mac,check_allow_zone,check_register_mac,activate_voucher_account,reporting_process";
                
                $kiw_temp['ip_address'] = $kiw_db->sanitize($_REQUEST['ip_address']);

                $kiw_db->insert("kiwire_clouds", $kiw_temp);

                unset($kiw_temp);


                // create custom directory

                if (file_exists(dirname(__FILE__, 3) . "/custom/{$kiw_tenant_id}/") == false) {

                    mkdir(dirname(__FILE__, 3) . "/custom/{$kiw_tenant_id}/", 0755, true);

                }

                //create custom stylesheet directory
                if (file_exists(dirname(__FILE__, 3) . "/custom/{$kiw_tenant_id}/stylesheets") == false) {

                    mkdir(dirname(__FILE__, 3) . "/custom/{$kiw_tenant_id}/stylesheets", 0755, true);

                }

                //create custom login images directory
                if (file_exists(dirname(__FILE__, 3) . "/custom/{$kiw_tenant_id}/stylesheets/images") == false) {

                    mkdir(dirname(__FILE__, 3) . "/custom/{$kiw_tenant_id}/stylesheets/images", 0755, true);

                }


                // insert license key if available

                if (strlen($_REQUEST['lkey']) > 0) {

                    file_put_contents(dirname(__FILE__, 3) . "/custom/{$kiw_tenant_id}/tenant.license", $_REQUEST['lkey']);

                }


                // create profile

                $kiw_temp['tenant_id'] = $kiw_tenant_id;
                $kiw_temp['name'] = "Temp_Access";
                $kiw_temp['price'] = 0;
                $kiw_temp['type'] = "countdown";

                $kiw_attribute["control:Max-All-Session"] = 600;
                $kiw_attribute["control:Simultaneous-Use"] = 1;
                $kiw_attribute["control:Kiwire-Total-Quota"] = 100;
                $kiw_attribute["reply:Acct-Interim-Interval"] = 300;
                $kiw_attribute["reply:Idle-Timeout"] = 300;
                $kiw_attribute["reply:WISPr-Bandwidth-Max-Down"] = 1048576;
                $kiw_attribute["reply:WISPr-Bandwidth-Max-Up"] = 1048576;
                $kiw_attribute["reply:WISPr-Bandwidth-Min-Up"] = 524288;
                $kiw_attribute["reply:WISPr-Bandwidth-Min-Down"] = 524288;

                $kiw_temp['attribute'] = json_encode($kiw_attribute);

                unset($kiw_attribute);

                $kiw_db->insert("kiwire_profiles", $kiw_temp);

                unset($kiw_temp);


                // todo: need to inject default pages for login

                $kiw_db->insert("kiwire_notification", array("tenant_id" => $kiw_tenant_id));


                $kiw_notification = array();

                $kiw_notification['notification_account_created']   = "Your account has been created.";
                $kiw_notification['notification_password_reset']    = "Your password has been reset. Please check your Email Inbox / SMS.";
                $kiw_notification['error_no_credential']            = "Please provide credential to login.";
                $kiw_notification['error_wrong_otp']                = "You have provided wrong OTP code.";
                $kiw_notification['error_username_existed']         = "This username already existed in the system.";
                $kiw_notification['error_future_value_date']        = "Your account can only login after {{value_date}}";
                $kiw_notification['error_account_inactive']         = "This account is not active.";
                $kiw_notification['error_wrong_credential']         = "You have provided wrong username or password.";
                $kiw_notification['error_reached_quota_limit']      = "You have reached quota limit.";
                $kiw_notification['error_reached_time_limit']       = "You have reached time limit.";

                $kiw_db->update("kiwire_notification", $kiw_notification, "tenant_id = '{$kiw_tenant_id}'");

                unset($kiw_notification);

                $kiw_notification = array();


                $kiw_notification['error_max_simultaneous_use']     = "You have reached max simultaneous use limit.";
                $kiw_notification['error_zone_restriction']         = "You are not allowed to login from this zone.";
                $kiw_notification['error_wrong_mac_address']        = "You are not allowed to login using this device.";
                $kiw_notification['error_zone_reached_limit']       = "This zone already reached maximum limit of login.";
                $kiw_notification['error_invalid_email_address']    = "You have provided invalid email address.";
                $kiw_notification['error_invalid_phone_number']     = "You have provided invalid phone number.";
                $kiw_notification['error_no_profile_subscribe']     = "This account has not subscribe to any profile.";
                $kiw_notification['error_wrong_captcha']            = "You have provided wrong captcha code.";
                $kiw_notification['error_country_code']             = "You are not allowed to register using this country code.";
                $kiw_notification['error_device_blacklisted']       = "This device has been blacklisted.";

                $kiw_db->update("kiwire_notification", $kiw_notification, "tenant_id = '{$kiw_tenant_id}'");

                unset($kiw_notification);

                $kiw_notification = array();


                $kiw_notification['error_password_expired']         = "Your password already expired. Please change immediately.";
                $kiw_notification['error_password_contained_num']   = "Your password must contain atleast a number.";
                $kiw_notification['error_password_contained_alp']   = "Your password must contain atleast a character.";
                $kiw_notification['error_password_contained_sym']   = "Your password must contain atleast a symbol.";
                $kiw_notification['error_password_length']          = "Your password must be atleast {{character_count}} character long.";
                $kiw_notification['error_password_not_same']        = "You are not allowed to use same password as previous.";
                $kiw_notification['error_password_max_attemp']      = "You have reached max login attempts.";
                $kiw_notification['error_pass_username_matched']    = "You are not allowed to use username as your password.";
                $kiw_notification['error_password_reused']          = "You are not allowed to use previous password.";
                $kiw_notification['error_user_email_mismatched']    = "This email address are not belong to the account.";

                $kiw_db->update("kiwire_notification", $kiw_notification, "tenant_id = '{$kiw_tenant_id}'");

                unset($kiw_notification);

                $kiw_notification = array();


                $kiw_notification['error_user_sms_mismatched']      = "This phone number not belong to the account.";
                $kiw_notification['error_user_not_found']           = "We unable to locate this account. Please try again.";
                $kiw_notification['error_username_cannot_space']    = "Username cannot have any space.";
                $kiw_notification['error_missing_sponsor_email']    = "Please provide your sponsor email address.";
                $kiw_notification['error_empty_password']           = "Please provide a valid password.";
                $kiw_notification['notification_password_changed']  = "Your password has been changed. Please login using new password.";
                $kiw_notification['error_inactive_account']         = "Your account already inactive.";
                $kiw_notification['error_ot_reset_grace']           = "You need to wait another {{remaining_minute}} minutes before you are allowed to login.";
                $kiw_notification['error_password_need_to_change']  = "You need to change your password upon the first login.";
                $kiw_notification['error_password_change_day']      = "You need to change your password every 90 days.";
                $kiw_notification['error_missing_credential_check']     = "Please provide your account ID.";
                $kiw_notification['error_password_verification_failed'] = "You have entered wrong password or verfication.";
                $kiw_notification['error_password_too_much_retries']    = "Too many retries. Your account has been suspended.";

                $kiw_db->update("kiwire_notification", $kiw_notification, "tenant_id = '{$kiw_tenant_id}'");

                unset($kiw_notification);



                if (isset($_REQUEST['send_email'])){


                    $kiw_email_content = @file_get_contents(dirname(__FILE__, 3) . "/user/templates/email-tenant.html");


                    if (!empty($kiw_email_content)){


                        // get the first row as subject

                        $kiw_subject = explode(PHP_EOL, $kiw_email_content)[0];

                        $kiw_subject = trim($kiw_subject);


                        $kiw_email_content = preg_replace('/^.+\n/', '', $kiw_email_content);


                        $kiw_email = array();


                        $kiw_email['content'] = htmlentities(str_replace(array('{{username}}', '{{password}}', '{{tenant_id}}'), array($_REQUEST['admin_id'], $_REQUEST['admin_pass'], $kiw_tenant_id), $kiw_email_content));


                        $kiw_email['action']        = "send_email";
                        $kiw_email['tenant_id']     = "superuser";
                        $kiw_email['email_address'] = $kiw_db->escape($_REQUEST['admin_email']);
                        $kiw_email['subject']       = $kiw_subject;
                        $kiw_email['name']          = $kiw_db->escape($_REQUEST['admin_email']);


                        $kiw_connection = curl_init();


                        curl_setopt($kiw_connection, CURLOPT_URL, "http://127.0.0.1:9956");
                        curl_setopt($kiw_connection, CURLOPT_POST, true);
                        curl_setopt($kiw_connection, CURLOPT_POSTFIELDS, http_build_query($kiw_email));
                        curl_setopt($kiw_connection, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($kiw_connection, CURLOPT_TIMEOUT, 5);
                        curl_setopt($kiw_connection, CURLOPT_CONNECTTIMEOUT, 5);


                        curl_exec($kiw_connection);
                        curl_close($kiw_connection);


                    }


                }


                sync_logger("{$_SESSION['user_name']} create tenant {$kiw_tenant_id}", "system");

                echo json_encode(array("status" => "success", "message" => "SUCCESS: Tenant ID [ {$kiw_tenant_id} ] has been created", "data" => null));


            } else {


                echo json_encode(array("status" => "failed", "message" => "ERROR: Tenant ID already existed", "data" => null));


            }


        } else {


            echo json_encode(array("status" => "failed", "message" => "ERROR: Tenant name need to be at least 5 characters", "data" => null));


        }


    } else {


        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));


    }


}


function delete()
{
    global $kiw_db;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));
        $kiw_tenant_id = $kiw_db->escape(preg_replace('/[^A-Za-z0-9_-]/', '', $_REQUEST['id']));


        if ($kiw_tenant_id == "default"){

            die(json_encode(array("status" => "error", "message" => "ERROR: Tenant default cannot be deleted", "data" => null)));

        }


        if (strlen($kiw_tenant_id) > 0) {


            $kiw_tables = $kiw_db->fetch_array("SELECT DISTINCT(TABLE_NAME) AS TABLE_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'tenant_id'");


            foreach ($kiw_tables as $kiw_table) {

                if (strpos($kiw_table['TABLE_NAME'], "_report_") == false && strpos($kiw_table['TABLE_NAME'], "_sessions_") == false) {

                    $kiw_db->query("DELETE FROM {$kiw_table['TABLE_NAME']} WHERE tenant_id = '{$kiw_tenant_id}'");

                }

            }


            if (!empty($kiw_tenant_id)) {

                exec("rm -rf " . dirname(__FILE__, 3) . "/custom/{$kiw_tenant_id}");

                exec("rm -rf " . dirname(__FILE__, 4) . "/logs/{$kiw_tenant_id}");

            }


            sync_logger("{$_SESSION['user_name']} deleted role {$kiw_tenant_id}", $_SESSION['tenant_id']);

            echo json_encode(array("status" => "success", "message" => "SUCCESS: Tenant ID [ {$kiw_tenant_id} ] has been deleted", "data" => null));


        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

        }


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}


function get_data()
{

    global $kiw_db;


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_result = array();


        $kiw_tenants = $kiw_db->fetch_array("SELECT * FROM kiwire_clouds");


        $kiw_cloud_expiry = @file_get_contents(dirname(__FILE__, 3) . "/custom/cloud.license");

        if (strlen($kiw_cloud_expiry) > 0) $kiw_cloud_expiry = sync_license_decode($kiw_cloud_expiry);


        foreach ($kiw_tenants as $kiw_tenant){


            $kiw_temp['tenant_id']   = $kiw_tenant['tenant_id'];
            $kiw_temp['tenant_name'] = $kiw_tenant['name'];


            $kiw_admin = $kiw_db->query_first("SELECT username FROM kiwire_admin WHERE tenant_id = '{$kiw_tenant['tenant_id']}' ORDER BY id ASC LIMIT 1");

            $kiw_temp['admin'] = $kiw_admin['username'];


            $kiw_expire = @file_get_contents(dirname(__FILE__, 3) . "/custom/{$kiw_temp['tenant_id']}/tenant.license");

            if (strlen($kiw_expire) > 0) $kiw_expire = sync_license_decode($kiw_expire);


            if ($kiw_expire['expire_on']) {

                $kiw_temp['expiry_date'] = date("d-m-Y", $kiw_expire['expire_on']);

            } elseif ($kiw_cloud_expiry['expiry_date']){

                $kiw_temp['expiry_date'] = date("d-m-Y", $kiw_expire['expire_on']);

            } else {

                $kiw_temp['expiry_date'] = "Unlicensed";

            }


            $kiw_result[] = $kiw_temp;

            unset($kiw_temp);


        }


        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_result));


    }


}


function get_update()
{

    global $kiw_db;


    $kiw_id = $kiw_db->escape($_REQUEST['id']);


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_tenant = $kiw_db->query_first("SELECT * FROM kiwire_clouds WHERE tenant_id = '{$kiw_id}' LIMIT 1");


        $kiw_temp['tenant_id']    = $kiw_tenant['tenant_id'];
        $kiw_temp['ip_address']   = $kiw_tenant['ip_address'];
        $kiw_temp['client_name']  = $kiw_tenant['name'];
        $kiw_temp['simultaneous'] = $kiw_tenant['concurrent_user'];


        $kiw_admin = $kiw_db->query_first("SELECT username,email,password FROM kiwire_admin WHERE tenant_id = '{$kiw_id}' ORDER BY id ASC LIMIT 1");

        $kiw_temp['admin_id']    = $kiw_admin['username'];
        $kiw_temp['admin_email'] = $kiw_admin['email'];
        $kiw_temp['admin_pass']  = $kiw_admin['password'];


        $kiw_expire = @file_get_contents(dirname(__FILE__, 3) . "/custom/{$kiw_id}/tenant.license");


        if ($kiw_expire['expire_on']) {

            $kiw_temp['lkey'] = $kiw_expire;

        }


        $kiw_temp['reference'] = $kiw_temp['tenant_id'];

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }


}


function edit_single_data()
{

    global $kiw_db;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));
        $kiw_new_id = $kiw_db->escape(preg_replace('/[^A-Za-z0-9_-]/', '', $_REQUEST['tenant_id']));

        $kiw_old_id = $kiw_db->escape(preg_replace('/[^A-Za-z0-9_-]/', '', $_REQUEST['reference']));

        $kiw_ip = $kiw_db->sanitize($_REQUEST['ip_address']);

        $kiw_concurrent = $kiw_db->sanitize($_REQUEST['simultaneous']);


        $kiw_path = dirname(__FILE__, 3);


        $kiw_db->query("UPDATE kiwire_clouds SET updated_date = NOW(), ip_address = '{$kiw_ip}', concurrent_user = '{$kiw_concurrent}' WHERE tenant_id = '{$kiw_old_id}'");
        if ($kiw_new_id != $_REQUEST['reference']) {


            $kiw_tables = $kiw_db->fetch_array("SELECT DISTINCT(TABLE_NAME) AS TABLE_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME = 'tenant_id'");


            foreach ($kiw_tables as $kiw_table) {

                $kiw_db->query("UPDATE kiwire.{$kiw_table['TABLE_NAME']} SET updated_date = NOW(), tenant_id = '{$kiw_new_id}' WHERE tenant_id = '{$kiw_old_id}'");

            }




            exec("mv {$kiw_path}/custom/{$kiw_old_id} {$kiw_path}/custom/{$kiw_new_id}");


        }


        // save license key first

        if (strlen($_REQUEST['lkey']) > 0) file_put_contents("{$kiw_path}/custom/{$kiw_new_id}/", $_REQUEST['lkey']);


        sync_logger("{$_SESSION['user_name']} updated role {$kiw_old_id}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Tenant ID [{$kiw_old_id}] has been has been updated", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}
