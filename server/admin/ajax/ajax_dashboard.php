<?php

$kiw['module'] = "General -> Dashboard";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_general.php";
require_once "../includes/include_connection.php";
require_once "../includes/include_report.php";


if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}


$kiw_data_for = $_REQUEST['data'];


$kiw_config = $kiw_db->query_first("SELECT  timezone FROM kiwire_clouds WHERE tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");


if (empty($kiw_config['timezone'])) $kiw_config['timezone'] = "Asia/Kuala_Lumpur";


if ($kiw_data_for == "actions") {


    $kiw_path = dirname(__FILE__, 4);

    $kiw_time = date("Ymd-*");



    $kiw_result_data = `tail -n 10 $(ls -Art /var/www/kiwire/logs/{$_SESSION['tenant_id']}/kiwire-system-{$_SESSION['tenant_id']}* | tail -n 1)`;

    $kiw_result_data = explode(PHP_EOL, $kiw_result_data);



    $kiw_result = [];


    foreach ($kiw_result_data as $kiw_log) {


        $kiw_log = explode(" : ", $kiw_log);

        if (!empty($kiw_log[1])) {

            $kiw_result[] = array("date" => sync_tolocaltime(date("Y-m-d H:i:s", strtotime($kiw_log[0]))), "message" => ucfirst($kiw_db->sanitize($kiw_log[1])));

        }


    }

    echo json_encode(array("status" => "success", "data" => $kiw_result));


}elseif ($kiw_data_for == "impressionvslogin"){


    $kiw_end = date("Y-m-d H:00:00", strtotime("-1 Hour"));

    $kiw_start = date("Y-m-d H:00:00", strtotime("{$kiw_end} -23 Hour"));

    $kiw_temps = $kiw_db->fetch_array("SELECT CONVERT_TZ(report_date, 'UTC', '{$kiw_config['timezone']}') AS ddate, SUM(IFNULL(succeed, 0)) AS login, SUM(IFNULL(impression, 0)) AS impression FROM kiwire_report_login_general WHERE tenant_id = '{$_SESSION['tenant_id']}' AND (report_date BETWEEN '{$kiw_start}' AND '{$kiw_end}') GROUP BY ddate");

    echo json_encode(array("status" => "success", "data" => $kiw_temps));


} elseif ($kiw_data_for == "dwell"){


    $kiw_end = date("Y-m-d H:00:00", strtotime("-1 Hour"));

    $kiw_start = date("Y-m-d H:00:00", strtotime("{$kiw_end} -23 Hour"));

    $kiw_temp = $kiw_db->fetch_array("SELECT CONVERT_TZ(report_date, 'UTC', '{$kiw_config['timezone']}') AS xreport_date, SUM(dwell) AS dwell, SUM(login) AS login FROM kiwire_report_login_profile WHERE tenant_id = '{$_SESSION['tenant_id']}' AND (report_date BETWEEN '{$kiw_start}' AND '{$kiw_end}') GROUP BY xreport_date");

    echo json_encode(array("status" => "success", "data" => $kiw_temp));



} elseif ($kiw_data_for == "general"){


    $kiw_time = date("YmdH");

    $kiw_result = [];


    // count active session

    $kiw_temp = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_active_session WHERE tenant_id = '{$_SESSION['tenant_id']}'");

    $kiw_result['active'] = $kiw_temp['kcount'];

    if (empty($kiw_result['active'])) $kiw_result['active'] = 0;



    // count login success

    $kiw_temp = $kiw_cache->keys("REPORT_LOGIN_SUCCESS:{$kiw_time}:{$_SESSION['tenant_id']}:*");

    foreach ($kiw_temp as $kiw_key){

        $kiw_result['connected'] += $kiw_cache->get($kiw_key);

    }

    if (empty($kiw_result['connected'])) $kiw_result['connected'] = 0;


    // count disconnect

    $kiw_temp = $kiw_cache->keys("REPORT_DISCONNECT:{$kiw_time}:{$_SESSION['tenant_id']}:*");

    foreach ($kiw_temp as $kiw_key){

        $kiw_result['disconnected'] += $kiw_cache->get($kiw_key);

    }

    if (empty($kiw_result['disconnected'])) $kiw_result['disconnected'] = 0;


    // live page impression

    $kiw_temp = $kiw_cache->keys("REPORT_IMPRESSION_ZONE:{$kiw_time}:{$_SESSION['tenant_id']}:*");

    foreach ($kiw_temp as $kiw_key){

        $kiw_result['page_impression'] += $kiw_cache->get($kiw_key);

    }

    if (empty($kiw_result['page_impression'])) $kiw_result['page_impression'] = 0;



    // live campaign impression

    $kiw_temp = $kiw_cache->keys("REPORT_CAMPAIGN_IMPRESS:{$kiw_time}:{$_SESSION['tenant_id']}:*");

    foreach ($kiw_temp as $kiw_key){

        $kiw_result['campaign_impress'] += $kiw_cache->get($kiw_key);

    }

    if (empty($kiw_result['campaign_impress'])) $kiw_result['campaign_impress'] = 0;



    // live campaign click

    $kiw_temp = $kiw_cache->keys("REPORT_CAMPAIGN_CLICK:{$kiw_time}:{$_SESSION['tenant_id']}:*");

    foreach ($kiw_temp as $kiw_key){

        $kiw_result['campaign_click'] += $kiw_cache->get($kiw_key);

    }

    if (empty($kiw_result['campaign_click'])) $kiw_result['campaign_click'] = 0;



    // live login data

    $kiw_temp = $kiw_cache->keys("REPORT_LOGIN_SUCCESS:{$kiw_time}:{$_SESSION['tenant_id']}:*");

    foreach ($kiw_temp as $kiw_key){

        $kiw_result['login'] += $kiw_cache->get($kiw_key);

    }

    if (empty($kiw_result['login'])) $kiw_result['login'] = 0;




    echo json_encode(array("status" => "success", "data" => $kiw_result));


}

























