<?php

require_once dirname(__FILE__, 2) . "/includes/include_general.php";
require_once dirname(__FILE__, 2) . "/includes/include_connection.php";



$kiw_email = $kiw_db->escape($_REQUEST['email']);
$kiw_tenant = $kiw_db->escape($_REQUEST['tenant_id']);
$kiw_action = $kiw_db->escape($_REQUEST['action']);
$kiw_captcha = $kiw_db->escape($_REQUEST['captcha']);

header("Content-Type: application/json");


if ($kiw_action == "submit") {

    if (!empty($kiw_email) && !empty($kiw_tenant)) {


        session_start();


        if (!empty($kiw_captcha) && $_SESSION['captcha']['code'] == $kiw_captcha) {


            $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_admin WHERE tenant_id = '{$kiw_tenant}' AND email = '{$kiw_email}' LIMIT 1");


            if (!empty($kiw_temp)) {


                unset($_SESSION['captcha']['code']);


                $kiw_temp_password_raw = strtoupper(substr(md5(time() . rand(rand(1, 10), rand(1000, 9999))), 4, 8));

                $kiw_temp_password_hash = sync_encrypt($kiw_temp_password_raw);

                $kiw_db->query("UPDATE kiwire_admin SET updated_date = NOW(), password = '{$kiw_temp_password_hash}' WHERE tenant_id = '{$kiw_temp['tenant_id']}' AND username = '{$kiw_temp['username']}' LIMIT 1");


                $kiw_submit['action']         = "send_email";
                $kiw_submit['tenant_id']      = $kiw_temp['tenant_id'];
                $kiw_submit['email_address']  = $kiw_temp['email'];
                $kiw_submit['name']           = $kiw_temp['fullname'];
                $kiw_submit['subject']        = "Forgot Password: Temporary Password Has Been Generated";


                $kiw_submit['content'] = "Hi {$kiw_temp['fullname']},\n\n<br><br>We have generated a temporary password for you.\n\n<br><br>The password is: {$kiw_temp_password_raw}";

                
                $kiw_temp = curl_init();

                curl_setopt($kiw_temp, CURLOPT_URL, "http://127.0.0.1:9956");
                curl_setopt($kiw_temp, CURLOPT_POST, true);
                curl_setopt($kiw_temp, CURLOPT_POSTFIELDS, http_build_query($kiw_submit));
                curl_setopt($kiw_temp, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($kiw_temp, CURLOPT_TIMEOUT, 5);
                curl_setopt($kiw_temp, CURLOPT_CONNECTTIMEOUT, 5);

                unset($kiw_email);


                curl_exec($kiw_temp);

                curl_close($kiw_temp);


                unset($kiw_temp);


                echo json_encode(array("status" => "success", "message" => "SUCCESS: Temporary password has been sent to {$kiw_submit['email_address']}. Please check your email.", "data" => null));


            } else {

                echo json_encode(array("status" => "error", "message" => "ERROR: Email address does not exist", "data" => null));

            }


        } else {

            echo json_encode(array("status" => "error", "message" => "ERROR: Wrong verification code provided", "data" => null));

        }


    } else {

        echo json_encode(array("status" => "error", "message" => "ERROR: Missing email address or tenant id", "data" => null));

    }


} elseif ($kiw_action == "captcha"){


    session_start();


    require_once dirname(__FILE__, 3) . "/libs/simplecaptcha/simple-php-captcha.php";


    try {


        $_SESSION['captcha'] = simple_php_captcha();
        $_SESSION['last'] = time();

        echo json_encode(array("status" => "success", "message" => null, "data" => $_SESSION['captcha']['image_src']));


    } catch (Exception $e) {

        echo json_encode(array("status" => "error", "message" => "ERROR: " . $e->getMessage(), "data" => null));

    }



} elseif ($kiw_action == "login"){


    session_start();


    if (file_exists(dirname(__FILE__, 3) . "/custom/cloud.license")) {


        $kiw['multi-license'] = @file_get_contents(dirname(__FILE__, 3) . "/custom/cloud.license");
        $kiw['multi-license'] = sync_license_decode($kiw['multi-license']);

        if (is_array($kiw['multi-license']) && $kiw['multi-license']['multi-tenant'] == true) {

            $kiw['multi-tenant'] = true;

        } else {

            $kiw['multi-tenant'] = false;

        }


    } else {

        $kiw['multi-tenant'] = false;

    }


    $kiw['tenant-id'] = isset($_REQUEST['tenant']) ? preg_replace('/[^A-Za-z0-9_-]/', '', $_REQUEST['tenant']) : 'default';


    if ($kiw['tenant-id'] != "superuser") {


        $kiw['tenant-license'] = @file_get_contents(dirname(__FILE__, 3) . "/custom/{$kiw['tenant-id']}/tenant.license");
        $kiw['tenant-license'] = sync_license_decode($kiw['tenant-license']);

        $kiw['tenant-install-date'] = @file_get_contents(dirname(__FILE__, 3) . "/custom/{$kiw['tenant-id']}/tenant.data");
        $kiw['tenant-install-date'] = sync_brand_decrypt($kiw['tenant-install-date']);


    } else {


        $kiw['tenant-install-date'] = @file_get_contents(dirname(__FILE__, 3) . "/custom/cloud.data");
        $kiw['tenant-install-date'] = sync_brand_decrypt($kiw['tenant-install-date']);


    }


    if (!is_array($kiw['multi-license']) && !is_array($kiw['tenant-license'])) {


        if (empty($kiw['tenant-install-date']) || (time() - $kiw['tenant-install-date']) > (sync_brand_decrypt(SYNC_MAX_TRIAL_DAYS) * 86400)) {


            echo json_encode(array("status" => "failed", "message" => "Your trial already expired. Please contact our Sales representative.", "data" => null));

            die();


        } else {

            $kiw['tenant-valid'] = true;

        }


    } else {


        if (is_array($kiw['tenant-license'])) {

            $kiw['tenant-valid'] = ($kiw['tenant-license']['expire_on'] - time()) > 0;

        } else {

            $kiw['tenant-valid'] = ($kiw['multi-license']['expire_on'] - time()) > 0;

        }


    }




    $kiw_db = new mysqli(SYNC_DB1_HOST, SYNC_DB1_USER, SYNC_DB1_PASSWORD, SYNC_DB1_DATABASE, SYNC_DB1_PORT);

    if ($kiw_db->connect_errno) {

        echo json_encode(array("status" => "failed", "message" => "Database error. Please try again.", "data" => null));

        die();

    }


    $kiw['username'] = $kiw_db->escape_string($_REQUEST['username']);
    $kiw['password'] = sync_encrypt($_REQUEST['password']);


    $kiw_user = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_admin WHERE username = '{$kiw['username']}' AND tenant_id = '{$kiw['tenant-id']}' LIMIT 1");


    if ($kiw_user) {


        $kiw_user = $kiw_user->fetch_all(MYSQLI_ASSOC);
        $kiw_user = $kiw_user[0];

        if($kiw_user['is_active']){

            if ($kiw_user['password'] == $kiw['password']) {


                $_SESSION['token']      = md5(uniqid(mt_rand(), true));
                $_SESSION['id']         = $kiw_user['id'];
                $_SESSION['permission'] = $kiw_user['permission'];
                $_SESSION['role']       = $kiw_user['groupname'];
                $_SESSION['theme']      = $kiw_user['theme'];
                $_SESSION['email']      = $kiw_user['email'];
                $_SESSION['photo']      = $kiw_user['photo'];
    
    
                $_SESSION['user_name']              = $kiw_user['username'];
                $_SESSION['full_name']              = $kiw_user['fullname'];
                $_SESSION['first_login']            = $kiw_user['first_login'];
                $_SESSION['last_password_change']   = $kiw_user['last_change_pass'];
    
    
                $_SESSION['last_active'] = time();
    
    
                $_SESSION['multi_tenant'] = $kiw['multi-tenant'] ?: false;
                $_SESSION['tenant_valid'] = $kiw['tenant-valid'];
    
    
                $_SESSION['system_admin'] = true;
    
                if ($kiw['tenant-id'] == "superuser") {
    
    
                    $_SESSION['access_level']   = "superuser";
                    $_SESSION['tenant_allowed'] = $kiw_user['tenant_allowed'];
                    $_SESSION['tenant_id']      = $kiw_user['tenant_default'];
    
    
                } else {
    
    
                    $_SESSION['access_level'] = "administrator";
                    $_SESSION['tenant_id'] = $kiw_user['tenant_id'];
    
    
                }
    
    
                $kiw_data = $kiw_db->query("SELECT SQL_CACHE moduleid FROM kiwire_admin_group WHERE groupname = '{$_SESSION['role']}' AND tenant_id = '{$kiw['tenant-id']}' ORDER BY moduleid ASC LIMIT 500");
    
                if ($kiw_data) {
    
    
                    $kiw_data = $kiw_data->fetch_all(MYSQLI_ASSOC);
    
                    $_SESSION['access_group'] = array();
    
                    foreach ($kiw_data as $role) {
    
    
                        $kiw_temp = trim($role['moduleid']);
                        $kiw_temp_group = trim(explode('->', $kiw_temp)[0]);
    
    
                        if (!in_array($kiw_temp_group, $_SESSION['access_group'])) $_SESSION['access_group'][] =  $kiw_temp_group;
    
    
                        $_SESSION['access_list'][] = $kiw_temp;
    
    
                    }
    
                    unset($kiw_data);
    
    
                } else {
    
                    echo json_encode(array("status" => "failed", "message" => "There is no role setup for this user.", "data" => null));
    
                    die();
    
                }
    
    
                $kiw_data = $kiw_db->query("SELECT * FROM kiwire_clouds WHERE tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");
    
                if ($kiw_data) $kiw_data = $kiw_data->fetch_all(MYSQLI_ASSOC)[0];
    
    
                if ($kiw_data['status'] !== "y"){
    
                    echo json_encode(array("status" => "failed", "message" => "This tenant has been disabled", "data" => null));
    
                    die();
    
                }
    
    
                if (!empty($kiw_data)) {
    
    
                    $_SESSION['metrics'] = $kiw_data['volume_metrics'];
    
                    $_SESSION['company_name'] = $kiw_data['name'];
    
                    $_SESSION['timezone'] = $kiw_data['timezone'];
    
                    $_SESSION['date_format'] = $kiw_data['date_format'];
    
    
                    if (empty($_SESSION['metrics'])) $_SESSION['metrics'] = "Gb";
    
                    if (empty($_SESSION['timezone'])) $_SESSION['timezone'] = "Asia/Kuala_Lumpur";
    
                    if (empty($_SESSION['style'])){
                        if($kiw_data['custom_style'] == 'y') $_SESSION['style'] = true;
                        else if($kiw_data['custom_style'] == 'n') $_SESSION['style'] = false;
                    }
    
    
                } else {
    
                    echo json_encode(array("status" => "failed", "message" => "Missing tenant information", "data" => null));
    
                    die();
    
                }
    
    
                // set a default timezone if user not set
    
                if (empty($_SESSION['timezone'])) $_SESSION['timezone'] = "Asia/Kuala_Lumpur";
    
                
                // set a default date format if user not set
    
                if (empty($_SESSION['date_format'])) $_SESSION['date_format'] = "d-m-Y";
    
    
                if (($kiw['multi-tenant'] == true && $_SESSION['access_level'] == "superuser") || $kiw['multi-tenant'] == false) {
    
                    $_SESSION['system_info'] = true;
    
                } else {
    
                    $_SESSION['system_info'] = false;
    
                }
    
    
                // update last login for admin user
    
                $kiw_db->query("UPDATE kiwire_admin SET attempt_count = 0, attempt_time = NULL, is_active = 1, updated_date = NOW(), lastlogin = NOW() WHERE tenant_id = '{$kiw['tenant-id']}' AND username = '{$kiw['username']}' LIMIT 1");
    
    
                sync_logger("User: {$_SESSION['user_name']} login to system [ {$_SERVER['REMOTE_ADDR']} ]", $_SESSION['tenant_id']);
    
    
                if (($_SESSION['access_level'] == "superuser" && $kiw_user['require_mfactor'] == "y") || ($_SESSION['access_level'] !== "superuser" && $kiw_data['require_mfactor'] == "y") || !empty($kiw_user['mfactor_key'])) {
    
    
                    if (!empty($kiw_user['mfactor_key'])) {
    
    
                        $_SESSION['mfactkey'] = $kiw_user['mfactor_key'];
    
                        $_SESSION['2factors'] = false;
    
    
                        sync_logger("User: {$_SESSION['user_name']} pending 2-factors authentication", $_SESSION['tenant_id']);
    
                        echo json_encode(array("status" => "success", "message" => "", "data" => array("next" => "2factor", "page" => "")));
    
    
                    } else {
    
    
                        echo json_encode(array("status" => "success", "message" => "", "data" => array("next" => "default", "page" => "/admin/general_2factor_register.php?reason=missing-key")));
    
    
                    }
    
    
                } elseif (isset($_SESSION['page']) && !empty($_SESSION['page'])) {
    
    
                    unset($_SESSION['2factors']);
    
    
                    $kiw_test = base64_decode($_SESSION['page']);
    
    
                    if (strpos($kiw_test, "mfactor") == false) {
    
                        echo json_encode(array("status" => "success", "message" => "", "data" => array("next" => "resume", "page" => $kiw_test)));
    
                    } else {
    
                        echo json_encode(array("status" => "success", "message" => "", "data" => array("next" => "resume", "page" => "/admin/dashboard.php")));
    
                    }
    
    
                } else {
    
    
                    unset($_SESSION['2factors']);
    
                    if(isset($_REQUEST['page']) && !empty($_REQUEST['page']) ){
                        
                        echo json_encode(array("status" => "success", "message" => "", "data" => array("next" => "default", "page" => base64_decode($_REQUEST['page']))));
                    }
                    else{
    
                        echo json_encode(array("status" => "success", "message" => "", "data" => array("next" => "default", "page" => "/admin/dashboard.php")));
                    }
    
    
    
                }
    
    
            } else {

                $triedleft          = 0;
                $is_active          = 1;
                $old_attempt_time   = $kiw_user['attempt_time'];
                $cur_attempt_time   = date('Y-m-d H:i:s');

                $diff = round(abs(strtotime($cur_attempt_time) - strtotime($old_attempt_time)) / 60,2);
               
                if($diff < 5){ //interval 5 minute to detect as brute attack  

                    $attempt_count = $kiw_user['attempt_count'] + 1;

                    if($attempt_count == 5){
                        $is_active = 0;
                        $attempt_count = 0;
                    } 
                   
                }
                else $attempt_count = 1;


                if($attempt_count > 0)  $triedleft = 5 - (int)$attempt_count;
            

                $kiw_db->query("UPDATE kiwire_admin SET attempt_count = '{$attempt_count}', attempt_time = '{$cur_attempt_time}', is_active = '{$is_active}' WHERE username = '{$kiw['username']}' AND tenant_id = '{$kiw['tenant-id']}' LIMIT 1");

    
                echo json_encode(array("status" => "failed", "message" => "Incorrect username or password has been provided. You have ({$triedleft}) trial left", "data" => null));
    
    
            }
            
        }
        else {
    
    
            echo json_encode(array("status" => "failed", "message" => "Your account has been blocked. Please contact system administrator", "data" => null));


        }

        


    } else {


        echo json_encode(array("status" => "failed", "message" => "Incorrect username or password has been provided.", "data" => null));


    }


} elseif ($kiw_action == "mfactor-check"){


    session_start();

    require_once "../../libs/google-authenticator/PHPGangsta/GoogleAuthenticator.php";


    $kiw_code = $_REQUEST['auth_code'];

    $kiw_user_key = $_SESSION['mfactkey'];


    if (strlen($kiw_code) > 0) {


        $kiw_authenticator = new PHPGangsta_GoogleAuthenticator();

        $kiw_result = $kiw_authenticator->verifyCode($kiw_user_key, $kiw_code);


        if ($kiw_result == true) {


            sync_logger("User: {$_SESSION['user_name']} succeed 2-factors", $_SESSION['tenant_id']);

            $_SESSION['2factors'] = true;


            if (isset($_SESSION['page']) && !empty($_SESSION['page'])) {


                echo json_encode(array("status" => "success", "message" => "", "data" => array("next" => "resume", "page" => base64_decode($_SESSION['page']))));


            } else {


                echo json_encode(array("status" => "success", "message" => "", "data" => array("next" => "default", "page" => "/admin/dashboard.php")));


            }


        } else {


            $_SESSION['2factors'] = false;

            sync_logger("User: {$_SESSION['user_name']} failed 2-factors", $_SESSION['tenant_id']);

            echo json_encode(array("status" => "failed", "message" => "Wrong authentication code provided", "data" => null));


        }


    }

} else {

    echo json_encode(array("status" => "error", "message" => "ERROR: Missing credential", "data" => null));

}
