<?php

require_once dirname(__FILE__, 4) . "/user/includes/include_redirect_from_login.php";
require_once dirname(__FILE__, 4) . "/user/includes/include_session.php";
require_once dirname(__FILE__, 4) . "/user/includes/include_general.php";
require_once dirname(__FILE__, 4) . "/user/includes/include_account.php";
require_once dirname(__FILE__, 4) . "/user/includes/include_registration.php";

require_once dirname(__FILE__, 4) . "/admin/includes/include_connection.php";

require_once dirname(__FILE__, 4) . "/libs/class.sql.helper.php";


$kiw_user['username']       = $kiw_db->escape($_REQUEST['username']);
$kiw_user['password']       = $kiw_db->escape($_REQUEST['password']);
$kiw_user['verification']   = $kiw_db->escape($_REQUEST['verification']);
$kiw_user['fullname']       = $kiw_db->escape($_REQUEST['fullname']);

$kiw_user['email_address']  = $kiw_db->escape($_REQUEST['email_address']);
$kiw_user['phone_number']   = $kiw_db->escape($_REQUEST['phone_number']);
$kiw_user['identity_no']    = $kiw_db->escape($_REQUEST['identity_no']);


$kiw_notification = $kiw_cache->get("NOTIFICATION_DATA:{$_SESSION['controller']['tenant_id']}");

if (empty($kiw_notification)) {


    $kiw_notification = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_notification WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

    if (empty($kiw_notification)) $kiw_notification = array("dummy" => true);

    $kiw_cache->set("NOTIFICATION_DATA:{$_SESSION['controller']['tenant_id']}", $kiw_notification, 1800);
}


if (empty($kiw_user['username'])) {

    error_redirect($_SERVER['HTTP_REFERER'], "Please provide your username");
} elseif ($kiw_user['password'] != $kiw_user['verification']) {

    error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_password_verification_failed']);
} elseif (empty($kiw_user['password']) || empty($kiw_user['verification'])) {

    error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_empty_password']);
} elseif (empty($kiw_user['identity_no'])) {

    error_redirect($_SERVER['HTTP_REFERER'], "Please provide your IC number");
}


$kiw_signup = $kiw_cache->get("PUBLIC_SIGNUP_DATA:{$_SESSION['controller']['tenant_id']}");

if (empty($kiw_signup)) {

    $kiw_signup = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_signup_public WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

    if (empty($kiw_signup)) $kiw_signup = array("dummy" => true);

    $kiw_cache->set("PUBLIC_SIGNUP_DATA:{$_SESSION['controller']['tenant_id']}", $kiw_signup, 1800);
}


// overwrite profile with zone profile if available

if (isset($_SESSION['controller']['force_profile']) && !empty($_SESSION['controller']['force_profile'])) {

    $kiw_signup['profile'] = $_SESSION['controller']['force_profile'];
}


if (isset($_SESSION['controller']['force_allowed_zone']) && !empty($_SESSION['controller']['force_allowed_zone'])) {

    $kiw_signup['allowed_zone'] = $_SESSION['controller']['force_allowed_zone'];
}


if (!empty($kiw_signup) && $kiw_signup['enabled'] == "y") {


    if (check_account_exist($kiw_db, $kiw_user['username'], $_SESSION['controller']['tenant_id']) == false) {


        $kiw_password_test = check_password_policy($kiw_db, $kiw_cache, $_SESSION['controller']['tenant_id'], $kiw_notification, $kiw_username, $kiw_user['password']);

        if ($kiw_password_test !== true) {

            error_redirect("/user/pages/?session={$session_id}", $kiw_password_test);
        }


        $kiw_create_user = array();

        $kiw_create_user['tenant_id']      = $_SESSION['controller']['tenant_id'];
        $kiw_create_user['username']       = $kiw_user['username'];
        $kiw_create_user['password']       = $kiw_user['password'];
        $kiw_create_user['fullname']       = $kiw_user['fullname'];
        $kiw_create_user['email_address']  = $kiw_user['email_address'];
        $kiw_create_user['phone_number']   = $kiw_user['phone_number'];
        $kiw_create_user['remark']         = $kiw_signup['public_remark'];
        $kiw_create_user['profile_subs']   = $kiw_signup['profile'];
        $kiw_create_user['profile_curr']   = $kiw_signup['profile'];
        $kiw_create_user['ktype']          = "account";
        $kiw_create_user['status']         = "active";
        $kiw_create_user['integration']    = "int";
        $kiw_create_user['allowed_zone']   = $kiw_signup['allowed_zone'];
        $kiw_create_user['date_value']     = "NOW()";
        $kiw_create_user['date_expiry']    = date("Y-m-d H:i:s", strtotime("+{$kiw_signup['validity']} Day"));

        create_account($kiw_db, $kiw_cache, $kiw_create_user);

        $_SESSION['signup']['username'] = $kiw_create_user['username'];
        $_SESSION['signup']['password'] = $kiw_create_user['password'];



        unset($kiw_create_user);


        // get the data mapping setting

        // $kiw_mapping = file_get_contents(dirname(__FILE__, 3) . "/custom/{$_SESSION['controller']['tenant_id']}/data-mapping.json");

        $kiw_mapping = file_get_contents(dirname(__FILE__, 3) . "/{$_SESSION['controller']['tenant_id']}/data-mapping.json");
        file_put_contents(dirname(__FILE__, 1) . "/controller.log",  "{$_SESSION['controller']['zone']} - {$_SESSION['controller']['id']} \n", FILE_APPEND);


        $kiw_mapping = json_decode($kiw_mapping, true);


        // collect all necessary data

        if (strlen($kiw_signup['data']) > 0 && count($kiw_mapping) > 0) {




            foreach (explode(",", $kiw_signup['data']) as $kiw_data) {


                //remove dash in ic number
                $kiw_identity_no = str_replace("-", "", $kiw_user['identity_no']);

                if ($kiw_data == "identity_no") $_REQUEST[$kiw_data] = md5($kiw_identity_no);

                if ($kiw_data == "identity_no_hash6") {
                    
                    $kiw_latest_6_digits                  = substr($kiw_identity_no, 0, 6);
                    $kiw_replace                          = str_replace($kiw_latest_6_digits, "XXXXXX", $kiw_latest_6_digits);
                    $kiw_remaining_6_digits               = substr($kiw_identity_no, 6, 12);

                    $_REQUEST[$kiw_data]  = $kiw_replace.$kiw_remaining_6_digits;
                
                }
               
                if($kiw_data == "zone")     $_REQUEST[$kiw_data]    = isset($_SESSION['controller']['zone']) ? $_SESSION['controller']['zone'] : "";
                if($kiw_data == "nas_id")   $_REQUEST[$kiw_data]    = isset($_SESSION['controller']['id']) ? $_SESSION['controller']['id'] : "";

                if (isset($_REQUEST[$kiw_data]) && !empty($_REQUEST[$kiw_data])) {

                    foreach ($kiw_mapping as $kiw_map) {

                        if ($kiw_map['variable'] == $kiw_data) {

                            $kiw_create_user[$kiw_map['field']] = $kiw_db->escape($_REQUEST[$kiw_data]);

                            break;
                        
                        }
                    }
                }
            }

            $kiw_create_user['tenant_id']        = $_SESSION['controller']['tenant_id'];
            $kiw_create_user['username']         = $kiw_user['username'];
            $kiw_create_user['fullname']         = $kiw_user['fullname'];

            $kiw_create_user['source']           = "system";
            $kiw_create_user['email_address']    = $kiw_user['email_address'];


            $kiw_db->query(sql_insert($kiw_db, "kiwire_account_info", $kiw_create_user));


            unset($kiw_create_user);
        }




        if ($kiw_signup['after_register'] == "internet") {


            login_user($kiw_user['username'], $kiw_user['password'], $session_id);
        
        
        } else {


            $_SESSION['user']['current'] = next_page($_SESSION['user']['journey'], $_SESSION['user']['current'], $_SESSION['user']['default']);

            error_redirect("/user/pages/?session={$session_id}", $kiw_notification['notification_account_created']);
        }
    
    
    } else {


        error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_username_existed']);
    }


} else {

    error_redirect($_SERVER['HTTP_REFERER'], "You are not allowed to access this module.");
}
