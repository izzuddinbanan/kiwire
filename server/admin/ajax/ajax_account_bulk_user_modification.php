<?php

$kiw['module'] = "Account -> Bulk User Modification";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_general.php";
require_once "../includes/include_connection.php";

require_once "../../libs/ssp.class.php";

require_once "../../user/includes/include_radius.php";


if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));
    
}

$action = $_REQUEST['action'];

switch ($action) {
    case "get_all": get_all($kiw_db); break;
    case "extend": extend_user($kiw_db); break;
    case "change": change_profile($kiw_db); break;
    case "reset": reset_account($kiw_db); break;
    case "export": export_user($kiw_db); break;
    case "delete": delete_user($kiw_db, $kiw_cache); break;
    default: echo "ERROR: Wrong implementation";
}





function get_all($kiw_db){


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_timezone = $_SESSION['timezone'];

        $kiw_timezone = empty($kiw_timezone) ?: "Asia/Kuala_Lumpur";


        $kiw_columns = array(
            array( 'db' => 'username',      'dt' => 1 ),
            array( 'db' => 'profile_subs',  'dt' => 2 ),
            array( 'db' => 'date_expiry',   'dt' => 3 )
        );


        $kiw_sqlinfo = array('user' => SYNC_DB1_USER, 'pass' => SYNC_DB1_PASSWORD, 'db'   => SYNC_DB1_DATABASE, 'host' => SYNC_DB1_HOST, 'port' => SYNC_DB1_PORT);


        $kiw_data = SSP::complex( $_GET, $kiw_sqlinfo, "kiwire_account_auth", "id", $kiw_columns, null, "(ktype != 'voucher' AND ktype != 'simcard') AND tenant_id = '{$_SESSION['tenant_id']}'");


        $kiw_start = $_GET['start'] + 1;

        $kiw_end = count($kiw_data['data']) + $kiw_start;


        for ($x = $kiw_start; $x < $kiw_end; $x++){

            $kiw_data['data'][$x - $kiw_start][0] = $x;

            $kiw_data['data'][$x - $kiw_start][3] = sync_tolocaltime($kiw_data['data'][$x - $kiw_start][3], $kiw_timezone);

        }


        echo json_encode($kiw_data);



    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}



function extend_user($kiw_db){

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));


        $kiw_date = date("Y-m-d H:i:s", strtotime($_REQUEST['expiry']));

        $kiw_users = $_REQUEST['username'];


        if (count($kiw_users)) {


            foreach ($kiw_users as $kiw_user) {

                $kiw_data[] = $kiw_db->escape($kiw_user);

            }

            $kiw_data = implode("','", $kiw_data);


            $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), date_expiry = '{$kiw_date}' WHERE username IN ('{$kiw_data}') AND tenant_id = '{$_SESSION['tenant_id']}'");


            sync_logger("{$_SESSION['user_name']} extend account " . implode(",", $kiw_users) . " to {$kiw_date}", $_SESSION['tenant_id']);

            echo json_encode(array("status" => "success", "message" => "Username selected has been extended.", "data" => ""));


        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Missing account to be action", "data" => null));

        }


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}




function change_profile($kiw_db){

    if (in_array($_SESSION['permission'], array("w", "rw"))) {
        
        csrf($kiw_db->escape($_REQUEST['token']));


        $kiw_profile = $_REQUEST['profile'];

        $kiw_users = $_REQUEST['username'];


        if (count($kiw_users) > 0) {


            foreach ($kiw_users as $kiw_user) {

                $kiw_data[] = $kiw_db->escape($kiw_user);

            }

            $kiw_data = implode("','", $kiw_data);


            $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), profile_subs = '{$kiw_profile}', profile_curr = '{$kiw_profile}' WHERE username IN ('{$kiw_data}') AND tenant_id = '{$_SESSION['tenant_id']}'");


            sync_logger("{$_SESSION['user_name']} changed profile for account " . implode(",", $kiw_users) . " to {$kiw_profile}", $_SESSION['tenant_id']);

            echo json_encode(array("status" => "success", "message" => "Username selected has been extended.", "data" => ""));


        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Missing account to be action", "data" => null));

        }


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}





function reset_account($kiw_db){

    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        $kiw_users = $_REQUEST['username'];


        if (count($kiw_users) > 0) {


            foreach ($kiw_users as $kiw_user) {

                $kiw_data[] = $kiw_db->escape($kiw_user);

            }


            $kiw_data = implode("','", $kiw_data);


            $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), profile_curr = profile_subs, quota_out = 0, quota_in = 0, session_time = 0 WHERE username IN ('{$kiw_data}') AND tenant_id = '{$_SESSION['tenant_id']}'");


            sync_logger("{$_SESSION['user_name']} reset account " . implode(",", $kiw_users), $_SESSION['tenant_id']);

            echo json_encode(array("status" => "success", "message" => "Accounts has been reset", "data" => ""));


        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Missing account to be action", "data" => null));

        }


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}





function export_user($kiw_db){

    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        $kiw_users = $_REQUEST['username'];


        if (count($kiw_users) > 0) {


            foreach ($kiw_users as $kiw_user) {

                $kiw_data[] = $kiw_db->escape($kiw_user);

            }


            $kiw_data = implode("','", $kiw_data);


            $kiw_db->query("SELECT * FROM kiwire_account_auth WHERE username IN ('{$kiw_data}') AND tenant_id = '{$_SESSION['tenant_id']}'");


            sync_logger("{$_SESSION['user_name']} export account " . implode(",", $kiw_users), $_SESSION['tenant_id']);

            echo json_encode(array("status" => "success", "message" => "/PATH/TO/DOWNLOAD", "data" => ""));


        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Missing account to be action", "data" => null));

        }


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}


function delete_user($kiw_db, $kiw_cache){


    if (in_array($_SESSION['permission'], array("w", "rw"))) {

       
        csrf($kiw_db->escape($_REQUEST['token']));


        $kiw_users = $_REQUEST['username'];


        if (count($kiw_users) > 0) {


            foreach ($kiw_users as $kiw_user) {

                $kiw_data[] = $kiw_db->escape($kiw_user);

            }


            $kiw_data = implode("','", $kiw_data);

            $kiw_db->query("DELETE FROM kiwire_account_auth WHERE username IN ('{$kiw_data}') AND tenant_id = '{$_SESSION['tenant_id']}'");
            $kiw_db->query("DELETE FROM kiwire_account_info WHERE username IN ('{$kiw_data}') AND tenant_id = '{$_SESSION['tenant_id']}'");


            $kiw_temps = $kiw_db->fetch_array("SELECT DISTINCT(username) FROM kiwire_active_session WHERE username IN ('{$kiw_data}') AND tenant_id = '{$_SESSION['tenant_id']}'");

            foreach ($kiw_temps as $kiw_temp){

                disconnect_user($kiw_db, $kiw_cache, $_SESSION['tenant_id'], $kiw_temp['username']);

            }


            sync_logger("{$_SESSION['user_name']} deleted account " . implode(",", $kiw_users), $_SESSION['tenant_id']);

            echo json_encode(array("status" => "success", "message" => "Accounts has been deleted", "data" => ""));

        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Missing account to be action", "data" => null));

        }


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}
