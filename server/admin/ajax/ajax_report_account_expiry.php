<?php

$kiw['module'] = "Report -> Accounts -> Account Expiry";
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

$action = $_REQUEST['action'];

switch ($action) {

    case "get_by_date": get_by_date(); break;
    default: echo "ERROR: Wrong implementation";

}


function get_by_date()
{

    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        $startdate = report_date_start($_REQUEST['startdate'], 30);
        $enddate = report_date_end($_REQUEST['enddate'], 1);

        $kiw_timezone = $_SESSION['timezone'];

        $kiw_timezone = empty($kiw_timezone) ?: "Asia/Kuala_Lumpur";


        $kiw_columns = array(
            array( 'db' => 'username',      'dt' => 1 ),
            array( 'db' => 'profile_subs',  'dt' => 2 ),
            array( 'db' => 'price',         'dt' => 3 ),
            array( 'db' => 'date_create',   'dt' => 4 ),
            array( 'db' => 'date_expiry',   'dt' => 5 )
        );


        $kiw_sqlinfo = array('user' => SYNC_DB1_USER, 'pass' => SYNC_DB1_PASSWORD, 'db'   => SYNC_DB1_DATABASE, 'host' => SYNC_DB1_HOST, 'port' => SYNC_DB1_PORT);


        $kiw_data = SSP::complex( $_GET, $kiw_sqlinfo, "kiwire_account_auth", "id", $kiw_columns, null, "(date_expiry BETWEEN '{$startdate}' AND '{$enddate}') AND tenant_id = '{$_SESSION['tenant_id']}'");
        

        $kiw_start = $_GET['start'] + 1;

        $kiw_end = count($kiw_data['data']) + $kiw_start;


        for ($x = $kiw_start; $x < $kiw_end; $x++){

            $kiw_data['data'][$x - $kiw_start][0] = $x;

            $kiw_data['data'][$x - $kiw_start][4] = sync_tolocaltime($kiw_data['data'][$x - $kiw_start][4], $kiw_timezone);

            $kiw_data['data'][$x - $kiw_start][5] = sync_tolocaltime($kiw_data['data'][$x - $kiw_start][5], $kiw_timezone);

        }


        echo json_encode($kiw_data);


    }


}
