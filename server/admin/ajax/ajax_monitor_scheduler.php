<?php

$kiw['module'] = "Report -> Monitor -> Service";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_config.php";
require_once "../includes/include_session.php";
require_once "../includes/include_connection.php";
require_once "../includes/include_general.php";

if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}


foreach (["KIWIRE_SCHEDULER" => "Every Minute", "REPLICATION_SCHEDULER" => "Every Minute", "STATISTIC_SCHEDULER" => "Every Minute", "REPORTING_SCHEDULER" => "Every 30 Minutes", "GHOST_SCHEDULER" => "Every Minute", "GHOST_SESSION_SCHEDULER" => "Every Minute", "NMS_SCHEDULER" => "Every Minute"] as $value => $minute) {

    $schedulers[$value]['name']     = str_replace("_", " ", $value);
    $schedulers[$value]['run']    = $minute ;
    
    $data = $kiw_cache->get("KIW_SCHEDULER:{$value}:RUN_AT");
    // if($value == 'STATISTIC_SCHEDULER'){
        // echo json_encode(array("status" => "success", "message" => "aa", "data" => $data));

    // }

    if(isset($data['start']) && isset($data['end'])){


        $schedulers[$value]["last_run_start"] = date('d M Y H:i:s' , strtotime($data['start']));
        $schedulers[$value]["last_run_end"]   = date('d M Y H:i:s' , strtotime($data['start']));

        $temp = strtotime($data['end']) - strtotime($data['start']);

        $schedulers[$value]["time_taken"]     =  $temp . "(s)";

        $schedulers[$value]["status"]     = ($temp < 120) ? "Active" : "Inactive";

    } else {

        $schedulers[$value]["last_run_start"] = "-";
        $schedulers[$value]["last_run_end"]   = "-";
        $schedulers[$value]["time_taken"]     = "-";
        $schedulers[$value]["status"]         = "Inactive";

    }


}

echo json_encode(array("status" => "success", "message" => "", "data" => $schedulers));
