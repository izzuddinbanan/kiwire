<?php


error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);


require_once "kiwire_service_functions.php";

require_once dirname(__FILE__, 3) . "/server/admin/includes/include_config.php";
require_once dirname(__FILE__, 3) . "/server/admin/includes/include_general.php";
require_once dirname(__FILE__, 3) . "/server/user/includes/include_radius.php";

require_once dirname(__FILE__, 3) . "/server/libs/class.sql.helper.php";
require_once dirname(__FILE__, 3) . "/server/libs/class.ip_range.php";
require_once dirname(__FILE__, 3) . "/server/libs/class.kiwire_persistent.php";



$kiwire_server = new Swoole\Http\Server("127.0.0.1", 9955);


$kiw_system = @file_get_contents(dirname(__FILE__, 3) . "/server/custom/system_setting.json");
$kiw_system_enhance = @file_get_contents(dirname(__FILE__, 3) . "/server/custom/system_setting_enhance.json");

$kiw_system = json_decode($kiw_system, true);
$kiw_system_enhance = json_decode($kiw_system_enhance, true);

// if (empty($kiw_system['service_worker'])) $kiw_system['service_worker'] = 1;
// if (empty($kiw_system_enhance['max_conn'])) $kiw_system_enhance['max_conn'] = 1024;
// if (empty($kiw_system_enhance['max_request'])) $kiw_system_enhance['max_request'] = 0;


$kiwire_server->set(
    array(
        'worker_num' => $kiw_system['service_worker'] ?? 1,
        'max_conn' => $kiw_system_enhance['max_conn'] ?? 0,
        'max_request' => $kiw_system_enhance['max_request'] ?? 0,
        'group' => 'nginx',
        'user' => 'nginx',
        'pid_file' => '/run/kiwire-service.pid',
        'daemonize' => 1
    )
);


$kiwire_server->on("start", function () {


    check_logger("Kiwire authentication service started at : " . date("Y-m-d H:i:s"), "general");
});


$kiwire_server->on("request", function ($request, $response) {


    // time for reporting

    $kiw_time = date("YmdH");


    // set the response header to json

    $response->header("Content-Type", "application/json");


    // start mysql connection

    $kiw_db = new kiw_db_conn(array('host' => SYNC_DB1_HOST, 'user' => SYNC_DB1_USER, 'password' => SYNC_DB1_PASSWORD, 'database' => SYNC_DB1_DATABASE, 'port' => SYNC_DB1_PORT));

    $kiw_db->ping();


    // start cache connection

    $kiw_cache = new Swoole\Coroutine\Redis();

    $kiw_cache->connect(SYNC_REDIS_HOST, SYNC_REDIS_PORT, true);


    // collect number of attemp made

    $kiw_cache->incr("REPORT_LOGIN_ATTEMP:{$kiw_time}");


    // request received either from web interface or freeradius

    $kiw_request['action']      = (empty($request->post['action'])) ? $kiw_db->escape($request->get['action']) : $kiw_db->escape($request->post['action']);
    $kiw_request['nasid']       = (empty($request->post['nasid'])) ? $kiw_db->escape($request->get['nasid']) : $kiw_db->escape($request->post['nasid']);

    $kiw_request['username']    = $kiw_db->escape($request->post['username']);
    $kiw_request['mac_address'] = $kiw_db->escape(str_replace("-", ":", strtolower($request->post['macaddress'])));

    $kiw_request['password']    = urldecode(urlencode($request->post['password']));
    $kiw_request['unique_id']   = $kiw_db->escape($request->post['unique_id']);
    $kiw_request['type']        = strtolower($kiw_db->escape($request->post['type']));
    $kiw_request['auth_type']   = $kiw_db->escape(strtolower($request->post['auth_type']));

    $kiw_request['diagnose']    = isset($request->post['diagnose']);


    // FOR CHAP METHOD IF AVAILABLE
    $kiw_request['chap_challenge']  = $kiw_db->escape($request->post['chap-challenge']);
    $kiw_request['chap_password']   = $kiw_db->escape($request->post['chap-password']);

    if(!empty($kiw_request['chap_challenge'] && !empty($kiw_request['chap_password']))) $kiw_request['auth_type'] = "chap";
    

	
    // START CUSTOM VIRTUAL NAS
    if(isset($request->post['station_id'])) {


        // $stationID = explode(":", $request->post['station_id']);
        // // $stationID = explode("-", str_replace(':', '-', $request->post['station_id']));
        // //botswana custom virtual

        // if(in_array($stationID[1], array('vlan20','Gabz City Free Wifi'))){

        //     $kiw_request['nasid'] = $stationID[1];
        //     $kiw_request['ssid'] = $stationID[1];

        //     var_dump($kiw_request['nasid']);
        // }


    }


    if (preg_match('/Meraki/i', $request->get['nasid'])) {


        $kiw_request['nasid'] = "";


        if (empty($request->get['username'])) {


            if ($request->get['action'] == "nas-user") $response->end('Meraki-Controller-' . strtoupper(substr(md5(time()), 0, 4)));
            else $response->end("synchro*123");

            return;
        }
    }


    // if no action provided and no id provided then just return

    if (empty($kiw_request['action'])) {


        $kiw_result = array("reply:Reply-Message" => "Unknown / unsupported function.");

        $response->end(json_encode($kiw_result));

        return;
    }




    //tmp
    if ($kiw_request['username'] == "testHuaweiCloudCampus") {
        //preg_match('/Huawei/i', $kiw_request['nasid'])){
        $kiw_request['nasid'] = "KLIA1";
        $kiw_request['password'] = "testHuaweiCloudCampus";
    }





    if (preg_match('/Meraki/i', $request->post['nasid'])) {

        $kiw_request['nasid'] = "";
    }




    // check if nas-id provided, if not then use called-station-id

    if (empty($kiw_request['nasid'])) {


        // split the data if the nas id using called-station-id and ssid

        $kiw_request['nasid']   = isset($request->post['station_id']) ? urldecode($request->post['station_id']) : urldecode($request->get['station_id']);
        $kiw_request['nasid']   = explode(":", $kiw_request['nasid']);

        $kiw_request['ssid']    = $kiw_request['nasid'][1];
        $kiw_request['nasid']   = str_replace("-", ":", $kiw_request['nasid'][0]);
    } elseif (empty($kiw_request['ssid']) && !empty($request->post['station_id'])) {


        // if no ssid, but called-station-id contain info, then use it

        $kiw_request['ssid'] = explode(":", $request->post['station_id'])[1];
    }


    if (in_array($kiw_request['action'], array("nas-user", "nas-password"))) {


        $kiw_nas_identity = md5($kiw_request['nasid']);


        $kiw_nas = $kiw_cache->get("CONTROLLER_DATA:{$kiw_nas_identity}");

        if (empty($kiw_nas)) {


            $kiw_nas = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_controller WHERE device_type = 'controller' AND unique_id = '{$kiw_request['nasid']}' LIMIT 1")[0];

            $kiw_cache->set("CONTROLLER_DATA:{$kiw_nas_identity}", $kiw_nas, 1800);
        }


        $kiw_random = substr(md5(time()), 0, 12);


        if (empty($kiw_nas['unique_id'])) $kiw_nas['unique_id'] = "DUMMY" . strtoupper(substr($kiw_random, 0, 4));
        if (empty($kiw_nas['shared_secret'])) $kiw_nas['shared_secret'] = substr($kiw_random, 4, 8);

        if ($kiw_request['action'] == "nas-user") {

            $response->end($kiw_nas['unique_id']);
        } else $response->end($kiw_nas['shared_secret']);


        return;
    }



    // if no nas-identifier, respond error message

    if (empty($kiw_request['nasid'])) {


        // $response->status(404);
        $response->end(json_encode(array("reply:Reply-Message" => "NAS not configure")));

        return;
    }




    // duplicate check if within 10 seconds

    $kiw_dupe_id = md5(strtolower($kiw_request['action'] . $kiw_request['nasid'] . $kiw_request['username'] . $kiw_request['mac_address'] . $kiw_request['password'] . $kiw_request['unique_id'] . $kiw_request['type']));

    $kiw_dupe_check = $kiw_cache->get("REQUEST_DUPLICATE:{$kiw_dupe_id}");


    // check if we already respond for the past 60 seconds
    // if not then proceed, if yes then display the same result

    if (empty($kiw_dupe_check)) {


        // release some memory

        unset($kiw_dupe_check);


        // check if tenant id provided

        $kiw_request['tenant_id'] = $kiw_db->escape($request->post['tenant_id']);


        // check for tenant details

        if (empty($kiw_request['tenant_id'])) {


            $kiw_id_hash = md5($kiw_request['nasid']);

            $kiw_temp = $kiw_cache->hGetAll("NAS_TENANT:{$kiw_id_hash}");


            if (empty($kiw_temp)) {


                $kiw_temp = $kiw_db->query("SELECT SQL_CACHE tenant_id,is_virtual FROM kiwire_controller WHERE unique_id = '{$kiw_request['nasid']}' LIMIT 1")[0];

                if (empty($kiw_temp)) $kiw_temp = "XXinvalidXX";
            

                $kiw_cache->hMSet("NAS_TENANT:{$kiw_id_hash}", $kiw_temp, 1800);

                $kiw_request['tenant_id'] = $kiw_temp['tenant_id'];

            } else {

                $kiw_request['tenant_id'] = $kiw_temp["tenant_id"];
            }


            unset($kiw_temp);
            unset($kiw_id_hash);
        }



        if ($kiw_request['tenant_id'] != "XXinvalidXX") {


            // get the device vendor for attribute preparation

            $kiw_request['device_vendor'] = $kiw_db->escape($request->post['device_vendor']);


            if (empty($kiw_request['device_vendor'])) {


                $kiw_id_hash = md5($kiw_request['nasid']);


                $kiw_request['device_vendor'] = $kiw_cache->get("DEVICE_VENDOR:{$kiw_id_hash}");


                if (empty($kiw_request['device_vendor'])) {


                    $kiw_temp = $kiw_db->query("SELECT SQL_CACHE vendor FROM kiwire_controller WHERE unique_id = '{$kiw_request['nasid']}' LIMIT 1")[0];

                    if (empty($kiw_temp)) $kiw_temp = array("vendor" => null);


                    $kiw_cache->set("DEVICE_VENDOR:{$kiw_id_hash}", $kiw_temp['vendor'], 1800);

                    $kiw_request['device_vendor'] = $kiw_temp['vendor'];


                    unset($kiw_temp);
                }


                unset($kiw_id_hash);
            }



            // collect initial data for processing

            $kiw_request['ip_address']      = $kiw_db->escape($request->post['ipaddress']);
            $kiw_request['ipv6_address']    = $kiw_db->escape($request->post['ipv6address']);
            $kiw_request['zone']            = $kiw_db->escape($request->post['zone']);
            $kiw_request['ssid']            = $kiw_db->escape($request->post['ssid']);
            $kiw_request['vlan']            = $kiw_db->escape($request->post['vlan']);
            $kiw_request['controller_zone'] = $kiw_db->escape($request->post['controller_zone']);
            $kiw_request['controller_ip']   = $kiw_db->escape($request->post['controller_ip']);

            $kiw_request['system']          = $kiw_db->escape($request->post['system']);
            $kiw_request['class']           = $kiw_db->escape($request->post['class']);
            $kiw_request['brand']           = $kiw_db->escape($request->post['brand']);
            $kiw_request['model']           = $kiw_db->escape($request->post['model']);


            // try to populate zone based on all data that we have

            if (empty($kiw_request['zone'])) {


                $kiw_zone_attr = $kiw_cache->get("ZONE_DATA_ATTR:{$kiw_request['tenant_id']}");

                if (empty($kiw_zone_attr)) {

                    $kiw_zone_attr = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_zone_child WHERE tenant_id = '{$kiw_request['tenant_id']}' AND master_id IN (SELECT DISTINCT(name) FROM kiwire_zone WHERE kiwire_zone.tenant_id = '{$kiw_request['tenant_id']}' AND status = 'y') ORDER BY priority DESC");

                    $kiw_cache->set("ZONE_DATA_ATTR:{$kiw_request['tenant_id']}", $kiw_zone_attr, 1800);
                }


                foreach ($kiw_zone_attr as $attr) {

                    if (empty($attr['nasid']) || strtolower($attr['nasid']) == strtolower($kiw_request['nasid'])) {

                        if (empty($attr['vlan']) || strtolower($attr['vlan']) == strtolower($kiw_request['vlan'])) {

                            if (empty($attr['ssid']) || strtolower($attr['ssid']) == strtolower($kiw_request['ssid'])) {

                                if (empty($attr['ipaddr']) || ipv4_in_range($kiw_request['ip_address'], $attr['ipaddr'])) {

                                    if (empty($attr['ipv6addr']) || ipv6_in_range($kiw_request['ipv6_address'], $attr['ipv6addr'])) {

                                        if (empty($attr['dzone']) || strtolower($attr['dzone']) == strtolower($kiw_request['controller_zone'])) {


                                            $kiw_request['zone'] = $attr['master_id'];

                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }


                unset($attr);
                unset($kiw_zone_attr);


                // GET zone from kiwire_device_history
                if (empty($kiw_request['zone'])) {
                    $kiw_temp = $kiw_db->query("SELECT last_zone FROM kiwire_device_history WHERE mac_address = '{$kiw_request['mac_address']}' AND tenant_id = '{$kiw_request['tenant_id']}' LIMIT 1")[0];

                    $kiw_request['zone'] = $kiw_temp['last_zone'];

                    unset($kiw_temp);
                }

                // if still zone cannot be defined, set to nozone

                if (empty($kiw_request['zone'])) {

                    $kiw_request['zone'] = "nozone";
                }

                if (in_array($kiw_request['tenant_id'], array("GBTB_UAT", "GBTB"))){

                    $kiw_gbtb = $kiw_cache->get("GBTB_ZONE_DATA:" . str_replace(array(":", "-"), "", strtoupper($kiw_request['mac_address'])));

                    if (!empty($kiw_gbtb)){

                        $kiw_request['zone'] = $kiw_gbtb;
			
			            $kiw_cache->del("GBTB_ZONE_DATA:" . str_replace(array(":", "-"), "", strtoupper($kiw_request['mac_address'])));

                    }

		        }


                unset($kiw_temp);
            }


            // check for device details

            if (empty($kiw_request['system']) || empty($kiw_request['class'])) {


                $kiw_temp = $kiw_db->query("SELECT details FROM kiwire_device_history WHERE mac_address = '{$kiw_request['mac_address']}' AND tenant_id = '{$kiw_request['tenant_id']}' LIMIT 1")[0];

                $kiw_temp = json_decode($kiw_temp['details'], true);

                if (is_array($kiw_temp)) {

                    $kiw_request['system'] = $kiw_temp['system'];
                    $kiw_request['class'] = $kiw_temp['class'];
                    $kiw_request['brand'] = $kiw_temp['brand'];
                    $kiw_request['model'] = $kiw_temp['model'];
                }

                unset($kiw_temp);
            }


            if (empty($kiw_request['system'])) $kiw_request['system'] = "Unknown";
            if (empty($kiw_request['class'])) $kiw_request['class'] = "Unknown";
            if (empty($kiw_request['brand'])) $kiw_request['brand'] = "Unknown";
            if (empty($kiw_request['model'])) $kiw_request['model'] = "Unknown";


            // check for action to be taken

            if ($kiw_request['action'] == "authorize") {


                // get data for notification

                $kiw_notification = $kiw_cache->get("NOTIFICATION_DATA:{$kiw_request['tenant_id']}");

                if (empty($kiw_notification)) {


                    $kiw_notification = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_notification WHERE tenant_id = '{$kiw_request['tenant_id']}' LIMIT 1")[0];

                    if (empty($kiw_notification)) $kiw_notification = array("dummy" => true);

                    $kiw_cache->set("NOTIFICATION_DATA:{$kiw_request['tenant_id']}", $kiw_notification, 1800);
                }



                // check policy arrangement

                // $kiw_check_concurrent = "check_concurrent_user,";

                $kiw_check_policy = $kiw_cache->get("CHECK_ARRANGEMENT:{$kiw_request['tenant_id']}");

                // $kiw_check_policy = $kiw_check_concurrent . '' . $kiw_check_policy;


                if (empty($kiw_check_policy)) {


                    $kiw_check_policy = $kiw_db->query("SELECT SQL_CACHE check_arrangement_login FROM kiwire_clouds WHERE tenant_id = '{$kiw_request['tenant_id']}' LIMIT 1")[0];

                    if (empty($kiw_check_policy)) $kiw_check_policy = "";
                    else $kiw_check_policy = $kiw_check_policy['check_arrangement_login'];

                    // Add check_concurrent_user as first default policy arrangement
                    // else $kiw_check_policy = $kiw_check_concurrent . '' . $kiw_check_policy['check_arrangement_login'];

                    $kiw_cache->set("CHECK_ARRANGEMENT:{$kiw_request['tenant_id']}", $kiw_check_policy, 1800);
                }

                $kiw_check_policy = explode(",", $kiw_check_policy);


                // if no policy setup, use default

                if (empty($kiw_check_policy)) {

                    // $kiw_check_policy = array("check_active", "check_password", "check_password_policy", "check_zone_limit", "check_allow_mac", "check_allow_zone", "check_allow_credit", "check_allow_quota", "check_allow_simultaneous", "activate_voucher_account", "reporting_process");

                    $kiw_check_policy = array("check_active", "check_password", "check_password_policy", "check_zone_limit", "check_allow_mac", "check_allow_zone", "check_allow_credit", "check_allow_quota", "check_allow_simultaneous", "activate_voucher_account", "reporting_process");
                }


                // the the account details

                $kiw_user = $kiw_db->query("SELECT * FROM kiwire_account_auth WHERE tenant_id = '{$kiw_request['tenant_id']}' AND username = '{$kiw_request['username']}' LIMIT 1")[0];


                if (!empty($kiw_user)) {


                    // increase counter for authenticate request

                    $kiw_cache->incr("REPORT_LOGIN_REQUEST:{$kiw_time}:{$kiw_request['tenant_id']}:{$kiw_request['zone']}");


                    // get the user profile

                    $kiw_profile = $kiw_cache->get("PROFILE_DATA:{$kiw_request['tenant_id']}:{$kiw_user['profile_curr']}");

                    if (empty($kiw_profile)) {


                        $kiw_profile = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_profiles WHERE tenant_id = '{$kiw_request['tenant_id']}' AND name = '{$kiw_user['profile_curr']}' LIMIT 1")[0];

                        if (empty($kiw_profile)) $kiw_profile = array("dummy" => true);

                        $kiw_cache->set("PROFILE_DATA:{$kiw_request['tenant_id']}:{$kiw_user['profile_curr']}", $kiw_profile, 1800);
                    }



                    if (!$kiw_profile['dummy']) {


                        // check if login failed to skip campaign

                        $kiw_login_failed = false;


                        // check if zone have default profile


                        if ($kiw_request['zone'] != "nozone" && $kiw_user['profile_curr'] != "Temp_Access") {


                            $kiw_profile_temp = $kiw_cache->get("PROFILE_POLICY:{$kiw_request['tenant_id']}:{$kiw_request['zone']}");

                            if (empty($kiw_profile_temp)) {


                                $kiw_profile_temp = $kiw_db->query("SELECT force_profile FROM kiwire_zone WHERE tenant_id = '{$kiw_request['tenant_id']}' AND name = '{$kiw_request['zone']}' LIMIT 1")[0];

                                if (empty($kiw_profile_temp)) $kiw_profile_temp = array("dummy" => true);

                                $kiw_cache->set("PROFILE_POLICY:{$kiw_request['tenant_id']}:{$kiw_request['zone']}", $kiw_profile_temp, 1800);
                            }


                            if (!empty($kiw_profile_temp['force_profile']) && $kiw_profile_temp['force_profile'] !== "none") {


                                $kiw_profile = $kiw_cache->get("PROFILE_DATA:{$kiw_request['tenant_id']}:{$kiw_profile_temp['force_profile']}");

                                if (empty($kiw_profile)) {


                                    $kiw_profile = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_profiles WHERE tenant_id = '{$kiw_request['tenant_id']}' AND name = '{$kiw_profile_temp['force_profile']}' LIMIT 1")[0];

                                    if (empty($kiw_profile)) $kiw_profile = array("dummy" => true);

                                    $kiw_cache->set("PROFILE_DATA:{$kiw_request['tenant_id']}:{$kiw_profile_temp['force_profile']}", $kiw_profile, 1800);
                                }

                                $kiw_result = json_decode($kiw_profile['attribute'], true);
                            }


                            unset($kiw_profile_temp);
                        }




                        // check if available device policy enable

                        if (empty($kiw_result) && $kiw_user['profile_curr'] != "Temp_Access") {


                            $kiw_device_policies = $kiw_cache->get("DEVICE_POLICY:{$kiw_request['tenant_id']}");


                            if (empty($kiw_device_policies)) {


                                $kiw_device_policies = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_device_policy WHERE tenant_id = '{$kiw_request['tenant_id']}' AND status = 'y' ORDER BY priority DESC");

                                if (empty($kiw_device_policies)) $kiw_device_policies = array("dummy" => true);

                                $kiw_cache->set("DEVICE_POLICY:{$kiw_request['tenant_id']}", $kiw_device_policies, 3600);
                            }


                            if (!$kiw_device_policies['dummy']) {


                                foreach ($kiw_device_policies as $kiw_device_policy) {


                                    $kiw_matched = false;


                                    if ($kiw_device_policy['zone'] == 'none' || $kiw_device_policy['zone'] == $kiw_request['zone']) {


                                        if ($kiw_device_policy['type'] == "Brand") {

                                            if (strpos($kiw_request['brand'], $kiw_device_policy['type']) != false) {

                                                $kiw_matched = true;
                                            }
                                        } elseif ($kiw_device_policy['type'] == "Type") {

                                            if (strpos($kiw_request['class'], $kiw_device_policy['type']) != false) {

                                                $kiw_matched = true;
                                            }
                                        } elseif ($kiw_device_policy['type'] == "Model") {

                                            if (strpos($kiw_request['model'], $kiw_device_policy['type']) != false) {

                                                $kiw_matched = true;
                                            }
                                        } elseif ($kiw_device_policy['type'] == "OS") {

                                            if (strpos($kiw_request['system'], $kiw_device_policy['type']) != false) {

                                                $kiw_matched = true;
                                            }
                                        }


                                        if ($kiw_matched == true) {

                                            if (!empty($kiw_device_policy['profile']) && $kiw_device_policy['profile'] != 'none') {


                                                $kiw_profile = $kiw_cache->get("PROFILE_DATA:{$kiw_request['tenant_id']}:{$kiw_device_policy['profile']}");

                                                if (empty($kiw_profile)) {


                                                    $kiw_profile = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_profiles WHERE tenant_id = '{$kiw_request['tenant_id']}' AND name = '{$kiw_device_policy['profile']}' LIMIT 1")[0];

                                                    if (empty($kiw_profile)) $kiw_profile = array("dummy" => true);

                                                    $kiw_cache->set("PROFILE_DATA:{$kiw_request['tenant_id']}:{$kiw_device_policy['profile']}", $kiw_profile, 1800);
                                                }
                                            }


                                            $kiw_result = json_decode($kiw_profile['attribute'], true);


                                            check_logger("T:{$kiw_request['tenant_id']} N:{$kiw_request['nasid']} M:{$kiw_request['mac_address']} = Matched Device Policy [ {$kiw_device_policy['name']} ] Profile: {$kiw_profile['name']}", $kiw_user['tenant_id']);


                                            break;
                                        }
                                    }
                                }


                                // if no profile set then use back normal

                                if (empty($kiw_result)) {


                                    $kiw_result = json_decode($kiw_profile['attribute'], true);
                                }
                            } else {


                                // update profile info first

                                $kiw_result = json_decode($kiw_profile['attribute'], true);
                            }
                        } else {


                            // update profile info first

                            $kiw_result = json_decode($kiw_profile['attribute'], true);
                        }


                        // if the profile has custom attribute, the add first

                        if (strlen($kiw_profile['attribute_custom']) > 0) {


                            foreach (json_decode($kiw_profile['attribute_custom'], true) as $kiw_key => $kiw_value) {

                                $kiw_result[$kiw_key] = $kiw_value;
                            }


                            unset($kiw_key);
                            unset($kiw_value);
                            unset($kiw_profile['attribute_custom']);
                        }




                        unset($kiw_profile['attribute']);
                        unset($kiw_profile['attribute_custom']);


                        // if the user has custom profile, then merge

                        if (strlen($kiw_user['profile_cus']) > 0) {


                            foreach (json_decode($kiw_user['profile_cus'], true) as $kiw_key => $kiw_value) {

                                if ($kiw_key == "quota") {

                                    $kiw_result['control:Kiwire-Total-Quota'] += ($kiw_value / 1024 / 1024);
                                }
                                // $kiw_result[$kiw_key] = $kiw_value;

                            }


                            unset($kiw_key);
                            unset($kiw_value);
                            unset($kiw_user['profile_cus']);
                        }



                        // do check as per set by client

                        foreach ($kiw_check_policy as $kiw_check_item) {


                            // if function existed, then do checking

                            if (function_exists($kiw_check_item)) {

                                if (in_array($kiw_check_item, array("send_pms_payment", 'check_allow_credit', 'check_allow_quota')))
                                    $kiw_result = $kiw_check_item($kiw_db, $kiw_cache, $kiw_result, $kiw_notification, $kiw_user, $kiw_request, $kiw_profile);
                                else $kiw_result = $kiw_check_item($kiw_db, $kiw_cache, $kiw_result, $kiw_notification, $kiw_user, $kiw_request);
                            }


                            // if there is error, then stop checking

                            if (isset($kiw_result['Status']) && $kiw_result['Status'] == "Error") {


                                $kiw_login_failed = true;


                                $kiw_error_hash = md5($kiw_result['reply:Reply-Message']);


                                if ($kiw_request['diagnose'] == false) {

                                    $kiw_cache->incr("REPORT_ERROR_COUNT:{$kiw_time}:{$kiw_request['tenant_id']}:{$kiw_error_hash}");
                                }


                                // log if there is an error

                                check_logger("T:{$kiw_request['tenant_id']} U:{$kiw_request['username']} N:{$kiw_request['nasid']} M:{$kiw_request['mac_address']} = {$kiw_result['reply:Reply-Message']}", $kiw_user['tenant_id']);


                                // remove internal status from outsider

                                unset($kiw_result['Status']);

                                break;
                            }
                        }


                        // do filtering so that only reply and control attribute being send to controller

                        $kiw_result_temp = array();

                        foreach ($kiw_result as $key => $value) {


                            $kiw_test = explode(":", $key);


                            // check if Kiwire-Total-Byte, then need to add as attribute in response

                            if ($kiw_test[0] == "reply" || $kiw_test[1] == "Cleartext-Password" || $kiw_test[1] == "Kiwire-Total-Quota") {


                                if ($kiw_test[1] == "Kiwire-Total-Quota") {


                                    // check if we int quota larger than 32-bit

                                    $kiw_int_max = pow(2, 32);

                                    $kiw_result_gw = 0;


                                    // if profile quota larger than 4 Gb, then need to update gigawords

                                    if ($value > $kiw_int_max) {


                                        $kiw_result_gw = (int)($value / $kiw_int_max);

                                        if ($kiw_result_gw > 0) {

                                            $value = (int)($value % $kiw_int_max);
                                        }
                                    }


                                    // check based on device vendor

                                    if ($kiw_request['device_vendor'] == "mikrotik") {


                                        $kiw_result_temp["reply:Mikrotik-Total-Limit"] = $value;

                                        $kiw_result_temp["reply:Mikrotik-Total-Limit-Gigawords"] = $kiw_result_gw;
                                    } elseif ($kiw_request['device_vendor'] == "chillispot") {


                                        $kiw_result_temp["reply:ChilliSpot-Max-Total-Octets"] = $value;

                                        $kiw_result_temp["reply:ChilliSpot-Max-Total-Gigawords"] = $kiw_result_gw;
                                    } elseif ($kiw_request['device_vendor'] == "cambium") {


                                        $kiw_result_temp["reply:CAMB-Traffic-Quota-Limit-Total"] = $value;

                                        $kiw_result_temp["reply:CAMB-Traffic-Quota-Limit-Total-Gigwords"] = $kiw_result_gw;
                                    }
                                } elseif ($kiw_test[1] == "Cleartext-Password") {

                                    $kiw_result_temp[$key] = array("do_xlat" => false, "value" => $value);
                                } else $kiw_result_temp[$key] = $value;
                            }
                            else $kiw_result_temp[$key] = $value;
                        }


                        unset($kiw_test);

                        unset($kiw_result);


                        if ($kiw_result_temp['reply:Session-Timeout'] < 0) {

                            check_logger("T:{$kiw_request['tenant_id']} N:{$kiw_request['nasid']} M:{$kiw_request['mac_address']} = Please inform Developer team", $kiw_user['tenant_id']);
                        }


                        // if no error then log as success

                        if (empty($kiw_error_hash)) {

                            $kiw_temp = str_replace(array(":", "-"), "", $kiw_request['mac_address']);

                            $kiw_cache->set("LOGIN_PROFILE:{$kiw_request['tenant_id']}:{$kiw_temp}", $kiw_profile['name'], 60);

                            check_logger("T:{$kiw_request['tenant_id']} N:{$kiw_request['nasid']} M:{$kiw_request['mac_address']} = Login Success [ {$kiw_user['username']} ]", $kiw_user['tenant_id']);
                        }


                        // cache result for 60 seconds

                        $kiw_cache->set("REQUEST_DUPLICATE:{$kiw_dupe_id}", $kiw_result_temp, 10);

                        $response->end(json_encode($kiw_result_temp));


                        // start campaign check if login success

                        if ($kiw_login_failed == false && $kiw_request['diagnose'] == false) {


                            // go(function () use ($kiw_db, $kiw_request, $kiw_time) {


                            //     // create redis connection for campaign

                            //     $kiw_cache = new Swoole\Coroutine\Redis();

                            //     $kiw_cache->connect(SYNC_REDIS_HOST, SYNC_REDIS_PORT, true);


                            //     // save in cache for 30 mins

                            //     $kiw_campaign = $kiw_cache->get("CAMPAIGN_LOGIN_AVAILABLE:{$kiw_request['tenant_id']}:{$kiw_request['type']}");

                            //     if (empty($kiw_campaign)) {


                            //         $kiw_campaign = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_campaign_manager WHERE c_trigger = 'login' AND status = 'y' AND tenant_id = '{$kiw_request['tenant_id']}'");

                            //         if (empty($kiw_campaign)) $kiw_campaign = array("dummy" => true);

                            //         $kiw_cache->set("CAMPAIGN_LOGIN_AVAILABLE:{$kiw_request['tenant_id']}:{$kiw_request['type']}", $kiw_campaign, 1800);
                            //     }


                            //     if ($kiw_campaign['dummy'] != true) {


                            //         $kiw_campaign_time = (new DateTime("now", new DateTimeZone("UTC")))->setTimezone(new DateTimeZone("Asia/Kuala_Lumpur"))->format("H");

                            //         $kiw_user = $kiw_db->query("SELECT tenant_id,username,fullname,email_address,phone_number,campaign_history FROM kiwire_account_auth WHERE username = '{$kiw_request['username']}' AND tenant_id = '{$kiw_request['tenant_id']}' LIMIT 1")[0];


                            //         $kiw_user_campaign = json_decode($kiw_user['campaign'], true);


                            //         foreach ($kiw_campaign as $kiw_campaign_detail) {


                            //             // check for time to execute campaign if available

                            //             if ($kiw_campaign_detail['c_interval'] == "always" || (($kiw_campaign_detail['c_interval_time_start'] >= $kiw_campaign_time) && ($kiw_campaign_detail['c_interval_time_stop'] <= $kiw_campaign_time))) {


                            //                 // check for zone if available

                            //                 if ($kiw_campaign_detail['target'] == "all" || ($kiw_campaign_detail['zone'] == $kiw_request['zone'])) {


                            //                     if (!empty($kiw_user)) {


                            //                         // update the last time this campaign sent to user

                            //                         $kiw_user_campaign[$kiw_campaign_detail['id']] = time();


                            //                         // record if campaign triggered

                            //                         $kiw_cache->incr("REPORT_OFFLINE_CAMPAIGN:{$kiw_time}:{$kiw_request['tenant_id']}:" . base64_encode($kiw_campaign_detail['name']));


                            //                         // action to the campaign

                            //                         if ($kiw_campaign_detail['action_method'] == "email") {


                            //                             $kiw_template = $kiw_cache->get("CAMPAIGN_OFFLINE_TEMPLATE:{$kiw_campaign_detail['tenant_id']}:" . md5($kiw_campaign_detail['action_value']));

                            //                             if (empty($kiw_template)) {


                            //                                 $kiw_template = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_html_template WHERE tenant_id = '{$kiw_campaign_detail['tenant_id']}' AND type = 'email' AND name = '{$kiw_campaign_detail['action_value']}' LIMIT 1")[0];

                            //                                 if (empty($kiw_template)) $kiw_template = array("dummy" => true);

                            //                                 $kiw_cache->set("CAMPAIGN_OFFLINE_TEMPLATE:{$kiw_campaign_detail['tenant_id']}:" . md5($kiw_campaign_detail['action_value']), $kiw_template, 1800);
                            //                             }


                            //                             if (!empty($kiw_template['content']) && filter_var($kiw_user['email_address'], FILTER_VALIDATE_EMAIL)) {


                            //                                 $kiw_email['action']        = "send_email";
                            //                                 $kiw_email['tenant_id']     = $kiw_campaign_detail['tenant_id'];
                            //                                 $kiw_email['email_address'] = $kiw_user['email_address'];
                            //                                 $kiw_email['subject']       = $kiw_template['subject'];
                            //                                 $kiw_email['content']       = htmlentities($kiw_template['content']);
                            //                                 $kiw_email['name']          = $kiw_user['fullname'];

                            //                                 unset($kiw_template);


                            //                                 $kiw_temp = curl_init();

                            //                                 curl_setopt($kiw_temp, CURLOPT_URL, "http://127.0.0.1:9956");
                            //                                 curl_setopt($kiw_temp, CURLOPT_POST, true);
                            //                                 curl_setopt($kiw_temp, CURLOPT_POSTFIELDS, http_build_query($kiw_email));
                            //                                 curl_setopt($kiw_temp, CURLOPT_RETURNTRANSFER, true);
                            //                                 curl_setopt($kiw_temp, CURLOPT_TIMEOUT, 5);
                            //                                 curl_setopt($kiw_temp, CURLOPT_CONNECTTIMEOUT, 5);

                            //                                 unset($kiw_email);

                            //                                 curl_exec($kiw_temp);
                            //                                 curl_close($kiw_temp);

                            //                                 unset($kiw_temp);
                            //                             } else {

                            //                                 unset($kiw_template);
                            //                             }
                            //                         } elseif ($kiw_campaign_detail['action_method'] == "sms") {



                            //                             $kiw_template = $kiw_cache->get("CAMPAIGN_OFFLINE_TEMPLATE:{$kiw_campaign_detail['tenant_id']}:" . md5($kiw_campaign_detail['action_value']));

                            //                             if (empty($kiw_template)) {


                            //                                 $kiw_template = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_html_template WHERE tenant_id = '{$kiw_campaign_detail['tenant_id']}' AND type = 'sms' AND name = '{$kiw_campaign_detail['action_value']}' LIMIT 1")[0];

                            //                                 if (empty($kiw_template)) $kiw_template = array("dummy" => true);

                            //                                 $kiw_cache->set("CAMPAIGN_OFFLINE_TEMPLATE:{$kiw_campaign_detail['tenant_id']}:" . md5($kiw_campaign_detail['action_value']), $kiw_template, 1800);
                            //                             }


                            //                             if (!empty($kiw_template['content']) && !empty($kiw_user['phone_number'])) {


                            //                                 $kiw_sms['action']       = "send_sms";
                            //                                 $kiw_sms['tenant_id']    = $kiw_campaign_detail['tenant_id'];
                            //                                 $kiw_sms['phone_number'] = $kiw_user['phone_number'];
                            //                                 $kiw_sms['content']      = strip_tags($kiw_template['content']);

                            //                                 unset($kiw_template);


                            //                                 $kiw_temp = curl_init();

                            //                                 curl_setopt($kiw_temp, CURLOPT_URL, "http://127.0.0.1:9956");
                            //                                 curl_setopt($kiw_temp, CURLOPT_POST, true);
                            //                                 curl_setopt($kiw_temp, CURLOPT_POSTFIELDS, http_build_query($kiw_sms));
                            //                                 curl_setopt($kiw_temp, CURLOPT_RETURNTRANSFER, true);
                            //                                 curl_setopt($kiw_temp, CURLOPT_TIMEOUT, 5);
                            //                                 curl_setopt($kiw_temp, CURLOPT_CONNECTTIMEOUT, 5);

                            //                                 unset($kiw_sms);

                            //                                 curl_exec($kiw_temp);
                            //                                 curl_close($kiw_temp);

                            //                                 unset($kiw_temp);
                            //                             } else {

                            //                                 unset($kiw_template);
                            //                             }
                            //                         } elseif ($kiw_campaign_detail['action_method'] == "api") {


                            //                             // send api to user

                            //                             $kiw_campaign_detail['action_value'] = str_replace("{{tenant_id}}", $kiw_user['tenant_id'], $kiw_campaign_detail['action_value']);
                            //                             $kiw_campaign_detail['action_value'] = str_replace("{{username}}", $kiw_user['username'], $kiw_campaign_detail['action_value']);
                            //                             $kiw_campaign_detail['action_value'] = str_replace("{{fullname}}", $kiw_user['fullname'], $kiw_campaign_detail['action_value']);

                            //                             $kiw_temp = curl_init();

                            //                             curl_setopt($kiw_temp, CURLOPT_URL, $kiw_campaign_detail['action_value']);
                            //                             curl_setopt($kiw_temp, CURLOPT_POST, false);
                            //                             curl_setopt($kiw_temp, CURLOPT_RETURNTRANSFER, true);
                            //                             curl_setopt($kiw_temp, CURLOPT_TIMEOUT, 5);
                            //                             curl_setopt($kiw_temp, CURLOPT_CONNECTTIMEOUT, 5);

                            //                             unset($kiw_content);

                            //                             curl_exec($kiw_temp);
                            //                             curl_close($kiw_temp);

                            //                             unset($kiw_temp);
                            //                         } elseif ($kiw_campaign_detail['action_method'] == "webpush") {


                            //                             // send api to user

                            //                             $kiw_campaign_detail['action_value'] = str_replace("{{tenant_id}}", $kiw_user['tenant_id'], $kiw_campaign_detail['action_value']);
                            //                             $kiw_campaign_detail['action_value'] = str_replace("{{username}}", $kiw_user['username'], $kiw_campaign_detail['action_value']);
                            //                             $kiw_campaign_detail['action_value'] = str_replace("{{fullname}}", $kiw_user['fullname'], $kiw_campaign_detail['action_value']);

                            //                             $kiw_temp = curl_init();

                            //                             curl_setopt($kiw_temp, CURLOPT_URL, $kiw_campaign_detail['action_value']);
                            //                             curl_setopt($kiw_temp, CURLOPT_POST, false);
                            //                             curl_setopt($kiw_temp, CURLOPT_RETURNTRANSFER, true);
                            //                             curl_setopt($kiw_temp, CURLOPT_TIMEOUT, 5);
                            //                             curl_setopt($kiw_temp, CURLOPT_CONNECTTIMEOUT, 5);

                            //                             unset($kiw_content);

                            //                             curl_exec($kiw_temp);
                            //                             curl_close($kiw_temp);

                            //                             unset($kiw_temp);
                            //                         }
                            //                     }
                            //                 }
                            //             }
                            //         }


                            //         $kiw_cache->close();


                            //         // update campaign - user data

                            //         $kiw_user_campaign = json_encode($kiw_user_campaign);

                            //         $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), campaign_history = '{$kiw_user_campaign}' WHERE username = '{$kiw_request['username']}' AND tenant_id = '{$kiw_request['tenant_id']}' LIMIT 1");
                            //     }


                            //     $kiw_cache->close();
                            // });


                            if ($kiw_request['diagnose'] == false) {

                                $kiw_cache->incr("REPORT_LOGIN_SUCCESS:{$kiw_time}:{$kiw_request['tenant_id']}:{$kiw_request['zone']}");
                                $kiw_cache->incr("REPORT_LOGIN_PROFILE:{$kiw_time}:{$kiw_request['tenant_id']}:{$kiw_request['zone']}:{$kiw_profile['name']}");
                                $kiw_cache->incr("REPORT_LOGIN_CLASS:{$kiw_time}:{$kiw_request['tenant_id']}:{$kiw_request['zone']}:{$kiw_request['class']}");
                                $kiw_cache->incr("REPORT_LOGIN_SYSTEM:{$kiw_time}:{$kiw_request['tenant_id']}:{$kiw_request['zone']}:{$kiw_request['system']}");
                                $kiw_cache->incr("REPORT_LOGIN_BRAND:{$kiw_time}:{$kiw_request['tenant_id']}:{$kiw_request['zone']}:{$kiw_request['brand']}");
                                $kiw_cache->incr("REPORT_LOGIN_STATS:{$kiw_time}:{$kiw_request['tenant_id']}:{$kiw_request['nasid']}");
                            }
                        } else {

                            if ($kiw_request['diagnose'] == false) {

                                $kiw_cache->incr("REPORT_LOGIN_FAILED:{$kiw_time}:{$kiw_request['tenant_id']}:{$kiw_request['zone']}");
                            }
                        }


                        return;
                    } else {


                        if ($kiw_request['diagnose'] == false) {

                            $kiw_cache->incr("REPORT_LOGIN_FAILED:{$kiw_time}:{$kiw_request['tenant_id']}:{$kiw_request['zone']}");
                        }


                        $kiw_result = array("reply:Reply-Message" => "This account has not subscribed to any profile");

                        check_logger("T:{$kiw_request['tenant_id']} N:{$kiw_request['nasid']} M:{$kiw_request['mac_address']} = {$kiw_result['reply:Reply-Message']}", $kiw_user['tenant_id']);

                        $kiw_cache->set("REQUEST_DUPLICATE:{$kiw_dupe_id}", $kiw_result, 10);

                        $response->end(json_encode($kiw_result));

                        return;
                    }
                } else {


                    if ($kiw_request['diagnose'] == false) {

                        $kiw_cache->incr("REPORT_LOGIN_FAILED:{$kiw_time}:{$kiw_request['tenant_id']}:{$kiw_request['zone']}");
                    }


                    $kiw_result = array("reply:Reply-Message" => "Not a valid account or credential");

                    check_logger("T:{$kiw_request['tenant_id']} N:{$kiw_request['nasid']} M:{$kiw_request['mac_address']} = {$kiw_result['reply:Reply-Message']}", $kiw_user['tenant_id']);

                    $kiw_cache->set("REQUEST_DUPLICATE:{$kiw_dupe_id}", $kiw_result, 10);

                    $response->end(json_encode($kiw_result));


                    return;
                }
            } elseif ($kiw_request['action'] == "accounting") {


                $kiw_request['session_id']   = $kiw_db->escape($request->post['session_id']);
                $kiw_request['session_time'] = $kiw_db->escape($request->post['session_time']);
                $kiw_request['quota_in']     = $kiw_db->escape($request->post['quota_in']);
                $kiw_request['quota_out']    = $kiw_db->escape($request->post['quota_out']);


                // set the start time and the stop for stop accounting

                $kiw_time_test = strtotime($request->post['event-time']);


                if (date("Y", $kiw_time_test) != "1970") {

                    $kiw_request['start_time'] = date("Y-m-d H:i:s", $kiw_time_test);
                    $kiw_request['stop_time'] = date("Y-m-d H:i:s", $kiw_time_test);
                } else {

                    $kiw_request['start_time'] = date("Y-m-d H:i:s");
                    $kiw_request['stop_time'] = date("Y-m-d H:i:s");
                }


                unset($kiw_time_test);


                $kiw_request['terminate'] = $kiw_db->escape($request->post['terminate']);


                // if the quota more than 4 GB, controller will send data with gigaword

                if ($kiw_request['quota_in_gw'] > 0) $kiw_request['quota_in'] = $kiw_request['quota_in'] + ($kiw_request['quota_in_gw'] * (2 ** 32));
                if ($kiw_request['quota_out_gw'] > 0) $kiw_request['quota_out'] = $kiw_request['quota_out'] + ($kiw_request['quota_out_gw'] * (2 ** 32));


                // if interim update or accounting stop, then get session info. if no then add.

                if ($kiw_request['type'] != "start") {


                    // get the active session data. if not available, insert new row

                    $kiw_session = $kiw_db->query("SELECT * FROM kiwire_active_session WHERE unique_id = '{$kiw_request['unique_id']}' AND tenant_id = '{$kiw_request['tenant_id']}' ORDER BY id DESC LIMIT 1")[0];

                    if ($kiw_request['type'] == "interim-update" && empty($kiw_session)) {


                        check_logger("T:{$kiw_request['tenant_id']} N:{$kiw_request['nasid']} M:{$kiw_request['mac_address']} = Unable to retrieve session for [ {$kiw_request['unique_id']} ]", $kiw_request['tenant_id']);

                        sleep(2);

                        $kiw_session = $kiw_db->query("SELECT * FROM kiwire_active_session WHERE unique_id = '{$kiw_request['unique_id']}' AND tenant_id = '{$kiw_request['tenant_id']}' ORDER BY id DESC LIMIT 1")[0];
                    }


                    // get the user details

                    $kiw_user = $kiw_db->query("SELECT * FROM kiwire_account_auth WHERE username = '{$kiw_request['username']}' AND tenant_id = '{$kiw_request['tenant_id']}' LIMIT 1")[0];


                    // do some checking first to decide if we need to coa

                    $kiw_profile = $kiw_cache->get("PROFILE_DATA:{$kiw_request['tenant_id']}:{$kiw_session['profile']}");

                    if (empty($kiw_profile)) {


                        $kiw_profile = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_profiles WHERE tenant_id = '{$kiw_request['tenant_id']}' AND name = '{$kiw_user['profile_curr']}' LIMIT 1")[0];

                        if (empty($kiw_profile)) $kiw_profile = array("dummy" => true);

                        $kiw_cache->set("PROFILE_DATA:{$kiw_request['tenant_id']}:{$kiw_user['profile_curr']}", $kiw_profile, 1800);
                    }


                    $kiw_profile_temp = json_decode($kiw_profile['attribute'], true);


                    $kiw_current_data['quota_in'] = ($kiw_request['quota_in'] - $kiw_session['quota_in']);
                    $kiw_current_data['quota_out'] = ($kiw_request['quota_out'] - $kiw_session['quota_out']);
                    $kiw_current_data['session_time'] = ($kiw_request['session_time'] - $kiw_session['session_time']);

                    $kiw_user['quota_in']       += $kiw_current_data['quota_in'];
                    $kiw_user['quota_out']      += $kiw_current_data['quota_out'];
                    $kiw_user['session_time']   += $kiw_current_data['session_time'];


                    // check time different between this and previous interim update and need to be positive value

                    if ($kiw_current_data['session_time'] > 0) {

                        // get the average speed between last reporting and current in kbps

                        $kiw_user['avg_speed'] = (((($kiw_current_data['quota_in'] + $kiw_current_data['quota_out']) * 8) / 1024) / $kiw_current_data['session_time']);
                    }


                    // calculate average speed and quota


                    if (!empty($kiw_session['zone'])) {

                        $kiw_cache->incrby("REPORT_TOTAL_QUOTA_IN:{$kiw_time}:{$kiw_request['tenant_id']}:{$kiw_session['zone']}", $kiw_current_data['quota_in']);
                        $kiw_cache->incrby("REPORT_TOTAL_QUOTA_OUT:{$kiw_time}:{$kiw_request['tenant_id']}:{$kiw_session['zone']}", $kiw_current_data['quota_out']);
                        $kiw_cache->incrby("REPORT_TOTAL_TIME:{$kiw_time}:{$kiw_request['tenant_id']}:{$kiw_session['zone']}", ($kiw_current_data['session_time']));
                    }


                    unset($kiw_current_data);


                    $kiw_disconnect = false;

                    $kiw_dm_expired = false;


                    // if interim update, need to check if quota and session time already exhausted

                    if ($kiw_request['type'] == "interim-update") {


                        // check if profile expired, then dm if so


                        if ((time() - strtotime($kiw_user['date_expiry'])) >= 0) {


                            $kiw_disconnect = true;

                            $kiw_dm_expired = true;

                            check_logger("T:{$kiw_request['tenant_id']} N:{$kiw_request['nasid']} M:{$kiw_request['mac_address']} = DM due to Account Expired [ {$kiw_user['session_time']} > {$kiw_profile_temp['control:Max-All-Session']} ]", $kiw_user['tenant_id']);
                        }


                        if ($kiw_profile_temp['control:Max-All-Session'] > 0) {


                            // if (!empty($kiw_user['profile_cus'])){

                            //     $kiw_user['profile_cus'] = json_decode($kiw_user['profile_cus'], true);

                            //     if ($kiw_user['profile_cus']['time'] > 0){

                            //         $kiw_profile_temp['control:Max-All-Session'] += $kiw_user['profile_cus']['time'];

                            //     }

                            // }


                            if ($kiw_user['session_time'] > $kiw_profile_temp['control:Max-All-Session']) {

                                if (strlen($kiw_profile['advance']) > 0) {


                                    check_logger("T:{$kiw_request['tenant_id']} N:{$kiw_request['nasid']} M:{$kiw_request['mac_address']} = Profile changed to [ {$kiw_profile['advance']} ]", $kiw_user['tenant_id']);

                                    coa_user($kiw_db, $kiw_cache, $kiw_request['tenant_id'], $kiw_request['username'], $kiw_profile['advance']);
                                } else {

                                    $kiw_disconnect = true;

                                    check_logger("T:{$kiw_request['tenant_id']} N:{$kiw_request['nasid']} M:{$kiw_request['mac_address']} = DM due to Session Timeout [ {$kiw_user['session_time']} > {$kiw_profile_temp['control:Max-All-Session']} ]", $kiw_user['tenant_id']);
                                }
                            }
                        } elseif ($kiw_profile_temp['control:Access-Period'] > 0) {



                            // if (!empty($kiw_user['profile_cus'])){

                            //     $kiw_user['profile_cus'] = json_decode($kiw_user['profile_cus'], true);

                            //     if ($kiw_user['profile_cus']['time'] > 0){

                            //         $kiw_profile_temp['control:Access-Period'] += $kiw_user['profile_cus']['time'];

                            //     }

                            // }


                            $kiw_test = strtotime($kiw_user['date_activate']);


                            if ($kiw_test > 0) $kiw_test = time() - $kiw_test;
                            else $kiw_test = 0;


                            if ($kiw_test > $kiw_profile_temp['control:Access-Period']) {


                                if (strlen($kiw_profile['advance']) > 0) {


                                    check_logger("T:{$kiw_request['tenant_id']} N:{$kiw_request['nasid']} M:{$kiw_request['mac_address']} = Profile changed to [ {$kiw_profile['advance']} ]", $kiw_user['tenant_id']);

                                    coa_user($kiw_db, $kiw_cache, $kiw_request['tenant_id'], $kiw_request['username'], $kiw_profile['advance']);
                                } else {

                                    $kiw_disconnect = true;

                                    check_logger("T:{$kiw_request['tenant_id']} N:{$kiw_request['nasid']} M:{$kiw_request['mac_address']} = DM due to Access Period [ {$kiw_test} > {$kiw_profile_temp['control:Access-Period']} ]", $kiw_user['tenant_id']);
                                }
                            }

                            unset($kiw_test);
                        }



                        if ($kiw_profile_temp['control:Kiwire-Total-Quota'] > 0) {



                            $kiw_test = ($kiw_user['quota_in'] + $kiw_user['quota_out']);

                            $kiw_quota_limit = ($kiw_profile_temp['control:Kiwire-Total-Quota'] * (1024 * 1024));


                            if (!empty($kiw_user['profile_cus'])) {

                                $kiw_user['profile_cus'] = json_decode($kiw_user['profile_cus'], true);

                                if ($kiw_user['profile_cus']['quota'] > 0) {

                                    $kiw_quota_limit += $kiw_user['profile_cus']['quota'];
                                }
                            }



                            if ($kiw_profile['a_limit'] > 0) $kiw_quota_limit = $kiw_quota_limit * ($kiw_profile['a_limit'] / 100);


                            if ($kiw_test > $kiw_quota_limit) {


                                if (strlen($kiw_profile['advance']) > 0) {


                                    check_logger("T:{$kiw_request['tenant_id']} N:{$kiw_request['nasid']} M:{$kiw_request['mac_address']} = Profile changed to [ {$kiw_profile['advance']} ]", $kiw_user['tenant_id']);

                                    coa_user($kiw_db, $kiw_cache, $kiw_request['tenant_id'], $kiw_request['username'], $kiw_profile['advance']);
                                } else {

                                    $kiw_disconnect = true;

                                    check_logger("T:{$kiw_request['tenant_id']} N:{$kiw_request['nasid']} M:{$kiw_request['mac_address']} = DM due to Quota [ {$kiw_test} > {$kiw_profile_temp['control:Kiwire-Total-Quota']} ]", $kiw_user['tenant_id']);
                                }
                            }

                            unset($kiw_test);
                        }
                    }



                    if (is_array($kiw_session)) {


                        // check if table already existed and create if not

                        $kiw_request['table'] = $kiw_session['session_table'];

                        $kiw_existed = $kiw_cache->get("TABLE_CHECKED:{$kiw_request['table']}");

                        if (empty($kiw_existed)) {

                            $kiw_existed = $kiw_db->query("SELECT COUNT(*) AS kcount FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '{$kiw_request['table']}' AND TABLE_SCHEMA = 'kiwire'");

                            if ($kiw_existed['kcount'] == 0) $kiw_db->query("CREATE TABLE {$kiw_request['table']} LIKE kiwire_session_template");

                            $kiw_cache->set("TABLE_CHECKED:{$kiw_request['table']}", "checked", 3600);
                        }

                        unset($kiw_existed);


                        $kiw_current_session = array();

                        $kiw_current_session['updated_date']     = "NOW()";
                        $kiw_current_session['session_time']     = $kiw_request['session_time'];
                        $kiw_current_session['quota_in']         = $kiw_request['quota_in'];
                        $kiw_current_session['quota_out']        = $kiw_request['quota_out'];
                        $kiw_current_session['avg_speed']        = $kiw_user['avg_speed'];


                        // update ip address if available

                        if (!empty($kiw_request['ip_address']) && substr($kiw_session['ip_address'], 0, 3) == "169") {

                            $kiw_current_session['ip_address'] = $kiw_request['ip_address'];
                        }


                        if (!empty($kiw_request['ipv6_address'])) {

                            $kiw_current_session['ipv6_address'] = $kiw_request['ipv6_address'];
                        }


                        if ($kiw_request['type'] == "stop") {


                            // if status = stop then update these 2 fieds

                            $kiw_current_session['terminate_reason'] = $kiw_request['terminate'];
                            $kiw_current_session['stop_time']        = $kiw_request['stop_time'];


                            // total average speed for archiving

                            $kiw_current_session['avg_speed'] = ((($kiw_request['quota_in'] + $kiw_request['quota_out']) * 8) / 1024) / $kiw_current_session['session_time'];


                            $kiw_db->query("DELETE FROM kiwire_active_session WHERE tenant_id = '{$kiw_session['tenant_id']}' AND unique_id = '{$kiw_session['unique_id']}' LIMIT 1");


                            // update dwell data for reporting

                            $kiw_dwell_time = date("YmdH", strtotime($kiw_session['start_time']));


                            // check for dwell reporting

                            if ($kiw_request['session_time'] < (5 * 60)) $kiw_dwell_type = "5MIN";
                            elseif ($kiw_request['session_time'] < (15 * 60)) $kiw_dwell_type = "15MIN";
                            elseif ($kiw_request['session_time'] < (30 * 60)) $kiw_dwell_type = "30MIN";
                            elseif ($kiw_request['session_time'] < (45 * 60)) $kiw_dwell_type = "45MIN";
                            elseif ($kiw_request['session_time'] < (60 * 60)) $kiw_dwell_type = "1HOUR";
                            elseif ($kiw_request['session_time'] < (2 * (60 * 60))) $kiw_dwell_type = "2HOUR";
                            elseif ($kiw_request['session_time'] < (3 * (60 * 60))) $kiw_dwell_type = "3HOUR";
                            elseif ($kiw_request['session_time'] < (4 * (60 * 60))) $kiw_dwell_type = "4HOUR";
                            elseif ($kiw_request['session_time'] < (5 * (60 * 60))) $kiw_dwell_type = "5HOUR";
                            elseif ($kiw_request['session_time'] < (6 * (60 * 60))) $kiw_dwell_type = "6HOUR";
                            else $kiw_dwell_type = "MOREHOUR";


                            if (empty($kiw_session['zone'])) $kiw_session['zone'] = "nozone";


                            $kiw_cache->incrby("REPORT_DWELL_PROFILE:{$kiw_dwell_time}:{$kiw_session['tenant_id']}:{$kiw_session['zone']}:{$kiw_session['profile']}", $kiw_current_session['session_time']);
                            $kiw_cache->incrby("REPORT_DWELL_ZONE:{$kiw_dwell_time}:{$kiw_session['tenant_id']}:{$kiw_session['zone']}", $kiw_current_session['session_time']);

                            $kiw_cache->incr("REPORT_DWELL_TYPE:{$kiw_dwell_type}:{$kiw_dwell_time}:{$kiw_request['tenant_id']}:{$kiw_session['zone']}");
                            $kiw_cache->incr("REPORT_DISCONNECT:{$kiw_time}:{$kiw_request['tenant_id']}:{$kiw_session['zone']}");
                        } else {

                            $kiw_db->query(sql_update($kiw_db, "kiwire_active_session", $kiw_current_session, "unique_id = '{$kiw_session['unique_id']}' AND tenant_id = '{$kiw_session['tenant_id']}' LIMIT 1"));
                        }


                        // update session table

                        $kiw_db->query(sql_update($kiw_db, $kiw_session['session_table'], $kiw_current_session, "unique_id = '{$kiw_session['unique_id']}' AND tenant_id = '{$kiw_session['tenant_id']}' AND stop_time IS NULL LIMIT 1"));


                        unset($kiw_current_session);
                    } else {



                        // check if table already existed and create if not

                        $kiw_request['table'] = "kiwire_sessions_" . date("Ym", strtotime($kiw_request['start_time']));

                        $kiw_existed = $kiw_cache->get("TABLE_CHECKED:{$kiw_request['table']}");

                        if (empty($kiw_existed)) {

                            $kiw_existed = $kiw_db->query("SELECT COUNT(*) AS kcount FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '{$kiw_request['table']}' AND TABLE_SCHEMA = 'kiwire'");

                            if ($kiw_existed['kcount'] == 0) $kiw_db->query("CREATE TABLE {$kiw_request['table']} LIKE kiwire_session_template");

                            $kiw_cache->set("TABLE_CHECKED:{$kiw_request['table']}", "checked", 3600);
                        }

                        unset($kiw_existed);


                        $kiw_profile = $kiw_db->query("SELECT profile_curr FROM kiwire_account_auth WHERE username = '{$kiw_request['username']}' AND tenant_id = '{$kiw_request['tenant_id']}' LIMIT 1")[0];

                        $kiw_current_session = array();

                        $kiw_current_session['tenant_id']        = $kiw_request['tenant_id'];
                        $kiw_current_session['updated_date']     = "NOW()";
                        $kiw_current_session['session_id']       = $kiw_request['session_id'];
                        $kiw_current_session['unique_id']        = $kiw_request['unique_id'];
                        $kiw_current_session['controller']       = $kiw_request['nasid'];
                        $kiw_current_session['controller_ip']    = $kiw_request['controller_ip'];
                        $kiw_current_session['zone']             = $kiw_request['zone'];
                        $kiw_current_session['username']         = $kiw_request['username'];
                        $kiw_current_session['mac_address']      = $kiw_request['mac_address'];
                        $kiw_current_session['ip_address']       = $kiw_request['ip_address'];
                        $kiw_current_session['ipv6_address']     = $kiw_request['ipv6_address'];
                        $kiw_current_session['profile']          = $kiw_profile['profile_curr'];
                        $kiw_current_session['session_table']    = $kiw_request['table'];
                        $kiw_current_session['start_time']       = $kiw_request['start_time'];
                        $kiw_current_session['session_time']     = $kiw_request['session_time'];
                        $kiw_current_session['quota_in']         = $kiw_request['quota_in'];
                        $kiw_current_session['quota_out']        = $kiw_request['quota_out'];
                        $kiw_current_session['system']           = $kiw_request['system'];
                        $kiw_current_session['class']            = $kiw_request['class'];
                        $kiw_current_session['brand']            = $kiw_request['brand'];
                        $kiw_current_session['model']            = $kiw_request['model'];
                        $kiw_current_session['hostname']         = $kiw_request['hostname'];
                        $kiw_current_session['avg_speed']        = $kiw_user['avg_speed'];


                        // do not filled in stop field if interim update

                        if ($kiw_request['type'] == "stop") {


                            $kiw_current_session['stop_time']        = $kiw_request['stop_time'];
                            $kiw_current_session['terminate_reason'] = $kiw_request['terminate'];


                            $kiw_dwell_time = date("YmdH", strtotime($kiw_current_session['start_time']));


                            // check for dwell reporting

                            if ($kiw_request['session_time'] < (5 * 60)) $kiw_dwell_type = "5MIN";
                            elseif ($kiw_request['session_time'] < (15 * 60)) $kiw_dwell_type = "15MIN";
                            elseif ($kiw_request['session_time'] < (30 * 60)) $kiw_dwell_type = "30MIN";
                            elseif ($kiw_request['session_time'] < (45 * 60)) $kiw_dwell_type = "45MIN";
                            elseif ($kiw_request['session_time'] < (60 * 60)) $kiw_dwell_type = "1HOUR";
                            elseif ($kiw_request['session_time'] < (2 * (60 * 60))) $kiw_dwell_type = "2HOUR";
                            elseif ($kiw_request['session_time'] < (3 * (60 * 60))) $kiw_dwell_type = "3HOUR";
                            elseif ($kiw_request['session_time'] < (4 * (60 * 60))) $kiw_dwell_type = "4HOUR";
                            elseif ($kiw_request['session_time'] < (5 * (60 * 60))) $kiw_dwell_type = "5HOUR";
                            elseif ($kiw_request['session_time'] < (6 * (60 * 60))) $kiw_dwell_type = "6HOUR";
                            else $kiw_dwell_type = "MOREHOUR";


                            if (empty($kiw_request['zone'])) $kiw_request['zone'] = "nozone";


                            $kiw_cache->incrby("REPORT_DWELL_PROFILE:{$kiw_dwell_time}:{$kiw_current_session['tenant_id']}:{$kiw_current_session['profile']}", $kiw_current_session['session_time']);
                            $kiw_cache->incrby("REPORT_DWELL_ZONE:{$kiw_dwell_time}:{$kiw_current_session['tenant_id']}:{$kiw_current_session['zone']}", $kiw_current_session['session_time']);

                            $kiw_cache->incr("REPORT_DWELL_TYPE:{$kiw_dwell_type}:{$kiw_dwell_time}:{$kiw_current_session['tenant_id']}:{$kiw_current_session['zone']}");
                            $kiw_cache->incr("REPORT_DISCONNECT:{$kiw_time}:{$kiw_request['tenant_id']}:{$kiw_request['zone']}");

                            $kiw_cache->incr("REPORT_LOGOUT_STATS:{$kiw_time}:{$kiw_request['tenant_id']}:{$kiw_request['nasid']}");
                        }


                        $kiw_db->query(sql_insert($kiw_db, $kiw_request['table'], $kiw_current_session));


                        // insert into active session table if the session still active

                        if ($kiw_request['type'] == "interim-update") {

                            $kiw_db->query(sql_insert($kiw_db, "kiwire_active_session", $kiw_current_session));
                        }


                        unset($kiw_current_session);
                    }



                    // update user account with latest data quota and session time and last login


                    if ($kiw_request['type'] == "stop") {

                        $kiw_last_login = ", date_last_logout = '{$kiw_request['stop_time']}'";
                    }


                    $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), quota_in = {$kiw_user['quota_in']}, quota_out = {$kiw_user['quota_out']}, session_time = {$kiw_user['session_time']}{$kiw_last_login} WHERE username = '{$kiw_request['username']}' AND tenant_id = '{$kiw_request['tenant_id']}' LIMIT 1");


                    // send coa or dm if above quota

                    if ($kiw_disconnect == true) {

                        // disconnect user if more than limit provided

                        // check if required coa instead of disconnect

                        if (strlen($kiw_profile['advance']) > 0 && $kiw_dm_expired == false) {


                            // do coa if this profile has advance profile

                            coa_user($kiw_db, $kiw_cache, $kiw_session['tenant_id'], $kiw_session['username'], $kiw_profile['advance']);
                        } else {


                            // do disconnect since no more advance profile, or account expired

                            disconnect_user($kiw_db, $kiw_cache, $kiw_session['tenant_id'], $kiw_session['username']);
                        }
                    }


                    // if accounting start, then check if campaign tied to this event

                    go(function () use ($kiw_db, $kiw_request, $kiw_session, $kiw_time) {


                        // create redis connection for campaign

                        $kiw_cache = new Swoole\Coroutine\Redis();

                        $kiw_cache->connect(SYNC_REDIS_HOST, SYNC_REDIS_PORT, true);


                        // no need for start, as we have done during login

                        if ($kiw_request['type'] == "interim-update") $kiw_campaign_type = "c_trigger = 'dwell' AND";
                        elseif ($kiw_request['type'] == "stop") $kiw_campaign_type = "c_trigger = 'disconnect' AND";
                        else {

                            return;
                        }


                        // save in cache for 30 mins

                        $kiw_campaign = $kiw_cache->get("CAMPAIGN_OFFLINE_AVAILABLE:{$kiw_request['tenant_id']}:{$kiw_request['type']}");

                        if (empty($kiw_campaign)) {


                            $kiw_campaign = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_campaign_manager WHERE {$kiw_campaign_type} status = 'y' AND tenant_id = '{$kiw_request['tenant_id']}'");

                            if (empty($kiw_campaign)) $kiw_campaign = array("dummy" => true);

                            $kiw_cache->set("CAMPAIGN_OFFLINE_AVAILABLE:{$kiw_request['tenant_id']}:{$kiw_request['type']}", $kiw_campaign, 1800);
                        }



                        if ($kiw_campaign['dummy'] != true) {


                            $kiw_campaign_time = (new DateTime("now", new DateTimeZone("UTC")))->setTimezone(new DateTimeZone("Asia/Kuala_Lumpur"))->format("H");

                            $kiw_user = $kiw_db->query("SELECT tenant_id,username,fullname,email_address,phone_number,campaign_history FROM kiwire_account_auth WHERE username = '{$kiw_request['username']}' AND tenant_id = '{$kiw_request['tenant_id']}' LIMIT 1")[0];


                            $kiw_user_campaign = json_decode($kiw_user['campaign'], true);


                            foreach ($kiw_campaign as $kiw_campaign_detail) {


                                // check for time to execute campaign if available

                                if ($kiw_campaign_detail['c_interval'] == "always" || (($kiw_campaign_detail['c_interval_time_start'] >= $kiw_campaign_time) && ($kiw_campaign_detail['c_interval_time_stop'] <= $kiw_campaign_time))) {


                                    // check for zone if available

                                    if ($kiw_campaign_detail['target'] == "all" || ($kiw_campaign_detail['zone'] == $kiw_session['zone'])) {


                                        if (!empty($kiw_user)) {


                                            // if dwell campaign, make sure last time campaign sent longer than dwell time

                                            if ($kiw_campaign_detail['c_trigger'] == "dwell") {

                                                if ((time() - $kiw_user_campaign[$kiw_campaign_detail['id']]) < ($kiw_campaign_detail['c_trigger_value'] * 60)) {

                                                    continue;
                                                }
                                            }


                                            // update the last time this campaign sent to user

                                            $kiw_user_campaign[$kiw_campaign_detail['id']] = time();


                                            // record if campaign triggered

                                            $kiw_cache->incr("REPORT_OFFLINE_CAMPAIGN:{$kiw_time}:{$kiw_request['tenant_id']}:" . base64_encode($kiw_campaign_detail['name']));


                                            // action to the campaign

                                            if ($kiw_campaign_detail['action_method'] == "email") {


                                                $kiw_template = $kiw_cache->get("CAMPAIGN_OFFLINE_TEMPLATE:{$kiw_campaign_detail['tenant_id']}:" . md5($kiw_campaign_detail['action_value']));

                                                if (empty($kiw_template)) {


                                                    $kiw_template = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_html_template WHERE tenant_id = '{$kiw_campaign_detail['tenant_id']}' AND type = 'email' AND name = '{$kiw_campaign_detail['action_value']}' LIMIT 1")[0];

                                                    if (empty($kiw_template)) $kiw_template = array("dummy" => true);

                                                    $kiw_cache->set("CAMPAIGN_OFFLINE_TEMPLATE:{$kiw_campaign_detail['tenant_id']}:" . md5($kiw_campaign_detail['action_value']), $kiw_template, 1800);
                                                }


                                                if (!empty($kiw_template['content']) && filter_var($kiw_user['email_address'], FILTER_VALIDATE_EMAIL)) {


                                                    $kiw_email['action']        = "send_email";
                                                    $kiw_email['tenant_id']     = $kiw_campaign_detail['tenant_id'];
                                                    $kiw_email['email_address'] = $kiw_user['email_address'];
                                                    $kiw_email['subject']       = $kiw_template['subject'];
                                                    $kiw_email['content']       = htmlentities($kiw_template['content']);
                                                    $kiw_email['name']          = $kiw_user['fullname'];

                                                    unset($kiw_template);


                                                    $kiw_temp = curl_init();

                                                    curl_setopt($kiw_temp, CURLOPT_URL, "http://127.0.0.1:9956");
                                                    curl_setopt($kiw_temp, CURLOPT_POST, true);
                                                    curl_setopt($kiw_temp, CURLOPT_POSTFIELDS, http_build_query($kiw_email));
                                                    curl_setopt($kiw_temp, CURLOPT_RETURNTRANSFER, true);
                                                    curl_setopt($kiw_temp, CURLOPT_TIMEOUT, 5);
                                                    curl_setopt($kiw_temp, CURLOPT_CONNECTTIMEOUT, 5);

                                                    unset($kiw_email);

                                                    curl_exec($kiw_temp);
                                                    curl_close($kiw_temp);

                                                    unset($kiw_temp);
                                                } else {

                                                    unset($kiw_template);
                                                }
                                            } elseif ($kiw_campaign_detail['action_method'] == "sms") {



                                                $kiw_template = $kiw_cache->get("CAMPAIGN_OFFLINE_TEMPLATE:{$kiw_campaign_detail['tenant_id']}:" . md5($kiw_campaign_detail['action_value']));

                                                if (empty($kiw_template)) {


                                                    $kiw_template = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_html_template WHERE tenant_id = '{$kiw_campaign_detail['tenant_id']}' AND type = 'sms' AND name = '{$kiw_campaign_detail['action_value']}' LIMIT 1")[0];

                                                    if (empty($kiw_template)) $kiw_template = array("dummy" => true);

                                                    $kiw_cache->set("CAMPAIGN_OFFLINE_TEMPLATE:{$kiw_campaign_detail['tenant_id']}:" . md5($kiw_campaign_detail['action_value']), $kiw_template, 1800);
                                                }


                                                if (!empty($kiw_template['content']) && !empty($kiw_user['phone_number'])) {


                                                    $kiw_sms['action']       = "send_sms";
                                                    $kiw_sms['tenant_id']    = $kiw_campaign_detail['tenant_id'];
                                                    $kiw_sms['phone_number'] = $kiw_user['phone_number'];
                                                    $kiw_sms['content']      = strip_tags($kiw_template['content']);

                                                    unset($kiw_template);


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
                                                } else {

                                                    unset($kiw_template);
                                                }
                                            } elseif ($kiw_campaign_detail['action_method'] == "api") {


                                                // send api to user

                                                $kiw_campaign_detail['action_value'] = str_replace("{{tenant_id}}", $kiw_user['tenant_id'], $kiw_campaign_detail['action_value']);
                                                $kiw_campaign_detail['action_value'] = str_replace("{{username}}", $kiw_user['username'], $kiw_campaign_detail['action_value']);
                                                $kiw_campaign_detail['action_value'] = str_replace("{{fullname}}", $kiw_user['fullname'], $kiw_campaign_detail['action_value']);

                                                $kiw_temp = curl_init();

                                                curl_setopt($kiw_temp, CURLOPT_URL, $kiw_campaign_detail['action_value']);
                                                curl_setopt($kiw_temp, CURLOPT_POST, false);
                                                curl_setopt($kiw_temp, CURLOPT_RETURNTRANSFER, true);
                                                curl_setopt($kiw_temp, CURLOPT_TIMEOUT, 5);
                                                curl_setopt($kiw_temp, CURLOPT_CONNECTTIMEOUT, 5);

                                                unset($kiw_content);

                                                curl_exec($kiw_temp);
                                                curl_close($kiw_temp);

                                                unset($kiw_temp);
                                            } elseif ($kiw_campaign_detail['action_method'] == "webpush") {


                                                // send api to user

                                                $kiw_campaign_detail['action_value'] = str_replace("{{tenant_id}}", $kiw_user['tenant_id'], $kiw_campaign_detail['action_value']);
                                                $kiw_campaign_detail['action_value'] = str_replace("{{username}}", $kiw_user['username'], $kiw_campaign_detail['action_value']);
                                                $kiw_campaign_detail['action_value'] = str_replace("{{fullname}}", $kiw_user['fullname'], $kiw_campaign_detail['action_value']);

                                                $kiw_temp = curl_init();

                                                curl_setopt($kiw_temp, CURLOPT_URL, $kiw_campaign_detail['action_value']);
                                                curl_setopt($kiw_temp, CURLOPT_POST, false);
                                                curl_setopt($kiw_temp, CURLOPT_RETURNTRANSFER, true);
                                                curl_setopt($kiw_temp, CURLOPT_TIMEOUT, 5);
                                                curl_setopt($kiw_temp, CURLOPT_CONNECTTIMEOUT, 5);

                                                unset($kiw_content);

                                                curl_exec($kiw_temp);
                                                curl_close($kiw_temp);

                                                unset($kiw_temp);
                                            }
                                        }
                                    }
                                }
                            }


                            $kiw_cache->close();


                            // update campaign - user data

                            $kiw_user_campaign = json_encode($kiw_user_campaign);

                            $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), campaign_history = '{$kiw_user_campaign}' WHERE username = '{$kiw_request['username']}' AND tenant_id = '{$kiw_request['tenant_id']}' LIMIT 1");
                        }
                    });
                } else {



                    $kiw_temp = str_replace(array(":", "-"), "", $kiw_request['mac_address']);

                    $kiw_temp = $kiw_cache->get("LOGIN_PROFILE:{$kiw_request['tenant_id']}:{$kiw_temp}");


                    if (empty($kiw_temp)) {

                        $kiw_profile = $kiw_db->query("SELECT profile_curr FROM kiwire_account_auth WHERE username = '{$kiw_request['username']}' AND tenant_id = '{$kiw_request['tenant_id']}' LIMIT 1")[0];
                    } else $kiw_profile['profile_curr'] = $kiw_temp;


                    unset($kiw_temp);


                    $kiw_request['table'] = "kiwire_sessions_" . date("Ym", strtotime($kiw_request['start_time']));


                    // check if table existed or not. if no then create new table.

                    $kiw_existed = $kiw_cache->get("TABLE_CHECKED:{$kiw_request['table']}");

                    if (empty($kiw_existed)) {

                        $kiw_existed = $kiw_db->query("SELECT COUNT(*) AS kcount FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '{$kiw_request['table']}' AND TABLE_SCHEMA = 'kiwire'");

                        if ($kiw_existed['kcount'] == 0) $kiw_db->query("CREATE TABLE {$kiw_request['table']} LIKE kiwire_session_template");

                        $kiw_cache->set("TABLE_CHECKED:{$kiw_request['table']}", "checked", 3600);
                    }


                    // CHECK IF DUPLICATE START 
                    $kiw_check_dup = $kiw_db->query("SELECT id FROM kiwire_active_session WHERE username = '{$kiw_request['username']}' AND tenant_id = '{$kiw_request['tenant_id']}' AND unique_id = '{$kiw_request['unique_id']}' LIMIT 1")[0];

                    if (!empty($kiw_check_dup)) {

                        check_logger("T:{$kiw_request['tenant_id']} N:{$kiw_request['nasid']} M:{$kiw_request['mac_address']} = Accounting Received and Check Duplicate. Ignore [ " . ucfirst($kiw_request['type']) . " ]", $kiw_request['tenant_id']);

                        $kiw_cache->set("REQUEST_DUPLICATE:{$kiw_dupe_id}", json_encode(array("reply:Reply-Message" => "Accounting Processed")), 10);

                        $response->end(json_encode(array("reply:Reply-Message" => "Duplicate packet [query].")));

                        return;
                    }
                    unset($kiw_check_dup);
                    // CHECK IF DUPLICATE END 


                    unset($kiw_existed);


                    $kiw_current_session = array();

                    $kiw_current_session['tenant_id']        = $kiw_request['tenant_id'];
                    $kiw_current_session['updated_date']     = "NOW()";
                    $kiw_current_session['session_id']       = $kiw_request['session_id'];
                    $kiw_current_session['unique_id']        = $kiw_request['unique_id'];

                    $kiw_current_session['controller']       = $kiw_request['nasid'];
                    $kiw_current_session['controller_ip']    = $kiw_request['controller_ip'];
                    $kiw_current_session['zone']             = $kiw_request['zone'];
                    $kiw_current_session['username']         = $kiw_request['username'];

                    $kiw_current_session['mac_address']      = $kiw_request['mac_address'];
                    $kiw_current_session['ip_address']       = $kiw_request['ip_address'];
                    $kiw_current_session['ipv6_address']     = $kiw_request['ipv6_address'];
                    $kiw_current_session['profile']          = $kiw_profile['profile_curr'];
                    $kiw_current_session['session_table']    = $kiw_request['table'];

                    $kiw_current_session['start_time']       = $kiw_request['start_time'];
                    $kiw_current_session['stop_time']        = "NULL";
                    $kiw_current_session['session_time']     = 0;
                    $kiw_current_session['quota_in']         = 0;

                    $kiw_current_session['quota_out']        = 0;
                    $kiw_current_session['avg_speed']        = 0;
                    $kiw_current_session['terminate_reason'] = "NULL";

                    $kiw_current_session['system']           = $kiw_request['system'];
                    $kiw_current_session['class']            = $kiw_request['class'];
                    $kiw_current_session['brand']            = $kiw_request['brand'];
                    $kiw_current_session['model']            = $kiw_request['model'];
                    $kiw_current_session['hostname']         = $kiw_request['hostname'];


                    $kiw_db->query(sql_insert($kiw_db, $kiw_request['table'], $kiw_current_session));

                    $kiw_db->query(sql_insert($kiw_db, "kiwire_active_session", $kiw_current_session));


                    unset($kiw_current_session);
                }

                check_logger("T:{$kiw_request['tenant_id']} U:{$kiw_request['username']} N:{$kiw_request['nasid']} M:{$kiw_request['mac_address']} = Accounting Received and Responded [ " . ucfirst($kiw_request['type']) . " ]", $kiw_request['tenant_id']);

                $kiw_cache->set("REQUEST_DUPLICATE:{$kiw_dupe_id}", json_encode(array("reply:Reply-Message" => "Accounting Processed")), 10);

                // $response->status(204);
                $response->end(json_encode(array("reply:Reply-Message" => "Accounting Processed")));

                return;
            } else {


                $kiw_result = array("reply:Reply-Message" => "Unknown / unsupported function.");

                check_logger("T:{$kiw_request['tenant_id']} N:{$kiw_request['nasid']} M:{$kiw_request['mac_address']} = {$kiw_result['reply:Reply-Message']}", $kiw_request['tenant_id']);

                $kiw_cache->set("REQUEST_DUPLICATE:{$kiw_dupe_id}", $kiw_result, 10);

                $response->end(json_encode($kiw_result));

                return;
            }
        } else {


            $kiw_result = array("reply:Reply-Message" => "NAS not configure");

            check_logger("T:{$kiw_request['tenant_id']} N:{$kiw_request['nasid']} M:{$kiw_request['mac_address']} = {$kiw_result['reply:Reply-Message']}", "general");

            $kiw_cache->set("REQUEST_DUPLICATE:{$kiw_dupe_id}", $kiw_result, 10);

            $response->end(json_encode($kiw_result));

            return;
        }
    } else {


        if ($kiw_request['action'] == "authorize") {

            $response->end(json_encode($kiw_dupe_check));
        } else {

            $response->end(json_encode(array("reply:Reply-Message" => "Duplicate packet [cache].")));

            return;
            // $response->status(204);

        }
    }
});


// start the server

$kiwire_server->start();
