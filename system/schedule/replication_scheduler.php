<?php


error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);


$kiw_lock_name = "kiwire-replication.lock";

require_once "scheduler_lock.php";


require_once dirname(__FILE__, 3) . "/server/admin/includes/include_config.php";

require_once dirname(__FILE__, 3) . "/server/admin/includes/include_connection.php";


// $kiw_tables = [
//     "kiwire_blacklist_domain",
//     "kiwire_report_login_dwell",
//     "kiwire_report_login_profile",
//     "kiwire_report_login_device",
//     "kiwire_report_login_error",
//     "kiwire_report_campaign_general",
//     "kiwire_report_login_general",
//     "kiwire_report_controller_statistics",
//     "kiwire_report_controller",
//     "kiwire_device_history",
//     "kiwire_device_register",
//     "kiwire_facebook_reputation",
//     "kiwire_invoice",
//     "kiwire_message",
//     "kiwire_nms_log",
//     "kiwire_nps_score",
//     "kiwire_push_subscription",
//     "kiwire_reputation_data",
//     "kiwire_survey_respond",
//     "kiwire_active_session",
//     "kiwire_account_info",
//     "kiwire_account_auth",
//     "kiwire_payment_trx",
//     "kiwire_int_pms_transaction"
// ];

$kiw_time['start'] = date('Y-m-d H:i:s');

$get_tables = $kiw_db->fetch_array("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'kiwire'");

foreach($get_tables as $tbl) $kiw_tables[] = $tbl['TABLE_NAME'];


// previous month session and current session tables

// $kiw_tables[] = "kiwire_sessions_" . date("Ym", strtotime("-1 Month"));
// $kiw_tables[] = "kiwire_sessions_" . date("Ym");



// create a temp path for data compression

$kiw_path = dirname(__FILE__, 3) . "/replication/";

if (file_exists($kiw_path) == false) mkdir($kiw_path, 0755, true);


$kiw_replicate = @file_get_contents(dirname(__FILE__, 3) . "/server/custom/ha_setting.json");
$kiw_ha_status = @file_get_contents(dirname(__FILE__, 3) . "/server/custom/ha_status.json");

$kiw_replicate = json_decode($kiw_replicate, true);
$kiw_ha_status = json_decode($kiw_ha_status, true);


if (!$kiw_replicate) die("Replication setting unset");


if ($kiw_replicate['enabled'] == "y") {


    if ($kiw_replicate['role'] == "master") {


        if ((time() - $kiw_ha_status['latest']) >= ($kiw_replicate['t_interval'] * 60)){


            $kiw_connected = false;


            $kiw_error_count = @file_get_contents(dirname(__FILE__, 3) . "/server/custom/ha_error.json");

            $kiw_error_count = (int)$kiw_error_count;


            if ($kiw_error_count > 2){

                check_logger("Too much error. Stop sending data to avoid overwrite.");

                die();

            }



            check_logger("Replication: sending incremental reports data");


            $kiw_error_counted = false;


            foreach ($kiw_tables as $kiw_table) {


                $kiw_fn = "{$kiw_path}{$kiw_table}.sql.gz";


                if (isset($kiw_ha_status['tables'][$kiw_table]) == false || empty($kiw_ha_status['tables'][$kiw_table])){

                    $kiw_ha_status['tables'][$kiw_table] = "2020-01-01 00:00:00";

                }


                // create the dump file

                check_logger("{$kiw_table}: prepare replication");


                $kiw_time = date("Y-m-d H:i:s");


                if (SYNC_DB1_HOST == "127.0.0.1") {

                    $kiw_error_dump = system("mysqldump --no-create-info --single-transaction --replace kiwire {$kiw_table} --where=\"(updated_date BETWEEN '{$kiw_ha_status['tables'][$kiw_table]}' AND '{$kiw_time}')\" 2>/dev/null | gzip -c > $kiw_fn", $kiw_response);

                } else {

                    $kiw_error_dump = system("mysqldump -h " . SYNC_DB1_HOST . " -u " . SYNC_DB1_USER . " -p" . SYNC_DB1_PASSWORD . " --no-create-info --single-transaction --replace kiwire {$kiw_table} --where=\"(updated_date BETWEEN '{$kiw_ha_status['tables'][$kiw_table]}' AND '{$kiw_time}')\" 2>/dev/null | gzip -c > $kiw_fn", $kiw_response);

                }



                if ($kiw_response == 0) {


                    // push the dump file to backup server

                    check_logger("{$kiw_table}: sending replication to {$kiw_replicate['backup_ip_address']}");
                    
                    
                    $kiw_curl = curl_init();
                    
                    $kiw_file = curl_file_create($kiw_fn);
                    

                    curl_setopt($kiw_curl, CURLOPT_URL, "http://{$kiw_replicate['backup_ip_address']}:9958");
                    curl_setopt($kiw_curl, CURLOPT_POST, true);
                    curl_setopt($kiw_curl, CURLOPT_POSTFIELDS, array("data" => $kiw_file));

                    curl_setopt($kiw_curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($kiw_curl, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($kiw_curl, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($kiw_curl, CURLOPT_TIMEOUT, 10);
                    curl_setopt($kiw_curl, CURLOPT_CONNECTTIMEOUT, 10);

                    unset($kiw_file);


                    $kiw_temp = trim(curl_exec($kiw_curl));
                    $kiw_error = curl_errno($kiw_curl);

                    curl_close($kiw_curl);

                    unset($kiw_curl);


                } else $kiw_error = 1;


                if ($kiw_temp == "thanks" && $kiw_error == 0) {


                    $kiw_connected = true;

                    check_logger("{$kiw_table}: succeed");

                    $kiw_ha_status['tables'][$kiw_table] = $kiw_time;


                    // update status file to reflect current situation

                    $kiw_ha_status['latest'] = time();

                    @file_put_contents(dirname(__FILE__, 3) . "/server/custom/ha_status.json", json_encode($kiw_ha_status));
                    

                } elseif($kiw_temp == "config"){

                    check_logger("Config Error : Both server have same role");

                    die();

                } elseif ($kiw_temp == "reset") {


                    check_logger("backup reset in progress. terminated.");

                    die();


                } elseif ($kiw_temp == "archive") {


                    check_logger("backup archive in progress. terminated.");

                    die();


                } else {


                    check_logger("{$kiw_table}: failed: {$kiw_error_dump}");

                    if ($kiw_error > 0 && $kiw_error_counted == false) {


                        file_put_contents(dirname(__FILE__, 3) . "/server/custom/ha_error.json", $kiw_error_count + 1);

                        $kiw_error_counted = true;


                    }


                }


                // delete the file

                unlink($kiw_fn);


            }


            if ($kiw_error_counted == false){

                file_put_contents(dirname(__FILE__, 3) . "/server/custom/ha_error.json", 0);

            }



            // if ($kiw_connected == true) {


            //     check_logger("Replication: syncing tables");


            //     $kiw_db = Database::obtain();


            //     $kiw_syncs = $kiw_db->fetch_array("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'kiwire'");


            //     // replicate data using percona toolkit


            //     foreach ($kiw_syncs as $kiw_sync) {


            //         $kiw_sync = $kiw_sync['TABLE_NAME'];


            //         if (!in_array($kiw_sync, $kiw_tables)) {

            //             if (strpos("_sessions_", $kiw_sync) == false) {


            //                 $kiw_test = time();


            //                 check_logger("{$kiw_sync}: syncing..");


            //                 $kiw_error = system("pt-table-sync --no-version-check --execute h=" . SYNC_DB1_HOST . ",u=" . SYNC_DB1_USER . ",p=" . SYNC_DB1_PASSWORD . ",D=" . SYNC_DB1_DATABASE . ",t={$kiw_sync} h={$kiw_replicate['backup_ip_address']},u=kiwire_replication,p=nfsV8GzCNy25 2>&1", $kiw_response);

            //                 if ($kiw_response == 1 || $kiw_response == 3) {

            //                     check_logger("ERROR: " . $kiw_error);

            //                 } else $kiw_ha_status['tables'][$kiw_sync] = date("Y-m-d H:i:s");


            //                 unset($kiw_error);

            //                 check_logger("{$kiw_sync}: done");

            //                 unset($kiw_response);


            //                 if ((time() - $kiw_test) > 20) check_logger("{$kiw_sync}: too long to complete: " . (time() - $kiw_test) . " seconds");


            //             }


            //         }


            //     }


            //     // update status file to reflect current situation

            //     $kiw_ha_status['latest'] = time();

            //     @file_put_contents(dirname(__FILE__, 3) . "/server/custom/ha_status.json", json_encode($kiw_ha_status));


            //     // replicate custom directory

            //     check_logger("Replication: assets: syncing..");


            //     // need to put last / for proper synchronization

            //     system("rsync -e 'ssh -o StrictHostKeyChecking=no' --exclude 'ha_setting.json' --exclude 'service_notification.json' -qaz /var/www/kiwire/server/custom/ root@{$kiw_replicate['backup_ip_address']}:/var/www/kiwire/server/custom/");


            //     check_logger("Replication: assets: done");


            // } else {


            //     check_logger("Replication: skip percona and rsync since no interaction");


            // }


            check_logger("Replication: all done!");



        } else {


            check_logger("Replication: wait for interval: {$kiw_replicate['t_interval']} minutes");


        }



    }

    $kiw_time['end'] = date('Y-m-d H:i:s');
    $kiw_cache->set("KIW_SCHEDULER:REPLICATION_SCHEDULER:RUN_AT", $kiw_time);


}


function check_logger($message){


    if (file_exists(dirname(__FILE__, 3) . "/logs/general/") == false)
        mkdir(dirname(__FILE__, 3) . "/logs/general/", 0755, true);


    file_put_contents( dirname(__FILE__, 3) . "/logs/general/kiwire-replication-general-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s") . " :: " . $message . "\n", FILE_APPEND);


}

