<?php
ini_set('max_execution_time', '1200'); // for infinite time of execution 
$kiw['module'] = "Account -> HSS";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

// require_once "../includes/include_session.php";
require_once "../includes/include_general.php";
require_once "../includes/include_connection.php";
require_once "../includes/include_hss.php";


require_once "../../libs/class.sql.helper.php";
require_once "../../libs/ssp.class.php";

require_once "../../user/includes/include_account.php";




function get_hss_details($username) {

    global $kiw_db;

    $kiw_username           = $kiw_db->escape($username);
    $kiw_tenant_id          = $kiw_db->escape($_SESSION['tenant_id']);

    return $kiw_db->query_first("SELECT * FROM kiwire_account_hss WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant_id}' LIMIT 1");

}



// $payload = GenSoapRMVSUB("","5022010000{$last}");
// $payload =  GenSoapRMVKI("50220100000000{$last}");
// $payload =  GenSoapLISTKI("50220100000000{$last}");
// $payload = GenSoapADDKI("1","50220100000000{$last}","ADD","FF111111FFFF222222FFFFABCD00000{$last}","USIM","MILENAGE","1","ClearKey");
// $payload = GenSoapADDTPLSUB("1","50220100000000{$last}","5022010000{$last}","NORMAL","1");
// $payload = GenSoapLSTSUB("","5022010000{$last}");
// $payload = GenSoapMODOPTGPRS("50220100000000{$last}");

import();

function import() {


    global $kiw_db;

    $file = dirname(__FILE__) . "/data.csv";

    $kiw_file_reader = fopen($file, "r");



    $column = fgetcsv($kiw_file_reader);


    while ($line = fgetcsv($kiw_file_reader)) {
        
        $rowData[] = $line;
    }
    
    $i = 1;
    $kiw_tenant_id = "default";

    for ($i=1; $i <= count($rowData) ; $i++) { 

        if($i <= 46) continue;
        
        $kiw_username           = $kiw_db->escape($rowData[$i][7]);

        $kiw_existed = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_account_auth WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant_id}' LIMIT 1");


        if ($kiw_existed['kcount'] == 0) {


            $kiw_user = array();

            $kiw_user['tenant_id'] = $kiw_tenant_id;

            $kiw_user['creator']        = "admin";
            $kiw_user['username']       = $kiw_username;
            $kiw_user['fullname']       = "NA";
            $kiw_user['password']       = sync_encrypt($kiw_username);
            $kiw_user['email_address']  = "NA";

            $kiw_user['phone_number']   = "NA";
            $kiw_user['remark']         = "";
            $kiw_user['profile_subs']   = "Temp_Access";
            $kiw_user['profile_curr']   = "Temp_Access";
            $kiw_user['ktype']          = "simcard";
            $kiw_user['status']         = "suspend";

            $kiw_user['integration']    = "int";
            $kiw_user['allowed_zone']   = "none";
            $kiw_user['date_expiry']    = sync_toutctime(date("Y-m-d H:i:s", strtotime("2022-09-01 16:00:00")), "Asia/Kuala_Lumpur");
            $kiw_user['date_value']     = "NOW()";
            
            
            if($kiw_db->insert("kiwire_account_auth", $kiw_user)) {

                
                
                // INSERT DATA INTO kiwire_account_hss
                
                $kiw_hss["tenant_id"]   = $kiw_tenant_id;
                $kiw_hss["username"]    = $kiw_username;
                $kiw_hss["hlrsn"]       = $kiw_db->escape($rowData[$i][0]);
                $kiw_hss["private_key"] = $kiw_db->escape($rowData[$i][2]);
                $kiw_hss["card_type"]   = $kiw_db->escape($rowData[$i][3]);
                $kiw_hss["alg"]         = $kiw_db->escape($rowData[$i][4]);
                $kiw_hss["opsno"]       = $kiw_db->escape($rowData[$i][5]);
                $kiw_hss["key_type"]    = $kiw_db->escape($rowData[$i][6]);
                $kiw_hss["isdn"]        = $kiw_db->escape($rowData[$i][8]);
                $kiw_hss["tpltype"]     = $kiw_db->escape("normal");
                $kiw_hss["tplid"]       = $kiw_db->escape("1");
                
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


                        // // // // ADD KI
                        $kiw_response_add_ki = call_api_hss($ch, $location, GenSoapADDKI($kiw_hss["hlrsn"], $kiw_hss["username"], "ADD", $kiw_hss["private_key"], $kiw_hss["card_type"], $kiw_hss["alg"], $kiw_hss["opsno"], $kiw_hss["key_type"]), $kiw_tenant_id, "ADD KI");


                        // IFF NOT SUCCESS ADD KI
                        if(!$kiw_response_add_ki["status"]["type"]) {

                            // LOGOUT
                            call_api_hss($ch, $location, GenSOAPLogout(), $kiw_tenant_id, "LOGOUT");
                            continue;
                        }


                        // ADD TPL SUB
                        $kiw_response_add_sub = call_api_hss($ch, $location, GenSoapADDTPLSUB($kiw_hss["hlrsn"], $kiw_hss["username"], $kiw_hss["isdn"], $kiw_hss["tpltype"], $kiw_hss["tplid"]), $kiw_tenant_id, "ADD TPLSUB");


                        // IFF NOT SUCCESS ADD SUB
                        if(!$kiw_response_add_sub["status"]["type"]) {

                            // REMOVE KI
                            call_api_hss($ch, $location, GenSoapRMVKI($kiw_hss["username"]), $kiw_tenant_id, "REMOVE KI");
                            // LOGOUT
                            call_api_hss($ch, $location, GenSOAPLogout(), $kiw_tenant_id, "LOGOUT");
                            continue;
                        }



                        $kiw_response_lst_sub = call_api_hss($ch, $location, GenSoapLSTSUB("",$kiw_hss["isdn"]), $kiw_tenant_id, "ENABLE LST SUB");

                        if(!$kiw_response_lst_sub["status"]["type"]) {

                            // REMOVE SUB
                            call_api_hss($ch, $location, GenSoapRMVSUB("", $kiw_hss["isdn"]), $kiw_tenant_id, "REMOVE TPLSUB");
                            
                            // REMOVE KI
                            call_api_hss($ch, $location, GenSoapRMVKI($kiw_hss["username"]), $kiw_tenant_id, "REMOVE KI");

                            // LOGOUT
                            call_api_hss($ch, $location, GenSOAPLogout(), $kiw_tenant_id, "LOGOUT");
                            continue;
                        }


                        $kiw_response_mod_gprs = call_api_hss($ch, $location, GenSoapMODOPTGPRS($kiw_hss["username"]), $kiw_tenant_id, "ENABLE GPRS");

                        if(!$kiw_response_mod_gprs["status"]["type"]) {

                            // REMOVE SUB
                            call_api_hss($ch, $location, GenSoapRMVSUB("", $kiw_hss["isdn"]), $kiw_tenant_id , "REMOVE TPLSUB");
                            
                            // REMOVE KI
                            call_api_hss($ch, $location, GenSoapRMVKI($kiw_hss["username"]), $kiw_tenant_id, "REMOVE KI");

                            // LOGOUT
                            call_api_hss($ch, $location, GenSOAPLogout(), $kiw_tenant_id, "LOGOUT");
                            continue;
                        }


                    
                        $kiw_db->query(sql_update($kiw_db, "kiwire_account_hss", ["is_sync" => "y"], "username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant_id}' LIMIT 1"));


                        call_api_hss($ch, $location, GenSOAPLogout(), $kiw_tenant_id, "LOGOUT");

                        curl_close($ch);


                    }

                }else{

                    hss_logger($kiw_tenant_id, "Disconnect:Fail Auth");
                    

                }
                
                
            }




        } 



    }

    // return $i;

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




