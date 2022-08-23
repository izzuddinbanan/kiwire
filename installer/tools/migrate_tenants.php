<?php


$kiw_action = "";

$kiw_input = fopen("php://stdin", "r");


while (!in_array($kiw_action, array("dump", "update"))){


    echo "Please confirm your action [ dump / update / cancel ] : ";

    $kiw_action = strtolower(trim(fread($kiw_input, 10)));

    if ($kiw_action == "cancel") die("\n");


}


fclose($kiw_input);


if ($kiw_action == "dump") {


    echo "Check database connection.. ";

    $kiw_db = new mysqli("127.0.0.1", "root", "", "kiwire");

    if ($kiw_db->connect_errno) die("Unable to connect to Kiwire Database\n");

    echo "OK\n";


    echo "Load tenant list.. ";

    $kiw_clouds = $kiw_db->query("SELECT DISTINCT(x.cloud_id) AS cloud_id, z.customer AS name FROM kiwire_conf x LEFT JOIN kiwire_brand z ON x.cloud_id = z.cloud_id");

    echo "OK\n";


    if ($kiw_clouds) {


        echo "Saving tenant list to [ cloud_list.json ] .. ";

        $kiw_clouds = $kiw_clouds->fetch_all(MYSQLI_ASSOC);


        if (!empty($kiw_clouds)) {


            file_put_contents("cloud_list.json", json_encode($kiw_clouds));

            echo "Done\n";

            echo count($kiw_clouds) . " tenants has been saved.\n";


        } else {

            echo "\nNo tenant found..\n";

        }


    } else {

        echo "No tenant list found..\n";

    }


    $kiw_db->close();


} elseif ($kiw_action == "update") {


    require_once "/var/www/kiwire/server/admin/includes/include_connection.php";
    require_once "/var/www/kiwire/server/admin/includes/include_general.php";


    $kiw_db = Database::obtain();


    $kiw_clouds = file_get_contents("cloud_list.json");


    if (!empty($kiw_clouds)) {


        $kiw_clouds = json_decode($kiw_clouds, true);


        if ($kiw_clouds) {


            $kiw_total_tenant = count($kiw_clouds);

            $kiw_current_count = 1;


            foreach ($kiw_clouds as $kiw_cloud) {


                echo "Processing {$kiw_current_count}/{$kiw_total_tenant} Tenant [ {$kiw_cloud['cloud_id']} ]..\n";

                $kiw_current_count++;


                // create admin

                /*

                $kiw_temp = array();

                $kiw_temp['username'] = "admin";
                $kiw_temp['password'] = sync_encrypt("CyberN*123");
                $kiw_temp['email'] = "admin@cyber.net";
                $kiw_temp['fullname'] = "Administrator";
                $kiw_temp['monitor'] = "n";

                $kiw_temp['temp_pass'] = "y";
                $kiw_temp['groupname'] = "operator";
                $kiw_temp['permission'] = "rw";
                $kiw_temp['tenant_id'] = $kiw_cloud['cloud_id'];

                $kiw_db->insert("kiwire_admin", $kiw_temp);

                unset($kiw_temp);

                */

                // create module id for operator

                $kiw_db->query("INSERT INTO kiwire_admin_group (SELECT NULL, 'operator', moduleid, '{$kiw_cloud['cloud_id']}', NOW() FROM kiwire_moduleid ORDER BY moduleid)");


                // create basic config

                $kiw_temp = array();

                $kiw_temp['tenant_id'] = $kiw_cloud['cloud_id'];
                $kiw_temp['name'] = $kiw_cloud['name'];

                $kiw_temp['voucher_prefix'] = strtoupper(substr(md5(time()), 6, 3) . "_");
                $kiw_temp['voucher_limit'] = 5;
                $kiw_temp['campaign_wait_second'] = 15;
                $kiw_temp['campaign_multi_ads'] = "n";
                $kiw_temp['campaign_require_verification'] = "y";

                $kiw_temp['currency'] = "PKR";
                $kiw_temp['timezone'] = "Asia/Karachi";
                $kiw_temp['status'] = "y";

                $kiw_temp['forgot_password_method'] = "sms";
                $kiw_temp['forgot_password_template'] = "sms";

                $kiw_temp['check_arrangement_login'] = "check_active,check_password,check_allow_simultaneous,check_allow_quota,check_allow_credit,reporting_process";

                $kiw_db->insert("kiwire_clouds", $kiw_temp);

                unset($kiw_temp);


                // create custom directory

                if (file_exists("/var/www/kiwire/server/custom/{$kiw_cloud['cloud_id']}/") == false) {

                    mkdir("/var/www/kiwire/server/custom/{$kiw_cloud['cloud_id']}/", 0755, true);

                }


                if (file_exists("/var/www/kiwire/logs/{$kiw_cloud['cloud_id']}/") == false) {

                    mkdir("/var/www/kiwire/logs/{$kiw_cloud['cloud_id']}/", 0755, true);

                }


                // create notification


                $kiw_db->insert("kiwire_notification", array("tenant_id" => $kiw_cloud['cloud_id']));


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

                $kiw_db->update("kiwire_notification", $kiw_notification, "tenant_id = '{$kiw_cloud['cloud_id']}'");

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

                $kiw_db->update("kiwire_notification", $kiw_notification, "tenant_id = '{$kiw_cloud['cloud_id']}'");

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

                $kiw_db->update("kiwire_notification", $kiw_notification, "tenant_id = '{$kiw_cloud['cloud_id']}'");

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

                $kiw_db->update("kiwire_notification", $kiw_notification, "tenant_id = '{$kiw_cloud['cloud_id']}'");

                unset($kiw_notification);



                // create temporary profile

                $kiw_temp = array();

                $kiw_temp['tenant_id']  = $kiw_cloud['cloud_id'];
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


                // create cybernet tenant profile

                $kiw_temp = array();

                $kiw_temp['tenant_id']  = $kiw_cloud['cloud_id'];
                $kiw_temp['name']       = $kiw_cloud['cloud_id'];
                $kiw_temp['price']      = 0;
                $kiw_temp['type']       = "countdown";

                $kiw_attribute["control:Max-All-Session"]        = 10800;
                $kiw_attribute["control:Simultaneous-Use"]       = 1;
                $kiw_attribute["control:Kiwire-Total-Quota"]     = 0;
                $kiw_attribute["reply:Acct-Interim-Interval"]    = 1800;
                $kiw_attribute["reply:Idle-Timeout"]             = 1800;
                $kiw_attribute["reply:WISPr-Bandwidth-Max-Down"] = 10485760;
                $kiw_attribute["reply:WISPr-Bandwidth-Max-Up"]   = 10485760;
                $kiw_attribute["reply:WISPr-Bandwidth-Min-Up"]   = 1048576;
                $kiw_attribute["reply:WISPr-Bandwidth-Min-Down"] = 1048576;

                $kiw_temp['attribute'] = json_encode($kiw_attribute);

                unset($kiw_attribute);


                $kiw_db->insert("kiwire_profiles", $kiw_temp);

                unset($kiw_temp);



                // insert sms template

                $kiw_temp = array();

                $kiw_temp['id']             = "NULL";
                $kiw_temp['tenant_id']      = $kiw_cloud['cloud_id'];
                $kiw_temp['updated_date']   = "NOW()";
                $kiw_temp['name']           = "sms";
                $kiw_temp['user']           = "migration";
                $kiw_temp['type']           = "sms";
                $kiw_temp['subject']        = "sms";
                $kiw_temp['content']        = "[ {{tenant_id}} ] User {{username}} Pwd {{password}}";


                $kiw_db->insert("kiwire_html_template", $kiw_temp);

                unset($kiw_temp);


                // sms setting

                $kiw_temp = array();

                $kiw_temp['id']             = "NULL";
                $kiw_temp['tenant_id']      = $kiw_cloud['cloud_id'];
                $kiw_temp['updated_date']   = "NOW()";
                $kiw_temp['enabled']        = "y";
                $kiw_temp['allowed_zone']   = "none";
                $kiw_temp['profile']        = $kiw_cloud['cloud_id'];
                $kiw_temp['template_id']    = "sms";
                $kiw_temp['validity']       = "9999";
                $kiw_temp['after_register'] = "journey";
                $kiw_temp['mode']           = "3";
                $kiw_temp['operator']       = "cybernet";
                $kiw_temp['prefix_phoneno'] = "y";
                $kiw_temp['template']       = "sms";
                $kiw_temp['data']           = "";

                $kiw_db->insert("kiwire_int_sms", $kiw_temp);

                unset($kiw_temp);


                // set the journey

                $kiw_pages = [];

                while (count($kiw_pages) < 4) {

                    $kiw_temp = substr(md5($kiw_cloud['cloud_id'] . time() . rand(0, 99999)), 1, 8);

                    if (!in_array($kiw_temp, $kiw_pages)){

                        $kiw_pages[] = $kiw_temp;

                    }

                }

                unset($kiw_temp);


                $kiw_temp = array();

                $kiw_temp['id']              = "NULL";
                $kiw_temp['tenant_id']       = $kiw_cloud['cloud_id'];
                $kiw_temp['updated_date']    = "NOW()";
                $kiw_temp['journey_name']    = $kiw_cloud['cloud_id'];
                $kiw_temp['page_list']       = implode(",", array_slice($kiw_pages, 0, 3));
                $kiw_temp['created_by']      = "migration";
                $kiw_temp['created_when']    = "NOW()";
                $kiw_temp['status']          = "y";
                $kiw_temp['lang']            = "en";
                $kiw_temp['pre_login']       = "default";
                $kiw_temp['pre_login_url']   = "";
                $kiw_temp['post_login']      = "custom";
                $kiw_temp['post_login_url']  = urlencode("https://www.google.com/");

                $kiw_db->insert("kiwire_login_journey", $kiw_temp);

                unset($kiw_temp);


                // inject pages to the tenant

                if (file_exists("/var/www/kiwire/server/custom/{$kiw_cloud['cloud_id']}/thumbnails/") == false) {

                    mkdir("/var/www/kiwire/server/custom/{$kiw_cloud['cloud_id']}/thumbnails/", 0755, true);

                }


                foreach ($kiw_pages as $kiw_index => $kiw_unique){


                    $kiw_page_number = $kiw_index + 1;

                    $kiw_content = file_get_contents("{$kiw_page_number}.html");


                    if (!empty($kiw_content)){


                        if (file_exists("{$kiw_page_number}.png")) {

                            copy("{$kiw_page_number}.png", "/var/www/kiwire/server/custom/{$kiw_cloud['cloud_id']}/thumbnails/{$kiw_unique}.png");

                        }


                        $kiw_temp = array();

                        $kiw_temp['id']             = "NULL";
                        $kiw_temp['tenant_id']      = $kiw_cloud['cloud_id'];
                        $kiw_temp['updated_date']   = "NOW()";
                        $kiw_temp['unique_id']      = $kiw_unique;
                        $kiw_temp['page_name']      = "{$kiw_cloud['cloud_id']}-page{$kiw_page_number}";
                        $kiw_temp['purpose']        = "landing";
                        $kiw_temp['content']        = base64_encode(urlencode($kiw_content));
                        $kiw_temp['remark']         = "";
                        $kiw_temp['default_page']   = "y";
                        $kiw_temp['bg_lg']          = "";
                        $kiw_temp['bg_md']          = "";
                        $kiw_temp['bg_sm']          = "";
                        $kiw_temp['bg_css']         = "";

                        $kiw_temp['count_impress']  = ($kiw_index == 0 ? "y" : "n");

                        $kiw_db->insert("kiwire_login_pages", $kiw_temp);

                        unset($kiw_temp);



                    }


                    unset($kiw_page_number);
                    unset($kiw_content);


                }


                unset($kiw_index);
                unset($kiw_unique);
                unset($kiw_pages);


                // create zone

                $kiw_temp = array();

                $kiw_temp['id']              = "NULL";
                $kiw_temp['tenant_id']       = $kiw_cloud['cloud_id'];
                $kiw_temp['updated_date']    = "NOW()";
                $kiw_temp['name']            = "Free-WiFi";
                $kiw_temp['status']          = "y";
                $kiw_temp['created_by']      = "migration";
                $kiw_temp['auto_login']      = "";
                $kiw_temp['simultaneous']    = "";
                $kiw_temp['journey']         = $kiw_cloud['cloud_id'];
                $kiw_temp['priority']        = "999";
                $kiw_temp['force_profile']   = "";

                $kiw_db->insert("kiwire_zone", $kiw_temp);

                unset($kiw_temp);


                $kiw_temp = array();

                $kiw_temp['id']              = "NULL";
                $kiw_temp['tenant_id']       = $kiw_cloud['cloud_id'];
                $kiw_temp['updated_date']    = "NOW()";
                $kiw_temp['master_id']       = "Free-WiFi";
                $kiw_temp['nasid']           = "";
                $kiw_temp['ipaddr']          = "";
                $kiw_temp['vlan']            = "";
                $kiw_temp['ssid']            = "Free-WiFi";
                $kiw_temp['dzone']           = "";
                $kiw_temp['priority']        = "999";
                $kiw_temp['hash']            = "";

                $kiw_db->insert("kiwire_zone_child", $kiw_temp);

                unset($kiw_temp);


            }


        } else {

            echo "Invalid [ cloud.json ] file..\n";

        }


    } else {

        echo "Tenant list [ cloud.json ] not found..\n";

    }


}