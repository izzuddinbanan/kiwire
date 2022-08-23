<?php


class kiw_db_conn {


    private $kiw_db;
    private $ping;


    public function __construct($kiw_config = ""){


        $this->kiw_db = new mysqli("p:{$kiw_config['host']}", $kiw_config['user'], $kiw_config['password'], $kiw_config['database']);

        $this->ping = time();


    }


    public function escape ($kiw_string){

        return $this->kiw_db->escape_string($kiw_string);

    }


    public function query($kiw_query){


        $kiw_data = $this->kiw_db->query($kiw_query);


        if (strtoupper(substr($kiw_query, 0, 6)) == "SELECT") {

            if ($kiw_data->num_rows > 0) {

                return $kiw_data->fetch_all(MYSQLI_ASSOC);

            } else return false;

        } else return true;


    }


    public function ping(){

        return $this->kiw_db->ping();

    }


}


require_once "/var/www/kiwire/server/admin/includes/include_config.php";


error_reporting(E_ALL);


$kiw_db_pool = new Swoole\Coroutine\Channel(64);


go(function () use ($kiw_db_pool){


    for($kiw_x = 0; $kiw_x < 64; $kiw_x++){


        $kiw_db = new kiw_db_conn(array('host' => SYNC_DB1_HOST, 'user' => SYNC_DB1_USER, 'password' => SYNC_DB1_PASSWORD, 'database' => SYNC_DB1_DATABASE, 'port' => SYNC_DB1_PORT));

        $kiw_db_pool->push($kiw_db);


    }


    unset($kiw_db);


});


go(function () use ($kiw_db_pool) {


    for($kiw_x = 0; $kiw_x < 256; $kiw_x++){


        go(function () use ($kiw_x, $kiw_db_pool) {


            $kiw_db = $kiw_db_pool->pop(1);

            echo "swoole: [ {$kiw_x} ] " . json_encode($kiw_db->query("SELECT * FROM kiwire_clouds LIMIT 1")) . "\n";

            $kiw_db_pool->push($kiw_db);


        });

    }


});


