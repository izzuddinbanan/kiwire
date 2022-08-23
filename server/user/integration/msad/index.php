<?php

require_once dirname(__FILE__, 3) . "/includes/include_session.php";
require_once dirname(__FILE__, 3) . "/includes/include_general.php";
require_once dirname(__FILE__, 3) . "/includes/include_account.php";

require_once dirname(__FILE__, 4) . "/admin/includes/include_connection.php";


$kiw_temp = $kiw_cache->get("MSAD_DATA:{$_SESSION['controller']['tenant_id']}");

if (empty($kiw_temp)) {


    $kiw_temp = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_int_msad WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

    if (empty($kiw_temp)) $kiw_temp = array("dummy" => true);

    $kiw_cache->set("MSAD_DATA:{$_SESSION['controller']['tenant_id']}", $kiw_temp, 1800);


}



if ($kiw_temp['enabled'] == "y"){


    $kiw_notification = $kiw_cache->get("NOTIFICATION_DATA:{$_SESSION['controller']['tenant_id']}");

    if (empty($kiw_notification)) {


        $kiw_notification = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_notification WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

        if (empty($kiw_notification)) $kiw_notification = array("dummy" => true);

        $kiw_cache->set("NOTIFICATION_DATA:{$_SESSION['controller']['tenant_id']}", $kiw_notification, 1800);


    }


    $kiw_username = $kiw_db->escape($_REQUEST['username']);
    $kiw_password = $kiw_db->escape($_REQUEST['password']);

    // send request to kiwire integration service to check if valid. the create user if not exist or perform login


    $kiw_data['action']     = "check_msad";
    $kiw_data['tenant_id']  = $_SESSION['controller']['tenant_id'];
    $kiw_data['username']   = $kiw_username;
    $kiw_data['password']   = $kiw_password;


    $kiw_temp = curl_init();

    curl_setopt($kiw_temp, CURLOPT_URL, "http://127.0.0.1:9956");
    curl_setopt($kiw_temp, CURLOPT_POST, true);
    curl_setopt($kiw_temp, CURLOPT_POSTFIELDS, http_build_query($kiw_data));
    curl_setopt($kiw_temp, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($kiw_temp, CURLOPT_TIMEOUT, 5);
    curl_setopt($kiw_temp, CURLOPT_CONNECTTIMEOUT, 5);


    $kiw_login_status = curl_exec($kiw_temp);


    curl_close($kiw_temp);

    unset($kiw_temp);


    $kiw_login_status = json_decode($kiw_login_status, true);


    if ($kiw_login_status['status'] == "success"){


        // if successful then login user

        login_user($kiw_username, $kiw_password, $_REQUEST['session']);


    } else {


        $_SESSION['response']['error'] = $kiw_notification['error_wrong_credential'];

        header("Location: /user/pages/?session={$_REQUEST['session']}");


    }



} else {

    error_redirect($_SERVER['HTTP_REFERER'], "You are not allowed to access this module");

}




