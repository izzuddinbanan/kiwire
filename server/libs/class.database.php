<?php

require_once dirname(__FILE__, 2) . "/admin/includes/include_config.php";
require_once dirname(__FILE__, 2) . "/admin/includes/include_general.php";

class Database {

    private $db_connection;
    private $query_link;
    private $db_permission;
    private $db_actions = ['SELECT', 'UPDATE', 'DELETE', 'INSERT'];

    private static $db_instance;

    public $db_affected_row;


    public function __construct(){

        if (isset($_SESSION['permission']) && !empty($_SESSION['permission'])) {

            $this->db_permission = $_SESSION['permission'];

        } else {

            $this->db_permission = "rw";

        }


    }


    public static function obtain(){

        if (!self::$db_instance){

            self::$db_instance = new Database();

        }

        return self::$db_instance;

    }


    public function connect(){

        $this->db_connection = new mysqli("p:" . SYNC_DB1_HOST, SYNC_DB1_USER, SYNC_DB1_PASSWORD, SYNC_DB1_DATABASE, SYNC_DB1_PORT);

        if ($this->db_connection->connect_errno){

            if (!empty(SYNC_DB2_HOST) && SYNC_DB2_HOST != SYNC_DB1_HOST) {


                $this->db_connection = new mysqli("p:" . SYNC_DB2_HOST, SYNC_DB2_USER, SYNC_DB2_PASSWORD, SYNC_DB2_DATABASE, SYNC_DB2_PORT);

                if ($this->db_connection->connect_errno) {

                    $this->error_message("Unable to connect to database");

                    sync_logger("Unable to connect to database", "general");

                }


            } else {

                $this->error_message("Unable to connect to database");

                sync_logger("Database connection fail", "general");

            }

        }

    }


    public function close(){

        $this->db_connection->close();

    }


    public function escape($string){

        return $this->db_connection->escape_string($string);

    }

    public function sanitize($string){

        $string  = str_replace(array('<','>','"','(',')','”', '‘',"'", '`', '%'), '', $string);
        return $this->db_connection->escape_string($string);

    }


    public function query($query_string){


        $query_string = ltrim(trim($query_string));

        $query_action = trim(strtoupper(substr($query_string, 0, 6)));


        if (in_array($query_action, $this->db_actions)) {

            if ($this->db_permission == "r") {

                foreach (array("UPDATE", "DELETE", "INSERT") as $command) {

                    if (strtoupper(substr($query_string, 0, strlen($command))) == $command) {

                        $this->error_message("Unauthorized action");

                        exit("You do not have permission to perform this action!");

                    }

                }

            } elseif ($this->db_permission == "w") {

                if (trim(strtoupper(substr($query_string, 0, 6))) == "SELECT") {

                    $this->error_message("Unauthorized action");

                    exit("You do not have permission to perform this action!");

                }

            }

            $this->query_link = $this->db_connection->query($query_string);

            $this->db_affected_row = $this->db_connection->affected_rows;

            if($this->db_affected_row > 0) return true; 

            return false;

        } else {

            exit("Please contact administrator to perform this action.");

        }

    }


    public function fetch(){

        return $this->query_link->fetch_assoc();

    }


    public function query_first($query_string){

        $this->query($query_string);

        if ($this->query_link){

            $db_data = $this->query_link->fetch_assoc();

            $this->query_link->free_result();

        } else {

            $db_data = false;

        }

        return $db_data;

    }


    public function fetch_array($query_string){

        $this->query($query_string);

        if ($this->query_link) {

            $db_data = $this->query_link->fetch_all(MYSQLI_ASSOC);

            $this->query_link->free_result();

        } else {

            $db_data = false;

        }

        return $db_data;

    }


    public function update($table, $data, $where){

        $query_string = "UPDATE `{$table}` SET ";

        foreach ($data as $key => $value) {

            if (strtolower(trim($value)) == "null") {
            // if (strtolower(trim($value)) == "null" || strtolower(trim($value)) == "") {

                $query_string .= "`{$key}` = NULL, ";

            } elseif (strtolower(trim($value)) == "now()"){

                $query_string .= "`{$key}` = NOW(), ";

            } else {

                $query_string .= "`{$key}` = '" . $this->escape(trim($value)) . "', ";

            }

        }

        $query_string .= " updated_date = NOW(), ";

        $query_string = substr($query_string, 0,-2);

        $query_string .= " WHERE " . $where;

        $this->query($query_string);

        return $this->query_link;

    }



    public function insert($table, $data){

        $query_value = "";

        $query_string = "INSERT INTO `{$table}`(";

        foreach ($data as $key => $value) {

            $query_string .= "`{$key}`, ";

            if (strtolower(trim($value)) == "null") {
            // if (strtolower(trim($value)) == "null" || strtolower(trim($value)) == "") {

                $query_value .= "NULL, ";

            } elseif (strtolower(trim($value)) == "now()"){

                $query_value .= "NOW(), ";

            } else {

                $query_value .= "'" . $this->escape(trim($value)) . "', ";

            }


        }

        $query_string = substr($query_string, 0,-2);
        $query_value = substr($query_value, 0,-2);

        $query_string .= ") VALUE({$query_value})";

        $this->query($query_string);

        
        return $this->query_link;

    }


    public function insert_id(){

        return $this->query_first("SELECT LAST_INSERT_ID()")['LAST_INSERT_ID()'];

    }


    public function error_message($message){

        file_put_contents( dirname(__FILE__, 3) . "/logs/sql_error.log", date("Y-m-d H:i:s") . " :: {$message} \n", FILE_APPEND);

    }



}

