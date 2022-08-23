<?php

$kiw['module'] = "Device -> Monitoring -> Check Logs";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_connection.php";

require_once "../includes/include_general.php";

require_once "../../libs/ssp.class.php";


if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));
}


$action = $_REQUEST['action'];

switch ($action) {

    case "get_all": get_data(); break;

    default: echo "ERROR: Wrong implementation";
}



function get_data()
{

    global $kiw_db, $tenant_id;


    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        $kiw_timezone = $_SESSION['timezone'];

        $kiw_timezone = empty($kiw_timezone) ?: "Asia/Kuala_Lumpur";


        $kiw_columns = array(
            array('db' => 'unique_id', 'dt' => 1),
            array('db' => 'status', 'dt' => 2),
            array('db' => 'reason', 'dt' => 3),
            array('db' => 'updated_date', 'dt' => 4),
            array('db' => 'system_name', 'dt' => 5),
            array('db' => 'cpu_load', 'dt' => 6),
            array('db' => 'memory_used', 'dt' => 7),
            array('db' => 'disk_used', 'dt' => 8)

        );


        $kiw_sqlinfo = array('user' => SYNC_DB1_USER, 'pass' => SYNC_DB1_PASSWORD, 'db' => SYNC_DB1_DATABASE, 'host' => SYNC_DB1_HOST, 'port' => SYNC_DB1_PORT);


        $kiw_data = SSP::complex($_GET, $kiw_sqlinfo, "kiwire_nms_log", "id", $kiw_columns, null, "tenant_id = '{$_SESSION['tenant_id']}'");


        $kiw_start = $_GET['start'] + 1;

        $kiw_end = count($kiw_data['data']) + $kiw_start;


        for ($x = $kiw_start; $x < $kiw_end; $x++) {


            $kiw_data['data'][$x - $kiw_start][0] = $x;

            $kiw_data['data'][$x - $kiw_start][4] = sync_tolocaltime($kiw_data['data'][$x - $kiw_start][4], $kiw_timezone);


        }


        echo json_encode($kiw_data);


    }


}