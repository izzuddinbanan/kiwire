<?php

require_once dirname(__FILE__, 4) . "/includes/include_session.php";
require_once dirname(__FILE__, 4) . "/includes/include_general.php";
require_once dirname(__FILE__, 4) . "/includes/include_account.php";

require_once dirname(__FILE__, 5) . "/admin/includes/include_connection.php";


$kiw_user['username'] = preg_replace('/\D/', '', $_REQUEST['username']);

$kiw_user['fullname'] = $kiw_db->escape($_REQUEST['fullname']);


$kiw_notification = $kiw_cache->get("NOTIFICATION_DATA:{$_SESSION['controller']['tenant_id']}");

if (empty($kiw_notification)) {


    $kiw_notification = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_notification WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

    if (empty($kiw_notification)) $kiw_notification = array("dummy" => true);

    $kiw_cache->set("NOTIFICATION_DATA:{$_SESSION['controller']['tenant_id']}", $kiw_notification, 1800);


}


if (empty($kiw_user['username'])) {

    error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_missing_credential_check']);

}


if ($_SESSION['system']['checked'] == true) {


    // create a random code for the future use. ie otp or password

    $kiw_user['code'] = substr(preg_replace('/\D/', '', md5(time() . $kiw_user['username'])), 6, 8);


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


    if ($kiw_signup['enabled'] == "y") {


        // check if required otp to create user

        if ($kiw_signup['mode'] == "3") {


            if (check_account_exist($kiw_db, $kiw_user['username'], $_SESSION['controller']['tenant_id']) == false) {



                $kiw_user['password'] = substr(preg_replace('/\D/', '', md5($_REQUEST['username'] . time())), 6, 8);


                // create the user and send password via sms

                $kiw_create_user = array();

                $kiw_create_user['tenant_id']      = $_SESSION['controller']['tenant_id'];
                $kiw_create_user['username']       = $kiw_user['username'];
                $kiw_create_user['password']       = $kiw_user['password'];
                $kiw_create_user['fullname']       = $kiw_user['fullname'];
                $kiw_create_user['phone_number']   = $kiw_user['phone_number'];
                $kiw_create_user['remark']         = $kiw_signup['remark'];
                $kiw_create_user['profile_subs']   = $kiw_signup['profile'];
                $kiw_create_user['profile_curr']   = $kiw_signup['profile'];
                $kiw_create_user['ktype']          = "account";
                $kiw_create_user['status']         = "active";
                $kiw_create_user['integration']    = "int";
                $kiw_create_user['allowed_zone']   = $kiw_signup['allowed_zone'];
                $kiw_create_user['date_value']     = "NOW()";
                $kiw_create_user['date_expiry']    = date("Y-m-d H:i:s", strtotime("+{$kiw_signup['validity']} Day"));


                if (create_account($kiw_db, $kiw_cache, $kiw_create_user)){


                    $kiw_sms_template = $kiw_cache->get("SMS_TEMPLATE:{$_SESSION['controller']['tenant_id']}:{$kiw_signup['sms_text']}");

                    if (empty($kiw_sms_template)) {

                        $kiw_sms_template = $kiw_db->query_first("SELECT * FROM kiwire_html_template WHERE name = '{$kiw_signup['sms_text']}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

                        if (empty($kiw_sms_template)){

                            $kiw_sms_template['content'] = "[ WIFI Login ] Username: {{username}} Password: {{password}}. Thanks.";

                        }

                        $kiw_cache->set("SMS_TEMPLATE:{$_SESSION['controller']['tenant_id']}:{$kiw_signup['sms_text']}", $kiw_sms_template, 1800);

                    }


                    $kiw_sms_template['content'] = stripcslashes($kiw_sms_template['content']);

                    $kiw_sms['content'] = str_replace("{{tenant_id}}", $_SESSION['controller']['tenant_id'], $kiw_sms_template['content']);
                    $kiw_sms['content'] = str_replace("{{username}}", $kiw_user['username'], $kiw_sms['content']);
                    $kiw_sms['content'] = str_replace("{{password}}", $kiw_user['password'], $kiw_sms['content']);
                    $kiw_sms['content'] = str_replace("{{domain_used}}", $_SESSION['controller']['tenant_id'], $kiw_sms['content']);
                    $kiw_sms['content'] = str_replace("{{system_name}}", sync_brand_decrypt(SYNC_PRODUCT), $kiw_sms['content']);

                    $kiw_sms['content'] = strip_tags($kiw_sms['content']);


                    $kiw_sms['action']       = "send_sms";
                    $kiw_sms['tenant_id']    = $_SESSION['controller']['tenant_id'];
                    $kiw_sms['phone_number'] = $kiw_user['username'];


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


                }

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



                $_SESSION['user']['current'] = next_page($_SESSION['user']['journey'], $_SESSION['user']['current'], $_SESSION['user']['default']);

                logger($_SESSION['user']['mac'], "SMS sent to {$kiw_user['username']}");


                if ($kiw_signup['register'] == "internet") {

                    login_user($kiw_user['username'], $kiw_user['password'], $session_id);

                } elseif ($kiw_signup['register'] == "journey"){

                    header("Location: /user/pages/?session={$session_id}");

                } else {

                    error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['notification_account_created']);

                }


            } else {

                error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_username_existed']);

            }


        } else {

            error_redirect($_SERVER['HTTP_REFERER'], "You are not allowed to access this module.");

        }


    } else {


        error_redirect($_SERVER['HTTP_REFERER'], "You are not allowed to access this module.");


    }


} else {


    error_redirect($_SERVER['HTTP_REFERER'], "You are not allowed to access this module.");


}

