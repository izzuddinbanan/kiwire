<?php

require_once "config.php";

define(AGENT_NAME, basename(__FILE__, '.php'));

ini_set("memory_limit", MEMORY_LIMIT);



$transaction_time = date("Y-m-d H:i:s");
// fix_logger($transaction_time, "Start process.");


echo "Check v3 server DB connection.. ";
$kiw_v3 = new mysqli(DB_V3_HOST, DB_V3_USER, DB_V3_PASS, DB_V3_NAME, DB_V3_PORT);
if ($kiw_v3->connect_errno) die("Kiwire: Unable to connect to database.\n");
echo "OK\n============\n";



echo "Check v2 server DB connection.. ";
$kiw_v2 = new mysqli(DB_V2_HOST, DB_V2_USER, DB_V2_PASS, DB_V2_NAME, DB_V2_PORT);
if ($kiw_v2->connect_errno) die("Kiwire: Unable to connect to database.\n");
echo "OK\n============\n";



// $kiw_action = "";


$kiw_input = fopen("php://stdin", "r");


// while (!in_array($kiw_action, array("y"))){


//     echo "Are you sure to update data from kiwire_user from v2 to kiwire_account_auth into this server 'Kiwire v3' [ y / n ] : ";

//     $kiw_action = strtolower(trim(fread($kiw_input, 10)));

//     if ($kiw_action == "n") die("\n");


// }
unset($kiw_action);
unset($kiw_input);


system("rm -f logs/". AGENT_NAME . "*.log");


$kiw_total_data = $kiw_v3->query("SELECT count(id) AS total FROM kiwire_account_auth");

// var_dump($kiw_total_data);
// die();

if (!$kiw_total_data) die("Error on count user data.\n");

$kiw_total_data = $kiw_total_data->fetch_assoc()["total"];


// fix_logger($transaction_time, "Total row user [ ". $kiw_total_data ." ]");
echo "Total row user [ ". $kiw_total_data ." ] .. \n";


$kiw_conf["limit"]  = 50000; 
$kiw_conf["offset"] = 0; 


$kiw_conf["block"] = $kiw_total_data / $kiw_conf["limit"];
$kiw_conf["block"] = round($kiw_conf["block"]) + 1;
// var_dump($kiw_conf["block"]);


// fix_logger($transaction_time, "Split data into  [ ". $kiw_conf["block"] ." ] block ");
echo "Split data into  [ ". $kiw_conf["block"] ." ] block \n";

Swoole\Runtime::enableCoroutine();

// $kiw_tables = $kiw_v2->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'kiwire' AND table_name like  'radacct%'");


$kiw_tables = $kiw_v2->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'kiwire' AND table_name in ('radacct', 'radacct_archive')");


for ($i=1; $i <= $kiw_conf["block"]; $i++) { 

    go(function () use($kiw_conf, $transaction_time, $i, $kiw_v3, $kiw_v2, $kiw_tables){    

        $kiw_v3 = new Swoole\Coroutine\MySQL();

        $kiw_v3->connect([
            'host'      => DB_V3_HOST,
            'user'      => DB_V3_USER,
            'password'  => DB_V3_PASS,
            'database'  => DB_V3_NAME,
            'port'      => DB_V3_PORT
        ]);


        // fix_logger($transaction_time, "Start block  [ {$i} / {$kiw_conf['block']} ]  ");
        echo "Start block  [ {$i} / {$kiw_conf['block']} ] \n";

        $kiw_user_v3 = $kiw_v3->query("SELECT 
                                username, 
                                quota_in, 
                                quota_out,
                                session_time 
                                FROM kiwire_account_auth
                                WHERE status = 'active'
                                LIMIT {$kiw_conf["limit"]} OFFSET {$kiw_conf['offset']}");

        $kiw_total_row  = count($kiw_user_v3);

        if ($kiw_total_row <= 0) {
            

            // fix_logger($transaction_time, "No data in block  [ {$i} / {$kiw_conf['block']} ]  ");
            echo "No data in block  [ {$i} / {$kiw_conf['block']} ]  \n";

            
        }

        $kiw_curr_row   = 0;


        foreach($kiw_user_v3 as $user_v3){

            $kiw_v2 = new Swoole\Coroutine\MySQL();
            $kiw_v2->connect([
                'host'      => DB_V2_HOST,
                'user'      => DB_V2_USER,
                'password'  => DB_V2_PASS,
                'database'  => DB_V2_NAME,
                'port'      => DB_V2_PORT
            ]);
            
            $kiw_v3 = new Swoole\Coroutine\MySQL();
            $kiw_v3->connect([
                'host'      => DB_V3_HOST,
                'user'      => DB_V3_USER,
                'password'  => DB_V3_PASS,
                'database'  => DB_V3_NAME,
                'port'      => DB_V3_PORT
            ]);
            
            go(function () use($kiw_conf, $transaction_time, $kiw_v3, &$kiw_curr_row, $user_v3, $i, $kiw_total_row, $kiw_tables, $kiw_v2){   
                

                $kiw_curr_row++;
                echo "Processing Block [ {$i} / {$kiw_conf['block']} ] - Row {$kiw_curr_row} / {$kiw_total_row} ..\n";

                if($kiw_curr_row == $kiw_total_row) {

                    // fix_logger($transaction_time, "End Block  [ {$i} / {$kiw_conf['block']} ] - Total row [ {$kiw_curr_row} ] . Completed ..");
                    echo " Block  [ {$i} / {$kiw_conf['block']} ]  Completed . \n";
                }


                $kiw_temp = array();

                $kiw_temp['tenant_id']           = $user_v3['tenant_id'];
                $kiw_temp['username']            = $user_v3['username'];

                $account_users['session_time']   = ($user_v3['session_time'] ? $user_v3["session_time"] : 0);
                $account_users['quota_in']       = ($user_v3['quota_in'] ? $user_v3["quota_in"] : 0);
                $account_users['quota_out']      = ($user_v3['quota_out'] ? $user_v3["quota_out"] : 0);


                foreach ($kiw_tables as $kiw_table) {
                    
                    $kiw_connection = $kiw_v2->query("SELECT username, SUM(acctsessiontime) AS session_time, SUM(acctinputoctets) AS quota_in, SUM(acctoutputoctets) AS quota_out FROM {$kiw_table["table_name"]} WHERE cloud_id = '{$kiw_temp['tenant_id']}' AND username = '{$kiw_temp['username']}'");

                    if($kiw_connection[0]["username"] != NULL || $kiw_connection[0]["username"] != "") {
                        

                        $account_users["session_time"]  += ($kiw_connection["session_time"] ? $kiw_connection["session_time"] : 0);
                        $account_users["quota_in"]      += ($kiw_connection["quota_in"]     ? $kiw_connection["quota_in"] : 0);
                        $account_users["quota_out"]     += ($kiw_connection["quota_out"]    ? $kiw_connection["quota_out"] : 0);

                    }
                    # code...
                }

                // if(!empty($account_users["quota_in"])) {

                //     var_dump($kiw_temp['username']);
                //     var_dump($account_users);
                //     fix_logger($transaction_time, $kiw_temp['username'] . "==" . json_encode($account_users));

                // }
                $kiw_temp["quota_in"] = 0;
                $kiw_temp["quota_out"] = 0;
                $kiw_temp["session_time"] = 0;


                if(!empty($account_users)){

                    $kiw_temp["quota_in"]       = $account_users["quota_in"];
                    $kiw_temp["quota_out"]      = $account_users["quota_out"];
                    $kiw_temp["session_time"]   = $account_users["session_time"];

                }

                
                if($kiw_temp["quota_in"] > 0) {

                    $query  = "UPDATE kiwire_account_auth SET 
                    session_time = (session_time + ". (int) $kiw_temp["session_time"]  ."), 
                    quota_in = (quota_in + ". (int) $kiw_temp["quota_in"]  .") , 
                    quota_out = (quota_out + ". (int) $kiw_temp["quota_out"]  .")   
                    WHERE username='{$kiw_temp['username']}' LIMIT 1";


                    
                    $kiw_update_data = $kiw_v3->query($query);


                    if(!$kiw_update_data) {

                        fix_logger($transaction_time, $kiw_temp["username"] . " :: " . $query, "error");
                        echo "ERROR QUERY :: {$query}\n";

                    }else {

                        fix_logger($transaction_time, $kiw_temp["username"] . " :: " . $query, "success");
                        echo "SUCCESS QUERY\n";

                    }


                }else {

                    fix_logger($transaction_time, $kiw_temp["username"], "no-session");

                }
                // $kiw_update_data = $kiw_v3->query($query);

            

                // if(!$kiw_update_data) {

                //     fix_logger($transaction_time, "Error query - $kiw_update_data");
                //     echo "Error query ";
                //     die();


                // }


                
                unset($kiw_temp);
                unset($kiw_update_data);

            });
            
        }



    });
    

    $kiw_conf['offset'] = $kiw_conf['offset'] + $kiw_conf["limit"];

}

echo count(Swoole\Coroutine::listCoroutines()), " active coroutines when reaching the end of the PHP script.\n";



