<?php

require_once dirname(__FILE__, 2) . "/includes/include_redirect_from_login.php";

require_once dirname(__FILE__, 2) . "/includes/include_session.php";
require_once dirname(__FILE__, 2) . "/includes/include_general.php";

require_once dirname(__FILE__, 3) . "/libs/class.sql.helper.php";

require_once dirname(__FILE__, 3) . "/admin/includes/include_general.php";
require_once dirname(__FILE__, 3) . "/admin/includes/include_connection.php";


global $kiw_db;


$kiw_username     = $kiw_db->escape($_REQUEST['username']);

$kiw_topup_code   = $kiw_db->escape($_REQUEST['code']);


if (!empty($kiw_username) && !empty($kiw_topup_code)){


    $kiw_account = $kiw_db->query_first("SELECT * FROM kiwire_account_auth WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' AND username = '{$kiw_username}' LIMIT 1");


    if (!empty($kiw_account['username'])){


        $kiw_topup = $kiw_db->query_first("SELECT * FROM kiwire_topup_code WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' AND code = '{$kiw_topup_code}' LIMIT 1");


        if (!empty($kiw_topup['code'])){


            if ($kiw_topup['status'] == "n") {


                recharge_topup($kiw_db, $kiw_account, $kiw_topup, $kiw_username);


                if (recharge_topup($kiw_db, $kiw_account, $kiw_topup, $kiw_username) === true) {

                    error_redirect($_SERVER['HTTP_REFERER'], "You have successfully topup in the system.");

                } else error_redirect($_SERVER['HTTP_REFERER'], "Your profile has expired. Please re-activate to topup.");
                

            } else {

                error_redirect($_SERVER['HTTP_REFERER'], "Topup code already been used.");

            }


        } else {

            error_redirect($_SERVER['HTTP_REFERER'], "Invalid topup code has been provided.");

        }


    } else {

        error_redirect($_SERVER['HTTP_REFERER'], "Invalid account has been provided.");

    }



} else {

    error_redirect($_SERVER['HTTP_REFERER'], "Please provide all information required.");

}


