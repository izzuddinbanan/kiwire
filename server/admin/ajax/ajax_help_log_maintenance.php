<?php

$kiw['module'] = "Help -> System Logs";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

// header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_general.php";
require_once "../includes/include_connection.php";
require_once "../includes/include_report.php";


if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}


if (!in_array($_SESSION['permission'], array("r", "rw"))) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}


$kiw_action = $_REQUEST['action'];



if ($kiw_action == "view-latest-system") {


    $kiw_result_data = `tail -n 100 $(ls -Art /var/www/kiwire/logs/{$_SESSION['tenant_id']}/kiwire-system-* | tail -n 1)`;

    echo empty($kiw_result_data) ? "No log data available." : str_replace("\n", "<br>", $kiw_result_data);



} elseif ($kiw_action == "view-latest-integration") {


    $kiw_result_data = `tail -n 100 $(ls -Art /var/www/kiwire/logs/{$_SESSION['tenant_id']}/kiwire-integration-* | tail -n 1)`;

    echo empty($kiw_result_data) ? "No log data available." : str_replace("\n", "<br>", $kiw_result_data);


} elseif ($kiw_action == "view-latest-user") {



    $kiw_result_data = `tail -n 100 $(ls -Art /var/www/kiwire/logs/{$_SESSION['tenant_id']}/kiwire-user-* | tail -n 1)`;

    echo empty($kiw_result_data) ? "No log data available." : str_replace("\n", "<br>", $kiw_result_data);


} elseif ($kiw_action == "view-latest-service") {


    $kiw_result_data = `tail -n 100 $(ls -Art /var/www/kiwire/logs/{$_SESSION['tenant_id']}/kiwire-service-* | tail -n 1)`;

    echo empty($kiw_result_data) ? "No log data available." : str_replace("\n", "<br>", $kiw_result_data);


} elseif ($kiw_action == "view-latest-pms") {


    $kiw_db = Database::obtain();


    $kiw_pms = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_int_pms WHERE tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");


    if (!empty($kiw_pms)) {


        if (in_array($kiw_pms['pms_type'], array("micros", "infor"))){

            $kiw_pms['pms_type'] = "micros";

        }


        $kiw_result_data = `tail -n 100 $(ls -Art /var/www/kiwire/logs/pms/{$kiw_pms['pms_type']}/{$_SESSION['tenant_id']}/kiwire-pms-* | tail -n 1)`;

        echo empty($kiw_result_data) ? "No log data available." : str_replace("\n", "<br>", $kiw_result_data);


    } else echo "PMS setting is disabled";


} elseif ($kiw_action == "download-system") {


    header("Content-Type: application/json");


    $kiw_date = date("YmdHi");

    $kiw_filename = "system-log-{$_SESSION['tenant_id']}-{$kiw_date}.log";


    if (file_exists("{$kiw_filename}.zip") == false) {


        system("cat /var/www/kiwire/logs/{$_SESSION['tenant_id']}/kiwire-system-* > /var/www/kiwire/server/temp/{$kiw_filename} 2>/dev/null");

        system("zip -qJj /var/www/kiwire/server/temp/{$kiw_filename}.zip /var/www/kiwire/server/temp/{$kiw_filename}");

        system("rm -rf /var/www/kiwire/server/temp/{$kiw_filename}");


    }


    echo json_encode(array("status" => "success", "message" => "", "data" => "{$kiw_filename}.zip"));


} elseif ($kiw_action == "download-integration") {


    header("Content-Type: application/json");


    $kiw_date = date("YmdHi");

    $kiw_filename = "integration-log-{$_SESSION['tenant_id']}-{$kiw_date}.log";


    if (file_exists("{$kiw_filename}.zip") == false) {


        system("cat /var/www/kiwire/logs/{$_SESSION['tenant_id']}/kiwire-integration-* > /var/www/kiwire/server/temp/{$kiw_filename} 2>/dev/null");

        system("zip -qJj /var/www/kiwire/server/temp/{$kiw_filename}.zip /var/www/kiwire/server/temp/{$kiw_filename}");

        system("rm -rf /var/www/kiwire/server/temp/{$kiw_filename}");


    }


    echo json_encode(array("status" => "success", "message" => "", "data" => "{$kiw_filename}.zip"));


} elseif ($kiw_action == "download-user") {


    header("Content-Type: application/json");


    $kiw_date = date("YmdHi");

    $kiw_filename = "user-log-{$_SESSION['tenant_id']}-{$kiw_date}.log";


    if (file_exists("{$kiw_filename}.zip") == false) {


        system("cat /var/www/kiwire/logs/{$_SESSION['tenant_id']}/kiwire-user-* > /var/www/kiwire/server/temp/{$kiw_filename} 2>/dev/null");

        system("zip -qJj /var/www/kiwire/server/temp/{$kiw_filename}.zip /var/www/kiwire/server/temp/{$kiw_filename}");

        system("rm -rf /var/www/kiwire/server/temp/{$kiw_filename}");


    }


    echo json_encode(array("status" => "success", "message" => "", "data" => "{$kiw_filename}.zip"));


} elseif ($kiw_action == "download-service") {


    header("Content-Type: application/json");


    $kiw_date = date("YmdHi");

    $kiw_filename = "service-log-{$_SESSION['tenant_id']}-{$kiw_date}.log";


    if (file_exists("{$kiw_filename}.zip") == false) {


        system("cat /var/www/kiwire/logs/{$_SESSION['tenant_id']}/kiwire-service-* > /var/www/kiwire/server/temp/{$kiw_filename} 2>/dev/null");

        system("zip -qJj /var/www/kiwire/server/temp/{$kiw_filename}.zip /var/www/kiwire/server/temp/{$kiw_filename}");

        system("rm -rf /var/www/kiwire/server/temp/{$kiw_filename}");


    }


    echo json_encode(array("status" => "success", "message" => "", "data" => "{$kiw_filename}.zip"));



} elseif ($kiw_action == "download-pms") {


    header("Content-Type: application/json");


    $kiw_date = date("YmdHi");

    $kiw_filename = "pms-log-{$_SESSION['tenant_id']}-{$kiw_date}.log";


    if (file_exists("{$kiw_filename}.zip") == false) {


        $kiw_db = Database::obtain();

        $kiw_pms = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_int_pms WHERE tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");


        if (in_array($kiw_pms['pms_type'], array("micros", "infor"))){

            $kiw_pms['pms_type'] = "micros";

        }


        system("cat /var/www/kiwire/logs/pms/{$kiw_pms['pms_type']}/{$_SESSION['tenant_id']}/kiwire-pms-* > /var/www/kiwire/server/temp/{$kiw_filename} 2>/dev/null");

        system("zip -qJj /var/www/kiwire/server/temp/{$kiw_filename}.zip /var/www/kiwire/server/temp/{$kiw_filename}");

        system("rm -rf /var/www/kiwire/server/temp/{$kiw_filename}");


    }


    echo json_encode(array("status" => "success", "message" => "", "data" => "{$kiw_filename}.zip"));


} else {

    echo "Unable to collect log data.";

}




