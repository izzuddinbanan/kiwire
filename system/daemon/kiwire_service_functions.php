<?php


function check_logger($message, $tenant_id = "general"){


    if (empty(trim($tenant_id))){

        $tenant_id = "general";

    }


    if (file_exists(dirname(__FILE__, 3) . "/logs/{$tenant_id}/") == false){

        mkdir(dirname(__FILE__, 3) . "/logs/{$tenant_id}/", 0755, true);

    }


    file_put_contents( dirname(__FILE__, 3) . "/logs/{$tenant_id}/kiwire-service-{$tenant_id}-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s") . " :: " . $message . "\n", FILE_APPEND);


}




function check_active($kiw_db, $kiw_cache, $kiw_result, $kiw_notification, $kiw_user, $kiw_request){

    // check for user validity
    // status, expiry date, value date.

    if ($kiw_user['status'] != "active"){

        return array("Status" => "Error", "reply:Reply-Message" => $kiw_notification['error_account_inactive']);

    } elseif (time() - strtotime($kiw_user['date_value']) < 0){


        $kiw_notification['error_future_value_date'] = str_replace("{{value_date}}", $kiw_user['date_value'], $kiw_notification['error_future_value_date']);

        return array("Status" => "Error", "reply:Reply-Message" => $kiw_notification['error_future_value_date']);


    }


    // get the timezone for this tenant

    $kiw_timezone = $kiw_cache->get("CLOUD_DATA:{$kiw_user['tenant_id']}");

    if (empty($kiw_timezone)){


        $kiw_timezone = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_clouds WHERE tenant_id = '{$kiw_user['tenant_id']}' LIMIT 1")[0];

        if (empty($kiw_timezone)) $kiw_timezone = array("dummy" => true);

        $kiw_cache->set("CLOUD_DATA:{$kiw_user['tenant_id']}", $kiw_timezone, 1800);


    }


    try {


        $kiw_expiry = new DateTime($kiw_user['date_expiry'], new DateTimeZone("UTC"));

        $kiw_expiry->setTimezone(new DateTimeZone($kiw_timezone['timezone']));

        $kiw_expiry = $kiw_expiry->format("Y-m-d 23:59:59");


        $kiw_expiry = new DateTime($kiw_expiry, new DateTimeZone($kiw_timezone['timezone']));

        $kiw_expiry->setTimezone(new DateTimeZone("UTC"));

        $kiw_expiry = $kiw_expiry->getTimestamp();


    } catch (Exception $e){

        $kiw_expiry = strtotime($kiw_user['date_expiry']);

    }


    if ((time() - $kiw_expiry) >= 0){

        return array("Status" => "Error", "reply:Reply-Message" => $kiw_notification['error_account_inactive']);

    }


    // if no error, return the original value

    return $kiw_result;


}


function check_password($kiw_db, $kiw_cache, $kiw_result, $kiw_notification, $kiw_user, $kiw_request){

    // if no password then block access

    if ($kiw_user['integration'] != "pms") {


        // need to work on non local user

        if (in_array($kiw_user['integration'], array("msad", "ldap", "radius", "database"))) {


            // send to kiwire integration agent

            $kiw_data = array();

            $kiw_data['action']    = "check_{$kiw_user['integration']}";
            $kiw_data['username']  = $kiw_request['username'];
            $kiw_data['password']  = $kiw_request['password'];
            $kiw_data['tenant_id'] = $kiw_request['tenant_id'];

            $kiw_temp = curl_init();

            curl_setopt($kiw_temp, CURLOPT_URL, "http://127.0.0.1:9956");
            curl_setopt($kiw_temp, CURLOPT_POST, true);
            curl_setopt($kiw_temp, CURLOPT_POSTFIELDS, http_build_query($kiw_data));
            curl_setopt($kiw_temp, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($kiw_temp, CURLOPT_TIMEOUT, 5);
            curl_setopt($kiw_temp, CURLOPT_CONNECTTIMEOUT, 5);


            $kiw_login_status = curl_exec($kiw_temp);


            curl_close($kiw_temp);

            unset($kiw_temp);


            $kiw_login_status = json_decode($kiw_login_status, true);


            if ($kiw_login_status['status'] != "success") return array("Status" => "Error", "reply:Reply-Message" => $kiw_notification['error_wrong_credential']);


        } else {


            $kiw_temp = $kiw_cache->get("PASSWORD_POLICIES:{$kiw_user['tenant_id']}");

            if (empty($kiw_temp)){

                $kiw_temp = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_policies WHERE tenant_id = '{$kiw_user['tenant_id']}' LIMIT 1")[0];

                if (empty($kiw_temp)) $kiw_temp = array("dummy" => true);

                $kiw_cache->set("PASSWORD_POLICIES:{$kiw_user['tenant_id']}", $kiw_temp, 1800);

            }


            if (($kiw_request['auth_type'] != "chap") && empty($kiw_request['password'])) {


                return array("Status" => "Error", "reply:Reply-Message" => $kiw_notification['error_no_credential']);


            } elseif (($kiw_request['auth_type'] != "chap") && (sync_encrypt($kiw_request['password']) != $kiw_user['password'])) {


                if ($kiw_temp['password_policy'] == "y" && $kiw_temp['password_attempts'] == "y") {


                    $kiw_retry = $kiw_cache->get("LOGIN_ATTEMP:{$kiw_user['tenant_id']}:{$kiw_user['username']}");

                    if ($kiw_retry > 6) {


                        $kiw_cache->del("LOGIN_ATTEMP:{$kiw_user['tenant_id']}:{$kiw_user['username']}");

                        $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), status = 'suspend' WHERE username = '{$kiw_user['username']}' AND tenant_id = '{$kiw_user['tenant_id']}' LIMIT 1");


                    } else {


                        $kiw_cache->incr("LOGIN_ATTEMP:{$kiw_user['tenant_id']}:{$kiw_user['username']}");

                        $kiw_cache->expire("LOGIN_ATTEMP:{$kiw_user['tenant_id']}:{$kiw_user['username']}", 86400);


                    }


                }


                return array("Status" => "Error", "reply:Reply-Message" => $kiw_notification['error_wrong_credential']);


            } else {


                if ($kiw_temp['password_policy'] == "y" && $kiw_temp['password_attempts'] == "y") {

                    $kiw_cache->del("LOGIN_ATTEMP:{$kiw_user['tenant_id']}:{$kiw_user['username']}");

                }


            }


        }


    } else {


        // handle pms authentication

        $kiw_temp = $kiw_cache->get("PMS_DATA:{$kiw_user['tenant_id']}");

        if (empty($kiw_temp)){

            $kiw_temp = $kiw_db->query("SELECT * FROM kiwire_int_pms WHERE tenant_id = '{$kiw_user['tenant_id']}' LIMIT 1")[0];

            if (empty($kiw_temp)) $kiw_temp = array("dummy" => true);

            $kiw_cache->set("PMS_DATA:{$kiw_user['tenant_id']}", $kiw_temp, 1800);

        }


        if ($kiw_temp['enabled'] != "y"){


            return array("Status" => "Error", "reply:Reply-Message" => $kiw_notification['error_wrong_credential']);


        } else {


            // get the password supposed to be use by visitor

            if (!empty($kiw_user['fullname'])) {


                $kiw_multi_password = false;

                $kiw_fullnames = explode("|", $kiw_user['fullname']);


                foreach ($kiw_fullnames as $kiw_fullname) {


                    // make sure no spaces

                    $kiw_fullname = trim($kiw_fullname);


                    // set the password using the setting

                    switch ($kiw_temp['pass_mode']) {

                        case "0":
                            $kiw_pms_password = $kiw_temp['pass_predefined'];
                            break;

                        case "1":
                            $kiw_pms_password = $kiw_user['username'];
                            break;

                        case "2":

                            $kiw_pms_password = array_filter(explode(",", $kiw_fullname))[0];
                            $kiw_pms_password = trim($kiw_pms_password);

                            break;

                        case "3":

                            $kiw_pms_password = array_filter(explode(",", $kiw_fullname));
                            $kiw_pms_password = trim(end($kiw_pms_password));

                            break;

                        case "4":
                            $kiw_pms_password = $kiw_fullname;
                            break;

                        case "5":
                            $kiw_pms_password = $kiw_user['password'];
                            break;

                    }



                    $kiw_overwrite_pass = false;
                    if(isset($kiw_temp['use_first_login_only'])) {

                        if ($kiw_temp['use_first_login_only'] == "y" && in_array($kiw_temp['pass_mode'], array("2", "3", "4")) && !empty($kiw_user['date_password'])) {
                            $kiw_pms_password = $kiw_user['password'];
                            $kiw_overwrite_pass = true;
                            
                        }
                    }
        



                    if (in_array($kiw_temp['pass_mode'], array("2", "3", "4")) && $kiw_overwrite_pass == false) {


                        similar_text(strtolower($kiw_request['password']), strtolower($kiw_pms_password), $kiw_password_similar);


                        if ($kiw_password_similar >= $kiw_temp['pass_percentage']){


                            $kiw_multi_password = true;

                            break;


                        }


                    } elseif ($kiw_pms_password == sync_encrypt($kiw_request['password'])) {


                        $kiw_multi_password = true;

                        break;


                    }



                }


                if ($kiw_multi_password == false){

                    return array("Status" => "Error", "reply:Reply-Message" => $kiw_notification['error_wrong_credential']);

                }



            } else {


                return array("Status" => "Error", "reply:Reply-Message" => $kiw_notification['error_wrong_credential']);


            }


        }


    }


    if ($kiw_request['auth_type'] == "chap"){

        $kiw_result["control:Cleartext-Password"] = sync_decrypt($kiw_user['password']);

    } else $kiw_result["control:Cleartext-Password"] = $kiw_request['password'];


    return $kiw_result;


}


function check_zone_limit($kiw_db, $kiw_cache, $kiw_result, $kiw_notification, $kiw_user, $kiw_request){


    $kiw_temp = $kiw_cache->get("ZONE_DATA:{$kiw_user['tenant_id']}:{$kiw_request['zone']}");

    if (empty($kiw_temp)){

        $kiw_temp = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_zone WHERE tenant_id = '{$kiw_user['tenant_id']}' AND name = '{$kiw_request['zone']}' LIMIT 1")[0];

        if (empty($kiw_temp['journey'])) $kiw_temp['journey'] = "[none]";

        $kiw_cache->set("ZONE_DATA:{$kiw_user['tenant_id']}:{$kiw_request['zone']}", $kiw_temp, 1800);

    }


    if($kiw_temp['simultaneous'] > 0){

        $kiw_active_session = $kiw_db->query("SELECT COUNT(*) AS kcount FROM kiwire_active_session WHERE tenant_id = '{$kiw_user['tenant_id']}' AND zone = '{$kiw_request['zone']}' LIMIT 1")[0];

        if ($kiw_active_session["kcount"] >= $kiw_temp['simultaneous']){

            return array("Status" => "Error", "reply:Reply-Message" => $kiw_notification['error_zone_reached_limit']);

        }

    }


    return $kiw_result;

}


function check_allow_mac($kiw_db, $kiw_cache, $kiw_result, $kiw_notification, $kiw_user, $kiw_request){


    $kiw_temp = $kiw_cache->get("POLICIES_DATA:{$kiw_user['tenant_id']}");

    if (empty($kiw_temp)){

        $kiw_temp = $kiw_db->query("SELECT * FROM kiwire_policies WHERE tenant_id = '{$kiw_user['tenant_id']}' LIMIT 1")[0];

        if (empty($kiw_temp)) $kiw_temp = array("dummy" => true);

        $kiw_cache->set("POLICIES_DATA:{$kiw_user['tenant_id']}", $kiw_temp, 1800);

    }


    if (empty($kiw_temp['mac_max_register']) || $kiw_temp['mac_max_register'] > 10 || $kiw_temp['mac_max_register'] == 0)
        $kiw_temp['mac_max_register'] = "10";


    if ($kiw_temp["mac_security"] == "y") {

        $kiw_mac_list = array_filter(explode(",", $kiw_user['allowed_mac']));

        if (is_array($kiw_mac_list) && count($kiw_mac_list) >= $kiw_temp['mac_max_register']) {

            if (!in_array($kiw_request['mac_address'], $kiw_mac_list)) {

                return array("Status" => "Error", "reply:Reply-Message" => $kiw_notification['error_wrong_mac_address']);

            }

        }

    }


    return $kiw_result;


}


function check_register_mac($kiw_db, $kiw_cache, $kiw_result, $kiw_notification, $kiw_user, $kiw_request){


    $kiw_temp = $kiw_cache->get("POLICIES_DATA:{$kiw_user['tenant_id']}");

    if (empty($kiw_temp)){

        $kiw_temp = $kiw_db->query("SELECT * FROM kiwire_policies WHERE tenant_id = '{$kiw_user['tenant_id']}' LIMIT 1")[0];

        if (empty($kiw_temp)) $kiw_temp = array("dummy" => true);

        $kiw_cache->set("POLICIES_DATA:{$kiw_user['tenant_id']}", $kiw_temp, 1800);

    }


    if ($kiw_temp["mac_auto_register"] == "y") {


        // explode to make sure there is no empty array

        if (strlen($kiw_user['allowed_mac']) > 0) {

            $kiw_total_mac = array_filter(explode(",", $kiw_user['allowed_mac']));

        } else {

            $kiw_total_mac = array();

        }


        // hard code max mac address to 10 only

        if (empty($kiw_temp['mac_max_register']) || $kiw_temp['mac_max_register'] > 10 || $kiw_temp['mac_max_register'] == 0)
            $kiw_temp['mac_max_register'] = "10";


        if (count($kiw_total_mac) < $kiw_temp['mac_max_register']) {


            // if not i array then add to the list

            if (strlen($kiw_request['mac_address']) > 0) {


                if (!in_array($kiw_request['mac_address'], $kiw_total_mac)) {


                    $kiw_total_mac[] = $kiw_request['mac_address'];

                    $kiw_total_mac = implode(",", $kiw_total_mac);

                    $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), allowed_mac = '{$kiw_total_mac}' WHERE tenant_id = '{$kiw_user['tenant_id']}' AND username = '{$kiw_user['username']}' LIMIT 1");


                }


            }


        }


    }


    return $kiw_result;


}


function check_allow_zone($kiw_db, $kiw_cache, $kiw_result, $kiw_notification, $kiw_user, $kiw_request){


    if (!empty($kiw_user['allowed_zone']) && $kiw_user['allowed_zone'] != "none") {


        // this is allowable zone setting

        $kiw_temp = $kiw_cache->get("ZONE_ALLOWED_DATA:{$kiw_user['tenant_id']}:{$kiw_user['allowed_zone']}");

        if (empty($kiw_temp)) {


            $kiw_temp = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_allowed_zone WHERE tenant_id = '{$kiw_user['tenant_id']}' AND name = '{$kiw_user['allowed_zone']}' LIMIT 1")[0];

            if (empty($kiw_temp)) $kiw_temp = array("dummy" => true);

            $kiw_cache->set("ZONE_ALLOWED_DATA:{$kiw_user['tenant_id']}:{$kiw_user['allowed_zone']}", $kiw_temp, 1800);


        }


        // this is to compare the zone that we received from request, meant the current user zone
        // against the allowable zone setup in system

        if (!in_array($kiw_request['zone'], explode(",", $kiw_temp['zone']))) {

            return array("Status" => "Error", "reply:Reply-Message" => $kiw_notification['error_zone_restriction']);

        }


    }

    return $kiw_result;


}


function check_allow_credit($kiw_db, $kiw_cache, $kiw_result, $kiw_notification, $kiw_user, $kiw_request, $kiw_profile){


    $kiw_temp = $kiw_cache->get("AUTO_RESET_DATA:{$kiw_user['tenant_id']}:{$kiw_user['profile_subs']}");

    if (empty($kiw_temp)){


        $kiw_temp = $kiw_db->query("SELECT * FROM kiwire_auto_reset WHERE tenant_id = '{$kiw_user['tenant_id']}' AND profile = '{$kiw_user['profile_subs']}' LIMIT 1")[0];

        if (empty($kiw_temp)) $kiw_temp = array("dummy" => true);

        $kiw_cache->set("AUTO_RESET_DATA:{$kiw_user['tenant_id']}:{$kiw_user['profile_subs']}", $kiw_temp, 1800);


    }


    if ($kiw_result['control:Access-Period'] > 0){


        if (!empty($kiw_user['profile_cus'])){

            $kiw_user['profile_cus'] = json_decode($kiw_user['profile_cus'], true);

            if ($kiw_user['profile_cus']['time'] > 0){

                $kiw_result['control:Access-Period'] += $kiw_user['profile_cus']['time'];

            }

        }


        $kiw_time = time();


        if (strtotime($kiw_user['date_activate']) > 0 && ($kiw_time - strtotime($kiw_user['date_activate'])) >= $kiw_result['control:Access-Period']){


            if ($kiw_temp['exec_when'] == "ot") {


                // check if grace period need to be imposed

                if ($kiw_temp['grace'] > 0){


                    $kiw_grace_time = time() - strtotime($kiw_user['date_last_logout']);

                    $kiw_temp['grace'] = $kiw_temp['grace'] * 60;


                    if ($kiw_grace_time < $kiw_temp['grace']){


                        $kiw_grace_remaining = (int)(($kiw_temp['grace'] - $kiw_grace_time) / 60);

                        $kiw_notification['error_ot_reset_grace'] = str_replace("{{remaining_minute}}", $kiw_grace_remaining, $kiw_notification['error_ot_reset_grace']);

                        return array("Status" => "Error", "reply:Reply-Message" => $kiw_notification['error_ot_reset_grace']);


                    }


                }


                $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), date_activate = NULL, quota_in = 0, quota_out = 0, session_time = 0, profile_curr = profile_subs WHERE username = '{$kiw_user['username']}' AND tenant_id = '{$kiw_user['tenant_id']}' LIMIT 1");

                $kiw_result['reply:Session-Timeout'] = $kiw_result['control:Access-Period'];

                return $kiw_result;


            } else {


                if (strlen($kiw_profile['advance']) > 0){


                    // load new profile data and do coa

                    $kiw_temp = $kiw_cache->get("PROFILE_DATA:{$kiw_user['tenant_id']}:{$kiw_profile['advance']}");

                    if (empty($kiw_temp)){


                        $kiw_temp = $kiw_db->query("SELECT * FROM kiwire_profiles WHERE tenant_id = '{$kiw_user['tenant_id']}' AND name = '{$kiw_profile['advance']}' LIMIT 1")[0];

                        if (empty($kiw_temp)) $kiw_temp = array("dummy" => true);

                        $kiw_cache->set("PROFILE_DATA:{$kiw_user['tenant_id']}:{$kiw_profile['advance']}", $kiw_temp, 1800);


                    }


                    $kiw_temp = json_decode($kiw_temp['attribute'], true);

                    $kiw_profile_key = array_keys($kiw_temp);


                    foreach ($kiw_temp as $kiw_key => $kiw_value){

                        $kiw_result[$kiw_key] = $kiw_value;

                    }

                    unset($kiw_temp);


                    foreach ($kiw_result as $kiw_key => $kiw_value){

                        if (!in_array($kiw_key, $kiw_profile_key)){

                            if ($kiw_key !== "control:Cleartext-Password") {

                                unset($kiw_result[$kiw_key]);

                            }

                        }

                    }


                    coa_user($kiw_db, $kiw_cache, $kiw_user['tenant_id'], $kiw_user['username'], $kiw_profile['advance']);


                } else {

                    return array("Status" => "Error", "reply:Reply-Message" => $kiw_notification['error_reached_time_limit']);

                }


            }


        }


        $kiw_activated = strtotime($kiw_user['date_activate']);

        if ($kiw_activated > 0) {

            $kiw_result['reply:Session-Timeout'] = $kiw_result['control:Access-Period'] - ($kiw_time - $kiw_activated);

        } else {

            $kiw_result['reply:Session-Timeout'] = $kiw_result['control:Access-Period'];

        }


    } elseif ($kiw_result['control:Max-All-Session'] > 0){


        if (!empty($kiw_user['profile_cus'])){

            $kiw_user['profile_cus'] = json_decode($kiw_user['profile_cus'], true);

            if ($kiw_user['profile_cus']['time'] > 0){

                $kiw_result['control:Max-All-Session'] += $kiw_user['profile_cus']['time'];

            }

        }


        if ($kiw_user['session_time'] >= $kiw_result['control:Max-All-Session']){


            if ($kiw_temp['exec_when'] == "ot") {


                // check if grace period need to be imposed

                if ($kiw_temp['grace'] > 0){


                    $kiw_grace_time = time() - strtotime($kiw_user['date_last_logout']);

                    $kiw_temp['grace'] = $kiw_temp['grace'] * 60;


                    if ($kiw_grace_time < $kiw_temp['grace']){


                        $kiw_grace_remaining = (int)(($kiw_temp['grace'] - $kiw_grace_time) / 60);

                        $kiw_notification['error_ot_reset_grace'] = str_replace("{{remaining_minute}}", $kiw_grace_remaining, $kiw_notification['error_ot_reset_grace']);

                        return array("Status" => "Error", "reply:Reply-Message" => $kiw_notification['error_ot_reset_grace']);


                    }


                }


                $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), date_activate = NULL, quota_in = 0, quota_out = 0, session_time = 0, profile_curr = profile_subs WHERE username = '{$kiw_user['username']}' AND tenant_id = '{$kiw_user['tenant_id']}' LIMIT 1");

                $kiw_result['reply:Session-Timeout'] = $kiw_result['control:Max-All-Session'];

                return $kiw_result;


            } else {


                if (strlen($kiw_profile['advance']) > 0){


                    // load new profile data and do coa

                    $kiw_temp = $kiw_cache->get("PROFILE_DATA:{$kiw_user['tenant_id']}:{$kiw_profile['advance']}");

                    if (empty($kiw_temp)){


                        $kiw_temp = $kiw_db->query("SELECT * FROM kiwire_profiles WHERE tenant_id = '{$kiw_user['tenant_id']}' AND name = '{$kiw_profile['advance']}' LIMIT 1")[0];

                        if (empty($kiw_temp)) $kiw_temp = array("dummy" => true);

                        $kiw_cache->set("PROFILE_DATA:{$kiw_user['tenant_id']}:{$kiw_profile['advance']}", $kiw_temp, 1800);


                    }


                    $kiw_temp = json_decode($kiw_temp['attribute'], true);

                    $kiw_profile_key = array_keys($kiw_temp);


                    foreach ($kiw_temp as $kiw_key => $kiw_value){

                        $kiw_result[$kiw_key] = $kiw_value;

                    }

                    unset($kiw_temp);


                    foreach ($kiw_result as $kiw_key => $kiw_value){

                        if (!in_array($kiw_key, $kiw_profile_key)){

                            if ($kiw_key !== "control:Cleartext-Password") {

                                unset($kiw_result[$kiw_key]);

                            }

                        }

                    }


                    coa_user($kiw_db, $kiw_cache, $kiw_user['tenant_id'], $kiw_user['username'], $kiw_profile['advance']);



                } else {

                    return array("Status" => "Error", "reply:Reply-Message" => $kiw_notification['error_reached_time_limit']);

                }

            }


        }

        $kiw_result['reply:Session-Timeout'] = $kiw_result['control:Max-All-Session'] - $kiw_user['session_time'];


    }


    return $kiw_result;


}


function check_allow_quota($kiw_db, $kiw_cache, $kiw_result, $kiw_notification, $kiw_user, $kiw_request, $kiw_profile){


    $kiw_temp = $kiw_cache->get("AUTO_RESET_DATA:{$kiw_user['tenant_id']}:{$kiw_user['profile_subs']}");

    if (empty($kiw_temp)){

        $kiw_temp = $kiw_db->query("SELECT * FROM kiwire_auto_reset WHERE tenant_id = '{$kiw_user['tenant_id']}' AND profile = '{$kiw_user['profile_subs']}' AND exec_when = 'ot' LIMIT 1")[0];

        if (empty($kiw_temp)) $kiw_temp = array("dummy" => true);

        $kiw_cache->set("AUTO_RESET_DATA:{$kiw_user['tenant_id']}:{$kiw_user['profile_subs']}", $kiw_temp, 1800);

    }


    if (isset($kiw_result['control:Kiwire-Total-Quota']) && $kiw_result['control:Kiwire-Total-Quota'] > 0){


        $kiw_allow_usage = $kiw_result['control:Kiwire-Total-Quota'] * (1024 * 1024);

        $kiw_total_usage = $kiw_user['quota_in'] + $kiw_user['quota_out'];


        if (!empty($kiw_user['profile_cus'])){

            $kiw_user['profile_cus'] = json_decode($kiw_user['profile_cus'], true);

            if ($kiw_user['profile_cus']['quota'] > 0){

                $kiw_allow_usage += $kiw_user['profile_cus']['quota'];

            }

        }


        // check if this is want client wanted

        if ($kiw_total_usage >= $kiw_allow_usage){


            if ($kiw_temp['profile'] == "ot"){



                // reset user if profile set to be reset on-time

                $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), date_activate = NULL, quota_in = 0, quota_out = 0, session_time = 0, profile_curr = profile_subs WHERE username = '{$kiw_user['username']}' AND tenant_id = '{$kiw_user['tenant_id']}' LIMIT 1");


                // set the quota back to 100%

                $kiw_result['control:Kiwire-Total-Quota'] = $kiw_allow_usage;



            } else {


                if (strlen($kiw_profile['advance']) > 0){


                    // load new profile data and do coa

                    $kiw_temp = $kiw_cache->get("PROFILE_DATA:{$kiw_user['tenant_id']}:{$kiw_profile['advance']}");

                    if (empty($kiw_temp)){


                        $kiw_temp = $kiw_db->query("SELECT * FROM kiwire_profiles WHERE tenant_id = '{$kiw_user['tenant_id']}' AND name = '{$kiw_profile['advance']}' LIMIT 1")[0];

                        if (empty($kiw_temp)) $kiw_temp = array("dummy" => true);

                        $kiw_cache->set("PROFILE_DATA:{$kiw_user['tenant_id']}:{$kiw_profile['advance']}", $kiw_temp, 1800);


                    }


                    $kiw_temp = json_decode($kiw_temp['attribute'], true);

                    $kiw_profile_key = array_keys($kiw_temp);


                    foreach ($kiw_temp as $kiw_key => $kiw_value){

                        $kiw_result[$kiw_key] = $kiw_value;

                    }

                    unset($kiw_temp);


                    foreach ($kiw_result as $kiw_key => $kiw_value){

                        if (!in_array($kiw_key, $kiw_profile_key)){

                            if ($kiw_key !== "control:Cleartext-Password") {

                                unset($kiw_result[$kiw_key]);

                            }

                        }

                    }


                    coa_user($kiw_db, $kiw_cache, $kiw_user['tenant_id'], $kiw_user['username'], $kiw_profile['advance']);



                } else {


                    return array("Status" => "Error", "reply:Reply-Message" => $kiw_notification['error_reached_quota_limit']);


                }


            }


        } else {


            $kiw_result['control:Kiwire-Total-Quota'] = $kiw_allow_usage - $kiw_total_usage;


        }


    }


    return $kiw_result;


}


function check_concurrent_user($kiw_db, $kiw_cache, $kiw_result, $kiw_notification, $kiw_user){


    $kiw_temp = $kiw_cache->get("CLOUD_TENANT:{$kiw_user['tenant_id']}");


    if (empty($kiw_temp)){

        $kiw_temp = $kiw_db->query("SELECT * FROM kiwire_clouds WHERE tenant_id = '{$kiw_user['tenant_id']}' LIMIT 1")[0];

        if (empty($kiw_temp)) $kiw_temp = array("dummy" => true);

        $kiw_cache->set("CLOUD_TENANT:{$kiw_user['tenant_id']}", $kiw_temp, 1800);

    }


    $kiw_active_session = $kiw_db->query("SELECT COUNT(*) AS kcount FROM kiwire_active_session WHERE tenant_id = '{$kiw_user['tenant_id']}'");

    if ($kiw_active_session[0]['kcount'] >= $kiw_temp['concurrent_user']){


        // return array("Status" => "Error", "reply:Reply-Message" => $kiw_notification['error_max_simultaneous_use']);
        return array("Status" => "Error", "reply:Reply-Message" => "You have reached max concurrent user for this tenant.");



    }

    return $kiw_result;

}



function check_allow_simultaneous($kiw_db, $kiw_cache, $kiw_result, $kiw_notification, $kiw_user){


    $kiw_temp = $kiw_cache->get("POLICIES_DATA:{$kiw_user['tenant_id']}");

    if (empty($kiw_temp)){

        $kiw_temp = $kiw_db->query("SELECT * FROM kiwire_policies WHERE tenant_id = '{$kiw_user['tenant_id']}' LIMIT 1")[0];

        if (empty($kiw_temp)) $kiw_temp = array("dummy" => true);

        $kiw_cache->set("POLICIES_DATA:{$kiw_user['tenant_id']}", $kiw_temp, 1800);

    }


    $kiw_active_session = $kiw_db->query("SELECT COUNT(*) AS kcount FROM kiwire_active_session WHERE tenant_id = '{$kiw_user['tenant_id']}' AND username = '{$kiw_user['username']}'")[0];


    if ($kiw_active_session['kcount'] >= $kiw_result['control:Simultaneous-Use']){

        if ($kiw_temp['kick_on_simultaneous'] != "y") {


            return array("Status" => "Error", "reply:Reply-Message" => $kiw_notification['error_max_simultaneous_use']);


        } else {

            // disconnect all device

            disconnect_user($kiw_db, $kiw_cache, $kiw_user['tenant_id'], $kiw_user['username']);

        }

    }

    return $kiw_result;

}


function send_pms_payment($kiw_db, $kiw_cache, $kiw_result, $kiw_notification, $kiw_user, $kiw_request, $kiw_profile){


    if ($kiw_user['integration'] == "pms" && (empty($kiw_user['date_activate']) || strtotime($kiw_user['date_activate']) == 0)){


        $kiw_user['fullname'] = explode("|", $kiw_user['fullname'])[0];


        if ($kiw_profile['price'] > 0) {

            $kiw_db->query("INSERT INTO kiwire_int_pms_payment(id, updated_date, tenant_id, login_date, post_date, room, status, amount, profile, name) VALUE (NULL, NOW(), '{$kiw_user['tenant_id']}', NOW(), NULL, '{$kiw_user['username']}', 'new', {$kiw_profile['price']}, '{$kiw_profile['name']}', '{$kiw_user['fullname']}')");

        }


    }


    return $kiw_result;


}


function generate_invoice($kiw_db, $kiw_cache, $kiw_result, $kiw_notification, $kiw_user, $kiw_request){

    // check if first login then decide if need to generate invoice


    return $kiw_result;

}


function otp_change_password($kiw_db, $kiw_cache, $kiw_result, $kiw_notification, $kiw_user, $kiw_request){

   if ($kiw_user['integration'] == "sms-otp"){

       $kiw_temp = substr(md5(time() . $kiw_user['tenant_id']), 0, 12);

       $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), password = '{$kiw_temp}' WHERE username = '{$kiw_user['username']}' AND tenant_id = '{$kiw_user['tenant_id']}' LIMIT 1");

   }


    return $kiw_result;

}



function reporting_process($kiw_db, $kiw_cache, $kiw_result, $kiw_notification, $kiw_user, $kiw_request){


    // get the midnight first

    $kiw_timezone = $kiw_cache->get("CLOUD_DATA:{$kiw_user['tenant_id']}");

    if (empty($kiw_timezone)){


        $kiw_timezone = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_clouds WHERE tenant_id = '{$kiw_user['tenant_id']}' LIMIT 1")[0];

        if (empty($kiw_timezone)) $kiw_timezone = array("dummy" => true);

        $kiw_cache->set("CLOUD_DATA:{$kiw_user['tenant_id']}", $kiw_timezone, 1800);


    }


    $kiw_timezone = $kiw_timezone['timezone'];

    if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


    $kiw_midnight = new DateTime(date("Y-m-d 00:00:00"), new DateTimeZone($kiw_timezone));

    $kiw_midnight->setTimezone(new DateTimeZone("UTC"));

    $kiw_midnight = $kiw_midnight->getTimestamp();


    $kiw_time = date("YmdH");

    $kiw_activate = "";


    // update the activate date if this is first time login

    if ( $kiw_user['date_activate'] == '0000-00-00 00:00:00' || !strtotime($kiw_user['date_activate'])  || is_null($kiw_user['date_activate']) ) $kiw_activate = ", date_activate = NOW()";


    // update date last login time to now

    $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), date_last_login = NOW(), login = login + 1 {$kiw_activate} WHERE username = '{$kiw_user['username']}' AND tenant_id = '{$kiw_user['tenant_id']}' LIMIT 1");



    // if not activate before, increase the count for first time login

    if (strtotime($kiw_user['date_last_login']) > 0) {

        $kiw_cache->incr("REPORT_LOGIN_RETURN_ACCOUNT:{$kiw_time}:{$kiw_request['tenant_id']}:{$kiw_request['zone']}");

    } else {

        $kiw_cache->incr("REPORT_LOGIN_FIRST_ACCOUNT:{$kiw_time}:{$kiw_request['tenant_id']}:{$kiw_request['zone']}");

    }


    if (strtotime($kiw_user['date_last_login']) < $kiw_midnight){


        $kiw_cache->incr("REPORT_LOGIN_UNIQUE_ACCOUNT:{$kiw_time}:{$kiw_request['tenant_id']}:{$kiw_request['zone']}");


        // unique login in profile and zone are based on account daily uniqueness

        $kiw_cache->incr("REPORT_ULOGIN_PROFILE:{$kiw_time}:{$kiw_request['tenant_id']}:{$kiw_request['zone']}:{$kiw_user['profile_curr']}");
        $kiw_cache->incr("REPORT_ULOGIN_ZONE:{$kiw_time}:{$kiw_request['tenant_id']}:{$kiw_request['zone']}");


    }


    // check kiwire policy for mac authentication

    $kiw_policies = $kiw_cache->get("POLICIES_DATA:{$kiw_request['tenant_id']}");

    if (empty($kiw_policies)) {


        $kiw_policies = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_policies WHERE tenant_id = '{$kiw_request['tenant_id']}' LIMIT 1")[0];

        if (empty($kiw_policies)) $kiw_policies = array("dummy" => true);

        $kiw_cache->set("POLICIES_DATA:{$kiw_request['tenant_id']}", $kiw_policies, 1800);


    }



    // check if return or new device

    $kiw_previous_login = $kiw_db->query("SELECT * FROM kiwire_device_history WHERE mac_address = '{$kiw_request['mac_address']}' AND tenant_id = '{$kiw_request['tenant_id']}' LIMIT 1")[0];


    if (strtotime($kiw_previous_login['updated_date']) > 0){


        $kiw_cache->incr("REPORT_LOGIN_RETURN_DEVICE:{$kiw_time}:{$kiw_request['tenant_id']}:{$kiw_request['zone']}");


        if (strtotime($kiw_previous_login['updated_date']) < $kiw_midnight) {

            $kiw_cache->incr("REPORT_LOGIN_UNIQUE_DEVICE:{$kiw_time}:{$kiw_request['tenant_id']}:{$kiw_request['zone']}");

        }


        if (empty($kiw_previous_login['details'])){


            // update the device details if not available. for compatibility

            $kiw_device['system'] = $kiw_request['system'];
            $kiw_device['class']  = $kiw_request['class'];
            $kiw_device['brand']  = $kiw_request['brand'];
            $kiw_device['model']  = $kiw_request['model'];

            $kiw_device = $kiw_db->escape(json_encode($kiw_device));

            $kiw_device = ", details = '{$kiw_device}'";


        }


        if ($kiw_policies['mac_auto_login'] == "y"){

            // if ((int)((time() - strtotime($kiw_previous_login['last_auto'])) / (60 * 60 * 24)) > $kiw_policies['mac_auto_login_days']) {

                $kiw_last_login = ", last_auto = NOW()";

            // }

        }


        $kiw_db->query("UPDATE kiwire_device_history SET updated_date = NOW(), last_zone = '{$kiw_request['zone']}', last_account = '{$kiw_request['username']}'{$kiw_last_login}{$kiw_device} WHERE mac_address = '{$kiw_request['mac_address']}' AND tenant_id = '{$kiw_request['tenant_id']}' LIMIT 1");


    } else {


        $kiw_device['system'] = $kiw_request['system'];
        $kiw_device['class']  = $kiw_request['class'];
        $kiw_device['brand']  = $kiw_request['brand'];
        $kiw_device['model']  = $kiw_request['model'];


        if (!empty($kiw_request['class'])) {


            $kiw_device = $kiw_db->escape(json_encode($kiw_device));

            $kiw_db->query("INSERT INTO kiwire_device_history (tenant_id, updated_date, mac_address, last_account, login_count, last_auto, last_zone, details) VALUE ('{$kiw_request['tenant_id']}', NOW(), '{$kiw_request['mac_address']}', '{$kiw_request['username']}', 1, NOW(), '{$kiw_request['zone']}', '{$kiw_device}')");


        }


        $kiw_cache->incr("REPORT_LOGIN_FIRST_DEVICE:{$kiw_time}:{$kiw_request['tenant_id']}:{$kiw_request['zone']}");
        $kiw_cache->incr("REPORT_LOGIN_UNIQUE_DEVICE:{$kiw_time}:{$kiw_request['tenant_id']}:{$kiw_request['zone']}");


    }


    return $kiw_result;


}



