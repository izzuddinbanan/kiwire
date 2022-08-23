<?php


session_start();


// check if 2-factors still not confirmed

if (isset($_SESSION['2factors']) && $_SESSION['2factors'] == false){

    if (!empty($_SESSION['mfactkey'])) {

        header("Location: /admin/?mfactor=false");

        die();

    }

}


if (!isset($_SESSION['tenant_id']) || empty($_SESSION['tenant_id']) || !isset($_SESSION['system_admin']) || empty($_SESSION['system_admin'])){


    if (strpos($_SERVER['SCRIPT_NAME'], "admin/ajax") > 0){

        echo json_encode(array("status" => "error", "message" => "ERROR: Your session already expired. Please refresh your browser."));

    } else {

        header("Location: /admin/index.php?error=" . base64_encode("Please login to continue.") . "&page=" . base64_encode($_SERVER['SCRIPT_NAME'] . "?" . http_build_query($_GET)));

    }

    exit();


} elseif (time() - $_SESSION['last_active'] > 900){


    if (strpos($_SERVER['SCRIPT_NAME'], "admin/ajax") > 0){


        echo json_encode(array("status" => "error", "message" => "ERROR: Your session already expired. Please refresh your browser."));


    } else {


        header("Location: /admin/index.php?error=" . base64_encode("Your session already expired.") . "&page=" . base64_encode($_SERVER['SCRIPT_NAME']));


    }


    session_destroy();

    exit();


} elseif (!empty($_GET['change_tenant'])){


    $_SESSION['tenant_id'] = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $_REQUEST['change_tenant']);

    header("Location: {$_SERVER['SCRIPT_NAME']}");

    exit();


}


$_SESSION['last_active'] = time();

$tenant_id = $_SESSION['tenant_id'];



