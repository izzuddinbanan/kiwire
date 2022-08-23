<?php


if (php_sapi_name() != "cli") die();


$kiw_tenant = dirname(__FILE__);

if (strpos($kiw_tenant, "server/custom") == false){

    kiw_print_response(false, "Invalid tenant info has been provided");

}

$kiw_tenant = array_filter(explode("/", $kiw_tenant));


foreach ($kiw_tenant as $kiw_index => $kiw_tenant_){

    if ($kiw_tenant_ == "custom"){

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


$kiw_db = new mysqli(SYNC_DB1_HOST, SYNC_DB1_USER, SYNC_DB1_PASSWORD, SYNC_DB1_DATABASE, SYNC_DB1_PORT);


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



$kiw_posts = $kiw_db->query("SELECT * FROM kiwire_int_pms_payment WHERE tenant_id = '{$kiw_tenant}' AND status = 'new'");

if ($kiw_posts) $kiw_posts = $kiw_posts->fetch_all(MYSQLI_ASSOC);


$kiw_time = date('ymdHis');

$kiw_hash = md5($kiw_pms_setting['pms_token'] . $kiw_pms_setting['pms_project'] . $kiw_time);


foreach ($kiw_posts as $kiw_post){


    $kiw_data = array(
        'ROOM'          => $kiw_post['room'],
        'TRXDT'         => date('ymdHis', strtotime($kiw_post['login_date'])),
        'BASICCHARGE'   => number_format($kiw_post['amount'], 2) * 100,
        'DATETIME'      => $kiw_time,
        'DESCRIPTION'   => $kiw_post['profile'],
        'POSTID'        => $kiw_post['id']
    );


    $kiw_connection = curl_init();

    curl_setopt($kiw_connection, CURLOPT_URL, "{$kiw_pms_setting['pms_host']}/ws/kiwire/CHARGE_POSTING/{$kiw_hash}/{$kiw_pms_setting['pms_project']}");
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


    $kiw_response = json_decode($kiw_response, true);


    if ($kiw_response_error == 0){

        $kiw_db->query("UPDATE kiwire_int_pms_payment SET updated_date = NOW(), status = 'ok' WHERE tenant_id = '{$kiw_post['tenant_id']}' AND id = '{$kiw_post['id']}' LIMIT 1");

    } else $kiw_db->query("UPDATE kiwire_int_pms_payment SET updated_date = NOW(), status = 'failed' WHERE tenant_id = '{$kiw_post['tenant_id']}' AND id = '{$kiw_post['id']}' LIMIT 1");


    unset($kiw_response);


}
