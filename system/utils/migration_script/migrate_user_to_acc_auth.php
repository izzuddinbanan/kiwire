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


    echo "Are you sure to import data from kiwire_user from v2 to kiwire_account_auth into this server 'Kiwire v3' [ y / n ] : ";

    $kiw_action = strtolower(trim(fread($kiw_input, 10)));

    if ($kiw_action == "n") die("\n");


}
unset($kiw_action);
unset($kiw_input);

system("rm -f logs/". AGENT_NAME . "*.log");

$kiw_v3->query("TRUNCATE TABLE kiwire_account_auth");
$kiw_total_data = $kiw_v2->query("SELECT count(user_id) AS total FROM kiwire_user");

if (!$kiw_total_data) die("Error on count user data.\n");

$kiw_total_data = $kiw_total_data->fetch_assoc()["total"];

writeLog($transaction_time, "Total row user [ ". $kiw_total_data ." ]");
echo "Total row user [ ". $kiw_total_data ." ] .. \n";


$kiw_conf["limit"]  = 50000; 
$kiw_conf["offset"] = 0; 


$kiw_conf["block"] = $kiw_total_data / $kiw_conf["limit"];
$kiw_conf["block"] = round($kiw_conf["block"]) + 1;
// var_dump($kiw_conf["block"]);

writeLog($transaction_time, "Split data into  [ ". $kiw_conf["block"] ." ] block ");
echo "Split data into  [ ". $kiw_conf["block"] ." ] block \n";

Swoole\Runtime::enableCoroutine();

$kiw_tables = $kiw_v2->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'kiwire' AND table_name like  'radacct%'");


for ($i=1; $i <= $kiw_conf["block"]; $i++) { 

    go(function () use($kiw_conf, $transaction_time, $i, $kiw_v3, $kiw_v2, $kiw_tables){    

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

        $kiw_users = $kiw_v2->query("SELECT 
                                username, 
                                fullname, 
                                remark, 
                                who AS creator, 
                                price, 
                                plan AS profile_subs, 
                                plan AS profile_curr, 
                                createdate AS date_create, 
                                valuedate AS date_value, 
                                status, 
                                allownas AS allowed_zone, 
                                expiry AS date_expiry, 
                                oldpass AS password, 
                                mac AS allowed_mac, 
                                tag AS bulk_id, 
                                actdate AS date_activate, 
                                IF(prepaid = 'y', 'voucher', 'account') AS ktype, 
                                edx_auth AS integration, 
                                email AS email_address, 
                                phone AS phone_number, 
                                cloud_id AS tenant_id, 
                                last_pass_chg AS date_password
                                FROM kiwire_user
                                where status = 'act'
                                LIMIT {$kiw_conf["limit"]} OFFSET {$kiw_conf['offset']}");

        $kiw_total_row  = count($kiw_users);

        if ($kiw_total_row <= 0) {
            
            writeLog($transaction_time, "No data in block  [ {$i} / {$kiw_conf['block']} ]  ");
            echo "No data in block  [ {$i} / {$kiw_conf['block']} ]  \n";
            
        }

        $kiw_curr_row   = 0;


        foreach($kiw_users as $raw_user){

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
            
            go(function () use($kiw_conf, $transaction_time, $kiw_v3, &$kiw_curr_row, $raw_user, $i, $kiw_total_row, $kiw_tables, $kiw_v2){   
                

                $kiw_curr_row++;
                echo "Processing Block [ {$i} / {$kiw_conf['block']} ] - Row {$kiw_curr_row} / {$kiw_total_row} ..\n";

                if($kiw_curr_row == $kiw_total_row) {

                    writeLog($transaction_time, "End Block  [ {$i} / {$kiw_conf['block']} ] - Total row [ {$kiw_curr_row} ] . Completed ..");
                    echo " Block  [ {$i} / {$kiw_conf['block']} ]  Completed . \n";
                }


                $kiw_temp = array();

                $kiw_temp['id']                  = "NULL";
                $kiw_temp['tenant_id']           = $raw_user['tenant_id'];
                $kiw_temp['updated_date']        = "NOW()";
                $kiw_temp['creator']             = $raw_user['creator'];
                $kiw_temp['username']            = $raw_user['username'];
                $kiw_temp['fullname']            = $raw_user['fullname'];
                $kiw_temp['email_address']       = $raw_user['email_address'];
                $kiw_temp['phone_number']        = $raw_user['phone_number'];
                $kiw_temp['password']            = sync_encrypt($raw_user['password']);
                $kiw_temp['remark']              = $raw_user['remark'];
                $kiw_temp['profile_subs']        = $raw_user['profile_subs'];
                $kiw_temp['profile_curr']        = $raw_user['profile_curr'];
                $kiw_temp['price']               = $raw_user['price'];
                $kiw_temp['ktype']               = $raw_user['ktype'];
                $kiw_temp['bulk_id']             = $raw_user['bulk_id'];
                $kiw_temp['status']              = $raw_user['status'];
                $kiw_temp['allowed_zone']        = $raw_user['allowed_zone'];
                $kiw_temp['allowed_mac']         = $raw_user['allowed_mac'];
                $kiw_temp['date_create']         = $raw_user['date_create'];
                $kiw_temp['date_value']          = $raw_user['date_value'];
                $kiw_temp['date_expiry']         = $raw_user['date_expiry'];
                $kiw_temp['date_last_login']     = "NOW()";
                $kiw_temp['date_last_logout']    = "NOW()";
                $kiw_temp['date_activate']       = $raw_user['date_activate'];
                $kiw_temp['date_remove']         = $raw_user['date_remove'];
                $kiw_temp['date_password']       = $raw_user['date_password'];

                if ($raw_user['integration'] == "int") $kiw_temp['integration'] = "internal";


                if($kiw_temp["status"] == "act") $kiw_temp["status"] = "active";
                if($kiw_temp["status"] == "exp") $kiw_temp["status"] = "expired";
                if($kiw_temp["status"] == "sus") $kiw_temp["status"] = "suspend";

                $account_users = [];
                foreach ($kiw_tables as $kiw_table) {
                    
                    $kiw_connection = $kiw_v2->query("SELECT username, SUM(acctsessiontime) AS session_time, SUM(acctinputoctets) AS quota_in, SUM(acctoutputoctets) AS quota_out, MIN(acctstarttime) AS date_activate FROM {$kiw_table["table_name"]} WHERE cloud_id = '{$kiw_temp['tenant_id']}' AND username = '{$kiw_temp['username']}'");

                    if($kiw_connection[0]["username"] != NULL) {
                        

                        $account_users["session_time"]  += ($kiw_connection["session_time"] ? $kiw_connection["session_time"] : 0);
                        $account_users["quota_in"]      += ($kiw_connection["quota_in"]     ? $kiw_connection["quota_in"] : 0);
                        $account_users["quota_out"]     += ($kiw_connection["quota_out"]    ? $kiw_connection["quota_out"] : 0);


                        if(empty($account_user["date_activate"])) $account_users["date_activate"] = $kiw_connection["date_activate"];
                        
                        if(strtotime($kiw_connection["date_activate"]) < strtotime($account_users["date_activate"])) $account_users["date_activate"] = $kiw_connection["date_activate"];



                    }
                    # code...
                }

                if(!empty($account_users)){

                    $kiw_temp["quota_in"]       = $account_users["quota_in"];
                    $kiw_temp["quota_out"]      = $account_users["quota_out"];
                    $kiw_temp["session_time"]   = $account_users["session_time"];
                    $kiw_temp["date_activate"]  = $account_users["date_activate"];

                }

                $kiw_temp_query     = sql_insert($kiw_v3, "kiwire_account_auth", $kiw_temp);

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



