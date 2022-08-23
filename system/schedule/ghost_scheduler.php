<?php


error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);


$kiw_lock_name = "kiwire-ghost.lock";

// require_once "scheduler_lock.php";


$kiw_path = dirname(__FILE__);

// require_once "class.api.mikrotik.php";
require_once dirname(__FILE__, 3) . "/server/admin/includes/include_config.php";
require_once dirname(__FILE__, 3) . "/server/admin/includes/include_general.php";
require_once dirname(__FILE__, 3) . "/server/admin/includes/include_connection.php";

$kiw_time['start'] = date('Y-m-d H:i:s');

$kiw_active_sessions = $kiw_db->fetch_array("SELECT * FROM kiwire_active_session WHERE updated_date < DATE_SUB(NOW(), INTERVAL 60 MINUTE)");

$kiw_temp = count($kiw_active_sessions);

@file_put_contents("/var/www/kiwire/logs/ghost_session.log", date("Y-m-d H:i:s") . " | Total session found   = { $kiw_temp }" . "\n", FILE_APPEND);

// echo "Total session found   = { $kiw_temp } \n";

unset($kiw_temp);


foreach ($kiw_active_sessions as $key => $kiw_active_session) {


    @file_put_contents("/var/www/kiwire/logs/ghost_session.log", date("Y-m-d H:i:s") . " | {$kiw_active_session['username']} MAC: {$kiw_active_session['mac_address']} IP: {$kiw_active_session['ip_address']} SID: {$kiw_active_session['session_id']} | Terminated due no active session in [ {$kiw_active_session['username']} ]" . "\n", FILE_APPEND);



    $session_time   = $kiw_active_session['session_time'];
    $start_time     = $kiw_active_session['start_time'];


    $minutes_to_add = $session_time / 60;
    $minutes_to_add = round($minutes_to_add);
    $minutes_to_add = $minutes_to_add + 1;
    $time = new DateTime($start_time);
    $time->add(new DateInterval('PT' . $minutes_to_add . 'M'));
    $stamp = $time->format('Y-m-d H:i:s');

    // var_dump($stamp);

    $delete = $kiw_db->query("DELETE FROM kiwire_active_session WHERE  id = '{$kiw_active_session['id']}' LIMIT 1");
    // var_dump("delete - DELETE FROM kiwire_active_session WHERE  id = '{$kiw_active_session['id']}' LIMIT 1");

    $kiw_db->query("UPDATE {$kiw_active_session['session_table']} SET stop_time = '{$stamp}', terminate_reason = 'Stale-Reset' WHERE  username = '{$kiw_active_session['username']}' AND unique_id = '{$kiw_active_session['unique_id']}' AND terminate_reason IS NULL AND stop_time IS NULL LIMIT 1");

    // var_dump("UPDATE {$kiw_active_session['session_table']} SET stop_time = '{$stamp}', terminate_reason = 'Stale-Reset' WHERE  username = '{$kiw_active_session['username']}' AND unique_id = '{$kiw_active_session['unique_id']}' AND terminate_reason IS NULL AND stop_time IS NULL LIMIT 1");


}


$kiw_time['end'] = date('Y-m-d H:i:s');
$kiw_cache->set("KIW_SCHEDULER:GHOST_SCHEDULER:RUN_AT", $kiw_time);