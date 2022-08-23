<?php

$kiw_temp['id']       = $_GET['gw_id'];
$kiw_temp['stage']    = $_GET['stage'];
$kiw_temp['ip']       = $_GET['ip'];
$kiw_temp['ipv6']     = $_GET['ipv6'];
$kiw_temp['mac']      = $_GET['mac'];
$kiw_temp['token']    = $_GET['token'];
$kiw_temp['incoming'] = $_GET['incoming'];
$kiw_temp['outgoing'] = $_GET['outgoing'];
$kiw_temp['ssid']     = $_GET['ssid'];
$kiw_temp['vlan']     = $_GET['vlanid'];


// clean mac address for redis

$kiw_temp['mac_clean'] = str_replace(":", "", $kiw_temp['mac']);

$kiw_temp['controller_ip'] = $_SERVER['REMOTE_ADDR'];


if (!isset($kiw_temp['incoming']) || empty($kiw_temp['incoming'])) $kiw_temp['incoming'] = 0;
if (!isset($kiw_temp['outgoing']) || empty($kiw_temp['outgoing'])) $kiw_temp['outgoing'] = 0;


if (empty($kiw_temp['ip'])){

    $kiw_temp['ip'] = "169.254.0." . rand(2, 254);

}


if (!empty($kiw_temp['id']) && !empty($kiw_temp['mac'])){


    require_once dirname(__FILE__, 4) . "/user/includes/include_general.php";

    require_once dirname(__FILE__, 4) . "/admin/includes/include_connection.php";
    require_once dirname(__FILE__, 4) . "/admin/includes/include_general.php";

    require_once dirname(__FILE__, 4) . "/libs/class.ip_range.php";


    // filter all received data for safety

    foreach ($kiw_temp as $kiw_key => $kiw_value){

        $kiw_temp_enc[$kiw_key] = $kiw_db->escape($kiw_value);

    }


    $kiw_temp = $kiw_temp_enc;

    unset($kiw_temp_enc);


    // update for all request coming in tenant_id

    $kiw_id_hash = md5($kiw_temp['id']);

    $kiw_tenant = $kiw_cache->get("NAS_TENANT:{$kiw_id_hash}");

    if (empty($kiw_tenant)) {


        $kiw_tenant = $kiw_db->query_first("SELECT SQL_CACHE tenant_id FROM kiwire_controller WHERE unique_id = '{$kiw_temp['id']}' LIMIT 1");

        if (empty($kiw_tenant)) $kiw_tenant['tenant_id'] = "XXinvalidXX";

        $kiw_temp['tenant_id'] = $kiw_tenant['tenant_id'];

        $kiw_cache->set("NAS_TENANT:{$kiw_id_hash}", $kiw_temp['tenant_id'], 1800);


    } else {

        $kiw_temp['tenant_id'] = $kiw_tenant;

    }


    if ($kiw_temp['tenant_id'] == "XXinvalidXX"){

        die("Auth: 0");

    }

    unset($kiw_tenant);
    unset($kiw_id_hash);


    if ($kiw_temp['stage'] == "login") {


        if (!empty($kiw_temp['token'])) {


            $kiw_user = $kiw_cache->get("WD:LOGIN:{$kiw_temp['tenant_id']}:{$kiw_temp['token']}");


            if (!empty($kiw_user)) {


                $kiw_auth = curl_init();

                curl_setopt($kiw_auth, CURLOPT_URL, "http://127.0.0.1:9955");
                curl_setopt($kiw_auth, CURLOPT_POST, true);
                curl_setopt($kiw_auth, CURLOPT_POSTFIELDS, http_build_query($kiw_user));
                curl_setopt($kiw_auth, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($kiw_auth, CURLOPT_TIMEOUT, 5);
                curl_setopt($kiw_auth, CURLOPT_CONNECTTIMEOUT, 5);

                $kiw_auth = curl_exec($kiw_auth);
                $kiw_auth = json_decode($kiw_auth, true);

                if (!empty($kiw_auth)) {


                    foreach ($kiw_auth as $kiw_key => $kiw_value) {

                        if ($kiw_key == "reply:WISPr-Bandwidth-Max-Up") $kiw_limit['up'] = $kiw_value / 8000;
                        if ($kiw_key == "reply:WISPr-Bandwidth-Max-Down") $kiw_limit['down'] = $kiw_value / 8000;
                        if ($kiw_key == "reply:Session-Timeout") $kiw_limit['time'] = $kiw_value;

                    }


                    // if no time set, then allow for one month

                    if (!isset($kiw_limit['time']) || empty($kiw_limit['time'])) $kiw_limit['time'] = (30 * 24 * 60 * 60);


                    if ($kiw_limit['up'] > 0 && $kiw_limit['down'] > 0) {


                        // delete login data, so user need to relogin. and create session data for future use

                        $kiw_cache->del("WD:LOGIN:{$kiw_temp['tenant_id']}:{$kiw_temp['token']}");

                        $kiw_cache->set("WD:SESSION:{$kiw_temp['tenant_id']}:{$kiw_temp['token']}", array(
                            "tenant_id" => $kiw_user['tenant_id'],
                            "username" => $kiw_user['username'],
                            "start_time" => time()
                        ), 86400);


                        // if all good, then start new session

                        $kiw_account['action']         = "accounting";
                        $kiw_account['nasid']          = $kiw_temp['id'];
                        $kiw_account['username']       = $kiw_user['username'];
                        $kiw_account['macaddress']     = $kiw_temp['mac'];
                        $kiw_account['unique_id']      = $kiw_temp['token'];
                        $kiw_account['station_id']     = str_replace(":", "-", $kiw_temp['id']) . ":" . $kiw_temp['ssid'];
                        $kiw_account['ipaddress']      = $kiw_temp['ip'];
                        $kiw_account['ipv6address']    = $kiw_temp['ipv6'];
                        $kiw_account['controller_ip']  = $kiw_temp['controller_ip'];
                        $kiw_account['zone']           = $kiw_temp['zone'];
                        $kiw_account['session_id']     = $kiw_temp['token'];
                        $kiw_account['session_time']   = 0;
                        $kiw_account['quota_in']       = 0;
                        $kiw_account['quota_out']      = 0;
                        $kiw_account['event-time']     = date("Y-m-d H:i:s");
                        $kiw_account['type']           = "Start";
                        $kiw_account['terminate']      = "";
                        $kiw_account['quota_in_gw']    = 0;
                        $kiw_account['quota_out_gw']   = 0;

                        $kiw_auth = curl_init();

                        curl_setopt($kiw_auth, CURLOPT_URL, "http://127.0.0.1:9955");
                        curl_setopt($kiw_auth, CURLOPT_POST, true);
                        curl_setopt($kiw_auth, CURLOPT_POSTFIELDS, http_build_query($kiw_account));
                        curl_setopt($kiw_auth, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($kiw_auth, CURLOPT_TIMEOUT, 5);
                        curl_setopt($kiw_auth, CURLOPT_CONNECTTIMEOUT, 5);

                        $kiw_auth = curl_exec($kiw_auth);


                        // mark this device as active

                        $kiw_cache->set("WD:ACTIVE:{$kiw_temp['tenant_id']}:{$kiw_temp['mac_clean']}", $kiw_temp['token'], 86400);


                        echo json_encode(
                            array(
                                "auth"          => 1,
                                "username"      => $kiw_user['username'],
                                "usergroup"     => "/ruijie",
                                "url"           => "",
                                "timeout"       => $kiw_limit['time'],
                                "uplinklimit"   => $kiw_limit['up'],
                                "downlinklimit" => $kiw_limit['down']
                            )
                        );


                    } else{

                        logger($kiw_temp['mac'], "Not subscribe to any profile", $kiw_temp['tenant_id']);
                        echo "Auth: 0";

                    }


                } else{

                    logger($kiw_temp['mac'], "No response from Kiwire Service", $kiw_temp['tenant_id']);
                    echo "Auth: 0";

                }


            } else{

                logger($kiw_temp['mac'], "Not properly login from captive portal", $kiw_temp['tenant_id']);
                echo "Auth: 0";

            }


        } else{

            logger($kiw_temp['mac'], "Missing or invalid token provided", $kiw_temp['tenant_id']);
            echo "Auth: 0";

        }


    } elseif ($kiw_temp['stage'] == "logout"){


        $kiw_session = $kiw_cache->get("WD:SESSION:{$kiw_temp['tenant_id']}:{$kiw_temp['token']}");

        if (!empty($kiw_session)) {


            $kiw_user['action']         = "accounting";
            $kiw_user['nasid']          = $kiw_temp['id'];
            $kiw_user['username']       = $kiw_session['username'];
            $kiw_user['macaddress']     = $kiw_temp['mac'];
            $kiw_user['unique_id']      = $kiw_temp['token'];
            $kiw_user['station_id']     = str_replace(":", "-", $kiw_temp['id']) . ":" . $kiw_temp['ssid'];
            $kiw_user['ipaddress']      = $kiw_temp['ip'];
            $kiw_user['ipv6address']    = $kiw_temp['ipv6'];
            $kiw_user['controller_ip']  = $kiw_temp['controller_ip'];
            $kiw_user['session_id']     = $kiw_temp['token'];
            $kiw_user['session_time']   = time() - $kiw_session['start_time'];
            $kiw_user['quota_in']       = $kiw_temp['incoming'];
            $kiw_user['quota_out']      = $kiw_temp['outgoing'];
            $kiw_user['event-time']     = date("Y-m-d H:i:s");
            $kiw_user['type']           = "Stop";
            $kiw_user['terminate']      = "Session-Timeout";
            $kiw_user['quota_in_gw']    = 0;
            $kiw_user['quota_out_gw']   = 0;

            $kiw_auth = curl_init();

            curl_setopt($kiw_auth, CURLOPT_URL, "http://127.0.0.1:9955");
            curl_setopt($kiw_auth, CURLOPT_POST, true);
            curl_setopt($kiw_auth, CURLOPT_POSTFIELDS, http_build_query($kiw_user));
            curl_setopt($kiw_auth, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($kiw_auth, CURLOPT_TIMEOUT, 5);
            curl_setopt($kiw_auth, CURLOPT_CONNECTTIMEOUT, 5);

            $kiw_auth = curl_exec($kiw_auth);

            logger($kiw_temp['mac'], "Logout", $kiw_temp['tenant_id']);

            $kiw_cache->del("WD:SESSION:{$kiw_temp['tenant_id']}:{$kiw_temp['token']}");
            $kiw_cache->del("WD:DC:{$kiw_temp['tenant_id']}:{$kiw_temp['token']}");
            $kiw_cache->del("WD:ACTIVE:{$kiw_temp['tenant_id']}:{$kiw_temp['mac_clean']}");

            $kiw_cache->set("WD:BL:{$kiw_temp['tenant_id']}:{$kiw_temp['mac_clean']}", time(), 3);


        }


        // disconnect always return false

        echo "Auth: 0";


    } elseif (in_array($kiw_temp['stage'], array("counter", "counters"))){


        // check if this session need to be kick, if yes, then just kick since data will be captured in logout

        $kiw_disconnect_check = $kiw_cache->get("WD:DC:{$kiw_temp['tenant_id']}:{$kiw_temp['token']}");


        if ($kiw_disconnect_check['disconnected'] == true) {

            die("Auth: 0");

        }


        $kiw_session = $kiw_cache->get("WD:SESSION:{$kiw_temp['tenant_id']}:{$kiw_temp['token']}");

        if (!empty($kiw_session)) {


            $kiw_user['action']         = "accounting";
            $kiw_user['nasid']          = $kiw_temp['id'];
            $kiw_user['username']       = $kiw_session['username'];
            $kiw_user['macaddress']     = $kiw_temp['mac'];
            $kiw_user['unique_id']      = $kiw_temp['token'];
            $kiw_user['station_id']     = str_replace(":", "-", $kiw_temp['id']) . ":" . $kiw_temp['ssid'];
            $kiw_user['ipaddress']      = $kiw_temp['ip'];
            $kiw_user['ipv6address']    = $kiw_temp['ipv6'];
            $kiw_user['controller_ip']  = $kiw_temp['controller_ip'];
            $kiw_user['session_id']     = $kiw_temp['token'];
            $kiw_user['session_time']   = time() - $kiw_session['start_time'];
            $kiw_user['quota_in']       = $kiw_temp['incoming'];
            $kiw_user['quota_out']      = $kiw_temp['outgoing'];
            $kiw_user['event-time']     = date("Y-m-d H:i:s");
            $kiw_user['type']           = "Interim-Update";
            $kiw_user['terminate']      = "Session-Timeout";
            $kiw_user['quota_in_gw']    = 0;
            $kiw_user['quota_out_gw']   = 0;

            $kiw_auth = curl_init();

            curl_setopt($kiw_auth, CURLOPT_URL, "http://127.0.0.1:9955");
            curl_setopt($kiw_auth, CURLOPT_POST, true);
            curl_setopt($kiw_auth, CURLOPT_POSTFIELDS, http_build_query($kiw_user));
            curl_setopt($kiw_auth, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($kiw_auth, CURLOPT_TIMEOUT, 5);
            curl_setopt($kiw_auth, CURLOPT_CONNECTTIMEOUT, 5);

            $kiw_auth = curl_exec($kiw_auth);
            $kiw_auth = json_decode($kiw_auth, true);


            $kiw_disconnect_check = $kiw_cache->get("WD:DC:{$kiw_temp['tenant_id']}:{$kiw_temp['token']}");


            // check again if need to kick this session after accounting calculated

            if ($kiw_disconnect_check['disconnected'] == true) {

                echo "Auth: 0";

            } else echo "Auth: 1";



        } else {


            echo "Auth: 0";


        }




    } elseif ($kiw_temp['stage'] == "query") {


        if ($kiw_cache->exists("WD:BL:{$kiw_temp['tenant_id']}:{$kiw_temp['mac_clean']}")){


            logger($kiw_temp['mac'], "WF Roaming: Reject due to received logout request", $kiw_temp['tenant_id']);

            die("Auth: 0");


        }


        $kiw_query_unique = "QUERY_CACHE:" . md5("{$kiw_temp['id']}{$kiw_temp['mac']}");

        $kiw_response = $kiw_cache->get($kiw_query_unique);


        if (!empty($kiw_response)) {


            echo json_encode(array("status" => "duplicate"));

            die();


        } elseif ($kiw_response == "Auth: 0") {


            echo "Auth: 0";

            die();


        }


        // check if being kicked out, if yes then redirect to landing page

        $kiw_response = $kiw_cache->get("WD:BL:{$kiw_temp['tenant_id']}:{$kiw_temp['mac']}");

        if ($kiw_response['block'] == true) {


            echo "Auth: 0";

            exit();


        }


        $kiw_controller = $kiw_db->query_first("SELECT SQL_CACHE seamless_type FROM kiwire_controller WHERE unique_id = '{$kiw_temp['id']}' LIMIT 1");


        if ($kiw_controller['seamless_type'] !== "disabled") {

            switch ($kiw_controller['seamless_type']){

                case "hour"  : $kiw_last_login = "AND last_auto > DATE_SUB(NOW(), INTERVAL 1 HOUR)"; break;
                case "day"   : $kiw_last_login = "AND last_auto > DATE_SUB(NOW(), INTERVAL 1 DAY)"; break;
                case "week"  : $kiw_last_login = "AND last_auto > DATE_SUB(NOW(), INTERVAL 1 WEEK)"; break;
                case "month" : $kiw_last_login = "AND last_auto > DATE_SUB(NOW(), INTERVAL 1 MONTH)"; break;
                default: $kiw_last_login = "";

            }

            $kiw_username = $kiw_db->query_first("SELECT last_account FROM kiwire_device_history WHERE mac_address = '{$kiw_temp['mac']}' {$kiw_last_login} AND tenant_id = '{$kiw_temp['tenant_id']}' LIMIT 1");


        } else {


            logger($kiw_temp['mac'], "WF Roaming: Roaming disabled", $kiw_temp['tenant_id']);

            die("Auth: 0");


        }


        if (!empty($kiw_username)) {


            $kiw_username = $kiw_db->query_first("SELECT username,password FROM kiwire_account_auth WHERE username = '{$kiw_username['last_account']}' AND tenant_id = '{$kiw_temp['tenant_id']}' LIMIT 1");


            if (!empty($kiw_username)) {


                // sleep 0.5 seconds to make sure all logout done process

                usleep(500000);


                // check if this device is active

                if ($kiw_cache->exists("WD:ACTIVE:{$kiw_temp['tenant_id']}:{$kiw_temp['mac_clean']}")){


                    $kiw_sessions = $kiw_db->fetch_array("SELECT * FROM kiwire_active_session WHERE mac_address = '{$kiw_temp['mac']}' AND tenant_id = '{$kiw_temp['tenant_id']}'");


                    // send logout instruction

                    foreach ($kiw_sessions as $kiw_session){


                        $kiw_user['action']         = "accounting";
                        $kiw_user['nasid']          = $kiw_session['controller'];
                        $kiw_user['username']       = $kiw_session['username'];
                        $kiw_user['macaddress']     = $kiw_session['mac_address'];
                        $kiw_user['unique_id']      = $kiw_session['unique_id'];
                        $kiw_user['ipaddress']      = $kiw_session['ip_address'];
                        $kiw_user['ipv6address']    = $kiw_session['ipv6_address'];
                        $kiw_user['controller_ip']  = $kiw_session['controller_ip'];
                        $kiw_user['zone']           = $kiw_session['zone'];
                        $kiw_user['session_id']     = $kiw_session['session_id'];
                        $kiw_user['session_time']   = time() - strtotime($kiw_session['start_time']);
                        $kiw_user['quota_in']       = $kiw_session['quota_in'];
                        $kiw_user['quota_out']      = $kiw_session['quota_out'];
                        $kiw_user['event-time']     = date("Y-m-d H:i:s");
                        $kiw_user['type']           = "Stop";
                        $kiw_user['terminate']      = "Stale-Session";
                        $kiw_user['quota_in_gw']    = 0;
                        $kiw_user['quota_out_gw']   = 0;

                        $kiw_auth = curl_init();

                        curl_setopt($kiw_auth, CURLOPT_URL, "http://127.0.0.1:9955");
                        curl_setopt($kiw_auth, CURLOPT_POST, true);
                        curl_setopt($kiw_auth, CURLOPT_POSTFIELDS, http_build_query($kiw_user));
                        curl_setopt($kiw_auth, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($kiw_auth, CURLOPT_TIMEOUT, 5);
                        curl_setopt($kiw_auth, CURLOPT_CONNECTTIMEOUT, 5);

                        curl_exec($kiw_auth);
                        curl_close($kiw_auth);

                        unset($kiw_auth);
                        unset($kiw_user);


                    }


                    unset($kiw_session);
                    unset($kiw_sessions);


                    $kiw_cache->del("WD:ACTIVE:{$kiw_temp['tenant_id']}:{$kiw_temp['mac_clean']}");


                }


                // get zone details

                $kiw_zone_attr = $kiw_cache->get("ZONE_DATA_ATTR:{$kiw_temp['tenant_id']}");

                if (empty($kiw_zone_attr)) {

                    $kiw_zone_attr = $kiw_db->fetch_array("SELECT SQL_CACHE * FROM kiwire_zone_child WHERE tenant_id = '{$kiw_temp['tenant_id']}' AND master_id IN (SELECT DISTINCT(name) FROM kiwire_zone WHERE kiwire_zone.tenant_id = '{$kiw_temp['tenant_id']}' AND status = 'y') ORDER BY priority DESC");

                    $kiw_cache->set("ZONE_DATA_ATTR:{$kiw_temp['tenant_id']}", $kiw_zone_attr, 1800);

                }

                foreach ($kiw_zone_attr as $attr) {

                    if (empty($attr['nasid']) || strtolower($attr['nasid']) == strtolower($kiw_temp['id'])) {

                        if (empty($attr['vlan']) || strtolower($attr['vlan']) == strtolower($kiw_temp['vlan'])) {

                            if (empty($attr['ssid']) || strtolower($attr['ssid']) == strtolower($kiw_temp['ssid'])) {

                                if (empty($attr['ipaddr']) || ipv4_in_range($kiw_temp['ip'], $attr['ipaddr'])) {

                                    if (empty($attr['ipv6addr']) || ipv6_in_range($kiw_temp['ipv6'], $attr['ipv6addr'])) {

                                        if (empty($attr['dzone']) || strtolower($attr['dzone']) == strtolower($kiw_temp['zone'])) {


                                            $kiw_temp['zone'] = $attr['master_id'];

                                            break;


                                        }

                                    }

                                }

                            }

                        }

                    }

                }


                unset($kiw_zone_attr);

                if (empty($kiw_temp['zone'])) $kiw_temp['zone'] = "nozone";


                // end of zone


                $kiw_user['username']     = $kiw_username['username'];
                $kiw_user['password']     = sync_decrypt($kiw_username['password']);
                $kiw_user['macaddress']   = $kiw_temp['mac'];
                $kiw_user['ipaddress']    = $kiw_temp['ip'];
                $kiw_user['ipv6address']  = $kiw_temp['ipv6'];

                $kiw_user['nasid']        = $kiw_temp['id'];
                $kiw_user['vlan']         = $kiw_temp['vlan'];
                $kiw_user['ssid']         = $kiw_temp['ssid'];
                $kiw_user['station_id']   = str_replace(":", "-", $kiw_temp['id']) . ":" . $kiw_temp['ssid'];;

                $kiw_user['zone']             = $kiw_temp['zone'];
                $kiw_user['controller_zone']  = "";
                $kiw_user['controller_ip']    = $kiw_temp['controller_ip'];

                $kiw_user['action'] = "authorize";


                $kiw_auth = curl_init();

                curl_setopt($kiw_auth, CURLOPT_URL, "http://127.0.0.1:9955");
                curl_setopt($kiw_auth, CURLOPT_POST, true);
                curl_setopt($kiw_auth, CURLOPT_POSTFIELDS, http_build_query($kiw_user));
                curl_setopt($kiw_auth, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($kiw_auth, CURLOPT_TIMEOUT, 5);
                curl_setopt($kiw_auth, CURLOPT_CONNECTTIMEOUT, 5);

                $kiw_auth = curl_exec($kiw_auth);
                $kiw_auth = json_decode($kiw_auth, true);


                if (isset($kiw_auth['control:Cleartext-Password']['value']) && !empty($kiw_auth['control:Cleartext-Password']['value'])) {


                    if ($kiw_auth['control:Cleartext-Password']['value'] == $kiw_user['password']) {


                        foreach ($kiw_auth as $kiw_key => $kiw_value) {

                            if ($kiw_key == "reply:WISPr-Bandwidth-Max-Up") $kiw_limit['up'] = $kiw_value / 8000;
                            if ($kiw_key == "reply:WISPr-Bandwidth-Max-Down") $kiw_limit['down'] = $kiw_value / 8000;
                            if ($kiw_key == "reply:Session-Timeout") $kiw_limit['time'] = $kiw_value;

                        }


                        // if no time set, then allow for one month

                        if (!isset($kiw_limit['time']) || empty($kiw_limit['time'])) $kiw_limit['time'] = (30 * 24 * 60 * 60);


                        if ($kiw_limit['up'] > 0 && $kiw_limit['down'] > 0) {


                            // make sure no duplicate token

                            foreach (range(0, 9) as $kiw_range) {


                                $kiw_token = strtolower(date("ymd") . substr(bin2hex(random_bytes(10)), random_int(1, 10), 10));

                                $kiw_existed = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_active_session WHERE unique_id = '{$kiw_token}' AND tenant_id = '{$kiw_temp['tenant_id']}' LIMIT 1");

                                if ($kiw_existed['kcount'] == "0") break;


                            }


                            $kiw_cache->set("WD:SESSION:{$kiw_temp['tenant_id']}:{$kiw_token}", array(
                                "tenant_id" => $kiw_temp['tenant_id'],
                                "username" => $kiw_username['username'],
                                "start_time" => time()
                            ), 86400);


                            // if all good, then start new session

                            $kiw_account['action']          = "accounting";
                            $kiw_account['nasid']           = $kiw_temp['id'];
                            $kiw_account['username']        = $kiw_user['username'];
                            $kiw_account['macaddress']      = $kiw_temp['mac'];
                            $kiw_account['unique_id']       = $kiw_token;
                            $kiw_account['station_id']      = str_replace(":", "-", $kiw_temp['id']) . ":" . $kiw_temp['ssid'];
                            $kiw_account['ipaddress']       = $kiw_temp['ip'];
                            $kiw_account['ipv6address']     = $kiw_temp['ipv6'];
                            $kiw_account['controller_ip']   = $kiw_temp['controller_ip'];
                            $kiw_account['zone']            = $kiw_temp['zone'];
                            $kiw_account['session_id']      = $kiw_token;
                            $kiw_account['session_time']    = 0;
                            $kiw_account['quota_in']        = 0;
                            $kiw_account['quota_out']       = 0;
                            $kiw_account['event-time']      = date("Y-m-d H:i:s");
                            $kiw_account['type']            = "Start";
                            $kiw_account['terminate']       = "";
                            $kiw_account['quota_in_gw']     = 0;
                            $kiw_account['quota_out_gw']    = 0;

                            $kiw_auth = curl_init();

                            curl_setopt($kiw_auth, CURLOPT_URL, "http://127.0.0.1:9955");
                            curl_setopt($kiw_auth, CURLOPT_POST, true);
                            curl_setopt($kiw_auth, CURLOPT_POSTFIELDS, http_build_query($kiw_account));
                            curl_setopt($kiw_auth, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($kiw_auth, CURLOPT_TIMEOUT, 5);
                            curl_setopt($kiw_auth, CURLOPT_CONNECTTIMEOUT, 5);

                            $kiw_auth = curl_exec($kiw_auth);


                            // mark this device as active

                            $kiw_cache->set("WD:ACTIVE:{$kiw_temp['tenant_id']}:{$kiw_temp['mac_clean']}", $kiw_token, 86400);


                            $kiw_result = array(
                                "auth" => 1,
                                "username" => $kiw_user['username'],
                                "usergroup" => "/ruijie",
                                "url" => "",
                                "token" => $kiw_token,
                                "timeout" => $kiw_limit['time'],
                                "uplinklimit" => $kiw_limit['up'],
                                "downlinklimit" => $kiw_limit['down']
                            );


                            echo json_encode($kiw_result);

                            logger($kiw_temp['mac'], "WF Roaming: Success [ {$kiw_user['username']} ] [ {$kiw_token} ]", $kiw_temp['tenant_id']);

                            $kiw_cache->set($kiw_query_unique, $kiw_result, 5);

                            exit();


                        } else {


                            logger($kiw_temp['mac'], "WF Roaming: Not subscribe to any profile", $kiw_temp['tenant_id']);

                            echo "Auth: 0";


                        }


                    } else {

                        logger($kiw_temp['mac'], "WF Roaming: Internal error due to password mismatched", $kiw_temp['tenant_id']);

                        echo "Auth: 0";

                    }


                } else {


                    logger($kiw_temp['mac'], "WF Roaming: Unable to auto-login due to error", $kiw_temp['tenant_id']);

                    echo "Auth: 0";


                }


            } else {

                logger($kiw_temp['mac'], "WF Roaming: User already deleted", $kiw_temp['tenant_id']);

                echo "Auth: 0";

            }


        } else {

            logger($kiw_temp['mac'], "WF Roaming: Device never login previously", $kiw_temp['tenant_id']);

            echo "Auth: 0";

        }


        $kiw_cache->set($kiw_query_unique, "Auth: 0", 5);


    } elseif ($kiw_temp['stage'] == "check"){


        echo json_encode(array("Auth" => 1));


    } else {


        echo "Auth: 0";


    }


} else echo "Auth: 0";

