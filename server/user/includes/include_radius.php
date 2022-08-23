<?php


require_once "include_general.php";


error_reporting(E_ERROR);


function disconnect_user($kiw_db, $kiw_cache, $kiw_tenant, $kiw_username){


    $kiw_nas = [];


    $kiw_sql_class = method_exists($kiw_db, "fetch_array");


    if ($kiw_sql_class == true) $kiw_sessions = $kiw_db->fetch_array("SELECT * FROM kiwire_active_session WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant}' LIMIT 10");
    else $kiw_sessions = $kiw_db->query("SELECT * FROM kiwire_active_session WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant}' LIMIT 10");


    foreach ($kiw_sessions as $kiw_session){


        if ($kiw_nas['unique_id'] != $kiw_session['controller']) {


            $kiw_nas = $kiw_cache->get("CONTROLLER_DATA:{$kiw_session['controller']}");

            if (empty($kiw_nas)) {


                if ($kiw_sql_class == true) $kiw_nas = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_controller WHERE device_type = 'controller' AND unique_id = '{$kiw_session['controller']}' AND tenant_id = '{$kiw_tenant}' LIMIT 1");
                else $kiw_nas = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_controller WHERE device_type = 'controller' AND unique_id = '{$kiw_session['controller']}' AND tenant_id = '{$kiw_tenant}' LIMIT 1")[0];

                $kiw_cache->set("CONTROLLER_DATA:{$kiw_session['controller']}", $kiw_nas, 1800);


            }


            if ($kiw_nas['vendor'] == "ruckus_ap"){


                $kiw_nas = $kiw_cache->get("CONTROLLER_DATA:RUCKUS_CONTROLLER:{$kiw_session['tenant_id']}");

                if (empty($kiw_nas)){


                    if ($kiw_sql_class == true) $kiw_nas = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_controller WHERE device_type = 'controller' AND unique_id = 'Ruckus_Controller' AND tenant_id = '{$kiw_tenant}' LIMIT 1");
                    else $kiw_nas = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_controller WHERE device_type = 'controller' AND unique_id = 'Ruckus_Controller' AND tenant_id = '{$kiw_tenant}' LIMIT 1")[0];

                    if (!empty($kiw_nas)) {

                        $kiw_cache->set("CONTROLLER_DATA:RUCKUS_CONTROLLER:{$kiw_session['tenant_id']}", $kiw_nas, 1800);

                    }


                }


            }


        }


        if (is_array($kiw_nas)) {


            if ($kiw_nas['vendor'] == "nomadix_xml") {


                $kiw_session['mac_address'] = str_replace(array(":", "-"), "", $kiw_session['mac_address']);


                $kiw_nomadix_xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\" ?><USG></USG>");

                $kiw_nomadix_xml->addAttribute("COMMAND", "LOGOUT");

                $kiw_nomadix_xml->addChild("SUB_MAC_ADDR", $kiw_session['mac_address']);
                $kiw_nomadix_xml->addChild("SUB_USER_NAME", $kiw_session['username']);

                $kiw_connection = curl_init();


                // set the default ip address

                if (empty($kiw_nas['coa_port']) || $kiw_nas['coa_port'] == "80") {

                    $kiw_nas['coa_port'] = "";

                } else $kiw_nas['coa_port'] = ":{$kiw_nas['coa_port']}";


                curl_setopt($kiw_connection, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
                curl_setopt($kiw_connection, CURLOPT_URL, "http://{$kiw_nas['device_ip']}{$kiw_nas['coa_port']}/usg/command.xml");
                curl_setopt($kiw_connection, CURLOPT_POSTFIELDS, $kiw_nomadix_xml->asXML());
                curl_setopt($kiw_connection, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($kiw_connection, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($kiw_connection, CURLOPT_TIMEOUT, 10);

                unset($kiw_nomadix_xml);


                curl_exec($kiw_connection);

                $kiw_result = curl_error($kiw_connection);

                curl_close($kiw_connection);


                $kiw_result = $kiw_result == 0;


            } elseif ($kiw_nas['vendor'] == "fortigate"){


                $fortigate_link = ssh2_connect($kiw_nas['device_ip'], $kiw_nas['coa_port']);
                ssh2_auth_password($fortigate_link, $kiw_nas['username'], $kiw_nas['password']);

                $fortigate_sh = ssh2_shell($fortigate_link);
                stream_set_blocking($fortigate_sh, true);

                fwrite($fortigate_sh, "diagnose firewall auth filter user {$kiw_session['username']}" . PHP_EOL);
                sleep(1);

                fwrite($fortigate_sh, "diagnose firewall auth clear" . PHP_EOL);
                sleep(1);

                $kiw_result = fread($fortigate_sh, 1024);

                fclose($fortigate_sh);

                unset($fortigate_sh);

                ssh2_exec($fortigate_link, "exit");

                unset($fortigate_link);
                unset($kiw_result);


            } elseif (in_array($kiw_nas['vendor'], array("ruckus_scg", "ruckus_vsz"))){


                $ruckus_url = "http://{$kiw_nas['device_ip']}:9080/portalintf";

                $kiw_data["Vendor"]             = "ruckus";
                $kiw_data["RequestPassword"]    = $kiw_nas['password'];
                $kiw_data["APIVersion"]         = "1.0";
                $kiw_data["RequestCategory"]    = "GetConfig";
                $kiw_data["RequestType"]        = "Encrypt";
                $kiw_data["Data"]               = $kiw_session['ip_address'];

                $en_ip = json_decode(curl_json_post($ruckus_url, json_encode($kiw_data)))->Data;


                $kiw_data["Vendor"]             = "ruckus";
                $kiw_data["RequestPassword"]    = $kiw_nas['password'];
                $kiw_data["APIVersion"]         = "1.0";
                $kiw_data["RequestCategory"]    = "GetConfig";
                $kiw_data["RequestType"]        = "Encrypt";
                $kiw_data["Data"]               = $kiw_session['mac_address'];

                $en_mac = json_decode(curl_json_post($ruckus_url, json_encode($kiw_data)))->Data;


                $kiw_data["Vendor"]          = "ruckus";
                $kiw_data["RequestPassword"] = $kiw_nas['password'];
                $kiw_data["APIVersion"]      = "1.0";
                $kiw_data["RequestCategory"] = "UserOnlineControl";
                $kiw_data["RequestType"]     = "Disconnect";
                $kiw_data["UE-IP"]           = $en_ip;
                $kiw_data["UE-MAC"]          = $en_mac;

                $kiw_response = curl_json_post($ruckus_url, json_encode($kiw_data));


            } elseif (in_array($kiw_nas['vendor'], array("wifidog", "rwifidog", "other"))){


                $kiw_cache->set("WD:DC:{$kiw_session['tenant_id']}:{$kiw_session['unique_id']}", array("disconnected" => true), 600);


            } elseif (in_array($kiw_nas['vendor'], array("huawei", "sundray"))){


                system("php /var/www/html/user/nas/huawei/huawei_logout.php {$kiw_nas['device_ip']} 2000 {$kiw_session['ip_address']} 0000");


            } else {


                send_radius_instruction_to($kiw_db, "dm", $kiw_nas, $kiw_session);


            }


            unset($kiw_data);


        }


    }


}


function disconnect_device($kiw_db, $kiw_cache, $kiw_tenant, $kiw_device){


    $kiw_sql_class = method_exists($kiw_db, "fetch_array");

    $kiw_result = true;


    if ($kiw_sql_class == true) $kiw_session = $kiw_db->query_first("SELECT * FROM kiwire_active_session WHERE mac_address = '{$kiw_device}' AND tenant_id = '{$kiw_tenant}' LIMIT 1");
    else $kiw_session = $kiw_db->query("SELECT * FROM kiwire_active_session WHERE mac_address = '{$kiw_device}' AND tenant_id = '{$kiw_tenant}' LIMIT 1")[0];

    $kiw_nas = [];


    if ($kiw_nas['unique_id'] != $kiw_session['controller']) {


        $kiw_nas = $kiw_cache->get("CONTROLLER_DATA:{$kiw_session['controller']}");

        if (empty($kiw_nas)) {


            if ($kiw_sql_class == true) $kiw_nas = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_controller WHERE device_type = 'controller' AND unique_id = '{$kiw_session['controller']}' AND tenant_id = '{$kiw_tenant}' LIMIT 1");
            else $kiw_nas = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_controller WHERE device_type = 'controller' AND unique_id = '{$kiw_session['controller']}' AND tenant_id = '{$kiw_tenant}' LIMIT 1")[0];

            $kiw_cache->set("CONTROLLER_DATA:{$kiw_session['controller']}", $kiw_nas, 1800);


        }


        if ($kiw_nas['vendor'] == "ruckus_ap"){


            $kiw_nas = $kiw_cache->get("CONTROLLER_DATA:RUCKUS_CONTROLLER:{$kiw_session['tenant_id']}");

            if (empty($kiw_nas)){


                if ($kiw_sql_class == true) $kiw_nas = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_controller WHERE device_type = 'controller' AND unique_id = 'Ruckus_Controller' AND tenant_id = '{$kiw_tenant}' LIMIT 1");
                else $kiw_nas = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_controller WHERE device_type = 'controller' AND unique_id = 'Ruckus_Controller' AND tenant_id = '{$kiw_tenant}' LIMIT 1")[0];

                if (!empty($kiw_nas)) {

                    $kiw_cache->set("CONTROLLER_DATA:RUCKUS_CONTROLLER:{$kiw_session['tenant_id']}", $kiw_nas, 1800);

                }


            }


        }


    }


    if (is_array($kiw_nas)) {


        if ($kiw_nas['vendor'] == "nomadix_xml") {


            $kiw_session['mac_address'] = str_replace(array(":", "-"), "", $kiw_session['mac_address']);


            $kiw_nomadix_xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\" ?><USG></USG>");

            $kiw_nomadix_xml->addAttribute("COMMAND", "LOGOUT");

            $kiw_nomadix_xml->addChild("SUB_MAC_ADDR", $kiw_session['mac_address']);
            $kiw_nomadix_xml->addChild("SUB_USER_NAME", $kiw_session['username']);

            $kiw_connection = curl_init();


            // set the default ip address

            if (empty($kiw_nas['coa_port']) || $kiw_nas['coa_port'] == "80") {

                $kiw_nas['coa_port'] = "";

            } else $kiw_nas['coa_port'] = ":{$kiw_nas['coa_port']}";


            curl_setopt($kiw_connection, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
            curl_setopt($kiw_connection, CURLOPT_URL, "http://{$kiw_nas['device_ip']}{$kiw_nas['coa_port']}/usg/command.xml");
            curl_setopt($kiw_connection, CURLOPT_POSTFIELDS, $kiw_nomadix_xml->asXML());
            curl_setopt($kiw_connection, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($kiw_connection, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($kiw_connection, CURLOPT_TIMEOUT, 10);

            unset($kiw_nomadix_xml);


            curl_exec($kiw_connection);

            $kiw_result = curl_error($kiw_connection);

            curl_close($kiw_connection);


            $kiw_result = $kiw_result == 0;


        } elseif ($kiw_nas['vendor'] == "fortigate"){


            $fortigate_link = ssh2_connect($kiw_nas['device_ip'], $kiw_nas['coa_port']);
            ssh2_auth_password($fortigate_link, $kiw_nas['username'], $kiw_nas['password']);

            $fortigate_sh = ssh2_shell($fortigate_link);
            stream_set_blocking($fortigate_sh, true);

            fwrite($fortigate_sh, "diagnose firewall auth filter user {$kiw_session['username']}" . PHP_EOL);
            sleep(1);

            fwrite($fortigate_sh, "diagnose firewall auth clear" . PHP_EOL);
            sleep(1);

            $kiw_result = fread($fortigate_sh, 1024);

            fclose($fortigate_sh);

            unset($fortigate_sh);

            ssh2_exec($fortigate_link, "exit");

            unset($fortigate_link);
            unset($kiw_result);


        } elseif (in_array($kiw_nas['vendor'], array("ruckus_scg", "ruckus_vsz"))){


            $ruckus_url = "http://{$kiw_nas['device_ip']}:9080/portalintf";

            $kiw_data["Vendor"]             = "ruckus";
            $kiw_data["RequestPassword"]    = $kiw_nas['password'];
            $kiw_data["APIVersion"]         = "1.0";
            $kiw_data["RequestCategory"]    = "GetConfig";
            $kiw_data["RequestType"]        = "Encrypt";
            $kiw_data["Data"]               = $kiw_session['ip_address'];

            $en_ip = json_decode(curl_json_post($ruckus_url, json_encode($kiw_data)))->Data;


            $kiw_data["Vendor"]             = "ruckus";
            $kiw_data["RequestPassword"]    = $kiw_nas['password'];
            $kiw_data["APIVersion"]         = "1.0";
            $kiw_data["RequestCategory"]    = "GetConfig";
            $kiw_data["RequestType"]        = "Encrypt";
            $kiw_data["Data"]               = $kiw_session['mac_address'];

            $en_mac = json_decode(curl_json_post($ruckus_url, json_encode($kiw_data)))->Data;


            $kiw_data["Vendor"]          = "ruckus";
            $kiw_data["RequestPassword"] = $kiw_nas['password'];
            $kiw_data["APIVersion"]      = "1.0";
            $kiw_data["RequestCategory"] = "UserOnlineControl";
            $kiw_data["RequestType"]     = "Disconnect";
            $kiw_data["UE-IP"]           = $en_ip;
            $kiw_data["UE-MAC"]          = $en_mac;

            $kiw_result = curl_json_post($ruckus_url, json_encode($kiw_data));


        } elseif (in_array($kiw_nas['vendor'], array("wifidog", "rwifidog", "other"))){


            $kiw_cache->set("WD:DC:{$kiw_session['tenant_id']}:{$kiw_session['unique_id']}", ["time" => date("Y-m-d H:i:s"), "disconnected" => true], 600);


        } elseif (in_array($kiw_nas['vendor'], array("huawei", "sundray"))){


            system("php /var/www/html/user/nas/huawei/huawei_logout.php {$kiw_nas['nasname']} 2000 {$kiw_session['frameipadddress']} 0000");


        } else {

            
            $kiw_result = send_radius_instruction_to($kiw_db, "dm", $kiw_nas, $kiw_session);


        }


        unset($kiw_data);


    }


    return $kiw_result;


}




function coa_user($kiw_db, $kiw_cache, $kiw_tenant, $kiw_username, $kiw_profile_name){


    $kiw_sql_class = method_exists($kiw_db, "fetch_array");


    // get the new profile attribute


    $kiw_profile = $kiw_cache->get("PROFILE_DATA:{$kiw_tenant}:{$kiw_profile_name}");

    if (empty($kiw_profile)) {

        if ($kiw_sql_class == true) $kiw_profile = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_profiles WHERE tenant_id = '{$kiw_tenant}' AND name = '{$kiw_profile_name}' LIMIT 1");
        else $kiw_profile = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_profiles WHERE tenant_id = '{$kiw_tenant}' AND name = '{$kiw_profile_name}' LIMIT 1")[0];

        if (empty($kiw_profile)) $kiw_profile = array("dummy" => true);

        $kiw_cache->set("PROFILE_DATA:{$kiw_tenant}:{$kiw_profile_name}", $kiw_profile, 1800);

    }


    $kiw_profile = json_decode($kiw_profile['attribute'], true);


    // update account to use latest profile assigned

    $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), profile_curr = '{$kiw_profile_name}' WHERE tenant_id = '{$kiw_tenant}' AND username = '{$kiw_username}' LIMIT 1");


    // get all active sessions

    if ($kiw_sql_class == true) $kiw_sessions = $kiw_db->fetch_array("SELECT * FROM kiwire_active_session WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant}' LIMIT 100");
    else $kiw_sessions = $kiw_db->query("SELECT * FROM kiwire_active_session WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant}' LIMIT 100");

    $kiw_nas = array();


    foreach ($kiw_sessions as $kiw_session){


        if ($kiw_nas['unique_id'] != $kiw_session['controller']) {


            $kiw_nas = $kiw_cache->get("CONTROLLER_DATA:{$kiw_session['controller']}");

            if (empty($kiw_nas)) {


                if ($kiw_sql_class == true) $kiw_nas = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_controller WHERE device_type = 'controller' AND unique_id = '{$kiw_session['controller']}' AND tenant_id = '{$kiw_tenant}' LIMIT 1");
                else $kiw_nas = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_controller WHERE device_type = 'controller' AND unique_id = '{$kiw_session['controller']}' AND tenant_id = '{$kiw_tenant}' LIMIT 1")[0];

                $kiw_cache->set("CONTROLLER_DATA:{$kiw_session['controller']}", $kiw_nas, 1800);


            }


        }


        if ($kiw_nas) {


            if ($kiw_nas['device_type'] == "nomadix_xml") {


                $kiw_session['mac_address'] = str_replace(array(":", "-"), "", $kiw_session['mac_address']);


                // set max download speed

                $kiw_nomadix_xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\" ?><USG></USG>");

                $kiw_nomadix_xml->addAttribute("COMMAND", "SET_BANDWIDTH_DOWN");
                $kiw_nomadix_xml->addAttribute("SUBSCRIBER", $kiw_session['mac_address']);
                $kiw_nomadix_xml->addChild("BANDWIDTH_DOWN", round($kiw_profile['reply:WISPr-Bandwidth-Max-Down'] / 1024, 0, PHP_ROUND_HALF_UP));

                $kiw_connection = curl_init();


                // set the default ip address

                if (empty($kiw_nas['coa_port']) || $kiw_nas['coa_port'] == "80") {

                    $kiw_nas['coa_port'] = "";

                } else $kiw_nas['coa_port'] = ":{$kiw_nas['coa_port']}";


                curl_setopt($kiw_connection, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
                curl_setopt($kiw_connection, CURLOPT_URL, "http://{$kiw_nas['device_ip']}{$kiw_nas['coa_port']}/usg/command.xml");
                curl_setopt($kiw_connection, CURLOPT_POSTFIELDS, $kiw_nomadix_xml->asXML());
                curl_setopt($kiw_connection, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($kiw_connection, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($kiw_connection, CURLOPT_TIMEOUT, 10);

                unset($kiw_nomadix_xml);


                curl_exec($kiw_connection);
                curl_close($kiw_connection);


                // set max upload speed

                $kiw_nomadix_xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\" ?><USG></USG>");

                $kiw_nomadix_xml->addAttribute("COMMAND", "SET_BANDWIDTH_UP");
                $kiw_nomadix_xml->addAttribute("SUBSCRIBER", $kiw_session['mac_address']);
                $kiw_nomadix_xml->addChild("BANDWIDTH_UP", round($kiw_profile['reply:WISPr-Bandwidth-Max-Up'] / 1024, 0, PHP_ROUND_HALF_UP));

                $kiw_connection = curl_init();


                // set the default ip address

                if (empty($kiw_nas['coa_port']) || $kiw_nas['coa_port'] == "80") {

                    $kiw_nas['coa_port'] = "";

                } else $kiw_nas['coa_port'] = ":{$kiw_nas['coa_port']}";


                curl_setopt($kiw_connection, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
                curl_setopt($kiw_connection, CURLOPT_URL, "http://{$kiw_nas['device_ip']}{$kiw_nas['coa_port']}/usg/command.xml");
                curl_setopt($kiw_connection, CURLOPT_POSTFIELDS, $kiw_nomadix_xml->asXML());
                curl_setopt($kiw_connection, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($kiw_connection, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($kiw_connection, CURLOPT_TIMEOUT, 10);

                unset($kiw_nomadix_xml);


                curl_exec($kiw_connection);
                curl_close($kiw_connection);


            } else {


                send_radius_instruction_to($kiw_db,"coa", $kiw_nas, $kiw_session, $kiw_profile);


            }


        }


    }



}





function send_radius_instruction_to($kiw_db, $kiw_request_type, $kiw_nas, $kiw_session, $kiw_profile = array()){


    if (empty($kiw_session)) return false;
    elseif (empty($kiw_nas)) return false;


    // send request to external coa dispatcher if set

    if (constant("SYNC_COA_HOST") !== "127.0.0.1" && !empty(constant(SYNC_COA_HOST))){


        $kiw_connection = stream_socket_client("tcp://" . SYNC_COA_HOST . ":" . SYNC_COA_PORT, $kiw_error, $kiw_error_str, 5);

        if (!$kiw_connection) return false;
        else {


            $kiw_data['type']        = $kiw_request_type;
            $kiw_data['controller']  = $kiw_nas;
            $kiw_data['session']     = $kiw_session;
            $kiw_data['profile']     = $kiw_profile;

            fwrite($kiw_connection, json_encode($kiw_data));

            fclose($kiw_connection);


        }

        return true;

    }

    // start radius connection

    $kiw_connection = radius_acct_open();


    // add controller information

    radius_add_server($kiw_connection, $kiw_nas['device_ip'], $kiw_nas['coa_port'], $kiw_nas['shared_secret'], 2, 2);


    if ($kiw_request_type == "coa"){


        // create connection

        radius_create_request($kiw_connection, RADIUS_COA_REQUEST);


        // update max upload speed

        if ($kiw_profile['reply:WISPr-Bandwidth-Max-Up'] > 0) radius_put_vendor_int($kiw_connection, 14122, 7, $kiw_profile['reply:WISPr-Bandwidth-Max-Up']);
        if ($kiw_profile['reply:WISPr-Bandwidth-Min-Up'] > 0) radius_put_vendor_int($kiw_connection, 14122, 5, $kiw_profile['reply:WISPr-Bandwidth-Min-Up']);


        // update max download speed

        if ($kiw_profile['reply:WISPr-Bandwidth-Max-Down'] > 0) radius_put_vendor_int($kiw_connection, 14122, 8, $kiw_profile['reply:WISPr-Bandwidth-Max-Down']);
        if ($kiw_profile['reply:WISPr-Bandwidth-Min-Down'] > 0) radius_put_vendor_int($kiw_connection, 14122, 6, $kiw_profile['reply:WISPr-Bandwidth-Min-Down']);


        // update session time to new

        if ($kiw_profile['control:Access-Period'] > 0){

            radius_put_int($kiw_connection, RADIUS_SESSION_TIMEOUT, $kiw_profile['control:Access-Period']);

        } elseif ($kiw_profile['control:Max-All-Session'] > 0){

            radius_put_int($kiw_connection, RADIUS_SESSION_TIMEOUT, $kiw_profile['control:Max-All-Session']);

        } else {

            radius_put_int($kiw_connection, RADIUS_SESSION_TIMEOUT, 0);

        }


        // update idle time-out

        if ($kiw_profile['reply:Idle-Timeout'] > 0) radius_put_int($kiw_connection, RADIUS_IDLE_TIMEOUT, $kiw_profile['reply:Idle-Timeout']);



        if (in_array($kiw_nas['device_type'], array("ruckus_vsz", "ruckus_scg"))) {


            radius_put_string($kiw_connection, RADIUS_ACCT_SESSION_ID, $kiw_session['session_id']);


        } else {


            // total quota if controller support

            if ($kiw_profile['control:Kiwire-Total-Quota'] > 0){


                if ($kiw_nas['vendor'] == "mikrotik"){

                    radius_put_vendor_int($kiw_connection, 14988, 17, $kiw_profile['control:Kiwire-Total-Quota'] * pow(1024, 2));

                } elseif ($kiw_nas['vendor'] == "chillispot") {

                    radius_put_vendor_int($kiw_connection, 14559, 3, $kiw_profile['control:Kiwire-Total-Quota'] * pow(1024, 2));

                }


            } else {


                if ($kiw_nas['vendor'] == "mikrotik"){

                    radius_put_vendor_int($kiw_connection, 14988, 17, 0);

                } elseif ($kiw_nas['vendor'] == "chillispot") {

                    radius_put_vendor_int($kiw_connection, 14559, 3, 0);

                }


            }


            radius_put_string($kiw_connection, RADIUS_USER_NAME, $kiw_session['username']);

            radius_put_addr($kiw_connection, RADIUS_FRAMED_IP_ADDRESS, $kiw_session['ip_address']);

            radius_put_int($kiw_connection, 55, time());


        }


    } else {

        // create connection

        radius_create_request($kiw_connection, RADIUS_DISCONNECT_REQUEST);

        if (in_array($kiw_nas['vendor'], array("mikrotik", "aruba", "xirrus", "chilispot", "chillispot", "fortios", "ruckus_vsz", "ruckus_scg"))) {


            radius_put_string($kiw_connection, RADIUS_USER_NAME, $kiw_session['username']);
            radius_put_addr($kiw_connection, RADIUS_FRAMED_IP_ADDRESS, $kiw_session['ip_address']);


        } elseif (in_array($kiw_nas['vendor'], array("meraki", "ubnt"))) {


            radius_put_string($kiw_connection, RADIUS_ACCT_SESSION_ID, $kiw_session['session_id']);
            radius_put_int($kiw_connection, 55, time());


        } elseif ($kiw_nas['vendor'] == "cisco_wlc") {


            radius_put_addr($kiw_connection, RADIUS_FRAMED_IP_ADDRESS, $kiw_session['ip_address']);
            radius_put_addr($kiw_connection, RADIUS_NAS_IP_ADDRESS, $kiw_session['controller_ip']);
            radius_put_string($kiw_connection, RADIUS_CALLING_STATION_ID, $kiw_session['mac_address']);
            radius_put_string($kiw_connection, RADIUS_ACCT_SESSION_ID, $kiw_session['session_id']);


        } elseif ($kiw_nas['vendor'] == "cambium") {


            radius_put_string($kiw_connection, RADIUS_USER_NAME, $kiw_session['username']);
            radius_put_addr($kiw_connection, RADIUS_NAS_IP_ADDRESS, $kiw_session['controller_ip']);
            radius_put_string($kiw_connection, RADIUS_CALLING_STATION_ID, $kiw_session['mac_address']);


        } elseif ($kiw_nas['vendor'] == "cmcc") {


            radius_put_string($kiw_connection, RADIUS_USER_NAME, $kiw_session['username']);
            radius_put_string($kiw_connection, RADIUS_ACCT_SESSION_ID, $kiw_session['session_id']);


        } elseif ($kiw_nas['vendor'] == "huawei-cloud-ugw") {


            radius_put_string($kiw_connection, RADIUS_ACCT_SESSION_ID, $kiw_session['session_id']);


        }elseif ($kiw_nas['vendor'] == "huawei-nce") {


            radius_put_string($kiw_connection, RADIUS_USER_NAME, $kiw_session['username']);
            radius_put_string($kiw_connection, RADIUS_ACCT_SESSION_ID, $kiw_session['session_id']);


        }else {

            radius_put_string($kiw_connection, RADIUS_ACCT_SESSION_ID, $kiw_session['session_id']);

        }


    }


    // send radius packet to controller

    if (in_array(radius_send_request($kiw_connection), array(RADIUS_COA_ACK, RADIUS_DISCONNECT_ACK))){


        $kiw_request_type = strtoupper($kiw_request_type);

        logger($kiw_session['mac_address'], "Radius: Success sent {$kiw_request_type} to {$kiw_nas['unique_id']}", $kiw_nas['tenant_id']);

        return true;


    } else {


        logger($kiw_session['mac_address'], "Radius: No or unknown response received from {$kiw_nas['unique_id']}", $kiw_nas['tenant_id']);

        return false;


    }


}

function nomadix_dc($kiw_username, $kiw_macaddress, $kiw_nas_ipaddress, $kiw_port){

    $kiw_macaddress = preg_replace('/[^\w]/', '', $kiw_macaddress);

    // create the xml
    $nomadix_req = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\" ?><USG></USG>");

    $nomadix_req->addAttribute("COMMAND", "LOGOUT");
    $nomadix_req->addChild("SUB_MAC_ADDR", $kiw_macaddress);
    $nomadix_req->addChild("SUB_USER_NAME", $kiw_username);

    $nomadix_xml = $nomadix_req->asXML();

    return nomadix_send_xml($kiw_nas_ipaddress, $kiw_port, $nomadix_xml);

}

function nomadix_send_xml($nasip, $port, $nomadix_xml){

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml', 'Content-Length: ' . strlen($nomadix_xml)));
    curl_setopt($ch, CURLOPT_URL, 'http://' . $nasip . ':' . $port . '/usg/command.xml');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $nomadix_xml);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    return curl_exec($ch);

}


function curl_json_post($url,$data_string){

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
    );

    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    return curl_exec($ch);

}