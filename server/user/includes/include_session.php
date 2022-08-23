<?php

require_once dirname(__FILE__) . "/include_error.php";


// get time for better log data

$kiw_time = date("YmdH");

if (isset($_REQUEST['session']) && !empty($_REQUEST['session'])){


    $session_id = preg_replace('/[^A-Za-z0-9\-,]/', '', $_REQUEST['session']);

    session_id($session_id);


} else {

    print_error_message(102, "Invalid Session ID", "Please click on the retry button.");

}


// start session

if (session_status() == PHP_SESSION_NONE) session_start();


// check if valid tenant user, if not them produce error

if ($_SERVER['SCRIPT_NAME'] != "/user/init/index.php") {

    if (empty($_SESSION['controller']['tenant_id'])) {

        print_error_message(102, "Invalid Session ID", "Please click on the retry button.");

    }

}

