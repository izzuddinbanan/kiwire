<?php

class kiw_db_conn {


    private $kiw_db;
    private $ping;


    public function __construct($kiw_config = ""){


        $this->kiw_db = new mysqli("p:{$kiw_config['host']}", $kiw_config['user'], $kiw_config['password'], $kiw_config['database'], $kiw_config['port']);

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

        if ((time() - $this->ping) >= 900) {


            $this->ping = time();

            return $this->kiw_db->ping();


        } else  return true;

    }


}

