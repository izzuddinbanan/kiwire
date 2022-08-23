<?php

$kiw['module'] = "Report -> Who is Online";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_connection.php";
require_once "../includes/include_general.php";

require_once "../../user/includes/include_radius.php";
require_once "../../libs/ssp.class.php";


if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}


$action = $_REQUEST['action'];


switch ($action) {

    case "disconnect": disconnect($kiw_db, $kiw_cache); break;
    case "get_all": get_data(); break;
    case "coa": coa($kiw_db, $kiw_cache); break;
    default: echo "ERROR: Wrong implementation";

}

function get_data()
{

    global $kiw_db;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_timezone = $_SESSION['timezone'];

        $kiw_timezone = empty($kiw_timezone) ? "Asia/Kuala_Lumpur" : $kiw_timezone;


        $kiw_columns = array(
            array( 'db' => 'start_time',    'dt' => 2 ),
            array( 'db' => 'username',      'dt' => 3 ),
            array( 'db' => 'mac_address',   'dt' => 4 ),
            array( 'db' => 'ip_address',    'dt' => 5 ),
            array( 'db' => 'ipv6_address',  'dt' => 6 ),
            array( 'db' => 'quota_in',      'dt' => 7 ),
            array( 'db' => 'quota_out',     'dt' => 8 ),
            array( 'db' => 'zone',          'dt' => 9 ),
            array( 'db' => 'controller',    'dt' => 10 ),
            array( 'db' => 'avg_speed',     'dt' => 11 ),
            array( 'db' => 'class',         'dt' => 12 ),
            array( 'db' => 'brand',         'dt' => 13 ),
            array( 'db' => 'model',         'dt' => 14 ),
            array( 'db' => 'system',        'dt' => 15 ),
            array( 'db' => 'tenant_id',     'dt' => 16 ),
            array( 'db' => 'profile',     'dt' => 17 )
        );


        $kiw_where = "tenant_id = '{$_SESSION['tenant_id']}'";

       ############ Previous Code ##############################

        // if ($_SESSION['access_level'] == "superuser"){


        //     if (!empty($_SESSION['tenant_allowed'])){


        //         $kiw_where = explode(",", $_SESSION['tenant_allowed']);

        //         $kiw_where = "tenant_id IN ('" . implode("','", $kiw_where) . "')";


        //     } else $kiw_where = "";


        // } else $kiw_where = "tenant_id = '{$_SESSION['tenant_id']}'";

       ##############################################################




        $kiw_sqlinfo = array('user' => SYNC_DB1_USER, 'pass' => SYNC_DB1_PASSWORD, 'db'   => SYNC_DB1_DATABASE, 'host' => SYNC_DB1_HOST, 'port' => SYNC_DB1_PORT);


        $kiw_data = SSP::complex( $_GET, $kiw_sqlinfo, "kiwire_active_session", "id", $kiw_columns, $kiw_where);


        $kiw_start = $_GET['start'] + 1;

        $kiw_end = count($kiw_data['data']) + $kiw_start;


        for ($x = $kiw_start; $x < $kiw_end; $x++){


            $kiw_data['data'][$x - $kiw_start][0] = $x;


            if (empty($kiw_data['data'][$x - $kiw_start]['6'])){

                $kiw_data['data'][$x - $kiw_start]['6'] = "NA";

            }
            

            if ($_SESSION['metrics'] == "Gb"){

                $kiw_data['data'][$x - $kiw_start][7] = round($kiw_data['data'][$x - $kiw_start][7] / (1024 * 1024 * 1024), 3);
                $kiw_data['data'][$x - $kiw_start][8] = round($kiw_data['data'][$x - $kiw_start][8] / (1024 * 1024 * 1024), 3);

                $kiw_data['data'][$x - $kiw_start][11] = round($kiw_data['data'][$x - $kiw_start][11] / (1024 * 1024 * 1024), 3);

            } else {

                $kiw_data['data'][$x - $kiw_start][7] = round($kiw_data['data'][$x - $kiw_start][7] / (1024 * 1024), 3);
                $kiw_data['data'][$x - $kiw_start][8] = round($kiw_data['data'][$x - $kiw_start][8] / (1024 * 1024), 3);

                $kiw_data['data'][$x - $kiw_start][11] = round($kiw_data['data'][$x - $kiw_start][11] / 1024, 3);

            }

            $kiw_data['data'][$x - $kiw_start][2] = sync_tolocaltime($kiw_data['data'][$x - $kiw_start][2], $kiw_timezone);


        }


        echo json_encode($kiw_data);

    }


}


function disconnect($kiw_db, $kiw_cache){


    $kiw_device = $kiw_db->escape($_REQUEST['device']);

    $kiw_tenant = $kiw_db->escape($_REQUEST['tenant']);


    if (empty($kiw_tenant)) $kiw_tenant = $_SESSION['tenant_id'];


    if (!empty($kiw_device) && !empty($kiw_tenant)){

        if (in_array($_SESSION['permission'], array("w", "rw"))) {


            if (disconnect_device($kiw_db, $kiw_cache, $kiw_tenant, $kiw_device) == true) {

                echo json_encode(array("status" => "success", "message" => "Device has been disconnected.", "data" => null));

            } else {

                echo json_encode(array("status" => "failed", "message" => "We unable to disconnect the device.", "data" => null));

            }


        } else {

            echo json_encode(array("status" => "error", "message" => "You are not allowed to access this module", "data" => null));

        }


    }


}

function coa($kiw_db, $kiw_cache){

    $kiw_username       = $kiw_db->escape($_REQUEST['username']);
    $kiw_profile_name   = $kiw_db->escape($_REQUEST['profile']);
    $kiw_tenant         = $kiw_db->escape($_REQUEST['tenant']);

    if (empty($kiw_tenant)) $kiw_tenant = $_SESSION['tenant_id'];

    if (!empty($kiw_username) && !empty($kiw_profile_name) && !empty($kiw_tenant)){

        if (in_array($_SESSION['permission'], array("w", "rw"))) {

            coa_user($kiw_db, $kiw_cache, $kiw_tenant, $kiw_username, $kiw_profile_name);

            echo json_encode(array("status" => "success", "message" => "Profile has been updated.", "data" => null));

        } else {

            echo json_encode(array("status" => "error", "message" => "You are not allowed to access this module", "data" => null));
            
        }
        
        
    }
    else{

        echo json_encode(array("status" => "error", "message" => "Invalid data", "data" => null));

    }

    

}