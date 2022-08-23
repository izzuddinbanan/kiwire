<?php


if (php_sapi_name() != "cli") die();


$kiw_tenant = dirname(__FILE__);


if (strpos($kiw_tenant, "server/custom") == false) {

    kiw_print_response(false, "Invalid tenant info has been provided");

}


$kiw_tenant = array_filter(explode("/", $kiw_tenant));


foreach ($kiw_tenant as $kiw_index => $kiw_tenant_) {

    if ($kiw_tenant_ == "custom") {

        $kiw_tenant = $kiw_tenant[$kiw_index + 1];

        break;

    }

}

unset($kiw_index);

unset($kiw_tenant_);


require_once dirname(__FILE__, 5) . "/admin/includes/include_config.php";

require_once dirname(__FILE__, 6) . "/system/pms/kiwire_pms_functions.php";


// start connections to db and cache

$kiw_cache = new Redis();

$kiw_cache->connect(SYNC_REDIS_HOST, SYNC_REDIS_PORT);

$kiw_cache->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);


$kiw_db = new mysqli(SYNC_DB1_HOST, SYNC_DB1_USER, SYNC_DB1_PASSWORD, SYNC_DB1_DATABASE);


// get the pms setting to start with

$kiw_pms_setting = $kiw_cache->get("PMS_SETTING:{$kiw_tenant}");

if (empty($kiw_pms_setting)){


    $kiw_pms_setting = $kiw_db->query("SELECT * FROM kiwire_int_pms WHERE tenant_id = '{$kiw_tenant}' LIMIT 1");

    if ($kiw_pms_setting) $kiw_pms_setting = $kiw_pms_setting->fetch_all(MYSQLI_ASSOC)[0];

    if (empty($kiw_pms_setting)) $kiw_pms_setting = array("dummy" => true);

    $kiw_cache->set("PMS_SETTING:{$kiw_tenant}", $kiw_pms_setting, 1800);


}


if ($kiw_pms_setting['enabled'] != "y" || $kiw_pms_setting['pms_type'] != "idb") {

    kiw_print_response(false, "PMS setting has been disabled");

}


$kiw_vips = $kiw_cache->get("PMS_VIP_CODE:{$kiw_tenant}");

if (empty($kiw_vips)) {


    $kiw_vips = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_int_pms_vipcode WHERE tenant_id = '{$kiw_tenant}'");

    if ($kiw_vips) $kiw_vips = $kiw_vips->fetch_all(MYSQLI_ASSOC);

    if (empty($kiw_vips)) $kiw_vips = array("dummy" => true);

    $kiw_cache->set("PMS_VIP_CODE:{$kiw_tenant}", $kiw_vips, 1800);


}


if ($kiw_cache->get("PMS_ACTION:{$kiw_tenant}") !== "sync"){

    exit();

}


pms_logger("DBSWAP: [ started ]", "idb", $kiw_tenant);


$kiw_time = date('ymdHis');

$kiw_hash = md5($kiw_pms_setting['pms_token'] . $kiw_pms_setting['pms_project'] . $kiw_time);


$kiw_data = array('DATETIME' => $kiw_time);


$kiw_connection = curl_init();

curl_setopt($kiw_connection, CURLOPT_URL, "{$kiw_pms_setting['pms_host']}/ws/kiwire/DBSWAP/{$kiw_hash}/{$kiw_pms_setting['pms_project']}");
curl_setopt($kiw_connection, CURLOPT_POST, true);
curl_setopt($kiw_connection, CURLOPT_RETURNTRANSFER, true);
curl_setopt($kiw_connection, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
curl_setopt($kiw_connection, CURLOPT_POSTFIELDS, json_encode($kiw_data));
curl_setopt($kiw_connection, CURLOPT_CONNECTTIMEOUT, 20);
curl_setopt($kiw_connection, CURLOPT_TIMEOUT, 60);

unset($kiw_data);


$kiw_response = curl_exec($kiw_connection);

$kiw_response_error = curl_errno($kiw_connection);


curl_close($kiw_connection);

unset($kiw_connection);


if ($kiw_response_error == 0) {


    $kiw_response = json_decode($kiw_response, true);


    if (!empty($kiw_response['aaData'])) {


        // update all available entries to check-out state

        $kiw_db->query("UPDATE kiwire_int_pms_transaction SET updated_date = NOW(), status = 'db-sync', check_out_date = NOW() WHERE tenant_id = '{$kiw_tenant}' AND (status = 'check-in' OR status = 'move-in')");

        $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), status = 'suspend', profile_curr = profile_subs, quota_in = 0, quota_out = 0, date_activate = null, fullname = username WHERE tenant_id = '{$kiw_tenant}' AND ktype = 'account' AND integration = 'pms' AND status = 'active'");


        foreach ($kiw_response['aaData'] as $kiw_data) {


            // check-in for user available

            if ($kiw_data['COMMAND'] == "CHECK_IN") {


		        pms_logger("DBSWAP: processing [ {$kiw_data['ROOM']} ] [ {$kiw_data['GUEST']} ]", "idb", $kiw_tenant);


                switch ($kiw_pms_setting['pass_mode']) {

                    case 0:
                        $kiw_response['password'] = $kiw_pms_setting['pass_predefined'];
                        break;
                    case 1:
                        $kiw_response['password'] = $kiw_response['room_no'];
                        break;
                    default:
                        $kiw_response['password'] = pms_password();
                        break;

                }


                $kiw_response['room_no']        = $kiw_db->escape_string($kiw_data['ROOM']);
                $kiw_response['guest_first']    = $kiw_db->escape_string($kiw_data['GUEST']);
                $kiw_response['guest_last']     = $kiw_db->escape_string($kiw_data['LASTNAME']);
                $kiw_response['guest_arrival']  = $kiw_db->escape_string($kiw_data['ARRDATE']);
                $kiw_response['guest_vip']      = $kiw_db->escape_string($kiw_data['VIP']);
                $kiw_response['guest_name']     = "{$kiw_response['guest_first']} {$kiw_response['guest_last']}";

                pms_check_in($kiw_db, "idb", $kiw_tenant, $kiw_response, $kiw_pms_setting['vip_match'], $kiw_vips);


            }


        }


    }


}

$kiw_cache->del("PMS_ACTION:{$kiw_tenant}");

pms_logger("DBSWAP: [ completed ]", "idb", $kiw_tenant);


