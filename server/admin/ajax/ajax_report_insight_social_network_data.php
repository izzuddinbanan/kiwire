<?php

$kiw['module'] = "Report -> Insight -> Social Network Data";
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

    case "get_all":
        get_data();
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

    $report_data['timezone'] = $_SESSION['timezone'];

    if(empty($report_data['timezone'])) $report_data['timezone'] = "Asia/Kuala_Lumpur";

    $report_data['columns'] = [
        "CONVERT_TZ(updated_date, 'UTC', '{$report_data['timezone']}') AS updated_date",
        'username',
        'fullname',
        'gender',
        'age_group',
        'location',
        'email_address',
        'source',
        'birthday'
    ];

    # START FILTERING #
    $kiw_start = report_date_start($_REQUEST['startdate'], 30);

    $kiw_end = report_date_end($_REQUEST['enddate'], 1);

    $report_data['search']      = "(updated_date BETWEEN '{$kiw_start}' AND '{$kiw_end}') AND source != 'system' AND tenant_id = '{$_SESSION['tenant_id']}'";
    $report_data['kiw_tables']  = array("kiwire_account_info");
    $report_data['tenant_id']   = $_SESSION['tenant_id'];
    $report_data['filename']    = "social_network_data_{$_SESSION['tenant_id']}_" . date("Ymd") ."_". time(). "_{$_SESSION['user_name']}";
    $report_data['header_data'] = array("updated_date", 'username','fullname', 'gender','age_group','location','email_address','source','birthday');

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


function get_data()
{

    global $kiw_db, $tenant_id;


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $startdate = report_date_start($_REQUEST['startdate'], 30);
        $enddate = report_date_end($_REQUEST['enddate'], 1);

        $kiw_timezone = $_SESSION['timezone'];

        $kiw_timezone = empty($kiw_timezone) ?: "Asia/Kuala_Lumpur";


        $kiw_columns = array(
            array('db' => 'updated_date', 'dt' => 1),
            array('db' => 'username', 'dt' => 2),
            array('db' => 'fullname', 'dt' => 3),
            array('db' => 'gender', 'dt' => 4),
            array('db' => 'age_group', 'dt' => 5),
            array('db' => 'location', 'dt' => 6),
            array('db' => 'email_address', 'dt' => 7),
            array('db' => 'source', 'dt' => 8),
            array('db' => 'birthday', 'dt' => 9),

        );


        $kiw_sqlinfo = array('user' => SYNC_DB1_USER, 'pass' => SYNC_DB1_PASSWORD, 'db' => SYNC_DB1_DATABASE, 'host' => SYNC_DB1_HOST, 'port' => SYNC_DB1_PORT);


        $kiw_data = SSP::complex($_GET, $kiw_sqlinfo, "kiwire_account_info", "id", $kiw_columns, null, "(updated_date BETWEEN '{$startdate}' AND '{$enddate}') AND source != 'system' AND tenant_id = '{$_SESSION['tenant_id']}'");


        $kiw_start = $_GET['start'] + 1;

        $kiw_end = count($kiw_data['data']) + $kiw_start;


        for ($x = $kiw_start; $x < $kiw_end; $x++) {


            $kiw_data['data'][$x - $kiw_start][0] = $x;

            $kiw_data['data'][$x - $kiw_start][1] = sync_tolocaltime($kiw_data['data'][$x - $kiw_start][1], $kiw_timezone);


        }


        echo json_encode($kiw_data);


    }


}

