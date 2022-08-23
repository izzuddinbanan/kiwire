<?php

require_once dirname(__FILE__, 4) . "/user/includes/include_redirect_from_login.php";
require_once dirname(__FILE__, 4) . "/user/includes/include_session.php";
require_once dirname(__FILE__, 4) . "/user/includes/include_general.php";
require_once dirname(__FILE__, 4) . "/user/includes/include_account.php";
require_once dirname(__FILE__, 4) . "/user/includes/include_registration.php";

require_once dirname(__FILE__, 4) . "/admin/includes/include_connection.php";

require_once dirname(__FILE__, 4) . "/libs/class.sql.helper.php";



$kiw_signup['login'] = $_REQUEST['to-login'];

$kiw_login = (strlen($kiw_signup['login']) > 0) ? $kiw_db->escape($kiw_signup['login']) : "NA";


// if user continue signup (interpage form) 
if ($kiw_login == "yes") {

    login_user($_SESSION['signup']['username'], $_SESSION['signup']['password'], $session_id);


} else {

    error_redirect("/user/pages/?session={$session_id}", "There is an error. Please contact system administrator");


}