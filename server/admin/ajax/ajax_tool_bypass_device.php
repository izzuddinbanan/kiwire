<?php


$kiw['module'] = "Tools -> Bypass Device";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;


header("Content-Type: application/json");


require_once "../includes/include_session.php";
require_once "../includes/include_general.php";
require_once "../includes/include_connection.php";

require_once "../../libs/class.api.mikrotik.php";


if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}


$action = $_REQUEST['action'];


switch ($action) {

    case "get_data": get_data(); break;
    case "delete_data": delete_data(); break;
    case "create_data": create_data(); break;

    default: echo "ERROR: Wrong implementation";

}


function get_data(){


    global $kiw_db;


    $kiw_id = $kiw_db->escape($_REQUEST['controller']);

    $kiw_type = $kiw_db->escape($_REQUEST['type']);


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_controller = $kiw_db->query_first("SELECT * FROM kiwire_controller WHERE tenant_id = '{$_SESSION['tenant_id']}' AND unique_id = '{$kiw_id}' LIMIT 1");


        if (!empty($kiw_controller) && !empty($kiw_controller['device_ip']) && !empty($kiw_controller['username'])){


            // contact controller and get all device list

            $kiw_api = new routeros_api();

            $kiw_api->connect($kiw_controller['device_ip'], $kiw_controller['username'], $kiw_controller['password']);


            if ($kiw_type == "host") {

                $kiw_api->write("/ip/hotspot/host/print", false);
                $kiw_api->write("=without-paging=");

                $kiw_read = $kiw_api->read(false);
                $kiw_array = $kiw_api->parse_response($kiw_read);

            } elseif ($kiw_type == "bound") {

                $kiw_api->write("/ip/hotspot/ip-binding/print", false);
                $kiw_api->write("=without-paging=");

                $kiw_read = $kiw_api->read(false);
                $kiw_array = $kiw_api->parse_response($kiw_read);

            }

            $kiw_api->write("/queue/simple/print", false);
            $kiw_api->write("=without-paging=");

            $kiw_read = $kiw_api->read(false);
            $kiw_queues = $kiw_api->parse_response($kiw_read);


            foreach($kiw_array as $key => $mac_list) {

                $kiw_array[$key]['speed'] = "NULL";

                if(isset($mac_list['mac-address']) && !strpos($mac_list['address'], '-')) {

                    foreach ($kiw_queues as $key_queue => $kiw_speed) {
        
                        if($kiw_speed['name'] == "BYPASS_{$mac_list['mac-address']}") 
                        $kiw_array[$key]['speed'] = $kiw_speed['max-limit'] / 1000000;
        
                    }
        
               }else {
        
                    if(!strpos($mac_list['address'], '-')) {  // IP NOT RANGE 
                        
                        foreach ($kiw_queues as $key_queue => $kiw_speed) {
        
                            if($kiw_speed['name'] == "BYPASS_{$mac_list['address']}") 
                            $kiw_array[$key]['speed'] = $kiw_speed['max-limit'] / 1000000;
            
                        }
        
                    }else {
        
                        $kiw_data['ip'] = $mac_list['address'];
                        $kiw_data['ip'] = str_replace(" ","", $kiw_data['ip']);
                        $kiw_data['ip'] = str_replace("-",",", $kiw_data['ip']);
                        $kiw_data['ip'] = explode(",",  $kiw_data['ip']);
                        
                        $start_ip = $kiw_data["ip"][0];
        
                        foreach ($kiw_queues as $key_queue => $kiw_speed) {
        
                            if($kiw_speed['name'] == "BYPASS_{$start_ip}") 
                            $kiw_array[$key]['speed'] = $kiw_speed['max-limit'] / 1000000;
            
                        }
        
                    }
        
               }
            }


            $kiw_api->disconnect();

            echo json_encode(array("status" => "success", "message" => "", "type" => $kiw_type, "data" => $kiw_array));


        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Invalid controller identity provided", "data" => null));

        }


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}


function delete_data(){


    global $kiw_db;


    $kiw_id = $kiw_db->escape($_REQUEST['controller']);


    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $kiw_controller = $kiw_db->query_first("SELECT * FROM kiwire_controller WHERE tenant_id = '{$_SESSION['tenant_id']}' AND unique_id = '{$kiw_id}' LIMIT 1");


        if (!empty($kiw_controller)){


            $kiw_id = $kiw_db->escape($_REQUEST['id']);



            $kiw_api = new routeros_api();

            $kiw_api->connect($kiw_controller['device_ip'], $kiw_controller['username'], $kiw_controller['password']);


            $kiw_api->write("/ip/hotspot/ip-binding/remove", false);
            $kiw_api->write("=numbers={$kiw_id}");

            $kiw_api->read(false);


            $mac_address = $kiw_db->escape($_REQUEST['mac']);
            $ip_address = $kiw_db->escape($_REQUEST['ip']);

            if($mac_address == 'undefined') $mac_address = NULL;
            if($ip_address == 'undefined') $ip_address = NULL;


            if( !empty($mac_address) && !strpos($ip_address, '-')) {
                
                $kiw_api->write("/queue/simple/remove", false);
                $kiw_api->write("=numbers=BYPASS_{$mac_address}");

                $kiw_read = $kiw_api->read(false);
                $kiw_array = $kiw_api->parse_response($kiw_read);
    
            }else {


                if(strpos($ip_address, '-')) {

                    $kiw_data['ip'] = str_replace(" ","", $ip_address);
                    $kiw_data['ip'] = str_replace("-",",", $kiw_data['ip']);
                    $kiw_data['ip'] = explode(",",  $kiw_data['ip']);
                    
                    $start_ip = $kiw_data["ip"][0];
                    $end_ip   = $kiw_data["ip"][1];

                    $start_ip_arr = explode(".",$start_ip);
                    $start_ip = end($start_ip_arr);

                    $end_ip_arr = explode(".",$end_ip);
                    $end_ip = end($end_ip_arr);

                    if($start_ip < $end_ip) {

                        array_pop($start_ip_arr);
                        
                        for ($i=$start_ip; $i <= $end_ip; $i++) { 

                            $massage_ip = implode(".", $start_ip_arr) . "." . $i;

                            $kiw_api->write("/queue/simple/remove", false);
                            $kiw_api->write("=numbers=BYPASS_{$massage_ip}");

                            $kiw_read = $kiw_api->read(false);
                            $kiw_array = $kiw_api->parse_response($kiw_read);

                        }
                        

                    }


                }else {

                    $kiw_api->write("/queue/simple/remove", false);
                    $kiw_api->write("=numbers=BYPASS_{$ip_address}");

                    $kiw_api->read(false);

                }


            }


            $kiw_api->disconnect();

            echo json_encode(array("status" => "success", "message" => "SUCCESS: Device has been removed from bypass list", "data" => null));


        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Invalid controller identity provided", "data" => null));

        }


    }



}


function create_data(){


    global $kiw_db;


    $kiw_id_arr = $kiw_db->escape($_REQUEST['controller']);


    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));


        if(empty($kiw_id_arr)) die(json_encode(array("status" => "error", "message" => "ERROR: Please select at least one the controller" )));


        
        $kiw_data['mac']     = $_REQUEST['mac'];
        $kiw_data['ip']      = $_REQUEST['ip'];
        $kiw_data['remark']  = $_REQUEST['remark'];
        $kiw_data['speed']   = $_REQUEST['speed'];


        // contact controller and get all device list

        if(empty($kiw_data['mac']) && empty($kiw_data['ip'])) {

            die(json_encode(array("status" => "error", "message" => "ERROR: Please insert either IP Address or Mac Address." )));

        }

        $success = [];
        $error = [];

        $kiw_id_arr = explode(",", $kiw_id_arr);


        foreach ($kiw_id_arr as $kiw_id) {
            # code...

            $kiw_controller = $kiw_db->query_first("SELECT * FROM kiwire_controller WHERE tenant_id = '{$_SESSION['tenant_id']}' AND unique_id = '{$kiw_id}' LIMIT 1");

            
            if (!empty($kiw_controller)){


                $kiw_api = new routeros_api();
                
                $check_connection = $kiw_api->connect($kiw_controller['device_ip'], $kiw_controller['username'], $kiw_controller['password']);

                if($check_connection == false || $check_connection == NULL || empty($check_connection)) {

                    $error[] = $kiw_id;
                    // die(json_encode(array("status" => "error", "message" => "ERROR: Please check your connection to mikrotik API." )));
                    
                }
                

                $kiw_api->write("/ip/hotspot/ip-binding/add", false);

                if(!empty($kiw_data['mac'])) {

                    $kiw_api->write("=mac-address={$kiw_data['mac']}", false);
                }
                if(!empty($kiw_data['ip'])) {
                    // $kiw_api->write("=to-address={$kiw_data['ip']}", false);
                    $kiw_api->write("=address={$kiw_data['ip']}", false);
                }

                $kiw_api->write("=comment={$kiw_data['remark']}", false);
                $kiw_api->write('=type=bypassed', false);
                $kiw_api->write('=server=all');

                $kiw_read = $kiw_api->read(false);
                $kiw_array = $kiw_api->parse_response($kiw_read);

                if ($kiw_data['speed'] > 0) {
                    
                    if(!strpos($kiw_data['ip'], '-')) {  // IP NOT RANGE

                        
                        $kiw_api->write('/queue/simple/add', false);
                        
                        if(!empty($kiw_data['mac'])) {
                            $kiw_api->write("=name=BYPASS_{$kiw_data['mac']}", false);
                        }else {
                            $kiw_api->write("=name=BYPASS_{$kiw_data['ip']}", false);
                        }
                        
                        if(!empty($kiw_data['ip'])) {
                            
                            $kiw_api->write("=target={$kiw_data['ip']}", false);
                        }
                        $kiw_api->write("=max-limit={$kiw_data['speed']}M/{$kiw_data['speed']}M");


                        $kiw_read = $kiw_api->read(false);
                        
                    }else {


                        $kiw_data['ip'] = str_replace(" ","", $kiw_data['ip']);
                        $kiw_data['ip'] = str_replace("-",",", $kiw_data['ip']);
                        $kiw_data['ip'] = explode(",",  $kiw_data['ip']);
                        
                        $start_ip = $kiw_data["ip"][0];
                        $end_ip   = $kiw_data["ip"][1];

                        $start_ip_arr = explode(".",$start_ip);
                        $start_ip = end($start_ip_arr);

                        $end_ip_arr = explode(".",$end_ip);
                        $end_ip = end($end_ip_arr);

                        if($start_ip < $end_ip) {

                            array_pop($start_ip_arr);
                            
                            for ($i=$start_ip; $i <= $end_ip; $i++) { 

                                $kiw_api->write('/queue/simple/add', false);

                                $massage_ip = implode(".", $start_ip_arr) . "." . $i;
                                $kiw_api->write("=name=BYPASS_{$massage_ip}", false);
                                $kiw_api->write("=target={$massage_ip}", false);
                                $kiw_api->write("=max-limit={$kiw_data['speed']}M/{$kiw_data['speed']}M");
                                
                                $kiw_read = $kiw_api->read(false);

                            }
                            

                        }


                    }
                }



                $kiw_array = $kiw_api->parse_response($kiw_read);


                $kiw_api->disconnect();

                $success[] = "SUCCESS: device has been bypass in Mikrotik [ {$kiw_id} ]";
                // echo json_encode(array("status" => "success", "message" => "SUCCESS: address [ {$msg_ref} ] has been bypass in Mikrotik", "data" => null));


            } 
        }

        if(count($error) > 0) {

            $msg = "";
            foreach ($error as $value) {
                $msg .= "<code>" . $value . "</code><br>";
            }

            echo json_encode(array("status" => "success", "message" =>  empty($success) ? "ERROR": "SUCCESS" . ": Success, but Some AP not success Please check config for this AP:- <br> {$msg}", "data" => null));
            
        }else {

            echo json_encode(array("status" => "success", "message" => "SUCCESS: device has been bypass in all selected Mikrotik", "data" => null));

        }


    }



}


