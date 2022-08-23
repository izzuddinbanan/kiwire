<?php

require_once dirname(__FILE__, 5) . "/user/includes/include_session.php";
require_once dirname(__FILE__, 5) . "/user/includes/include_account.php";
require_once dirname(__FILE__, 5) . "/user/includes/include_general.php";

require_once dirname(__FILE__, 5) . "/admin/includes/include_config.php";
require_once dirname(__FILE__, 5) . "/admin/includes/include_general.php";
require_once dirname(__FILE__, 5) . "/admin/includes/include_connection.php";

// $_SESSION['controller']['tenant_id'] = 'default';
$session_id = session_id();

$kiw_username =  $kiw_db->escape($_REQUEST['username']);            //'testwifi_student@perdana.um.edu.my';
$kiw_password =  $kiw_db->escape($_REQUEST['password']);            //'tP@@1234';

if (empty($tenant)) $tenant = $_SESSION['controller']['tenant_id'];

if (file_exists(dirname(__FILE__, 6) . "/logs/{$tenant}/") == false) mkdir(dirname(__FILE__, 6) . "/logs/{$tenant}/", 0755, true);

//start populate data for API
$token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczpcL1wvYXBpLnVtLmVkdS5teSIsInN1YiI6ImZlMDA1MDgxLTQ1MDEtNTM5Ny05OTlkLWQwZWVhODI3OWZmMyIsImlhdCI6MTYzNDE4MDQ3OCwiZXhwIjozMzE1OTU1MzI3OCwibmFtZSI6InVtX3VuaWZpZWQifQ.IkjjXiaDfHDyUJzhWxRC1KY2mjr9TN6Qiv2W7VX5RIw';

$body = [
    'username' => $kiw_username,
    'password' => $kiw_password,
];

if(empty($kiw_username))
error_redirect($_SERVER['HTTP_REFERER'], 'Please enter your username');
if(empty($kiw_password))
error_redirect($_SERVER['HTTP_REFERER'], 'Please enter your password');



$header = array(
    "Authorization: Bearer ". $token,
    "Content-Type: application/json",
);
//end populate data for API

$kiw_log = array('tenant_id'=>$_SESSION['controller']['tenant_id']);
$kiw_log = array_merge($kiw_log, $body);

file_put_contents(dirname(__FILE__, 6) . "/logs/{$tenant}/kiwire-db-integrate-{$tenant}-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s :: ") . "User : {$kiw_username} = Incoming Request\n", FILE_APPEND);

// file_put_contents(dirname(__FILE__, 6) . "/logs/{$tenant}/kiwire-db-integrate-{$tenant}-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s :: ") .  "User : {$kiw_username} | Zone : {$_SESSION['user']['zone']}\n", FILE_APPEND);

file_put_contents(dirname(__FILE__, 6) . "/logs/{$tenant}/kiwire-db-integrate-{$tenant}-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s :: ") .  "User : {$kiw_username} | Data =  ". json_encode($body) ."\n", FILE_APPEND);

//check in kiwire database if user already exist in table kiwire_account_auth
if (check_account_exist($kiw_db, $kiw_username, $_SESSION['controller']['tenant_id'])){  //existing user

    //get login user
    $kiw_user   =  $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_account_auth WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' AND username = '{$kiw_username}' LIMIT 1");

    file_put_contents(dirname(__FILE__, 6) . "/logs/{$tenant}/kiwire-db-integrate-{$tenant}-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s :: ") .  "User : {$kiw_username} = User found in Kiwire DB\n", FILE_APPEND);
    file_put_contents(dirname(__FILE__, 6) . "/logs/{$tenant}/kiwire-db-integrate-{$tenant}-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s :: ") .  "User : {$kiw_username} | Profile = {$kiw_user['profile_subs']}\n", FILE_APPEND);


    // if($kiw_user['profile_subs'] != strtolower($_SESSION['user']['zone'])){
    
    //     error_redirect($_SERVER['HTTP_REFERER'], 'Your are not allowed in this zone');
    //     file_put_contents(dirname(__FILE__, 6) . "/logs/{$tenant}/kiwire-db-integrate-{$tenant}-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s :: ") .  "User : {$kiw_username} = Zone detected : [ {$_SESSION['user']['zone']} ] || User Zone {$kiw_user['profile_subs']}  \n", FILE_APPEND);
    
    // }

    //check last login within 24hours
    // if(time() - strtotime($kiw_user['date_last_login']) < 86400){  // 86400 is 24 h in seconds (60*60*24)

    //     //login user

    //     file_put_contents(dirname(__FILE__, 6) . "/logs/{$tenant}/kiwire-db-integrate-{$tenant}-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s :: ") .  "User : {$kiw_username} = User proceed login. Not call API.\n", FILE_APPEND);


    //     login_user($kiw_username, $kiw_password, $session_id);

    // }
    // else{

        //getting user info by API
        $data = call_api($kiw_db,$body, $header, $kiw_log);

        file_put_contents(dirname(__FILE__, 6) . "/logs/{$tenant}/kiwire-db-integrate-{$tenant}-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s :: ") .  "User : {$kiw_username} = Calling API \n", FILE_APPEND);

        file_put_contents(dirname(__FILE__, 6) . "/logs/{$tenant}/kiwire-db-integrate-{$tenant}-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s :: ") .  "User : {$kiw_username} = Response API ". json_encode($data) ." \n", FILE_APPEND);

        if($data['success']){

            $kiw_create_user = array();
            $kiw_create_user['status']          = "active";
            $kiw_create_user['updated_date']    = "NOW()";
            $kiw_create_user['date_last_login'] = "NOW()";
            $kiw_create_user['password']       = sync_encrypt($kiw_password);


            //update user data
            $kiw_db->query(sql_update($kiw_db, "kiwire_account_auth", $kiw_create_user, "tenant_id = '{$_SESSION['controller']['tenant_id']}' AND username = '{$kiw_username}' LIMIT 1"));

            unset($kiw_create_user);

            file_put_contents(dirname(__FILE__, 6) . "/logs/{$tenant}/kiwire-db-integrate-{$tenant}-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s :: ") .  "User : {$kiw_username} = Call Api and success. Proceed login.\n", FILE_APPEND);
            
            $kiw_log['status'] = 'success';
            $kiw_log['reason'] = 'Login Success';

            login_auth($kiw_db, $kiw_log);


            login_user($kiw_username, $kiw_password, $session_id);
        }
        else{

            $kiw_log['status'] = 'failed';
            $kiw_log['reason'] = $data['message'];

            login_auth($kiw_db, $kiw_log);

            file_put_contents(dirname(__FILE__, 6) . "/logs/{$tenant}/kiwire-db-integrate-{$tenant}-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s :: ") .  "User : {$kiw_username} = Call Api and Failed responsd. Redirect back [check username or password].\n", FILE_APPEND);

            error_redirect($_SERVER['HTTP_REFERER'], "[100] {$data['message']}");
        }
    // }
}
else{

    file_put_contents(dirname(__FILE__, 6) . "/logs/{$tenant}/kiwire-db-integrate-{$tenant}-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s :: ") .  "User : {$kiw_username} = User NOT found in Kiwire DB\n", FILE_APPEND);


    //getting user info by API
    $data = call_api($kiw_db, $body, $header, $kiw_log);

    file_put_contents(dirname(__FILE__, 6) . "/logs/{$tenant}/kiwire-db-integrate-{$tenant}-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s :: ") .  "User : {$kiw_username} = Calling API \n", FILE_APPEND);

    file_put_contents(dirname(__FILE__, 6) . "/logs/{$tenant}/kiwire-db-integrate-{$tenant}-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s :: ") .  "User : {$kiw_username} = Response API ". json_encode($data) ." \n", FILE_APPEND);

    if($data['success']){

        $domain = explode('@', $kiw_username);

        //user doesnt exist in kiwire table, need to create user
        $kiw_create_user = array();

        $kiw_create_user['username']        = $kiw_username;
        $kiw_create_user['password']        = $kiw_password;
        $kiw_create_user['fullname']        = $data['entry']['name'];
        $kiw_create_user['email_address']   = !empty($data['entry']['email']) ? $data['entry']['email'] : $data['entry']['altemail'];
        $kiw_create_user['phone_number']    = $data['entry']['telephonenumber'];

        $kiw_create_user['tenant_id']       = $_SESSION['controller']['tenant_id'];
        $kiw_create_user['profile_subs']    = $data['entry']['group'] ? $data['entry']['group']  : ($domain[1] == 'um.edu.my' ? 'staff' : 'student' );
        $kiw_create_user['ktype']           = "account";
        $kiw_create_user['status']          = "active";
        $kiw_create_user['integration']     = "int";
        $kiw_create_user['allowed_zone']    = 'none';
        $kiw_create_user['date_value']      = "NOW()";
        $kiw_create_user['remark']          = $data['entry']['group'] . ' - active by api';

        // $kiw_attribute['remark']               = $data['entry']['group'] . ' - active by api';
        // $kiw_attribute['manage-guest-account'] = "false";

        // $kiw_create_user['remark'] = json_encode($kiw_attribute);

        // unset($kiw_attribute);

        //create new user
        $create = create_account($kiw_db, $kiw_cache, $kiw_create_user);

        file_put_contents(dirname(__FILE__, 6) . "/logs/{$tenant}/kiwire-db-integrate-{$tenant}-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s :: ") .  "User : {$kiw_username} = Success API and store account in Kiwire DB\n", FILE_APPEND);

        unset($kiw_create_user);

        file_put_contents(dirname(__FILE__, 6) . "/logs/{$tenant}/kiwire-db-integrate-{$tenant}-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s :: ") .  "User : {$kiw_username} = User proceed login. .\n", FILE_APPEND);

        $kiw_log['status'] = 'success';
        $kiw_log['reason'] = 'Login Success';

        login_auth($kiw_db, $kiw_log);

        login_user($kiw_username, $kiw_password, $session_id);

    }
    else{

        $kiw_log['status'] = 'failed';
        $kiw_log['reason'] = $data['message'];

        login_auth($kiw_db, $kiw_log);

        file_put_contents(dirname(__FILE__, 6) . "/logs/{$tenant}/kiwire-db-integrate-{$tenant}-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s :: ") .  "User : {$kiw_username} = Failed API [check username or password]\n", FILE_APPEND);

        error_redirect($_SERVER['HTTP_REFERER'], "[101] {$data['message']}");
    }

}

function call_api($kiw_db, $body, $header, $kiw_log){

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.um.edu.my/v1/ldap/login",
        CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => json_encode($body),
        CURLOPT_HTTPHEADER => $header,
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    $response = json_decode($response, true);

    if(in_array($response['entry']['group'], array('student', 'umcced')))
        $user_zone = 'student';
    else if(in_array($response['entry']['group'], array('staff', 'PTM-Technical', 'PTM-SecurityDevice')))
        $user_zone = 'staff';

    file_put_contents(dirname(__FILE__, 6) . "/logs/{$_SESSION['controller']['tenant_id']}/kiwire-db-integrate-{$_SESSION['controller']['tenant_id']}-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s :: ") .  "User : {$_REQUEST['username']} = Zone detected : [ {$_SESSION['user']['zone']} ] || User Zone : [ {$response['entry']['group']} ] \n", FILE_APPEND);
    
    if($user_zone != strtolower($_SESSION['user']['zone'])){

        $kiw_log['status'] = 'failed';
        $kiw_log['reason'] = 'Zone not allowed';

        login_auth($kiw_db, $kiw_log);

        error_redirect($_SERVER['HTTP_REFERER'], 'Your are not allowed in this zone');

    }

    return $response;

}
