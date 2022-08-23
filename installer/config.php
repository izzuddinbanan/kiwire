#!/usr/bin/php
<?php


error_reporting(0);

require_once "/var/www/kiwire/server/admin/includes/include_general.php";
require_once "/var/www/kiwire/server/libs/class.sql.helper.php";



$kiw_db = new mysqli(SYNC_DB1_HOST,SYNC_DB1_USER,SYNC_DB1_PASSWORD,SYNC_DB1_DATABASE, SYNC_DB1_PORT);


if ($kiw_db->connect_errno){

    die("Kiwire installation: failed to connect to database");

}


$kiw_date = time();

$kiw_date = sync_brand_encrypt($kiw_date);

$kiw_secret = md5("kiwirev3.0");


$kiw_test = $kiw_db->query("SELECT * FROM kiwire_moduleid LIMIT 1");

if ($kiw_test) $kiw_test = $kiw_test->fetch_all(MYSQLI_ASSOC)[0];


if (!empty($kiw_test['moduleid'])) {


    $kiw_installed = @file_get_contents("/usr/share/test/{$kiw_secret}.conf");


    if ($kiw_installed !== "true") {


        if (file_exists("/usr/share/test/") == false) mkdir("/usr/share/test/", 0755, true);


        $kiw_password = sync_encrypt("testing*123");


        $kiw_db->query("INSERT INTO kiwire_admin_group SELECT NULL, 'operator', moduleid, 'default', NOW() FROM kiwire_moduleid");
        $kiw_db->query("INSERT INTO kiwire_admin_group SELECT NULL, 'operator', moduleid, 'superuser', NOW() FROM kiwire_moduleid");

        $kiw_db->query("INSERT INTO kiwire_admin VALUE (NULL, 'default', NOW(), 'admin', '{$kiw_password}', 'operator', 'Administrator', NULL, 'admin@test.com', 'default', 'y', 1, 'rw', 0.00, NULL, NULL, 'default', '', '', 'n')");
        $kiw_db->query("INSERT INTO kiwire_admin VALUE (NULL, 'superuser', NOW(), 'admin', '{$kiw_password}', 'operator', 'Administrator', NULL, 'admin@test.com', 'default', 'y', 1, 'rw', 0.00, NULL, NULL, 'default', '', '', 'n')");

        $kiw_db->query("INSERT INTO kiwire_clouds (tenant_id,timezone,voucher_prefix,currency,status) VALUE ('default', 'Asia/Kuala_Lumpur', 'VOU_', 'MYR', 'y')");
        $kiw_db->query("UPDATE kiwire_clouds SET updated_date = NOW(),  check_arrangement_login = 'check_active,check_password,check_allow_simultaneous,check_allow_quota,check_allow_credit,check_zone_limit,check_allow_mac,check_allow_zone,check_register_mac,activate_voucher_account,reporting_process' WHERE tenant_id = 'default'");


        $kiw_temp['tenant_id'] = "default";
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

        $kiw_db->query(sql_insert($kiw_db, "kiwire_profiles", $kiw_temp));


        $kiw_notification = array();

        $kiw_notification['tenant_id']                      = "default";
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

        $kiw_db->query(sql_insert($kiw_db, "kiwire_notification", $kiw_notification));

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

        $kiw_db->query(sql_update($kiw_db, "kiwire_notification", $kiw_notification, "tenant_id = 'default'"));

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

        $kiw_db->query(sql_update($kiw_db, "kiwire_notification", $kiw_notification, "tenant_id = 'default'"));

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

        $kiw_db->query(sql_update($kiw_db,"kiwire_notification", $kiw_notification, "tenant_id = 'default'"));

        unset($kiw_notification);


        // create current session table

        $kiw_session_date = date("Ym");

        $kiw_db->query("CREATE TABLE kiwire_sessions_{$kiw_session_date} LIKE kiwire_session_template");


        @file_put_contents("/var/www/kiwire/server/custom/cloud.data", $kiw_date);
        @file_put_contents("/var/www/kiwire/server/custom/default/tenant.data", $kiw_date);

        @file_put_contents("/usr/share/test/{$kiw_secret}.conf", "true");

        system("chown nginx:nginx /var/www/kiwire/server/custom/cloud.data");
        system("chown nginx:nginx /var/www/kiwire/server/custom/default/tenant.data");


    }


}