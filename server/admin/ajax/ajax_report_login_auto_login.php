<?php

$kiw['module'] = "Report -> Auto Login";
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
    case "delete": delete(); break;
    default: echo "ERROR: Wrong implementation";
}


function get_data() {

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        $kiw_timezone = $_SESSION['timezone'];

        $kiw_timezone = empty($kiw_timezone) ? "Asia/Kuala_Lumpur" : $kiw_timezone;


        $kiw_columns = array(
            array( 'db' => 'id',    'dt' => 8 ),
            array( 'db' => 'mac_address',    'dt' => 2 ),
            array( 'db' => 'last_auto',      'dt' => 3 ),
            array( 'db' => 'details',   'dt' => 4 ),
            array( 'db' => 'details',   'dt' => 5 ),
            array( 'db' => 'details',   'dt' => 6 ),
        );


        $kiw_where = "tenant_id = '{$_SESSION['tenant_id']}'";

        $kiw_sqlinfo = array('user' => SYNC_DB1_USER, 'pass' => SYNC_DB1_PASSWORD, 'db'   => SYNC_DB1_DATABASE, 'host' => SYNC_DB1_HOST, 'port' => SYNC_DB1_PORT);



        $kiw_data = SSP::complex( $_GET, $kiw_sqlinfo, "kiwire_device_history", "id", $kiw_columns, $kiw_where);


        $kiw_start = $_GET['start'] + 1;

        $kiw_end = count($kiw_data['data']) + $kiw_start;


        for ($x = $kiw_start; $x < $kiw_end; $x++){


            $kiw_data['data'][$x - $kiw_start][0] = $x;


            // if (empty($kiw_data['data'][$x - $kiw_start]['6'])){

            //     $kiw_data['data'][$x - $kiw_start]['6'] = "NA";

            // }
            
            $details = json_decode($kiw_data['data'][$x - $kiw_start][4], true);

            $kiw_data['data'][$x - $kiw_start][3] = sync_tolocaltime($kiw_data['data'][$x - $kiw_start][3], $kiw_timezone);
            $kiw_data['data'][$x - $kiw_start][4] = $details["system"];
            $kiw_data['data'][$x - $kiw_start][5] = $details["class"];
            $kiw_data['data'][$x - $kiw_start][6] = $details["brand"];
            $kiw_data['data'][$x - $kiw_start][7] = $details["model"];
            // $kiw_data['data'][$x - $kiw_start][7] = $kiw_data['data'][$x - $kiw_start][2];


        }


        echo json_encode($kiw_data);


    }



}



function delete()
{
    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        $id = $kiw_db->escape($_POST['id']);

        if (!empty($id)) {


            $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_device_history WHERE id ='{$id}' AND tenant_id = '{$tenant_id}'");


            if (!empty($kiw_temp)) {


                $kiw_db->query("DELETE FROM kiwire_device_history WHERE id = '{$id}' AND tenant_id = '{$tenant_id}'");


                sync_logger("{$_SESSION['user_name']} deleted kiwire_device_history {$kiw_temp['mac_address']}", $_SESSION['tenant_id']);

                echo json_encode(array("status" => "success", "message" => "SUCCESS: Device {$kiw_temp['mac_address']} deleted from auto login.", "data" => null));


            } else {


                echo json_encode(array("status" => "error", "message" => "ERROR: Device not found.", "data" => null));


            }


        }


    } else {


        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));


    }


}









