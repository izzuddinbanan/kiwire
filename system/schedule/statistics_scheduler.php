<?php


error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);


$kiw_lock_name = "kiwire-statistics.lock";

require_once "scheduler_lock.php";


require_once "/var/www/kiwire/server/admin/includes/include_config.php";


$kiw_path = dirname(__FILE__);


require_once dirname(__FILE__, 3) . "/server/admin/includes/include_config.php";
require_once dirname(__FILE__, 3) . "/server/admin/includes/include_general.php";
require_once dirname(__FILE__, 3) . "/server/admin/includes/include_connection.php";


ini_set("max_execution_time", 300);


// check if log path available. if not then create

$kiw_log_path = dirname(__FILE__, 3) . "/logs/nms/";

if (file_exists($kiw_log_path) == false){

    mkdir($kiw_log_path, 755, true);

}


system("chown www-data:www-data -R {$kiw_log_path}");

system("chmod 755 -R {$kiw_log_path}");


$kiw_db = Database::obtain();


// check nms statistics

$kiw_nms_logs = $kiw_db->fetch_array("SELECT * FROM kiwire_nms_log WHERE processed = 0 ORDER BY updated_date ASC");


$kiw_controller_down = [];

$kiw_scheduler_time['start'] = date('Y-m-d H:i:s');
foreach ($kiw_nms_logs as $kiw_nms_log){


    // device info

    $kiw_controller = $kiw_db->query_first("SELECT SQL_CACHE tenant_id,device_ip,device_type,location,mib,last_update,vendor FROM kiwire_controller WHERE tenant_id = '{$kiw_nms_log['tenant_id']}' AND unique_id = '{$kiw_nms_log['unique_id']}' LIMIT 1");


    $kiw_temp = null;


    if ($kiw_nms_log['status'] == "down"){


        $kiw_controller['issue'][] = "CRITICAL: Unreachable via IP {$kiw_controller['device_ip']}";

        $kiw_controller_down[$kiw_nms_log['tenant_id']][$kiw_nms_log['unique_id']] = $kiw_controller;


    } else {


        if (!empty($kiw_controller['mib'])) {


            // do checking if match with the rules

            $kiw_rules = $kiw_cache->get("NMS:RULES:{$kiw_controller['tenant_id']}:{$kiw_controller['mib']}");

            if (empty($kiw_rules)){


                $kiw_rules = $kiw_db->query_first("SELECT * FROM kiwire_nms_rules WHERE mib = '{$kiw_controller['mib']}' AND tenant_id = '{$kiw_controller['tenant_id']}' LIMIT 1");

                if (empty($kiw_rules)) $kiw_rules = array("dummy" => true);

                $kiw_cache->set("NMS:RULES:{$kiw_controller['tenant_id']}:{$kiw_controller['mib']}", $kiw_rules, 1800);


            }


            if ($kiw_rules['critical_cpu'] > 0 && $kiw_rules['warning_cpu'] > 0) {


                if ($kiw_nms_log['cpu_load'] >= $kiw_rules['critical_cpu']) {


                    $kiw_controller['issue'][] = "CRITICAL: CPU Usage [ {$kiw_nms_log['cpu_load']}% ]";

                    $kiw_controller_down[$kiw_nms_log['tenant_id']][$kiw_nms_log['unique_id']] = $kiw_controller;


                } elseif ($kiw_nms_log['cpu_load'] >= $kiw_rules['warning_cpu']) {


                    $kiw_controller['issue'][] = "WARNING: CPU Usage [ {$kiw_nms_log['cpu_load']}% ]";

                    $kiw_controller_down[$kiw_nms_log['tenant_id']][$kiw_nms_log['unique_id']] = $kiw_controller;


                }


            }


            unset($kiw_temp);


            if ($kiw_rules['critical_memory'] > 0 && $kiw_rules['warning_memory'] > 0) {


                $kiw_nms_log['memory_used'] = empty($kiw_nms_log['memory_used']) ? 100 : $kiw_nms_log['memory_used'];
                $kiw_nms_log['memory_total'] = empty($kiw_nms_log['memory_total']) ? 1 : $kiw_nms_log['memory_total'];

                $kiw_temp = round(($kiw_nms_log['memory_used'] / $kiw_nms_log['memory_total']) * 100, 0, PHP_ROUND_HALF_DOWN);


                if ($kiw_temp >= $kiw_rules['critical_memory']) {


                    $kiw_controller['issue'][] = "CRITICAL: Memory Usage [ {$kiw_temp}% ]";

                    $kiw_controller_down[$kiw_nms_log['tenant_id']][$kiw_nms_log['unique_id']] = $kiw_controller;


                } elseif ($kiw_temp >= $kiw_rules['warning_memory']) {


                    $kiw_controller['issue'][] = "WARNING: Memory Usage [ {$kiw_temp}% ]";

                    $kiw_controller_down[$kiw_nms_log['tenant_id']][$kiw_nms_log['unique_id']] = $kiw_controller;


                }


            }


            unset($kiw_temp);


            if ($kiw_rules['critical_disk'] > 0 && $kiw_rules['warning_disk'] > 0) {


                $kiw_nms_log['disk_used'] = empty($kiw_nms_log['disk_used']) ? 100 : $kiw_nms_log['disk_used'];
                $kiw_nms_log['disk_total'] = empty($kiw_nms_log['disk_total']) ? 1 : $kiw_nms_log['disk_total'];

                $kiw_temp = round(($kiw_nms_log['disk_used'] / $kiw_nms_log['disk_total']) * 100, 0, PHP_ROUND_HALF_DOWN);


                if ($kiw_temp >= $kiw_rules['critical_disk']) {


                    $kiw_controller['issue'][] = "CRITICAL: Storage Usage [ {$kiw_temp}% ]";

                    $kiw_controller_down[$kiw_nms_log['tenant_id']][$kiw_nms_log['unique_id']] = $kiw_controller;


                } elseif ($kiw_temp >= $kiw_rules['warning_disk']) {


                    $kiw_controller['issue'][] = "WARNING: Storage Usage [ {$kiw_temp}% ]";

                    $kiw_controller_down[$kiw_nms_log['tenant_id']][$kiw_nms_log['unique_id']] = $kiw_controller;


                }


            }


            unset($kiw_temp);


        }


        if ($kiw_nms_log['ping'] <= 80){


            $kiw_controller['issue'][] = "WARNING: Ping response less than 80% [ {$kiw_nms_log['ping']} ]";

            $kiw_controller_down[$kiw_nms_log['tenant_id']][$kiw_nms_log['unique_id']] = $kiw_controller;


        }


        if ($kiw_nms_log['status'] == "warning"){


            $kiw_controller['issue'][] = "WARNING: {$kiw_nms_log['reason']}";

            $kiw_controller_down[$kiw_nms_log['tenant_id']][$kiw_nms_log['unique_id']] = $kiw_controller;


        }


    }


    unset($kiw_temp);

    unset($kiw_rules);


    if ($kiw_nms_log['status'] == "running" || $kiw_nms_log['status'] == "warning") {


        $kiw_nms_data = @file_get_contents($kiw_log_path . "{$kiw_nms_log['tenant_id']}-" . md5($kiw_nms_log['unique_id']) . ".log");

        $kiw_nms_data = json_decode($kiw_nms_data, true);


        $kiw_first_data = count($kiw_nms_data) == 0;


        // calculate for speed based on previous data

        if (!isset($kiw_nms_data['time']) || empty($kiw_nms_data['time'])) $kiw_nms_data['time'] = strtotime($kiw_nms_log['updated_date'] . " -1 Minute");


        $kiw_temp['interval'] = (strtotime($kiw_nms_log['updated_date']) - $kiw_nms_data['time']);

        $kiw_temp['report_date'] = date("Y-m-d H:i:00", strtotime($kiw_nms_log['updated_date']));
        $kiw_temp['unique_id'] = $kiw_nms_log['unique_id'];


        // need to count if already repeat 32 bit number

        if ($kiw_first_data) {

            $kiw_temp['quota_upload'] = $kiw_nms_log['input_vol'];

        } elseif (($kiw_nms_log['input_vol'] > 0) && ($kiw_nms_log['input_vol'] < $kiw_nms_data['current_input_vol'])) {

            $kiw_temp['quota_upload'] = $kiw_nms_log['input_vol'] + (pow(2, 32) - $kiw_nms_data['current_input_vol']);

        } else $kiw_temp['quota_upload'] = $kiw_nms_log['input_vol'] - $kiw_nms_data['current_input_vol'];


        if ($kiw_first_data) {

            $kiw_temp['quota_download'] = $kiw_nms_log['output_vol'];

        } elseif (($kiw_nms_log['output_vol'] > 0) && ($kiw_nms_log['output_vol'] < $kiw_nms_data['current_output_vol'])) {

            $kiw_temp['quota_download'] = $kiw_nms_log['output_vol'] + (pow(2, 32) - $kiw_nms_data['current_output_vol']);

        } else $kiw_temp['quota_download'] = $kiw_nms_log['output_vol'] - $kiw_nms_data['current_output_vol'];


        $kiw_temp['avg_upload_speed'] = (($kiw_temp['quota_upload'] / $kiw_temp['interval']) * 8) / pow(1024, 2);
        $kiw_temp['avg_download_speed'] = (($kiw_temp['quota_download'] / $kiw_temp['interval']) * 8) / pow(1024, 2);
        $kiw_temp['avg_speed'] = ((($kiw_temp['quota_upload'] + $kiw_temp['quota_download']) * 8) / $kiw_temp['interval']) / pow(1024, 2);

        $kiw_temp['avg_upload_speed'] = round($kiw_temp['avg_upload_speed'], 3);
        $kiw_temp['avg_download_speed'] = round($kiw_temp['avg_download_speed'], 3);
        $kiw_temp['avg_speed'] = round($kiw_temp['avg_speed'], 3);

        // update data for future use

        $kiw_nms_data['time'] = strtotime($kiw_nms_log['updated_date']);
        $kiw_nms_data['input_vol'] = $kiw_temp['quota_upload'];
        $kiw_nms_data['output_vol'] = $kiw_temp['quota_download'];

        $kiw_nms_data['current_input_vol'] = $kiw_nms_log['input_vol'];
        $kiw_nms_data['current_output_vol'] = $kiw_nms_log['output_vol'];

        // additional data
        $kiw_nms_data['unique_id'] = $kiw_nms_log['unique_id'];
        $kiw_nms_data['ip_address'] = $kiw_controller['device_ip'];
        $kiw_nms_data['location'] = empty($kiw_controller['location']) ? "Unknown" : $kiw_controller['location'];
        $kiw_nms_data['status'] = $kiw_nms_log['status'];
        $kiw_nms_data['avg_speed'] = $kiw_temp['avg_speed'];

        unset($kiw_temp['interval']);

        $kiw_temp['tenant_id'] = $kiw_nms_log['tenant_id'];
        $kiw_temp['updated_date'] = "NOW()";
        $kiw_temp['source'] = "snmp";

        // update the log file again.

        @file_put_contents($kiw_log_path . "{$kiw_nms_log['tenant_id']}-" . md5($kiw_nms_log['unique_id']) . ".log", json_encode($kiw_nms_data));


        $kiw_db->insert("kiwire_report_controller_statistics", $kiw_temp);

        unset($kiw_temp);


        // update reason if got issue

        if (!empty($kiw_controller['issue'])){

            $kiw_temp = "status = 'warning', reason = '" . implode(",", $kiw_controller['issue']) . "'";

        } else $kiw_temp = "";


    } else {


        $kiw_nms_data['status']         = "down";
        $kiw_nms_data['input_vol']      = 0;
        $kiw_nms_data['output_vol']     = 0;

        $kiw_nms_data['current_input_vol']   = 0;
        $kiw_nms_data['current_output_vol']  = 0;

        $kiw_nms_data['unique_id']    = $kiw_nms_log['unique_id'];
        $kiw_nms_data['ip_address']   = $kiw_controller['device_ip'];
        $kiw_nms_data['location']     = $kiw_controller['location'];
        $kiw_nms_data['avg_speed']    = 0;


        @file_put_contents($kiw_log_path . "{$kiw_nms_log['tenant_id']}-" . md5($kiw_nms_log['unique_id']) . ".log", json_encode($kiw_nms_data));

        $kiw_temp = "";


    }


    $kiw_db->query("UPDATE kiwire_nms_log SET updated_date = NOW(),T processed = 1{$kiw_temp} WHERE id = '{$kiw_nms_log['id']}' LIMIT 1");

    unset($kiw_temp);
    unset($kiw_nms_data);
    unset($kiw_controller);


}


unset($kiw_nms_log);
unset($kiw_nms_logs);


// generate email for down devices

foreach ($kiw_controller_down as $kiw_tenant => $kiw_devices){


    $kiw_template = @file_get_contents(dirname(__FILE__, 3) . "/server/user/templates/notification-device-down.html");


    // get the email of the admin

    $kiw_admins = $kiw_db->fetch_array("SELECT SQL_CACHE username,email,fullname FROM kiwire_admin WHERE email <> '' AND monitor = 'y' AND tenant_id = '{$kiw_tenant}' LIMIT 50");


    if (is_array($kiw_admins) && count($kiw_admins) > 0) {


        $kiw_cache = new Redis();

        $kiw_cache->connect(SYNC_REDIS_HOST, SYNC_REDIS_PORT);


        $kiw_timezone = $kiw_db->query_first("SELECT timezone FROM kiwire_clouds WHERE tenant_id = '{$kiw_tenant}' LIMIT 1");

        $kiw_timezone = $kiw_timezone['timezone'];


        // get the template

        $kiw_email['action'] = "send_email";
        $kiw_email['tenant_id'] = $kiw_tenant;
        $kiw_email['subject'] = "Kiwire Notification: Device Issue !!";


        $kiw_temp = "<table style=\"border: thin black solid; margin: 10px; font-family: Arial, 'Open Sans', sans-serif; font-size: 12px; border-collapse: collapse;\">";
        $kiw_temp .= "        <thead>";
        $kiw_temp .= "            <tr>";
        $kiw_temp .= "                <td style='padding: 5px; border: thin black solid; background-color: #323232; color: white;'>NO</td>";
        $kiw_temp .= "                <td style='padding: 5px; border: thin black solid; background-color: #323232; color: white;'>UNIQUE ID</td>";
        $kiw_temp .= "                <td style='padding: 5px; border: thin black solid; background-color: #323232; color: white;'>IP ADDRESS</td>";
        $kiw_temp .= "                <td style='padding: 5px; border: thin black solid; background-color: #323232; color: white;'>TYPE</td>";
        $kiw_temp .= "                <td style='padding: 5px; border: thin black solid; background-color: #323232; color: white;'>VENDOR</td>";
        $kiw_temp .= "                <td style='padding: 5px; border: thin black solid; background-color: #323232; color: white;'>LOCATION</td>";
        $kiw_temp .= "                <td style='padding: 5px; border: thin black solid; background-color: #323232; color: white;'>LAST ACTIVE</td>";
        $kiw_temp .= "                <td style='padding: 5px; border: thin black solid; background-color: #323232; color: white;'>ISSUE(S)</td>";
        $kiw_temp .= "            </tr>";
        $kiw_temp .= "        </thead>";
        $kiw_temp .= "        <tbody>";

        $kiw_numbering = 1;


        foreach ($kiw_devices as $kiw_device => $kiw_data) {


            $kiw_issue = implode("<br> &bull; ", $kiw_data['issue']);


            $kiw_is_sent = $kiw_cache->get("DEVICE_ISSUES:{$kiw_tenant}:" . md5($kiw_issue));


            if (empty($kiw_is_sent) || (time() - $kiw_is_sent) > 900) {


                $kiw_temp .= "<tr>";
                $kiw_temp .= "<td style='padding: 5px; border: thin black solid;'>{$kiw_numbering}</td>";
                $kiw_temp .= "<td style='padding: 5px; border: thin black solid;'>{$kiw_device}</td>";
                $kiw_temp .= "<td style='padding: 5px; border: thin black solid;'>{$kiw_data['device_ip']}</td>";
                $kiw_temp .= "<td style='padding: 5px; border: thin black solid;'>" . ucfirst($kiw_data['device_type']) . "</td>";
                $kiw_temp .= "<td style='padding: 5px; border: thin black solid;'>" . ucfirst($kiw_data['vendor']) . "</td>";
                $kiw_temp .= "<td style='padding: 5px; border: thin black solid;'>" . (empty($kiw_data['location']) ? "Unknown" : $kiw_data['location']) . "</td>";
                $kiw_temp .= "<td style='padding: 5px; border: thin black solid;'>" . sync_tolocaltime($kiw_data['last_update'], $kiw_timezone) . "</td>";
                $kiw_temp .= "<td style='padding: 5px; border: thin black solid;'>{$kiw_issue}</td>";
                $kiw_temp .= "</tr>";

                $kiw_cache->set("DEVICE_ISSUES:{$kiw_tenant}:" . md5($kiw_issue), time(), 1800);

                $kiw_numbering++;


            }


            unset($kiw_issue);
            unset($kiw_is_sent);


        }


        $kiw_temp .= "        </tbody>";
        $kiw_temp .= "    </table>";


        unset($kiw_device);

        unset($kiw_data);

        unset($kiw_devices);


        $kiw_email['content'] = str_replace("{{table_data}}", $kiw_temp, $kiw_template);


        unset($kiw_temp);


        if ($kiw_numbering > 1) {


            // send the email

            foreach ($kiw_admins as $kiw_admin) {


                $kiw_email['email_address'] = $kiw_admin['email'];
                $kiw_email['name'] = $kiw_admin['fullname'];

                $kiw_connection = curl_init();

                curl_setopt($kiw_connection, CURLOPT_URL, "http://127.0.0.1:9956");
                curl_setopt($kiw_connection, CURLOPT_POST, true);
                curl_setopt($kiw_connection, CURLOPT_POSTFIELDS, http_build_query($kiw_email));
                curl_setopt($kiw_connection, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($kiw_connection, CURLOPT_TIMEOUT, 5);
                curl_setopt($kiw_connection, CURLOPT_CONNECTTIMEOUT, 5);

                curl_exec($kiw_connection);
                curl_close($kiw_connection);

                unset($kiw_connection);


            }


        }


    }



}


unset($kiw_controller_down);
unset($kiw_tenant);
unset($kiw_devices);
unset($kiw_data);
unset($kiw_admins);
unset($kiw_admin);



// get controller status reports

$kiw_controllers = $kiw_db->fetch_array("SELECT tenant_id,unique_id,status FROM kiwire_controller");

$kiw_controller_status = [];


foreach ($kiw_controllers as $kiw_controller){


    $kiw_controller_status[$kiw_controller['tenant_id']]['total'] += 1;


    if ($kiw_controller['status'] == "running"){


        $kiw_controller_status[$kiw_controller['tenant_id']]['running'] += 1;


    } else {


        $kiw_controller_status[$kiw_controller['tenant_id']]['issue'] = array($kiw_controller['unique_id'] => $kiw_controller['status']);


    }


}


unset($kiw_controller);

unset($kiw_controllers);


$kiw_time = date("Y-m-d H:00:00");


foreach ($kiw_controller_status as $kiw_key => $kiw_value){


    $kiw_controller = $kiw_db->query_first("SELECT * FROM kiwire_report_controller WHERE tenant_id = '{$kiw_key}' AND report_date = '{$kiw_time}' LIMIT 1");

    $kiw_status_update = false;


    if (!empty($kiw_controller)){


        $kiw_status_update = true;


        if ($kiw_value['total'] > $kiw_controller['total']) $kiw_controller['total'] = $kiw_value['total'];
        if ($kiw_value['running'] > $kiw_controller['running']) $kiw_controller['running'] = $kiw_value['running'];


        $kiw_controller['issue'] = json_decode($kiw_controller['issue'], true);


        if (is_array($kiw_controller['issue'])) {


            $kiw_device_list = array_keys($kiw_controller['issue']);


            foreach ($kiw_value['issue'] as $kiw_uniqueid => $kiw_status) {

                if (!in_array($kiw_uniqueid, $kiw_device_list)) {

                    $kiw_controller['issue'][] = array($kiw_uniqueid => $kiw_status);

                }

            }


            $kiw_controller['issue'] = json_encode($kiw_controller['issue']);

            $kiw_controller['incident_count'] = count($kiw_value['issue']);


        } else {


            $kiw_controller['issue'] = '';

            $kiw_controller['incident_count'] = 0;


        }


        unset($kiw_device_list);


        $kiw_db->query("UPDATE kiwire_report_controller SET updated_date = NOW(), total = {$kiw_controller['total']}, running = {$kiw_controller['running']}, incident_count = {$kiw_controller['incident_count']}, issue = '{$kiw_controller['issue']}' WHERE tenant_id = '{$kiw_key}' AND report_date = '{$kiw_time}' LIMIT 1");


    } else {


        $kiw_controller['total']     = empty($kiw_value['total']) ? 0 : $kiw_value['total'];
        $kiw_controller['running']   = empty($kiw_value['running']) ? 0 : $kiw_value['running'];


        if (is_array($kiw_value['issue'])){


            $kiw_controller['issue'] = json_encode($kiw_value['issue']);

            $kiw_controller['incident_count'] = count($kiw_value['issue']);

        } else {

            $kiw_controller['issue'] = '';

            $kiw_controller['incident_count'] = 0;

        }


        $kiw_db->query("INSERT INTO kiwire_report_controller (id, tenant_id, updated_date, report_date, total, running, incident_count, issue) VALUE (NULL, '{$kiw_key}', NOW(), '{$kiw_time}', {$kiw_controller['total']}, {$kiw_controller['running']}, {$kiw_controller['incident_count']}, '{$kiw_controller['issue']}')");


    }


    unset($kiw_controller);


}

$kiw_scheduler_time['end'] = date('Y-m-d H:i:s');
$kiw_cache->set("KIW_SCHEDULER:STATISTIC_SCHEDULER:RUN_AT", $kiw_scheduler_time);



// check user statistics



// check administrator statistics





