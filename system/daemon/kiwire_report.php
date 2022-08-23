<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING );

require_once dirname(__FILE__, 3) . "/server/user/includes/include_account.php";

require_once dirname(__FILE__, 3) . "/server/admin/includes/include_config.php";
require_once dirname(__FILE__, 3) . "/server/admin/includes/include_general.php";


$kiwire_server = new Swoole\Http\Server("127.0.0.1", 9951);

$kiw_system = @file_get_contents(dirname(__FILE__, 3) . "/server/custom/system_setting.json");

$kiw_system = json_decode($kiw_system, true);

$kiwire_server->set(
    array(
        'worker_num' => $kiw_system['service_worker'],
        'max_conn' => 1024,
        'max_request' => 0,
        'group' => 'nginx',
        'user' => 'nginx',
        'pid_file' => '/run/kiwire-report.pid',
        // 'open_tcp_keepalive' => true,
        'daemonize' => 1
    )
);


$kiwire_server->on("start", function () {

    echo "Kiwire generate report started at : " . date("Y-m-d H:i:s") . "\n";

});

$kiwire_server->on("request", function ($request, $response) {

    // initiate mysql connection

    $kiw_db = new Swoole\Coroutine\MySQL();

    $kiw_db->connect(array('host' => SYNC_DB1_HOST, 'user' => SYNC_DB1_USER, 'password' => SYNC_DB1_PASSWORD, 'database' => SYNC_DB1_DATABASE, 'port' => SYNC_DB1_PORT));


    $response->header("Content-Type", "application/json");

    $kiw_data = $request->post;

    if(strtolower(trim($request->server['request_method'])) == "post") {
        
        if (empty($kiw_data)) {

            echo "Empty payload. Please check your parameter.";
            return;

        }


        if (is_array($kiw_data)){

            $kiw_columns = $kiw_data['columns'];

            $kiw_filename = $kiw_data['filename'];

            $kiw_path = dirname(__FILE__, 3);

            //prepare header title 
            $data = $kiw_data['header_data'];
            

            if (file_exists( $kiw_path . "/server/custom/{$kiw_data['tenant_id']}/reports") == false) {

                mkdir( $kiw_path . "/server/custom/{$kiw_data['tenant_id']}/reports", 0755,true);
    
            }

            file_put_contents($kiw_path . "/server/custom/{$kiw_data['tenant_id']}/reports/{$kiw_filename}.log",  "START REPORTING \n", FILE_APPEND);

            
            //open file to write
            $fp = fopen("{$kiw_path}/server/custom/{$kiw_data['tenant_id']}/reports/{$kiw_filename}.csv", 'w');
        
            fputcsv($fp, $data);
        
            
            //loop all table involved
            foreach($kiw_data['kiw_tables'] as $kiw_table){
        
                $offset = 0;
                $limit = 5000;

                //get total row in a table
                $kcount = $kiw_db->query("SELECT count(id) as total FROM {$kiw_table}  WHERE {$kiw_data['search']} LIMIT 1")[0];
        
                //create partition 
                $part = $kcount['total'] / $limit;
                $part = round($part) + 1;
        
                unset($kcount);
        
                //loop by partition created
                for ($i=1; $i <= $part; $i++) {
        
                    $query_data = $kiw_db->query("SELECT " . implode(",", $kiw_columns) . " FROM {$kiw_table} WHERE {$kiw_data['search']} LIMIT {$limit} OFFSET {$offset} ");
                    
                    foreach ($query_data as $data) {
                        //write data inside csv
                        fputcsv($fp, $data);
                
                    }
        
                    //unset to avoid memory limit
                    unset($query_data);
        
                    $offset = $offset + $limit;
        
                } 
        
            }
        
            unset($offset);
            unset($limit);
            unset($part);
        
            //close file after finish 
            fclose($fp);    
            
            unset($data);
            unset($fp);
            
            
            //remove log
            unlink($kiw_path . "/server/custom/{$kiw_data['tenant_id']}/reports/{$kiw_filename}.log");
            echo "Done";

        } else {

            echo "Unknown request.";
            return;

        }

    } else {

        echo "Method not allowed.";
        return;

    }

});



$kiwire_server->start();