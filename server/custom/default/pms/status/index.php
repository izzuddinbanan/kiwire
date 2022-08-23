<?php


require_once dirname(__FILE__, 5) . "/user/includes/include_redirect_from_login.php";
// require_once dirname(__FILE__, 5) . "/user/includes/include_session.php";
require_once dirname(__FILE__, 5) . "/user/includes/include_account.php";
require_once dirname(__FILE__, 5) . "/user/includes/include_error.php";
require_once dirname(__FILE__, 5) . "/user/includes/include_general.php";

require_once dirname(__FILE__, 5) . "/admin/includes/include_config.php";
require_once dirname(__FILE__, 5) . "/admin/includes/include_general.php";
require_once dirname(__FILE__, 5) . "/admin/includes/include_connection.php";
require_once dirname(__FILE__, 5) . "/libs/class.sql.helper.php";

$session_id = "123123123121";

global $kiw_db, $session_id;

$_SESSION["mid_login"]["username"] = "A23";
$_SESSION["mid_login"]["password"] = "Mmp3UFNCUXFqREU2ejFjcExmdDJydz09";

$_SESSION['controller']['tenant_id'] = 'default';


$page_id       = "6ae7df9d";
$kiw_tenant    = $kiw_db->escape($_SESSION['controller']['tenant_id']);


// Check tenant is correct to run the function
if($kiw_tenant != end(explode("/", dirname(__FILE__, 3)))) error_redirect($_SERVER['HTTP_REFERER'], "You are not allowed to access this module");



$user_page = $kiw_db->query_first("SELECT SQL_CACHE content FROM kiwire_login_pages WHERE tenant_id = '{$kiw_tenant}' AND unique_id = '{$page_id}' LIMIT 1");

$user_page = $user_page['content'];
$user_page = urldecode(base64_decode($user_page));


$kiw_guest_record = $kiw_db->query_first("SELECT * FROM kiwire_account_auth WHERE username = '{$_SESSION["mid_login"]["username"]}' AND password = '{$_SESSION["mid_login"]["password"]}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

$kiw_profile = $kiw_db->query_first("SELECT * FROM kiwire_profiles WHERE name = '{$kiw_guest_record['profile_curr']}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");


$kiw_profile['attribute'] = json_decode($kiw_profile['attribute'], true);


if (!empty($kiw_guest_record)) {


    try {


        if ($kiw_guest_record['quota_in'] > 0 && $kiw_guest_record['quota_out'] > 0) {


            $kiw_total_byte = $kiw_guest_record['quota_in'] + $kiw_guest_record['quota_out'];

            $kiw_quota = round(($kiw_total_byte) / (1024 * 1024), 3, PHP_ROUND_HALF_DOWN);
        
        
        } else {

            $kiw_quota = 0;
        }
    
    
    } catch (Exception $e) {

        die("ERROR: " . $e->getMessage());
    
    }


    try {

        
        if ($kiw_profile['type'] == "free") {


            $kiw_remaining_time = "Unlimited";


        } elseif ($kiw_profile['type'] == "countdown") {


            $kiw_remaining_time = secondsToTime($kiw_profile['attribute']['control:Max-All-Session'] - $kiw_guest_record['session_time']);


        } elseif ($kiw_profile['type'] == "expiration") {


            if (empty($kiw_guest_record['date_activate']) || $kiw_guest_record['date_activate'] === '0000-00-00 00:00:00') {

               
                $kiw_remaining_time = secondsToTime($kiw_profile['attribute']['control:Access-Period']);


            } else {

                $kiw_remaining_time = secondsToTime($kiw_profile['attribute']['control:Access-Period'] - (time() - strtotime($kiw_guest_record['date_activate'])));

            }


        }
    
    
    
    } catch (Exception $e) {

        die("ERROR: " . $e->getMessage());
    
    }



    if ($kiw_guest_record["profile_subs"] == $kiw_guest_record["profile_curr"]) {

        $button_html = "<p style='color: rgb(46, 125, 50); font-size: 12px;'>Please click upgrade button to buy high speed internet plan.</p><input type='submit' class='button' name='custom_upgrade' value='Upgrade'>";
    
    
    } else {

        $button_html = "";
    
    }


} else {

    error_redirect($_SERVER['HTTP_REFERER'], "Missing account details");

}


// Function to convert seconds to days, hours, minutes, seconds
function secondsToTime($seconds)
{

    $dtF = new DateTime("@0");
    $dtT = new DateTime("@$seconds");

    return $dtF->diff($dtT)->format('%a days : %h hours : %i minutes : %s seconds');

}



$user_page = str_replace(array("{{username}}", "{{password}}", "{{session_id}}", "{{custom_button}}", "{{fullname}}", "{{quota}}", "{{time_left}}"), array($_SESSION["mid_login"]["username"], $_SESSION["mid_login"]["password"], $session_id, $button_html, $kiw_guest_record['fullname'], $kiw_quota, $kiw_remaining_time), $user_page);



// require_once "assets/header.php";
require_once dirname(__FILE__, 5) . "/user/header.php";

echo html_entity_decode($user_page);

require_once dirname(__FILE__, 5) . "/user/footer.php";
