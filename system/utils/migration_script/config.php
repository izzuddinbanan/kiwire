<?php

define(DB_V3_HOST, "127.0.0.1");
define(DB_V3_USER, "root");
define(DB_V3_PASS, "");
define(DB_V3_NAME, "kiwire_migrate");
define(DB_V3_PORT, "3306");




echo "Check v2 server DB connection.. ";
define(DB_V2_HOST, "192.168.0.46");
define(DB_V2_USER, "user_migrate");
define(DB_V2_PASS, "");
define(DB_V2_NAME, "kiwire_v2");
define(DB_V2_PORT, "3306");


define(MEMORY_LIMIT, "64G");




function writeLog($transaction_time, $message){

    file_put_contents("logs/" . AGENT_NAME . "-" . $transaction_time  . ".log", date("Y-m-d H:i:s") . "::" . $message . "\n", FILE_APPEND);

}




function sql_insert($kiw_db, $kiw_table, $kiw_data){

    $query_value = "";

    $query_string = "INSERT INTO `{$kiw_table}`(";


    foreach ($kiw_data as $key => $value) {


        $query_string .= "`{$key}`, ";

        if (strtolower(trim($value)) == "null_time") {
            $query_value .= "NULL, ";
        }
        elseif (strtolower(trim($value)) == "null") {

            $query_value .= "NULL, ";

        } elseif (strtolower(trim($value)) == "now()"){

            $query_value .= "NOW(), ";

        } else {

            $query_value .= "'" . trim($value) . "', ";


        }


    }


    $query_string = substr($query_string, 0,-2);
    $query_value = substr($query_value, 0,-2);

    $query_string .= ") VALUE({$query_value})";

    return $query_string;


}


function sync_encrypt($raw_string) {

    return base64_encode(openssl_encrypt($raw_string, "AES-256-CBC", "2GBrx3*mcQF7", 0, "h9m9knts_ARNh9m9"));

}