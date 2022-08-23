<?php


global $kiw_request, $kiw_api, $kiw_roles;

if (in_array("Report -> Login Summary", $kiw_roles) == false) {

    die(json_encode(array("status" => "error", "message" => "This API key not allowed to access this module [ {$request_module[0]} ]", "data" => "")));

}


require_once dirname(__FILE__, 2) . "/admin/includes/include_report.php";


if ($kiw_request['method'] == "GET") {


    $kiw_config['report'] = $request_module[1];


    $kiw_config['start']   = report_date_start($request_module[2], 30);
    $kiw_config['end']     = report_date_end($request_module[3], 1);

    if (strlen($kiw_config['report']) < 5){

        die(json_encode(array("status" => "error", "message" => "Please provide report name to collect", "data" => "")));

    }


    if (strtotime($kiw_config['start']) == 0 || strtotime($kiw_config['end']) == 0){

        die(json_encode(array("status" => "error", "message" => "Please provide valid start and end date", "data" => "")));

    }

    if (count($request_module) > 4) {

        $kiw_config['limit']    = (int)$request_module[4];
        $kiw_config['offset']   = (int)$request_module[5];
        $kiw_config['column']   = $kiw_db->escape($request_module[6]);
        $kiw_config['order']    = strtolower($request_module[7]) == "asc" ? "ASC" : "DESC";

    } else {

        $kiw_config['limit']    = 1;
        $kiw_config['offset']   = 10;
        $kiw_config['column']   = "id";
        $kiw_config['order']    = "DESC";

    }

    if ($kiw_request['tenant'] !== "superuser") {

        $kiw_tenant_query = "AND tenant_id = '{$kiw_request['tenant']}'";

    } else $kiw_tenant_query = "";


    if ($kiw_config['report'] == "session"){


        $kiw_data = [];


        foreach (range(0, 6) as $kiw_range) {


            $kiw_month = date("Ym", strtotime("-{$kiw_range} Month"));


            $kiw_temp = $kiw_db->fetch_array("SELECT * FROM kiwire_sessions_{$kiw_month} WHERE (start_time BETWEEN '{$kiw_config['start']}' AND '{$kiw_config['end']}') {$kiw_tenant_query} ORDER BY {$kiw_config['column']} {$kiw_config['order']} LIMIT {$kiw_config['offset']}, {$kiw_config['limit']}");


            if (is_array($kiw_temp)) {

                $kiw_data = array_merge($kiw_data, $kiw_temp);

            }


            unset($kiw_temp);


        }


    } elseif ($kiw_config['report'] == "login-general"){

        $kiw_data = $kiw_db->fetch_array("SELECT * FROM kiwire_report_login_general WHERE (report_date BETWEEN '{$kiw_config['start']}' AND '{$kiw_config['end']}') AND tenant_id = '{$kiw_request['tenant']}' ORDER BY {$kiw_config['column']} {$kiw_config['order']} LIMIT {$kiw_config['offset']}, {$kiw_config['limit']}");


    } elseif ($kiw_config['report'] == "login-profile"){


        $kiw_data = $kiw_db->fetch_array("SELECT * FROM kiwire_report_login_profile WHERE (report_date BETWEEN '{$kiw_config['start']}' AND '{$kiw_config['end']}') {$kiw_tenant_query} ORDER BY {$kiw_config['column']} {$kiw_config['order']} LIMIT {$kiw_config['offset']}, {$kiw_config['limit']}");


    } elseif ($kiw_config['report'] == "login-error"){


        $kiw_data = $kiw_db->fetch_array("SELECT * FROM kiwire_report_login_error WHERE (report_date BETWEEN '{$kiw_config['start']}' AND '{$kiw_config['end']}') {$kiw_tenant_query} ORDER BY {$kiw_config['column']} {$kiw_config['order']} LIMIT {$kiw_config['offset']}, {$kiw_config['limit']}");


    } elseif ($kiw_config['report'] == "login-device"){


        $kiw_data = $kiw_db->fetch_array("SELECT * FROM kiwire_report_login_device WHERE (report_date BETWEEN '{$kiw_config['start']}' AND '{$kiw_config['end']}') {$kiw_tenant_query} ORDER BY {$kiw_config['column']} {$kiw_config['order']} LIMIT {$kiw_config['offset']}, {$kiw_config['limit']}");


    } elseif ($kiw_config['report'] == "login-dwell"){


        $kiw_data = $kiw_db->fetch_array("SELECT * FROM kiwire_report_login_dwell WHERE (report_date BETWEEN '{$kiw_config['start']}' AND '{$kiw_config['end']}') {$kiw_tenant_query} ORDER BY {$kiw_config['column']} {$kiw_config['order']} LIMIT {$kiw_config['offset']}, {$kiw_config['limit']}");


    } elseif ($kiw_config['report'] == "controller-general"){


        $kiw_data = $kiw_db->fetch_array("SELECT * FROM kiwire_report_controller WHERE (report_date BETWEEN '{$kiw_config['start']}' AND '{$kiw_config['end']}') {$kiw_tenant_query} ORDER BY {$kiw_config['column']} {$kiw_config['order']} LIMIT {$kiw_config['offset']}, {$kiw_config['limit']}");


    } elseif ($kiw_config['report'] == "controller-statistics"){


        $kiw_data = $kiw_db->fetch_array("SELECT * FROM kiwire_report_controller_statistics WHERE (report_date BETWEEN '{$kiw_config['start']}' AND '{$kiw_config['end']}') {$kiw_tenant_query} ORDER BY {$kiw_config['column']} {$kiw_config['order']} LIMIT {$kiw_config['offset']}, {$kiw_config['limit']}");


    } elseif ($kiw_config['report'] == "campaign"){


        $kiw_data = $kiw_db->fetch_array("SELECT * FROM kiwire_report_campaign_general WHERE (report_date BETWEEN '{$kiw_config['start']}' AND '{$kiw_config['end']}') {$kiw_tenant_query} ORDER BY {$kiw_config['column']} {$kiw_config['order']} LIMIT {$kiw_config['offset']}, {$kiw_config['limit']}");


    }
    elseif($kiw_config['report'] == "login-record"){


        $kiw_columns = [
            'tenant_id',
            'start_time as date_time_login',
            'stop_time as date_time_logout',
            'username',
            'zone',
            'mac_address',
            'ip_address',
            'ipv6_address',
            'controller',
            'quota_in', 
            'quota_out',
            'terminate_reason',
            'session_time',
            'avg_speed',
            'profile',
            'class',
            'brand',
            'model',
        ];

        if(empty($_GET['start_time']) && empty($_GET['stop_time'])){
            echo json_encode(array("status" => "error", "message" => "Please check your parameter", "data" => NULL));
            die;
        }

        $kiw_cloud =  $kiw_db->query_first("SELECT volume_metrics, timezone FROM `kiwire_clouds` WHERE tenant_id = '{$kiw_request['tenant']}'");
        
        $kiw_tables = [];
        
        $kiw_type       = "start_time";
        $kiw_timezone   = !empty($_GET['timezone']) ? $_GET['timezone'] : 'Asia/Kuala_Lumpur';

        $start_time     = $kiw_db->escape($_GET['start_time']);
        $stop_time      = $kiw_db->escape($_GET['stop_time']);

        $kiw_start      = $request_module[2] . " " .  $start_time;
        $kiw_end        = $request_module[3] . " " . $stop_time;

        $kiw_table_name = "kiwire_sessions";
        $kiw_username   = $kiw_db->escape($_GET['username']);

        $timestamp      = time();

        try {

            $kiw_start  = new DateTime($kiw_start, new DateTimeZone($kiw_timezone));

            $kiw_end    = date("Y-m-d H:i:s", strtotime($kiw_end . " +1 Day -1 Seconds"));
            $kiw_end    = new DateTime($kiw_end, new DateTimeZone($kiw_timezone));

            $kiw_interval = $kiw_start->diff($kiw_end)->format("%a");

            $kiw_start->setTimeZone(new DateTimeZone("UTC"));
            $kiw_end->setTimeZone(new DateTimeZone("UTC"));

            $kiw_start  = $kiw_start->format('Y-m-d H:i:s');
            $kiw_end    = $kiw_end->format('Y-m-d H:i:s');

        } catch (Exception $e) {

            return false;

        }


        foreach (range($kiw_interval, 0) as $kiw_range) {


            $kiw_current_date = date("Ym", strtotime($kiw_end . "-{$kiw_range} Day"));

            $kiw_current_date = $kiw_table_name . "_" . $kiw_current_date;


            if (!in_array($kiw_current_date, $kiw_tables)) {

                $kiw_tables[] = $kiw_current_date;

            }


        }

        if (!empty($kiw_username)){

            $kiw_username = "AND username = '{$kiw_username}'";

        }
        else{

            $kiw_end = date("Y-m-d {$stop_time}", strtotime($kiw_start . ' +1 day'));
            $kiw_end    = new DateTime($kiw_end, new DateTimeZone($kiw_timezone));
            $kiw_end->setTimeZone(new DateTimeZone("UTC"));
            $kiw_end    = $kiw_end->format('Y-m-d H:i:s');

        } 


        $kiw_columns = implode(',', $kiw_columns);

        // get the actual data


        $kiw_result['data']             = array();
        foreach ($kiw_tables as $kiw_table) {

            $kiw_temp = $kiw_db->fetch_array("SELECT {$kiw_columns} FROM {$kiw_table} WHERE ({$kiw_type} BETWEEN '{$kiw_start}' AND '{$kiw_end}')  {$kiw_username} {$kiw_tenant_query}");


            if (is_array($kiw_temp)) {

                $kiw_result['data'] = array_merge($kiw_result['data'], $kiw_temp);

            }

            unset($kiw_temp);

        }


        $kiw_metric = $kiw_cloud['volume_metrics'];


        if ($kiw_metric['volume_metrics'] == "Gb" || empty($kiw_metric['volume_metrics'])) $kiw_metric = 1024 * 1024 * 1024;
        else $kiw_metric = 1024 * 1024;

        foreach($kiw_result['data'] as $key => $kiwire_data){


            $kiw_result['data'][$key]['session_time'] = gmdate("H:i:s", $kiw_result['data'][$key]['session_time']);

            if ($kiw_metric['volume_metrics'] == "Gb") {

                $kiw_result['data'][$key]['avg_speed'] = number_format(round($kiw_result['data'][$key]['avg_speed'] / (1024 * 1024), 3), 3);

            } else $kiw_result['data'][$key]['avg_speed'] = number_format(round($kiw_result['data'][$key]['avg_speed'] / 1024, 3), 3);
        }

        
        $kiw_data = $kiw_result['data']; 
        
    }


    echo json_encode(array("status" => "success", "message" => "", "data" => $kiw_data));


} else echo json_encode(array("status" => "error", "message" => "This module only allow GET request", "data" => $kiw_data));
