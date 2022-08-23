<?php

require_once dirname(__FILE__, 4) . "/includes/include_session.php";
require_once dirname(__FILE__, 4) . "/includes/include_general.php";
require_once dirname(__FILE__, 4) . "/includes/include_account.php";

require_once dirname(__FILE__, 5) . "/admin/includes/include_general.php";
require_once dirname(__FILE__, 5) . "/admin/includes/include_connection.php";


if ($_SESSION['system']['checked'] == true) {


    $kiw_notification = $kiw_cache->get("NOTIFICATION_DATA:{$_SESSION['controller']['tenant_id']}");

    if (empty($kiw_notification)) {


        $kiw_notification = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_notification WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

        if (empty($kiw_notification)) $kiw_notification = array("dummy" => true);

        $kiw_cache->set("NOTIFICATION_DATA:{$_SESSION['controller']['tenant_id']}", $kiw_notification, 1800);


    }



    $kiw_signup = $kiw_cache->get("SMS_SIGNUP:{$_SESSION['controller']['tenant_id']}");

    if (empty($kiw_signup)) {


        $kiw_signup = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_int_sms WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

        if (empty($kiw_signup)) $kiw_signup = array("dummy" => true);

        $kiw_cache->set("SMS_SIGNUP:{$_SESSION['controller']['tenant_id']}", $kiw_signup, 1800);


    }


    // overwrite profile with zone profile if available

    if (isset($_SESSION['controller']['force_profile']) && !empty($_SESSION['controller']['force_profile'])){

        $kiw_signup['profile'] = $_SESSION['controller']['force_profile'];

    }


    if (isset($_SESSION['controller']['force_allowed_zone']) && !empty($_SESSION['controller']['force_allowed_zone'])){

        $kiw_signup['allowed_zone'] = $_SESSION['controller']['force_allowed_zone'];

    }


    if (!isset($_REQUEST['code']) && $kiw_signup['enabled'] == "y") {


        // send otp for every login

        if ($kiw_signup['mode'] == "2") {


            // set new password to login

            $kiw_user['password'] = substr(preg_replace('/\D/', '', md5(time() . $_SESSION['controller']['tenant_id'] . $_SESSION['user']['mac'])), 6, 6);


            // if not 2 factor authentication, then proceed to check and create account

            if (!isset($_SESSION['user']['two_factor'])) {


                $kiw_user['username'] = preg_replace('/\D/', '', $_REQUEST['username']);

                $kiw_user['fullname'] = $kiw_db->escape($_REQUEST['fullname']);


                if (empty($kiw_user['username'])) {

                    error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_invalid_phone_number']);

                }


                $kiw_test = $kiw_db->query_first("SELECT username,password FROM kiwire_account_auth WHERE username = '{$kiw_user['username']}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");


                if (!isset($kiw_test['username']) || empty($kiw_test['username'])) {


                    $kiw_create_user = array();

                    $kiw_create_user['tenant_id']      = $_SESSION['controller']['tenant_id'];
                    $kiw_create_user['username']       = $kiw_user['username'];
                    $kiw_create_user['password']       = $kiw_user['password'];
                    $kiw_create_user['fullname']       = $kiw_user['fullname'];
                    $kiw_create_user['profile_subs']   = $kiw_signup['profile'];
                    $kiw_create_user['ktype']          = "account";
                    $kiw_create_user['status']         = "active";
                    $kiw_create_user['integration']    = "sms";
                    $kiw_create_user['allowed_zone']   = $kiw_signup['allowed_zone'];
                    $kiw_create_user['date_value']     = "NOW()";
                    $kiw_create_user['date_expiry']    = date("Y-m-d H:i:s", strtotime("+{$kiw_signup['validity']} Day"));

                    create_account($kiw_db, $kiw_cache, $kiw_create_user);

                    unset($kiw_create_user);


                    // get the data mapping setting

                    $kiw_mapping = file_get_contents(dirname(__FILE__, 5) . "/custom/{$_SESSION['controller']['tenant_id']}/data-mapping.json");

                    $kiw_mapping = json_decode($kiw_mapping, true);


                    // collect all necessary data

                    if (strlen($kiw_signup['data']) > 0 && count($kiw_mapping) > 0) {


                        foreach (explode(",", $kiw_signup['data']) as $kiw_data) {

                            if (isset($_REQUEST[$kiw_data]) && !empty($_REQUEST[$kiw_data])) {

                                foreach ($kiw_mapping as $kiw_map) {

                                    if ($kiw_map['variable'] == $kiw_data) {


                                        $kiw_create_user[$kiw_map['field']] = $kiw_db->escape($_REQUEST[$kiw_data]);

                                        break;


                                    }

                                }

                            }


                        }


                        $kiw_create_user['tenant_id']   = $_SESSION['controller']['tenant_id'];
                        $kiw_create_user['username']    = $kiw_user['username'];
                        $kiw_create_user['source']      = "system";

                        $kiw_db->query(sql_insert($kiw_db, "kiwire_account_info", $kiw_create_user));

                        unset($kiw_create_user);


                    }



                } else {


                    $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), password = '" . sync_encrypt($kiw_user['password']) . "' WHERE username = '{$kiw_user['username']}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");



                    // get the data mapping setting

                    $kiw_mapping = file_get_contents(dirname(__FILE__, 5) . "/custom/{$_SESSION['controller']['tenant_id']}/data-mapping.json");

                    $kiw_mapping = json_decode($kiw_mapping, true);


                    // collect all necessary data

                    if (strlen($kiw_signup['data']) > 0 && count($kiw_mapping) > 0) {


                        foreach (explode(",", $kiw_signup['data']) as $kiw_data) {

                            // if (isset($_REQUEST[$kiw_data]) && !empty($_REQUEST[$kiw_data])) {

                                foreach ($kiw_mapping as $kiw_map) {

                                    if ($kiw_map['variable'] == $kiw_data) {


                                        $kiw_update_user[$kiw_map['field']] = $kiw_db->escape($_REQUEST[$kiw_data]) ?? NULL;

                                        break;


                                    }

                                }

                            // }


                        }

                        if(!empty($kiw_update_user)) {

                            
                            $kiw_db->query(sql_update($kiw_db, "kiwire_account_info", $kiw_update_user, "tenant_id = '{$_SESSION['controller']['tenant_id']}' AND username = '{$kiw_user['username']}' LIMIT 1"));
                            
                            unset($kiw_update_user);


                            
                        }

                    }


                }


                $kiw_sms['phone_number'] = $kiw_user['username'];


            } else {


                // the the user phone number if 2-factors authentication

                $kiw_user['username'] = $kiw_db->escape($_REQUEST['username']);

                $kiw_sms['phone_number'] = $kiw_db->query_first("SELECT phone_number FROM kiwire_account_auth WHERE username = '{$kiw_user['username']}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1")['phone_number'];


            }



            $kiw_sms_template = $kiw_cache->get("SMS_TEMPLATE:{$_SESSION['controller']['tenant_id']}:{$kiw_signup['sms_text']}");

            if (empty($kiw_sms_template)) {

                $kiw_sms_template = $kiw_db->query_first("SELECT * FROM kiwire_html_template WHERE name = '{$kiw_signup['sms_text']}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

                if (empty($kiw_sms_template)){

                    $kiw_sms_template['content'] = "[ WIFI Login ] Your OTP is {{otp_code}}";

                }

                $kiw_cache->set("SMS_TEMPLATE:{$_SESSION['controller']['tenant_id']}:{$kiw_signup['sms_text']}", $kiw_sms_template, 1800);

            }


            $kiw_sms_template['content'] = stripcslashes($kiw_sms_template['content']);

            $kiw_sms['content'] = str_replace("{{tenant_id}}", $_SESSION['controller']['tenant_id'], $kiw_sms_template['content']);
            $kiw_sms['content'] = str_replace("{{username}}", $kiw_user['username'], $kiw_sms['content']);
            $kiw_sms['content'] = str_replace("{{otp_code}}", $kiw_user['password'], $kiw_sms['content']);

            $kiw_sms['content'] = strip_tags($kiw_sms['content']);

            $kiw_sms['action']       = "send_sms";
            $kiw_sms['tenant_id']    = $_SESSION['controller']['tenant_id'];



            $kiw_temp = curl_init();

            curl_setopt($kiw_temp, CURLOPT_URL, "http://127.0.0.1:9956");
            curl_setopt($kiw_temp, CURLOPT_POST, true);
            curl_setopt($kiw_temp, CURLOPT_POSTFIELDS, http_build_query($kiw_sms));
            curl_setopt($kiw_temp, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($kiw_temp, CURLOPT_TIMEOUT, 5);
            curl_setopt($kiw_temp, CURLOPT_CONNECTTIMEOUT, 5);

            unset($kiw_sms);


            curl_exec($kiw_temp);

            curl_close($kiw_temp);

            unset($kiw_temp);



            // send otp to user for them to verification and redirect user to otp page

            $_SESSION['user']['username'] = $kiw_user['username'];
            $_SESSION['user']['otp'] = $kiw_user['password'];

            $_SESSION['user']['current'] = next_page($_SESSION['user']['journey'], $_SESSION['user']['current'], $_SESSION['user']['default']);


            logger($_SESSION['user']['mac'], "SMS sent to {$kiw_user['username']}");

            header("Location: /user/pages/?session={$session_id}");


        } else {

            error_redirect($_SERVER['HTTP_REFERER'], "You are not allowed to access this module.");

        }



    } else {


        if ($kiw_signup['enabled'] == "y" && $kiw_signup['mode'] == "2") {


            $kiw_user['code'] = preg_replace('/\D/', '', $_REQUEST['code']);


            if (!empty($_SESSION['user']['username']) && !empty($_SESSION['user']['otp'])) {


                if ($kiw_user['code'] == $_SESSION['user']['otp']) {


                    unset($_SESSION['user']['otp']);


                    // check if this otp for 2-factor, if yes then use 2 factor password

                    if (!isset($_SESSION['user']['two_factor'])) {


                        $kiw_temp = $kiw_db->query_first("SELECT password FROM kiwire_account_auth WHERE username = '{$_SESSION['user']['username']}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

                        login_user($_SESSION['user']['username'], sync_decrypt($kiw_temp['password']), $session_id);


                    } else {


                        $_SESSION['user']['two_factor_succeed'] = true;

                        login_user($_SESSION['user']['username'], $_SESSION['user']['two_factor'], $session_id);

                    }



                } else {


                    error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_wrong_otp']);


                }


            } else {

                error_redirect($_SERVER['HTTP_REFERER'], "Your session already expired. Please try again.");

            }


        } else {

            error_redirect($_SERVER['HTTP_REFERER'], "You are not allowed to access this module.");

        }


    }


} else {

    error_redirect($_SERVER['HTTP_REFERER'], "You are not allowed to access this module");

}