<?php


error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);


$kiw_lock_name = "kiwire-ghost.lock";

require_once "scheduler_lock.php";


require_once "/var/www/kiwire/server/admin/includes/include_config.php";


$kiw_path = dirname(__FILE__);


require_once dirname(__FILE__, 3) . "/server/admin/includes/include_config.php";
require_once dirname(__FILE__, 3) . "/server/admin/includes/include_general.php";
require_once dirname(__FILE__, 3) . "/server/admin/includes/include_connection.php";


ini_set("max_execution_time", 300);

$kiw_time['start'] = date('Y-m-d H:i:s');
// check if log path available. if not then create

$kiw_log_path = dirname(__FILE__, 3) . "/logs/ghost-sessions/";

if (file_exists($kiw_log_path) == false){

    mkdir($kiw_log_path, 755, true);

}


system("chown nginx:nginx -R {$kiw_log_path}");

system("chmod 755 -R {$kiw_log_path}");


// var_dump($kiw_db);

$kiw_sessions = $kiw_db->fetch_array("SELECT * FROM kiwire_sessions_202108 WHERE updated_date < DATE_SUB(NOW(), INTERVAL 5 MINUTE) AND (terminate_reason = '' OR terminate_reason IS NULL ) AND stop_time IS NULL");


foreach($kiw_sessions as $kiw_session) {

    // FORMULA TO FIX GHOST SESSION
    // 1. get the ghost session id
    // 2. find previous session
    // 3. session_time (kiwire_account_auth) - session_time (kiwire_session_yyymm ** from number 2)


    // FIND DATA PREVIOUS
    $kiw_last_row = $kiw_db->fetch_array("SELECT * FROM kiwire_sessions_202108 where id < '{$kiw_session["id"]}' AND tenant_id = '{$kiw_session["tenant_id"]}' AND username = '{$kiw_session["username"]}' ORDER BY id DESC LIMIT 1")[0];

    if(!empty($kiw_last_row)) {

        
        // Fix kiwire_account_auth table
        $kiw_auth = $kiw_db->query("UPDATE kiwire_account_auth SET 
            updated_date = NOW(),
            session_time = (session_time - {$kiw_last_row['session_time']}),
            quota_in = (quota_in - {$kiw_last_row['quota_in']}),
            quota_out = (quota_out - {$kiw_last_row['quota_out']}) 
            WHERE username = '{$kiw_session["username"]}' AND tenant_id = '{$kiw_session["tenant_id"]}' LIMIT 1");


        //if success fix kiwire_account_auth table

        if ($kiw_db->db_affected_row) {

            if (file_exists("{$kiw_log_path}/{$kiw_session["tenant_id"]}") == false){

                mkdir("{$kiw_log_path}/{$kiw_session["tenant_id"]}", 755, true);
            
            }

            $kiw_db->query("DELETE FROM kiwire_sessions_202108 WHERE id = '{$kiw_session["id"]}'");


            file_put_contents("{$kiw_log_path}/{$kiw_session["tenant_id"]}/log-" . date("Ymd") . ".log", date("Y-m-d H:i:s ") . ":: last row ::" . json_encode($kiw_last_row) . "\n", FILE_APPEND);

            file_put_contents("{$kiw_log_path}/{$kiw_session["tenant_id"]}/log-" . date("Ymd") . ".log", date("Y-m-d H:i:s ") . ":: ghost row ::" . json_encode($kiw_session) . "\n", FILE_APPEND);

        }


    }

}

$kiw_time['end'] = date('Y-m-d H:i:s');
$kiw_cache->set("KIW_SCHEDULER:GHOST_SESSION_SCHEDULER:RUN_AT", $kiw_time);
