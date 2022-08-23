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

    case "get_by_date": get_datatable($kiw_db); break;
    
    default: echo "ERROR: Wrong implementation";

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


function get_datatable($kiw_db){


    $kiw_columns = [
        'id',
        'date_time',
        'severity',
        'tenant_id',
        'username',
        'source_user',
        'level',
        'source_ip',
        'service',
        'host',
        'vuln',
        'action',
    ];


    $kiw_date_start  = $_REQUEST['startdate'];

    $kiw_date_end    = $_REQUEST['enddate'];

    $kiw_date_start = report_date_start($kiw_date_start, "30");
    
    $kiw_date_end = report_date_end($kiw_date_end, "1");
    
    if ($_SESSION['access_level'] == "superuser"){


        $kiw_tenant = $kiw_db->escape($_REQUEST['tenant_id']);


        if (!empty($_SESSION['tenant_allowed'])){


            $kiw_where = explode(",", $_SESSION['tenant_allowed']);


            if (!empty($kiw_tenant) && in_array($kiw_tenant, $kiw_where)) {

                $kiw_where = " WHERE tenant_id = '{$kiw_tenant}'";

            } else $kiw_where = " WHERE tenant_id IN ('" . implode("','", $kiw_where) . "')";


        } else {


            if (!empty($kiw_tenant)){

                $kiw_where = " WHERE tenant_id = '{$kiw_tenant}'";

            } else $kiw_where = " WHERE tenant_id = '{$_SESSION['tenant_id']}'";
        


        }


        unset($kiw_tenant);


    } else $kiw_where = " WHERE tenant_id = '{$_SESSION['tenant_id']}'";

    
    
    $kiw_timezone = $_SESSION['timezone'];

    if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";

    if (!empty($kiw_db->escape($kiw_date_start)) && !empty($kiw_db->escape($kiw_date_end))) $kiw_where .= " AND (date_time BETWEEN '{$kiw_date_start}' AND '{$kiw_date_end}') ";

    if (!empty($kiw_db->escape($_REQUEST['username']))) $kiw_where .= " AND username = '{$kiw_db->escape($_REQUEST['username'])}' ";

    if (!empty($kiw_db->escape($_REQUEST['severity']))) $kiw_where .= "AND severity = '{$kiw_db->escape($_REQUEST['severity'])}'";

    if (!empty($kiw_db->escape($_REQUEST['ip_address']))) $kiw_where .= "AND ip_address = '{$kiw_db->escape($_REQUEST['ip_address'])}'";


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


    $kiw_result['draw']             = $_REQUEST['draw'];
    $kiw_result['recordsTotal']     = 0;
    $kiw_result['recordsFiltered']  = 0;
    $kiw_result['data']             = array();


    $kiw_count = [];




    // get the count of data
    $kiw_temp = $kiw_db->query_first("SELECT COUNT(id) AS kcount FROM kiwire_paloalto {$kiw_where}  {$kiw_search} ");
    $kiw_result['recordsFiltered'] = $kiw_temp['kcount'];


    $kiw_remaining      = $kiw_length;
    $kiw_counter_all    = 0;
    $kiw_counter_skip   = $kiw_row;
    $kiw_first          = true;


    // get the actual data
    if ($kiw_remaining > 0) {


        $kiw_counter_all = $kiw_result['recordsFiltered'];


        if ($kiw_counter_all >= $kiw_row) {


            if ($kiw_first == false) $kiw_counter_skip = 0;
            else $kiw_first = false;

            $kiw_result['data'] = $kiw_db->fetch_array("SELECT SQL_CACHE {$kiw_columns} FROM kiwire_paloalto {$kiw_where} {$kiw_search} {$kiw_order} LIMIT {$kiw_counter_skip},{$kiw_remaining}");



            $kiw_remaining -= count($kiw_temp);

            unset($kiw_temp);


        } else $kiw_counter_skip -= $kiw_result['recordsFiltered'];


    }



    for ($kiw_x = 0; $kiw_x < $kiw_length; $kiw_x++){


        if (isset($kiw_result['data'][$kiw_x])) {


            $kiw_result['data'][$kiw_x]['0'] = ($kiw_row + $kiw_x) + 1;


            $kiw_result['data'][$kiw_x]['1'] = sync_tolocaltime($kiw_result['data'][$kiw_x]['1'], $kiw_timezone);

        }


    }


    echo json_encode($kiw_result);


}