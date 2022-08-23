<?php

require_once dirname(__FILE__, 3) . "/includes/include_session.php";
require_once dirname(__FILE__, 3) . "/includes/include_general.php";
require_once dirname(__FILE__, 4) . "/admin/includes/include_connection.php";
require_once dirname(__FILE__, 4) . "/admin/includes/include_general.php";


// check request made for this page actually follow the flow

if ($_SESSION['system']['checked'] == true){


     unset($_SESSION['system']['checked']);


} else {


    $_SESSION['response']['error'] = "You are not allowed to access this module";

    die(json_encode(array("status" => "error", "message" => "You are not allowed to access this module")));


}


// set the response header to json

header("Content-Type: application/json");


$kiw_user['username'] = $_SESSION['check']['username'];
$kiw_user['password'] = $_SESSION['check']['password'];


// remove check session so only once can do checking

unset($_SESSION['check']);


$kiw_notification = $kiw_cache->get("NOTIFICATION_DATA:{$_SESSION['controller']['tenant_id']}");

if (empty($kiw_notification)) {


    $kiw_notification = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_notification WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

    if (empty($kiw_notification)) $kiw_notification = array("dummy" => true);

    $kiw_cache->set("NOTIFICATION_DATA:{$_SESSION['controller']['tenant_id']}", $kiw_notification, 1800);


}


if (empty($kiw_user['username']) || empty($kiw_user['password'])){

    $_SESSION['response']['error'] = "[999] Invalid username or password";

    die(json_encode(array("status" => "error", "message" => "[999] Invalid username or password")));

}


$kiw_policies = $kiw_cache->get("POLICIES_DATA:{$_SESSION['controller']['tenant_id']}");

if (empty($kiw_policies)) {


    $kiw_policies = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_policies WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

    if (empty($kiw_policies)) $kiw_policies = array("dummy" => true);

    $kiw_cache->set("POLICIES_DATA:{$_SESSION['controller']['tenant_id']}", $kiw_policies, 1800);


}


// include custom script if available

$kiw_custom = dirname(__FILE__, 4) . "/custom/{$_SESSION['controller']['tenant_id']}/user/login.php";

if (file_exists($kiw_custom) == true){

    include_once $kiw_custom;

}


// cybernet customization for redirect user if issue

$kiw_checks = $kiw_db->query_first("SELECT * FROM kiwire_account_auth WHERE username = '{$kiw_user['username']}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

if (empty($kiw_checks)){


    // get the registration page id

    $kiw_page_id = $kiw_cache->get("REGPAGE:{$_SESSION['controller']['tenant_id']}");

    if (empty($kiw_page_id)) {

        $kiw_page_id = $kiw_db->query_first("SELECT unique_id FROM kiwire_login_pages WHERE page_name LIKE '%{$_SESSION['controller']['tenant_id']}%-page2%' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

        $kiw_cache->set("REGPAGE:{$_SESSION['controller']['tenant_id']}", $kiw_page_id, 1800);

    }


    if (!empty($kiw_page_id['unique_id'])){

        $_SESSION['user']['current'] = $kiw_page_id['unique_id'];

    }

    // redirect to registration page

    $_SESSION['response']['error'] = "Your Account Does Not Exist! Please Sign Up";

    die(json_encode(array("status" => "error", "message" => $_SESSION['response']['error'])));


}

unset($kiw_checks);


// pass user information

$kiw_user['macaddress']  = $_SESSION['user']['mac'];
$kiw_user['ipaddress']   = $_SESSION['user']['ip'];
$kiw_user['ipv6address'] = $_SESSION['user']['ipv6'];
$kiw_user['zone']        = $_SESSION['user']['zone'];


// pass controller information

$kiw_user['nasid']          = $_SESSION['controller']['id'];
$kiw_user['tenant_id']      = $_SESSION['controller']['tenant_id'];
$kiw_user['device_vendor']  = $_SESSION['controller']['type'];


// provide action for this request

$kiw_user['action'] = "authorize";

$kiw_user['system'] = $_SESSION['user']['system'];
$kiw_user['class']  = $_SESSION['user']['class'];
$kiw_user['brand']  = $_SESSION['user']['brand'];
$kiw_user['model']  = $_SESSION['user']['model'];


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


// trigger controller to login the device based on type

if (strlen($kiw_login_status['control:Cleartext-Password']['value']) > 0){


    $kiw_time = date("YmdH");


    // save username in session for future use like campaign

    $_SESSION['user']['login']['username'] = $kiw_user['username'];


    // reset error count for future

    unset($_SESSION['user']['login']['ecount']);


    // save cookies for future login

    if ($kiw_policies['cookies_login'] == "y") {


        $kiw_cookie_encrypted = sync_encrypt(base64_encode("{$kiw_user['username']}||{$kiw_user['password']}||{$_SESSION['controller']['tenant_id']}"));

        setcookie("smart_wifi_login", $kiw_cookie_encrypted, time() + ($kiw_policies['cookies_login_validity'] * 86400), "/");


    }


    if ($_SESSION['controller']['type'] == "mikrotik"){


        echo json_encode(array(
            "status"   => "success",
            "message"  => null,
            "action"   => $_SESSION['controller']['login'],
            "method"   => "post",
            "data"     => [
                array("type" => "text",
                      "name" => "username",
                      "value" => $kiw_user['username']
                ),
                array("type" => "password",
                      "name" => "password",
                      "value" => $kiw_user['password']
                ),
                array("type" => "text",
                      "name" => "dst",
                      "value" => $_SESSION['user']['destination']
                )
            ]
        ));


    } elseif ($_SESSION['controller']['type'] == "meraki"){


        echo json_encode(array(
            "status"   => "success",
            "message"  => null,
            "action"   => $_SESSION['controller']['login'],
            "method"   => "post",
            "data"     => [
                array("type" => "text",
                      "name" => "username",
                      "value" => $kiw_user['username']
                ),
                array("type" => "password",
                      "name" => "password",
                      "value" => $kiw_user['password']
                ),
                array("type" => "text",
                      "name" => "success_url",
                      "value" => $_SESSION['user']['destination']
                )
            ]
        ));


    } elseif ($_SESSION['controller']['type'] == "cisco_wlc"){


        echo json_encode(array(
            "status"   => "success",
            "message"  => null,
            "action"   => $_SESSION['controller']['login'],
            "method"   => "post",
            "data"     => [
                array("type" => "text",
                      "name" => "username",
                      "value" => $kiw_user['username']
                ),
                array("type" => "password",
                      "name" => "password",
                      "value" => $kiw_user['password']
                ),
                array("type" => "text",
                      "name" => "dst",
                      "value" => $_SESSION['user']['destination']
                )
            ]
        ));


    } elseif ($_SESSION['controller']['type'] == "motorola"){


        echo json_encode(array(
            "status"   => "success",
            "message"  => null,
            "action"   => $_SESSION['controller']['login'],
            "method"   => "post",
            "data"     => [
                array("type" => "text",
                      "name" => "username",
                      "value" => $kiw_user['username']
                ),
                array("type" => "password",
                      "name" => "password",
                      "value" => $kiw_user['password']
                ),
                array("type" => "text",
                      "name" => "dst",
                      "value" => $_SESSION['user']['destination']
                )
            ]
        ));


    } elseif ($_SESSION['controller']['type'] == "aruba"){


        echo json_encode(array(
            "status"   => "success",
            "message"  => null,
            "action"   => $_SESSION['controller']['login'],
            "method"   => "post",
            "data"     => [
                array("type" => "text",
                      "name" => "username",
                      "value" => $kiw_user['username']
                ),
                array("type" => "password",
                      "name" => "password",
                      "value" => $kiw_user['password']
                ),
                array("type" => "text",
                      "name" => "dst",
                      "value" => $_SESSION['user']['destination']
                )
            ]
        ));


    } elseif ($_SESSION['controller']['type'] == "chillispot"){


        echo json_encode(array(
            "status"   => "success",
            "message"  => null,
            "action"   => $_SESSION['controller']['login'],
            "method"   => "post",
            "data"     => [
                array("type" => "text",
                      "name" => "username",
                      "value" => $kiw_user['username']
                ),
                array("type" => "password",
                      "name" => "password",
                      "value" => $kiw_user['password']
                ),
                array("type" => "text",
                      "name" => "dst",
                      "value" => $_SESSION['user']['destination']
                )
            ]
        ));


    } elseif ($_SESSION['controller']['type'] == "xirrus"){


        echo json_encode(array(
            "status"   => "success",
            "message"  => null,
            "action"   => $_SESSION['controller']['login'],
            "method"   => "post",
            "data"     => [
                array("type" => "text",
                      "name" => "username",
                      "value" => $kiw_user['username']
                ),
                array("type" => "password",
                      "name" => "password",
                      "value" => $kiw_user['password']
                ),
                array("type" => "text",
                      "name" => "dst",
                      "value" => $_SESSION['user']['destination']
                )
            ]
        ));


    } elseif ($_SESSION['controller']['type'] == "nomadix"){


        echo json_encode(array(
            "status"   => "success",
            "message"  => null,
            "action"   => $_SESSION['controller']['login'],
            "method"   => "post",
            "data"     => [
                array("type" => "text",
                      "name" => "username",
                      "value" => $kiw_user['username']
                ),
                array("type" => "password",
                      "name" => "password",
                      "value" => $kiw_user['password']
                ),
                array("type" => "text",
                      "name" => "dst",
                      "value" => $_SESSION['user']['destination']
                )
            ]
        ));


    } elseif ($_SESSION['controller']['type'] == "ruckus_ap"){


        echo json_encode(array(
            "status"   => "success",
            "message"  => null,
            "action"   => "http://{$_SESSION['controller']['login']}:9997/SubscriberPortal/hotspotlogin",
            "method"   => "post",
            "data"     => [
                array("type" => "text",
                    "name" => "username",
                    "value" => $kiw_user['username']
                ),
                array("type" => "password",
                    "name" => "password",
                    "value" => $kiw_user['password']
                ),
                array("type" => "text",
                    "name" => "url",
                    "value" => $_SESSION['user']['destination']
                ),
                array("type" => "text",
                    "name" => "proxy",
                    "value" => $_SESSION['controller']['proxy']
                ),
                array("type" => "text",
                    "name" => "uip",
                    "value" => $_SESSION['user']['ip']
                ),
                array("type" => "text",
                    "name" => "client-mac",
                    "value" => $_SESSION['user']['mac']
                )
            ]
        ));


    } elseif (in_array($_SESSION['controller']['type'], array("ruckus_vsz", "ruckus_scg"))){


        // send instruction to ruckus first and redirect user

        $kiw_request = array(
            "Vendor"            => "ruckus",
            "RequestPassword"   => $_SESSION['controller']['password'],
            "APIVersion"        => "1.0",
            "RequestCategory"   => "UserOnlineControl",
            "RequestType"       => "Login",
            "UE-IP"             => $_SESSION['user']['ip'],
            "UE-MAC"            => $_SESSION['user']['mac'],
            "UE-Proxy"          => "0",
            "UE-Username"       => $kiw_user['username'],
            "UE-Password"       => $kiw_user['password']
        );


        $kiw_curl = curl_init("http://{$_SESSION['controller']['login']}:9080/portalintf");

        curl_setopt($kiw_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($kiw_curl, CURLOPT_POST, true);
        curl_setopt($kiw_curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($kiw_curl, CURLOPT_POSTFIELDS, json_encode($kiw_request));
        curl_setopt($kiw_curl, CURLOPT_TIMEOUT, 5);
        curl_setopt($kiw_curl, CURLOPT_CONNECTTIMEOUT, 5);

        $kiw_temp = json_decode(curl_exec($kiw_curl), true);


        curl_close($kiw_curl);


        if (strtolower($kiw_temp['ReplyMessage']) == "login succeeded") {


            echo json_encode(array(
                "status" => "success",
                "message" => null,
                "action" => $_SESSION['user']['destination'],
                "method" => "get",
                "data" => []
            ));


        } else {


            $_SESSION['response']['error'] = $kiw_temp['ReplyMessage'];

            echo json_encode(array("status" => "failed", "message" => $kiw_temp['ReplyMessage'], "data" => null));


        }


    } elseif ($_SESSION['controller']['type'] == "fortiap"){

        echo json_encode(array(
            "status"   => "success",
            "message"  => null,
            "action"   => $_SESSION['controller']['login'],
            "method"   => "post",
            "data"     => [
                array("type" => "text",
                      "name" => "username",
                      "value" => $kiw_user['username']
                ),
                array("type" => "password",
                      "name" => "password",
                      "value" => $kiw_user['password']
                ),
                array("type" => "text",
                      "name" => "dst",
                      "value" => $_SESSION['user']['destination']
                )
            ]
        ));

    } elseif ($_SESSION['controller']['type'] == "fortigate"){

        echo json_encode(array(
            "status"   => "success",
            "message"  => null,
            "action"   => $_SESSION['controller']['login'],
            "method"   => "post",
            "data"     => [
                array("type" => "text",
                      "name" => "username",
                      "value" => $kiw_user['username']
                ),
                array("type" => "password",
                      "name" => "password",
                      "value" => $kiw_user['password']
                ),
                array("type" => "text",
                      "name" => "dst",
                      "value" => $_SESSION['user']['destination']
                )
            ]
        ));

    } elseif ($_SESSION['controller']['type'] == "cambium"){

        echo json_encode(array(
            "status"   => "success",
            "message"  => null,
            "action"   => "http://{$_SESSION['controller']['login']}:880/cgi-bin/hotspot_login.cgi?ga_Qv={$_SESSION['controller']['qv']}&ga_orig_url={$_SESSION['user']['destination']}",
            "method"   => "post",
            "data"     => [
                array("type" => "text",
                      "name" => "ga_user",
                      "value" => $kiw_user['username']
                ),
                array("type" => "password",
                      "name" => "ga_pass",
                      "value" => $kiw_user['password']
                )
            ]
        ));

    } elseif ($_SESSION['controller']['type'] == "cmcc"){

        echo json_encode(array(
            "status"   => "success",
            "message"  => null,
            "action"   => $_SESSION['controller']['login'],
            "method"   => "post",
            "data"     => [
                array("type" => "text",
                      "name" => "username",
                      "value" => $kiw_user['username']
                ),
                array("type" => "password",
                      "name" => "password",
                      "value" => $kiw_user['password']
                ),
                array("type" => "text",
                      "name" => "dst",
                      "value" => $_SESSION['user']['destination']
                )
            ]
        ));

    } elseif ($_SESSION['controller']['type'] == "huawei"){

        echo json_encode(array(
            "status"   => "success",
            "message"  => null,
            "action"   => $_SESSION['controller']['login'],
            "method"   => "post",
            "data"     => [
                array("type" => "text",
                      "name" => "username",
                      "value" => $kiw_user['username']
                ),
                array("type" => "password",
                      "name" => "password",
                      "value" => $kiw_user['password']
                ),
                array("type" => "text",
                      "name" => "dst",
                      "value" => $_SESSION['user']['destination']
                )
            ]
        ));

    } elseif ($_SESSION['controller']['type'] == "sundray"){

        echo json_encode(array(
            "status"   => "success",
            "message"  => null,
            "action"   => $_SESSION['controller']['login'],
            "method"   => "post",
            "data"     => [
                array("type" => "text",
                      "name" => "username",
                      "value" => $kiw_user['username']
                ),
                array("type" => "password",
                      "name" => "password",
                      "value" => $kiw_user['password']
                ),
                array("type" => "text",
                      "name" => "dst",
                      "value" => $_SESSION['user']['destination']
                )
            ]
        ));

    } elseif (in_array($_SESSION['controller']['type'], array("wifidog", "rwifidog"))){


        $kiw_domain = $_SESSION['controller']['login'];

        if (substr($kiw_domain, 0, 4) != "http") $kiw_domain = "http://" . $kiw_domain;


        // make sure no duplicate token

        foreach (range(0, 9) as $kiw_range) {


            $kiw_token = strtolower(date("ymd") . substr(bin2hex(random_bytes(10)), random_int(1, 10), 10));

            $kiw_existed = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_active_session WHERE unique_id = '{$kiw_token}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

            if ($kiw_existed['kcount'] == "0") break;


        }


        $kiw_cache->set("WD:LOGIN:{$_SESSION['controller']['tenant_id']}:{$kiw_token}", $kiw_user, 10);
        $kiw_cache->set("WD:PORTAL:{$_SESSION['user']['mac']}", $_SESSION['user']['destination'], 10);

        echo json_encode(array(
            "status"   => "success",
            "message"  => null,
            "action"   => $kiw_domain,
            "method"   => "get",
            "data"     => [
                array("type" => "text",
                      "name" => "token",
                      "value" => $kiw_token,
                )
            ]
        ));


    } elseif ($_SESSION['controller']['type'] == "ubnt"){


        echo json_encode(array(
            "status"   => "success",
            "message"  => null,
            "action"   => $_SESSION['controller']['login'],
            "method"   => "post",
            "data"     => [
                array("type" => "text",
                      "name" => "username",
                      "value" => $kiw_user['username']
                ),
                array("type" => "password",
                      "name" => "password",
                      "value" => $kiw_user['password']
                ),
                array("type" => "text",
                      "name" => "dst",
                      "value" => $_SESSION['user']['destination']
                )
            ]
        ));


    } elseif ($_SESSION['controller']['type'] == "sonicwall"){

        echo json_encode(array(
            "status"   => "success",
            "message"  => null,
            "action"   => $_SESSION['controller']['login'],
            "method"   => "post",
            "data"     => [
                array("type" => "text",
                      "name" => "username",
                      "value" => $kiw_user['username']
                ),
                array("type" => "password",
                      "name" => "password",
                      "value" => $kiw_user['password']
                ),
                array("type" => "text",
                      "name" => "dst",
                      "value" => $_SESSION['user']['destination']
                )
            ]
        ));

    }


    if ($kiw_cache->exists("RETRY_ERROR:COUNT:{$_SESSION['controller']['tenant_id']}:{$kiw_user['username']}") == true) {


        $kiw_cache->del("RETRY_ERROR:COUNT:{$_SESSION['controller']['tenant_id']}:{$kiw_user['username']}");

        $kiw_cache->del("LOGIN_ATTEMP:{$_SESSION['controller']['tenant_id']}:{$kiw_user['username']}");


    }


} else {


    logger($_SESSION['user']['mac'], $kiw_login_status['reply:Reply-Message'], $_SESSION['controller']['tenant_id']);


    // only count if password wrong

    if ($kiw_policies['password_attempts'] == "y") {


        if ($kiw_notification['error_wrong_credential'] == $kiw_login_status['reply:Reply-Message']) {


            $kiw_error_count = $kiw_cache->get("RETRY_ERROR:COUNT:{$_SESSION['controller']['tenant_id']}:{$kiw_user['username']}");


            if ($kiw_error_count > 6) {


                $kiw_cache->del("RETRY_ERROR:COUNT:{$_SESSION['controller']['tenant_id']}:{$kiw_user['username']}");

                $kiw_cache->del("LOGIN_ATTEMP:{$_SESSION['controller']['tenant_id']}:{$kiw_user['username']}");


                $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), status = 'suspend' WHERE username = '{$kiw_user['username']}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");


                $kiw_login_status['reply:Reply-Message'] = $kiw_notification['error_password_too_much_retries'];


            } else {


                $kiw_cache->incr("RETRY_ERROR:COUNT:{$_SESSION['controller']['tenant_id']}:{$kiw_user['username']}");

                $kiw_cache->expire("RETRY_ERROR:COUNT:{$_SESSION['controller']['tenant_id']}:{$kiw_user['username']}", 86400);


            }


        }


    }


    $_SESSION['response']['error'] = $kiw_login_status['reply:Reply-Message'];

    die(json_encode(array("status" => "error", "message" => $kiw_login_status['reply:Reply-Message'])));


}
