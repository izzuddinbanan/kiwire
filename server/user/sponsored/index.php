<?php

require_once dirname(__FILE__, 2) . "/includes/include_session.php";
require_once dirname(__FILE__, 2) . "/includes/include_general.php";
require_once dirname(__FILE__, 2) . "/includes/include_account.php";
require_once dirname(__FILE__, 2) . "/includes/include_registration.php";

require_once dirname(__FILE__, 3) . "/admin/includes/include_general.php";
require_once dirname(__FILE__, 3) . "/admin/includes/include_connection.php";


if ($_SESSION['system']['checked'] == true){


    $kiw_user['username']       = $kiw_db->escape($_REQUEST['username']);
    $kiw_user['password']       = $kiw_db->escape($_REQUEST['password']);
    $kiw_user['verification']   = $kiw_db->escape($_REQUEST['verification']);
    $kiw_user['sponsor']        = $kiw_db->escape($_REQUEST['sponsor']);
    $kiw_user['fullname']       = $kiw_db->escape($_REQUEST['fullname']);
    $kiw_user['phone_number']   = $kiw_db->escape($_REQUEST['phone_number']);
    $kiw_user['email_address']  = $kiw_db->escape($_REQUEST['email_address']);


    $kiw_notification = $kiw_cache->get("NOTIFICATION_DATA:{$_SESSION['controller']['tenant_id']}");

    if (empty($kiw_notification)) {


        $kiw_notification = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_notification WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

        if (empty($kiw_notification)) $kiw_notification = array("dummy" => true);

        $kiw_cache->set("NOTIFICATION_DATA:{$_SESSION['controller']['tenant_id']}", $kiw_notification, 1800);


    }



    if ($kiw_user['password'] != $kiw_user['verification']){


        error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_password_verification_failed']);


    } elseif (empty($kiw_user['username'])){


        error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_password_verification_failed']);


    } elseif (empty($kiw_user['password']) || empty($kiw_user['verification'])){


        error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_password_verification_failed']);


    } elseif (empty($kiw_user['sponsor'])){


        error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_missing_sponsor_email']);


    }


    $kiw_signup = $kiw_cache->get("SPONSOR_VERIFICATION:{$_SESSION['controller']['tenant_id']}");

    if (empty($kiw_signup)){

        $kiw_signup = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_signup_visitor WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

        if (empty($kiw_signup)) $kiw_signup = array("dummy" => true);

        $kiw_cache->set("SPONSOR_VERIFICATION:{$_SESSION['controller']['tenant_id']}", $kiw_signup, 1800);

    }


    // overwrite profile with zone profile if available

    if (isset($_SESSION['controller']['force_profile']) && !empty($_SESSION['controller']['force_profile'])){

        $kiw_signup['profile'] = $_SESSION['controller']['force_profile'];

    }


    if (isset($_SESSION['controller']['force_allowed_zone']) && !empty($_SESSION['controller']['force_allowed_zone'])){

        $kiw_signup['allowed_zone'] = $_SESSION['controller']['force_allowed_zone'];

    }


    $kiw_test = explode("@", $kiw_user['sponsor'])[1];


    // remove prefix @ if available

    $kiw_signup['domain'] = ltrim($kiw_signup['domain'], "@");


    if ($kiw_test == $kiw_signup['domain']){


        if (check_account_exist($kiw_db, $kiw_user['username'], $_SESSION['controller']['tenant_id']) == false){


            $kiw_password_test = check_password_policy($kiw_db, $kiw_cache, $_SESSION['controller']['tenant_id'], $kiw_notification, $kiw_username, $kiw_user['password']);

            if ($kiw_password_test !== true){

                error_redirect("/user/pages/?session={$session_id}", $kiw_password_test);

            }


            // create user with status suspended until someone enable the account

            $kiw_create_user = array();

            $kiw_create_user['tenant_id']      = $_SESSION['controller']['tenant_id'];
            $kiw_create_user['username']       = $kiw_user['username'];
            $kiw_create_user['password']       = $kiw_user['password'];
            $kiw_create_user['fullname']       = $kiw_user['fullname'];
            $kiw_create_user['phone_number']   = $kiw_user['phone_number'];
            $kiw_create_user['email_address']  = $kiw_user['email_address'];
            $kiw_create_user['remark']         = $kiw_signup['public_remark'];
            $kiw_create_user['profile_subs']   = $kiw_signup['profile'];
            $kiw_create_user['profile_curr']   = $kiw_signup['profile'];
            $kiw_create_user['ktype']          = "account";
            $kiw_create_user['status']         = "suspend";
            $kiw_create_user['integration']    = "int";
            $kiw_create_user['allowed_zone']   = $kiw_signup['allowed_zone'];
            $kiw_create_user['date_value']     = "NOW()";
            $kiw_create_user['date_expiry']    = date("Y-m-d H:i:s", strtotime("+{$kiw_signup['validity']} Day"));

            create_account($kiw_db, $kiw_cache, $kiw_create_user);

            unset($kiw_create_user);


            $kiw_mapping = file_get_contents(dirname(__FILE__, 3) . "/custom/{$_SESSION['controller']['tenant_id']}/data-mapping.json");

            $kiw_mapping = json_decode($kiw_mapping, true);


            // collect all necessary data

            if (strlen($kiw_signup['data']) > 0 && count($kiw_mapping) > 0) {


                foreach (explode(",", $kiw_signup['data']) as $kiw_data) {

                    if (isset($_REQUEST[$kiw_data]) && !empty($_REQUEST[$kiw_data])) {

                        foreach ($kiw_mapping as $kiw_map) {

                            if ($kiw_map['variable'] == $kiw_data) {


                                $kiw_create_user[$kiw_map['field']] = $kiw_db->escape($_REQUEST[$kiw_data]);

                                break;


                            }

                        }

                    }

                }


                $kiw_create_user['tenant_id']      = $_SESSION['controller']['tenant_id'];
                $kiw_create_user['username']       = $kiw_user['username'];
                $kiw_create_user['source']         = "system";

                $kiw_db->query(sql_insert($kiw_db, "kiwire_account_info", $kiw_create_user));

                unset($kiw_create_user);


            }


            // create the validation code

            $kiw_validation = sync_encrypt(json_encode(array("tenant_id" => $_SESSION['controller']['tenant_id'], "username" => $kiw_user['username'], "time" => time())));

            $kiw_validation = "{$_SESSION['system']['domain']}/user/sponsored/verify/?data={$kiw_validation}";


            // get the template content

            $kiw_content = $kiw_cache->get("TEMPLATE:SPONSOR_VERIFICATION:{$kiw_signup['tenant_id']}:" . md5($kiw_signup['verification_content']));

            if (empty($kiw_content)) {


                $kiw_content = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_html_template WHERE name = '{$kiw_signup['verification_content']}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

                if (empty($kiw_content)) {

                    $kiw_content['content'] = @file_get_contents(dirname(__FILE__, 2) . "/templates/sponsored-registration.html");
                    $kiw_content['subject'] = "WIFI Registration: New Account Verification";

                }

                $kiw_cache->set("TEMPLATE:SPONSOR_VERIFICATION:{$kiw_signup['tenant_id']}:" . md5($kiw_signup['verification_content']), $kiw_content, 1800);


            }


            $kiw_content['content'] = stripcslashes($kiw_content['content']);

            // send out the email to sponsor

            $kiw_content['content'] = str_replace("{{verification_link}}", $kiw_validation, $kiw_content['content']);
            $kiw_content['content'] = str_replace("{{tenant_id}}", $_SESSION['controller']['tenant_id'], $kiw_content['content']);


            foreach ($_REQUEST as $kiw_key => $kiw_value){

                $kiw_content['content'] = str_replace("{{{$kiw_key}}}", $kiw_db->escape($kiw_value), $kiw_content['content']);

            }


            $kiw_email['action']          = "send_email";
            $kiw_email['tenant_id']       = $_SESSION['controller']['tenant_id'];
            $kiw_email['email_address']   = $kiw_user['sponsor'];
            $kiw_email['subject']         = $kiw_content['subject'];
            $kiw_email['content']         = htmlentities($kiw_content['content']);
            $kiw_email['name']            = $kiw_user['fullname'];


            // send email to agent

            $kiw_temp = curl_init();

            curl_setopt($kiw_temp, CURLOPT_URL, "http://127.0.0.1:9956");
            curl_setopt($kiw_temp, CURLOPT_POST, true);
            curl_setopt($kiw_temp, CURLOPT_POSTFIELDS, http_build_query($kiw_email));
            curl_setopt($kiw_temp, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($kiw_temp, CURLOPT_TIMEOUT, 5);
            curl_setopt($kiw_temp, CURLOPT_CONNECTTIMEOUT, 5);

            unset($kiw_email);


            curl_exec($kiw_temp);

            curl_close($kiw_temp);


            unset($kiw_temp);


            $_SESSION['user']['current'] = $kiw_next = next_page($_SESSION['user']['journey'], $_SESSION['user']['current'], $_SESSION['user']['default']);


            header("Location: /user/pages/?session={$session_id}");


        } else {

            error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_username_existed']);

        }


    } else {


        error_redirect($_SERVER['HTTP_REFERER'], "Only domain @{$kiw_temp['domain']} allowed!");


    }


} else {


    error_redirect($_SERVER['HTTP_REFERER'], "You are not allowed to access this module.");


}