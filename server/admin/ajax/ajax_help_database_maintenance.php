<?php

$kiw['module'] = "Help -> Database Maintenance";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_connection.php";
require_once "../includes/include_report.php";
require_once "../includes/include_general.php";

if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}


if ($_REQUEST['action'] == "download") {


    $kiw_date = $kiw_db->escape($_REQUEST['data_date']);

    $kiw_path = dirname(__FILE__, 4) . "/backups/";

    $kiw_temp = dirname(__FILE__, 3) . "/temp/";


    if (file_exists($kiw_path . "/{$kiw_date}/") == true) {

        if (in_array($_SESSION['permission'], array("r", "rw"))) {


            $kiw_random = substr(md5(time() . rand(1, 10)), rand(1, 4), 10);

            $kiw_exec = `tar cfvz {$kiw_temp}kiwire-{$kiw_date}-{$kiw_random}.tgz -C {$kiw_path} {$kiw_date}`;

            echo json_encode(array("status" => "success", "message" => null, "data" => "/temp/kiwire-{$kiw_date}-{$kiw_random}.tgz"));


        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Missing backup directory", "data" => null));

        }

    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


} elseif ($_REQUEST['action'] == "purge") {


    $kiw_table = $kiw_db->escape($_REQUEST['table']);

    $days = $kiw_db->escape($_REQUEST['days']);

    switch ($kiw_table) {

        case "account":
            $kiw_db->query("DELETE FROM kiwire_account_auth WHERE date_activate IS NOT NULL AND date_last_login < DATE_SUB(NOW(), INTERVAL {$days} DAY)");
            break;
        case "info":
            $kiw_db->query("DELETE FROM kiwire_account_info WHERE updated_date < DATE_SUB(NOW(), INTERVAL {$days} DAY)");
            break;
        case "history":
            $kiw_db->query("DELETE FROM kiwire_device_history WHERE updated_date < DATE_SUB(NOW(), INTERVAL {$days} DAY)");
            break;
        case "survey":
            $kiw_db->query("DELETE FROM kiwire_survey_respond WHERE updated_date < DATE_SUB(NOW(), INTERVAL {$days} DAY)");
            break;
        case "nms":
            $kiw_db->query("DELETE FROM kiwire_nms_log WHERE updated_date < DATE_SUB(NOW(), INTERVAL {$days} DAY)");
            break;
        case "greport":
            $kiw_db->query("DELETE FROM kiwire_report_login_general WHERE updated_date < DATE_SUB(NOW(), INTERVAL {$days} DAY)");
            break;
        case "dreport":
            $kiw_db->query("DELETE FROM kiwire_report_login_device WHERE updated_date < DATE_SUB(NOW(), INTERVAL {$days} DAY)");
            break;
        case "dwreport":
            $kiw_db->query("DELETE FROM kiwire_report_login_dwell WHERE updated_date < DATE_SUB(NOW(), INTERVAL {$days} DAY)");
            break;
        case "ereport":
            $kiw_db->query("DELETE FROM kiwire_report_login_error WHERE updated_date < DATE_SUB(NOW(), INTERVAL {$days} DAY)");
            break;
        case "preport":
            $kiw_db->query("DELETE FROM kiwire_report_login_profile WHERE updated_date < DATE_SUB(NOW(), INTERVAL {$days} DAY)");
            break;
        case "creport":
            $kiw_db->query("DELETE FROM kiwire_report_campaign_general WHERE updated_date < DATE_SUB(NOW(), INTERVAL {$days} DAY)");
            break;
        case "coreport":
            $kiw_db->query("DELETE FROM kiwire_report_controller WHERE updated_date < DATE_SUB(NOW(), INTERVAL {$days} DAY)");
            break;
        case "cunique":
            $kiw_db->query("DELETE FROM kiwire_device_unique WHERE updated_date < DATE_SUB(NOW(), INTERVAL {$days} DAY)");
            break;
        case "message":
            $kiw_db->query("DELETE FROM kiwire_message WHERE updated_date < DATE_SUB(NOW(), INTERVAL {$days} DAY)");
            break;

    }


    echo json_encode(array("status" => "success", "message" => "SUCCESS: Deleted successfully",  "rows" => $kiw_db->db_affected_row));


}
