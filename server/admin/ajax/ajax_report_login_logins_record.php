<?php


$kiw['module'] = "Report -> Login History";
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


$action = $_REQUEST['action'];



switch ($action) {

    case "get_by_date":
        get_datatable($kiw_db);
        break;
    case "get_csv":
        get_csv($kiw_db);
        break;

    default:
        echo "ERROR: Wrong implementation";
}



function get_csv($kiw_db)
{

    set_time_limit(0);

    $report_data = array();

    $report_data['metric'] = $_SESSION['metrics'];

    if ($report_data['metric'] == "Gb" || empty($report_data['metric'])) $report_data['metric'] = 1024 * 1024 * 1024;
    else $report_data['metric'] = 1024 * 1024;


    $report_data['timezone'] = $_SESSION['timezone'];

    if (empty($report_data['timezone'])) $report_data['timezone'] = "Asia/Kuala_Lumpur";


    $report_data['columns'] = [
        "CONVERT_TZ(start_time, 'UTC', '{$report_data['timezone']}') AS start_time",
        "IFNULL(CONVERT_TZ(stop_time, 'UTC', '{$report_data['timezone']}'), '-') AS stop_time",
        "username",
        "mac_address",
        "ip_address",
        "IFNULL(ipv6_address, 'NA') AS ipv6_address",
        "zone",
        "controller",
        "IFNULL(terminate_reason, '-') AS terminate_reason",
        "SEC_TO_TIME(session_time) AS session_time",
        "(avg_speed / 1024) AS avg_speed",
        "((quota_in + quota_in) / {$report_data['metric']}) AS quota",
        "profile",
        "class",
        "brand",
        "model",
        "tenant_id"
    ];

    # START FILTERING #
    $kiw_start = report_date_start($_REQUEST['startdate'], 30);

    $kiw_end = report_date_end($_REQUEST['enddate'], 1);


    $kiw_sql_search = "";


    if ($_REQUEST['type'] == "login") {

        $kiw_sql_search .= "(start_time BETWEEN '{$kiw_start}' AND '{$kiw_end}')";
    } else {

        $kiw_sql_search .= "(stop_time BETWEEN '{$kiw_start}' AND '{$kiw_end}')";
    }


    if (isset($_REQUEST['mac_address']) && !empty($_REQUEST['mac_address'])) {

        $kiw_sql_search .= " AND mac_address = '" . $kiw_db->escape($_REQUEST['mac_address']) . "'";
    }


    if (isset($_REQUEST['username']) && !empty($_REQUEST['username'])) {

        $kiw_sql_search .= " AND username = '" . $kiw_db->escape($_REQUEST['username']) . "'";
    }

    if (isset($_REQUEST['controller']) && !empty($_REQUEST['controller'])) {

        $kiw_sql_search .= " AND controller = '" . $kiw_db->escape($_REQUEST['controller']) . "'";
    }


    if (isset($_REQUEST['ip_address']) && !empty($_REQUEST['ip_address'])) {

        $kiw_sql_search .= " AND ip_address = '" . $kiw_db->escape($_REQUEST['ip_address']) . "'";
    }


    if ($_SESSION['access_level'] == "superuser") {


        $kiw_tenant = $kiw_db->escape($_REQUEST['tenant_id']);


        if (!empty($_SESSION['tenant_allowed'])) {


            $kiw_where = explode(",", $_SESSION['tenant_allowed']);


            if (!empty($kiw_tenant) && in_array($kiw_tenant, $kiw_where)) {

                $kiw_sql_search .= " AND tenant_id = '{$kiw_tenant}'";
            } else $kiw_sql_search .= " AND tenant_id IN ('" . implode("','", $kiw_where) . "')";


            unset($kiw_where);
        } else {


            if (!empty($kiw_tenant)) {

                $kiw_sql_search .= " AND tenant_id = '{$kiw_tenant}'";
            }
        }


        unset($kiw_tenant);
    } else $kiw_sql_search .= " AND tenant_id = '{$_SESSION['tenant_id']}'";


    if (isset($_REQUEST['profile']) && !empty($_REQUEST['profile'])) {

        $kiw_sql_search .= " AND profile = '" . $kiw_db->escape($_REQUEST['profile']) . "'";
    }


    // filter by zone/project

    if (isset($_REQUEST['zone']) && !empty($_REQUEST['zone'])) {

        $kiw_zone = $kiw_db->escape($_REQUEST['zone']);

        $kiw_zone = explode(":", $kiw_zone);

        $kiw_sql_search .= "AND zone = '{$kiw_zone[1]}'";

    } else {

        $kiw_sql_search .= " ";

    }


    if (isset($_REQUEST['project']) && !empty($_REQUEST['project'])) {

        $kiw_project = $kiw_db->escape($_REQUEST['project']);

        $kiw_project = explode(":", $kiw_project);

        $kiw_project_list = $kiw_db->query_first("SELECT * FROM kiwire_project WHERE name = '{$kiw_project[1]}'");

        $kiw_array = explode(",", $kiw_project_list['zone_list']);

        $kiw_sql_search .= "AND zone IN ('" . implode("','", $kiw_array) . "')";


    } else {

        $kiw_sql_search .= " ";

    }

    // end


    if ($kiw_sql_search == "") {
        $kiw_sql_search = "WHERE tenant_id = '{$_SESSION['tenant']}'";
    }

    # END FILTERING #

    $kiw_tables = get_by_date($kiw_start, $kiw_end, $report_data['timezone'], "kiwire_sessions");

    $report_data['search']      = $kiw_sql_search;
    $report_data['kiw_tables']  = $kiw_tables;
    $report_data['tenant_id']   = $_SESSION['tenant_id'];
    $report_data['filename']    = "login_record_{$_SESSION['tenant_id']}_" . date("Ymd") . "_" . time() . "_{$_SESSION['user_name']}";
    $report_data['header_data'] = array("LOGIN DATE/TIME", "LOGOUT DATE/TIME", "USERNAME", "MAC ADDRESS", "IP ADDRESS", "IPV6 ADDRESS", "ZONE", "NAS ID", "REASON", "TOTAL TIME", "AVERAGE SPEED", "TRAFFIC USED", "CLASS", "BRAND", "MODEL", "TENANT");

    //send to function running background

    $kiw_temp = curl_init();

    curl_setopt($kiw_temp, CURLOPT_URL, "http://127.0.0.1:9951");
    curl_setopt($kiw_temp, CURLOPT_POST, true);
    curl_setopt($kiw_temp, CURLOPT_POSTFIELDS, http_build_query($report_data));
    curl_setopt($kiw_temp, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($kiw_temp, CURLOPT_TIMEOUT, 5);
    curl_setopt($kiw_temp, CURLOPT_CONNECTTIMEOUT, 5);

    unset($report_data);

    curl_exec($kiw_temp);
    curl_close($kiw_temp);


    echo json_encode(array("status" => "completed"));
}


function get_by_date($kiw_start, $kiw_end, $kiw_timezone, $kiw_table_name)
{


    $kiw_table = [];


    $kiw_timezone = $kiw_timezone ?: "now";


    try {


        $kiw_start = new DateTime($kiw_start, new DateTimeZone($kiw_timezone));


        $kiw_end = date("Y-m-d H:i:s", strtotime($kiw_end . " +1 Day -1 Seconds"));

        $kiw_end = new DateTime($kiw_end, new DateTimeZone($kiw_timezone));
    } catch (Exception $e) {

        return false;
    }


    $kiw_interval = $kiw_start->diff($kiw_end)->format("%a");


    foreach (range($kiw_interval, 0) as $kiw_range) {


        $kiw_current_date = date("Ym", strtotime($kiw_end->format("Y-m-d H:i:s") . "-{$kiw_range} Day"));

        $kiw_current_date = $kiw_table_name . "_" . $kiw_current_date;


        if (!in_array($kiw_current_date, $kiw_table)) {

            $kiw_table[] = $kiw_current_date;
        }
    }


    return $kiw_table;
}


function get_datatable($kiw_db)
{


    $kiw_columns = [
        'id',
        'start_time',
        'stop_time',
        'username',
        'mac_address',
        'ip_address',
        'ipv6_address',
        'zone',
        'controller',
        'terminate_reason',
        'session_time',
        'avg_speed',
        '(quota_in + quota_out)',
        'profile',
        'class',
        'brand',
        'model',
        'tenant_id'
    ];


    $kiw_date_start  = $_REQUEST['startdate'];

    $kiw_date_end    = $_REQUEST['enddate'];


    $kiw_date_start = report_date_start($kiw_date_start, "30");

    $kiw_date_end = report_date_end($kiw_date_end, "1");


    $kiw_timezone = $_SESSION['timezone'];

    if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


    $kiw_type = $kiw_db->escape($_REQUEST['type']);

    if ($kiw_type == "login") $kiw_type = "start_time";
    else $kiw_type = "stop_time";


    $kiw_mac = $kiw_db->escape($_REQUEST['mac_address']);

    if (!empty($kiw_mac)) $kiw_mac = "AND mac_address = '{$kiw_mac}'";


    $kiw_username = $kiw_db->escape($_REQUEST['username']);

    if (!empty($kiw_username)) $kiw_username = "AND username = '{$kiw_username}'";


    $kiw_controller = $kiw_db->escape($_REQUEST['controller']);

    if (!empty($kiw_controller)) $kiw_controller = "AND controller = '{$kiw_controller}'";


    $kiw_ip = $kiw_db->escape($_REQUEST['ip_address']);

    if (!empty($kiw_ip)) $kiw_ip = "AND ip_address = '{$kiw_ip}'";


    $kiw_profile = $kiw_db->escape($_REQUEST['profile']);

    if (!empty($kiw_profile)) $kiw_profile = "AND profile = '{$kiw_profile}'";


    // filter by zone/project

    $kiw_zone = $kiw_db->escape($_REQUEST['zone']);


    if (!empty($kiw_zone)) {

        $kiw_zone = explode(":", $kiw_zone);

        $kiw_zone = "AND zone = '{$kiw_zone[1]}'";
    } else {

        $kiw_zone = '';
    }


    $kiw_project = $kiw_db->escape($_REQUEST['project']);


    if (!empty($kiw_project)) {

        $kiw_project = explode(":", $kiw_project);

        $kiw_project_list = $kiw_db->query_first("SELECT * FROM kiwire_project WHERE name = '{$kiw_project[1]}'");

        $kiw_array = explode(",", $kiw_project_list['zone_list']);

        $kiw_project = "AND zone IN ('" . implode("','", $kiw_array) . "')";
    } else {

        $kiw_project = '';
    }

    // end


    $kiw_row     = (int) $_REQUEST['start'];
    $kiw_length  = (int) $_REQUEST['length'];
    $kiw_search  = $_REQUEST['search']['value'];

    $kiw_order_direction = strtoupper($_REQUEST['order'][0]['dir']);


    $kiw_order = $_REQUEST['order'][0]['column'];

    $kiw_order = "ORDER BY {$kiw_columns[$kiw_order]} {$kiw_order_direction}";


    if (empty($kiw_row)) $kiw_row = 0;

    if (empty($kiw_length)) $kiw_length = 10;



    if (!empty($kiw_search)) {


        $row_search_x = "AND (";


        foreach ($kiw_columns as $columns) {

            $row_search_x .= "{$columns} LIKE '{$kiw_search}%' OR ";
        }


        $kiw_search = substr($row_search_x, 0, -4);

        $kiw_search = $kiw_search . ")";

        unset($columns);
        unset($row_search_x);
    }


    $kiw_columns_temp = "";


    foreach ($kiw_columns as $index => $name) {

        $kiw_columns_temp .= "{$name} AS '{$index}', ";
    }


    $kiw_columns = substr($kiw_columns_temp, 0, -2);

    unset($kiw_columns_temp);


    $kiw_tables = get_by_date($kiw_date_start, $kiw_date_end, "Asia/Kuala_Lumpur", "kiwire_sessions");


    if ($kiw_order_direction == "DESC") $kiw_tables = array_reverse($kiw_tables);


    $kiw_result['draw']             = $_REQUEST['draw'];
    $kiw_result['recordsTotal']     = 0;
    $kiw_result['recordsFiltered']  = 0;
    $kiw_result['data']             = array();


    $kiw_count = [];



    if ($_SESSION['access_level'] == "superuser") {


        $kiw_tenant = $kiw_db->escape($_REQUEST['tenant_id']);


        if (!empty($_SESSION['tenant_allowed'])) {


            $kiw_where = explode(",", $_SESSION['tenant_allowed']);


            if (!empty($kiw_tenant) && in_array($kiw_tenant, $kiw_where)) {

                $kiw_where = "AND tenant_id = '{$kiw_tenant}'";
            } else $kiw_where = "AND tenant_id IN ('" . implode("','", $kiw_where) . "')";
        } else {


            if (!empty($kiw_tenant)) {

                $kiw_where = "AND tenant_id = '{$kiw_tenant}'";
            } else $kiw_where = "AND tenant_id = '{$_SESSION['tenant_id']}'";
        }


        unset($kiw_tenant);
    } else $kiw_where = "AND tenant_id = '{$_SESSION['tenant_id']}'";



    // get the count of data

    foreach ($kiw_tables as $kiw_table) {

        // $kiw_temp = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM {$kiw_table} USE INDEX (ki_start_time,  ki_stop_time) WHERE ({$kiw_type} BETWEEN '{$kiw_date_start}' AND '{$kiw_date_end}') {$kiw_where} {$kiw_mac} {$kiw_ip} {$kiw_username} {$kiw_controller}");

        // $kiw_result['recordsTotal'] += $kiw_temp['kcount'];

        // $kiw_count[$kiw_table]['total'] = $kiw_temp['kcount'];

        $kiw_temp = $kiw_db->query_first("SELECT COUNT(id) AS kcount FROM {$kiw_table} USE INDEX (ki_start_time, ki_stop_time) WHERE ({$kiw_type} BETWEEN '{$kiw_date_start}' AND '{$kiw_date_end}') {$kiw_where} {$kiw_mac} {$kiw_ip} {$kiw_search} {$kiw_username} {$kiw_controller} {$kiw_profile} {$kiw_zone} {$kiw_project} LIMIT 1");


        $kiw_result['recordsFiltered'] += $kiw_temp['kcount'];

        $kiw_count[$kiw_table]['filtered'] = $kiw_temp['kcount'];
    }

    if($kiw_result['recordsFiltered'] > 8000){

        return;

    }

    $kiw_remaining = $kiw_length;

    $kiw_counter_all = 0;

    $kiw_counter_skip = $kiw_row;

    $kiw_first = true;


    // get the actual data

    foreach ($kiw_tables as $kiw_table) {


        if ($kiw_remaining > 0) {


            $kiw_counter_all += $kiw_count[$kiw_table]['filtered'];


            if ($kiw_counter_all >= $kiw_row) {


                if ($kiw_first == false) $kiw_counter_skip = 0;
                else $kiw_first = false;

                $kiw_temp = $kiw_db->fetch_array("SELECT SQL_CACHE {$kiw_columns} FROM {$kiw_table} USE INDEX (ki_start_time, ki_stop_time) WHERE ({$kiw_type} BETWEEN '{$kiw_date_start}' AND '{$kiw_date_end}') {$kiw_where} {$kiw_mac} {$kiw_ip} {$kiw_search} {$kiw_username} {$kiw_controller} {$kiw_profile} {$kiw_zone} {$kiw_project} {$kiw_order} LIMIT {$kiw_counter_skip},{$kiw_remaining}");


                if (is_array($kiw_temp)) {

                    $kiw_result['data'] = array_merge($kiw_result['data'], $kiw_temp);
                }


                $kiw_remaining -= count($kiw_temp);

                unset($kiw_temp);
            } else $kiw_counter_skip -= $kiw_count[$kiw_table]['filtered'];
        }
    }


    $kiw_metric = $_SESSION['metrics'];

    if ($kiw_metric == "Gb" || empty($kiw_metric)) $kiw_metric = 1024 * 1024 * 1024;
    else $kiw_metric = 1024 * 1024;


    for ($kiw_x = 0; $kiw_x < $kiw_length; $kiw_x++) {


        if (isset($kiw_result['data'][$kiw_x])) {


            $kiw_result['data'][$kiw_x]['0'] = ($kiw_row + $kiw_x) + 1;


            $kiw_result['data'][$kiw_x]['1'] = sync_tolocaltime($kiw_result['data'][$kiw_x]['1'], $kiw_timezone);

            $kiw_result['data'][$kiw_x]['2'] = sync_tolocaltime($kiw_result['data'][$kiw_x]['2'], $kiw_timezone);


            if ($kiw_result['data'][$kiw_x]['7'] == null) {

                $kiw_result['data'][$kiw_x]['7'] = "<td>NA</td>";
            }


            $kiw_result['data'][$kiw_x]['10'] = gmdate("H:i:s", $kiw_result['data'][$kiw_x]['10']);


            if ($_SESSION['metrics'] == "Gb") {

                $kiw_result['data'][$kiw_x]['11'] = number_format(round($kiw_result['data'][$kiw_x]['11'] / (1024 * 1024), 3), 3);
            } else $kiw_result['data'][$kiw_x]['11'] = number_format(round($kiw_result['data'][$kiw_x]['11'] / 1024, 3), 3);


            $kiw_result['data'][$kiw_x]['12'] = number_format(round($kiw_result['data'][$kiw_x]['12'] / $kiw_metric, 3), 3);
        }
    }


    echo json_encode($kiw_result);
}
