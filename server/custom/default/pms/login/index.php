<?php


require_once dirname(__FILE__, 5) . "/user/includes/include_redirect_from_login.php";
require_once dirname(__FILE__, 5) . "/user/includes/include_session.php";
require_once dirname(__FILE__, 5) . "/user/includes/include_account.php";
require_once dirname(__FILE__, 5) . "/user/includes/include_error.php";
require_once dirname(__FILE__, 5) . "/user/includes/include_general.php";

require_once dirname(__FILE__, 5) . "/admin/includes/include_config.php";
require_once dirname(__FILE__, 5) . "/admin/includes/include_general.php";
require_once dirname(__FILE__, 5) . "/admin/includes/include_connection.php";
require_once dirname(__FILE__, 5) . "/libs/class.sql.helper.php";


$kiw_username   = $_REQUEST['username'];
$kiw_password   = $_REQUEST['password'];

$kiw_password_decr = sync_decrypt($kiw_password);

$_SESSION['controller']['tenant_id'] = 'default';

$kiw_tenant     = $_SESSION['controller']['tenant_id'];


global $kiw_db, $session_id;



// Check tenant is correct to run the function
if($kiw_tenant != end(explode("/", dirname(__FILE__, 3)))) error_redirect($_SERVER['HTTP_REFERER'], "You are not allowed to access this module");




$kiw_notification = $kiw_cache->get("NOTIFICATION_DATA:{$_SESSION['controller']['tenant_id']}");

if (empty($kiw_notification)) {

    $kiw_notification = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_notification WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

    if (empty($kiw_notification)) $kiw_notification = array("dummy" => true);

    $kiw_cache->set("NOTIFICATION_DATA:{$_SESSION['controller']['tenant_id']}", $kiw_notification, 1800);

}





if (!empty($kiw_username) && !empty($kiw_password)) {


    $kiw_password_enc = sync_encrypt($kiw_password);
    // $kiw_username_enc = sync_encrypt($kiw_username);

    $kiw_guest_record = $kiw_db->query_first("SELECT * FROM kiwire_account_auth WHERE username = '{$kiw_username}' AND password = '{$kiw_password_enc}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");


    if (!empty($kiw_guest_record)) {

 

        $_SESSION["mid_login"]["username"] = $kiw_username;
        $_SESSION["mid_login"]["password"] = $kiw_password_enc;


        // GET PROFILE control:Simultaneous-Use;
        $kiw_profile = $kiw_db->query_first("SELECT * FROM kiwire_profiles WHERE name = '{$kiw_guest_record["profile_curr"]}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

        $kiw_attr = json_decode($kiw_profile["attribute"], true);


        // GET currect active session for user
        $kiw_total_user = $kiw_db->query_first("SELECT count(id) as kcount FROM kiwire_active_session WHERE username = '{$kiw_guest_record["username"]}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}'");


        if($kiw_total_user["kcount"] >= $kiw_attr["control:Simultaneous-Use"]) {


            error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_max_simultaneous_use']);

     

        }

        
        // if there is no quota usage yet, redirect user to profile plan selection page else redirect user to status page
        if (($kiw_guest_record["profile_subs"] == $kiw_guest_record["profile_curr"]) && ( ($kiw_guest_record['quota_in'] == 0 || $kiw_guest_record['quota_in'] == 0) || ($kiw_guest_record["quota_in"] == NULL || $kiw_guest_record["quota_out"] == NULL)) ) {
            
            
            // plan selection page 

            header("Location: /custom/default/pms/plan_selection/?session={$_GET['session']}");

            die();


        } else {
       
            login_user($kiw_username, $kiw_password, $session_id);

        }

    
    
    } else {

        error_redirect($_SERVER['HTTP_REFERER'], "Room No or Last Name doesn't exist. Please provide valid credentials.");

    }



} else {


    error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_no_credential']);


}