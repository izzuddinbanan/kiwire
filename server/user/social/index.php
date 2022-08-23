<?php

require_once dirname(__FILE__, 2) . "/includes/include_redirect_from_login.php";

require_once dirname(__FILE__, 2) . "/includes/include_session.php";
require_once dirname(__FILE__, 2) . "/includes/include_account.php";
require_once dirname(__FILE__, 2) . "/includes/include_general.php";

require_once dirname(__FILE__, 3) . "/libs/class.sql.helper.php";

require_once dirname(__FILE__, 3) . "/admin/includes/include_general.php";
require_once dirname(__FILE__, 3) . "/admin/includes/include_connection.php";


if (!isset($_REQUEST['complete'])) {


    if ($_SESSION['system']['checked'] == true && !empty($_SESSION['controller']['tenant_id'])) {


        $kiw_temp = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_int_social WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

        $kiw_social_type = $kiw_db->escape($_REQUEST['type']);


        if ($kiw_temp["{$kiw_social_type}_en"] == "y") {


            // try to use cloud license

            $kiw_social_license = @file_get_contents( dirname(__FILE__, 3) . "/custom/{$_SESSION['controller']['tenant_id']}/tenant.license");


            // if no cloud license then use mutli-tenant license

            if (empty($kiw_social_license)) $kiw_social_license = @file_get_contents(dirname(__FILE__, 3) . "/custom/cloud.license");



            // all goods then redirect user to social gate

            if (!empty($kiw_social_license) && !empty($kiw_social_type)) {


                $kiw_protocol = $_SERVER['https'];


                if ($kiw_protocol == "on") $kiw_protocol = "https";
                else $kiw_protocol = "http";


                if (!empty($_POST)) {

                    $_SESSION['user']['data'] = base64_encode(json_encode($_POST));

                }


                header("Location: " . sync_brand_decrypt(SYNC_SOCIAL_URL) . "?version=3&license={$kiw_social_license}&method={$kiw_social_type}&domain=" . urlencode($_SESSION['system']['domain']) . "&protocol={$kiw_protocol}&session={$session_id}");


            } else {

                error_redirect($_SERVER['HTTP_REFERER'], "No license provided. Please contact your network administrator.");

            }


        } else {

            error_redirect("/user/pages/?session=" . $session_id, "This social login option has been disabled. Please contact your network administrator.");

        }


    }


} else {


    if (!isset($_REQUEST['error'])) {


        $signature = $_REQUEST['signature'];


        if ($signature == md5($_POST['uid'] . substr($_POST['email'], 0, 6) . "SyncSocial^*123")) {


            // collect data and escape

            foreach ($_POST as $item => $value) {

                $kiw_data[$item] = $kiw_db->escape($value);

            }


            $kiw_temp = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_int_social WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

            $kiw_type = strtolower($_REQUEST['socialgate_type']);


            switch ($kiw_type){
                case 'facebook':    $kiw_account_prefix = "Facebook_"; break;
                case 'line':        $kiw_account_prefix = "Line_"; break;
                case 'kakao':       $kiw_account_prefix = "Kakao_"; break;
                case 'zalo':        $kiw_account_prefix = "Zalo_"; break;
                case 'twitter':     $kiw_account_prefix = "Twitter_"; break;
                case 'instagram':   $kiw_account_prefix = "Instagram_"; break;
                case 'microsoft':   $kiw_account_prefix = "Microsoft_"; break;
                case 'linkedin':    $kiw_account_prefix = "Linkedin_"; break;
                case 'vk':          $kiw_account_prefix = "VK_"; break;
                case 'wechat':      $kiw_account_prefix = "Wechat_"; break;
            }



            if ($kiw_temp["{$kiw_type}_en"] == "y") {


                if (empty(trim($kiw_data['uid']))){

                    error_redirect("/user/pages/?session={$session_id}", "User reject authentication");

                    die();

                }



                // if microsoft, check the domain

                if ($kiw_type == "microsoft"){


                    $kiw_test = explode("@", $kiw_data['email']);

                    if (!empty($kiw_temp['365_domain'])){


                        $kiw_temp['365_domain'] = ltrim($kiw_temp['365_domain'], "@");


                        if ($kiw_test[1] !== $kiw_temp['365_domain']){

                            error_redirect("/user/pages/?session={$session_id}", "Only domain [ {$kiw_temp['365_domain']} ] allowed to login");

                        }


                    }


                    $kiw_temp['profile']        = $kiw_temp["microsoft_profile"];
                    $kiw_temp['allowed_zone']   = $kiw_temp["microsoft_zone"];


                }


                // overwrite profile with zone profile if available

                if (isset($_SESSION['controller']['force_profile']) && !empty($_SESSION['controller']['force_profile'])){

                    $kiw_temp['profile'] = $_SESSION['controller']['force_profile'];

                }


                if (isset($_SESSION['controller']['force_allowed_zone']) && !empty($_SESSION['controller']['force_allowed_zone'])){

                    $kiw_temp['allowed_zone'] = $_SESSION['controller']['force_allowed_zone'];

                }



                $kiw_social_username = $kiw_account_prefix . trim($kiw_data['uid']);
                $kiw_social_username = $kiw_db->escape($kiw_social_username);


                $kiw_user = $kiw_db->query_first("SELECT username,password,date_last_login FROM kiwire_account_auth WHERE username = '{$kiw_social_username}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");


                if (empty($kiw_user)) {


                    $kiw_social_password = substr(hash("sha256", time()), 0, 12);

                    $kiw_user = array();

                    $kiw_user['tenant_id']          = $kiw_temp['tenant_id'];
                    $kiw_user['username']           = $kiw_social_username;
                    $kiw_user['password']           = $kiw_social_password;
                    $kiw_user['fullname']           = $kiw_data['fullname'];
                    $kiw_user['email_address']      = $kiw_data['email'];
                    $kiw_user['remark']             = "";
                    $kiw_user['profile_subs']       = $kiw_temp['profile'];
                    $kiw_user['profile_curr']       = $kiw_temp['profile'];
                    $kiw_user['ktype']              = "account";
                    $kiw_user['status']             = "active";
                    $kiw_user['integration']        = "social";
                    $kiw_user['allowed_zone']       = $kiw_temp['allowed_zone'];
                    $kiw_user['date_value']         = "NOW()";
                    $kiw_user['date_password']      = "NOW()";

                    if (create_account($kiw_db, $kiw_cache, $kiw_user) == false){

                        error_redirect("/user/pages/?session={$session_id}", "Invalid profile subscribed.");

                    }

                    unset($kiw_user);

                    $kiw_user = array();

                    $kiw_user['username']       = $kiw_social_username;
                    $kiw_user['fullname']       = $kiw_data['fullname'];
                    $kiw_user['gender']         = ucfirst($kiw_data['gender']);
                    $kiw_user['picture']        = $kiw_data['image_link'];
                    $kiw_user['email_address']  = $kiw_data['email'];
                    $kiw_user['birthday']       = $kiw_data['birthday'];
                    $kiw_user['interest']       = $kiw_data['interest'];
                    $kiw_user['source']         = $kiw_type;
                    $kiw_user['age_group']      = $kiw_data['age_range'];
                    $kiw_user['location']       = ucfirst($kiw_data['location']);
                    $kiw_user['tenant_id']      = $kiw_temp['tenant_id'];


                    // get all data required

                    if (!empty($_SESSION['user']['data'])) {


                        $kiw_mapping = file_get_contents(dirname(__FILE__, 3) . "/custom/{$_SESSION['controller']['tenant_id']}/data-mapping.json");

                        $kiw_mapping = json_decode($kiw_mapping, true);


                        if (!empty($kiw_temp['data']) && count($kiw_mapping) > 0) {


                            $_SESSION['user']['data'] = json_decode(base64_decode($_SESSION['user']['data']), true);


                            foreach (explode(",", $kiw_temp['data']) as $kiw_data) {

                                if (isset($_SESSION['user']['data'][$kiw_data]) && !empty($_SESSION['user']['data'][$kiw_data])) {

                                    foreach ($kiw_mapping as $kiw_map) {

                                        if ($kiw_map['variable'] == $kiw_data) {


                                            $kiw_user[$kiw_map['field']] = $kiw_db->escape($_SESSION['user']['data'][$kiw_data]);

                                            break;


                                        }

                                    }

                                }

                            }


                            unset($_SESSION['user']['data']);


                        }


                    }


                    $kiw_db->query(sql_insert($kiw_db, "kiwire_account_info", $kiw_user));


                } else {


                    $kiw_social_password = sync_decrypt($kiw_user['password']);

                    if (time() - strtotime($kiw_user['date_last_login']) > (86400 * 30)) {


                        $kiw_user = array();

                        $kiw_user['fullname']       = $kiw_data['fullname'];
                        $kiw_user['gender']         = $kiw_data['gender'];
                        $kiw_user['picture']        = $kiw_data['image_link'];
                        $kiw_user['email_address']  = $kiw_data['email'];
                        $kiw_user['birthday']       = $kiw_data['birthday'];
                        $kiw_user['interest']       = $kiw_data['interest'];
                        $kiw_user['age_group']      = $kiw_data['age_range'];
                        $kiw_user['location']       = $kiw_data['location'];

                        $kiw_db->query(sql_update($kiw_db, "kiwire_account_info", $kiw_user, "tenant_id = '{$kiw_temp['tenant_id']}' AND username = '{$kiw_social_username}' LIMIT 1"));


                        // update password so not rejected by password policy

                        $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), date_password = NOW() WHERE username = '{$kiw_social_username}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");


                    }


                }


                login_user($kiw_social_username, $kiw_social_password, $session_id);


            } else {

                error_redirect("/user/pages/?session={$session_id}", "This social login option has been disabled. Please contact your network administrator.");

            }


        } else {

            print_error_message(106, "Wrong Signature", "Wrong singnature received. Possible fake request.");

        }


    } else {

        error_redirect("/user/pages/?session={$session_id}", base64_decode($_GET['error']));

    }


}