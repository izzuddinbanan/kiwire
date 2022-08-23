<?php

$kiw['module'] = "Device -> Monitoring -> Dashboard";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_general.php";
require_once "../includes/include_connection.php";

if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}


$action = $_REQUEST['action'];

switch ($action) {

    case "general": general($kiw_db, $_SESSION['tenant_id']); break;
    case "warning_critical": warning_critical($kiw_db, $_SESSION['tenant_id']); break;
    case "status": status($kiw_db, $_SESSION['tenant_id']); break;
    default: echo "ERROR: Wrong implementation";

}


function general($kiw_db, $kiw_tenant){

    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_result = [];

        $kiw_data = $kiw_db->fetch_array("SELECT unique_id,status FROM kiwire_controller WHERE monitor_method IN ('ping', 'snmp', 'wifidog') AND tenant_id = '{$kiw_tenant}'");


        foreach ($kiw_data as $kiw_datum){


            if (empty($kiw_datum['status'])) $kiw_datum['status'] = "down";

            $kiw_result[$kiw_datum['status']] += 1;


        }


        $kiw_result['total'] = count($kiw_data);

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_result));


    } else {

        echo json_encode(array("status" => "error", "message" => "You are not allowed to access this module", "data" => null));

    }


}


function warning_critical($kiw_db, $kiw_tenant){


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_controllers = $kiw_db->fetch_array("SELECT SQL_CACHE UPPER(unique_id) AS unique_id,IFNULL(status, 'down') AS status,IFNULL(last_update, 'Never') AS last_update FROM kiwire_controller WHERE tenant_id = '{$kiw_tenant}' AND monitor_method IN ('ping', 'snmp', 'wifidog') AND (status = 'warning' OR status = 'down' OR status = 'unknown' OR status = '')");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_controllers));



    } else {

        echo json_encode(array("status" => "error", "message" => "You are not allowed to access this module", "data" => null));

    }


}


function status($kiw_db, $kiw_tenant){


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_log_path = dirname(__FILE__, 4) . "/logs/nms/";


        $kiw_timezone = $_SESSION['timezone'];

        if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


        $kiw_result = [];

        $kiw_data = $kiw_db->fetch_array("SELECT SQL_CACHE unique_id,status,device_ip,location FROM kiwire_controller WHERE monitor_method IN ('ping', 'snmp', 'wifidog') AND tenant_id = '{$kiw_tenant}'");


        foreach ($kiw_data as $kiw_datum){


            $kiw_nms_data = @file_get_contents($kiw_log_path . "{$_SESSION['tenant_id']}-" . md5($kiw_datum['unique_id']) . ".log");

            if (!empty($kiw_nms_data)) {


                $kiw_temp = json_decode($kiw_nms_data, true);


                if (date("Y", $kiw_temp['time']) == "1970"){

                    $kiw_temp['time'] = "Unknown";

                } else $kiw_temp['time'] = sync_tolocaltime(date("Y-m-d H:i:s", $kiw_temp['time']), $kiw_timezone);


                $kiw_temp['unique_id']      = $kiw_datum['unique_id'];
                $kiw_temp['input_vol']      = round($kiw_temp['input_vol'] / pow(1024, 2), 2);
                $kiw_temp['output_vol']     = round($kiw_temp['output_vol'] / pow(1024, 2), 2);
                $kiw_temp['input_vol']      = number_format($kiw_temp['input_vol'], 3, ".", ",");
                $kiw_temp['output_vol']     = number_format($kiw_temp['output_vol'], 3, ".", ",");
                $kiw_temp["location"]       = $kiw_datum['location'];

            } else {

                $kiw_temp["time"]          = "Never";
                $kiw_temp["input_vol"]     = "0.000";
                $kiw_temp["output_vol"]    = "0.000";
                $kiw_temp["unique_id"]     = $kiw_datum['unique_id'];
                $kiw_temp["ip_address"]    = $kiw_datum['device_ip'];
                $kiw_temp["location"]      = $kiw_datum['location'];
                $kiw_temp["status"]        = "down";
                $kiw_temp["avg_speed"]     = "0";

            }


            $kiw_result[$kiw_datum['unique_id']] = $kiw_temp;

            unset($kiw_nms_data);

            unset($kiw_temp);


        }


        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_result));


    } else {

        echo json_encode(array("status" => "error", "message" => "You are not allowed to access this module", "data" => null));

    }


}

