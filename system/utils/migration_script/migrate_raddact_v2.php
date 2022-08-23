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



$kiw_action         = "";
$migrate_selection  = "";
$start              = "";
$end                = "";

$kiw_input = fopen("php://stdin", "r");

while (!in_array($kiw_action, array("y"))){

    echo "Are you sure to import data from v2 to kiwire_profiles into this server 'Kiwire v3' [ y / n ] : ";

    $kiw_action = strtolower(trim(fread($kiw_input, 10)));

    if ($kiw_action == "n") die("\n");

}

while (!in_array($migrate_selection, array("all", "certain"))){

    echo "Do you want to migrate all data or certain month only? [ all / certain ] : ";

    $migrate_selection = strtolower(trim(fread($kiw_input, 10)));

    

}

if($migrate_selection == "certain"){
        
    echo "Please enter start date to get data? [ eg : 01 ] : ";

    $start = strtolower(trim(fread($kiw_input, 10)));
    
}

if($start != ""){

    echo "Please enter end date to get data? [ eg : 03 ] : ";

    $end = strtolower(trim(fread($kiw_input, 10)));


}

unset($kiw_action);
unset($kiw_input);

system("rm -f logs/". AGENT_NAME . "*.log");


Swoole\Runtime::enableCoroutine();

go(function () use($kiw_conf, $transaction_time, $i,&$kiw_curr_row, $kiw_v3, $kiw_v2, $migrate_selection, $start, $end){    

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
        'host' => DB_V3_HOST,
        'user' => DB_V3_USER,
        'password' => DB_V3_PASS,
        'database' => DB_V3_NAME,
        'port' => DB_V3_PORT
    ]);

    echo "get all  raddact table.. \n";

    $tables = $kiw_v2->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'kiwire' AND table_name like  'radacct_archive%'");

    foreach($tables as $table){

        echo "GET list of month for table  {$table['table_name']} .. \n";

		$month_lists = $kiw_v2->query("SELECT acctstarttime as month_date FROM {$table['table_name']} GROUP BY extract(month from acctstarttime), extract(year from acctstarttime)");
        
        if($migrate_selection == 'certain'){

            $month_lists = $kiw_v2->query("SELECT acctstarttime as month_date FROM {$table['table_name']} WHERE MONTH(acctstarttime ) BETWEEN {$start} AND {$end}  GROUP BY extract(month from acctstarttime), extract(year from acctstarttime)");
            
        }


        foreach($month_lists as $month_list){

            if(date('Y',strtotime($month_list["month_date"])) == '1970') continue;

            $temp_data["year"] 			= date('Y',strtotime($month_list["month_date"]));
            $temp_data["month"] 		= date('n',strtotime($month_list["month_date"]));
            $temp_data["month_full"] 	= date('m',strtotime($month_list["month_date"]));

            $temp_data["count"] = $kiw_v2->query("SELECT count(radacctid) as ccount FROM {$table['table_name']} WHERE YEAR(acctstarttime) = '{$temp_data['year']}' AND MONTH(acctstarttime) = '{$temp_data['month']}'");

            $temp_data['count'] = $temp_data['count'][0]['ccount'];
            
            $temp_data['cur_count'] = 1;

            writeLog($transaction_time, "Total row data [ ". $temp_data['count'] ." ]");
            echo "Total row data [ ". $temp_data['count'] ." ] .. \n";

            ### PREVIOUS RECORD
                $check = $kiw_v3->query("SELECT count(id) as ccount FROM kiwire_sessions_{$temp_data['year']}{$temp_data['month_full']}");

                if($check) {
                    
                    $check = $check[0]['ccount'];
                    var_dump($check);

        
                    if ($check == $temp_data['count']) {
                        echo "previous process already done. skip table .. \n";
                        continue;
                    }
                }

            ### PREVIOUS RECORD

            $kiw_conf["limit"]  = 90000; 
            $kiw_conf["offset"] = 0;

            $kiw_conf["block"] = $temp_data['count'] / $kiw_conf["limit"];
            $kiw_conf["block"] = round($kiw_conf["block"]) + 1;

        
            writeLog($transaction_time, "Split data into  [ ". $kiw_conf["block"] ." ] block ");
            echo "Split data into  [ ". $kiw_conf["block"] ." ] block \n";

            for ($i=1; $i <= $kiw_conf["block"]; $i++) {

                writeLog($transaction_time, "Start block  [ {$i} / {$kiw_conf['block']} ]  ");
                echo "Start block  [ {$i} / {$kiw_conf['block']} ] \n";

                $data_lists = $kiw_v2->query("SELECT 
                                            radacctid as id,
                                            cloud_id as tenant_id,
                                            acctupdatetime as updated_date,
                                            acctsessionid as session_id,
                                            acctuniqueid as unique_id,
                                            calledstationid as controller,
                                            callingstationid as controller_ip,
                                            zone as zone,
                                            username as username,
                                            -- framedipaddress as mac_address,
                                            framedipaddress as ip_address,
                                            -- framedipaddress as profile,
                                            acctstarttime as session_table,
                                            acctstarttime as start_time,
                                            acctstoptime as stop_time,
                                            acctsessiontime as session_time,
                                            acctinputoctets as quota_in,
                                            acctoutputoctets as quota_out,
                                            acctterminatecause as terminate_reason,
                                            -- acctterminatecause as avg_speed,
                                            dclass as system,
                                            dclass as class,
                                            dbrand as brand,
                                            dmodel as model,
                                            dhostname as hostname,
                                            dhostname as ipv6_address
                                            FROM {$table['table_name']} 
                                            WHERE YEAR(acctstarttime) = '{$temp_data['year']}' 
                                            AND MONTH(acctstarttime) = '{$temp_data['month']}'
                                            LIMIT {$kiw_conf["limit"]} OFFSET {$kiw_conf['offset']}");
                
                $kiw_total_row  = count($data_lists);

                $kiw_curr_row   = 0;


                foreach($data_lists as $data_list){

                    $kiw_v3 = new Swoole\Coroutine\MySQL();
                    $kiw_v3->connect([
                        'host' => DB_V3_HOST,
                        'user' => DB_V3_USER,
                        'password' => DB_V3_PASS,
                        'database' => DB_V3_NAME,
                        'port' => DB_V3_PORT
                    ]);

                    go(function () use($kiw_conf, $transaction_time, $kiw_v3, &$kiw_curr_row, $data_list, $i, $kiw_total_row, $temp_data, $table){ 

                        $kiw_curr_row++;
                        
                        
                        if($kiw_curr_row == $kiw_total_row) {
                            
                            writeLog($transaction_time, "End Block  [ {$i} / {$kiw_conf['block']} ] - Total row [ {$kiw_curr_row} ] . Completed ..");
                            echo " Block  [ {$i} / {$kiw_conf['block']} ]  Completed . \n";
                        }
                        
                        $data_list["mac_address"] 	= NULL;
                        $data_list["profile"] 		= NULL;
                        $data_list["session_table"] = "kiwire_sessions_" . date("Ym", strtotime($data_list["session_table"]));
                        
                        echo "Processing Block [ {$i} / {$kiw_conf['block']} ] - Row {$kiw_curr_row} / {$kiw_total_row}  - table {$table['table_name']} to table {$data_list["session_table"]}..\n";
    
                        $check_table_V3 = $kiw_v3->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'kiwire_migrate' AND table_name = '{$data_list["session_table"]}'");
    
                        if($check_table_V3->num_rows == 0) $kiw_v3->query("CREATE TABLE {$data_list["session_table"]} LIKE kiwire_session_template");
    
    
                        $kiw_temp_query     = sql_insert($kiw_v3, $data_list["session_table"], $data_list);

                        $kiw_temp_result = $kiw_v3->query($kiw_temp_query);
                        if(!$kiw_temp_result) {
        
                            writeLog($transaction_time, "Error : {$kiw_temp_query}");
        
                        }

                    });
                    
                    

                }


                $kiw_conf['offset'] = $kiw_conf['offset'] + $kiw_conf["limit"];
            }


        }

    }



});


echo count(Swoole\Coroutine::listCoroutines()), " active coroutines when reaching the end of the PHP script.\n";



