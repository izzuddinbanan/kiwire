<?php

require_once "config.php";

define(AGENT_NAME, basename(__FILE__, '.php'));

ini_set("memory_limit", MEMORY_LIMIT);


$transaction_time = date("ymd-his");
writeLog($transaction_time, "Start process.");


echo "Check v3 server DB connection.. ";
$kiw_v3 = new mysqli(DB_V3_HOST, DB_V3_USER, DB_V3_PASS, DB_V3_NAME, DB_V3_PORT);
if ($kiw_v3->connect_errno) die("Kiwire: Unable to connect to database.\n");
echo "OK\n============\n";



echo "Check v2 server DB connection.. ";
$kiw_v2 = new mysqli(DB_V2_HOST, DB_V2_USER, DB_V2_PASS, DB_V2_NAME, DB_V2_PORT);
if ($kiw_v2->connect_errno) die("Kiwire: Unable to connect to database.\n");
echo "OK\n============\n";



$kiw_action = "";


$kiw_input = fopen("php://stdin", "r");


while (!in_array($kiw_action, array("y"))){


    echo "Are you sure to import data from nas from v2 to kiwire_controller into this server 'Kiwire v3' [ y / n ] : ";

    $kiw_action = strtolower(trim(fread($kiw_input, 10)));

    if ($kiw_action == "n") die("\n");


}
unset($kiw_action);
unset($kiw_input);

system("rm -f logs/". AGENT_NAME . "*.log");

$kiw_v3->query("TRUNCATE TABLE kiwire_controller");
$kiw_total_data = $kiw_v2->query("SELECT count(id) as devices FROM nas");

if (!$kiw_total_data) die("Error on count device data.\n");

$kiw_total_data = $kiw_total_data->fetch_assoc()["devices"];

writeLog($transaction_time, "Total row in nas [ ". $kiw_total_data ." ]");
echo "Total row in nas [ ". $kiw_total_data ." ] .. \n";


$kiw_conf["limit"]  = 90000; 
$kiw_conf["offset"] = 0; 


$kiw_conf["block"] = $kiw_total_data / $kiw_conf["limit"];
$kiw_conf["block"] = round($kiw_conf["block"]) + 1;
// var_dump($kiw_conf["block"]);

writeLog($transaction_time, "Split data into  [ ". $kiw_conf["block"] ." ] block ");
echo "Split data into  [ ". $kiw_conf["block"] ." ] block \n";

Swoole\Runtime::enableCoroutine();

for ($i=1; $i <= $kiw_conf["block"]; $i++) { 

    go(function () use($kiw_conf, $transaction_time, $i, $kiw_v3, $kiw_v2){    

        $kiw_v2 = new Swoole\Coroutine\MySQL();
        $kiw_v2->connect([
            'host'      => DB_V2_HOST,
            'user'      => DB_V2_USER,
            'password'  => DB_V2_PASS,
            'database'  => DB_V2_NAME,
            'port'      => DB_V2_PORT
        ]);


        writeLog($transaction_time, "Start block  [ {$i} / {$kiw_conf['block']} ]  ");
        echo "Start block  [ {$i} / {$kiw_conf['block']} ] \n";

        $kiw_devices = $kiw_v2->query("SELECT 
                                nasname AS device_ip, 
                                shortname AS unique_id, 
                                kiwire_type AS vendor, 
                                ports AS coa_port, 
                                'controller' AS device_type, 
                                secret AS shared_secret, 
                                community, 
                                description, 
                                location, 
                                username, 
                                password, 
                                cloud_id AS tenant_id, 
                                seamless_type, 
                                snmpv, 
                                mib 
                                FROM nas
                                LIMIT {$kiw_conf["limit"]} OFFSET {$kiw_conf['offset']}");

        $kiw_total_row  = count($kiw_devices);
        
        if ($kiw_total_row <= 0) {

            writeLog($transaction_time, "No data in block  [ {$i} / {$kiw_conf['block']} ]  ");
            echo "No data in block  [ {$i} / {$kiw_conf['block']} ]  \n";
    
        }

        $kiw_curr_row   = 0;

        foreach($kiw_devices as $kiw_device){

            
            $kiw_v3 = new Swoole\Coroutine\MySQL();
            $kiw_v3->connect([
                'host' => DB_V3_HOST,
                'user' => DB_V3_USER,
                'password' => DB_V3_PASS,
                'database' => DB_V3_NAME,
                'port' => DB_V3_PORT
            ]);
            
            go(function () use($kiw_conf, $transaction_time, $kiw_v3, &$kiw_curr_row, $kiw_device, $i, $kiw_total_row){   
                

                $kiw_curr_row++;
                echo "Processing Block [ {$i} / {$kiw_conf['block']} ] - Row {$kiw_curr_row} / {$kiw_total_row} ..\n";


                if($kiw_curr_row == $kiw_total_row) {

                    writeLog($transaction_time, "End Block  [ {$i} / {$kiw_conf['block']} ] - Total row [ {$kiw_curr_row} ] . Completed ..");
                    echo " Block  [ {$i} / {$kiw_conf['block']} ]  Completed . \n";
                }

                $kiw_temp = array();


                $kiw_temp['id']             = "NULL";
                $kiw_temp['tenant_id']      = $kiw_device['tenant_id'];
                $kiw_temp['updated_date']   = "NOW()";
                $kiw_temp['unique_id']      = $kiw_device['unique_id'];
                $kiw_temp['device_ip']      = $kiw_device['device_ip'];
                $kiw_temp['coa_port']       = $kiw_device['coa_port'];
                $kiw_temp['vendor']         = $kiw_device['vendor'];
                $kiw_temp['device_type']    = $kiw_device['device_type'];
                $kiw_temp['shared_secret']  = $kiw_device['shared_secret'];
                $kiw_temp['description']    = $kiw_device['description'];
                $kiw_temp['community']      = $kiw_device['community'];
                $kiw_temp['location']       = $kiw_device['location'];
                $kiw_temp['monitor_method'] = $kiw_device['monitor_method'];
                $kiw_temp['username']       = $kiw_device['username'];
                $kiw_temp['password']       = $kiw_device['password'];
                $kiw_temp['seamless_type']  = $kiw_device['seamless_type'];
                $kiw_temp['snmpv']          = $kiw_device['snmpv'];
                $kiw_temp['mib']            = $kiw_device['mib'];

                $kiw_temp_query     = sql_insert($kiw_v3, "kiwire_controller", $kiw_temp);

                $kiw_temp_result = $kiw_v3->query($kiw_temp_query);
                if(!$kiw_temp_result) {

                    writeLog($transaction_time, "Error : {$kiw_temp_query}");

                }
                

                unset($kiw_temp);
                unset($kiw_temp_result);

            });
            
        }


    });
    

    $kiw_conf['offset'] = $kiw_conf['offset'] + $kiw_conf["limit"];

}

echo count(Swoole\Coroutine::listCoroutines()), " active coroutines when reaching the end of the PHP script.\n";



