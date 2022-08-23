<?php


global $kiw_request, $kiw_api, $kiw_roles;


$kiw_required = array(
    "username",
    "password",
    "email_address",
    "tenant_id",
    "tenant_name",
    "hostname"
);

if (in_array("Cloud -> Manage Client", $kiw_roles) == false) {

    die(json_encode(array("status" => "error", "message" => "This API key not allowed to access this module [ {$request_module[0]} ]", "data" => "")));

}


if ($kiw_request['method'] == "GET") {


    if (count($request_module) == 2) {


        $kiw_request['id'] = $kiw_db->escape($request_module[1]);

        $kiw_data = $kiw_db->query_first("SELECT * FROM kiwire_clouds WHERE tenant_id = '{$kiw_request['id']}' LIMIT 1");
  
  
    } else {


        if ($kiw_api['tenant_id'] == "superuser") {


            if (count($request_module) > 2) {

                $kiw_config['offset'] = (int)$request_module[1];
                $kiw_config['limit'] = (int)$request_module[2];
                $kiw_config['column'] = $kiw_db->escape($request_module[3]);
                $kiw_config['order'] = strtolower($request_module[4]) == "asc" ? "ASC" : "DESC";

            } else {

                $kiw_config['limit'] = 10;
                $kiw_config['offset'] = 0;
                $kiw_config['column'] = "id";
                $kiw_config['order'] = "DESC";

            }


            $kiw_data = $kiw_db->fetch_array("SELECT * FROM kiwire_clouds ORDER BY {$kiw_config['column']} {$kiw_config['order']} LIMIT {$kiw_config['offset']}, {$kiw_config['limit']}");


        } else {

            die(json_encode(array("status" => "error", "message" => "Only superuser can list all tenant detail", "data" => null)));

        }


    }


    echo json_encode(array("status" => "success", "message" => "", "data" => $kiw_data));


} elseif ($kiw_request['method'] == "POST") {


    require_once dirname(__FILE__, 2) . "/admin/includes/include_general.php";


    if ($kiw_api['tenant_id'] == "superuser") {


        // check for required info to execute

        $_REQUEST = file_get_contents("php://input");

        $_REQUEST = json_decode($_REQUEST, true);


        foreach ($kiw_required as $kiw_key) {


            if (empty($_REQUEST[$kiw_key])) {


                die(json_encode(array("status" => "error", "message" => "Missing required data [ {$kiw_key} ]", "data" => "")));


            } else $kiw_data[$kiw_key] = $kiw_db->escape($_REQUEST[$kiw_key]);


        }


        // sanitize tenant-id

        $kiw_data['tenant_id'] = preg_replace("/[^A-Za-z0-9_-]/", "", $kiw_data['tenant_id']);


        if (in_array(strtolower($kiw_data['tenant_id']), array("superuser", "general", "system", "nms", "log", "api"))) {


            echo json_encode(array("status" => "failed", "message" => "ERROR: Invalid tenant id [ {$kiw_data['tenant_id']} ]. Please use other tenant id.", "data" => null));

            die();


        }


        // check if the name already been used

        $kiw_test = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_clouds WHERE tenant_id = '{$kiw_data['tenant_id']}'");

        if ($kiw_test['kcount'] == 0) {


            // if no tenant with the same id, then create one

            // create admin

            $kiw_temp['username']   = $kiw_db->escape($kiw_data['username']);
            $kiw_temp['password']   = sync_encrypt($kiw_data['password']);
            $kiw_temp['email']      = $kiw_db->escape($kiw_data['email_address']);
            $kiw_temp['fullname']   = "Administrator";
            $kiw_temp['monitor']    = "y";

            $kiw_temp['temp_pass']  = "y";
            $kiw_temp['groupname']  = "operator";
            $kiw_temp['permission'] = "rw";
            $kiw_temp['tenant_id']  = $kiw_data['tenant_id'];

            $kiw_db->insert("kiwire_admin", $kiw_temp);

            unset($kiw_temp);


            // create admin group access

            $kiw_db->query("INSERT INTO kiwire_admin_group (SELECT NULL, 'operator', moduleid, '{$kiw_data['tenant_id']}', NOW() FROM kiwire_moduleid ORDER BY moduleid)");


            // create clouds

            $kiw_temp['tenant_id']  = $kiw_data['tenant_id'];
            $kiw_temp['name']       = $kiw_data['tenant_name'];
            $kiw_temp['ip_address'] = $kiw_data['hostname'];

            $kiw_temp['voucher_prefix']                 = strtoupper(substr(md5(time()), 6, 3) . "_");
            $kiw_temp['voucher_limit']                  = 5;
            $kiw_temp['campaign_wait_second']           = 15;
            $kiw_temp['campaign_multi_ads']             = "y";
            $kiw_temp['campaign_require_verification']  = "y";

            $kiw_temp['currency'] = "MYR";
            $kiw_temp['timezone'] = "Asia/Kuala_Lumpur";

            $kiw_temp['check_arrangement_login'] = "check_concurrent_user,check_active,check_allow_simultaneous,check_allow_quota,check_allow_credit,check_zone_limit,check_allow_mac,check_allow_zone,check_register_mac,activate_voucher_account";

            $kiw_db->insert("kiwire_clouds", $kiw_temp);

            unset($kiw_temp);


            // create custom directory

            if (file_exists(dirname(__FILE__, 2) . "/custom/{$kiw_data['tenant_id']}/") == false) {

                mkdir(dirname(__FILE__, 2) . "/custom/{$kiw_data['tenant_id']}/", 0755, true);

                //create custom stylesheet directory
                mkdir(dirname(__FILE__, 2) . "/custom/{$kiw_data['tenant_id']}/stylesheets", 0755, true);

                //create custom login images directory
                mkdir(dirname(__FILE__, 2) . "/custom/{$kiw_data['tenant_id']}/stylesheets/images", 0755, true);

            }

            // insert license key if available

            if (strlen($kiw_data['license_key']) > 0) {

                file_put_contents(dirname(__FILE__, 2) . "/custom/{$kiw_data['tenant_id']}/tenant.license", $kiw_data['license_key']);

            }


            if (file_exists(dirname(__FILE__, 3) . "/logs/{$kiw_data['tenant_id']}/") == false) {

                mkdir(dirname(__FILE__, 3) . "/logs/{$kiw_data['tenant_id']}/", 0755, true);

            }


            // insert notification

            $kiw_db->insert("kiwire_notification", array("tenant_id" => $kiw_data['tenant_id']));


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

            $kiw_db->update("kiwire_notification", $kiw_notification, "tenant_id = '{$kiw_data['tenant_id']}'");

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

            $kiw_db->update("kiwire_notification", $kiw_notification, "tenant_id = '{$kiw_data['tenant_id']}'");

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

            $kiw_db->update("kiwire_notification", $kiw_notification, "tenant_id = '{$kiw_data['tenant_id']}'");

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

            $kiw_db->update("kiwire_notification", $kiw_notification, "tenant_id = '{$kiw_data['tenant_id']}'");

            unset($kiw_notification);



            // create profile

            $kiw_temp['tenant_id']  = $kiw_data['tenant_id'];
            $kiw_temp['name']       = "Temp_Access";
            $kiw_temp['price']      = 0;
            $kiw_temp['type']       = "countdown";

            $kiw_attribute["control:Max-All-Session"]        = 600;
            $kiw_attribute["control:Simultaneous-Use"]       = 1;
            $kiw_attribute["control:Kiwire-Total-Quota"]     = 100;
            $kiw_attribute["reply:Acct-Interim-Interval"]    = 300;
            $kiw_attribute["reply:Idle-Timeout"]             = 300;
            $kiw_attribute["reply:WISPr-Bandwidth-Max-Down"] = 10240;
            $kiw_attribute["reply:WISPr-Bandwidth-Max-Up"]   = 10240;
            $kiw_attribute["reply:WISPr-Bandwidth-Min-Up"]   = 5120;
            $kiw_attribute["reply:WISPr-Bandwidth-Min-Down"] = 5120;

            $kiw_temp['attribute'] = json_encode($kiw_attribute);

            unset($kiw_attribute);


            $kiw_db->insert("kiwire_profiles", $kiw_temp);

            unset($kiw_temp);


            if ($_REQUEST['send_email'] == "y"){


                $kiw_email_content = @file_get_contents(dirname(__FILE__, 2) . "/user/templates/email-tenant.html");


                if (!empty($kiw_email_content)){


                    // get the first row as subject

                    $kiw_subject = explode(PHP_EOL, $kiw_email_content)[0];

                    $kiw_subject = trim($kiw_subject);


                    $kiw_email_content = preg_replace('/^.+\n/', '', $kiw_email_content);


                    $kiw_email = array();


                    $kiw_email['content'] = htmlentities(str_replace(array('{{username}}', '{{password}}', '{{tenant_id}}'), array($kiw_data['username'], stripcslashes($kiw_data['password']), $kiw_data['tenant_id']), $kiw_email_content));


                    $kiw_email['action']        = "send_email";
                    $kiw_email['tenant_id']     = "superuser";
                    $kiw_email['email_address'] = $kiw_db->escape($kiw_data['email_address']);
                    $kiw_email['subject']       = $kiw_subject;
                    $kiw_email['name']          = $kiw_db->escape($kiw_data['email_address']);


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


            echo json_encode(array("status" => "success", "message" => "Tenant ID [ {$kiw_data['tenant_id']} ] has been created", "data" => null));

            logger_api($kiw_api['tenant_id'], "{$kiw_request['api_key']} created clouds {$kiw_data['name']}");


        } else {

            echo json_encode(array("status" => "error", "message" => "Tenant ID already existed", "data" => ""));

        }


    } else {

        echo json_encode(array("status" => "error", "message" => "Only superuser can create new tenant", "data" => ""));

    }



} elseif ($kiw_request['method'] == "PATCH") {


    // check for required info to execute

    $_REQUEST = file_get_contents("php://input");

    $_REQUEST = json_decode($_REQUEST, true);


    foreach (array("tenant_id") as $kiw_key) {


        if (empty($_REQUEST[$kiw_key])) {

            die(json_encode(array("status" => "error", "message" => "Missing required data [ {$kiw_key} ]", "data" => "")));

        } else $kiw_data[$kiw_key] = $kiw_db->escape($_REQUEST[$kiw_key]);


    }


    // add remaining variable set

    foreach ($_REQUEST as $kiw_key => $kiw_value){

        if (!isset($kiw_data[$kiw_key]) && !in_array($kiw_key, array("id", "updated_date", "tenant_id"))){

            $kiw_data[$kiw_key] = $kiw_db->escape($kiw_value);

        }


    }


    if (count($request_module) == 2) {


        $kiw_request['id'] = $kiw_db->escape($request_module[1]);

        $kiw_db->update("kiwire_clouds", $kiw_data, "tenant_id = '{$kiw_data['tenant_id']}' LIMIT 1");


        if ($kiw_db->db_affected_row > 0) {

            echo json_encode(array("status" => "success", "message" => "", "data" => ""));

            logger_api($kiw_api['tenant_id'], "{$kiw_request['api_key']} updated clouds {$kiw_request['name']}");


        } else echo json_encode(array("status" => "error", "message" => "", "data" => ""));


    } else die(json_encode(array("status" => "error", "message" => "Missing ID for this request", "data" => "")));


} elseif ($kiw_request['method'] == "DELETE") {


    if (count($request_module) == 2) {


        if ($kiw_api['tenant_id'] == "superuser") {


            // need to delete data from all tables except reports

            $kiw_request = $kiw_db->escape($request_module[1]);

            $kiw_request = $kiw_db->query_first("SELECT tenant_id FROM kiwire_clouds WHERE tenant_id = '{$kiw_request}' LIMIT 1 ");


            if (!empty($kiw_request['tenant_id'])) {


                $kiw_tables = $kiw_db->fetch_array("SELECT DISTINCT(TABLE_NAME) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'kiwire' AND COLUMN_NAME = 'tenant_id'");

                foreach ($kiw_tables as $kiw_table) {

                    if (strpos($kiw_table, "_report_") == false && strpos($kiw_table, "_sessions_") == false) {

                        $kiw_db->query("DELETE FROM {$kiw_table['TABLE_NAME']} WHERE tenant_id = '{$kiw_request['tenant_id']}'");

                    }


                }

               
                if (file_exists(dirname(__FILE__, 2) . "/custom/{$kiw_request['tenant_id']}/") == true) {

                    system("rm -rf " . dirname(__FILE__, 2) . "/custom/{$kiw_request['tenant_id']}/");

                }
                
                $kiw_name = $kiw_db->query_first("SELECT name FROM {$kiw_table['TABLE_NAME']} LIMIT 1");
 

                echo json_encode(array("status" => "success", "message" => "Tenant ID [ {$kiw_request['tenant_id']} ] has been deleted", "data" => ""));

                logger_api($kiw_api['tenant_id'], "{$kiw_request['api_key']} deleted clouds {$kiw_name['name']}");


            } else {

                echo json_encode(array("status" => "error", "message" => "Invalid tenant id", "data" => ""));

            }


        } else {

            echo json_encode(array("status" => "error", "message" => "Only superuser can delete tenant", "data" => ""));

        }

  
    } else {

        echo json_encode(array("status" => "error", "message" => "Missing tenant id", "data" => ""));

    }


}
