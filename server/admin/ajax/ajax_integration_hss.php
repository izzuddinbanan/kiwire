<?php

// ini_set("max_execution_time", 15);

$kiw['module'] = "Integration -> HSS";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

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


    header("Content-Type: application/json");

    global $kiw_db, $tenant_id;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $data['username']           = $kiw_db->escape($_POST['hss_user']);
        $data['password']           = $kiw_db->escape($_POST['hss_password']);
        $data['hss_server_url']     = $kiw_db->escape($_POST['hss_url']);
        $data['updated_date']       = date('Y-m-d H-i-s');
        $data['status']             = (isset($_POST['hss_status']) ? "y" : "n");

        if($data["status"] == "y") {

            foreach(["username", "password", "hss_server_url"] as $field) {

                if(empty($data[$field])) {
                    
                    die(json_encode(array("status" => "error", "message" => "ERROR: {$field} cannot be empty.", "data" => NULL)));
                    
                }
                
            }

        }

       
        // $data['is_24_hour']     = isset($_REQUEST['is_24']) && $_REQUEST['is_24'] ? 1 : 0;
        // $data['start_time']     = $kiw_db->escape($_REQUEST['start_time']);

        // if(!$data['is_24_hour']){
            
        //     if($_REQUEST['start_time'] == $_REQUEST['stop_time'])
        //         die(json_encode(array("status" => "failed", "message" => "ERROR: Stop Time cannot same as Start Time", "data" => null)));
        //     else
        //         $data['stop_time'] =   $kiw_db->escape($_REQUEST['stop_time']);

        // }
        
        if($kiw_db->update("kiwire_int_hss", $data, "tenant_id = '$tenant_id'")){


            sync_logger("{$_SESSION['user_name']} updated HSS Setting ", $_SESSION['tenant_id']);

            echo json_encode(array("status" => "success", "message" => "SUCCESS: HSS setting has been saved", "data" => NULL));

        }else {


            echo json_encode(array("status" => "error", "message" => "ERROR: HSS setting has been saved", "data" => NULL));

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


        $data['username']           = $_POST['hss_user'];
        $data['password']           = $_POST['hss_password'];
        $data['hss_server_url']     = $_POST['hss_url'];


        foreach(["username", "password", "hss_server_url"] as $field) {

            if(empty($data[$field])) {
                
                die(json_encode(array("status" => "error", "message" => "ERROR: {$field} cannot be empty.", "data" => NULL)));
                
            }
            
        }


                
        //Login via LGI to HSS
        $req_data = GenSOAPlogin($data['username'],$data['password']);
        $ch = curl_init();
        $payload =  $req_data;
        curl_setopt($ch, CURLOPT_URL, $data['hss_server_url']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($ch, CURLOPT_POST,           true ); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: text/xml; charset=utf-8', 'Content-Length: '.strlen($payload) )); 
        curl_setopt($ch, CURLOPT_HEADER, true);
        $result = curl_exec($ch);

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($result, 0, $header_size);
        $body = substr($result, $header_size);


        //end of login connect , now do check

        $xml = simplexml_load_string($body);
        foreach ($xml->xpath('//Result') as $item){}
        $login_status =  $item->ResultCode;

        unset($data);

        $data['last_test']           = "NOW()";

        if ( $login_status == "0" ){


            $data['last_test_status']           = "success";
            $kiw_db->update("kiwire_int_hss", $data, "tenant_id = '$tenant_id'");

            sync_logger("{$_SESSION['user_name']} Test connection HSS Setting : Success ", $_SESSION['tenant_id']);

            die(json_encode(array("status" => "success", "message" => "SUCCESS: HSS setting configure correctly", "data" => NULL)));
            
        }else{
            

            $data['last_test_status']           = "failed";
            $kiw_db->update("kiwire_int_hss", $data, "tenant_id = '$tenant_id'");

            sync_logger("{$_SESSION['user_name']} Test connection HSS Setting : Failed ", $_SESSION['tenant_id']);

            die(json_encode(array("status" => "Error", "message" => "Error: HSS setting configure incorrect. Cannot login.", "data" => NULL)));
        }





    }


}





// HSS FUNCTION

function GenSOAPlogin($username,$password){

    $request="
        <soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\">
        <soapenv:Body>
        <LGI><OPNAME>$username</OPNAME><PWD>$password</PWD></LGI>
        </soapenv:Body>
        </soapenv:Envelope>
        ";
    return $request;

}