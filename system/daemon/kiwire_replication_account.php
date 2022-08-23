<?php


error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);


require_once dirname(__FILE__, 3) . "/server/admin/includes/include_config.php";


$kiw_path = dirname(__FILE__, 3) . "/replication/";

if (file_exists($kiw_path) == false) mkdir($kiw_path, 0755, true);


$kiw_replicate = @file_get_contents(dirname(__FILE__, 3) . "/server/custom/ha_setting.json");

$kiw_replicate = json_decode($kiw_replicate, true);


if (!is_array($kiw_replicate)) die("Replication setting unset");


if ($kiw_replicate['enabled'] != "y") die("Replication disabled");


$kiw_db = new mysqli("p:" . SYNC_DB1_HOST, SYNC_DB1_USER, SYNC_DB1_PASSWORD, SYNC_DB1_DATABASE, SYNC_DB1_PORT);

$kiw_path = dirname(__FILE__, 3) . "/replication/";


if (file_exists($kiw_path) == false){

    mkdir($kiw_path, 0755, true);

}


while (true){


    try {

        $kiw_db->ping();

    } catch (Exception $kiw_e){

        $kiw_db = new mysqli("p:" . SYNC_DB1_HOST, SYNC_DB1_USER, SYNC_DB1_PASSWORD, SYNC_DB1_DATABASE, SYNC_DB1_PORT);

    }


    // get the last execute date

    $kiw_last = @file_get_contents(dirname(__FILE__, 3) . "/server/custom/ha_account.log");

    if (empty($kiw_last)) $kiw_last = "2020-01-01 00:00:00";


    $kiw_time = date("Y-m-d H:i:s");


    $kiw_count = $kiw_db->query("SELECT COUNT(*) AS kcount FROM kiwire_account_auth WHERE (date_create BETWEEN '{$kiw_last}' AND '{$kiw_time}')");

    $kiw_count = $kiw_count->fetch_all(MYSQLI_ASSOC)[0];



    if ($kiw_count['kcount'] > 0){


        check_logger("New account detected. Sending to backup server.");
        
        
        $kiw_response = 1;

        
        $kiw_filename = "{$kiw_path}kiwire_account_auth_fast.sql.gz";
        
        
        
        if (SYNC_DB1_HOST == "127.0.0.1") {
            
            $kiw_error_dump = system("mysqldump --no-create-info --single-transaction --replace kiwire kiwire_account_auth --where=\"(date_create BETWEEN '{$kiw_last}' AND '{$kiw_time}')\" 2>/dev/null | gzip -c > {$kiw_filename}", $kiw_response);
            
        } else {
            
            $kiw_error_dump = system("mysqldump -h " . SYNC_DB1_HOST . " -u " . SYNC_DB1_USER . " -p" . SYNC_DB1_PASSWORD . " --no-create-info --single-transaction --replace kiwire kiwire_account_auth --where=\"(date_create BETWEEN '{$kiw_last}' AND '{$kiw_time}')\" 2>/dev/null | gzip -c > {$kiw_filename}", $kiw_response);
            
        }
        
        check_logger($kiw_response);

        if ($kiw_response == 0){


            $kiw_curl = curl_init();

            $kiw_file = curl_file_create($kiw_filename);


            curl_setopt($kiw_curl, CURLOPT_URL, "http://{$kiw_replicate['backup_ip_address']}:9958");
            curl_setopt($kiw_curl, CURLOPT_POST, true);
            curl_setopt($kiw_curl, CURLOPT_POSTFIELDS, array("data" => $kiw_file));

            curl_setopt($kiw_curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($kiw_curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($kiw_curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($kiw_curl, CURLOPT_TIMEOUT, 10);
            curl_setopt($kiw_curl, CURLOPT_CONNECTTIMEOUT, 10);

            unset($kiw_file);


            $kiw_temp = curl_exec($kiw_curl);
            $kiw_error = curl_errno($kiw_curl);

            curl_close($kiw_curl);

            unset($kiw_curl);


            if (trim($kiw_temp) == "thanks" && $kiw_error == 0) {


                @file_put_contents(dirname(__FILE__, 3) . "/server/custom/ha_account.log", $kiw_time);


            } else check_logger("ERROR: Unable to send new account to backup server. Return code: {$kiw_error}");


            unset($kiw_temp);

            unset($kiw_error);


        } else {

            check_logger("ERROR: {$kiw_error_dump}");

        }


        // remove the dump file

        unlink($kiw_filename);

        unset($kiw_filename);


        unset($kiw_response);

        unset($kiw_error_dump);


    } else {


        @file_put_contents(dirname(__FILE__, 3) . "/server/custom/ha_account.log", $kiw_time);


    }


    unset($kiw_count);

    unset($kiw_time);


    // wait 10 second before restart checking

    sleep(10);


}

function check_logger($message){


    if (file_exists(dirname(__FILE__, 3) . "/logs/general/") == false) {

        mkdir(dirname(__FILE__, 3) . "/logs/general/", 0755, true);

    }

    file_put_contents( dirname(__FILE__, 3) . "/logs/general/kiwire-replication-general-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s") . " :: " . $message . "\n", FILE_APPEND);


}