<?php

require_once dirname(__FILE__, 4) . "/user/includes/include_session.php";
require_once dirname(__FILE__, 4) . "/user/includes/include_general.php";

require_once dirname(__FILE__, 4) . "/admin/includes/include_config.php";
require_once dirname(__FILE__, 4) . "/admin/includes/include_general.php";
require_once dirname(__FILE__, 4) . "/admin/includes/include_connection.php";


$kiw_username           = $kiw_db->escape($_REQUEST['username']);
// $kiw_phone_no           = $kiw_db->escape($_REQUEST['phone_number']);

$kiw_user = $kiw_db->query_first("SELECT username,password,fullname FROM kiwire_account_auth WHERE username = '{$kiw_username}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

$kiw_notification = $kiw_db->query_first("SELECT * FROM kiwire_notification WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");


if (is_array($kiw_user) && !empty($kiw_user)){


    $kiw_user_details = $kiw_db->query_first("SELECT username FROM kiwire_account_auth WHERE username = '{$kiw_username}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

    $old_password = sync_decrypt($kiw_user_details['password']);

    $kiw_temp_password = '12345678';

    $kiw_enc_password = sync_encrypt($kiw_temp_password);

    $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), password = '{$kiw_enc_password}', date_password = NULL, login = 0 WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' AND username = '{$kiw_username}' LIMIT 1");

    logger($_SESSION['user']['mac'], "{$kiw_username} - Forgot password, reset to 12345678", $_SESSION['controller']['tenant_id']);

    error_redirect($_SERVER['HTTP_REFERER'], "Your password has been reset. Please re-login to change your password");

} else {

    error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_user_not_found']);

}







