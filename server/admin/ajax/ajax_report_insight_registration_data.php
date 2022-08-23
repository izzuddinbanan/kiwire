<?php

$kiw['module'] = "Report -> Insight -> Sign-Up Data";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_connection.php";
require_once "../includes/include_report.php";
require_once "../includes/include_general.php";

require_once "../../libs/ssp.class.php";


if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}


if (in_array($_SESSION['permission'], array("r", "rw"))) {


    $startdate = report_date_start($_REQUEST['start_date'], 30);

    $enddate = report_date_end($_REQUEST['end_date'], 1);


    $kiw_timezone = $_SESSION['timezone'];

    $kiw_timezone = empty($kiw_timezone) ?: "Asia/Kuala_Lumpur";


    $kiw_columns = array();


    foreach (explode(",", $_REQUEST['columns_registered']) as $kiw_index => $kiw_column){

        if (!empty($kiw_column)) {

            $kiw_columns[] = array('db' => $kiw_db->escape($kiw_column), 'dt' => $kiw_index);

        }

    }


    if (!empty($kiw_columns)) {


        $kiw_sqlinfo = array('user' => SYNC_DB1_USER, 'pass' => SYNC_DB1_PASSWORD, 'db' => SYNC_DB1_DATABASE, 'host' => SYNC_DB1_HOST, 'port' => SYNC_DB1_PORT);


        $kiw_data = SSP::complex($_GET, $kiw_sqlinfo, "kiwire_account_info", "id", $kiw_columns, null, "(updated_date BETWEEN '{$startdate}' AND '{$enddate}') AND source = 'system' AND tenant_id = '{$_SESSION['tenant_id']}'");


        $kiw_start = $_GET['start'] + 1;

        $kiw_end = count($kiw_data['data']) + $kiw_start;


        for ($x = $kiw_start; $x < $kiw_end; $x++) {

            $kiw_data['data'][$x - $kiw_start][0] = $x;

            $kiw_data['data'][$x - $kiw_start][1] = sync_tolocaltime($kiw_data['data'][$x - $kiw_start][1], $kiw_timezone);

            
        }


    }


    echo json_encode($kiw_data);


}
