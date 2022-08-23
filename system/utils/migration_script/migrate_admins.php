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


    echo "Are you sure to import data from kiwire_admin from v2 to this server 'Kiwire v3' [ y / n ] : ";

    $kiw_action = strtolower(trim(fread($kiw_input, 10)));

    if ($kiw_action == "n") die("\n");


}
unset($kiw_action);
unset($kiw_input);

system("rm -f logs/". AGENT_NAME . "*.log");

$kiw_v3->query("TRUNCATE TABLE kiwire_admin");
$kiw_total_data = $kiw_v2->query("SELECT count(admin_id) as total_admin FROM kiwire_admin");

if (!$kiw_total_data) die("Error on count admin data.\n");

$kiw_total_data = $kiw_total_data->fetch_assoc()["total_admin"];

writeLog($transaction_time, "Total row in kiwire_admin [ ". $kiw_total_data ." ]");
echo "Total row in kiwire_admin [ ". $kiw_total_data ." ] .. \n";


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

        $kiw_admins = $kiw_v2->query("SELECT 
                                username, 
                                password, 
                                groupname, 
                                fullname, 
                                lastlogin, 
                                email, 
                                monitor, 
                                cloud_id AS tenant_id, 
                                temp_pass, 
                                permission, 
                                bal_credit AS balance_credit, 
                                last_change_pass, 
                                last_pass, 
                                first_login, 
                                'default' AS tenant_default
                                FROM kiwire_admin LIMIT {$kiw_conf["limit"]} OFFSET {$kiw_conf['offset']}");

        $kiw_total_row  = count($kiw_admins);
        
        if ($kiw_total_row <= 0) {

            writeLog($transaction_time, "No data in block  [ {$i} / {$kiw_conf['block']} ]  ");
            echo "No data in block  [ {$i} / {$kiw_conf['block']} ]  \n";
    
        }

        $kiw_curr_row   = 0;

        foreach($kiw_admins as $kiw_admin){

            
            $kiw_v3 = new Swoole\Coroutine\MySQL();
            $kiw_v3->connect([
                'host' => DB_V3_HOST,
                'user' => DB_V3_USER,
                'password' => DB_V3_PASS,
                'database' => DB_V3_NAME,
                'port' => DB_V3_PORT
            ]);
            
            go(function () use($kiw_conf, $transaction_time, $kiw_v3, &$kiw_curr_row, $kiw_admin, $i, $kiw_total_row){   
                

                $kiw_curr_row++;
                echo "Processing Block [ {$i} / {$kiw_conf['block']} ] - Row {$kiw_curr_row} / {$kiw_total_row} ..\n";


                if($kiw_curr_row == $kiw_total_row) {

                    writeLog($transaction_time, "End Block  [ {$i} / {$kiw_conf['block']} ] - Total row [ {$kiw_curr_row} ] . Completed ..");
                    echo " Block  [ {$i} / {$kiw_conf['block']} ]  Completed . \n";
                }

                $kiw_temp = array();


                $kiw_temp['id']                 = "NULL";
                $kiw_temp['tenant_id']          = $kiw_admin['tenant_id'];
                $kiw_temp['updated_date']       = "NOW()";
                $kiw_temp['username']           = $kiw_admin['username'];
                $kiw_temp['password']           = sync_encrypt($kiw_admin['password']);
                $kiw_temp['groupname']          = "operator";
                $kiw_temp['fullname']           = $kiw_admin['fullname'];
                $kiw_temp['lastlogin']          = $kiw_admin['lastlogin'];
                $kiw_temp['email']              = $kiw_admin['email'];

                $kiw_temp['theme']              = $kiw_admin['theme'];
                $kiw_temp['monitor']            = $kiw_admin['monitor'];
                $kiw_temp['temp_pass']          = $kiw_admin['temp_pass'];
                $kiw_temp['permission']         = $kiw_admin['permission'];
                $kiw_temp['balance_credit']     = $kiw_admin['balance_credit'];
                $kiw_temp['last_change_pass']   = $kiw_admin['last_change_pass'];
                $kiw_temp['first_login']        = $kiw_admin['first_login'];
                $kiw_temp['tenant_default']     = $kiw_admin['tenant_default'];


                $kiw_temp_query     = sql_insert($kiw_v3, "kiwire_admin", $kiw_temp);

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



