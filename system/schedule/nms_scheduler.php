<?php


error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);


$kiw_lock_name = "kiwire-nms.lock";

require_once "scheduler_lock.php";


$kiw_path = dirname(__FILE__);


require_once dirname(__FILE__, 3) . "/server/admin/includes/include_config.php";
require_once dirname(__FILE__, 3) . "/server/admin/includes/include_connection.php";


ini_set("max_execution_time", 300);

$kiw_time['start'] = date('Y-m-d H:i:s');
// get number of days for log data to keep

$kiw_system = @file_get_contents(dirname(__FILE__, 3) . "/server/custom/system_setting.json");

$kiw_system = json_decode($kiw_system, true);


$kiw_system['device_monitor'] = (int)$kiw_system['device_monitor'];
$kiw_system['keep_log_data'] = (int)$kiw_system['keep_log_data'];

if (empty($kiw_system['device_monitor'])) $kiw_system['device_monitor'] = 5;
if (empty($kiw_system['keep_log_data'])) $kiw_system['keep_log_data'] = 3;


$kiw_schedule = @file_get_contents(dirname(__FILE__, 3) . "/server/custom/nms_time.log");

if (empty($kiw_schedule)) $kiw_schedule = 120;


if ((time() - $kiw_schedule) < ($kiw_system['device_monitor'] * 60)){

    die(0);

}

@file_put_contents(dirname(__FILE__, 3) . "/server/custom/nms_time.log", time());


// delete all log data to keep storage low

$kiw_db->query("DELETE FROM kiwire_nms_log WHERE updated_date < DATE_SUB(NOW(), INTERVAL {$kiw_system['keep_log_data']} DAY)");


// update wifidog device since they are not able to be ping or snmp

$kiw_wifidogs = $kiw_db->fetch_array("SELECT * FROM kiwire_controller WHERE monitor_method = 'wifidog' AND last_update < DATE_SUB(NOW(), INTERVAL " . ($kiw_system['device_monitor'] * 60) . " SECOND)");

foreach ($kiw_wifidogs as $kiw_wifidog){


    $kiw_data['id']             = "NULL";
    $kiw_data['tenant_id']      = $kiw_wifidog['tenant_id'];
    $kiw_data['unique_id']      = $kiw_wifidog['unique_id'];
    $kiw_data['updated_date']   = "NOW()";

    $kiw_data['processed']      = 0;
    $kiw_data['status']         = "down";
    $kiw_data['reason']         = "";
    $kiw_data['ping']           = "0";

    $kiw_data['system_name']    = "";
    $kiw_data['uptime']         = "";
    $kiw_data['cpu_load']       = 0;
    $kiw_data['memory_total']   = 0;

    $kiw_data['memory_used']    = 0;
    $kiw_data['disk_total']     = 0;
    $kiw_data['disk_used']      = 0;
    $kiw_data['input_vol']      = 0;

    $kiw_data['output_vol']     = 0;
    $kiw_data['if_total']       = 0;
    $kiw_data['if_status']      = 0;
    $kiw_data['if_desc']        = 0;

    $kiw_data['dev_loc']        = 0;
    $kiw_data['device_count']   = 0;

    $kiw_db->insert("kiwire_nms_log", $kiw_data);

    unset($kiw_data);


}

unset($kiw_wifidog);
unset($kiw_wifidogs);


// make sure all devices mark as down if no update interval +1 minute

$kiw_db->query("UPDATE kiwire_controller SET updated_date = NOW(), status = 'down' WHERE monitor_method <> 'none' AND last_update < DATE_SUB(NOW(), INTERVAL " . (($kiw_system['device_monitor'] * 60) + 60) . " SECOND)");


// the list of device

$kiw_devices = $kiw_db->fetch_array("SELECT SQL_CACHE unique_id,tenant_id,device_ip,monitor_method,mib,snmpv,community FROM kiwire_controller WHERE monitor_method = 'ping' OR monitor_method = 'snmp'");


// hardcoded max process to be 128 max to max sure not burst resource

if (is_array($kiw_devices) && count($kiw_devices) > 0) {

    $kiw_max_processes = count($kiw_devices);

} else die();


if ($kiw_max_processes > 128) $kiw_max_processes = 128;



// create a space to hold process

$kiw_max_processes = range(0, ($kiw_max_processes - 1));


$kiw_processes = [];
$kiw_pipe = [];

$kiw_completed = 0;
$kiw_total_device = count($kiw_devices);

$kiw_descriptor = array(
    0 => array("pipe", 'r'),
    1 => array("pipe", 'w'),
    2 => array("file", "/tmp/kiwire-error.log", 'a')
);


// check if child processer existed. if not then die.

if (file_exists("{$kiw_path}/nms_processor.php") == false){

    die("Child processor not found!\n");

}


foreach ($kiw_max_processes as $kiw_range) {

    $kiw_processes[$kiw_range]['process'] = proc_open("/usr/bin/php {$kiw_path}/nms_processor.php", $kiw_descriptor, $kiw_pipe[$kiw_range]);
    $kiw_processes[$kiw_range]['response'] = "";
    $kiw_processes[$kiw_range]['use'] = false;

    stream_set_blocking($kiw_pipe[$kiw_range][0], 0);
    stream_set_blocking($kiw_pipe[$kiw_range][1], 0);

}


$kiw_start = time();


while (true) {


    if ($kiw_completed < $kiw_total_device) {


        foreach ($kiw_max_processes as $kiw_range) {

            if ($kiw_processes[$kiw_range]['use'] == false) {

                if (is_resource($kiw_processes[$kiw_range]['process'])) {

                    if (isset($kiw_devices[$kiw_completed])) {


                        fwrite($kiw_pipe[$kiw_range][0], json_encode($kiw_devices[$kiw_completed]));

                        $kiw_processes[$kiw_range]['use'] = true;

                        $kiw_completed++;


                    }

                }

            }

        }


    }


    foreach ($kiw_max_processes as $kiw_range) {


        if ($kiw_processes[$kiw_range]['use'] == true) {


            $kiw_processes[$kiw_range]['response'] .= trim(fread($kiw_pipe[$kiw_range][1], 1024));

            $kiw_buffer = json_decode($kiw_processes[$kiw_range]['response'], true);


            if (!empty($kiw_processes[$kiw_range]['response'])) {


                if (is_array($kiw_buffer)) {

                    $kiw_processes[$kiw_range]['response'] = "";
                    $kiw_processes[$kiw_range]['use'] = false;

                } else {

                    file_put_contents(dirname(__FILE__, 3) . "/logs/nms/nms_scheduler-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s") . " | {$kiw_processes[$kiw_range]['response']}\n", FILE_APPEND);

                }


            }


        }


    }


    $kiw_check_complete = true;

    foreach ($kiw_processes as $process) {

        if ($process['use'] == true) {

            $kiw_check_complete = false;

        }


    }


    if ($kiw_check_complete == true && $kiw_completed >= $kiw_total_device) break;
    elseif ((time() - $kiw_start) > 60){

        // stop process after 60 seconds to avoid zombie

        break;

    }

    sleep(1);


}


foreach ($kiw_max_processes as $kiw_range) {


    fwrite($kiw_pipe[$kiw_range][0], "exit");

    fclose($kiw_pipe[$kiw_range][0]);
    fclose($kiw_pipe[$kiw_range][1]);

    proc_close($kiw_processes[$kiw_range]['process']);


}


$kiw_time['end'] = date('Y-m-d H:i:s');
$kiw_cache->set("KIW_SCHEDULER:NMS_SCHEDULER:RUN_AT", $kiw_time);
