<?php

require_once dirname(__FILE__, 2) . "/includes/include_session.php";
require_once dirname(__FILE__, 2) . "/includes/include_general.php";

require_once dirname(__FILE__, 3) . "/admin/includes/include_config.php";
require_once dirname(__FILE__, 3) . "/admin/includes/include_general.php";
require_once dirname(__FILE__, 3) . "/admin/includes/include_connection.php";


if ($_SESSION['system']['checked'] == "true") {


    $kiw_username           = $kiw_db->escape($_REQUEST['username']);
    $kiw_email_address      = $kiw_db->escape($_REQUEST['email_address']);
    $kiw_phone_no           = $kiw_db->escape($_REQUEST['phone_number']);


    //forgot password using only email
    $_SESSION['username_byemail'] = $kiw_db->escape($_REQUEST['username_email']);

    if ($_SESSION['username_byemail'] == "yes") {

        $kiw_email_address = $kiw_db->escape($_REQUEST['username']);
    
    }
    

    $kiw_user = $kiw_db->query_first("SELECT username,password,fullname FROM kiwire_account_auth WHERE username = '{$kiw_username}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");


    $kiw_notification = $kiw_cache->get("NOTIFICATION_DATA:{$_SESSION['controller']['tenant_id']}");

    if (empty($kiw_notification)) {


        $kiw_notification = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_notification WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

        if (empty($kiw_notification)) $kiw_notification = array("dummy" => true);

        $kiw_cache->set("NOTIFICATION_DATA:{$_SESSION['controller']['tenant_id']}", $kiw_notification, 1800);


    }


    if (is_array($kiw_user) && !empty($kiw_user)){


        $kiw_user_details = $kiw_db->query_first("SELECT username,email_address,phone_number FROM kiwire_account_auth WHERE username = '{$kiw_username}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");


        $kiw_cloud_config = $kiw_cache->get("CLOUD_DATA:{$_SESSION['controller']['tenant_id']}");

        if (empty($kiw_cloud_config)) {

            $kiw_cloud_config = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_clouds WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

            $kiw_cache->set("CLOUD_DATA:{$_SESSION['controller']['tenant_id']}", $kiw_cloud_config, 1800);

        }


        $kiw_temp_password = substr(md5(time() . $kiw_username . "SynHC*12jk3j2"), 0, 8);

        $kiw_temp_password = strtoupper($kiw_temp_password);


        $kiw_enc_password = sync_encrypt($kiw_temp_password);


        if ($kiw_cloud_config['forgot_password_method'] == "email"){


            if ($kiw_user_details['email_address'] != "NA" && $kiw_email_address == $kiw_user_details['email_address']){


                $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), password = '{$kiw_enc_password}' WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' AND username = '{$kiw_username}' LIMIT 1");


                $kiw_email_template = $kiw_cache->get("EMAIL_TEMPLATE_FP:{$_SESSION['controller']['tenant_id']}");

                if (empty($kiw_email_template)) {


                    $kiw_email_template = $kiw_db->query_first("SELECT * FROM kiwire_html_template WHERE name = '{$kiw_cloud_config['forgot_password_template']}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

                    if (empty($kiw_email_template)){


                        $kiw_email_template['content'] = @file_get_contents(dirname(__FILE__, 2) . "/templates/user-forgot-password.html");

                        $kiw_email_template['subject'] = "WIFI Access: Forgot Password";


                    }

                    $kiw_cache->set("EMAIL_TEMPLATE_FP:{$_SESSION['controller']['tenant_id']}", $kiw_email_template, 1800);


                }


                $kiw_email_template['content'] = stripcslashes($kiw_email_template['content']);


                $kiw_email_content = str_replace('{{tenant_id}}', $_SESSION['controller']['tenant_id'], $kiw_email_template['content']);
                $kiw_email_content = str_replace('{{username}}', $kiw_username, $kiw_email_content);
                $kiw_email_content = str_replace('{{temporary_password}}', $kiw_temp_password, $kiw_email_content);
                $kiw_email_content = str_replace('{{session_id}}', $_GET['session'], $kiw_email_content);
                $kiw_email_content = str_replace('{{system_name}}', sync_brand_decrypt(SYNC_PRODUCT), $kiw_email_content);


                $kiw_domain = parse_url($_SESSION['system']['domain'])['host'];

                if (empty($kiw_domain)) $kiw_domain = $_SESSION['system']['domain'];


                $kiw_email_content = str_replace('{{domain_used}}', $kiw_domain, $kiw_email_content);


                $kiw_email['action']        = "send_email";
                $kiw_email['tenant_id']     = $_SESSION['controller']['tenant_id'];
                $kiw_email['email_address'] = $kiw_user['username'];
                $kiw_email['subject']       = $kiw_email_template['subject'];
                $kiw_email['content']       = $kiw_email_content;
                $kiw_email['name']          = $kiw_user['fullname'];


                unset($kiw_email_template);


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

                unset($kiw_temp);


                error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['notification_password_reset']);


            } else {

                error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_user_email_mismatched']);

            }



        } elseif ($kiw_cloud_config['forgot_password_method'] == "sms"){


            if ($kiw_user_details['phone_number'] != "NA" && $kiw_phone_no == $kiw_user_details['phone_number']){


                $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), password = '{$kiw_enc_password}' WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' AND username = '{$kiw_username}' LIMIT 1");


                $kiw_sms_template = $kiw_cache->get("SMS_TEMPLATE_FP:{$_SESSION['controller']['tenant_id']}");

                if (empty($kiw_sms_template)) {


                    $kiw_sms_template = $kiw_db->query_first("SELECT * FROM kiwire_html_template WHERE name = '{$kiw_cloud_config['forgot_password_template']}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

                    if (empty($kiw_sms_template)){

                        $kiw_sms_template['content'] = "[ WIFI Access ] Your password is {{password}}";

                    }

                    $kiw_cache->set("SMS_TEMPLATE_FP:{$_SESSION['controller']['tenant_id']}", $kiw_sms_template, 1800);


                }


                $kiw_sms_template['content'] = stripcslashes($kiw_sms_template['content']);

                $kiw_sms_content = str_replace('{{tenant_id}}', $_SESSION['controller']['tenant_id'], $kiw_sms_template['content']);
                $kiw_sms_content = str_replace('{{username}}', $kiw_username, $kiw_sms_content);
                $kiw_sms_content = str_replace('{{password}}', $kiw_temp_password, $kiw_sms_content);


                unset($kiw_sms_template);


                $kiw_sms['action']       = "send_sms";
                $kiw_sms['tenant_id']    = $_SESSION['controller']['tenant_id'];
                $kiw_sms['phone_number'] = $kiw_user['username'];
                $kiw_sms['content']      = $kiw_sms_content;

                unset($kiw_sms_content);


                $kiw_temp = curl_init();

                curl_setopt($kiw_temp, CURLOPT_URL, "http://127.0.0.1:9956");
                curl_setopt($kiw_temp, CURLOPT_POST, true);
                curl_setopt($kiw_temp, CURLOPT_POSTFIELDS, http_build_query($kiw_sms));
                curl_setopt($kiw_temp, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($kiw_temp, CURLOPT_TIMEOUT, 5);
                curl_setopt($kiw_temp, CURLOPT_CONNECTTIMEOUT, 5);

                unset($kiw_content);

                curl_exec($kiw_temp);


                error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['notification_password_reset']);


            } else {

                error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_user_sms_mismatched']);

            }


        } else {

            error_redirect($_SERVER['HTTP_REFERER'], "You are not allowed to access this module");

        }




    } else {

        error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_user_not_found']);

    }



}




