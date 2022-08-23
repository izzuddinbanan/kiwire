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


    echo "Are you sure to import data from v2 to kiwire_profiles into this server 'Kiwire v3' [ y / n ] : ";

    $kiw_action = strtolower(trim(fread($kiw_input, 10)));

    if ($kiw_action == "n") die("\n");


}
unset($kiw_action);
unset($kiw_input);

system("rm -f logs/". AGENT_NAME . "*.log");

$kiw_v3->query("TRUNCATE TABLE kiwire_profiles");
$kiw_total_data = $kiw_v2->query("SELECT COUNT(*) AS total FROM (SELECT groupname  from radgroupreply GROUP BY groupname) AS groupname");

if (!$kiw_total_data) die("Error on count group name.\n");

$kiw_total_data = $kiw_total_data->fetch_assoc()["total"];

writeLog($transaction_time, "Total row group name [ ". $kiw_total_data ." ]");
echo "Total row group name [ ". $kiw_total_data ." ] .. \n";


$kiw_conf["limit"]  = 90000; 
$kiw_conf["offset"] = 0; 


$kiw_conf["block"] = $kiw_total_data / $kiw_conf["limit"];
$kiw_conf["block"] = round($kiw_conf["block"]) + 1;
// var_dump($kiw_conf["block"]);

$kiw_curr_row   = 0;

writeLog($transaction_time, "Split data into  [ ". $kiw_conf["block"] ." ] block ");
echo "Split data into  [ ". $kiw_conf["block"] ." ] block \n";

Swoole\Runtime::enableCoroutine();

for ($i=1; $i <= $kiw_conf["block"]; $i++) { 

    go(function () use($kiw_conf, $transaction_time, $i,&$kiw_curr_row, $kiw_v3, $kiw_v2){    


        
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

        $kiw_profiles = $kiw_v2->query("SELECT 
                                    groupname , cloud_id
                                    from radgroupreply 
                                    GROUP BY groupname,  cloud_id
                                    LIMIT {$kiw_conf["limit"]} OFFSET {$kiw_conf['offset']}");
        
        $kiw_total_row  = count($kiw_profiles);

        $kiw_data_profiles = [];
        if($kiw_total_row > 0){

            foreach($kiw_profiles as $key => $value){

                $kiw_data = $kiw_v2->query("SELECT * from radgroupreply WHERE groupname = '{$value["groupname"]}' AND cloud_id = '{$value["cloud_id"]}'");


                foreach ($kiw_data as $key_kiw_data => $val_kiw_data) {

                    
                    if($key_kiw_data == 0) {

                
                        $kiw_data_profiles[$key]["name"]        = $val_kiw_data["groupname"];
                        $kiw_data_profiles[$key]["price"]       = $val_kiw_data["price"];
                        $kiw_data_profiles[$key]["price"]       = $val_kiw_data["price"];
                        $kiw_data_profiles[$key]["tenant_id"]   = $val_kiw_data["cloud_id"];
                        $kiw_data_profiles[$key]["tenant_id"]   = $val_kiw_data["cloud_id"];
        
        
                        if ($val_kiw_data['kiwire_plan'] == "Session-Timeout")  $kiw_data_profiles[$key]["type"]        = "countdown";
                        if ($val_kiw_data['kiwire_plan'] == "Access-Period")    $kiw_data_profiles[$key]["type"]        = "expiration";
                        if ($val_kiw_data['kiwire_plan'] == "Free")             $kiw_data_profiles[$key]["type"]        = "free";
                        if ($val_kiw_data['kiwire_plan'] == "Payu")             $dakiw_data_profilesta[$key]["type"]    = "pay-per-use";
        
                        if($val_kiw_data["attribute"] == "Idle-Timeout")                $val_kiw_data["attribute"] = "reply:Idle-Timeout"; 
                        if($val_kiw_data["attribute"] == "WISPr-Bandwidth-Max-Down")    $val_kiw_data["attribute"] = "reply:WISPr-Bandwidth-Max-Down"; 
                        if($val_kiw_data["attribute"] == "WISPr-Bandwidth-Max-Up")      $val_kiw_data["attribute"] = "reply:WISPr-Bandwidth-Max-Up"; 
                        if($val_kiw_data["attribute"] == "WISPr-Bandwidth-Min-Up")      $val_kiw_data["attribute"] = "reply:WISPr-Bandwidth-Min-Up"; 
                        if($val_kiw_data["attribute"] == "WISPr-Bandwidth-Min-Down")    $val_kiw_data["attribute"] = "reply:WISPr-Bandwidth-Min-Down"; 
                        if($val_kiw_data["attribute"] == "Acct-Interim-Interval")       $val_kiw_data["attribute"] = "reply:Acct-Interim-Interval"; 
                        if($val_kiw_data["attribute"] == "Access-Period")               $val_kiw_data["attribute"] = "control:Access-Period"; 


                        $kiw_data_profiles[$key]["attribute"]   = [$val_kiw_data["attribute"] => $val_kiw_data["value"]];
        
                    }else {
        
                        if($val_kiw_data["attribute"] == "Idle-Timeout")                $val_kiw_data["attribute"] = "reply:Idle-Timeout"; 
                        if($val_kiw_data["attribute"] == "WISPr-Bandwidth-Max-Down")    $val_kiw_data["attribute"] = "reply:WISPr-Bandwidth-Max-Down"; 
                        if($val_kiw_data["attribute"] == "WISPr-Bandwidth-Max-Up")      $val_kiw_data["attribute"] = "reply:WISPr-Bandwidth-Max-Up"; 
                        if($val_kiw_data["attribute"] == "WISPr-Bandwidth-Min-Up")      $val_kiw_data["attribute"] = "reply:WISPr-Bandwidth-Min-Up"; 
                        if($val_kiw_data["attribute"] == "WISPr-Bandwidth-Min-Down")    $val_kiw_data["attribute"] = "reply:WISPr-Bandwidth-Min-Down"; 
                        if($val_kiw_data["attribute"] == "Acct-Interim-Interval")       $val_kiw_data["attribute"] = "reply:Acct-Interim-Interval"; 
                        if($val_kiw_data["attribute"] == "Access-Period")               $val_kiw_data["attribute"] = "control:Access-Period"; 
        
                        $kiw_data_profiles[$key]["attribute"]   = array_merge($kiw_data_profiles[$key]["attribute"], [$val_kiw_data["attribute"] => $val_kiw_data["value"]]);
        
                    }

                    

                }

            }

            $kiw_curr_row   = 0;

            foreach($kiw_data_profiles as $kiw_data_profile){
                
                $kiw_v3 = new Swoole\Coroutine\MySQL();
                $kiw_v3->connect([
                    'host' => DB_V3_HOST,
                    'user' => DB_V3_USER,
                    'password' => DB_V3_PASS,
                    'database' => DB_V3_NAME,
                    'port' => DB_V3_PORT
                ]);

                go(function () use($kiw_conf, $transaction_time, $kiw_v3, &$kiw_curr_row, $kiw_data_profile, $i, $kiw_total_row){   
                
                    $kiw_data_profile["attribute"] = json_encode($kiw_data_profile["attribute"]);

                    $kiw_curr_row++;
                    echo "Processing Block [ {$i} / {$kiw_conf['block']} ] - Row {$kiw_curr_row} / {$kiw_total_row} ..\n";
    
    
                    if($kiw_curr_row == $kiw_total_row) {
    
                        writeLog($transaction_time, "End Block  [ {$i} / {$kiw_conf['block']} ] - Total row [ {$kiw_curr_row} ] . Completed ..");
                        echo " Block  [ {$i} / {$kiw_conf['block']} ]  Completed . \n";
                    }
    
                    $kiw_temp_query     = sql_insert($kiw_v3, "kiwire_profiles", $kiw_data_profile);

                    $kiw_temp_result = $kiw_v3->query($kiw_temp_query);
                    if(!$kiw_temp_result) {
    
                        writeLog($transaction_time, "Error : {$kiw_temp_query}");
    
                    }
                    
    
                    unset($kiw_temp);
                    unset($kiw_temp_result);
                });
            }
            

        }else{

            writeLog($transaction_time, "No data in block  [ {$i} / {$kiw_conf['block']} ]  ");
            echo "No data in block  [ {$i} / {$kiw_conf['block']} ]  \n";
    
        }
    


    });
    

    $kiw_conf['offset'] = $kiw_conf['offset'] + $kiw_conf["limit"];

}

echo count(Swoole\Coroutine::listCoroutines()), " active coroutines when reaching the end of the PHP script.\n";



