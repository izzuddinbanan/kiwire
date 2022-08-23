<?php

require_once dirname(__FILE__, 2) . "/includes/include_session.php";
require_once dirname(__FILE__, 2) . "/includes/include_general.php";

require_once dirname(__FILE__, 3) . "/admin/includes/include_connection.php";
require_once dirname(__FILE__, 3) . "/admin/includes/include_general.php";

/********************add for captcha landing page testing **************************/

require_once dirname(__FILE__, 3) . "/config.php";
require_once dirname(__FILE__, 3) . "/libs/simplecaptcha/simple-php-captcha.php";

/************************    end for captcha testing     ****************************/



if (empty($_SESSION['user'])){

    print_error_message(103, "Missing Session ID", "Please click on the retry button.");

}


if (empty($_SESSION['user']['current'])) {


    if (!empty($_SESSION['user']['default'])) {

        $_SESSION['user']['current'] = $_SESSION['user']['default'];

    } else {

        print_error_message(108, "No Page Allocated. Please check your journey, zone or default page", "Please ask your network administrator to check.");

    }


}

/****************************************
* 
* START FOR CAPTCHA TESTING
* 
****************************************/ 

try {

    $_SESSION['user']['captcha'] = simple_php_captcha();

    // echo json_encode(array("status" => "success", "message" => null, "data" => $_SESSION['user']['captcha']['image_src']));

} catch (Exception $e) {

    print_error_message(108, "Error", "Please ask your network administrator to check.");
    // echo json_encode(array("status" => "error", "message" => "ERROR: " . $e->getMessage(), "data" => null));

}


/****************************************
* 
* END FOR CAPTCHA TESTING 
* 
****************************************/ 




// get the page details

$kiw_temp = $kiw_cache->get("PAGE_DATA:{$_SESSION['controller']['tenant_id']}:{$_SESSION['user']['current']}");

if (empty($kiw_temp)) {


    $kiw_temp = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_login_pages WHERE unique_id = '{$_SESSION['user']['current']}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

    if (empty($kiw_temp)) {
        // $kiw_temp = array("dummy" => true);
        // not found will return error directly
        print_error_message(104, "No Page Found", "Please ask your network administrator to check.");
    }
        

    $kiw_cache->set("PAGE_DATA:{$_SESSION['controller']['tenant_id']}:{$_SESSION['user']['current']}", $kiw_temp, 1800);


}



if ($kiw_temp['dummy'] != true) {


    // set session to allow login

    $_SESSION['system']['checked'] = true;


    $kiw_config = $kiw_cache->get("CLOUD_DATA:{$_SESSION['controller']['tenant_id']}");

    if (empty($kiw_config)){

        $kiw_config = $kiw_db->query_first("SELECT * FROM kiwire_clouds WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

        if (empty($kiw_config)) $kiw_config = array("dummy" => true);

        $kiw_cache->set("CLOUD_DATA:{$_SESSION['controller']['tenant_id']}", $kiw_config, 1800);

    }


    // count display impression

    if ($kiw_temp['count_impress'] != "n") {

        $kiw_cache->incr("REPORT_IMPRESSION_PAGE:{$kiw_time}:{$_SESSION['controller']['tenant_id']}:{$kiw_temp['unique_id']}");
        $kiw_cache->incr("REPORT_IMPRESSION_ZONE:{$kiw_time}:{$_SESSION['controller']['tenant_id']}:{$_SESSION['user']['zone']}");

    }


    if (strpos($kiw_temp['content'], "{{customize=") > 0){


        $kiw_custom_url_start = strpos($kiw_temp['content'], "{{customize=") + 12;

        $kiw_custom_url_stop = substr($kiw_temp['content'], $kiw_custom_url_start, strpos($kiw_temp['content'], "}", $kiw_custom_url_start));


        header("Location: {$kiw_custom_url_stop}");

        die();


    }



    // get the mobile apps

    $kiw_banner = $kiw_cache->get("MOBILE_APP:{$_SESSION['controller']['tenant_id']}");

    if (empty($kiw_banner)){

        $kiw_banner = $kiw_db->query_first("SELECT * FROM kiwire_campaign_apps WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

        if (empty($kiw_banner)) $kiw_banner = array("dummy" => true);

        $kiw_cache->set("MOBILE_APP:{$_SESSION['controller']['tenant_id']}", $kiw_banner, 1800);

    }


    // set path for custom items

    $kiw_custom_path = dirname(__FILE__, 3) . "/custom/{$_SESSION['controller']['tenant_id']}";


    // use custom header if available

    if (file_exists( "{$kiw_custom_path}/scripts/header.php")) {

        require_once "{$kiw_custom_path}/scripts/header.php";

    } else {

        require_once dirname(__FILE__, 2) . "/header.php";

    }


    // get the page details

    $kiw_page_html = urldecode(base64_decode($kiw_temp['content']));


    if ($kiw_temp['purpose'] == "landingwinfo"){


        // get data from session if available, if not then query for mariadb

        $kiw_previous = $_SESSION['user']['last_account'];


        if (empty($kiw_previous) || (time() - $kiw_previous['time']) > 600) {


            $kiw_previous = $kiw_db->query_first("SELECT last_account FROM kiwire_device_history WHERE mac_address = '{$_SESSION['user']['mac']}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");


            if (!empty($kiw_previous)) {


                $kiw_previous = $kiw_db->query_first("SELECT username,password,fullname FROM kiwire_account_auth WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' AND username = '{$kiw_previous['last_account']}' LIMIT 1");


                if (!empty($kiw_previous)) {


                    $kiw_previous['password'] = sync_decrypt($kiw_previous['password']);

                    $kiw_page_html = str_replace(["{{last_account}}", "{{last_credential}}", "{{fullname}}"], [$kiw_previous['username'], $kiw_previous['password'], $kiw_previous['fullname']], $kiw_page_html);


                } else {

                    $kiw_previous = ['dummy' => true];

                }


                $kiw_previous['time'] = time();

                $_SESSION['user']['last_account'] = $kiw_previous;


            } else {


                $kiw_page_html = str_replace(["{{last_account}}", "{{last_credential}}", "{{fullname}}"], ["", "", ""], $kiw_page_html);

                $_SESSION['user']['last_account'] = ['dummy' => true];


            }


            unset($kiw_previous);


        } else {


            if ($kiw_previous['dummy'] !== true){


                $kiw_page_html = str_replace(["{{last_account}}", "{{last_credential}}", "{{fullname}}"], [$kiw_previous['username'], $kiw_previous['password'], $kiw_previous['fullname']], $kiw_page_html);


            } else {


                $kiw_page_html = str_replace(["{{last_account}}", "{{last_credential}}", "{{fullname}}"], ["", "", ""], $kiw_page_html);


            }


        }


    }


    // replace static data

    $kiw_page_html = str_replace(array('{{mac_address}}', '{{random_number}}', '{{session_id}}', '{{image_src}}'), array($_SESSION['mac'], rand(0, 8), $session_id, $_SESSION['user']['captcha']['image_src']), $kiw_page_html);
    //  $kiw_page_html = str_replace(array('{{mac_address}}', '{{random_number}}', '{{session_id}}'), array($_SESSION['mac'], rand(0, 8), $session_id), $kiw_page_html);

    // replace external data

    $kiw_temp_data = json_decode(base64_decode($_SESSION['user']['page_data']), true);

    if (count($kiw_temp_data) > 0) {

        foreach ($kiw_temp_data as $kiw_key => $kiw_value) {

            $kiw_page_html = str_replace("{{{$kiw_key}}}", $kiw_value, $kiw_page_html);

        }

    }


    // clear the external data so only display once

    $_SESSION['user']['page_data'] = "";


    // log to show user reached this page

    logger($_SESSION['user']['mac'], "Display Page: {$kiw_temp['page_name']} [ {$_SESSION['user']['current']} ]");



    // display page

    echo html_entity_decode($kiw_page_html);



    // use custom footer if available

    if (file_exists("{$kiw_custom_path}/scripts/footer.php")){

        require_once "{$kiw_custom_path}/scripts/footer.php";

    } else {

        require_once dirname(__FILE__, 2) . "/footer.php";

    }


} else {

    print_error_message(104, "No Page Found", "Please ask your network administrator to check.");

}








