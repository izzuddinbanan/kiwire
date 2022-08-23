<?php


require_once dirname(__FILE__, 2) . "/user/includes/include_session.php";
require_once dirname(__FILE__, 2) . "/user/includes/include_account.php";
require_once dirname(__FILE__, 2) . "/user/includes/include_general.php";

require_once dirname(__FILE__, 2) . "/admin/includes/include_general.php";
require_once dirname(__FILE__, 2) . "/admin/includes/include_connection.php";
require_once dirname(__FILE__, 2) . "/libs/class.sql.helper.php";


$kiw_phone  = $kiw_db->escape($_REQUEST['phone_number']);
$kiw_otp    = $kiw_db->escape($_REQUEST['otp_code']);
$kiw_resend = $kiw_db->escape($_REQUEST['hsbc_otp']);

$kiw_username = $kiw_db->escape($_REQUEST['username']);
$kiw_password = $kiw_db->escape($_REQUEST['password']);


if (!empty($kiw_phone)) {


    $kiw_password = substr(preg_replace('/[^0-9]+/', '', md5(time() . rand(0, 9999))), 0, 6);


    // send the sms to user

    $kiw_signup = $kiw_cache->get("SMS_SIGNUP:{$_SESSION['controller']['tenant_id']}");

    if (empty($kiw_signup)) {


        $kiw_signup = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_int_sms WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

        if (empty($kiw_signup)) $kiw_signup = array("dummy" => true);

        $kiw_cache->set("SMS_SIGNUP:{$_SESSION['controller']['tenant_id']}", $kiw_signup, 1800);


    }


    if ($kiw_signup['enabled'] == "y") {


        if ($kiw_cache->exists("OTP:GENERATED:{$_SESSION['controller']['tenant_id']}:{$kiw_phone}") == true){

            error_redirect($_SERVER['HTTP_REFERER'], "Your OTP has been generated previously");

        }


        // get the template

        $kiw_sms_template = $kiw_cache->get("SMS_TEMPLATE:{$_SESSION['controller']['tenant_id']}:{$kiw_signup['sms_text']}");

        if (empty($kiw_sms_template)) {

            $kiw_sms_template = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_html_template WHERE name = '{$kiw_signup['sms_text']}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

            if (empty($kiw_sms_template)) {

                $kiw_sms_template['content'] = "[ WIFI Login ] Your OTP is {{otp_code}}";

            }

            $kiw_cache->set("SMS_TEMPLATE:{$_SESSION['controller']['tenant_id']}:{$kiw_signup['sms_text']}", $kiw_sms_template, 1800);

        }


        $kiw_sms_template['content'] = stripcslashes($kiw_sms_template['content']);

        $kiw_sms['content'] = str_replace("{{tenant_id}}", $_SESSION['controller']['tenant_id'], $kiw_sms_template['content']);
        $kiw_sms['content'] = str_replace("{{username}}", $kiw_phone, $kiw_sms['content']);
        $kiw_sms['content'] = str_replace("{{otp_code}}", $kiw_password, $kiw_sms['content']);

        $kiw_sms['content'] = strip_tags($kiw_sms['content']);

        $kiw_sms['action']          = "send_sms";
        $kiw_sms['tenant_id']       = $_SESSION['controller']['tenant_id'];
        $kiw_sms['phone_number']    = $kiw_phone;


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


        $kiw_temp = $kiw_db->query_first("SELECT password FROM kiwire_account_auth WHERE username = '{$kiw_phone}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

        $_SESSION['user']['hsbc']['username'] = $kiw_phone;
        $_SESSION['user']['hsbc']['password'] = $kiw_password;

        if (empty($kiw_temp['password'])) {


            $kiw_create_user['tenant_id']       = $_SESSION['controller']['tenant_id'];
            $kiw_create_user['username']        = $kiw_phone;
            $kiw_create_user['password']        = $kiw_password;
            $kiw_create_user['fullname']        = $kiw_phone;
            $kiw_create_user['phone_number']    = $kiw_phone;
	        $kiw_create_user['remark']          = $kiw_signup['remark'];
            $kiw_create_user['profile_subs']    = $kiw_signup['profile'];
            $kiw_create_user['profile_curr']    = $kiw_signup['profile'];
            $kiw_create_user['ktype']           = "account";
            $kiw_create_user['status']          = "suspend";
            $kiw_create_user['integration']     = "int";
            $kiw_create_user['allowed_zone']    = $kiw_signup['allowed_zone'];
            $kiw_create_user['date_value']      = "NOW()";
            $kiw_create_user['date_expiry']     = date("Y-m-d H:i:s", strtotime("+{$kiw_signup['validity']} Day"));

            create_account($kiw_db, $kiw_cache, $kiw_create_user);


            $kiw_user_info['tenant_id']   = $_SESSION['controller']['tenant_id'];
            $kiw_user_info['username']    = $kiw_phone;
            $kiw_user_info['source']      = "system";

            $kiw_db->query(sql_insert($kiw_db, "kiwire_account_info", $kiw_user_info));


        } else {


            $kiw_password = sync_encrypt($kiw_password);

            $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), password = '{$kiw_password}' WHERE username = '{$kiw_phone}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");


        }


        $kiw_cache->set("OTP:SESSION:{$_SESSION['controller']['tenant_id']}:{$kiw_phone}", time(), 600);


        $kiw_next_date = strtotime("+1 Day");

        $kiw_next_date = new DateTime(date("Y-m-d 00:00:00", $kiw_next_date), new DateTimeZone("Asia/Kuala_Lumpur"));

        $kiw_next_date->setTimezone(new DateTimeZone("UTC"));

        $kiw_next_date = $kiw_next_date->getTimestamp();


        $kiw_cache->set("OTP:GENERATED:{$_SESSION['controller']['tenant_id']}:{$kiw_phone}", time(), $kiw_next_date - time());


        $_SESSION['user']['current'] = next_page($_SESSION['user']['journey'], $_SESSION['user']['current'], $_SESSION['user']['default']);


        logger($_SESSION['user']['mac'], "SMS sent to {$kiw_phone}");


        header("Location: /user/pages/?session={$session_id}");


    } else {

        error_redirect($_SERVER['HTTP_REFERER'], "SMS registration has been disabled");

    }


} elseif (!empty($kiw_otp)) {


    if ($kiw_otp == $_SESSION['user']['hsbc']['password']) {


        if ($kiw_cache->exists("OTP:SESSION:{$_SESSION['controller']['tenant_id']}:{$_SESSION['user']['hsbc']['username']}")) {


            if (!empty($_SESSION['user']['hsbc']['username']) && !empty($_SESSION['user']['hsbc']['password'])) {


                $kiw_cache->del("OTP:SESSION:{$_SESSION['controller']['tenant_id']}:{$_SESSION['user']['hsbc']['username']}");

                $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(),  status = 'active' WHERE username = '{$_SESSION['user']['hsbc']['username']}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

                login_user($_SESSION['user']['hsbc']['username'], $_SESSION['user']['hsbc']['password'], $session_id);


            } else error_redirect($_SERVER['HTTP_REFERER'], "Unknown session data");


        } else error_redirect($_SERVER['HTTP_REFERER'], "Your OTP already expired");


    } else error_redirect($_SERVER['HTTP_REFERER'], "Wrong OTP provided");


} elseif ($kiw_resend == "true") {


    if (!empty($_SESSION['user']['hsbc']['username']) && !empty($_SESSION['user']['hsbc']['password'])) {


        $kiw_signup = $kiw_cache->get("SMS_SIGNUP:{$_SESSION['controller']['tenant_id']}");

        if (empty($kiw_signup)) {


            $kiw_signup = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_int_sms WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

            if (empty($kiw_signup)) $kiw_signup = array("dummy" => true);

            $kiw_cache->set("SMS_SIGNUP:{$_SESSION['controller']['tenant_id']}", $kiw_signup, 1800);

        }


        $kiw_sms_template = $kiw_cache->get("SMS_TEMPLATE:{$_SESSION['controller']['tenant_id']}:{$kiw_signup['sms_text']}");

        if (empty($kiw_sms_template)) {

            $kiw_sms_template = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_html_template WHERE name = '{$kiw_signup['sms_text']}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

            if (empty($kiw_sms_template)) {

                $kiw_sms_template['content'] = "[ WIFI Login ] Your OTP is {{otp_code}}";

            }

            $kiw_cache->set("SMS_TEMPLATE:{$_SESSION['controller']['tenant_id']}:{$kiw_signup['sms_text']}", $kiw_sms_template, 1800);

        }


        $kiw_sms_template['content'] = stripcslashes($kiw_sms_template['content']);

        $kiw_sms['content'] = str_replace("{{tenant_id}}", $_SESSION['controller']['tenant_id'], $kiw_sms_template['content']);
        $kiw_sms['content'] = str_replace("{{username}}", $_SESSION['user']['hsbc']['username'], $kiw_sms['content']);
        $kiw_sms['content'] = str_replace("{{otp_code}}", $_SESSION['user']['hsbc']['password'], $kiw_sms['content']);

        $kiw_sms['content'] = strip_tags($kiw_sms['content']);

        $kiw_sms['action'] = "send_sms";
        $kiw_sms['tenant_id'] = $_SESSION['controller']['tenant_id'];
        $kiw_sms['phone_number'] = $_SESSION['user']['hsbc']['username'];


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


        error_redirect($_SERVER['HTTP_REFERER'], "OTP has been resent to {$_SESSION['user']['hsbc']['username']}");


    } else error_redirect($_SERVER['HTTP_REFERER'], "Unknown session data");


} elseif (!empty($kiw_username) && !empty($kiw_password)){


    $kiw_temp = $kiw_db->query_first("SELECT username,password,status FROM kiwire_account_auth WHERE username = '{$kiw_username}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");


    if (!empty($kiw_temp['password']) && sync_decrypt($kiw_temp['password']) == $kiw_password) {


        $kiw_cache->del("OTP:SESSION:{$_SESSION['controller']['tenant_id']}:{$kiw_username}");

        if ($kiw_temp['status'] !== "active") {

            $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(),  status = 'active' WHERE username = '{$kiw_username}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

        }

        login_user($kiw_username, $kiw_password, $session_id);


    } else error_redirect($_SERVER['HTTP_REFERER'], "Wrong credential has been provided");


} else {

    error_redirect($_SERVER['HTTP_REFERER'], "No phone number provided");

}