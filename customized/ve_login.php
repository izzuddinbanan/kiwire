<?php


require_once dirname(__FILE__, 3) . "/user/includes/include_session.php";
require_once dirname(__FILE__, 3) . "/user/includes/include_account.php";
require_once dirname(__FILE__, 3) . "/user/includes/include_general.php";

require_once dirname(__FILE__, 3) . "/admin/includes/include_connection.php";
require_once dirname(__FILE__, 3) . "/admin/includes/include_general.php";


$kiw_username = $kiw_db->escape($_REQUEST['username']);
$kiw_password = $kiw_db->escape($_REQUEST['password']);


if (!empty($kiw_username) && !empty($kiw_password)){


    $kiw_user = $kiw_db->query_first("SELECT username, fullname, password FROM kiwire_account_auth WHERE username = '{$kiw_username}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");


    if (!empty($kiw_user)){


        $kiw_temps = explode("|", $kiw_user['fullname']);


        foreach ($kiw_temps as $kiw_temp){


            $kiw_current_pass = explode(" ", $kiw_temp);

            $kiw_current_pass = substr(end($kiw_temp),0,3);


            if (strtolower($kiw_password) == strtolower($kiw_current_pass)){

                login_user($kiw_username, $kiw_temp, $_GET['session']);

            }


        }


        error_redirect($_SERVER['HTTP_REFERER'], "You have provided wrong credential");


    } else {

        error_redirect($_SERVER['HTTP_REFERER'], "Please provide a valid credential to login");

    }


} else {

    error_redirect($_SERVER['HTTP_REFERER'], "Please provide a valid credential to login");

}