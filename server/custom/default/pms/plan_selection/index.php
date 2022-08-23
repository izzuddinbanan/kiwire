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


$kiw_username      = $_SESSION["mid_login"]["username"];
$kiw_password      = $_SESSION["mid_login"]["password"];

$kiw_password_decr = sync_decrypt($kiw_password);


$kiw_next_login = $kiw_db->query_first("SELECT * FROM kiwire_account_auth WHERE username = '{$kiw_username}' AND password = '{$kiw_password}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");


if (($kiw_next_login['profile_subs'] == $kiw_next_login['profile_curr'])) {


    if (($kiw_next_login['quota_in'] == 0 ||  $kiw_next_login['quota_out'] == 0) || ($kiw_next_login['quota_in'] == NULL ||  $kiw_next_login['quota_out']  == NULL)) {



        $page_id       = "0fdd0609";
        $kiw_tenant    = $kiw_db->escape($_SESSION['controller']['tenant_id']);
    
    
        // Check tenant is correct to run the function
        if ($kiw_tenant != end(explode("/", dirname(__FILE__, 3)))) error_redirect($_SERVER['HTTP_REFERER'], "You are not allowed to access this module");
    
    
        $user_page = $kiw_db->query_first("SELECT SQL_CACHE content FROM kiwire_login_pages WHERE tenant_id = '{$kiw_tenant}' AND unique_id = '{$page_id}' LIMIT 1");
    
        $user_page = $user_page['content'];
        $user_page = urldecode(base64_decode($user_page));
    
    
    
        $user_page = str_replace(array("{{username}}", "{{password}}", "{{session_id}}"), array($kiw_username, $kiw_password_decr, $session_id), $user_page);
    
     
    
        // require_once "assets/header.php";
        require_once dirname(__FILE__, 5) . "/user/header.php";
    
        echo html_entity_decode($user_page);
    
        require_once dirname(__FILE__, 5) . "/user/footer.php";

    
    
    } else {

        error_redirect($_SERVER['HTTP_REFERER'], $kiw_notification['error_user_not_found']);


    }


} else {

 
    login_user($kiw_username, $kiw_password, $session_id);


}
