<?php

require_once dirname(__FILE__, 2) . "/includes/include_session.php";
require_once dirname(__FILE__, 2) . "/includes/include_registration.php";
require_once dirname(__FILE__, 2) . "/includes/include_general.php";
require_once dirname(__FILE__, 2) . "/includes/include_account.php";

require_once dirname(__FILE__, 3) . "/admin/includes/include_config.php";
require_once dirname(__FILE__, 3) . "/admin/includes/include_general.php";
require_once dirname(__FILE__, 3) . "/admin/includes/include_connection.php";


if ($_SESSION['system']['checked'] == "true"){


    $kiw_notification = $kiw_cache->get("NOTIFICATION_DATA:{$_SESSION['controller']['tenant_id']}");

    if (empty($kiw_notification)) {


        $kiw_notification = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_notification WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

        if (empty($kiw_notification)) $kiw_notification = array("dummy" => true);

        $kiw_cache->set("NOTIFICATION_DATA:{$_SESSION['controller']['tenant_id']}", $kiw_notification, 1800);


    }


    $kiw_username               = $kiw_db->escape($_REQUEST['username']);
    $kiw_password_original      = $kiw_db->escape($_REQUEST['password']);
    $kiw_password_new           = $kiw_db->escape($_REQUEST['new_password']);
    $kiw_password_verification  = $kiw_db->escape($_REQUEST['ver_password']);


    if (empty($kiw_username) || empty($kiw_password_new)){

        error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_no_credential']);

    } elseif ($kiw_password_new != $kiw_password_verification) {

        error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_password_verification_failed']);

    } elseif (empty($kiw_password_original) || empty($kiw_password_verification)){
        
        error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_empty_password']);

    }


    // $kiw_user = $kiw_db->query_first("SELECT tenant_id,username,password,status,ktype FROM kiwire_account_auth WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' AND username = '{$kiw_username}' LIMIT 1");
    $kiw_user = $kiw_db->query_first("SELECT * FROM kiwire_account_auth WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' AND username = '{$kiw_username}' LIMIT 1");


    if ($kiw_user['status'] !== "active"){

        error_redirect("/user/pages/?session={$session_id}", $kiw_notification['error_inactive_account']);

    }


    if ($kiw_user['ktype'] == "voucher"){

        error_redirect("/user/pages/?session={$session_id}", "You are not allowed to change password of this account");

    }

    $kiw_multi_password = false;

    // Remember username and password
    $_SESSION['user']['page_data'] = base64_encode(json_encode(array("username" => $kiw_user['username'], "password" => $kiw_password_original)));


    if ($kiw_user['integration'] == "pms" && empty($kiw_user['date_password'])) {


        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_int_pms WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

        if (empty($kiw_temp)) {

            error_redirect("/user/pages/?session={$session_id}", "You are not allowed to change password of this [pms] account");

        }
    

        if (!empty($kiw_user['fullname'])) {



            $kiw_fullnames = explode("|", $kiw_user['fullname']);


            foreach ($kiw_fullnames as $kiw_fullname) {


                // make sure no spaces

                $kiw_fullname = trim($kiw_fullname);


                // set the password using the setting

                switch ($kiw_temp['pass_mode']) {

                    case "0":
                        $kiw_pms_password = $kiw_temp['pass_predefined'];
                        break;

                    case "1":
                        $kiw_pms_password = $kiw_user['username'];
                        break;

                    case "2":

                        $kiw_pms_password = array_filter(explode(",", $kiw_fullname))[0];
                        $kiw_pms_password = trim($kiw_pms_password);

                        break;

                    case "3":

                        $kiw_pms_password = array_filter(explode(",", $kiw_fullname));
                        $kiw_pms_password = trim(end($kiw_pms_password));

                        break;

                    case "4":
                        $kiw_pms_password = $kiw_fullname;
                        break;

                    case "5":
                        $kiw_pms_password = $kiw_user['password'];
                        break;
                }

                // check pms user first login setting


                if (in_array($kiw_temp['pass_mode'], array("2", "3", "4"))) {


                    similar_text(strtolower($kiw_password_original), strtolower($kiw_pms_password), $kiw_password_similar);


                    if ($kiw_password_similar >= $kiw_temp['pass_percentage']) {


                        $kiw_multi_password = true;

                        break;

                    }
                } elseif ($kiw_pms_password == sync_encrypt($kiw_request['password'])) {


                    $kiw_multi_password = true;

                    break;


                }
            }


        
        } else {

            $_SESSION['user']['page_data'] = base64_encode(json_encode(array("username" => $kiw_user['username'], "password" => $kiw_password_original, "input_type_username" => "text", "input_type_password" => "password")));
            error_redirect("/user/pages/?session={$session_id}", $kiw_notification['error_wrong_credential']);

        }
    


    }
    


    if (!empty($kiw_user['password']) && ($kiw_user['password'] == sync_encrypt($kiw_password_original) || $kiw_multi_password == true)) {


        // change the password to encrypted for comparison


        if (!empty($kiw_user['password']) && !empty($kiw_password_original)) {


            // get password history

            $kiw_user_password = $kiw_db->query_first("SELECT password_history,date_password FROM kiwire_account_auth WHERE username = '{$kiw_user['username']}' AND tenant_id = '{$kiw_user['tenant_id']}' LIMIT 1");


            // get policies as we need to use later if success

            $kiw_policies = $kiw_cache->get("PASSWORD_POLICIES:{$_SESSION['controller']['tenant_id']}");

            if (empty($kiw_policies)){


                $kiw_policies = $kiw_db->query_first("SELECT * FROM kiwire_policies WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

                if (empty($kiw_policies)) $kiw_policies = array("dummy" => true);

                $kiw_cache->set("PASSWORD_POLICIES:{$_SESSION['controller']['tenant_id']}", $kiw_policies, 1800);


            }



            $kiw_password_test = check_password_policy($kiw_db, $kiw_cache, $_SESSION['controller']['tenant_id'], $kiw_notification, $kiw_username, $kiw_password_new, $kiw_password_original, $kiw_user_password['password_history']);


            if ($kiw_password_test !== true){

                error_redirect("/user/pages/?session={$session_id}", $kiw_password_test);

            }


            $kiw_password_new = sync_encrypt($kiw_password_new);

            $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), password = '{$kiw_password_new}', date_password = NOW() WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' AND username = '{$kiw_username}' LIMIT 1");


            if ($kiw_policies['auto_login'] == "y"){

                login_user($kiw_username, sync_decrypt($kiw_password_new), $_GET['session']);

            } else {


                $_SESSION['user']['current'] = next_page($_SESSION['user']['journey'], $_SESSION['user']['current'], $_SESSION['user']['default']);

                error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['notification_password_changed']);


            }


        } else {

            $_SESSION['user']['page_data'] = base64_encode(json_encode(array("username" => $kiw_user['username'], "password" => $kiw_password_original, "input_type_username" => "text", "input_type_password" => "password")));

            error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_password_verification_failed']);

        }


    } else {


        $_SESSION['user']['page_data'] = base64_encode(json_encode(array("username" => $kiw_user['username'], "password" => $kiw_password_original, "input_type_username" => "text", "input_type_password" => "password")));

        error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_wrong_credential']);


    }


} else {

    error_redirect($_SERVER['HTTP_REFERER'], "You are not allowed to access this page");

}

