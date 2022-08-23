<?php

require_once dirname(__FILE__) . "/include_account.php";
require_once dirname(__FILE__) . "/include_general.php";

function qr_auto_login($kiw_db, $kiw_cache, $kiw_zone, $kiw_session_id){


    $kiw_url = urldecode($_SESSION['user']['destination']);


    if (strpos($kiw_url, "s_type=qr") > 0) {


        $qr_d = explode("?", html_entity_decode($kiw_url));

        parse_str($qr_d[1], $qrcode_d);


        if($qrcode_d['s_type'] == "qr"){


            $_SESSION['system']['checked'] = true;

            logger($_SESSION['user']['mac'], "QR Code Auto-Login: {$qrcode_d['u']}", $_SESSION['controller']['tenant_id']);


            $_SESSION['system']['auto'] = true;

            $_SESSION['user']['auser'] = $qrcode_d['u'];
            $_SESSION['user']['apass'] = $qrcode_d['p'];

            header("Location: /user/login/?session={$_GET['session']}");

            exit(0);


        }


        unset($qr_d);
        unset($qrcode_d);


    }


}


function zone_auto_login($kiw_db, $kiw_cache, $kiw_zone, $kiw_session_id){


    if ($_SESSION['user']['zone'] != "nozone") {


        if (!empty($kiw_zone['auto_login']) && $kiw_zone['auto_login'] != "none"){


            $kiw_temp = $kiw_db->query_first("SELECT username,password,status FROM kiwire_account_auth WHERE username = '{$kiw_zone['auto_login']}' AND tenant_id = '{$kiw_zone['tenant_id']}' LIMIT 1");


            if (!empty($kiw_temp['username']) && $kiw_temp['status'] == "active") {


                $_SESSION['system']['checked'] = true;

                logger($_SESSION['user']['mac'], "Zone Auto-Login: {$kiw_temp['username']}", $_SESSION['controller']['tenant_id']);


                $_SESSION['system']['auto'] = true;

                $_SESSION['user']['auser'] = $kiw_temp['username'];
                $_SESSION['user']['apass'] = sync_decrypt($kiw_temp['password']);

                header("Location: /user/login/?session={$_GET['session']}");

                exit(0);


            }


        }


    }

}


function mac_auto_login($kiw_db, $kiw_cache, $kiw_zone, $kiw_session_id){


    // need to get policy for mac auto login


    if (!empty($_SESSION['user']['mac'])) {


        $kiw_temp = $kiw_db->query_first("SELECT last_account,last_auto,last_zone FROM kiwire_device_history WHERE mac_address = '{$_SESSION['user']['mac']}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");


        if (!empty($kiw_temp['last_account'])){


            $kiw_user = $kiw_db->query_first("SELECT username,password,date_last_logout,status FROM kiwire_account_auth WHERE username = '{$kiw_temp['last_account']}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");


            if (!empty($kiw_user['username']) && $kiw_user['status'] == "active") {


                $kiw_policies = $kiw_cache->get("POLICIES_DATA:{$_SESSION['controller']['tenant_id']}");

                if (empty($kiw_policies)) {


                    $kiw_policies = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_policies WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

                    if (empty($kiw_policies)) $kiw_policies = array("dummy" => true);

                    $kiw_cache->set("POLICIES_DATA:{$_SESSION['controller']['tenant_id']}", $kiw_policies, 1800);


                }



                if ($kiw_policies['mac_auto_login'] == "y" && ($kiw_policies['mac_auto_same_zone'] == "n" || ($kiw_policies['mac_auto_same_zone'] == "y" && $_SESSION['user']['zone'] == $kiw_temp['last_zone']))) {


                    if (strtotime($kiw_temp['last_auto']) < 1) $kiw_temp['last_auto'] = date("Y-m-d H:i:s");


                    $kiw_test = (int)((time() - strtotime($kiw_temp['last_auto'])) / (60 * 60 * 24));


                    if ($kiw_test <= $kiw_policies['mac_auto_login_days']) {


                        $_SESSION['system']['checked'] = true;

                        logger($_SESSION['user']['mac'], "MAC Address Auto-Login: {$kiw_user['username']}", $_SESSION['controller']['tenant_id']);


                        $_SESSION['system']['auto'] = true;

                        $_SESSION['user']['auser'] = $kiw_user['username'];
                        $_SESSION['user']['apass'] = sync_decrypt($kiw_user['password']);

                        header("Location: /user/login/?session={$_GET['session']}");

                        exit(0);


                    }


                }


            }


        }


    }


}


function cookies_auto_login($kiw_db, $kiw_cache, $kiw_zone, $kiw_session_id){


    $kiw_temp = $_COOKIE['smart-wifi-login'];


    if (!empty($kiw_temp)){


        $kiw_cookies = base64_decode(sync_decrypt($kiw_temp));

        $kiw_cookies = explode("||", $kiw_cookies);


        if (!empty($kiw_cookies[2]) && $kiw_cookies[2] == $_SESSION['controller']['tenant_id']){


            $_SESSION['system']['checked'] = true;

            logger($_SESSION['user']['mac'], "Cookie Auto-Login: {$kiw_cookies[0]}", $_SESSION['controller']['tenant_id']);


            $_SESSION['system']['auto'] = true;

            $_SESSION['user']['auser'] = $kiw_cookies[0];
            $_SESSION['user']['apass'] = $kiw_cookies[1];

            header("Location: /user/login/?session={$_GET['session']}");

            exit(0);


        }


    }


}




