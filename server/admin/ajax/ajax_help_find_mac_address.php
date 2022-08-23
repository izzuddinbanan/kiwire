<?php

$kiw['module'] = "Help -> Find Mac Address";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;


header("Content-Type: application/json");


require_once "../includes/include_session.php";
require_once "../includes/include_connection.php";
require_once "../includes/include_report.php";


if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}


$action = $_REQUEST['action'];


switch ($action) {

    case "get_login_history": get_login_history(); break;
    case "get_details": get_details(); break;
    default: echo "ERROR: Wrong implementation";

}


function get_login_history()
{


    global $kiw_db;


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_result = array();

        $kiw_mac = $kiw_db->escape($_REQUEST['mac_address']);

        if (empty($kiw_mac)){

            die(json_encode(array("status" => "failed", "message" => "Please provide a valid MAC address", "data" => null)));

        }


        foreach (range(0, 5) as $kiw_range) {


            $kiw_date = date("Ym", strtotime("-{$kiw_range} MONTH"));

            $kiw_table_name = "kiwire_sessions_{$kiw_date}";


            $kiw_temp_data = $kiw_db->fetch_array("SELECT * FROM {$kiw_table_name} WHERE mac_address = '{$kiw_mac}' AND tenant_id = '{$_SESSION['tenant_id']}'");


            if (is_array($kiw_temp_data)){


                $kiw_result = array_merge($kiw_result, $kiw_temp_data);

                
            }

           
        }


        unset($kiw_temp_data);


        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_result));

        
    }


}



function get_details()
{


    global $kiw_db;


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_result = array();

        $kiw_mac = $kiw_db->escape($_REQUEST['mac_address']);

        if (empty($kiw_mac)){

            die(json_encode(array("status" => "failed", "message" => "Please provide a valid MAC address", "data" => null)));

        }


        $kiw_result['info'] = $kiw_db->query_first("SELECT * FROM kiwire_device_history WHERE mac_address = '{$kiw_mac}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");

        $kiw_result['info']['details'] = json_decode($kiw_result['info']['details']);

        if ($kiw_result['info']['details'] != NULL){

            echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_result));
            
        } else {

            echo json_encode(array("status" => "failed", "message" => "No data exists for this MAC address", "data" => null));

        }
        
    }

    
}
