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



// slow down brute force if same session try to login multiple time

if ($_SESSION['control']['login_count'] > 2){

    sleep(5);

} else $_SESSION['control']['login_count']++;


// check previous similarity, if high then slow down 5 mins or event blocked

$kiw_username_count = count($_SESSION['control']['login_previous']);

if ($kiw_username_count > 0){


    if ($kiw_username_count > 2){


        sleep(5);

        array_shift($_SESSION['control']['login_previous']);

        // check_logger("WARNING: Possible brute-force [ more than 2 accounts per session ]", $_SESSION['controller']['tenant_id']);


    }


    foreach ($_SESSION['control']['login_previous'] as $kiw_previous) {


        similar_text($kiw_user['username'], $kiw_previous, $kiw_percentage);


        if ($kiw_percentage > 60 && $kiw_percentage < 100) {


            sleep(5);

            // check_logger("WARNING: Possible brute-force [ try to login with similar account {$kiw_user['username']} ]", $_SESSION['controller']['tenant_id']);


        }


    }


}


if (!in_array($kiw_user['username'], $_SESSION['control']['login_previous'])) {

    $_SESSION['control']['login_previous'][] = $kiw_user['username'];

}




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

    if ($kiw_policies['cookies_login'] == "y" || ($kiw_policies['remember_me'] == "y" && $_SESSION['user']['remember_me'] == "y")) {


        $kiw_cookie_encrypted = sync_encrypt(base64_encode("{$kiw_user['username']}||{$kiw_user['password']}||{$_SESSION['controller']['tenant_id']}"));

        setcookie("smart-wifi-login", $kiw_cookie_encrypted, time() + ($kiw_policies['cookies_login_validity'] * 86400), "/");

        unset($_SESSION['user']['remember_me']);


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
                      "name" => "redirect_url",
                      "value" => $_SESSION['user']['destination']
                ),
                array("type" => "text",
                    "name" => "buttonClicked",
                    "value" => 4
                ),
                array("type" => "text",
                    "name" => "err_flag",
                    "value" => 0
                )
            ]
        ));


    } elseif ($_SESSION['controller']['type'] == "motorola"){


        echo json_encode(array(
            "status"   => "success",
            "message"  => null,
            "action"   => "http://{$_SESSION['controller']['login']}:880/cgi-bin/hslogin.cgi",
            "method"   => "post",
            "data"     => [
                array("type" => "text",
                      "name" => "f_user",
                      "value" => $kiw_user['username']
                ),
                array("type" => "password",
                      "name" => "f_pass",
                      "value" => $kiw_user['password']
                ),
                array("type" => "text",
                      "name" => "f_hs_server",
                      "value" => $_SESSION['controller']['login']
                ),
                array("type" => "text",
                    "name" => "f_Qv",
                    "value" => $_SESSION['controller']['qv']
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


    } elseif ($_SESSION['controller']['type'] == "aruba_os"){


        // if (substr($_SESSION['controller']['login'], 0,37) == "https://securelogin.arubanetworks.com") $linkloginonly = "https://securelogin.arubanetworks.com/swarm.cgi";
        // else $linkloginonly = "https://{$_SESSION['controller']['login']}/auth/index.html/u";

        $linkloginonly = $kiw_db->query_first("SELECT SQL_CACHE device_ip FROM kiwire_controller WHERE unique_id = '{$_SESSION['controller']['id']}' LIMIT 1");
        $linkloginonly = "https://{$linkloginonly['device_ip']}/cgi-bin/login/";

        echo json_encode(array(
            "status"   => "success",
            "message"  => null,
            "action"   => $linkloginonly,
            "method"   => "post",
            "data"     => [
                array("type" => "text",
                      "name" => "user",
                      "value" => $kiw_user['username']
                ),
                array("type" => "password",
                      "name" => "password",
                      "value" => $kiw_user['password']
                ),
                array("type" => "text",
                      "name" => "dst",
                      "value" => $_SESSION['user']['destination']
                ),
                array("type" => "text",
                      "name" => "cmd",
                      "value" => "authenticate"
                )
            ]
        ));


    } elseif ($_SESSION['controller']['type'] == "chillispot"){


        // reference : https://help.ubuntu.com/community/WifiDocs/ChillispotHotspot


        $kiw_uam_secret = $kiw_db->query_first("SELECT SQL_CACHE shared_secret FROM kiwire_controller WHERE unique_id = '{$_SESSION['controller']['id']}' LIMIT 1");

        $kiw_uam_secret = $kiw_uam_secret['shared_secret'];


        $kiw_challenge_hex = pack("H32", $_SESSION['user']['challenge']);

        $kiw_challenge_new = $kiw_uam_secret ? pack("H*", md5($kiw_challenge_hex . $kiw_uam_secret)) : $kiw_challenge_hex;


        $kiw_user_password = pack("a32", $kiw_user['password']);

        $kiw_user_password = implode ('', unpack("H32", ($kiw_user_password ^ $kiw_challenge_new)));


        echo json_encode(array(
            "status"   => "success",
            "message"  => null,
            "action"   => $_SESSION['controller']['login'],
            "method"   => "get",
            "data"     => [
                array("type" => "text",
                      "name" => "username",
                      "value" => $kiw_user['username']
                ),
                array("type" => "password",
                      "name" => "password",
                      "value" => $kiw_user_password
                )
            ]
        ));


    } elseif ($_SESSION['controller']['type'] == "xirrus"){


        $kiw_uam_secret = $_SESSION['controller']['id'];

        $kiw_challenge = $_SESSION['user']['challenge'];


        $kiw_challenge_hex = pack ("H32", $kiw_challenge);

        $kiw_challenge_new = pack ("H*", md5($kiw_challenge_hex . $kiw_uam_secret));


        $kiw_user_password = md5("\0" . $kiw_user['password'] . $kiw_challenge_new);


        echo json_encode(array(
            "status"   => "success",
            "message"  => null,
            "action"   => $_SESSION['controller']['login'],
            "method"   => "get",
            "data"     => [
                array("type" => "text",
                      "name" => "username",
                      "value" => $kiw_user['username']
                ),
                array("type" => "text",
                      "name" => "response",
                      "value" => $kiw_user_password
                ),
                array("type" => "text",
                      "name" => "userurl",
                      "value" => $_SESSION['user']['destination']
                )
            ]
        ));


    } elseif ($_SESSION['controller']['type'] == "nomadix"){


        echo json_encode(array(
            "status"   => "success",
            "message"  => null,
            "action"   => "http://login.nomadix.com:1111/usg/process",
            "method"   => "get",
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
                    "name" => "OS",
                    "value" => $_SESSION['user']['destination']
                )
            ]
        ));


    } elseif ($_SESSION['controller']['type'] == "nomadix_xml"){


        $kiw_user_mac = str_replace(array(":", "-"), "", $_SESSION['user']['mac']);


        $kiw_gateway = $kiw_cache->get("NOMADIX_DATA:{$_SESSION['controller']['tenant_id']}:{$_SESSION['controller']['id']}");

        if (empty($kiw_gateway)) {

            $kiw_gateway = $kiw_db->query_first("SELECT * FROM kiwire_controller WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' AND unique_id = '{$_SESSION['controller']['id']}' LIMIT 1");

            if (empty($kiw_gateway)) $kiw_gateway = array("dummy" => true);

            $kiw_cache->set("NOMADIX_DATA:{$_SESSION['controller']['tenant_id']}:{$_SESSION['controller']['id']}", $kiw_gateway, 1800);

        }


        $kiw_nomadix_xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\" ?><USG></USG>");

        $kiw_nomadix_xml->addAttribute("COMMAND", "RADIUS_LOGIN");

        $kiw_nomadix_xml->addChild("SUB_USER_NAME", $kiw_user['username']);
        $kiw_nomadix_xml->addChild("SUB_PASSWORD", $kiw_user['password']);
        $kiw_nomadix_xml->addChild("SUB_MAC_ADDR", $kiw_user_mac);
        $kiw_nomadix_xml->addChild("PORTAL_SUB_ID", $kiw_user['zone']);

        $kiw_connection = curl_init();

        curl_setopt($kiw_connection, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($kiw_connection, CURLOPT_URL, "http://{$kiw_gateway['device_ip']}/usg/command.xml");
        curl_setopt($kiw_connection, CURLOPT_POSTFIELDS, $kiw_nomadix_xml->asXML());
        curl_setopt($kiw_connection, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($kiw_connection, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($kiw_connection, CURLOPT_TIMEOUT, 10);

        unset($kiw_nomadix_xml);

        $kiw_response = curl_exec($kiw_connection);

        $kiw_error = curl_errno($kiw_connection);


        curl_close($kiw_connection);


        if ($kiw_error == 0) {


            $kiw_response = simplexml_load_string($kiw_response);


            if ($kiw_response['RESULT'] == "OK") {


                sleep(5);


                $kiw_nomadix_xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\" ?><USG></USG>");

                $kiw_nomadix_xml->addAttribute("COMMAND", "SET_BANDWIDTH_DOWN");
                $kiw_nomadix_xml->addAttribute("SUBSCRIBER", $kiw_user_mac);
                $kiw_nomadix_xml->addChild("BANDWIDTH_DOWN", round($kiw_login_status['reply:WISPr-Bandwidth-Max-Down'] / 1024, 0, PHP_ROUND_HALF_UP));

                $kiw_connection = curl_init();


                // set the default ip address

                curl_setopt($kiw_connection, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
                curl_setopt($kiw_connection, CURLOPT_URL, "http://{$kiw_gateway['device_ip']}/usg/command.xml");
                curl_setopt($kiw_connection, CURLOPT_POSTFIELDS, $kiw_nomadix_xml->asXML());
                curl_setopt($kiw_connection, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($kiw_connection, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($kiw_connection, CURLOPT_TIMEOUT, 10);

                unset($kiw_nomadix_xml);

                curl_exec($kiw_connection);
                curl_close($kiw_connection);


                sleep(2);


                // set max upload speed

                $kiw_nomadix_xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\" ?><USG></USG>");

                $kiw_nomadix_xml->addAttribute("COMMAND", "SET_BANDWIDTH_UP");
                $kiw_nomadix_xml->addAttribute("SUBSCRIBER", $kiw_user_mac);
                $kiw_nomadix_xml->addChild("BANDWIDTH_UP", round($kiw_login_status['reply:WISPr-Bandwidth-Max-Up'] / 1024, 0, PHP_ROUND_HALF_UP));

                $kiw_connection = curl_init();


                // set the default ip address

                curl_setopt($kiw_connection, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
                curl_setopt($kiw_connection, CURLOPT_URL, "http://{$kiw_gateway['device_ip']}/usg/command.xml");
                curl_setopt($kiw_connection, CURLOPT_POSTFIELDS, $kiw_nomadix_xml->asXML());
                curl_setopt($kiw_connection, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($kiw_connection, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($kiw_connection, CURLOPT_TIMEOUT, 10);

                unset($kiw_nomadix_xml);

                curl_exec($kiw_connection);
                curl_close($kiw_connection);


                echo json_encode(array(
                    "status" => "success",
                    "message" => null,
                    "action" => $_SESSION['user']['destination'],
                    "method" => "get",
                    "data" => [
                        array("type" => "text",
                            "name" => "from",
                            "value" => "captive"
                        )
                    ]
                ));


            } else {


                $_SESSION['response']['error'] = $kiw_response['RESULT'];

                echo json_encode(array("status" => "failed", "message" => $_SESSION['response']['error'], "data" => null));


            }


        } else {


            $_SESSION['response']['error'] = "Unable to connect to Gateway";

            echo json_encode(array("status" => "failed", "message" => $_SESSION['response']['error'], "data" => null));


        }


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
                array("type" => "hidden",
                    "name" => "magic",
                    "value" => $_SESSION['controller']['magic']
                ),
                array("type" => "hidden",
                      "name" => "username",
                      "value" => $kiw_user['username']
                ),
                array("type" => "hidden",
                      "name" => "password",
                      "value" => $kiw_user['password']
                )
            ]
        ));


    } elseif ($_SESSION['controller']['type'] == "fortigate"){


        $kiw_data = http_build_query(
            array(
                "userid"        => $kiw_user['username'],
                "password"      => $kiw_user['password'],
                "station_mac"   => $_SESSION['user']['mac'],
                "station_ip"    => $_SESSION['user']['ip'],
            )
        );


        $kiw_connection = curl_init();

        curl_setopt($kiw_connection, CURLOPT_URL, $_SESSION['controller']['login']);
        curl_setopt($kiw_connection, CURLOPT_HEADER, array("Content-type: application/x-www-form-urlencoded"));
        curl_setopt($kiw_connection, CURLOPT_POSTFIELDS, $kiw_data);
        curl_setopt($kiw_connection, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($kiw_connection, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($kiw_connection, CURLOPT_TIMEOUT, 10);

        unset($kiw_data);


        $kiw_response = curl_exec($kiw_connection);

        $kiw_error = curl_errno($kiw_connection);


        $kiw_response = simplexml_load_string($kiw_response);


        if (is_object($kiw_response) && $kiw_response->status == "success") {


            echo json_encode(array(
                "status" => "success",
                "message" => null,
                "action" => $_SESSION['user']['destination'],
                "method" => "post",
                "data" => [
                    array("type" => "text",
                        "name" => "from",
                        "value" => "captive"
                    )
                ]
            ));


        } else {


            $_SESSION['response']['error'] = "Internal Error. Please try again.";

            header("Location: /user/pages/?session={$_GET['session']}");

            die();


        }

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

    } elseif ($_SESSION['controller']['type'] == "huawei-nce"){

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

    } elseif ($_SESSION['controller']['type'] == "huawei-cloud-ugw"){

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


    } elseif ($_SESSION['controller']['type'] == "engenius"){


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
                    "name" => "userurl",
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



        $kiw_session = base64_encode("username={$kiw_user['username']}&password={$kiw_user['password']}&kiwire={$_SERVER['HTTP_HOST']}&session={$_GET['session']}&tenant={$_SESSION['controller']['tenant_id']}&url=" . urlencode($_SESSION['user']['destination']));


        $kiw_login = parse_url(urldecode($_SESSION['controller']['login']));


        parse_str($kiw_login['query'], $kiw_query);


        echo json_encode(array(
            "status"   => "success",
            "message"  => null,
            "action"   => "{$kiw_login['scheme']}://{$kiw_login['host']}:{$kiw_login['port']}/guest/s/{$_SESSION['controller']['tenant_id']}/radius.html",
            "method"   => "get",
            "data"     => [
                // array("type" => "text",
                //     "name" => "ap",
                //     "value" => $kiw_query['ap']
                // ),
                // array("type" => "text",
                //     "name" => "ec",
                //     "value" => $kiw_query['amp;ec']
                // ),
                array("type" => "text",
                      "name" => "session",
                      "value" => $kiw_session
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

    } elseif ($_SESSION['controller']['type'] == "virtual-nas"){

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
    elseif ($_SESSION['controller']['type'] == "pfsense"){

        echo json_encode(array(
            "status"   => "success",
            "message"  => null,
            "action"   => $_SESSION['controller']['login'],
            "method"   => "POST",
            "type"     => "manual_trigger",
            "type_name"=> "accept",
            "data"     => [
                array("type" => "text",
                      "name" => "auth_user",
                      "value" => $kiw_user['username']
                ),
                array("type" => "password",
                      "name" => "auth_pass",
                      "value" => $kiw_user['password']
                ),
                array("type" => "text",
                      "name" => "redirurl",
                      "value" => $_SESSION['user']['destination']
                ),
                array("type" => "text",
                      "name" => "zone",
                      "value" => $_SESSION['controller']['zone']
                ),
                array("type" => "submit",
                      "name" => "accept",
                      "value" => "continue"
                )
            ]
        ));

    }


    // set post login if available

    if ($_SESSION['system']['post_campaign'] == "y"){

        $_SESSION['user']['current'] = $_SESSION['system']['post_page'];

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
