<?php

require_once dirname(__FILE__, 5) . "/user/includes/include_session.php";
require_once dirname(__FILE__, 5) . "/user/includes/include_general.php";
require_once dirname(__FILE__, 5) . "/user/includes/include_account.php";

require_once dirname(__FILE__, 5) . "/admin/includes/include_connection.php";

require_once dirname(__FILE__) . "/rms_helper.php";

$session_id = session_id();

$username = $kiw_db->escape($_REQUEST['username']); //912
$password = $kiw_db->escape($_REQUEST['password']); //Test
$pcode    = trim($kiw_db->escape($_REQUEST['propertycode'])); //51052
$type     = $kiw_db->escape($_REQUEST['type']); // room || asr_member 

//save log each username & password request
rms_logger("CREDENTIALS. username [ {$username} ] password [ {$password} ]", "RMS", $_SESSION['controller']['tenant_id']);

$file = $pcode.'.json';
if(file_exists(trim($file)) == true){
    
    $data = @file_get_contents(trim($file));
    $data = json_decode($data, true);
    
}
else{

    rms_logger("READ JSON FILE FAILED.", "RMS", $_SESSION['controller']['tenant_id']);
    
    error_redirect($_SERVER['HTTP_REFERER'], "Please contact your administrator");
}

$kiw_data = array();

if($type == 'room'){

    $kiw_username = $data['roomId_start'] .''. trim($username) .''.  $data['roomId_end'];   //combine username/roomID given with fixed setting
    $kiw_password = exc_char(trim($password));

    $kiw_data['roomId']                 = $kiw_username;
    $kiw_data['primaryGuestLastName']   = $kiw_password;

    $kiw_username_db = "rms_" . str_replace(" ", "_", $kiw_username);

}
else if($type == 'asr_member'){

    $kiw_data['asrmemberEmail']         = $username; 
    $kiw_data['password']               = $password;

    $kiw_username_db = $username;
    $kiw_password = trim($password);
}


if (check_account_exist($kiw_db, $kiw_username_db, $_SESSION['controller']['tenant_id']) == false){
    
    //START GETTING DATA TO SEND

    $kiw_data['messageId']              = "kiwire_request";
    $kiw_data['terminalId']             = $data['terminalId'];
    $kiw_data['propertyCode']           = $data['propertyCode'];
    $kiw_data['action']                 = "login";
    
    //add custom header for security credentials
    $headers = [
        'client_id:c00922c9fa6f45c999400a989df2a4f7', // 01793a7cc8be49c19e0eb1a88d91495b -- uat
        'client_secret:001E164bC0634ADF84A4E1AB25f05CC9', //515a4F3E1b5344378ee457A0b325cd01 -- uat
        'Content-Type:application/json'
    ];

    //data in json format
    $kiw_data = json_encode($kiw_data);

    //END GETTING DATA TO SEND


    //START CURL
    $kiw_auth = curl_init();

    curl_setopt($kiw_auth, CURLOPT_URL, "https://api.capitaland.com/lodging/odx/properties/v1/getstatus"); //https://api-uat.capitaland.com/dev/group/channel/common/v1/getstatus  -- uat
    curl_setopt($kiw_auth, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($kiw_auth, CURLOPT_POST, true);
    curl_setopt($kiw_auth, CURLOPT_POSTFIELDS, $kiw_data);
    curl_setopt($kiw_auth, CURLOPT_RETURNTRANSFER, true);

    $response =  json_decode(curl_exec($kiw_auth), true);

    curl_close($kiw_auth);
    //END CURL

    
    unset($kiw_auth);

    if($response['message'] == 'Success'){
        
        //user doesnt exist in kiwire table, need to create user
        $kiw_create_user = array();
        
        $kiw_create_user['username']       = $kiw_username_db;
        $kiw_create_user['password']       = $kiw_password;
        $kiw_create_user['fullname']       = $kiw_username_db;
        
        $kiw_create_user['tenant_id']      = $_SESSION['controller']['tenant_id'];
        $kiw_create_user['profile_subs']   = 'Temp_Access';
        $kiw_create_user['ktype']          = "account";
        $kiw_create_user['status']         = "active";
        $kiw_create_user['integration']    = "int";
        $kiw_create_user['allowed_zone']   = 'none';
        $kiw_create_user['date_value']     = "NOW()";
        
        //create new user
        create_account($kiw_db, $kiw_cache, $kiw_create_user);

        unset($kiw_create_user);
        
        //login user
        rms_logger("SUCCESS LOGIN AND CREATED ACCOUNT . username [{$kiw_username_db}] tenant [{$_SESSION['controller']['tenant_id']}] ", "RMS", $_SESSION['controller']['tenant_id']);   

        login_user($kiw_username_db, $kiw_password, $session_id);
    }
    else {

        rms_logger("API RESPOND FAILED.[{$response['message']}][{$kiw_data}] ", "RMS", $_SESSION['controller']['tenant_id']);   

        error_redirect($_SERVER['HTTP_REFERER'], $response['message']);
        // die(json_encode(array("status" => "error", "message" => $response['message'])));
    }

}
else{

    $kiw_create_user = array();
    
    $kiw_create_user['profile_subs']   = 'Temp_Access';
    $kiw_create_user['ktype']          = "account";
    $kiw_create_user['status']         = "active";
    $kiw_create_user['integration']    = "int";
    $kiw_create_user['allowed_zone']   = 'none';
    $kiw_create_user['date_value']     = "NOW()";
    $kiw_create_user['updated_date']   = "NOW()";
    

    $kiw_db->query(sql_update($kiw_db, "kiwire_account_auth", $kiw_create_user, "tenant_id = '{$_SESSION['controller']['tenant_id']}' AND username = '{$kiw_username_db}' LIMIT 1"));
    
    unset($kiw_create_user);
    
    
    rms_logger("SUCCESS LOGIN AND UPDATED ACCOUNT . username [{$kiw_username_db}] tenant [{$_SESSION['controller']['tenant_id']}] ", "RMS", $_SESSION['controller']['tenant_id']);   
    
    //login user
    login_user($kiw_username_db, $kiw_password, $session_id);

}