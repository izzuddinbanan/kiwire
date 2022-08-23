<?php


// legacy support to Cybernet Mobile App

$cn_password = "cybernet_test";
$storm_tenant = "stormwifi";


require_once "/var/www/kiwire/server/admin/includes/include_connection.php";
require_once "/var/www/kiwire/server/admin/includes/include_general.php";

require_once "/var/www/kiwire/server/user/includes/include_account.php";


header("Content-Type: application/json");
header("Cache-Control: no-cache");
header("Connection: close");


$cn_action  = $kiw_db->escape($_REQUEST['request']);
$cn_time    = $kiw_db->escape($_REQUEST['time']);
$cn_sec     = $kiw_db->escape($_REQUEST['signature']);


if (empty($cn_sec)) die(json_encode(array("status" => false, "data" => array("message" => "Missing signature"))));

$cn_time = preg_replace( '/[a-zA-Z]/', '', $cn_time);

$cn_password = md5($cn_password . "|" . $cn_time);


if (empty($cn_password) || $cn_password !== $cn_sec){

    die(json_encode(array("status" => false, "data" => array("message" => "Invalid signature"))));

}


if (empty($cn_action)) die(json_encode(array("status" => false, "data" => array("message" => "Missing / empty request parameter"))));


if (abs(time() - strtotime($cn_time)) > 3600) die(json_encode(array("status" => false, "data" => array("message" => "Time cannot be larger than 1 hour from current time."))));


if ($cn_action == "tenant"){

    $sys_cloud_id = $kiw_db->escape($_REQUEST['apserial']);
    $sys_cloud_id = $kiw_db->query_first("SELECT tenant_id FROM kiwire_controller WHERE unique_id = '{$sys_cloud_id}' LIMIT 1");

    if (!empty($sys_cloud_id['tenant_id'])) die(json_encode(array("status" => true, "data" => array("tenant" => $sys_cloud_id['tenant_id']))));
    else die(json_encode(array("status" => false, "data" => array("message" => "Unknown serial number"))));

}

$user_name = $kiw_db->escape($_REQUEST['mobile_number']);
$sys_cloud_id = $kiw_db->escape($_REQUEST['tenant']);

if (empty($sys_cloud_id)) die(json_encode(array("status" => false, "data" => array("message" => "Missing tenant name"))));
if (empty($user_name)) die(json_encode(array("status" => false, "data" => array("message" => "Missing phone number"))));


// cache error message
$error_msg = $kiw_cache->get("{$sys_cloud_id}_error_messages");

if (empty($error_msg)) {

    $error_msg = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_notification WHERE tenant_id = '{$sys_cloud_id}' LIMIT 1");

    $kiw_cache->set("{$sys_cloud_id}_error_messages", $error_msg, 1800);

}


if ($cn_action == "login"){


    $username = $user_name;


    $profile_info = $kiw_cache->get("{$sys_cloud_id}_ma_profile");

    if (empty($profile_info)) {

        $profile_info = $kiw_db->query_first("SELECT * FROM kiwire_int_sms WHERE tenant_id = '{$sys_cloud_id}' LIMIT 1");

        $kiw_cache->set("{$sys_cloud_id}_ma_profile", $profile_info, 1800);

    }


    $profile_draw = $kiw_cache->get("{$sys_cloud_id}_ma_pdata");

    if (empty($profile_draw)) {

        $profile_draw = $kiw_db->query_first("SELECT * FROM (SELECT * FROM kiwire_profiles WHERE tenant_id = '{$sys_cloud_id}' AND name = '{$profile_info['plan']}' LIMIT 1");

        $profile_draw = json_decode($profile_draw['attribute'], true);

        $kiw_cache->set("{$sys_cloud_id}_ma_pdata", $profile_draw, 1800);

    }


    $profile_dproc = $kiw_cache->get("{$sys_cloud_id}_ma_pproc");

    if (empty($profile_dproc)) {

        foreach ($profile_draw as $attribute){

            if ($attribute['attribute'] == "Kiwire-Total-Bytes")    $profile_dproc['max_byte']      = $attribute['value'];
            if ($attribute['attribute'] == "Max-All-Session")       $profile_dproc['max_time']      = $attribute['value'];
            if ($attribute['attribute'] == "Access-Period")         $profile_dproc['max_time']      = $attribute['value'];
            if ($attribute['attribute'] == "Simultaneous-Use")      $profile_dproc['max_user']      = $attribute['value'];
            if ($attribute['attribute'] == "Session-Timeout")       $profile_dproc['max_time']      = $attribute['value'];

        }

        $profile_dproc['max_byte'] = (empty($profile_dproc['max_byte']) ? 0 : ($profile_dproc['max_byte'] / 1024));

        $kiw_cache->set("{$sys_cloud_id}_ma_pproc", $profile_dproc, 1800);

    }


    $user_detail = $kiw_cache->get("{$sys_cloud_id}_ud_{$user_name}");

    $password = $_REQUEST['password'];

    if (empty($user_detail)) {


        if ($sys_cloud_id == $storm_tenant){


            $kiw_data['action']     = "check_radius";
            $kiw_data['tenant_id']  = $storm_tenant;
            $kiw_data['username']   = $username;
            $kiw_data['password']   = $password;


            $kiw_temp = curl_init();

            curl_setopt($kiw_temp, CURLOPT_URL, "http://127.0.0.1:9956");
            curl_setopt($kiw_temp, CURLOPT_POST, true);
            curl_setopt($kiw_temp, CURLOPT_POSTFIELDS, http_build_query($kiw_data));
            curl_setopt($kiw_temp, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($kiw_temp, CURLOPT_TIMEOUT, 10);
            curl_setopt($kiw_temp, CURLOPT_CONNECTTIMEOUT, 10);


            $kiw_login_status = curl_exec($kiw_temp);

            curl_close($kiw_temp);

            unset($kiw_temp);


            $kiw_login_status = json_decode($kiw_login_status, true);


            if($kiw_login_status['success'] != 'success'){

                die(json_encode(array("status" => false, "data" => array("message" => $error_msg['err_active']))));

            }


            unset($kiw_login_status);


        }


        $user_detail = $kiw_db->query_first("SELECT * FROM kiwire_account_auth WHERE username = '{$user_name}' AND tenant_id = '{$sys_cloud_id}' LIMIT 1");

        $kiw_cache->set("{$sys_cloud_id}_ud_{$user_name}", $user_detail, 1800);


    }


    if (!empty($user_detail)){


        // pass controller information

        $kiw_user['nasid']          = $_REQUEST['apserial'];
        $kiw_user['tenant_id']      = $sys_cloud_id;
        $kiw_user['device_vendor']  = "wifidog";

        $kiw_user['username']       = $username;
        $kiw_user['password']       = $password;


        // provide action for this request

        $kiw_user['action'] = "authorize";


        // send request to kiwire service to check if allow to login

        $kiw_temp = curl_init();

        curl_setopt($kiw_temp, CURLOPT_URL, "http://127.0.0.1:9955");
        curl_setopt($kiw_temp, CURLOPT_POST, true);
        curl_setopt($kiw_temp, CURLOPT_POSTFIELDS, http_build_query($kiw_user));
        curl_setopt($kiw_temp, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($kiw_temp, CURLOPT_TIMEOUT, 5);
        curl_setopt($kiw_temp, CURLOPT_CONNECTTIMEOUT, 5);


        $kiw_login_status = curl_exec($kiw_temp);

        curl_close($kiw_temp);


        // decode response from service

        $kiw_login_status = json_decode($kiw_login_status, true);


        if ($kiw_login_status['status'] == "success"){


            $kiw_domain = $_SESSION['controller']['login'];

            if (substr($kiw_domain, 0, 4) != "http") $kiw_domain = "http://" . $kiw_domain;


            // make sure no duplicate token

            foreach (range(0, 9) as $kiw_range) {


                $kiw_token = date("ymd") . strtoupper(substr(md5(time() . rand(0, 9)), 0, 10));

                $kiw_existed = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_active_session WHERE unique_id = '{$kiw_token}' AND tenant_id = '{$sys_cloud_id}' LIMIT 1");

                if ($kiw_existed['kcount'] == "0") break;


            }


            $kiw_cache->set("WD:LOGIN:{$kiw_token}", $kiw_user, 10);


            $total_ol = $kiw_db->query_first("SELECT IFNULL(COUNT(*), 0) AS ccount FROM kiwire_active_session WHERE username = '{$user_name}' AND tenant_id = '{$sys_cloud_id}'");


            $user_data['status'] = true;
            $user_data['data']['user'] = array(
                "fullname" => $user_detail['fullname'],
                "country_code" => "+92",
                "city" => "",
                "service_provider" => "",
                "mobile_num" => $user_detail['username'],
                "is_active" => ($user_detail['status'] == "active" ? true: false),
                "user_id" => $user_detail['username'],
                "access_token" => $kiw_token,
                "subcription_info" => array(
                    "todays_total_volume" => $profile_dproc['max_byte'] . " Mb",
                    "todays_total_duration" => ($profile_dproc['max_time'] / 60) . " Minutes",
                    "todays_remaining_limit" => round($profile_dproc['max_byte'] - ($user_detail['quota_in'] + $user_detail['quota_out']) / 1024 / 1024, 2) . " Mb",
                    "todays_remaining_duration" => round(($profile_dproc['max_time'] - $user_detail['session_time']) / 60, 0) . " Minutes",
                    "package_name" => $profile_info['profile'],
                    "package_id" => "",
                    "allowed_devices" => $profile_dproc['max_user'],
                    "current_active_device" => (empty($total_ol['ccount']) ? "0" : $total_ol['ccount'])
                ));

            die(json_encode($user_data));


        } else {

            die(json_encode(array("status" => false, "data" => array("message" => $kiw_login_status['reply:Reply-Message']))));

        }


    } else {

        die(json_encode(array("status" => false, "data" => array("message" => $error_msg['err_active']))));

    }


}


if ($cn_action == "signup"){


    $user_exist = $kiw_db->query_first("SELECT IFNULL(COUNT(username), 0) AS ccount FROM kiwire_account_auth WHERE username = '{$user_name}' AND tenant_id = '{$sys_cloud_id}'");


    if($user_exist['ccount'] == "0" && !empty($user_name)){


        $profile_info = $kiw_cache->get("{$sys_cloud_id}_ma_profile");

        if (empty($profile_info)) {

            $profile_info = $kiw_db->query_first("SELECT * FROM kiwire_int_sms WHERE tenant_id = '{$sys_cloud_id}' LIMIT 1");

            $kiw_cache->set("{$sys_cloud_id}_ma_profile", $profile_info, 1800);

        }


        $profile_draw = $kiw_cache->get("{$sys_cloud_id}_ma_pdata");

        if (empty($profile_draw)) {

            $profile_draw = $kiw_db->query_first("SELECT * FROM (SELECT * FROM kiwire_profiles WHERE tenant_id = '{$sys_cloud_id}' AND name = '{$profile_info['plan']}' LIMIT 1");

            $profile_draw = json_decode($profile_draw['attribute'], true);

            $kiw_cache->set("{$sys_cloud_id}_ma_pdata", $profile_draw, 1800);

        }


        $profile_dproc = $kiw_cache->get("{$sys_cloud_id}_ma_pproc");

        if (empty($profile_dproc)) {

            foreach ($profile_draw as $attribute){

                if ($attribute['attribute'] == "Kiwire-Total-Bytes")    $profile_dproc['max_byte']      = $attribute['value'];
                if ($attribute['attribute'] == "Max-All-Session")       $profile_dproc['max_time']      = $attribute['value'];
                if ($attribute['attribute'] == "Access-Period")         $profile_dproc['max_time']      = $attribute['value'];
                if ($attribute['attribute'] == "Simultaneous-Use")      $profile_dproc['max_user']      = $attribute['value'];
                if ($attribute['attribute'] == "Session-Timeout")       $profile_dproc['max_time']      = $attribute['value'];

            }

            $profile_dproc['max_byte'] = (empty($profile_dproc['max_byte']) ? 0 : ($profile_dproc['max_byte'] / 1024));

            $kiw_cache->set("{$sys_cloud_id}_ma_pproc", $profile_dproc, 1800);

        }


        if (!empty($profile_dproc)) {


            $temp_pass = substr(preg_replace("/\D+/", "", md5(uniqid("", true))), 2, 4);

            $user_data = $kiw_db->escape(json_encode($_REQUEST));

            $kiw_db->insert("kiwire_temp_registration", array("phoneno" => $user_name, "temp_key" => $temp_pass, "data" => $user_data));


            if (empty($error_msg['err_verification_resent'])) $error_msg['err_verification_resent'] = "Your verification code is:";


            $kiw_sms['content']      = "{$error_msg['err_verification_resent']} {$temp_pass}";
            $kiw_sms['action']       = "send_sms";
            $kiw_sms['tenant_id']    = $sys_cloud_id;
            $kiw_sms['phone_number'] = "+92" . $user_name;

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


            $user_d['status'] = true;
            $user_d['data']['user'] = array(
                "fullname" => $_REQUEST['first_name'] . " " . $_REQUEST['last_name'],
                "country_code" => "+92",
                "city" => "",
                "service_provider" => "",
                "mobile_num" => $_REQUEST['mobile_number'],
                "is_active" => false,
                "user_id" => $_REQUEST['mobile_number'],
                "access_token" => $cn_sec,
                "subcription_info" => array(
                    "todays_total_volume" => $profile_dproc['max_byte'] . " Mb",
                    "todays_total_duration" => $profile_dproc['max_time'] . " Minutes",
                    "todays_remaining_limit" => $profile_dproc['max_byte'] . " Mb",
                    "todays_remaining_duration" => $profile_dproc['max_time'] . " Minutes",
                    "package_name" => $profile_info['plan'],
                    "package_id" => "",
                    "allowed_devices" => $profile_dproc['max_user'],
                    "current_active_device" => ""
                ));


            die(json_encode($user_d));


        } else {


            die(json_encode(array("status" => false, "data" => array("message" => $error_msg['err_noprofile']))));


        }

    } else {

        die(json_encode(array("status" => false, "data" => array("message" => $error_msg['err_user_exist']))));

    }

}

if ($cn_action == "verification"){


    $verification = $kiw_db->escape($_REQUEST['4_digit_code']);

    $data_available = $kiw_db->query_first("SELECT * FROM kiwire_temp_registration WHERE phoneno = '{$user_name}' AND temp_key = '{$verification}' ORDER BY id DESC LIMIT 1");


    if (!empty($data_available)){


        if ($verification == $data_available['temp_key']) {


            $tmp_pass = substr(preg_replace("/\D+/", "", md5(openssl_encrypt($user_name, "des-ecb", "SyNChro*CynNet", 0, "CyBERnet-*NaVeed"))), 2, 4);


            $data_available = json_decode(stripcslashes($data_available['data']));


            $profile_info = $kiw_cache->get("{$sys_cloud_id}_ma_profile");

            if (empty($profile_info)) {

                $profile_info = $kiw_db->query_first("SELECT * FROM kiwire_int_sms WHERE tenant_id = '{$sys_cloud_id}' LIMIT 1");

                $kiw_cache->set("{$sys_cloud_id}_ma_profile", $profile_info, 1800);

            }


            $check_e = $kiw_db->query_first("SELECT COUNT(*) AS ccount FROM kiwire_account_auth WHERE username = '{$user_name}' AND tenant_id = '{$sys_cloud_id}'");

            $kiw_db->query("DELETE FROM kiwire_temp_registration WHERE phoneno = '{$user_name}'");


            if ($check_e['ccount'] == "0") {


                $sms_content = $kiw_cache->get("{$sys_cloud_id}_ma_sms_contents");

                if (empty($sms_content)) {

                    $sms_content = $kiw_db->query_first("SELECT * FROM kiwire_html_template WHERE tenant_id = '{$sys_cloud_id}' AND id = (SELECT template_id FROM kiwire_int_sms WHERE kiwire_int_sms.tenant_id = '{$sys_cloud_id}' LIMIT 1) LIMIT 1");

                    $kiw_cache->set("{$sys_cloud_id}_ma_sms_contents", $sms_content, 1800);

                }


                if (empty($sms_content['msg_content'])) $sms_content['msg_content'] = "Username: {{username}} | Password: {{password}}";


                $sms_content['msg_content'] = strip_tags(html_entity_decode(str_replace("&nbsp;", " ", $sms_content['msg_content'])));
                $sms_content['msg_content'] = str_replace(array("{{username}}", "{{password}}", "{{pass_encode}}", "{{cloud_id}}"), array($user_name, $tmp_pass, $tmp_pass, $sys_cloud_id), $sms_content['msg_content']);


                send_sms("+92" . $user_name, $sms_content['msg_content']);

                adduser($db->escape($data_available->mobile_number), $tmp_pass, $profile_info['plan'], "", $db->escape($data_available->first_name . " " . $data_available->last_name), $profile_info['expiry'], "Mobile Apps");


                die(json_encode(array("status" => true, "data" => array("verification_status" => true, "password" => $tmp_pass))));


            } else {


                die(json_encode(array("status" => false, "data" => array("message" => $error_msg['err_user_exist']))));


            }


        } else {


            die(json_encode(array("status" => false, "data" => array("message" => $error_msg['err_wrongcaptcha']))));


        }


    } else {


        die(json_encode(array("status" => false, "data" => array("message" => $error_msg['err_active']))));


    }

}

if ($cn_action == "resend"){


    $data_available = $kiw_db->query_first("SELECT temp_key FROM kiwire_temp_registration WHERE phoneno = '{$user_name}' ORDER BY id DESC LIMIT 1");


    if (!empty($data_available)){


        if (empty($error_msg['err_verification_resent'])) $error_msg['err_verification_resent'] = "Your verification code is:";

        $kiw_sms['content']      = "{$error_msg['err_verification_resent']} {$data_available['temp_key']}";
        $kiw_sms['action']       = "send_sms";
        $kiw_sms['tenant_id']    = $sys_cloud_id;
        $kiw_sms['phone_number'] = "+92" . $user_name;

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


        die(json_encode(array("status" => true, "data" => array("message" => $error_msg['err_verification_resent']))));


    } else {


        die(json_encode(array("status" => false, "data" => array("message" => "Missing registration data."))));


    }


}


if ($cn_action == "usage"){


    $usage = $kiw_cache->get($sys_cloud_id . "_" . $user_name . "_user_usage");

    if (empty($usage)) {

        $usage = $kiw_db->query_first("SELECT username,quota_in,quota_out,session_time,profile_curr FROM kiwire_account_auth WHERE username = '{$user_name}' AND tenant_id = '{$sys_cloud_id}' LIMIT 1");

        $kiw_cache->set("{$sys_cloud_id}_{$user_name}_user_usage", $usage, 60);

    }


    $profile_info = $kiw_cache->get("{$sys_cloud_id}_ma_profile");

    if (empty($profile_info)) {

        $profile_info = $kiw_db->query_first("SELECT * FROM kiwire_int_sms WHERE tenant_id = '{$sys_cloud_id}' LIMIT 1");

        $kiw_cache->set("{$sys_cloud_id}_ma_profile", $profile_info, 1800);

    }


    if (!empty($usage)) {


        if ($sys_cloud_id == $storm_tenant) {


            $profile_draw = $kiw_cache->get("{$storm_tenant}_ma_pdata_{$usage['profile_curr']}");

            if (empty($profile_draw)) {

                $profile_draw = $kiw_db->query_first("SELECT * FROM kiwire_profiles WHERE name = '{$usage['profile_curr']}' AND tenant_id = '{$storm_tenant}' LIMIT 1");

                $profile_draw = json_decode($profile_draw['attribute'], true);

                $kiw_cache->set("{$storm_tenant}_ma_pdata_{$usage['profile_curr']}", $profile_draw, 1800);

            }


            $profile_dproc = $kiw_cache->get("{$storm_tenant}_ma_pproc_{$usage['profile_curr']}");

            if (empty($profile_dproc)) {

                foreach ($profile_draw as $attribute) {

                    if ($attribute['attribute'] == "Kiwire-Total-Bytes") $profile_dproc['max_byte'] = $attribute['value'];
                    if ($attribute['attribute'] == "Max-All-Session") $profile_dproc['max_time'] = $attribute['value'];
                    if ($attribute['attribute'] == "Access-Period") $profile_dproc['max_time'] = $attribute['value'];
                    if ($attribute['attribute'] == "Simultaneous-Use") $profile_dproc['max_user'] = $attribute['value'];
                    if ($attribute['attribute'] == "Session-Timeout") $profile_dproc['max_time'] = $attribute['value'];

                }

                $profile_dproc['max_byte'] = (empty($profile_dproc['max_byte']) ? 0 : ($profile_dproc['max_byte'] / 1024));

                $kiw_cache->set("{$storm_tenant}_ma_pproc_{$usage['profile_curr']}", $profile_dproc, 1800);

            }


        } else {


            $profile_draw = $kiw_cache->get("{$sys_cloud_id}_ma_pdata_{$usage['profile_curr']}");

            if (empty($profile_draw)) {

                $profile_draw = $kiw_db->fetch_array("SELECT * FROM kiwire_profiles WHERE name = '{$usage['profile_curr']}' AND tenant_id = '' LIMIT 1");

                $kiw_cache->set("{$sys_cloud_id}_ma_pdata_{$usage['profile_curr']}", $profile_draw, 1800);

            }


            $profile_dproc = $kiw_cache->get("{$sys_cloud_id}_ma_pproc_{$usage['profile_curr']}");

            if (empty($profile_dproc)) {

                foreach ($profile_draw as $attribute) {

                    if ($attribute['attribute'] == "Kiwire-Total-Bytes") $profile_dproc['max_byte'] = $attribute['value'];
                    if ($attribute['attribute'] == "Max-All-Session") $profile_dproc['max_time'] = $attribute['value'];
                    if ($attribute['attribute'] == "Access-Period") $profile_dproc['max_time'] = $attribute['value'];
                    if ($attribute['attribute'] == "Simultaneous-Use") $profile_dproc['max_user'] = $attribute['value'];
                    if ($attribute['attribute'] == "Session-Timeout") $profile_dproc['max_time'] = $attribute['value'];

                }


                $profile_dproc['max_byte'] = (empty($profile_dproc['max_byte']) ? 0 : ($profile_dproc['max_byte'] / 1024));

                $kiw_cache->set("{$sys_cloud_id}_ma_pproc_{$usage['profile_curr']}", $profile_dproc, 1800);


            }


        }


        $user_d['status'] = true;
        $user_d['data']['usage'] = array(
            "todays_total_volume" => round($profile_dproc['max_byte'] / 1024, 0) . " Mb",
            "todays_total_duration" => round(($profile_dproc['max_time'] / 60), 0) . " Minutes",
            "todays_remaining_limit" => round(($profile_dproc['max_byte'] - (($usage['quota_in'] + $usage['quota_out']) / 1024)) / 1024, 2) . " Mb",
            "todays_remaining_duration" => round(($profile_dproc['max_time'] - $usage['session_time']) / 60, 0) . " Minutes");

    } else {

        $user_d = array("status" => false, "data" => array("message" => $error_msg['err_active']));

    }


    die(json_encode($user_d));


}

if ($cn_action == "forgot"){


    if (!empty($user_name)){


        $u_password = $kiw_db->query_first("SELECT password,status FROM kiwire_account_auth WHERE username = '{$user_name}' AND tenant_id = '{$sys_cloud_id}' LIMIT 1");


        if ($u_password['status'] != "active") {


            $u_password['password'] = sync_decrypt($u_password['password']);


            if (!empty($u_password['password'])) {


                if (empty($error_msg['err_forgotpassword'])) $error_msg['err_forgotpassword'] = "You password is: ";


                $kiw_sms['content']      = "{$error_msg['err_forgotpassword']} {$u_password['password']}";
                $kiw_sms['action']       = "send_sms";
                $kiw_sms['tenant_id']    = $sys_cloud_id;
                $kiw_sms['phone_number'] = "+92" . $user_name;

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

                die(json_encode(array("status" => true, "data" => array("message" => $error_msg['err_forgotsent']))));


            } else {

                die(json_encode(array("status" => false, "data" => array("message" => $error_msg['err_active']))));

            }


        } else {

            die(json_encode(array("status" => false, "data" => array("message" => $error_msg['err_expire']))));

        }


    } else {

        die(json_encode(array("status" => false, "data" => array("message" => $error_msg['err_active']))));

    }


}


if ($cn_action == "signout"){


    if (!empty($user_name)){


        // get the latest data then mark as terminate

        $kiw_mac_addresses = $kiw_db->fetch_array("SELECT mac_address FROM kiwire_active_session WHERE username = '{$user_name}' AND tenant_id = '{$sys_cloud_id}'");


        foreach ($kiw_mac_addresses as $kiw_mac_address) {

            $kiw_cache->set("WD:DC:{$kiw_mac_address['mac_address']}", ["time" => date("Y-m-d H:i:s"), "disconnected" => true], 600);

        }


        die(json_encode(array("status" => true, "data" => array("message" => $error_msg['err_user_logout']))));


    } else {


        die(json_encode(array("status" => false, "data" => array("message" => $error_msg['err_active']))));


    }


}
