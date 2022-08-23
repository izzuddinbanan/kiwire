<?php

require_once dirname(__FILE__, 2) . "/includes/include_session.php";
require_once dirname(__FILE__, 2) . "/includes/include_error.php";
require_once dirname(__FILE__, 2) . "/includes/include_general.php";
require_once dirname(__FILE__, 2) . "/includes/include_auto_login.php";

require_once dirname(__FILE__, 3) . "/admin/includes/include_general.php";
require_once dirname(__FILE__, 3) . "/admin/includes/include_connection.php";


// check for nas is or tenant id

if (empty($_SESSION['controller']['id'])) {

    print_error_message(100, "Invalid / Unknown Controller ID", "Please ask your network administrator to check.");

} else {


    $kiw_id_hash = md5($_SESSION['controller']['id']);

    $kiw_temp = $kiw_cache->hGetAll("NAS_TENANT:{$kiw_id_hash}");

    if (empty($kiw_temp)) {

        $kiw_temp = $kiw_db->query_first("SELECT SQL_CACHE tenant_id, is_virtual FROM kiwire_controller WHERE device_type = 'controller' AND unique_id = '{$_SESSION['controller']['id']}' LIMIT 1");

        if (empty($kiw_temp)) $kiw_temp = "XXinvalidXX";
            

        $kiw_cache->hMSet("NAS_TENANT:{$kiw_id_hash}", $kiw_temp, 1800);

    }

    $kiw_virtual = 0;
    if($kiw_temp != "XXinvalidXX") {

        if(is_array($kiw_temp)) {

            $kiw_virtual = $kiw_temp["is_virtual"] ?? 0;
            $kiw_temp    = $kiw_temp["tenant_id"];

        }

    }
    ###########################################
    // Overwrite nasid when is using virtual nas
    // But please make sure ssid is bring in $_SESSION['controller']['ssid'] from server/login/index.php
    if($kiw_virtual == 1) {

        $kiw_virtual = true;
        $kiw_temp = $kiw_db->query_first("SELECT SQL_CACHE tenant_id  FROM kiwire_controller WHERE device_type = 'controller' AND unique_id = '{$_SESSION['controller']['ssid']}' LIMIT 1");

        if (empty($kiw_temp)) $kiw_temp = "XXinvalidXX";
        else $kiw_temp = $kiw_temp["tenant_id"];

    }
    // END VIRTUAL NAS FUNCTION 
    ###########################################

    if ($kiw_temp == "XXinvalidXX") print_error_message(101, "NAS Not Configured. nasid received [ {$_SESSION['controller']['id']} ]", "Please ask your network administrator to check.");
    else $_SESSION['controller']['tenant_id'] = $kiw_temp;

    unset($kiw_temp);


}


logger($_SESSION['user']['mac'], "Detected Tenant: {$_SESSION['controller']['tenant_id']}  |  Detected nasid: {$_SESSION['controller']['id']}");
if($kiw_virtual) {

    logger($_SESSION['user']['mac'], "Detected Virtual Nas using nasid : {$_SESSION['controller']['ssid']}");
}


if ($kiw_cache->get("OVERALL_SYSTEM_UNDER_MAINTENANCE") == true) {

    header("Location: /user/templates/maintenance.html");

    die();

} elseif($kiw_cache->get("SYSTEM_UNDER_MAINTENANCE:{$_SESSION['controller']['tenant_id']}") == true){

    header("Location: /user/templates/maintenance.html");

    die();

}

logger($_SESSION['user']['mac'], "Check Initiated: {$_SESSION['user']['ip']}");

// get cloud information to proceed

$kiw_cloud_config = $kiw_cache->get("CLOUD_DATA:{$_SESSION['controller']['tenant_id']}");

if (empty($kiw_cloud_config)) {


    $kiw_cloud_config = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_clouds WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

    $kiw_cache->set("CLOUD_DATA:{$_SESSION['controller']['tenant_id']}", $kiw_cloud_config, 1800);


}


// set the timezone

$_SESSION['system']['timezone'] = $kiw_cloud_config['timezone'];

if (empty($_SESSION['system']['timezone'])) $_SESSION['system']['timezone'] = "Asia/Kuala_Lumpur";


// check for licensing, if invalid or expired then moved to system under maintenance

$kiw_license = $kiw_cache->get("LICENSE_CHECKED:{$_SESSION['controller']['tenant_id']}");

if (!in_array($kiw_license, array("yes", "invalid"))) {


    // check for cloud license

    $kiw_license = @file_get_contents(dirname(__FILE__, 3) . "/custom/{$_SESSION['controller']['tenant_id']}/tenant.license");
    $kiw_license = sync_license_decode($kiw_license);

    $kiw_temp_install = @file_get_contents(dirname(__FILE__, 3) . "/custom/{$_SESSION['controller']['tenant_id']}/tenant.data");
    $kiw_temp_install = sync_brand_decrypt($kiw_temp_install);


    // if not available, then proceed to multi-tenant and do checking

    if (empty($kiw_license)){


        $kiw_license = @file_get_contents(dirname(__FILE__, 3) . "/custom/cloud.license");
        $kiw_license = sync_license_decode($kiw_license);


        if (empty($kiw_license)){


            $kiw_lic_controller = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_controller");


            if ((time() - $kiw_temp_install) > ((int)sync_brand_decrypt(SYNC_MAX_TRIAL_DAYS) * 86400)){


                $kiw_cache->set("LICENSE_CHECKED:{$_SESSION['controller']['tenant_id']}", "invalid", 86400);

                print_error_message(111, "Trial License Expired", "Your trial license has expired, please contact our Sales Representative.");


            } elseif ($kiw_lic_controller['kcount'] > ((int)sync_brand_decrypt(SYNC_MAX_TRIAL_DEVICES))){


                $kiw_cache->set("LICENSE_CHECKED:{$_SESSION['controller']['tenant_id']}", "invalid", 86400);

                print_error_message(111, "Trial License Expired", "Your trial license has expired, please contact our Sales Representative.");


            }


        } else {


            $kiw_lic_controller = $kiw_cache->get("TENANT_MASTER_LICENSE");

            if (empty($kiw_lic_controller)){


                $kiw_clouds = $kiw_db->fetch_array("SELECT tenant_id FROM kiwire_clouds");


                foreach ($kiw_clouds as $kiw_cloud){


                    if (file_exists(dirname(__FILE__, 3) . "/custom/{$kiw_cloud['tenant_id']}/tenant.license") == true){


                        $kiw_test = file_get_contents(dirname(__FILE__, 3) . "/custom/{$kiw_cloud['tenant_id']}/tenant.license");

                        $kiw_test = sync_license_decode($kiw_test);


                        if (empty($kiw_test)){

                            $kiw_lic_controller[] = $kiw_cloud['tenant_id'];

                        }


                    }


                }


                $kiw_cache->set("TENANT_MASTER_LICENSE", $kiw_lic_controller, 86400);


            }


            $kiw_lic_controller = implode("','", $kiw_lic_controller);

            $kiw_lic_controller = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_controller WHERE tenant_id IN ('{$kiw_lic_controller}')");

            unset($kiw_lic_controller);


            if ($kiw_lic_controller['kcount'] > $kiw_license['device_limit']){


                $kiw_cache->set("LICENSE_CHECKED:{$_SESSION['controller']['tenant_id']}", "invalid", 86400);

                print_error_message(111, "Trial License Expired", "Your trial license has expired, please contact our Sales Representative.");


            }


        }


    } else {


        // get the total number of nas

        $kiw_lic_controller = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_controller WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}'");


        // check if one of the variable changed, then license become void

        if ($kiw_lic_controller['kcount'] > $kiw_license['device_limit']){


            print_error_message(112, "Reached Maximum Controller Limit", "License error. Please contact our support for more detail.");


        } elseif ($kiw_cloud_config['name'] != $kiw_license['client_name']){


            print_error_message(113, "Client Name Mismatched", "License error. Please contact our support for more detail.");


        }



    }


    // if all good, means got license, update the cache

    $kiw_cache->set("LICENSE_CHECKED:{$_SESSION['controller']['tenant_id']}", "yes", 86400);


} elseif ($kiw_license == "invalid") {

    print_error_message(110, "Trial License Expired", "Your trial license has expired, please contact our Sales Representative");

}



// keep this server access domain for future reference

$_SESSION['system']['domain'] = ($_SERVER['HTTPS'] == 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . (in_array($_SERVER['SERVER_PORT'], array("80", "443")) ? "" : (":" . $_SERVER['SERVER_PORT'])) ;


// saved ip with the tenant id for error hinting

$kiw_temp = $_SERVER['REMOTE_ADDR'];

$kiw_cache->set("IP_HINT_{$kiw_temp}", $_SESSION['controller']['tenant_id'], 600);

logger($_SESSION['user']['mac'], "Request received from {$kiw_temp}");


unset($kiw_temp);


// check if controller produced error

if (!empty($_SESSION['response']['error'])) {


    logger($_SESSION['user']['mac'], "Redirect to Pages due to Error Response : {$_SESSION['response']['error']}");

    ?>

    <script>

        window.onload = function () {

            window.location.href = "/user/pages/?session=<?= $session_id ?>";

        }

    </script>

    <?

    die();


}


// check for device information

require_once dirname(__FILE__, 3) . "/libs/spyc.php";
require_once dirname(__FILE__, 3) . "/libs/devicedetector/autoload.php";

$kiw_temp = new DeviceDetector\DeviceDetector($_SERVER['HTTP_USER_AGENT']);
$kiw_temp->parse();

$_SESSION['user']['system']     = ucfirst(preg_replace('/[^A-Za-z0-9\- ]/', '', $kiw_temp->getOs()['name']));
$_SESSION['user']['model']      = preg_replace('/[^A-Za-z0-9\- ]/', '', $kiw_temp->getModel());
$_SESSION['user']['brand']      = preg_replace('/[^A-Za-z0-9\- ]/', '', $kiw_temp->getBrandName());
$_SESSION['user']['class']      = ucfirst(preg_replace('/[^A-Za-z0-9\- ]/', '', $kiw_temp->getDeviceName()));

unset($kiw_temp);

logger($_SESSION['user']['mac'], "Device Detected as {$_SESSION['user']['class']} {$_SESSION['user']['brand']} {$_SESSION['user']['model']} {$_SESSION['user']['os']}");

// device language

$_SESSION['user']['lang'] = $_REQUEST['lang'];


// check for zone information

$kiw_temp['ip']     = strtolower($_SESSION['user']['ip']);
$kiw_temp['ipv6']   = strtolower($_SESSION['user']['ipv6']);
$kiw_temp['vlan']   = strtolower($_SESSION['controller']['vlan']);
$kiw_temp['id']     = strtolower($_SESSION['controller']['id']);
$kiw_temp['ssid']   = strtolower($_SESSION['controller']['ssid']);
$kiw_temp['zone']   = strtolower($_SESSION['controller']['zone']);


require_once dirname(__FILE__, 3) . "/libs/class.ip_range.php";

$kiw_zone_attr = $kiw_cache->get("ZONE_DATA_ATTR:{$_SESSION['controller']['tenant_id']}");

if (empty($kiw_zone_attr)) {

    $kiw_zone_attr = $kiw_db->fetch_array("SELECT SQL_CACHE * FROM kiwire_zone_child WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' AND master_id IN (SELECT DISTINCT(name) FROM kiwire_zone WHERE kiwire_zone.tenant_id = '{$_SESSION['controller']['tenant_id']}' AND status = 'y') ORDER BY priority DESC");

    $kiw_cache->set("ZONE_DATA_ATTR:{$_SESSION['controller']['tenant_id']}", $kiw_zone_attr, 1800);

}

foreach ($kiw_zone_attr as $attr) {

    if (empty($attr['nasid']) || strtolower($attr['nasid']) == strtolower($kiw_temp['id'])) {

        if (empty($attr['vlan']) || strtolower($attr['vlan']) == strtolower($kiw_temp['vlan'])) {

            if (empty($attr['ssid']) || strtolower($attr['ssid']) == strtolower($kiw_temp['ssid'])) {

                if (empty($attr['ipaddr']) || ipv4_in_range($kiw_temp['ip'], $attr['ipaddr'])) {

                    if (empty($attr['ipv6addr']) || ipv6_in_range($kiw_temp['ipv6'], $attr['ipv6addr'])) {

                        if (empty($attr['dzone']) || strtolower($attr['dzone']) == strtolower($kiw_temp['zone'])) {


                            $_SESSION['user']['zone'] = $attr['master_id'];

                            logger($_SESSION['user']['mac'], "Assigned to Zone: {$attr['master_id']}");

                            break;


                        }

                    }

                }

            }

        }

    }

}


unset($kiw_zone_attr);
unset($kiw_temp);

if (empty($_SESSION['user']['zone'])) $_SESSION['user']['zone'] = "nozone";


// check for blocked device

$kiw_blocked = $kiw_cache->get("BLACK_LISTED:{$_SESSION['controller']['tenant_id']}");

if (empty($kiw_blocked)) {

    $kiw_temps = $kiw_db->fetch_array("SELECT * FROM kiwire_firewall WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 10000");

    if (empty($kiw_temps[0]['type'])){

        $kiw_blocked = array("dummy" => true);

    } else {

        foreach ($kiw_temps as $kiw_temp) {

            $kiw_blocked[$kiw_temp['type']][] = $kiw_temp['dest'];

        }

        unset($kiw_temp);

    }


    unset($kiw_temps);

    $kiw_cache->set("BLACK_LISTED:{$_SESSION['controller']['tenant_id']}", $kiw_blocked, 1800);


}


if ($kiw_blocked['dummy'] != true) {


    $kiw_notification = $kiw_cache->get("NOTIFICATION_DATA:{$_SESSION['controller']['tenant_id']}");

    if (empty($kiw_notification)) {


        $kiw_notification = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_notification WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

        if (empty($kiw_notification)) $kiw_notification = array("dummy" => true);

        $kiw_cache->set("NOTIFICATION_DATA:{$_SESSION['controller']['tenant_id']}", $kiw_notification, 1800);


    }

    if (is_array($kiw_blocked['fwmac'])){

        if (in_array($_SESSION['user']['mac'], $kiw_blocked['fwmac'])){


            // display custom error message

            logger($_SESSION['user']['mac'], "Blocked due to Blacklisted");

            print_error_message(119, "Device Blocked", $kiw_notification['error_device_blacklisted']);


        }

    }


    if (is_array($kiw_blocked['fwip'])) {

        if (in_array($_SESSION['user']['ip'], $kiw_blocked['fwip'])) {


            // display custom error message

            logger($_SESSION['user']['mac'], "Blocked due to Blacklisted");

            print_error_message(119, "Device Blocked", $kiw_notification['error_device_blacklisted']);


        }

    }


}


$kiw_zone = $kiw_cache->get("ZONE_DATA:{$_SESSION['controller']['tenant_id']}:{$_SESSION['user']['zone']}");

if (empty($kiw_zone)){


    $kiw_zone = $kiw_db->query_first("SELECT * FROM kiwire_zone WHERE status = 'y' AND name = '{$_SESSION['user']['zone']}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

    if (empty($kiw_zone['journey'])) $kiw_zone['journey'] = "[none]";

    $kiw_cache->set("ZONE_DATA:{$_SESSION['controller']['tenant_id']}:{$_SESSION['user']['zone']}", $kiw_zone, 1800);


}


// log this session zone data

logger($_SESSION['user']['mac'], "Journey: {$kiw_zone['journey']}");


// set this session to the speicific zone

$_SESSION['user']['journey_name'] = $kiw_zone['journey'];


if (!empty($kiw_zone['force_profile']) && $kiw_zone['force_profile'] != "none"){


    $_SESSION['controller']['force_profile'] = $kiw_zone['force_profile'];


}


if (!empty($kiw_zone['force_allowed_zone']) && $kiw_zone['force_allowed_zone'] != "none"){


    $_SESSION['controller']['force_allowed_zone'] = $kiw_zone['force_allowed_zone'];


}



// check for journey

if (!empty($_SESSION['user']['journey_name']) && $_SESSION['user']['journey_name'] != "[none]") {


    $kiw_temp = $kiw_cache->get("JOURNEY_DATA:{$_SESSION['user']['journey_name']}:{$_SESSION['controller']['tenant_id']}");


    if (empty($kiw_temp)) {


        $kiw_temp = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_login_journey WHERE journey_name = '{$_SESSION['user']['journey_name']}' AND status = 'y' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

        if (empty($kiw_temp)) $kiw_temp = array("dummy" => true);

        $kiw_cache->set("JOURNEY_DATA:{$_SESSION['user']['journey_name']}:{$_SESSION['controller']['tenant_id']}", $kiw_temp, 1800);


    }


}


// set the journey based on zone

$_SESSION['user']['journey'] = array_filter(explode(",", $kiw_temp['page_list']));



// set pre login journey

if ($kiw_temp['pre_login'] == "custom"){


    $next_page = urldecode($kiw_temp['pre_login_url']);

    if (strlen($next_page) > 0) {

        $next_page = str_replace('{{session_id}}', $session_id, $next_page);
        $next_page = str_replace('{{mac_id}}', $_SESSION['user']['mac'], $next_page);
        $next_page = str_replace('{{tenant_id}}', $_SESSION['controller']['tenant_id'], $next_page);

    } else $next_page = "/user/pages/?session={$session_id}";


} else {

    $next_page = "/user/pages/?session={$session_id}";

}



if ($kiw_temp['post_login'] !== "default"){


    if ($kiw_temp['post_login'] == "custom"){


        if (!empty($kiw_temp['post_login_url'])) {

            $_SESSION['user']['destination'] = urldecode($kiw_temp['post_login_url']);
            $_SESSION['user']['destination'] = str_replace('{{session_id}}', $session_id, $_SESSION['user']['destination']);
            

            $next_page = str_replace('{{session_id}}', $session_id, $next_page);
            

        }


    } elseif ($kiw_temp['post_login'] == "campaign"){


        $kiw_campaign_page = end($_SESSION['user']['journey']);


        if (!empty($kiw_campaign_page)) {


            $kiw_campaign_check = $kiw_cache->get("CAMPAIGN_PAGE_POST:{$_SESSION['controller']['tenant_id']}:{$kiw_campaign_page}");

            if (empty($kiw_campaign_check)){


                $kiw_campaign_check = $kiw_db->query_first("SELECT purpose FROM kiwire_login_pages WHERE unique_id = '{$kiw_campaign_page}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

                $kiw_campaign_check = $kiw_campaign_check['purpose'] == "campaign" ? "y" : "n";

                $kiw_cache->set("CAMPAIGN_PAGE_POST:{$_SESSION['controller']['tenant_id']}:{$kiw_campaign_check}", $kiw_campaign_check, 1800);


            }


            if ($kiw_campaign_check == "y") {


                $_SESSION['system']['post_campaign'] = "y";

                $_SESSION['system']['post_page'] = $kiw_campaign_page;

                $_SESSION['user']['destination'] = "{$_SESSION['system']['domain']}/user/pages/?session={$_GET['session']}";


            }


        }


    }


}

logger($_SESSION['user']['mac'], "Post-Login to " . (empty($_SESSION['user']['destination']) ? "[ None ]" : $_SESSION['user']['destination']));



unset($kiw_temp);


// get the default page for future used

$kiw_temp = $kiw_cache->get("DEFAULT_PAGE:{$_SESSION['controller']['tenant_id']}");

if (empty($kiw_temp)) {

    $kiw_temp = $kiw_db->query_first("SELECT SQL_CACHE unique_id FROM kiwire_login_pages WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' AND default_page = 'y' LIMIT 1")['unique_id'];

    $kiw_cache->set("DEFAULT_PAGE:{$_SESSION['controller']['tenant_id']}", $kiw_temp, 1800);

}


$_SESSION['user']['default'] = $kiw_temp;

unset($kiw_temp);


// if no journey then use default page instead

if (empty($_SESSION['user']['journey'][0])) {

    $_SESSION['user']['current'] = $_SESSION['user']['default'];

} else {

    $_SESSION['user']['current'] = $_SESSION['user']['journey'][0];

}


// if there is error from ap, then redirect to pages

if (!empty($_SESSION['response']['error'])){


    header("Location: /user/pages/?session={$_GET['session']}");

    die();


}



// check for auto login if available

$kiw_temp = explode(",", $kiw_cloud_config['check_arrangement_auto']);


if (is_array($kiw_temp) && count($kiw_temp) > 0) {


    require_once dirname(__FILE__, 2) . "/includes/include_auto_login.php";


    foreach ($kiw_temp as $auto_login) {

        if (function_exists($auto_login)) {

            $auto_login($kiw_db, $kiw_cache, $kiw_zone, $session_id);

        }

    }


}

unset($kiw_temp);


$kiw_custom = dirname(__FILE__, 3) . "/custom/{$_SESSION['controller']['tenant_id']}/user/init.php";

if (file_exists($kiw_custom) == true){

    include_once $kiw_custom;

}


logger($_SESSION['user']['mac'], "Redirect to {$next_page}");

header("Location: {$next_page}");




