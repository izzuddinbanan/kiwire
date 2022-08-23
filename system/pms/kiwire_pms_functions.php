<?php


// include the main setting file
require_once dirname(__FILE__, 3) . "/server/admin/includes/include_config.php";
require_once dirname(__FILE__, 3) . "/server/admin/includes/include_general.php";


// include radius function to disconnect device if available

require_once dirname(__FILE__, 3) . "/server/user/includes/include_radius.php";


function pms_logger($kiw_message, $kiw_pms_type = "", $kiw_tenant = "default"){


    $kiw_path = "/var/www/kiwire/logs/pms/{$kiw_pms_type}/{$kiw_tenant}/";

    if (file_exists($kiw_path) == false) mkdir($kiw_path, 0755, true);

    @file_put_contents($kiw_path . "kiwire-pms-" . date("YmdH") . ".log", date("Y-m-d H:i:s") . " :: " . $kiw_message . "\n", FILE_APPEND);


}


function pms_password($length = 6, $strength = 0){


    $consonant = array("b", "c", "d", "f", "g", "h", "j", "k", "l", "m", "n", "p", "r", "s", "t", "v", "w", "x", "y", "z");

    $vocal = array("a", "e", "i", "o", "u");


    $password = "";

    srand((double)microtime() * 1000000);

    $max = $length / 2;


    for ($i = 1; $i <= $max; $i++) {

        $password .= $consonant[rand(0, 19)];
        $password .= $vocal[rand(0, 4)];

    }


    return $password;


}


function pms_write_stream($kiw_stream, $kiw_data, $kiw_type = "", $kiw_tenant = "default"){


    pms_logger("TX: {$kiw_data}", $kiw_type, $kiw_tenant);

    fwrite($kiw_stream, $kiw_data);


}


function pms_check_data($kiw_data, $kiw_type = "", $kiw_tenant = "default"){


    foreach (array("room_no", "guest_name", "guest_first", "guest_last", "guest_vip", "password") as $kiw_field){

        if (!isset($kiw_data[$kiw_field])){


            pms_logger("Missing important variable [{$kiw_data[$kiw_field]}]", $kiw_type, $kiw_tenant);

            return false;


        }

    }


    return true;


}



function pms_check_in($kiw_db, $kiw_type, $kiw_tenant, $kiw_data, $kiw_vip_match = "", $kiw_vips = array()){


    if (pms_check_data($kiw_data, $kiw_type, $kiw_tenant) == true){


        $kiw_vip_overwrite = "";


        // check for vip profile and price

        if (!empty($kiw_vips) && !empty($kiw_vip_match)){

            if (isset($kiw_data[$kiw_vip_match])) {

                foreach ($kiw_vips as $kiw_vip) {

                    if ($kiw_data[$kiw_vip_match] == $kiw_vip['code']){


                        if (empty($kiw_vip['price']) && $kiw_vip['price'] != 'none')
                            $kiw_vip_overwrite = ", price = '{$kiw_vip['price']}'";


                        if (empty($kiw_vip['profile']) && $kiw_vip['profile'] != 'none')
                            $kiw_vip_overwrite = ", profile_curr = '{$kiw_vip['profile']}'";


                    }

                }

            }

        }


        // get current status to check if sharer check-in. if yes, the add new password

        $kiw_current = $kiw_db->query("SELECT password, fullname, status FROM kiwire_account_auth WHERE username = '{$kiw_data['room_no']}' AND tenant_id = '{$kiw_tenant}' LIMIT 1");

        if ($kiw_current) $kiw_current = $kiw_current->fetch_all(MYSQLI_ASSOC)[0];
        else return false;


        if (empty($kiw_current)){


            pms_logger("ERROR: Invalid room number [ {$kiw_data['room_no']} ]", "micros", $kiw_tenant);

            return false;


        }


        if ($kiw_current['status'] == "active" && $kiw_data['guest_sharer'] == "Y"){

            $kiw_data['guest_name'] = $kiw_current['fullname'] . " | " . $kiw_data['guest_name'];

        }


        $kiw_data['password'] = sync_encrypt($kiw_data['password']);


        $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), status = 'active', password = '{$kiw_data['password']}', fullname = '{$kiw_data['guest_name']}'{$kiw_vip_overwrite} WHERE username = '{$kiw_data['room_no']}' AND tenant_id = '{$kiw_tenant}' LIMIT 1");


        // update pms transaction list table as well

        $kiw_db->query("INSERT INTO kiwire_int_pms_transaction(updated_date, check_in_date, check_out_date, tenant_id, room, first_name, last_name, vip_code, status, printed) VALUE (NOW(), NOW(), NULL, '{$kiw_tenant}', '{$kiw_data['room_no']}', '{$kiw_data['guest_first']}', '{$kiw_data['guest_last']}', '{$kiw_data['guest_vip']}', 'check-in', 'n')");


        return true;


    } else return false;


}


function pms_check_out($kiw_db, $kiw_type, $kiw_tenant, $kiw_data){


    $kiw_random_password = sync_encrypt(pms_password(10));

    $kiw_temp = $kiw_db->query("SELECT reset_acc_and_date_password FROM kiwire_clouds WHERE tenant_id = '{$kiw_tenant}' LIMIT 1");

    if ($kiw_temp) $kiw_temp = $kiw_temp->fetch_all(MYSQLI_ASSOC)[0];

    $date_password = "";
    if($kiw_temp['reset_acc_and_date_password'] == "y") {
        $date_password = " ,date_password = NULL ";
    }


    $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), fullname = '{$kiw_data['room_no']}', status = 'suspend', profile_curr = profile_subs, password = '{$kiw_random_password}', date_activate = NULL, quota_in = 0, quota_out = 0, session_time = 0 {$date_password} WHERE username = '{$kiw_data['room_no']}' AND tenant_id = '{$kiw_tenant}' LIMIT 1");


    // update pms transaction data as well

    $kiw_db->query("UPDATE kiwire_int_pms_transaction SET updated_date = NOW(), check_out_date = NOW(), status = 'check-out' WHERE room = '{$kiw_data['room_no']}' AND (status = 'check-in' OR status = 'move-in') AND tenant_id = '{$kiw_tenant}'");


    // logout the user from network

    if (method_exists($kiw_db, "query_first") == true) {

        $kiw_temp = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_active_session WHERE tenant_id = '{$kiw_tenant}' AND username = '{$kiw_data['room_no']}'");

    } else {

        $kiw_temp = $kiw_db->query("SELECT COUNT(*) AS kcount FROM kiwire_active_session WHERE tenant_id = '{$kiw_tenant}' AND username = '{$kiw_data['room_no']}'");

        if ($kiw_temp) $kiw_temp = $kiw_temp->fetch_all(MYSQLI_ASSOC)[0];

    }


    if ($kiw_temp['kcount'] > 0) {


        $omy_curl = curl_init("http://127.0.0.1:9956/");

        curl_setopt($omy_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($omy_curl, CURLOPT_POST, true);
        curl_setopt($omy_curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($omy_curl, CURLOPT_POSTFIELDS, http_build_query(array("action" => "disconnect_user", "tenant_id" => $kiw_tenant, "username" => $kiw_data['room_no'])));
        curl_setopt($omy_curl, CURLOPT_TIMEOUT, 5);
        curl_setopt($omy_curl, CURLOPT_CONNECTTIMEOUT, 5);

        curl_exec($omy_curl);
        curl_close($omy_curl);

        unset($omy_curl);


    }


    return true;


}


function pms_update_info($kiw_db, $kiw_type, $kiw_tenant, $kiw_data){


    $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), password = '{$kiw_data['password']}', fullname = '{$kiw_data['guest_name']}' WHERE status = 'active' AND username = '{$kiw_data['room_no']}' AND tenant_id = '{$kiw_tenant}' LIMIT 1");

    return true;


}


function pms_move_room($kiw_db, $kiw_type, $kiw_tenant, $kiw_previous, $kiw_new_room){


    $kiw_random_password = sync_encrypt(pms_password(10));


    // get latest data to be moved?

    $kiw_current = $kiw_db->query("SELECT password, fullname, quota_out, quota_in, session_time, date_activate, profile_curr, price FROM kiwire_account_auth WHERE username = '{$kiw_previous}' AND tenant_id = '{$kiw_tenant}' LIMIT 1");

    if ($kiw_current) $kiw_current = $kiw_current->fetch_all(MYSQLI_ASSOC)[0];


    $kiw_current_pms = $kiw_db->query("SELECT * FROM kiwire_int_pms_transaction WHERE tenant_id = '{$kiw_tenant}' AND (status = 'check-in' OR status = 'move-in') AND room = '{$kiw_previous}' ORDER BY id DESC LIMIT 1");

    if ($kiw_current_pms) $kiw_current_pms = $kiw_current_pms->fetch_all(MYSQLI_ASSOC)[0];


    if (empty($kiw_current) || empty($kiw_current_pms)){

        return false;

    }


    // perform check out on the previous room

    $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), status = 'suspend', fullname = '{$kiw_previous}', password = '{$kiw_random_password}', profile_curr = profile_subs, date_activate = NULL, quota_in = 0, quota_out = 0, session_time = 0 WHERE username = '{$kiw_previous}' AND tenant_id = '{$kiw_tenant}' LIMIT 1");

    $kiw_db->query("UPDATE kiwire_int_pms_transaction SET updated_date = NOW(), check_out_date = NOW(), status = 'move-out' WHERE room = '{$kiw_previous}' AND (status = 'check-in' OR status = 'move-in') AND tenant_id = '{$kiw_tenant}'");


    // disconnect user from previous room access

    if (method_exists($kiw_db, "query_first")) {

        $kiw_temp = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_active_session WHERE tenant_id = '{$kiw_tenant}' AND username = '{$kiw_previous}'");

    } else {

        $kiw_temp = $kiw_db->query("SELECT COUNT(*) AS kcount FROM kiwire_active_session WHERE tenant_id = '{$kiw_tenant}' AND username = '{$kiw_previous}'");

        if ($kiw_temp) $kiw_temp = $kiw_temp->fetch_all(MYSQLI_ASSOC)[0];

    }


    if ($kiw_temp['kcount'] > 0) {


        $omy_curl = curl_init("http://127.0.0.1:9956/");

        curl_setopt($omy_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($omy_curl, CURLOPT_POST, true);
        curl_setopt($omy_curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($omy_curl, CURLOPT_POSTFIELDS, http_build_query(array("action" => "disconnect_user", "tenant_id" => $kiw_tenant, "username" => $kiw_previous)));
        curl_setopt($omy_curl, CURLOPT_TIMEOUT, 5);
        curl_setopt($omy_curl, CURLOPT_CONNECTTIMEOUT, 5);

        curl_exec($omy_curl);
        curl_close($omy_curl);

        unset($omy_curl);


    }


    if (empty($kiw_current['guest_name'])) $kiw_current['guest_name'] = $kiw_current['fullname'];


    if (empty($kiw_current['date_activate'])) {

        $kiw_current['date_activate'] = ", date_activate = NULL";

    } else $kiw_current['date_activate'] = ", date_activate = '{$kiw_current['date_activate']}'";



    // then perform check in on new room

    $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), status = 'active', password = '{$kiw_current['password']}', fullname = '{$kiw_current['guest_name']}', price = '{$kiw_current['price']}', profile_curr = '{$kiw_current['profile_curr']}', quota_out = '{$kiw_current['quota_out']}', quota_in = '{$kiw_current['quota_in']}', session_time = '{$kiw_current['session_time']}'{$kiw_current['date_activate']} WHERE username = '{$kiw_new_room}' AND tenant_id = '{$kiw_tenant}' LIMIT 1");


    // update pms transaction data as well

    $kiw_db->query("INSERT INTO kiwire_int_pms_transaction(updated_date, check_in_date, check_out_date, tenant_id, room, first_name, last_name, vip_code, status, printed) VALUE (NOW(), NOW(), NULL, '{$kiw_tenant}', '{$kiw_new_room}', '{$kiw_current_pms['first_name']}', '{$kiw_current_pms['last_name']}', '{$kiw_current_pms['vip_code']}', 'move-in', 'n')");


    return true;


}
