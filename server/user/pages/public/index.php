<?php


require_once dirname(__FILE__, 4) . "/admin/includes/include_connection.php";
require_once dirname(__FILE__, 4) . "/admin/includes/include_general.php";


$kiw_request = $_REQUEST['request'];
$kiw_request = explode("/", $kiw_request);


$kiw_db = Database::obtain();


$kiw_tenant = $kiw_db->escape($kiw_request[0]);
$kiw_page = $kiw_db->escape($kiw_request[1]);
$kiw_session = $kiw_db->escape($kiw_request[2]);

$kiw_message = base64_decode($kiw_request[3]);


unset($kiw_request);


if (!empty($kiw_session)){

    session_id($kiw_session);

}

session_start();


if (strlen($kiw_page) == 8) {


    if (!empty($kiw_page) && !empty($kiw_tenant)) {


        $kiw_temp = $kiw_cache->get("PAGE_DATA:{$kiw_tenant}:{$kiw_page}");

        if (empty($kiw_temp)) {


            $kiw_temp = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_login_pages WHERE unique_id = '{$kiw_page}' AND tenant_id = '{$kiw_tenant}' LIMIT 1");

            if (empty($kiw_temp)) $kiw_temp = array("dummy" => true);

            $kiw_cache->set("PAGE_DATA:{$kiw_tenant}:{$kiw_page}", $kiw_temp, 1800);


        }


        if (!empty($kiw_temp)){


            $kiw_custom_path = dirname(__FILE__, 4) . "/custom/{$kiw_tenant}";


            // use custom header if available

            if (file_exists( "{$kiw_custom_path}/scripts/header.php")) {

                require_once "{$kiw_custom_path}/scripts/header.php";

            } else {

                require_once dirname(__FILE__, 3) . "/header.php";

            }



            $kiw_content = base64_decode($kiw_temp['content']);


            // update what ever data in url to content

            $kiw_content = urldecode($kiw_content);


            // replace session if available

            $kiw_content = str_replace("{{session_id}}", $kiw_session, $kiw_content);

            $kiw_content = str_replace("{{message}}", $kiw_message, $kiw_content);


            // if page for status, then pull out data

            if ($kiw_temp['purpose'] == "status"){


                $kiw_username = $kiw_db->escape($_SESSION['user']['login']['username']);


                if (empty($kiw_username)) {


                    $kiw_username = $kiw_db->escape($_REQUEST['username']);
                    $kiw_password = $kiw_db->escape($_REQUEST['password']);


                    if (!empty($kiw_username)) {


                        $kiw_user = $kiw_db->query_first("SELECT tenant_id,username,password,ktype,profile_curr,status,session_time,quota_out,quota_in,date_activate,date_expiry FROM kiwire_account_auth WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant}' LIMIT 1");


                        if (($kiw_user['ktype'] == "voucher") || (!empty($kiw_user['password']) && $kiw_user['password'] == sync_encrypt($kiw_password))) {

                            $kiw_content = str_replace("{{tenant_id}}", $kiw_user['tenant_id'], $kiw_content);
                            $kiw_content = str_replace("{{username}}", $kiw_user['username'], $kiw_content);
                            $kiw_content = str_replace("{{session_time}}", $kiw_user['session_time'], $kiw_content);
                            $kiw_content = str_replace("{{quota_out}}", $kiw_user['quota_out'], $kiw_content);
                            $kiw_content = str_replace("{{quota_in}}", $kiw_user['quota_in'], $kiw_content);
                            $kiw_content = str_replace("{{date_activate}}", $kiw_user['date_activate'], $kiw_content);
                            $kiw_content = str_replace("{{date_expiry}}", $kiw_user['date_expiry'], $kiw_content);
                            $kiw_content = str_replace("{{status}}", $kiw_user['status'], $kiw_content);

                        }


                    }


                } else {


                    $kiw_user = $kiw_db->query_first("SELECT tenant_id,username,password,profile_curr,status,session_time,quota_out,quota_in,date_activate,date_expiry FROM kiwire_account_auth WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant}' LIMIT 1");

                    $kiw_content = str_replace("{{tenant_id}}", $kiw_user['tenant_id'], $kiw_content);
                    $kiw_content = str_replace("{{username}}", $kiw_user['username'], $kiw_content);
                    $kiw_content = str_replace("{{session_time}}", $kiw_user['session_time'], $kiw_content);
                    $kiw_content = str_replace("{{quota_out}}", $kiw_user['quota_out'], $kiw_content);
                    $kiw_content = str_replace("{{quota_in}}", $kiw_user['quota_in'], $kiw_content);
                    $kiw_content = str_replace("{{date_activate}}", $kiw_user['date_activate'], $kiw_content);
                    $kiw_content = str_replace("{{date_expiry}}", $kiw_user['date_expiry'], $kiw_content);
                    $kiw_content = str_replace("{{status}}", $kiw_user['status'], $kiw_content);


                }


                if (!empty($kiw_user['profile_curr'])){


                    $kiw_profile = $kiw_user['profile_curr'];


                    $kiw_user = $kiw_cache->get("PROFILE_DATA:{$kiw_tenant}:{$kiw_profile}");


                    if (empty($kiw_user)) {


                        $kiw_user = $kiw_db->query_first("SELECT SQL_CACHE FROM kiwire_profiles WHERE name = '{$kiw_profile}' AND tenant_id = '{$kiw_tenant}' LIMIT 1");

                        if (empty($kiw_user)) $kiw_user = array("dummy" => true);

                        $kiw_cache->set("PROFILE_DATA:{$kiw_tenant}:{$kiw_profile}", $kiw_user, 1800);


                    }


                    if ($kiw_user['dummy'] !== true){


                        $kiw_content = str_replace("{{profile_name}}", $kiw_user['name'], $kiw_content);
                        $kiw_content = str_replace("{{profile_type}}", $kiw_user['type'], $kiw_content);
                        $kiw_content = str_replace("{{profile_price}}", $kiw_user['price'], $kiw_content);

                        $kiw_user = json_decode($kiw_user['attribute'], true);

                        if ($kiw_user) {

                            $kiw_content = str_replace("{{profile_download}}", $kiw_user['reply:WISPr-Bandwidth-Max-Down'], $kiw_content);
                            $kiw_content = str_replace("{{profile_upload}}", $kiw_user['reply:WISPr-Bandwidth-Max-Up'], $kiw_content);
                            $kiw_content = str_replace("{{profile_quota}}", $kiw_user['control:Kiwire-Total-Quota'], $kiw_content);
                            $kiw_content = str_replace("{{profile_simultaneous_use}}", $kiw_user['control:Kiwire-Total-Quota'], $kiw_content);

                            $kiw_content = str_replace("{{profile_total_time}}", $kiw_user['control:Max-All-Session'], $kiw_content);
                            $kiw_content = str_replace("{{profile_expired_after}}", $kiw_user['control:Access-Period'], $kiw_content);

                        }


                    }


                }



                // slow down brute force if same session try to login multiple time

                if ($_SESSION['control']['login_count'] > 0){

                    sleep(5);

                } else $_SESSION['control']['login_count']++;


                // check previous similarity, if high then slow down 5 mins or event blocked

                $kiw_username_count = count($_SESSION['control']['login_previous']);

                if ($kiw_username_count > 0){


                    if ($kiw_username_count > 5){

                        sleep(5);

                        array_shift($_SESSION['control']['login_previous']);

                    }


                    foreach ($_SESSION['control']['login_previous'] as $kiw_previous) {


                        similar_text($kiw_username, $kiw_previous, $kiw_percentage);


                        if ($kiw_percentage > 60 && $kiw_percentage < 100) {

                            sleep(5);

                        }


                    }


                }


                if (!in_array($kiw_username, $_SESSION['control']['login_previous'])) {

                    $_SESSION['control']['login_previous'][] = $kiw_username;

                }


            }



            echo $kiw_content;


            // use custom footer if available

            if (file_exists("{$kiw_custom_path}/scripts/footer.php")){

                require_once "{$kiw_custom_path}/scripts/footer.php";

            } else {

                require_once dirname(__FILE__, 3) . "/footer.php";

            }


        }


    }


}