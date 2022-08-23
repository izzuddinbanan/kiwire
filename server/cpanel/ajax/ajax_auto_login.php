<?php


header("Content-Type: application/json");

require_once "../includes/include_general.php";
require_once "../includes/include_session.php";

require_once "../../libs/ssp.class.php";


global $kiw_db;


$action = $_REQUEST['action'];

switch ($action) {

    case "get_all": get_data(); break;
    case "delete": delete(); break;
    default: echo "ERROR: Wrong implementation";
}



function get_data() {


    $kiw_username = $_SESSION['cpanel']['username'];

    $kiw_tenant   = $_SESSION['cpanel']['tenant_id'];

    $kiw_timezone = $_SESSION['timezone'];

    if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";

    $kiw_columns = array(
        array( 'db' => 'id',            'dt' => 8 ),
        array( 'db' => 'mac_address',   'dt' => 2 ),
        array( 'db' => 'last_auto',     'dt' => 3 ),
        array( 'db' => 'details',       'dt' => 4 ),
        array( 'db' => 'details',       'dt' => 5 ),
        array( 'db' => 'details',       'dt' => 6 ),
    );

    $kiw_where = "last_account = '{$kiw_username}' AND tenant_id = '{$kiw_tenant}'";

    $kiw_sqlinfo = array('user' => SYNC_DB1_USER, 'pass' => SYNC_DB1_PASSWORD, 'db'   => SYNC_DB1_DATABASE, 'host' => SYNC_DB1_HOST, 'port' => SYNC_DB1_PORT);


    $kiw_data = SSP::complex( $_GET, $kiw_sqlinfo, "kiwire_device_history", "id", $kiw_columns, $kiw_where);

    
    $kiw_start = $_GET['start'] + 1;

    $kiw_end = count($kiw_data['data']) + $kiw_start;


    for ($x = $kiw_start; $x < $kiw_end; $x++){


        $kiw_data['data'][$x - $kiw_start][0] = $x;

        
        $details = json_decode($kiw_data['data'][$x - $kiw_start][4], true);

        $kiw_data['data'][$x - $kiw_start][3] = sync_tolocaltime($kiw_data['data'][$x - $kiw_start][3], $kiw_timezone);
        $kiw_data['data'][$x - $kiw_start][4] = $details["system"];
        $kiw_data['data'][$x - $kiw_start][5] = $details["class"];
        $kiw_data['data'][$x - $kiw_start][6] = $details["brand"];
        $kiw_data['data'][$x - $kiw_start][7] = $details["model"];


    }


    echo json_encode($kiw_data);

}


function delete() {


    global $kiw_db, $kiw_tenant;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        $id = $kiw_db->escape($_POST['id']);

        if (!empty($id)) {


            $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_device_history WHERE id ='{$id}' AND tenant_id = '{$kiw_tenant}'");


            if (!empty($kiw_temp)) {


                $kiw_db->query("DELETE FROM kiwire_device_history WHERE id = '{$id}' AND tenant_id = '{$kiw_tenant}'");


                sync_logger("{$_SESSION['user_name']} deleted kiwire_device_history {$kiw_temp['mac_address']}", $kiw_tenant);

                echo json_encode(array("status" => "success", "message" => "SUCCESS: Device {$kiw_temp['mac_address']} deleted from auto login.", "data" => null));


            } else {


                echo json_encode(array("status" => "error", "message" => "ERROR: Device not found.", "data" => null));


            }


        }


    } else {


        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));


    }



}
