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

$_SESSION["mid_login"]["username"] = "A23";
$_SESSION["mid_login"]["password"] = "Mmp3UFNCUXFqREU2ejFjcExmdDJydz09";

global $kiw_db, $session_id;


$_SESSION['controller']['tenant_id'] = 'default';


$kiw_free_plan    = $_REQUEST['free_plan'];
$kiw_upgrade_plan = $_REQUEST['upgrade_plan'];

$kiw_profile  = $_REQUEST['upgrade-plan'];

var_dump($kiw_profile);

$kiw_username   = $_SESSION["mid_login"]["username"];
$kiw_password   = $_SESSION["mid_login"]["password"];

$kiw_tenant     = $kiw_db->escape($_SESSION['controller']['tenant_id']);



// Check tenant is correct to run the function
if($kiw_tenant != end(explode("/", dirname(__FILE__, 3)))) error_redirect($_SERVER['HTTP_REFERER'], "You are not allowed to access this module");

// Check data post

if (empty($kiw_username) || empty($kiw_password)) error_redirect($_SERVER['HTTP_REFERER'], "Error credentials");

$kiw_password_decr = sync_decrypt($kiw_password);


$kiw_guest_record = $kiw_db->query_first("SELECT * FROM kiwire_account_auth WHERE username = '{$kiw_username}' AND password = '{$kiw_password}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");



if (!empty($kiw_guest_record)) {



    $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), profile_curr = '{$kiw_profile}' WHERE username = '{$kiw_username}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");



    if ($kiw_upgrade_plan == 2) {


        login_user($kiw_username, $kiw_password_decr, $session_id);


    } else {


        // status page after user upgrade from free plan
        header("Location: /custom/default/pms/status/?session={$_GET['session']}");
        
        die();

    }



} else {

    error_redirect($_SERVER['HTTP_REFERER'], "Missing account details");
    
}
