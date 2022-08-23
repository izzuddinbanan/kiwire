<?php

require_once dirname(__FILE__, 2) . "/includes/include_session.php";
require_once dirname(__FILE__, 2) . "/includes/include_registration.php";
require_once dirname(__FILE__, 2) . "/includes/include_general.php";
require_once dirname(__FILE__, 2) . "/includes/include_account.php";

require_once dirname(__FILE__, 3) . "/admin/includes/include_config.php";
require_once dirname(__FILE__, 3) . "/admin/includes/include_general.php";
require_once dirname(__FILE__, 3) . "/admin/includes/include_connection.php";


if ($_SESSION['system']['checked'] == "true"){


    $kiw_notification = $kiw_cache->get("NOTIFICATION_DATA:{$_SESSION['controller']['tenant_id']}");

    if (empty($kiw_notification)) {


        $kiw_notification = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_notification WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

        if (empty($kiw_notification)) $kiw_notification = array("dummy" => true);

        $kiw_cache->set("NOTIFICATION_DATA:{$_SESSION['controller']['tenant_id']}", $kiw_notification, 1800);


    }


    $kiw_username               = $kiw_db->escape($_REQUEST['username']);
    $kiw_email_address          = $kiw_db->escape($_REQUEST['email_address']);
    $kiw_password_new           = $kiw_db->escape($_REQUEST['new_password']);
    $kiw_password_verification  = $kiw_db->escape($_REQUEST['ver_password']);


    if (empty($kiw_username) || empty($kiw_password_new)){

        error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_no_credential']);

    } elseif ($kiw_password_new != $kiw_password_verification) {

        error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_password_verification_failed']);

    } elseif (empty($kiw_email_address)){

        error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_invalid_email_address']);

    } elseif (empty($kiw_password_verification)){

        error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_empty_password']);

    }


    $kiw_user = $kiw_db->query_first("SELECT tenant_id,username,password,email_address,status,ktype FROM kiwire_account_auth WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' AND username = '{$kiw_username}' LIMIT 1");


    if ($kiw_user['status'] !== "active"){

        error_redirect("/user/pages/?session={$session_id}", $kiw_notification['error_inactive_account']);

    }


    if ($kiw_user['ktype'] == "voucher"){

        error_redirect("/user/pages/?session={$session_id}", "You are not allowed to change password of this account");

    }


    if (!empty($kiw_user['email_address']) && $kiw_user['email_address'] == ($kiw_email_address)) {


        if (!empty($kiw_user['email_address']) && !empty($kiw_email_address)) {


            $kiw_password_new = sync_encrypt($kiw_password_new);

            $kiw_db->query("UPDATE kiwire_account_auth SET password = '{$kiw_password_new}' WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' AND username = '{$kiw_username}' LIMIT 1");

            $_SESSION['user']['current'] = next_page($_SESSION['user']['journey'], $_SESSION['user']['current'], $_SESSION['user']['default'], true);

            error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['notification_password_changed']);


        } else {

            error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_user_email_mismatched']);

        }


    } else {


        error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_invalid_email_address']);


    }


} else {

    error_redirect($_SERVER['HTTP_REFERER'], "You are not allowed to access this page");

}