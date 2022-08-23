<?php

$kiw['module'] = "Help -> Services";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_general.php";
require_once "../includes/include_session.php";
require_once "../includes/include_connection.php";


if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}


$kiw_ha_setting = @file_get_contents(dirname(__FILE__, 3) . "/custom/ha_setting.json");

$kiw_ha_setting = json_decode($kiw_ha_setting, true);


$kiw_temp['last_update'] = date("Y-m-d H:i:s");


$kiw_timezone = $_SESSION['timezone'];

if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


$kiw_temp['last_update'] = sync_tolocaltime($kiw_temp['last_update'], $kiw_timezone);


$kiw_services = @file_get_contents(dirname(__FILE__, 3) . "/custom/service_report.json");

$kiw_services = json_decode($kiw_services, true);


foreach ($kiw_services as $kiw_service => $kiw_value){


    if ($kiw_service == "kiwire_replication"){


        if ($kiw_ha_setting['enabled'] == "y") {


            if ($kiw_ha_setting['role'] == "backup") {


                $kiw_current_service = array(
                    "service" => $kiw_service,
                    "status" => ((time() - strtotime($kiw_value)) > 180 ? "down" : "up"),
                    "last_running" => sync_tolocaltime($kiw_value, $kiw_timezone),
                );


            } else {


                $kiw_current_service = array(
                    "service" => $kiw_service,
                    "status" => "up",
                    "last_running" => sync_tolocaltime(date("Y-m-d H:i:s"), $kiw_timezone),
                );


            }


        }


    } else {

        $kiw_current_service = array(
            "service" => $kiw_service,
            "status" => ((time() - strtotime($kiw_value)) > 180 ? "down" : "up"),
            "last_running" => sync_tolocaltime($kiw_value, $kiw_timezone),
        );

    }


    $kiw_temp['service'][] = $kiw_current_service;

    unset($kiw_current_service);


}


if (!isset($kiw_temp['service']) || empty($kiw_temp['service'])){

    $kiw_temp['service'] = [];

}


// get current cpu info

$kiw_cpu = "";
$kiw_cpu = sys_getloadavg();
$kiw_temp['cpu_used'] = $kiw_cpu;


// get current memory usage / total memory

$kiw_free = shell_exec('free');
$kiw_free = (string)trim($kiw_free);
$kiw_free = explode("\n", $kiw_free);

$kiw_free = explode(" ", $kiw_free[1]);
$kiw_free = array_filter($kiw_free);
$kiw_free = array_merge($kiw_free);

$kiw_temp['memory_used'] = round(($kiw_free[2] / pow(1024, 2)), 2);
$kiw_temp['memory_free'] = round(($kiw_free[3] / pow(1024, 2)), 2);
$kiw_temp['memory_total'] = round(($kiw_free[1] / pow(1024, 2)), 2);
$kiw_temp['memory_percent'] = round(($kiw_temp['memory_used'] / $kiw_temp['memory_total']) * 100, 2);

// get current disk usage / total usage

$kiw_free = disk_free_space("/");
$kiw_total = disk_total_space("/");
$kiw_used = $kiw_total - $kiw_free;

$kiw_free = round($kiw_free / pow(1024, 3), 2);
$kiw_total = round($kiw_total / pow(1024, 3), 2);
$kiw_used = round($kiw_used / pow(1024, 3), 2);

$kiw_temp['disk_used'] = $kiw_used;
$kiw_temp['disk_free'] = $kiw_free;
$kiw_temp['disk_total'] = $kiw_total;
$kiw_temp['disk_percent'] = round(($kiw_temp['disk_used'] / $kiw_temp['disk_total']) * 100, 2);

echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));

