<?php


require_once dirname(__FILE__, 3) . "/includes/include_general.php";

require_once dirname(__FILE__, 4) . "/admin/includes/include_general.php";
require_once dirname(__FILE__, 4) . "/admin/includes/include_connection.php";


$kiw_data = sync_decrypt($_REQUEST['data']);

$kiw_data = json_decode($kiw_data, true);


if (is_array($kiw_data)){


    // sent email to end user if required

    $kiw_signup = $kiw_cache->get("SPONSOR_VERIFICATION:{$kiw_data['tenant_id']}");

    if (empty($kiw_signup)){

        $kiw_signup = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_signup_visitor WHERE tenant_id = '{$kiw_data['tenant_id']}' LIMIT 1");

        if (empty($kiw_signup)) $kiw_signup = array("dummy" => true);

        $kiw_cache->set("SPONSOR_VERIFICATION:{$kiw_data['tenant_id']}", $kiw_signup, 1800);

    }


    $kiw_user = $kiw_db->query_first("SELECT * FROM kiwire_account_auth WHERE username = '{$kiw_data['username']}' AND tenant_id = '{$kiw_data['tenant_id']}' AND status = 'suspend' LIMIT 1");


    if (!empty($kiw_user)){


        if ($kiw_signup['enabled'] == "y"){


            $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), status = 'active' WHERE username = '{$kiw_data['username']}' AND tenant_id = '{$kiw_data['tenant_id']}' LIMIT 1");


            if ($kiw_signup['send_notification'] == "email"){


                $kiw_content = $kiw_cache->get("TEMPLATE:{$kiw_signup['tenant_id']}:" . md5($kiw_signup['content']));

                if (empty($kiw_content)) {


                    $kiw_content = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_html_template WHERE name = '{$kiw_signup['content']}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

                    if (empty($kiw_content)) {

                        $kiw_content['content'] = @file_get_contents(dirname(__FILE__, 3) . "/templates/account-verified.html");
                        $kiw_content['subject'] = "WIFI Registration: Account Verified";

                    }

                    $kiw_cache->set("TEMPLATE:{$kiw_signup['tenant_id']}:" . md5($kiw_signup['content']), $kiw_content, 1800);


                }


                $kiw_content['content'] = stripcslashes($kiw_content['content']);


                $kiw_content['content'] = str_replace("{{username}}", $kiw_user['username'], $kiw_content['content']);
                $kiw_content['content'] = str_replace("{{fullname}}", $kiw_user['fullname'], $kiw_content['content']);
                $kiw_content['content'] = str_replace("{{tenant_id}}", $kiw_user['tenant_id'], $kiw_content['content']);
                $kiw_content['content'] = str_replace("{{email_address}}", $kiw_user['email_address'], $kiw_content['content']);
                $kiw_content['content'] = str_replace("{{phone_number}}", "", $kiw_content['content']);


                $kiw_email['action']        = "send_email";
                $kiw_email['tenant_id']     = $kiw_user['tenant_id'];
                $kiw_email['email_address'] = $kiw_user['email_address'];
                $kiw_email['subject']       = $kiw_content['subject'];
                $kiw_email['content']       = htmlentities($kiw_content['content']);
                $kiw_email['name']          = $kiw_user['fullname'];


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


            } elseif ($kiw_signup['send_notification'] == "sms"){


                $kiw_content = $kiw_cache->get("TEMPLATE:{$kiw_signup['tenant_id']}:" . md5($kiw_signup['content']));

                if (empty($kiw_content)) {


                    $kiw_content = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_html_template WHERE name = '{$kiw_signup['confirmation_content']}' AND tenant_id = '{$kiw_signup['tenant_id']}' LIMIT 1");

                    if (empty($kiw_content)) {

                        $kiw_content['content'] = "Hi {{username}}. Your account has been verified. Thanks.";

                    }

                    $kiw_cache->set("TEMPLATE:{$kiw_signup['tenant_id']}:" . md5($kiw_signup['content']), $kiw_content, 1800);


                }


                $kiw_content['content'] = stripcslashes($kiw_content['content']);

                $kiw_sms['content'] = str_replace("{{username}}", $kiw_user['username'], $kiw_content['content']);
                $kiw_sms['content'] = str_replace("{{fullname}}", $kiw_user['fullname'], $kiw_sms['content']);
                $kiw_sms['content'] = str_replace("{{tenant_id}}", $kiw_user['tenant_id'], $kiw_sms['content']);
                $kiw_sms['content'] = str_replace("{{email_address}}", $kiw_user['email_address'], $kiw_sms['content']);
                $kiw_sms['content'] = str_replace("{{phone_number}}", $kiw_user['phone_number'], $kiw_sms['content']);


                $kiw_sms['content'] = strip_tags($kiw_sms['content']);


                $kiw_sms['action']       = "send_sms";
                $kiw_sms['tenant_id']    = $kiw_user['tenant_id'];
                $kiw_sms['phone_number'] = $kiw_user['phone_number'];


                $kiw_temp = curl_init();

                curl_setopt($kiw_temp, CURLOPT_URL, "http://127.0.0.1:9956");
                curl_setopt($kiw_temp, CURLOPT_POST, true);
                curl_setopt($kiw_temp, CURLOPT_POSTFIELDS, http_build_query($kiw_sms));
                curl_setopt($kiw_temp, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($kiw_temp, CURLOPT_TIMEOUT, 5);
                curl_setopt($kiw_temp, CURLOPT_CONNECTTIMEOUT, 5);

                unset($kiw_sms);


                curl_exec($kiw_temp);

                curl_close($kiw_temp);


                unset($kiw_temp);


            }


        }


    }


    header("Location: /user/pages/public/{$kiw_signup['tenant_id']}/{$kiw_signup['confirmed_page']}/");


}