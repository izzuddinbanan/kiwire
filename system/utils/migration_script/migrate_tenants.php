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


    echo "Are you sure to import data tenant from v2 to kiwire_controller into this server 'Kiwire v3' [ y / n ] : ";

    $kiw_action = strtolower(trim(fread($kiw_input, 10)));

    if ($kiw_action == "n") die("\n");


}
unset($kiw_action);
unset($kiw_input);

system("rm -f logs/". AGENT_NAME . "*.log");

$kiw_v3->query("TRUNCATE TABLE kiwire_clouds");
$kiw_total_data = $kiw_v2->query("SELECT count(x.cloud_id) AS cloud_id FROM kiwire_conf x LEFT JOIN kiwire_brand z ON x.cloud_id = z.cloud_id");

if (!$kiw_total_data) die("Error on count tenants data.\n");

$kiw_total_data = $kiw_total_data->fetch_assoc()["cloud_id"];

writeLog($transaction_time, "Total row tenants [ ". $kiw_total_data ." ]");
echo "Total row tenants [ ". $kiw_total_data ." ] .. \n";


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

        $kiw_clouds = $kiw_v2->query("SELECT 
                                DISTINCT(x.cloud_id) AS cloud_id, 
                                z.customer AS name 
                                FROM kiwire_conf x 
                                LEFT JOIN kiwire_brand z 
                                ON x.cloud_id = z.cloud_id
                                LIMIT {$kiw_conf["limit"]} OFFSET {$kiw_conf['offset']}");

        $kiw_total_row  = count($kiw_clouds);
        
        if ($kiw_total_row <= 0) {

            writeLog($transaction_time, "No data in block  [ {$i} / {$kiw_conf['block']} ]  ");
            echo "No data in block  [ {$i} / {$kiw_conf['block']} ]  \n";
    
        }

        $kiw_curr_row   = 0;

        foreach($kiw_clouds as $kiw_cloud){

            
            $kiw_v3 = new Swoole\Coroutine\MySQL();
            $kiw_v3->connect([
                'host'      => DB_V3_HOST,
                'user'      => DB_V3_USER,
                'password'  => DB_V3_PASS,
                'database'  => DB_V3_NAME,
                'port'      => DB_V3_PORT
            ]);
            
            go(function () use($kiw_conf, $transaction_time, $kiw_v3, &$kiw_curr_row, $kiw_cloud, $i, $kiw_total_row){   
                

                $kiw_curr_row++;
                echo "Processing Block [ {$i} / {$kiw_conf['block']} ] - Row {$kiw_curr_row} / {$kiw_total_row} ..\n";


                if($kiw_curr_row == $kiw_total_row) {

                    writeLog($transaction_time, "End Block  [ {$i} / {$kiw_conf['block']} ] - Total row [ {$kiw_curr_row} ] . Completed ..");
                    echo " Block  [ {$i} / {$kiw_conf['block']} ]  Completed . \n";
                }

                $kiw_temp = array();

                $kiw_temp['tenant_id']                      = $kiw_cloud['cloud_id'];
                $kiw_temp['name']                           = $kiw_cloud['name'];

                $kiw_temp['voucher_prefix']                 = strtoupper(substr(md5(time()), 6, 3) . "_");
                $kiw_temp['voucher_limit']                  = 5;
                $kiw_temp['campaign_wait_second']           = 15;
                $kiw_temp['campaign_multi_ads']             = "n";
                $kiw_temp['campaign_require_verification']  = "y";

                $kiw_temp['currency']                       = "MYR";
                $kiw_temp['timezone']                       = "Asia/Kuala_Lumpur";
                $kiw_temp['status']                         = "y";

                $kiw_temp['forgot_password_method']         = "sms";
                $kiw_temp['forgot_password_template']       = "sms";

                $kiw_temp['check_arrangement_login']        = "check_active,check_password,check_allow_simultaneous,check_allow_quota,check_allow_credit,reporting_process";

                $kiw_temp_query     = sql_insert($kiw_v3, "kiwire_clouds", $kiw_temp);

                $kiw_temp_result = $kiw_v3->query($kiw_temp_query);
                if(!$kiw_temp_result) {

                    writeLog($transaction_time, "Error : {$kiw_temp_query}");

                }
                

                unset($kiw_temp);
                unset($kiw_temp_result);

                // create custom directory

                if (file_exists("/var/www/kiwire/server/custom/{$kiw_cloud['cloud_id']}/") == false) {

                    mkdir("/var/www/kiwire/server/custom/{$kiw_cloud['cloud_id']}/", 0755, true);

                }


                if (file_exists("/var/www/kiwire/logs/{$kiw_cloud['cloud_id']}/") == false) {

                    mkdir("/var/www/kiwire/logs/{$kiw_cloud['cloud_id']}/", 0755, true);

                }

            });
            
        }


    });
    

    $kiw_conf['offset'] = $kiw_conf['offset'] + $kiw_conf["limit"];

}

echo count(Swoole\Coroutine::listCoroutines()), " active coroutines when reaching the end of the PHP script.\n";



