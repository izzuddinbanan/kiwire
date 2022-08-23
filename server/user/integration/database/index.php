<?php
    // file_put_contents(dirname(__FILE__, 5) . "/logs/uniti/kiwire-db-integration-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s :: ") . "REQUEST : ". json_encode($_REQUEST) ." : \n", FILE_APPEND);

require_once dirname(__FILE__, 3) . "/includes/include_session.php";
require_once dirname(__FILE__, 3) . "/includes/include_general.php";
require_once dirname(__FILE__, 3) . "/includes/include_account.php";

require_once dirname(__FILE__, 4) . "/admin/includes/include_connection.php";


    // if empty tenant id try to populate from session

    if (empty($tenant)) $tenant = $_SESSION['controller']['tenant_id'];


    // check if directory existed, if not then create

    if (file_exists(dirname(__FILE__, 5) . "/logs/{$tenant}/") == false) mkdir(dirname(__FILE__, 5) . "/logs/{$tenant}/", 0755, true);


// $_SESSION['controller']['tenant_id'] = "uniti";
$kiw_temp = $kiw_cache->get("DB_DATA:{$_SESSION['controller']['tenant_id']}");

if (empty($kiw_temp)) {
    
    
    $kiw_temp = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_int_api_setting WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' AND enabled = 'y' LIMIT 1");
    
    if (empty($kiw_temp)) $kiw_temp = array("dummy" => true);
    
    $kiw_cache->set("DB_DATA:{$_SESSION['controller']['tenant_id']}", $kiw_temp, 1800);
    
    
}



if (!empty($kiw_temp) || $kiw_temp['enabled'] == "y"){



    file_put_contents(dirname(__FILE__, 5) . "/logs/{$tenant}/kiwire-db-integration-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s :: ") . "REQUEST : ". json_encode($_REQUEST) ." : \n", FILE_APPEND);


    $kiw_username = $kiw_db->escape(trim($_REQUEST['username']));
    $kiw_password = $kiw_db->escape(trim($_REQUEST['password']));



    //parameter to send to api
    $kiw_data['api_key']    = $kiw_temp['api_key'];
    $kiw_data['tenant_id']  = $_SESSION['controller']['tenant_id'];
    $kiw_data['url']        = 'https://captive.synchroweb.com/api/integration_api';
    $kiw_data['username']   = $kiw_username;
    $kiw_data['password']   = $kiw_password;

    
    file_put_contents(dirname(__FILE__, 5) . "/logs/{$tenant}/kiwire-db-integration-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s :: ") .  "CHECKING - U : {$kiw_username} :  T: {$_SESSION['controller']['tenant_id']}\n", FILE_APPEND);

    
    if (check_account_exist($kiw_db, $kiw_username, $_SESSION['controller']['tenant_id'])){

        file_put_contents(dirname(__FILE__, 5) . "/logs/{$tenant}/kiwire-db-integration-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s :: ") . "U : {$kiw_username} : Exist\n", FILE_APPEND);

        
        $kiw_user   =  $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_account_auth WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' AND username = '{$kiw_data['username']}' LIMIT 1");


        //check last login within 24hours 
        if(time() - strtotime($kiw_user['date_last_login']) < 86400){  // 86400 is 24 h in seconds (60*60*24)
            
            file_put_contents(dirname(__FILE__, 5) . "/logs/{$tenant}/kiwire-db-integration-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s :: ") . "U : {$kiw_username} : Login\n", FILE_APPEND);

            //login user
            login_user($kiw_username, $kiw_password, $session_id);
            
        }
        else{

            //get user data from external db using api
            $kiw_auth = curl_init();

            curl_setopt($kiw_auth, CURLOPT_URL, "http://58.26.127.168:8088/kiwire/public/api/kiwire-integration");
            curl_setopt($kiw_auth, CURLOPT_POST, true);
            curl_setopt($kiw_auth, CURLOPT_POSTFIELDS, http_build_query($kiw_data));
            curl_setopt($kiw_auth, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($kiw_auth, CURLOPT_SSL_VERIFYPEER ,false);
            curl_setopt($kiw_auth, CURLOPT_SSL_VERIFYHOST, false);

            $response =  json_decode(curl_exec($kiw_auth), true);

            curl_close($kiw_auth);

            if($response['status'] == 'success'){
                
                file_put_contents(dirname(__FILE__, 5) . "/logs/{$tenant}/kiwire-db-integration-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s :: ") . "U : {$kiw_username} : Update data and Login \n", FILE_APPEND);

                //login user
                login_user($response['data']['noMatriks'], $response['data']['no_ic'], $session_id);

            }

        }

    }
    else{

        file_put_contents(dirname(__FILE__, 5) . "/logs/{$tenant}/kiwire-db-integration-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s :: ") . "U : {$kiw_username} : Not Exist \n", FILE_APPEND);

        //get user data from external db using api
        $kiw_auth = curl_init();
        
        curl_setopt($kiw_auth, CURLOPT_URL, "http://58.26.127.168:8088/kiwire/public/api/kiwire-integration");
        curl_setopt($kiw_auth, CURLOPT_POST, true);
        curl_setopt($kiw_auth, CURLOPT_POSTFIELDS, http_build_query($kiw_data));
        curl_setopt($kiw_auth, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($kiw_auth, CURLOPT_SSL_VERIFYPEER ,false);
        curl_setopt($kiw_auth, CURLOPT_SSL_VERIFYHOST, false);
        
        $response =  json_decode(curl_exec($kiw_auth), true);
        
        curl_close($kiw_auth);


        if($response['status'] == 'success'){
            

            file_put_contents(dirname(__FILE__, 5) . "/logs/{$tenant}/kiwire-db-integration-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s :: ") . "U : {$kiw_username} : API Success Login \n", FILE_APPEND);

            //user doesnt exist in kiwire table, need to create user
            $kiw_create_user = array();

            $kiw_create_user['username']       = $response['data']['noMatriks'];
            $kiw_create_user['password']       = $response['data']['no_ic'];
            $kiw_create_user['fullname']       = $response['data']['name'];

            $kiw_create_user['tenant_id']      = $_SESSION['controller']['tenant_id'];
            $kiw_create_user['profile_subs']   = 'Student';
            $kiw_create_user['ktype']          = "account";
            $kiw_create_user['status']         = "active";
            $kiw_create_user['integration']    = "int";
            $kiw_create_user['allowed_zone']   = 'none';
            $kiw_create_user['date_value']     = "NOW()";

            //create new user
            create_account($kiw_db, $kiw_cache, $kiw_create_user);

            unset($kiw_create_user);
            
            //login user
            login_user($response['data']['noMatriks'], $response['data']['no_ic'], $session_id);

        }else {

            file_put_contents(dirname(__FILE__, 5) . "/logs/{$tenant}/kiwire-db-integration-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s :: ") . "U : {$kiw_username} : API Failed \n", FILE_APPEND);


        }

    }


}
else {

    error_redirect($_SERVER['HTTP_REFERER'], "You are not allowed to access this module");

}
