<?php


error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);


$kiw_lock_name = "kiwire-scheduler.lock";

require_once "scheduler_lock.php";


require_once dirname(__FILE__, 3) . "/server/admin/includes/include_config.php";
require_once dirname(__FILE__, 3) . "/server/admin/includes/include_general.php";
require_once dirname(__FILE__, 3) . "/server/user/includes/include_radius.php";

require_once dirname(__FILE__, 3) . "/server/libs/class.sql.helper.php";


ini_set("max_execution_time", 300);


go(function () {


    $kiw_this_file = dirname(__FILE__);

    $kiw_log_path = dirname($kiw_this_file, 2) . "/server/custom";


    // connection to mariadb server

    $kiw_db = new Swoole\Coroutine\MySQL();

    $kiw_db->connect(array('host' => SYNC_DB1_HOST, 'user' => SYNC_DB1_USER, 'password' => SYNC_DB1_PASSWORD, 'database' => SYNC_DB1_DATABASE, 'port' => SYNC_DB1_PORT));


    // connection to redis server

    $kiw_cache = new Swoole\Coroutine\Redis();

    $kiw_cache->connect(SYNC_REDIS_HOST, SYNC_REDIS_PORT, true);

    $kiw_time['start'] = date('Y-m-d H:i:s');


    // get all tenant for this system

    $kiw_clouds_db = $kiw_db->query("SELECT tenant_id,timezone FROM kiwire_clouds LIMIT 1000");


    $kiw_system = @file_get_contents("{$kiw_log_path}/system_setting.json");
    $kiw_system = json_decode($kiw_system, true);


    $kiw_data['time'] = date("Y-m-d H:i:s");
    $kiw_data['time_seconds'] = time();
    $kiw_data['path'] = dirname(__FILE__, 3);


    $kiw_data['end_month'] = date("d", strtotime("+1 Minute")) == "01";
    $kiw_data['end_week'] = date("D", strtotime("+1 Minute")) == "Mon";
    $kiw_data['end_year'] = date("md", strtotime("+1 Minute")) == "0101";


    $kiw_data['hour'] =  date("H", strtotime($kiw_data['time']));
    $kiw_data['minute'] = date("i", strtotime($kiw_data['time']));


    // delete temporary file for protection

    system("find {$kiw_data['path']}/server/temp -mmin +10 -type f -delete");
    system("touch {$kiw_data['path']}/server/temp/index.php");
    system("chmod -R 755 {$kiw_data['path']}/server/custom/");
    system("chmod -R 755 {$kiw_data['path']}/logs/*");
    system("chmod -R 755 {$kiw_data['path']}/server/temp/");

    system("chown nginx:nginx -R {$kiw_data['path']}/server/custom/*");
    system("chown nginx:nginx -R {$kiw_data['path']}/logs/*");
    system("chown nginx:nginx -R {$kiw_data['path']}/server/temp*");
    

    // delete older log file

    if (file_exists("{$kiw_data['path']}/logs/")) {


        foreach ($kiw_clouds_db as $kiw_cloud) {

            if (file_exists("{$kiw_data['path']}/logs/{$kiw_cloud['tenant_id']}/")) {

                //convert days to minutes 
                $min = $kiw_system['keep_log_data'] * 24 * 60;
                system("find {$kiw_data['path']}/logs/{$kiw_cloud['tenant_id']}/ -mmin +{$min} -type f -delete");


            } else {

                mkdir("{$kiw_data['path']}/logs/{$kiw_cloud['tenant_id']}/", 0755, true);
            }
        }
    }


    // flush sql cache every 6 am local time

    if (empty($kiw_system['timezone'])) $kiw_system['timezone'] = "Asia/Kuala_Lumpur";

    if ((new DateTime("now", new DateTimeZone("UTC")))->setTimezone(new DateTimeZone($kiw_system['timezone']))->format("H") == "06") {

        $kiw_db->query("FLUSH QUERY CACHE");
    }


    // check for pms script to be run for each tenant if available.

    foreach ($kiw_clouds_db as $kiw_cloud) {


        $kiw_pms_script_path = dirname(__FILE__, 3) . "/server/custom/{$kiw_cloud['tenant_id']}/pms/";

        $kiw_pms_scripts = scandir($kiw_pms_script_path);


        if (count($kiw_pms_scripts) > 2) {


            foreach ($kiw_pms_scripts as $kiw_pms_script) {

                if ($kiw_pms_script == "idb") {

                    system("nohup /usr/bin/php {$kiw_pms_script_path}idb/post.php 1>/dev/null 2>&1 &");
                    system("nohup /usr/bin/php {$kiw_pms_script_path}idb/synchronize.php 1>/dev/null 2>&1 &");
                }
            }
        }
    }


    // delete account that need to be deleted

    // $kiw_db->query("DELETE FROM kiwire_account_auth WHERE date_remove < NOW()");


    // get all active session right now

    $kiw_connected = [];

    $kiw_active_session = [];


    // populate profile data to be check against active session

    $kiw_profiles = [];

    $kiw_temp = $kiw_db->query("SELECT tenant_id,name,type FROM kiwire_profiles");

    foreach ($kiw_temp as $kiw_profile) {

        $kiw_profiles[$kiw_profile['tenant_id']][$kiw_profile['name']] = ($kiw_profile['type'] == "free") ? $kiw_system['freeprofile_interim'] : $kiw_system['paidprofile_interim'];
    }


    unset($kiw_profile);

    unset($kiw_temp);


    $kiw_concurrents = [];


    $kiw_temp = $kiw_db->query("SELECT tenant_id,username,mac_address,updated_date,profile,session_table,unique_id,zone FROM kiwire_active_session");


    foreach ($kiw_temp as $kiw_active) {


        $kiw_concurrents[$kiw_active['tenant_id']][$kiw_active['zone']] += 1;


        if (($kiw_data['time_seconds'] - strtotime($kiw_active['updated_date'])) <= ($kiw_profiles[$kiw_active['tenant_id']][$kiw_active['profile']] + 120)) {


            $kiw_active_session[$kiw_active['tenant_id']]['name'][] = $kiw_active['username'];

            $kiw_active_session[$kiw_active['tenant_id']]['mac'][] = $kiw_active['mac_address'];

            $kiw_connected[$kiw_active['tenant_id']][$kiw_active['zone']] += 1;
        } else {


            // push data to update everything and delete active session

            $kiw_stale = $kiw_db->query("SELECT * FROM kiwire_active_session WHERE unique_id = '{$kiw_active['unique_id']}' AND tenant_id = '{$kiw_active['tenant_id']}' LIMIT 1")[0];

            $kiw_user['action']         = "accounting";
            $kiw_user['nasid']          = $kiw_stale['controller'];
            $kiw_user['username']       = $kiw_stale['username'];
            $kiw_user['macaddress']     = $kiw_stale['mac_address'];
            $kiw_user['unique_id']      = $kiw_stale['unique_id'];
            $kiw_user['ipaddress']      = $kiw_stale['ip_address'];
            $kiw_user['controller_ip']  = $kiw_stale['controller_ip'];
            $kiw_user['session_id']     = $kiw_stale['session_id'];
            $kiw_user['session_time']   = time() - strtotime($kiw_stale['start_time']);
            $kiw_user['quota_in']       = $kiw_stale['quota_in'];
            $kiw_user['quota_out']      = $kiw_stale['quota_out'];
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

            echo curl_exec($kiw_auth);
            curl_close($kiw_auth);

            unset($kiw_auth);
            unset($kiw_user);
            unset($kiw_stale);
        }
    }

    unset($kiw_temp);

    unset($kiw_profiles);


    // update concurrent data to redis

    $kiw_concurrent_date = date("YmdH");

    foreach ($kiw_concurrents as $kiw_tenant => $kiw_values) {


        foreach ($kiw_values as $kiw_zone => $kiw_value) {


            $kiw_current_value = $kiw_cache->get("REPORT_CONCURRENT:{$kiw_concurrent_date}:{$kiw_tenant}:{$kiw_zone}");


            // only record max simultaneous session number

            if ($kiw_value > $kiw_current_value) {

                $kiw_cache->set("REPORT_CONCURRENT:{$kiw_concurrent_date}:{$kiw_tenant}:{$kiw_zone}", $kiw_value);
            }
        }
    }


    unset($kiw_key);
    unset($kiw_value);

    unset($kiw_concurrents);
    unset($kiw_concurrent_date);


    // check for temporary access and terminate if available

    $kiw_policies = $kiw_db->query("SELECT tenant_id,delete_unverified FROM kiwire_policies");

    foreach ($kiw_policies as $kiw_tenant) {

        $kiw_unverified[$kiw_tenant['tenant_id']] = $kiw_tenant['delete_unverified'];
    }

    unset($kiw_policy);

    unset($kiw_policies);


    $kiw_temp = $kiw_db->query("SELECT * FROM kiwire_temporary_access");

    foreach ($kiw_temp as $kiw_expired) {


        $kiw_profile = $kiw_cache->get("PROFILE_DATA:{$kiw_expired['tenant_id']}:Temp_Access");

        if (empty($kiw_profile)) {


            $kiw_profile = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_profiles WHERE tenant_id = '{$kiw_expired['tenant_id']}' AND name = 'Temp_Access' LIMIT 1")[0];

            if (empty($kiw_profile)) $kiw_profile = array("dummy" => true);

            $kiw_cache->set("PROFILE_DATA:{$kiw_expired['tenant_id']}:Temp_Access", $kiw_profile, 1800);
        }


        if (empty($kiw_profile['grace']) || $kiw_profile['grace'] == 0) {


            $kiw_profile['attribute'] = json_decode($kiw_profile['attribute'], true);

            $kiw_profile['grace'] = $kiw_profile['attribute']['control:Max-All-Session'];
        }


        if (($kiw_data['time_seconds'] - strtotime($kiw_expired['updated_date'])) >= ($kiw_profile['grace'] * 60)) {


            // update the status to expired and delete


            if (is_array($kiw_active_session[$kiw_expired['tenant_id']]['name'])) {


                if (in_array($kiw_expired['username'], $kiw_active_session[$kiw_expired['tenant_id']]['name'])) {


                    disconnect_user($kiw_db, $kiw_cache, $kiw_expired['tenant_id'], $kiw_expired['username']);
                }
            }


            if ($kiw_unverified[$kiw_expired['tenant_id']] == "y") {


                $kiw_db->query("DELETE FROM kiwire_account_auth WHERE username = '{$kiw_expired['username']}' AND tenant_id = '{$kiw_expired['tenant_id']}' LIMIT 1");

                $kiw_db->query("DELETE FROM kiwire_device_history WHERE last_account = '{$kiw_expired['username']}' AND tenant_id = '{$kiw_expired['tenant_id']}'");
            } else {

                $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), status = 'suspend' WHERE username = '{$kiw_expired['username']}' AND tenant_id = '{$kiw_expired['tenant_id']}' LIMIT 1");
            }


            $kiw_db->query("DELETE FROM kiwire_temporary_access WHERE username = '{$kiw_expired['username']}' AND tenant_id = '{$kiw_expired['tenant_id']}'");
        }


        unset($kiw_profile);
    }


    unset($kiw_unverified);

    unset($kiw_expired);

    unset($kiw_temp);


    // check for expired account and mark expired after midnight


    foreach ($kiw_clouds_db as $kiw_temp) {


        if (empty($kiw_temp['timezone'])) $kiw_temp['timezone'] = "Asia/Kuala_Lumpur";


        try {


            $kiw_expiry = new DateTime("now", new DateTimeZone("UTC"));

            $kiw_expiry->setTimezone(new DateTimeZone($kiw_temp['timezone']));

            $kiw_expiry = $kiw_expiry->format("Y-m-d 23:59:59");


            $kiw_expiry = new DateTime($kiw_expiry, new DateTimeZone($kiw_temp['timezone']));

            $kiw_expiry->setTimezone(new DateTimeZone("UTC"));

            $kiw_expiry = $kiw_expiry->format("Y-m-d H:i:s");
        } catch (Exception $e) {


            echo $e->getMessage() . "\n";
            echo "Possible invalid timezone setting: {$kiw_temp['tenant_id']} [ {$kiw_temp['timezone']} ]\n";

            $kiw_expiry = date("Y-m-d H:i:s");
        }


        $kiw_expired_account = $kiw_db->query("SELECT tenant_id,username FROM kiwire_account_auth WHERE date_expiry < '{$kiw_expiry}' AND status <> 'expired' AND tenant_id = '{$kiw_temp["tenant_id"]}'");

        $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), status = 'expired' WHERE date_expiry < '{$kiw_expiry}' AND status <> 'expired' AND tenant_id = '{$kiw_temp["tenant_id"]}'");


        if (!empty($kiw_active_session)) {


            foreach ($kiw_expired_account as $kiw_expired) {

                if (in_array($kiw_expired['username'], $kiw_active_session[$kiw_expired['tenant_id']]['name'])) {

                    // get the device details, user device and controller

                    disconnect_user($kiw_db, $kiw_cache, $kiw_expired['tenant_id'], $kiw_expired['username']);
                }
            }

            unset($kiw_expired);
        }


        unset($kiw_expired_account);
    }


    // generate offline campaign (last visit) if required


    $kiw_reset_status = @file_get_contents("{$kiw_log_path}/reset_status.json");

    $kiw_reset_status = json_decode($kiw_reset_status, true);


    // reset account 30 minutes

    if ($kiw_data['minute'] == "30" || $kiw_data['minute'] == "00") {

        foreach ($kiw_clouds_db as $kiw_cloud) {

            $kiw_profiles = $kiw_db->query("SELECT * FROM kiwire_auto_reset WHERE exec_when = 't' AND tenant_id = '{$kiw_cloud['tenant_id']}'");

            foreach ($kiw_profiles as $kiw_profile) {

                $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), session_time = 0, quota_in = 0, quota_out = 0, date_activate = NULL, profile_curr = '{$kiw_profile['profile']}' WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND profile_subs = '{$kiw_profile['profile']}'");
            }

            unset($kiw_profile);
            unset($kiw_profiles);
        }

        $kiw_reset_status['minute'] = time();
    }


    // reset account 60 minute

    if ($kiw_data['minute'] == "00") {

        foreach ($kiw_clouds_db as $kiw_cloud) {


            $kiw_profiles = $kiw_db->query("SELECT * FROM kiwire_auto_reset WHERE exec_when = 'h' AND tenant_id = '{$kiw_cloud['tenant_id']}'");


            foreach ($kiw_profiles as $kiw_profile) {


                $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), session_time = 0, quota_in = 0, quota_out = 0, date_activate = NULL, profile_curr = '{$kiw_profile['profile']}' WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND profile_subs = '{$kiw_profile['profile']}'");
                
            }

            unset($kiw_profile);
            unset($kiw_profiles);
        }

        $kiw_reset_status['hourly'] = time();
    }


    // reset account daily

    if ("{$kiw_data['hour']}:{$kiw_data['minute']}" == $kiw_system['reset_daily']) {


        if ((time() - $kiw_reset_status['daily']) > 43200) {

            foreach ($kiw_clouds_db as $kiw_cloud) {


                $kiw_profiles = $kiw_db->query("SELECT * FROM kiwire_auto_reset WHERE exec_when = 'd' AND tenant_id = '{$kiw_cloud['tenant_id']}'");


                $kiw_profiles['attribute'] = json_decode($kiw_profiles['attribute'], true);


                foreach ($kiw_profiles as $kiw_profile) {


                    $kiw_accounts = $kiw_db->query("SELECT username, profile_cus, quota_in, quota_out, session_time FROM kiwire_account_auth WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND profile_subs = '{$kiw_profile['profile']}'");

                    $kiw_cf = $kiw_db->query("SELECT * from kiwire_clouds WHERE tenant_id = '{$kiw_cloud['tenant_id']}'");


                    foreach ($kiw_accounts as $kiw_account) {


                        if (!empty($kiw_account['profile_cus']) && $kiw_cf['carry_forward_topup'] == 'y') {


                            $kiw_account['profile_cus'] = json_decode($kiw_account['profile_cus'], true);


                            $kiw_usage['quota'] = ($kiw_account['quota_in'] + $kiw_account['quota_out']);



                            if ($kiw_account['profile_cus']['time'] > 0) {


                                if ($kiw_account['session_time'] < ($kiw_profiles['attribute']['control:Max-All-Session'] + $kiw_profiles['attribute']['control:Access-Period'])) {


                                    $kiw_temp['time'] = (($kiw_profiles['attribute']['control:Max-All-Session'] + $kiw_profiles['attribute']['control:Access-Period']) + $kiw_account['profile_cus']['time']) - $kiw_account['session_time'];


                                } else {

                                    $kiw_temp['time'] = $kiw_account['profile_cus']['time'];


                                }

                                if ($kiw_temp['time'] < 0) $kiw_temp['time'] = 0;


                            }


                            if ($kiw_account['profile_cus']['quota'] > 0) {


                                if ($kiw_usage['quota'] < $kiw_profiles['attribute']['control:Kiwire-Total-Quota']) {


                                    $kiw_temp['quota'] = ($kiw_profiles['attribute']['control:Kiwire-Total-Quota'] + $kiw_account['profile_cus']['quota']) - $kiw_usage['quota'];


                                } else {

                                    $kiw_temp['quota'] = $kiw_account['profile_cus']['quota'];


                                }


                                if ($kiw_temp['quota'] < 0) $kiw_temp['quota'] = 0;


                            }


                            unset($kiw_usage);


                            $kiw_temp = json_encode($kiw_temp);

                            $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), updated_date = NOW(), session_time = 0, quota_in = 0, quota_out = 0, date_activate = NULL, profile_curr = profile_subs, profile_cus = '{$kiw_temp}' WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND profile_subs = '{$kiw_profile['profile']}'");


                        } else {

                            $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), updated_date = NOW(), session_time = 0, quota_in = 0, quota_out = 0, date_activate = NULL, profile_curr = profile_subs, profile_cus = '' WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND profile_subs = '{$kiw_profile['profile']}'");


                        }

                    }

                }


                unset($kiw_profile);
                unset($kiw_profiles);
            }

            $kiw_reset_status['daily'] = time();

        }


    }


    // reset account weekly

    if ($kiw_data['end_week'] == true && ("{$kiw_data['hour']}:{$kiw_data['minute']}" == $kiw_system['reset_weekly'])) {


        if ((time() - $kiw_reset_status['weekly']) > 259200) {


            foreach ($kiw_clouds_db as $kiw_cloud) {


                $kiw_profiles = $kiw_db->query("SELECT * FROM kiwire_auto_reset WHERE exec_when = 'w' AND tenant_id = '{$kiw_cloud['tenant_id']}'");

                $kiw_profiles['attribute'] = json_decode($kiw_profiles['attribute'], true);


                foreach ($kiw_profiles as $kiw_profile) {


                    $kiw_accounts = $kiw_db->query("SELECT username, profile_cus, quota_in, quota_out, session_time FROM kiwire_account_auth WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND profile_subs = '{$kiw_profile['profile']}'");

                    $kiw_cf = $kiw_db->query("SELECT * from kiwire_clouds WHERE tenant_id = '{$kiw_cloud['tenant_id']}'");


                    foreach ($kiw_accounts as $kiw_account) {


                        if (!empty($kiw_account['profile_cus']) && $kiw_cf['carry_forward_topup'] == 'y') {


                            $kiw_account['profile_cus'] = json_decode($kiw_account['profile_cus'], true);


                            $kiw_usage['quota'] = ($kiw_account['quota_in'] + $kiw_account['quota_out']);



                            if ($kiw_account['profile_cus']['time'] > 0) {


                                if ($kiw_account['session_time'] < ($kiw_profiles['attribute']['control:Max-All-Session'] + $kiw_profiles['attribute']['control:Access-Period'])) {


                                    $kiw_temp['time'] = (($kiw_profiles['attribute']['control:Max-All-Session'] + $kiw_profiles['attribute']['control:Access-Period']) + $kiw_account['profile_cus']['time']) - $kiw_account['session_time'];


                                } else {

                                    $kiw_temp['time'] = $kiw_account['profile_cus']['time'];


                                }


                                if ($kiw_temp['time'] < 0) $kiw_temp['time'] = 0;


                            }


                            if ($kiw_account['profile_cus']['quota'] > 0) {


                                if ($kiw_usage['quota'] < $kiw_profiles['attribute']['control:Kiwire-Total-Quota']) {


                                    $kiw_temp['quota'] = ($kiw_profiles['attribute']['control:Kiwire-Total-Quota'] + $kiw_account['profile_cus']['quota']) - $kiw_usage['quota'];


                                } else {

                                    $kiw_temp['quota'] = $kiw_account['profile_cus']['quota'];


                                }

                                if ($kiw_temp['quota'] < 0) $kiw_temp['quota'] = 0;


                            }


                            unset($kiw_usage);


                            $kiw_temp = json_encode($kiw_temp);

                            $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), session_time = 0, quota_in = 0, quota_out = 0, date_activate = NULL, profile_curr = profile_subs, profile_cus = '{$kiw_temp}' WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND profile_subs = '{$kiw_profile['profile']}'");


                        } else {

                            $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), session_time = 0, quota_in = 0, quota_out = 0, date_activate = NULL, profile_curr = profile_subs, profile_cus = '' WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND profile_subs = '{$kiw_profile['profile']}'");

                            // $kiw_db->query("UPDATE kiwire_account_auth SET session_time = 0, quota_in = 0, quota_out = 0, date_activate = NULL, profile_curr = '{$kiw_profile['profile']}' WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND profile_subs = '{$kiw_profile['profile']}'");

                        }

                    }

                }


                unset($kiw_profile);
                unset($kiw_profiles);


            }

            $kiw_reset_status['weekly'] = time();


        }


    }



    // reset account monthly

    if ($kiw_data['end_month'] == true && ("{$kiw_data['hour']}:{$kiw_data['minute']}" == $kiw_system['reset_monthly'])) {


        if ((time() - $kiw_reset_status['monthly']) > 259200) {


            foreach ($kiw_clouds_db as $kiw_cloud) {


                $kiw_profiles = $kiw_db->query("SELECT * FROM kiwire_auto_reset WHERE exec_when = 'm' AND tenant_id = '{$kiw_cloud['tenant_id']}'");

                $kiw_profiles['attribute'] = json_decode($kiw_profiles['attribute'], true);


                foreach ($kiw_profiles as $kiw_profile) {


                    $kiw_accounts = $kiw_db->query("SELECT username, profile_cus, quota_in, quota_out, session_time FROM kiwire_account_auth WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND profile_subs = '{$kiw_profile['profile']}'");

                    $kiw_cf = $kiw_db->query("SELECT * from kiwire_clouds WHERE tenant_id = '{$kiw_cloud['tenant_id']}'");


                    foreach ($kiw_accounts as $kiw_account) {


                        if (!empty($kiw_account['profile_cus']) && $kiw_cf['carry_forward_topup'] == 'y') {


                            $kiw_account['profile_cus'] = json_decode($kiw_account['profile_cus'], true);


                            $kiw_usage['quota'] = ($kiw_account['quota_in'] + $kiw_account['quota_out']);



                            if ($kiw_account['profile_cus']['time'] > 0) {


                                if ($kiw_account['session_time'] < ($kiw_profiles['attribute']['control:Max-All-Session'] + $kiw_profiles['attribute']['control:Access-Period'])) {


                                    $kiw_temp['time'] = (($kiw_profiles['attribute']['control:Max-All-Session'] + $kiw_profiles['attribute']['control:Access-Period']) + $kiw_account['profile_cus']['time']) - $kiw_account['session_time'];


                                } else {

                                    $kiw_temp['time'] = $kiw_account['profile_cus']['time'];


                                }


                                if ($kiw_temp['time'] < 0) $kiw_temp['time'] = 0;


                            }


                            if ($kiw_account['profile_cus']['quota'] > 0) {


                                if ($kiw_usage['quota'] < $kiw_profiles['attribute']['control:Kiwire-Total-Quota']) {


                                    $kiw_temp['quota'] = ($kiw_profiles['attribute']['control:Kiwire-Total-Quota'] + $kiw_account['profile_cus']['quota']) - $kiw_usage['quota'];


                                } else {

                                    $kiw_temp['quota'] = $kiw_account['profile_cus']['quota'];


                                }

                                if ($kiw_temp['quota'] < 0) $kiw_temp['quota'] = 0;


                            }


                            unset($kiw_usage);


                            $kiw_temp = json_encode($kiw_temp);

                            $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), session_time = 0, quota_in = 0, quota_out = 0, date_activate = NULL, profile_curr = profile_subs, profile_cus = '{$kiw_temp}' WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND profile_subs = '{$kiw_profile['profile']}'");




                        } else {


                            $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), session_time = 0, quota_in = 0, quota_out = 0, date_activate = NULL, profile_curr = profile_subs, profile_cus = '' WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND profile_subs = '{$kiw_profile['profile']}'");

                            // $kiw_db->query("UPDATE kiwire_account_auth SET session_time = 0, quota_in = 0, quota_out = 0, date_activate = NULL, profile_curr = '{$kiw_profile['profile']}' WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND profile_subs = '{$kiw_profile['profile']}'");


                        }

                    }

                }

                unset($kiw_profile);
                unset($kiw_profiles);

            }

            $kiw_reset_status['monthly'] = time();


        }


    }



    // reset account yearly

    if ($kiw_data['end_year'] == true && ("{$kiw_data['hour']}:{$kiw_data['minute']}" == $kiw_system['reset_yearly'])) {

        if ((time() - $kiw_reset_status['yearly']) > 259200) {


            foreach ($kiw_clouds_db as $kiw_cloud) {


                $kiw_profiles = $kiw_db->query("SELECT * FROM kiwire_auto_reset WHERE exec_when = 'm' AND tenant_id = '{$kiw_cloud['tenant_id']}'");


                foreach ($kiw_profiles as $kiw_profile) {


                    $kiw_accounts = $kiw_db->query("SELECT username, profile_cus, quota_in, quota_out, session_time FROM kiwire_account_auth WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND profile_subs = '{$kiw_profile['profile']}'");

                    $kiw_cf = $kiw_db->query("SELECT * from kiwire_clouds WHERE tenant_id = '{$kiw_cloud['tenant_id']}'");


                    foreach ($kiw_accounts as $kiw_account) {


                        if (!empty($kiw_account['profile_cus']) && $kiw_cf['carry_forward_topup'] == 'y') {


                            $kiw_account['profile_cus'] = json_decode($kiw_account['profile_cus'], true);


                            $kiw_usage['quota'] = ($kiw_account['quota_in'] + $kiw_account['quota_out']);



                            if ($kiw_account['profile_cus']['time'] > 0) {


                                if ($kiw_account['session_time'] < ($kiw_profiles['attribute']['control:Max-All-Session'] + $kiw_profiles['attribute']['control:Access-Period'])) {


                                    $kiw_temp['time'] = (($kiw_profiles['attribute']['control:Max-All-Session'] + $kiw_profiles['attribute']['control:Access-Period']) + $kiw_account['profile_cus']['time']) - $kiw_account['session_time'];


                                } else {

                                    $kiw_temp['time'] = $kiw_account['profile_cus']['time'];


                                }


                                if ($kiw_temp['time'] < 0) $kiw_temp['time'] = 0;


                            }


                            if ($kiw_account['profile_cus']['quota'] > 0) {


                                if ($kiw_usage['quota'] < $kiw_profiles['attribute']['control:Kiwire-Total-Quota']) {


                                    $kiw_temp['quota'] = ($kiw_profiles['attribute']['control:Kiwire-Total-Quota'] + $kiw_account['profile_cus']['quota']) - $kiw_usage['quota'];


                                } else {

                                    $kiw_temp['quota'] = $kiw_account['profile_cus']['quota'];


                                }

                                if ($kiw_temp['quota'] < 0) $kiw_temp['quota'] = 0;


                            }


                            unset($kiw_usage);


                            $kiw_temp = json_encode($kiw_temp);

                            $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), session_time = 0, quota_in = 0, quota_out = 0, date_activate = NULL, profile_curr = profile_subs, profile_cus = '{$kiw_temp}' WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND profile_subs = '{$kiw_profile['profile']}'");



                        } else {


                            $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), session_time = 0, quota_in = 0, quota_out = 0, date_activate = NULL, profile_curr = profile_subs, profile_cus = '' WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND profile_subs = '{$kiw_profile['profile']}'");

                            // $kiw_db->query("UPDATE kiwire_account_auth SET session_time = 0, quota_in = 0, quota_out = 0, date_activate = NULL, profile_curr = '{$kiw_profile['profile']}' WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND profile_subs = '{$kiw_profile['profile']}'");

                        }

                    }
                  
                }

                unset($kiw_profile);
                unset($kiw_profiles);

            }


            $kiw_reset_status['yearly'] = time();

        }


    }



    @file_put_contents("{$kiw_log_path}/reset_status.json", json_encode($kiw_reset_status));

    unset($kiw_reset_status);



    // check for domain blacklisted

    if ($kiw_data['end_week'] == true && $kiw_data['hour'] == "00" && $kiw_data['minute'] == "00") {


        $kiw_temp = @file_get_contents("https://mail-delivery-system.synchroweb.com/bad_mails.php");

        $kiw_temp = json_decode($kiw_temp, true);


        if ($kiw_temp) {

            $kiw_db->query("TRUNCATE kiwire_blacklist_domain");

            foreach ($kiw_temp as $kiw_mail) {

                $kiw_db->query("INSERT INTO kiwire_blacklist_domain(`mail_domain`) VALUE('{$kiw_mail}')");
            }
        }

        unset($kiw_temp);
    }


    // delete log files more than specified by user


    // send data to marketing email service

    $kiw_last_data = @file_get_contents("{$kiw_log_path}/marketing_email.json");

    $kiw_last_data = json_decode($kiw_last_data, true);


    $kiw_temp = $kiw_db->query("SELECT * FROM kiwire_int_marketing_email WHERE madmini_en = 'y' OR mailchimp_en = 'y'");



    if (is_array($kiw_temp) && count($kiw_temp) > 0) {


        require_once dirname(__FILE__, 3) . "/server/libs/mailchimp/MailChimp.php";
        require_once dirname(__FILE__, 3) . "/server/libs/mailchimp/Batch.php";

        require_once dirname(__FILE__, 3) . "/server/libs/madmimi/autoload.php";


        foreach ($kiw_temp as $kiw_email_sender) {

            if ($kiw_email_sender['madmini_en'] == "y") {


                $kiw_email_datas = $kiw_db->query("SELECT id,fullname,email_address FROM kiwire_account_info WHERE tenant_id = '{$kiw_email_sender['tenant_id']}' AND id > '{$kiw_last_data[$kiw_email_sender['tenant_id']]['madmimi']}' ORDER BY id ASC");

                foreach ($kiw_email_datas as $kiw_email_data) {

                    $kiw_last_data[$kiw_email_sender['tenant_id']]['madmimi'] = $kiw_email_data['id'];

                    echo "";
                }

                unset($kiw_email_datas);
            }


            if ($kiw_email_sender['mailchimp_en'] == "y") {


                $kiw_email_datas = $kiw_db->query("SELECT id,fullname,email_address,phone_number FROM kiwire_account_info WHERE tenant_id = '{$kiw_email_sender['tenant_id']}' AND id > '{$kiw_last_data[$kiw_email_sender['tenant_id']]['mailchimp']}' ORDER BY id ASC");


                if (is_array($kiw_email_datas) && count($kiw_email_datas) > 0) {

                    try {


                        $kiw_mailchimp = new \DrewM\MailChimp\MailChimp($kiw_email_sender['mailchimp_api']);

                        $kiw_batch = $kiw_mailchimp->new_batch();


                        foreach ($kiw_email_datas as $kiw_email_data) {


                            $kiw_batch->post($kiw_email_sender['id'], "lists/{$kiw_email_sender['mailchimp_lid']}/members", array(
                                'email_address' => $kiw_email_data['email_address'],
                                'merge_fields' => [
                                    'FNAME' => $kiw_email_data['fullname'],
                                    'PHONE' => $kiw_email_data['phone_number']
                                ],
                                'status' => 'subscribed'
                            ));

                            $kiw_last_data[$kiw_email_sender['tenant_id']]['mailchimp'] = $kiw_email_data['id'];
                        }


                        $kiw_batch->execute();
                    } catch (Exception $e) {

                        echo $kiw_email_sender['tenant_id'] . " : " . $e->getMessage() . "\n";
                    }

                    unset($kiw_email_data);
                }


                unset($kiw_email_datas);
            }
        }
    }


    unset($kiw_temp);


    @file_put_contents("{$kiw_log_path}/marketing_email.json", json_encode($kiw_last_data));

    unset($kiw_last_data);


    // check for license, if less than 1 months, 2 week, 1 week and on the day, it will send email to check

    if ("{$kiw_data['hour']}:{$kiw_data['minute']}" == "00:00") {


        foreach (array_merge($kiw_clouds_db, array("tenant_id" => "superuser")) as $kiw_cloud) {


            if ($kiw_cloud['tenant_id'] == "superuser") {

                $kiw_license = @file_get_contents("{$kiw_log_path}/cloud.license");
            } else $kiw_license = @file_get_contents("{$kiw_log_path}/{$kiw_cloud['tenant_id']}/tenant.license");


            if (strlen($kiw_license) > 0) {


                $kiw_license = sync_license_decode($kiw_license);

                if ($kiw_license) {


                    $kiw_test = ((new DateTime("now"))->diff(new DateTime(date("Y-m-d H:i:s", $kiw_license['expire_on']))))->format("%R%a");

                    if (in_array($kiw_test, array("+1", "+7", "+14", "+30"))) {


                        // get admin or superadmin email address

                        if ($kiw_cloud['tenant_id'] == "superuser") {

                            $kiw_admins = $kiw_db->query("SELECT * FROM kiwire_admin WHERE email <> '' AND monitor = 'y' AND tenant_id = 'superuser'");
                        } else $kiw_admins = $kiw_db->query("SELECT * FROM kiwire_admin WHERE email <> '' AND monitor = 'y' AND tenant_id = '{$kiw_cloud['tenant_id']}'");


                        $kiw_test = str_replace("+", "", $kiw_test);


                        foreach ($kiw_admins as $kiw_admin) {


                            $kiw_email['action']        = "send_email";
                            $kiw_email['tenant_id']     = $kiw_cloud['tenant_id'];
                            $kiw_email['email_address'] = $kiw_admin['email'];
                            $kiw_email['subject']       = "System Expired In {$kiw_test} Days";
                            $kiw_email['content']       = htmlentities("Hi there, <br><br>Your system will expired soon.");
                            $kiw_email['name']          = $kiw_admin['username'];


                            // update internal notification as well

                            $kiw_db->query("INSERT INTO kiwire_message (id, updated_date, tenant_id, sender, recipient, date_sent, date_read, title, message) VALUE (NULL, NOW(), '{$kiw_cloud['tenant_id']}', 'System', '{$kiw_admin['username']}', NOW(), '0000-00-00 00:00:00', '{$kiw_email['subject']}', '{$kiw_email['content']}')");


                            // send email to agent

                            $kiw_email_submit = curl_init();

                            curl_setopt($kiw_email_submit, CURLOPT_URL, "http://127.0.0.1:9956");
                            curl_setopt($kiw_email_submit, CURLOPT_POST, true);
                            curl_setopt($kiw_email_submit, CURLOPT_POSTFIELDS, http_build_query($kiw_email));
                            curl_setopt($kiw_email_submit, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($kiw_email_submit, CURLOPT_TIMEOUT, 5);
                            curl_setopt($kiw_email_submit, CURLOPT_CONNECTTIMEOUT, 5);


                            unset($kiw_email);


                            curl_exec($kiw_email_submit);

                            curl_close($kiw_email_submit);

                            unset($kiw_email_submit);
                        }


                        unset($kiw_admin);
                        unset($kiw_admins);
                    }


                    unset($kiw_test);
                }
            }


            unset($kiw_license);
        }
    }



    // collect report data from redis and save to database for view

    $kiw_report_test = date("YmdH");

    $kiw_ran_before = @file_get_contents("{$kiw_log_path}/report_date.log");


    if ($kiw_ran_before != $kiw_data['hour']) {


        foreach ($kiw_clouds_db as $kiw_cloud) {


            // get all necessary data for reporting for this cloud

            $kiw_report_zones = $kiw_db->query("SELECT name FROM kiwire_zone WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND status = 'y'");

            $kiw_report_zones[] = array("name" => "nozone");


            // get the profile list

            $kiw_report_profiles = $kiw_db->query("SELECT name FROM kiwire_profiles WHERE tenant_id = '{$kiw_cloud['tenant_id']}'");


            // get the error list

            $kiw_report_errors = $kiw_db->query("SELECT error_account_inactive, error_future_value_date, error_wrong_credential, error_no_credential, error_password_expired, error_zone_reached_limit, error_wrong_mac_address, error_zone_restriction, error_ot_reset_grace, error_reached_time_limit, error_reached_quota_limit, error_max_simultaneous_use FROM kiwire_notification WHERE tenant_id = '{$kiw_cloud['tenant_id']}'");


            $kiw_error_unique = array();


            foreach ($kiw_report_errors as $kiw_report_error) {

                foreach ($kiw_report_error as $kiw_key => $kiw_value) {

                    $kiw_error_unique[md5($kiw_value)] = $kiw_value;
                }
            }


            unset($kiw_report_error);

            unset($kiw_report_errors);


            // insert current time row

            $kiw_report_time = date("Y-m-d H:00:00", strtotime("-1 Hour"));


            // insert dummy data to be update later

            foreach ($kiw_report_zones as $kiw_zone) {


                $kiw_connected_number = $kiw_connected[$kiw_cloud['tenant_id']][$kiw_zone['name']];

                if (empty($kiw_connected_number)) $kiw_connected_number = 0;


                $kiw_db->query("INSERT INTO kiwire_report_login_general(id,report_date,tenant_id,zone, connected) VALUE (NULL, '{$kiw_report_time}', '{$kiw_cloud['tenant_id']}', '{$kiw_zone['name']}', {$kiw_connected_number})");

                unset($kiw_connected_number);



                foreach ($kiw_report_profiles as $kiw_report_profile) {

                    $kiw_db->query("INSERT INTO kiwire_report_login_profile (id, report_date, tenant_id, zone, profile, dwell, login, u_login) VALUE (NULL, '{$kiw_report_time}', '{$kiw_cloud['tenant_id']}', '{$kiw_zone['name']}', '{$kiw_report_profile['name']}', 0, 0, 0)");
                }


                // update dwell table

                foreach (array('5MIN', '15MIN', '30MIN', '45MIN', '1HOUR', '2HOUR', '3HOUR', '4HOUR', '5HOUR', '6HOUR', 'MOREHOUR') as $kiw_dwell_type) {

                    $kiw_db->query("INSERT INTO kiwire_report_login_dwell(id, report_date, updated_date, tenant_id, zone, type, count) VALUE (NULL, '{$kiw_report_time}', NOW(), '{$kiw_cloud['tenant_id']}', '{$kiw_zone['name']}', '{$kiw_dwell_type}', 0)");
                }
            }


            unset($kiw_report_time);



            // update data from redis to db and delete

            $kiw_redis_reports = $kiw_cache->keys("REPORT_*:*:{$kiw_cloud['tenant_id']}:*");


            foreach ($kiw_redis_reports as $kiw_redis_report) {


                // explode the key to determine function

                $kiw_redis_report = explode(":", $kiw_redis_report);


                if ($kiw_redis_report[0] != "REPORT_DWELL_TYPE") {


                    if ($kiw_redis_report[1] == $kiw_report_test) {

                        continue;
                    }

                    $kiw_report_time = date("Y-m-d H:00:00", strtotime($kiw_redis_report[1] . ":00:00"));
                } else {


                    if ($kiw_redis_report[2] == $kiw_report_test) {

                        continue;
                    }

                    $kiw_report_time = date("Y-m-d H:00:00", strtotime($kiw_redis_report[2] . ":00:00"));
                }



                // get the actual value from redis

                $kiw_redis_value = $kiw_cache->get(implode(":", array_filter($kiw_redis_report)));


                if (in_array($kiw_redis_report[0], array("REPORT_CAMPAIGN_UIMPRESS", "REPORT_CAMPAIGN_IMPRESS", "REPORT_CAMPAIGN_CLICK", "REPORT_CAMPAIGN_UCLICK"))) {


                    $kiw_campaign_name = urldecode(base64_decode($kiw_redis_report[5]));


                    if (strpos($kiw_campaign_name, "||") == false) {

                        $kiw_campaign_name = urldecode(base64_decode($kiw_campaign_name));
                    }


                    if ($kiw_redis_report[4] == "external") {

                        $kiw_source = ", source = 'external'";
                    } else $kiw_source = ", source = 'internal'";


                    switch ($kiw_redis_report[0]) {
                        case "REPORT_CAMPAIGN_UIMPRESS":
                            $kiw_column = "u_impress";
                            break;
                        case "REPORT_CAMPAIGN_IMPRESS":
                            $kiw_column = "impress";
                            break;
                        case "REPORT_CAMPAIGN_CLICK":
                            $kiw_column = "click";
                            break;
                        case "REPORT_CAMPAIGN_UCLICK":
                            $kiw_column = "u_click";
                            break;
                    }


                    $kiw_db->query("UPDATE kiwire_report_campaign_general SET updated_date = NOW(), {$kiw_column} = {$kiw_redis_value} {$kiw_source} WHERE name = '{$kiw_campaign_name}' AND tenant_id = '{$kiw_cloud['tenant_id']}' AND zone = '{$kiw_redis_report[3]}' AND report_date = '{$kiw_report_time}' LIMIT 1");

                    if ($kiw_db->affected_rows == 0) {


                        if ($kiw_redis_report[4] == "external") {

                            $kiw_source = "external";
                        } else $kiw_source = "internal";


                        $kiw_db->query("INSERT INTO kiwire_report_campaign_general(id, report_date, tenant_id, zone, updated_date, name, source, {$kiw_column}) VALUE (NULL, '{$kiw_report_time}', '{$kiw_cloud['tenant_id']}', '{$kiw_redis_report[3]}', NOW(), '{$kiw_campaign_name}', '{$kiw_source}', {$kiw_redis_value})");
                    }

                    unset($kiw_column);
                    unset($kiw_campaign_name);
                    unset($kiw_source);
                } elseif ($kiw_redis_report[0] == "REPORT_OFFLINE_CAMPAIGN") {


                    $kiw_campaign_name = base64_decode($kiw_redis_report[3]);

                    $kiw_db->query("INSERT INTO kiwire_report_campaign_offline(id, report_date, tenant_id, zone, updated_date, name, source, execute) VALUE (NULL, '{$kiw_report_time}', '{$kiw_cloud['tenant_id']}', 'nozone', NOW(), '{$kiw_campaign_name}', 'internal', {$kiw_redis_value})");

                    unset($kiw_campaign_name);
                } elseif ($kiw_redis_report[0] == "REPORT_IMPRESSION_ZONE") {


                    $kiw_db->query("UPDATE kiwire_report_login_general SET updated_date = NOW(), impression = impression + {$kiw_redis_value} WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND report_date = '{$kiw_report_time}' AND zone = '{$kiw_redis_report[3]}' LIMIT 1");
                } elseif ($kiw_redis_report[0] == "REPORT_LOGIN_REQUEST") {


                    $kiw_db->query("UPDATE kiwire_report_login_general SET updated_date = NOW(), attemp = attemp + {$kiw_redis_value} WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND report_date = '{$kiw_report_time}' AND zone = '{$kiw_redis_report[3]}' LIMIT 1");
                } elseif ($kiw_redis_report[0] == "REPORT_LOGIN_FAILED") {


                    $kiw_db->query("UPDATE kiwire_report_login_general SET updated_date = NOW(), failed = failed + {$kiw_redis_value} WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND report_date = '{$kiw_report_time}' AND zone = '{$kiw_redis_report[3]}' LIMIT 1");
                } elseif ($kiw_redis_report[0] == "REPORT_LOGIN_FIRST_ACCOUNT") {


                    $kiw_db->query("UPDATE kiwire_report_login_general SET updated_date = NOW(), account_new = account_new + {$kiw_redis_value} WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND report_date = '{$kiw_report_time}' AND zone = '{$kiw_redis_report[3]}' LIMIT 1");
                } elseif ($kiw_redis_report[0] == "REPORT_LOGIN_RETURN_ACCOUNT") {


                    $kiw_db->query("UPDATE kiwire_report_login_general SET updated_date = NOW(), account_return = account_return + {$kiw_redis_value} WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND report_date = '{$kiw_report_time}' AND zone = '{$kiw_redis_report[3]}' LIMIT 1");
                } elseif ($kiw_redis_report[0] == "REPORT_LOGIN_UNIQUE_ACCOUNT") {


                    $kiw_db->query("UPDATE kiwire_report_login_general SET updated_date = NOW(), account_unique = account_unique + {$kiw_redis_value} WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND report_date = '{$kiw_report_time}' AND zone = '{$kiw_redis_report[3]}' LIMIT 1");
                } elseif ($kiw_redis_report[0] == "REPORT_LOGIN_RETURN_DEVICE") {


                    $kiw_db->query("UPDATE kiwire_report_login_general SET updated_date = NOW(), device_return = device_return + {$kiw_redis_value} WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND report_date = '{$kiw_report_time}' AND zone = '{$kiw_redis_report[3]}' LIMIT 1");
                } elseif ($kiw_redis_report[0] == "REPORT_LOGIN_UNIQUE_DEVICE") {


                    $kiw_db->query("UPDATE kiwire_report_login_general SET updated_date = NOW(), device_unique = device_unique + {$kiw_redis_value} WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND report_date = '{$kiw_report_time}' AND zone = '{$kiw_redis_report[3]}' LIMIT 1");
                } elseif ($kiw_redis_report[0] == "REPORT_DISCONNECT") {


                    $kiw_db->query("UPDATE kiwire_report_login_general SET updated_date = NOW(), disconnect = disconnect + {$kiw_redis_value} WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND report_date = '{$kiw_report_time}' AND zone = '{$kiw_redis_report[3]}' LIMIT 1");
                } elseif ($kiw_redis_report[0] == "REPORT_CONCURRENT") {


                    $kiw_db->query("UPDATE kiwire_report_login_general SET updated_date = NOW(), concurrent = concurrent + {$kiw_redis_value} WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND report_date = '{$kiw_report_time}' AND zone = '{$kiw_redis_report[3]}' LIMIT 1");
                } elseif ($kiw_redis_report[0] == "REPORT_ULOGIN_ZONE") {


                    $kiw_db->query("UPDATE kiwire_report_login_general SET updated_date = NOW(), ulogin = ulogin + {$kiw_redis_value} WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND report_date = '{$kiw_report_time}' AND zone = '{$kiw_redis_report[3]}' LIMIT 1");
                } elseif ($kiw_redis_report[0] == "REPORT_TOTAL_TIME") {


                    $kiw_db->query("UPDATE kiwire_report_login_general SET updated_date = NOW(), time = time + {$kiw_redis_value} WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND report_date = '{$kiw_report_time}' AND zone = '{$kiw_redis_report[3]}' LIMIT 1");
                } elseif ($kiw_redis_report[0] == "REPORT_TOTAL_QUOTA_IN") {


                    $kiw_db->query("UPDATE kiwire_report_login_general SET updated_date = NOW(), quota_in = quota_in + {$kiw_redis_value} WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND report_date = '{$kiw_report_time}' AND zone = '{$kiw_redis_report[3]}' LIMIT 1");
                } elseif ($kiw_redis_report[0] == "REPORT_TOTAL_QUOTA_OUT") {


                    $kiw_db->query("UPDATE kiwire_report_login_general SET updated_date = NOW(), quota_out = quota_out + {$kiw_redis_value} WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND report_date = '{$kiw_report_time}' AND zone = '{$kiw_redis_report[3]}' LIMIT 1");
                } elseif ($kiw_redis_report[0] == "REPORT_LOGIN_SUCCESS") {


                    $kiw_db->query("UPDATE kiwire_report_login_general SET updated_date = NOW(), succeed = succeed + {$kiw_redis_value} WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND report_date = '{$kiw_report_time}' AND zone = '{$kiw_redis_report[3]}' LIMIT 1");
                } elseif ($kiw_redis_report[0] == "REPORT_ERROR_COUNT") {


                    $kiw_error_message = $kiw_error_unique[$kiw_redis_report[3]];

                    $kiw_db->query("INSERT INTO kiwire_report_login_error(id, report_date, tenant_id, updated_date, error_hash, error_message, count) VALUE (NULL, '{$kiw_report_time}', '{$kiw_cloud['tenant_id']}', NOW(), '{$kiw_redis_report[3]}', '{$kiw_error_message}', {$kiw_redis_value})");

                    unset($kiw_error_message);
                } elseif ($kiw_redis_report[0] == "REPORT_ACCOUNT_CREATION") {


                    $kiw_db->query("UPDATE kiwire_report_login_general SET updated_date = NOW(), account_create = account_create + {$kiw_redis_value} WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND report_date = '{$kiw_report_time}' AND zone = 'nozone' LIMIT 1");
                } elseif ($kiw_redis_report[0] == "REPORT_DWELL_ZONE") {


                    $kiw_db->query("UPDATE kiwire_report_login_general SET updated_date = NOW(), dwell = dwell + {$kiw_redis_value} WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND report_date = '{$kiw_report_time}' AND zone = '{$kiw_redis_report[3]}' LIMIT 1");
                } elseif ($kiw_redis_report[0] == "REPORT_DWELL_PROFILE") {


                    $kiw_db->query("UPDATE kiwire_report_login_profile SET updated_date = NOW(), dwell = dwell + {$kiw_redis_value} WHERE profile = '{$kiw_redis_report[4]}' AND tenant_id = '{$kiw_cloud['tenant_id']}' AND report_date = '{$kiw_report_time}' AND zone = '{$kiw_redis_report[3]}' LIMIT 1");
                } elseif ($kiw_redis_report[0] == "REPORT_LOGIN_PROFILE") {


                    $kiw_db->query("UPDATE kiwire_report_login_profile SET updated_date = NOW(), login = login + {$kiw_redis_value} WHERE profile = '{$kiw_redis_report[4]}' AND tenant_id = '{$kiw_cloud['tenant_id']}' AND report_date = '{$kiw_report_time}' AND zone = '{$kiw_redis_report[3]}' LIMIT 1");
                } elseif ($kiw_redis_report[0] == "REPORT_ULOGIN_PROFILE") {


                    $kiw_db->query("UPDATE kiwire_report_login_profile SET updated_date = NOW(), u_login = u_login + {$kiw_redis_value} WHERE profile = '{$kiw_redis_report[4]}' AND tenant_id = '{$kiw_cloud['tenant_id']}' AND report_date = '{$kiw_report_time}' AND zone = '{$kiw_redis_report[3]}' LIMIT 1");
                } elseif ($kiw_redis_report[0] == "REPORT_LOGIN_FIRST_DEVICE") {


                    $kiw_db->query("UPDATE kiwire_report_login_general SET updated_date = NOW(), device_new = device_new + {$kiw_redis_value} WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND report_date = '{$kiw_report_time}' AND zone = '{$kiw_redis_report[3]}' LIMIT 1");
                } elseif ($kiw_redis_report[0] == "REPORT_DWELL_TYPE") {


                    $kiw_db->query("UPDATE kiwire_report_login_dwell SET updated_date = NOW(), count = count + {$kiw_redis_value} WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND report_date = '{$kiw_report_time}' AND type = '{$kiw_redis_report[1]}' AND zone = '{$kiw_redis_report[4]}' LIMIT 1");
                } elseif (in_array($kiw_redis_report[0], array("REPORT_LOGIN_BRAND", "REPORT_LOGIN_SYSTEM", "REPORT_LOGIN_CLASS"))) {


                    switch ($kiw_redis_report[0]) {
                        case "REPORT_LOGIN_BRAND":
                            $kiw_info_type = "brand";
                            break;
                        case "REPORT_LOGIN_SYSTEM":
                            $kiw_info_type = "system";
                            break;
                        case "REPORT_LOGIN_CLASS":
                            $kiw_info_type = "class";
                            break;
                    }


                    if (empty($kiw_redis_report[4])) $kiw_redis_report[4] = "Unknown";


                    $kiw_db->query("INSERT INTO kiwire_report_login_device(id, report_date, tenant_id, updated_date, zone, info, value, count) VALUE (NULL, '{$kiw_report_time}', '{$kiw_cloud['tenant_id']}', NOW(), '{$kiw_redis_report[3]}', '{$kiw_info_type}', '{$kiw_redis_report[4]}', {$kiw_redis_value})");


                    unset($kiw_info_type);
                } elseif ($kiw_redis_report[0] == "REPORT_EMAIL_SENT") {


                    $kiw_db->query("UPDATE kiwire_report_login_general SET updated_date = NOW(), email = email + {$kiw_redis_value} WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND report_date = '{$kiw_report_time}' AND zone = 'nozone' LIMIT 1");
                } elseif ($kiw_redis_report[0] == "REPORT_SMS_SENT") {


                    $kiw_db->query("UPDATE kiwire_report_login_general SET updated_date = NOW(), sms = sms + {$kiw_redis_value} WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND report_date = '{$kiw_report_time}' AND zone = 'nozone' LIMIT 1");
                }


                $kiw_cache->del(implode(":", array_filter($kiw_redis_report)));
            }


            unset($kiw_error_unique);
        }


        unset($kiw_report_zones);
        unset($kiw_report_profiles);


        @file_put_contents("{$kiw_log_path}/report_date.log", $kiw_data['hour']);


        // delete login attempt data as no longer use

        $kiw_report_time = date("YmdH");


        $kiw_login_attemps = $kiw_cache->keys("REPORT_LOGIN_ATTEMP:*");


        foreach ($kiw_login_attemps as $kiw_login_attemp) {

            if (substr($kiw_login_attemp, -10) != $kiw_report_time) {

                $kiw_cache->del($kiw_login_attemp);
            }
        }
    }


    unset($kiw_connected);
    unset($kiw_report_time);
    unset($kiw_ran_before);


    if ($kiw_data['end_month'] == true) {


        $kiw_ran_before = @file_get_contents("{$kiw_log_path}/password_date.log");

        $kiw_last_run = date("m");


        if ($kiw_ran_before != $kiw_last_run) {


            $kiw_next_month = date("m", strtotime("+1 Minute"));


            foreach ($kiw_clouds_db as $kiw_cloud) {


                if (file_exists("{$kiw_log_path}/{$kiw_cloud['tenant_id']}/password_reset.json") == true) {


                    $kiw_password_reset = @file_get_contents("{$kiw_log_path}/{$kiw_cloud['tenant_id']}/password_reset.json");

                    $kiw_password_reset = trim(preg_replace('/\s\s+/', ' ', $kiw_password_reset));

                    $kiw_password_reset = json_decode($kiw_password_reset, true);


                    if (is_array($kiw_password_reset)) {


                        if (isset($kiw_password_reset[0])) {


                            foreach ($kiw_password_reset as $kiw_password_reset_) {


                                if (!empty($kiw_password_reset_[$kiw_next_month])) {


                                    $kiw_new_password = sync_encrypt($kiw_password_reset_[$kiw_next_month]);

                                    $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), password = '{$kiw_new_password}' WHERE profile_subs = '{$kiw_password_reset_['profile']}' AND tenant_id = '{$kiw_cloud['tenant_id']}'");
                                }
                            }
                        } else {


                            if (!empty($kiw_password_reset[$kiw_next_month])) {


                                $kiw_new_password = sync_encrypt($kiw_password_reset[$kiw_next_month]);

                                $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), password = '{$kiw_new_password}' WHERE profile_subs = '{$kiw_password_reset['profile']}' AND tenant_id = '{$kiw_cloud['tenant_id']}'");
                            }
                        }
                    }


                    unset($kiw_password_reset);
                }
            }


            unset($kiw_next_month);


            @file_put_contents("{$kiw_log_path}/password_date.log", $kiw_last_run);
        }
    }


    // get the custom schedule script

    foreach ($kiw_clouds_db as $kiw_cloud) {


        $kiw_custom_schedule_path = "{$kiw_log_path}/{$kiw_cloud['tenant_id']}/schedule";

        $kiw_custom_schedule_scripts = scandir($kiw_custom_schedule_path);


        foreach ($kiw_custom_schedule_scripts as $kiw_custom_schedule_script) {


            $kiw_temp = false;


            if (!in_array($kiw_custom_schedule_script, array(".", ".."))) {


                // check if we need to execute this script

                if ($kiw_custom_schedule_script == "schedule-min.php") $kiw_temp = true;
                elseif ($kiw_custom_schedule_script == "schedule-30mins.php" && "{$kiw_data['hour']}:{$kiw_data['minute']}" == "00:30") $kiw_temp = true;
                elseif ($kiw_custom_schedule_script == "schedule-hourly.php" && "{$kiw_data['hour']}:{$kiw_data['minute']}" == "00:00") $kiw_temp = true;
                elseif ($kiw_custom_schedule_script == "schedule-daily.php" && "{$kiw_data['hour']}:{$kiw_data['minute']}" == $kiw_system['reset_daily']) $kiw_temp = true;
                elseif ($kiw_custom_schedule_script == "schedule-weekly.php" && $kiw_data['end_week'] == true && ("{$kiw_data['hour']}:{$kiw_data['minute']}" == $kiw_system['reset_weekly'])) $kiw_temp = true;
                elseif ($kiw_custom_schedule_script == "schedule-monthly.php" && $kiw_data['end_month'] == true && ("{$kiw_data['hour']}:{$kiw_data['minute']}" == $kiw_system['reset_monthly'])) $kiw_temp = true;
                elseif ($kiw_custom_schedule_script == "schedule-yearly.php" && $kiw_data['end_year'] == true && ("{$kiw_data['hour']}:{$kiw_data['minute']}" == $kiw_system['reset_yearly'])) $kiw_temp = true;


                // execute the script if all passed

                if ($kiw_temp == true) system("nohup /usr/bin/php {$kiw_custom_schedule_path}/{$kiw_custom_schedule_script} 1>/dev/null 2>&1 &");
            }
        }
    }


    unset($kiw_custom_schedule_path);

    unset($kiw_custom_schedule_scripts);



    if ("{$kiw_data['hour']}:{$kiw_data['minute']}" == $kiw_system['reset_daily']) {


        $kiw_ran_before = @file_get_contents("{$kiw_log_path}/account_date.log");

        $kiw_last_run = date("d");


        if ($kiw_ran_before != $kiw_last_run) {


            foreach ($kiw_clouds_db as $kiw_cloud) {


                // if got policies then execute these policies

                $kiw_policies = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_account_policy WHERE tenant_id = '{$kiw_cloud['tenant_id']}' AND status = 'y'");


                foreach ($kiw_policies as $kiw_policy) {


                    // default to not run the policy

                    $kiw_temp = false;


                    // set to proceed or not based on the frequency

                    if ($kiw_policy['frequency'] == "daily") $kiw_temp = true;
                    elseif ($kiw_policy['frequency'] == "weekly" && $kiw_data['end_week'] == true) $kiw_temp = true;
                    elseif ($kiw_policy['frequency'] == "monthly" && $kiw_data['end_month'] == true) $kiw_temp = true;
                    elseif ($kiw_policy['frequency'] == "yearly" && $kiw_data['end_year'] == true) $kiw_temp = true;


                    if ($kiw_temp == true) {


                        // check if only certain username to be search

                        if (!empty($kiw_policy['username'])) {


                            $kiw_policy['username'] = $kiw_db->escape($kiw_policy['username']);


                            if (substr($kiw_policy['username'], 0, 1) == "-" && substr($kiw_policy['username'], -1) == "-") {

                                $kiw_policy['username'] = "AND username = '" . ltrim(trim($kiw_policy['username'], "-"), "-") . "'";
                            } else $kiw_policy['username'] = "AND username LIKE '%{$kiw_policy['username']}%'";
                        } else $kiw_policy['username'] = "";


                        // check only if certain status to be search

                        if (!empty($kiw_policy['policy_status'])) {


                            if (strtolower($kiw_policy['policy_status']) != "all") {

                                $kiw_policy['policy_status'] = "AND status = '{$kiw_policy['policy_status']}'";
                            } else $kiw_policy['policy_status'] = "";
                        }
                        $kiw_policy['policy_status'] = "";


                        // check if certain integration to be search

                        if (!empty($kiw_policy['policy_integration'])) {


                            if (strtolower($kiw_policy['policy_integration']) != "all") {

                                $kiw_policy['policy_integration'] = "AND integration = '{$kiw_policy['policy_integration']}'";
                            } else $kiw_policy['policy_integration'] = "";
                        }
                        $kiw_policy['policy_integration'] = "";



                        // take action accordingly

                        if ($kiw_policy['exec_action'] == "delete_account") {


                            $kiw_db->query("DELETE FROM kiwire_account_auth WHERE tenant_id = '{$kiw_cloud['tenant_id']}' {$kiw_policy['policy_status']} {$kiw_policy['policy_integration']} {$kiw_policy['username']}");
                        } elseif ($kiw_policy['exec_action'] == "update_password") {


                            // check if password need to be randomize

                            if ($kiw_policy['action_value'] == "[random]") {

                                $kiw_policy['action_value'] = substr(md5(time() . rand(0, 9999)), 0, 8);
                            }


                            // encrypted the password value before save

                            $kiw_policy['action_value'] = sync_encrypt($kiw_policy['action_value']);


                            // update password for tenant or user

                            $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), password = '{$kiw_policy['action_value']}' WHERE tenant_id = '{$kiw_cloud['tenant_id']}' {$kiw_policy['policy_status']} {$kiw_policy['policy_integration']} {$kiw_policy['username']}");
                        } elseif ($kiw_policy['exec_action'] == "update_status") {


                            // check the status insert by the user

                            if ($kiw_policy['action_value'] == "active") {

                                $kiw_policy['action_value'] = "active";
                            } elseif ($kiw_policy['action_value'] == "suspend") {

                                $kiw_policy['action_value'] = "suspend";
                            } else {

                                $kiw_policy['action_value'] = "expired";
                            }


                            // update the table with the correct value

                            $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), status = '{$kiw_policy['action_value']}' WHERE tenant_id = '{$kiw_cloud['tenant_id']}' {$kiw_policy['policy_status']} {$kiw_policy['policy_integration']} {$kiw_policy['username']}");
                        }
                    }
                }


                unset($kiw_policies);
            }


            // update log file to avoid duplicate actions

            @file_put_contents("{$kiw_log_path}/account_date.log", $kiw_last_run);
        }
    }


    // do file backup overnight

    if (empty($kiw_system['backup_db'])) {

        $kiw_system['backup_db'] = "00:00";
    }


    $kiw_check_time = strtotime(date("Y-m-d {$kiw_system['backup_db']}:00"));


    $kiw_replication = @file_get_contents("{$kiw_log_path}/ha_setting.json");

    $kiw_replication = json_decode($kiw_replication, true);


    // if ha enabled, backup only done at backup server

    if ($kiw_replication['enabled'] != "y" || $kiw_replication['role'] == "backup") {


        if ((($kiw_check_time - 300) < $kiw_data['time_seconds']) && ($kiw_data['time_seconds'] < ($kiw_check_time + 300))) {


            $kiw_ran_before = @file_get_contents("{$kiw_log_path}/backup_date.log");

            $kiw_last_run = date("d");


            if ($kiw_ran_before !== $kiw_last_run) {


                $kiw_cache->set("REPLICATION_ARCHIVE", true);
                $kiw_cache->expire("REPLICATION_ARCHIVE", 120);


                $kiw_data['backup'] = date("Ymd");


                // check if folder available, if no then create

                if (file_exists("{$kiw_data['path']}/backups/{$kiw_data['backup']}/") == false) {

                    mkdir("{$kiw_data['path']}/backups/{$kiw_data['backup']}/", 0755, true);
                }


                // check all available table

                $kiw_temp = $kiw_db->query("SELECT `TABLE_NAME` FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'kiwire'");

                foreach ($kiw_temp as $table) {

                    if (!empty($table['TABLE_NAME'])) {


                        // dont overwrite if file existed

                        if (file_exists("{$kiw_data['path']}/backups/{$kiw_data['backup']}/{$table['TABLE_NAME']}.sql.gz") == false) {

                            system("mysqldump --single-transaction --replace kiwire {$table['TABLE_NAME']} | gzip -c > {$kiw_data['path']}/backups/{$kiw_data['backup']}/{$table['TABLE_NAME']}.sql.gz");
                        }
                    }
                }


                unset($table);
                unset($kiw_temp);


                system("tar cfzp {$kiw_data['path']}/backups/{$kiw_data['backup']}/daily-files-" . date("Ymd") . ".tgz {$kiw_data['path']}/server/custom");


                @file_put_contents("{$kiw_log_path}/backup_date.log", $kiw_last_run);
            }
        }
    }

    $kiw_time['end'] = date('Y-m-d H:i:s');
    $kiw_cache->set("KIW_SCHEDULER:KIWIRE_SCHEDULER:RUN_AT", $kiw_time);

});
