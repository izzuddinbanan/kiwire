<?php


error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);


require_once dirname(__FILE__, 3) . "/server/admin/includes/include_config.php";


$kiw_path = dirname(__FILE__, 3) . "/replication/";

if (file_exists($kiw_path) == false) mkdir($kiw_path, 0755, true);


$kiw_replicate = @file_get_contents(dirname(__FILE__, 3) . "/server/custom/ha_setting.json");

$kiw_replicate = json_decode($kiw_replicate, true);


if (!is_array($kiw_replicate)) die("Replication setting unset");


$kiw_server = new swoole_http_server("0.0.0.0", 9958);

$kiw_server->set(
    array(
        'worker_num' => 1,
        'max_conn' => 64,
        'max_request' => 64,
        'pid_file' => '/run/kiwire-replication.pid',
        'daemonize' => 1
    )
);


$kiw_server->on("start", function ($server) {

    check_logger("Replication service started: " . date("Y-m-d H:i:s"));

});


$kiw_server->on("request", function ($request, $response) use ($kiw_path, $kiw_replicate) {


    // check for ip address where packet came from

    if ($request->server['remote_addr'] !== $kiw_replicate['master_ip_address']){

        check_logger("Received data for unknown IP: " . $request->server['remote_addr']);

        $response->end("thanks");

        return;

    }


    if ($request->rawcontent() == "ping"){


        $response->end("pong");

        return;


    }


    if ($request->post['action'] == "delete"){


        $kiw_db = new Swoole\Coroutine\MySQL();

        $kiw_db->connect(array('host' => SYNC_DB1_HOST, 'user' => SYNC_DB1_USER, 'password' => SYNC_DB1_PASSWORD, 'database' => SYNC_DB1_DATABASE, 'port' => SYNC_DB1_PORT));
        


        $kiw_username = $kiw_db->escape($request->post['username']);

        $kiw_tenant = $kiw_db->escape($request->post['tenant_id']);


        if (!empty($kiw_username) && !empty($kiw_tenant)) {

            $kiw_db->query("DELETE FROM kiwire_account_auth WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant}' LIMIT 1");
            $kiw_db->query("DELETE FROM kiwire_account_info WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant}' LIMIT 1");
            $kiw_db->query("UPDATE kiwire_device_history SET updated_date = NOW(), last_account = '' WHERE last_account ='{$kiw_username}' AND tenant_id = '{$kiw_tenant}' LIMIT 1");

        }


        $kiw_db->close();


        $response->end("done");

        return;


    }


    $kiw_filename = $request->files['data']['name'];

    $kiw_content = $request->files['data']['tmp_name'];


    check_logger("Received File: {$kiw_filename}");


    if (strpos($kiw_filename, ".sql.gz")) {


        // start redis connection

        $kiw_cache = new Redis();

        $kiw_cache->connect(SYNC_REDIS_HOST, SYNC_REDIS_PORT);

        $kiw_cache->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);


        if ($kiw_cache->exists("REPLICATION_RESET")){


            $response->end("reset");

            return;


        } elseif ($kiw_cache->exists("REPLICATION_ARCHIVE")){


            $response->end("archive");

            return;


        }


        if (move_uploaded_file($kiw_content, "{$kiw_filename}")) {

            if($kiw_replicate['master_ip_address'] != 'master'){ 

                system("gunzip {$kiw_filename}");


                $kiw_filename = str_replace(".gz", "", $kiw_filename);
    
    
                if (file_exists($kiw_filename) == true) {
    
                    if (SYNC_DB1_HOST == "127.0.0.1"){
    
                        $kiw_error = system("mysql kiwire < {$kiw_filename}", $kiw_test);
    
                    } else {
    
                        $kiw_error = system("mysql -h " . SYNC_DB1_HOST . " -u " . SYNC_DB1_USER . " -p" . SYNC_DB1_PASSWORD . "  kiwire < {$kiw_filename}", $kiw_test);
    
                    }
    
    
                    if ($kiw_test == 0) {
    
    
                        $kiw_cache->set("REPLICATION_RECEIVED", time());
    
                        check_logger("Succeed dump file for: {$kiw_filename}");
    
                        $response->end("thanks");
    
    
                    } else {
    
    
                        check_logger("Error dump file for: {$kiw_filename} :: {$kiw_error}");
    
                        $response->end("MySQL return error during restore process.");
    
    
                    }
    
    
                    system("rm -rf {$kiw_filename}");
    
    
                } else {
    
    
                    $response->end("Error during file extraction.");
    
                    return;
    
    
                }

                
            }else{
                $response->end("config");
            }
           

        } else {

            $response->end("Internal error. Unable to move file.");

        }


    } else {

        $response->end("Invalid payload provided");

    }


});


$kiw_server->start();


function check_logger($message){


    if (file_exists(dirname(__FILE__, 3) . "/logs/general/") == false) {

        mkdir(dirname(__FILE__, 3) . "/logs/general/", 0755, true);

    }

    file_put_contents( dirname(__FILE__, 3) . "/logs/general/kiwire-replication-general-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s") . " :: " . $message . "\n", FILE_APPEND);


}

