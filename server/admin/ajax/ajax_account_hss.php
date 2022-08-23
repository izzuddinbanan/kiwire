<?php

$kiw['module'] = "Account -> HSS";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_general.php";
require_once "../includes/include_connection.php";
require_once "../includes/include_hss.php";


require_once "../../libs/class.sql.helper.php";
require_once "../../libs/ssp.class.php";

require_once "../../user/includes/include_account.php";


if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));
}


$action = $_REQUEST['action'];

switch ($action) {
    case "get_all":
        get_all();
        break;

    case "get_update":
        get_update();
        break;

        
    case "history":
        get_history($kiw_db);
        break;
    case "statistics":
        statistics($kiw_db);
        break;
    case "reset":
        voucher_reset();
        break;
    case "reset-mac":
        voucher_reset_mac();
        break;
    case "create":
        create();
        break;

    case "delete":
        delete();
        break;
    case "line_chart":
        line_chart();
        break;
    case "chart_history":
        chart_history();
        break;

    default:
        echo "ERROR: Wrong implementation";
}


function get_all()
{

    global $kiw_db;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_timezone = $_SESSION['timezone'];

        $kiw_timezone = empty($kiw_timezone) ?: "Asia/Kuala_Lumpur";


        $kiw_columns = array(
            array('db' => 'username', 'dt' => 1),
            array('db' => 'status', 'dt' => 2),
            array('db' => 'profile_subs', 'dt' => 3),
            array('db' => 'price', 'dt' => 4),
            array('db' => 'date_create', 'dt' => 5),
            array('db' => 'date_expiry', 'dt' => 6),
            array( 'db' => 'tenant_id',     'dt' => 7 )

        );


        $kiw_sqlinfo = array('user' => SYNC_DB1_USER, 'pass' => SYNC_DB1_PASSWORD, 'db' => SYNC_DB1_DATABASE, 'host' => SYNC_DB1_HOST, 'port' => SYNC_DB1_PORT);


        $kiw_where = "ktype = 'simcard'";

        
        if (!empty($_REQUEST['username'])) {

            $kiw_where .= " AND username LIKE '%" . $kiw_db->escape($_REQUEST['username']) . "%'";
        }


        if (!empty($_REQUEST['status'])) {

            $kiw_where .= " AND status = '" . $kiw_db->escape($_REQUEST['status']) . "'";
        }


        if (!empty($_REQUEST['profile'])) {

            $kiw_where .= " AND profile_subs = '" . $kiw_db->escape($_REQUEST['profile']) . "'";
        }


        if (!empty($_REQUEST['created_date'])) {


            $kiw_temp = date("Y-m-d H:i:s", strtotime($_REQUEST['created_date']));

            $kiw_temp = sync_toutctime($kiw_temp);


            $kiw_where .= " AND date_create >= '{$kiw_temp}'";
        }


        if (!empty($_REQUEST['expiry_from'])) {


            $kiw_temp = date("Y-m-d H:i:s", strtotime($_REQUEST['expiry_from']));

            $kiw_temp = sync_toutctime($kiw_temp);


            $kiw_where .= " AND date_expiry >= '{$kiw_temp}'";
        }

         // pending tenant check

         if ($_SESSION['access_level'] == "superuser") {


            if (!empty($_REQUEST['tenant_id'])) {


                if (!empty($_SESSION['tenant_allowed'])) {


                    $kiw_temp = explode(",", $_SESSION['tenant_allowed']);


                    if (in_array($_REQUEST['tenant_id'], $kiw_temp) == true) {


                        $kiw_where .= " AND tenant_id = '" . $kiw_db->escape($_REQUEST['tenant_id']) . "'";


                    } else {


                        $kiw_where .= " AND tenant_id IN ('" . implode("','", $kiw_temp) . "')";


                    }


                } else {

                    $kiw_where .= " AND tenant_id = '" . $kiw_db->escape($_REQUEST['tenant_id']) . "'";

                }


            } else {


                if (!empty($_SESSION['tenant_allowed'])) {


                    $kiw_where .= " AND tenant_id IN ('" . implode("','", explode(",", $_SESSION['tenant_allowed'])) . "')";


                }


            }


        } else  $kiw_where .= " AND tenant_id = '{$_SESSION['tenant_id']}'";


        $kiw_data = SSP::complex($_GET, $kiw_sqlinfo, "kiwire_account_auth", "id", $kiw_columns, null, $kiw_where);


        $kiw_start = $_GET['start'] + 1;

        $kiw_end = count($kiw_data['data']) + $kiw_start;


        for ($x = $kiw_start; $x < $kiw_end; $x++) {

            $kiw_hss = get_hss_details($kiw_data['data'][$x - $kiw_start][1]);

            $kiw_data['data'][$x - $kiw_start][0] = $x;

            // $kiw_data['data'][$x - $kiw_start][2] = $kiw_hss["private_key"];

            // $kiw_data['data'][$x - $kiw_start][3] = $kiw_hss["isdn"];

            $kiw_data['data'][$x - $kiw_start][4] = $kiw_hss["is_sync"];

            $kiw_data['data'][$x - $kiw_start][5] = sync_tolocaltime($kiw_data['data'][$x - $kiw_start][5], $kiw_timezone);

            $kiw_data['data'][$x - $kiw_start][6] = sync_tolocaltime($kiw_data['data'][$x - $kiw_start][6], $kiw_timezone);


        }


        echo json_encode($kiw_data);


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}



function get_update() {


    global $kiw_db, $kiw_tenant_id;


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_result = array();


        $kiw_username = $kiw_db->escape($_REQUEST['username']);


        $kiw_user = $kiw_db->query_first("SELECT * FROM kiwire_account_auth WHERE tenant_id = '{$kiw_tenant_id}' AND username = '{$kiw_username}' LIMIT 1");

        $kiw_result['username']     = $kiw_user['username'];
        $kiw_result['fullname']     = $kiw_user['fullname'];
        $kiw_result['password']     = $kiw_user['password'];
        $kiw_result['remark']       = $kiw_user['remark'];
        $kiw_result['profile_subs'] = $kiw_user['profile_subs'];
        $kiw_result['ktype']        = "account";
        $kiw_result['status']       = $kiw_user['status'];
        $kiw_result['integration']  = $kiw_user['integration'];
        $kiw_result['allowed_zone'] = $kiw_user['allowed_zone'];
        $kiw_result['date_expiry']  = sync_tolocaltime($kiw_user['date_expiry'], $_SESSION['timezone']);
        $kiw_result['date_value']   = sync_tolocaltime($kiw_user['date_value'], $_SESSION['timezone']);


        $kiw_result['date_expiry']  = date("m/d/Y", strtotime($kiw_result['date_expiry']));
        $kiw_result['date_value']   = date("m/d/Y", strtotime($kiw_result['date_value']));

        $kiw_result['email_address']  = $kiw_user['email_address'];
        $kiw_result['phone_number']   = $kiw_user['phone_number'];

        $kiw_result['tenant_id']    = $kiw_user['tenant_id'];

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_result));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}



function get_hss_details($username) {

    global $kiw_db;

    $kiw_username           = $kiw_db->escape($username);
    $kiw_tenant_id          = $kiw_db->escape($_SESSION['tenant_id']);

    return $kiw_db->query_first("SELECT * FROM kiwire_account_hss WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant_id}' LIMIT 1");

}


function create()
{

    global $kiw_db;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

       
        csrf($kiw_db->escape($_REQUEST['token']));


        
        $kiw_tenant_id = $kiw_db->escape($_SESSION['tenant_id']);

        $kiw_timezone = $_SESSION['timezone'];

        if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


        $kiw_username           = $kiw_db->escape($_REQUEST['imsi']);

        $kiw_existed = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_account_auth WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant_id}' LIMIT 1");


        if ($kiw_existed['kcount'] == 0) {


            $kiw_user = array();

            $kiw_user['tenant_id'] = $kiw_tenant_id;

            $kiw_user['creator']        = $kiw_db->escape($_SESSION['user_name']);
            $kiw_user['username']       = $kiw_username;
            $kiw_user['fullname']       = "NA";
            $kiw_user['password']       = sync_encrypt($kiw_db->escape($_REQUEST['imsi']));
            $kiw_user['email_address']  = "NA";

            $kiw_user['phone_number']   = "NA";
            $kiw_user['remark']         = $kiw_db->escape($_REQUEST['remark']);
            $kiw_user['profile_subs']   = $kiw_db->escape($_REQUEST['plan']);
            $kiw_user['profile_curr']   = $kiw_db->escape($_REQUEST['plan']);
            $kiw_user['ktype']          = "simcard";
            $kiw_user['status']         = "suspend";

            $kiw_user['integration']    = "int";
            $kiw_user['allowed_zone']   = $kiw_db->escape($_REQUEST['zone']) ?? "none";
            $kiw_user['date_expiry']    = sync_toutctime(date("Y-m-d H:i:s", strtotime($kiw_db->escape($_REQUEST['date_expiry']))), $kiw_timezone);
            $kiw_user['date_value']     = "NOW()";


            if($kiw_db->insert("kiwire_account_auth", $kiw_user)) {

                
                
                // INSERT DATA INTO kiwire_account_hss
                
                $kiw_hss["tenant_id"]   = $kiw_tenant_id;
                $kiw_hss["username"]    = $kiw_username;
                $kiw_hss["hlrsn"]       = $kiw_db->escape($_REQUEST['hlrsn']);
                $kiw_hss["private_key"] = $kiw_db->escape($_REQUEST['ki']);
                $kiw_hss["card_type"]   = $kiw_db->escape($_REQUEST['card_type']);
                $kiw_hss["alg"]         = $kiw_db->escape($_REQUEST['alg']);
                $kiw_hss["opsno"]       = $kiw_db->escape($_REQUEST['opsno']);
                $kiw_hss["key_type"]    = $kiw_db->escape($_REQUEST['key_type']);
                $kiw_hss["isdn"]        = $kiw_db->escape($_REQUEST['isdn']);
                $kiw_hss["tpltype"]     = $kiw_db->escape($_REQUEST['tpltype']);
                $kiw_hss["tplid"]       = $kiw_db->escape($_REQUEST['tplid']);
                
                $kiw_db->insert("kiwire_account_hss", $kiw_hss);

                // ############
                // START HSS PROCESS
                // ############
                $kiw_config_hss = $kiw_db->query_first("SELECT * FROM kiwire_int_hss WHERE tenant_id = '$kiw_tenant_id' LIMIT 1");

                                
                //Login via LGI to HSS
                $kiw_login_xml = GenSOAPlogin($kiw_config_hss["username"],$kiw_config_hss["password"]);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $kiw_config_hss["hss_server_url"]);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
                curl_setopt($ch, CURLOPT_POST,           true ); 
                curl_setopt($ch, CURLOPT_POSTFIELDS, $kiw_login_xml);
                curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: text/xml; charset=utf-8', 'Content-Length: '.strlen($kiw_login_xml) )); 
                curl_setopt($ch, CURLOPT_HEADER, true);
                $kiw_login_response = curl_exec($ch);

                $kiw_header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                $kiw_header = substr($kiw_login_response, 0, $kiw_header_size);
                $kiw_result_body = substr($kiw_login_response, $kiw_header_size);

                //end of login connect , now do check
                $xml = simplexml_load_string($kiw_result_body);
                foreach ($xml->xpath('//Result') as $item){}
                $kiw_login_status =  $item->ResultCode;

                if ( $kiw_login_status == "0" ){

                    if (preg_match('~Location: (.*)~i', $kiw_header, $match)) {
                        
                        $location = trim($match[1]);


                        hss_logger($kiw_tenant_id, "Success Login HSS");
                        hss_logger($kiw_tenant_id, "location : {$location}");
                        hss_logger($kiw_tenant_id, "payload : ". json_encode($kiw_login_xml));


                        // // ADD KI
                        $kiw_response_add_ki = call_api_hss($ch, $location, GenSoapADDKI($kiw_hss["hlrsn"], $kiw_hss["username"], "ADD", $kiw_hss["private_key"], $kiw_hss["card_type"], $kiw_hss["alg"], $kiw_hss["opsno"], $kiw_hss["key_type"]), $kiw_tenant_id, "ADD KI");


                        // IFF NOT SUCCESS ADD KI
                        if(!$kiw_response_add_ki["status"]["type"]) {

                            // LOGOUT
                            call_api_hss($ch, $location, GenSOAPLogout(), $kiw_tenant_id, "LOGOUT");
                            die(json_encode(array("status" => "success", "message" => "SUCCESS: New sim card added<br>ERROR: cannot sync KI", "data" => null)));
                        }


                        // ADD TPL SUB
                        $kiw_response_add_sub = call_api_hss($ch, $location, GenSoapADDTPLSUB($kiw_hss["hlrsn"], $kiw_hss["username"], $kiw_hss["isdn"], $kiw_hss["tpltype"], $kiw_hss["tplid"]), $kiw_tenant_id, "ADD TPLSUB");


                        // IFF NOT SUCCESS ADD SUB
                        if(!$kiw_response_add_sub["status"]["type"]) {

                            // REMOVE KI
                            call_api_hss($ch, $location, GenSoapRMVKI($kiw_hss["username"]), $kiw_tenant_id, "REMOVE KI");
                            // LOGOUT
                            call_api_hss($ch, $location, GenSOAPLogout(), $kiw_tenant_id, "LOGOUT");
                            die(json_encode(array("status" => "success", "message" => "SUCCESS: New sim card added<br>ERROR: cannot sync TPLSUB", "data" => null)));
                        }



                        $kiw_response_lst_sub = call_api_hss($ch, $location, GenSoapLSTSUB("",$kiw_hss["isdn"]), $kiw_tenant_id, "ENABLE LST SUB");

                        if(!$kiw_response_lst_sub["status"]["type"]) {

                            // REMOVE SUB
                            call_api_hss($ch, $location, GenSoapRMVSUB("", $kiw_hss["isdn"]), $kiw_tenant_id, "REMOVE TPLSUB");
                            
                            // REMOVE KI
                            call_api_hss($ch, $location, GenSoapRMVKI($kiw_hss["username"]), $kiw_tenant_id, "REMOVE KI");

                            // LOGOUT
                            call_api_hss($ch, $location, GenSOAPLogout(), $kiw_tenant_id, "LOGOUT");
                            die(json_encode(array("status" => "success", "message" => "SUCCESS: New sim card added<br>ERROR: cannot sync LSTSUB", "data" => null)));
                        }


                        $kiw_response_mod_gprs = call_api_hss($ch, $location, GenSoapMODOPTGPRS($kiw_hss["username"]), $kiw_tenant_id, "ENABLE GPRS");

                        if(!$kiw_response_mod_gprs["status"]["type"]) {

                            // REMOVE SUB
                            call_api_hss($ch, $location, GenSoapRMVSUB("", $kiw_hss["isdn"]), $kiw_tenant_id , "REMOVE TPLSUB");
                            
                            // REMOVE KI
                            call_api_hss($ch, $location, GenSoapRMVKI($kiw_hss["username"]), $kiw_tenant_id, "REMOVE KI");

                            // LOGOUT
                            call_api_hss($ch, $location, GenSOAPLogout(), $kiw_tenant_id, "LOGOUT");
                            die(json_encode(array("status" => "success", "message" => "SUCCESS: New sim card added<br>ERROR: cannot sync MOD_OPTGPRS", "data" => null)));
                        }


                    
                        $kiw_db->query(sql_update($kiw_db, "kiwire_account_hss", ["is_sync" => "y"], "username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant_id}' LIMIT 1"));


                        sync_logger("{$_SESSION['user_name']} create HSS sim card {$kiw_username}", $kiw_tenant_id);
                        
                        call_api_hss($ch, $location, GenSOAPLogout(), $kiw_tenant_id, "LOGOUT");

                        die(json_encode(array("status" => "success", "message" => "SUCCESS: New sim card added and successfully sync with HSS", "data" => null)));


                    }

                }else{

                    hss_logger($kiw_tenant_id, "Disconnect:Fail Auth");
                    
                    die(json_encode(array("status" => "success", "message" => "SUCCESS: New sim card added<br>ERROR: cannot sync to HSS, please check your config", "data" => null)));

                }
                
                
            }else {


                die(json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null)));

            }




        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: This sim card already existed in the system.", "data" => null));

        }



    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}



function call_api_hss($ch, $location, $payload, $tenant_id, $process_name) {

		
	curl_setopt($ch, CURLOPT_URL, $location);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt($ch, CURLOPT_POST,           true );
	curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
	curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: text/xml; charset=utf-8', 'Content-Length: '.strlen($payload) ));
	curl_setopt($ch, CURLOPT_HEADER, false);
	$result = curl_exec($ch);
	
	$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	$body        = substr($result, $header_size);
    
	if (str_contains($body, "success")){

		hss_logger($tenant_id, "Success {$process_name}");
		hss_logger($tenant_id, "Payload : " . json_encode(str_replace(" ", "",$payload)));
		return base_api_respond([], true, "success", 200);
		
	}else {

		hss_logger($tenant_id, "Error {$process_name}");
		hss_logger($tenant_id, "Payload : " . json_encode(str_replace(" ", "",$payload)));
		return base_api_respond([], false, "failed", 403);

	}

}











function api_hss($tenant_id, $payload, $need_login = false){

    global $kiw_db;


    $kiw_config_hss = $kiw_db->query_first("SELECT * FROM kiwire_int_hss WHERE tenant_id = '$tenant_id' LIMIT 1");

    //Login via LGI to HSS
    $req_data = GenSOAPlogin($kiw_config_hss["username"],$kiw_config_hss["password"]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $kiw_config_hss["hss_server_url"]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt($ch, CURLOPT_POST,           true ); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, $req_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: text/xml; charset=utf-8', 'Content-Length: '.strlen($req_data) )); 
    curl_setopt($ch, CURLOPT_HEADER, true);
    $result = curl_exec($ch);

    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($result, 0, $header_size);
    $body = substr($result, $header_size);


    //end of login connect , now do check
    $xml = simplexml_load_string($body);
    foreach ($xml->xpath('//Result') as $item){}
    $login_status =  $item->ResultCode;
    

    if ( $login_status == "0" ){
        
        if (preg_match('~Location: (.*)~i', $header, $match)) {
            
            $location = trim($match[1]);

            hss_logger($tenant_id, "Success Login HSS");

            hss_logger($tenant_id, "location : {$location}");
            hss_logger($tenant_id, "payload : {$payload}");
                        
            curl_setopt($ch, CURLOPT_URL, $location);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt($ch, CURLOPT_POST,           true );
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: text/xml; charset=utf-8', 'Content-Length: '.strlen($payload) ));
            curl_setopt($ch, CURLOPT_HEADER, false);
            $result = curl_exec($ch);

                        
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $body        = substr($result, $header_size);

            if (str_contains($body, "success")){

                hss_logger($tenant_id, "Response Success: " .$body);
                return base_api_respond([], true, "success", 200);
                
            }else {

                hss_logger($tenant_id, "Response Error: " . $body);
                return base_api_respond([], false, "failed", 403);

            }




        }
    }else{

        hss_logger($tenant_id, "Disconnect:Fail Auth");
        return base_api_respond([], false, "Disconnect:Fail Auth", 403);
    }


    
}

