<?php

$kiw['module'] = "Integration -> Database";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_general.php";
require_once "../includes/include_connection.php";

if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}

$action = $_REQUEST['action'];

switch ($action) {

    case "update": update(); break;
    case "test": test(); break;
    default: echo "ERROR: Wrong implementation";

}


function update()
{
    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));
        $data['enabled']            = (isset($_POST['enabled']) ? "y" : "n");
        $data['host']               = $kiw_db->escape($_POST['host']);
        $data['user']               = $kiw_db->sanitize($_POST['user']);
        $data['pass']               = $kiw_db->escape($_POST['pass']);
    
        $data['dbname']             = $kiw_db->sanitize($_POST['dbname']);
        $data['dbtype']             = $kiw_db->escape($_POST['dbtype']);
        $data['command']            = $kiw_db->escape($_POST['command']);
        $data['variables']          = htmlentities($_POST['variables']);
      
        $data['profile']            = $kiw_db->escape($_POST['profile']);
        $data['allowed_zone']       = $kiw_db->escape($_POST['allowed_zone']);
        $data['validity']           = $kiw_db->escape($_POST['validity']);
        $data['port']               = $kiw_db->sanitize($_POST['port']);
       
        $data['tenant_id']          = $tenant_id;
        $data['updated_date']       = date('Y-m-d H-i-s');

        // $data['is_24_hour']     = isset($_REQUEST['is_24']) && $_REQUEST['is_24'] ? 1 : 0;
        // $data['start_time']     = $kiw_db->escape($_REQUEST['start_time']);

        // if(!$data['is_24_hour']){
            
        //     if($_REQUEST['start_time'] == $_REQUEST['stop_time'])
        //         die(json_encode(array("status" => "failed", "message" => "ERROR: Stop Time cannot same as Start Time", "data" => null)));
        //     else
        //         $data['stop_time'] =   $kiw_db->escape($_REQUEST['stop_time']);

        // }

        
        if($kiw_db->update("kiwire_int_external_db", $data, "tenant_id = '{$tenant_id}'")){

            
            sync_logger("{$_SESSION['user_name']} updated Database setting", $_SESSION['tenant_id']);
            
            echo json_encode(array("status" => "success", "message" => "SUCCESS: Database integration has been updated", "data" => null));
        
        }
        else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }

    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
    }

}



function test()
{


    header("Content-Type: application/json");

    global $kiw_db, $tenant_id;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        $data['host']           = $_POST['host'];
        $data['port']           = $_POST['port'];
        $data['user']           = $_POST['user'];
        $data['pass']           = $_POST['pass'];
        $data['dbname']         = $_POST['dbname'];
        $data['dbtype']         = $_POST['dbtype'];


        foreach(["host", "port", "user", "pass", "dbname", "dbtype"] as $field) {

            if(empty($data[$field])) {
                
                die(json_encode(array("status" => "error", "message" => "ERROR: {$field} cannot be empty.", "data" => NULL)));
                
            }
            
        }


        //connection on sql server
        if($data['dbtype'] == 'mssql'){
            
            $serverName = $data['host']; //serverName\instanceName
            $connectionInfo = array( 
                "Database"  =>  $data['dbname'], 
                "UID"       =>  $data['user'], 
                "PWD"       =>  $data['pass']
            );

            $conn = sqlsrv_connect( $serverName, $connectionInfo);

            if( $conn ) {

                // sync_logger("{$_SESSION['user_name']} Test connection ext db Setting : Success ", $_SESSION['tenant_id']);

                die(json_encode(array("status" => "success", "message" => "SUCCESS: Connection database setting configure correctly", "data" => NULL)));



            }else{
                 
                // sync_logger("{$_SESSION['user_name']} Test connection ext db Setting : Failed ", $_SESSION['tenant_id']);

                die(json_encode(array("status" => "Error", "message" => sqlsrv_errors(), "data" => NULL)));

            }

        }
                
    }


}

