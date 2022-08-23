<?php

$kiw['module'] = "Account -> Topup Code";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_general.php";
require_once "../includes/include_connection.php";

if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));
}

$action = $_REQUEST['action'];

switch ($action) {

    case "get_all":get_all();break;
    case "get_update":get_update(); break;
    case "create":create();break;
    case "delete": delete();break;
    case "edit_single_data":edit_single_data();break;
    case "reset_code":reset_code();break;
    case "statistics":statistics();break;
    case "history":history();break;


    default:
        echo "ERROR: Wrong implementation";

}


function get_all()
{
    global $kiw_db;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        $kiw_codes = $kiw_db->fetch_array("SELECT * FROM kiwire_topup_code WHERE tenant_id = '{$_SESSION['tenant_id']}'");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_codes));

    }else{

        echo json_encode(array("status" => "failed", "message" => "You are not allow to this module", "data" => ""));
    
    }


}

function get_update()
{

    global $kiw_db, $tenant_id;

    $id = $kiw_db->escape($_REQUEST['id']);


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_code = $kiw_db->query_first("SELECT * FROM kiwire_topup_code WHERE id = '{$id}' AND tenant_id = '{$tenant_id}' LIMIT 1");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_code));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}


function create()
{


    global $kiw_db;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $kiw_total_code  = (int)$_REQUEST['ncode'];
        $kiw_code_length = (int)$_REQUEST['clength'];

        $kiw_date_expiry = date("Y-m-d H:i:s", strtotime($_REQUEST['date_expiry']));
        $kiw_remark      = $kiw_db->sanitize($_REQUEST['remark']);

        $kiw_plan        = $kiw_db->sanitize($_REQUEST['plan']);
        $kiw_creator     = $_SESSION['user_name'];

        $kiw_prefix      = $kiw_db->sanitize($_REQUEST['prefix']);


        if (isset($_REQUEST['tquota']) && !empty($_REQUEST['tquota'])) {

            $kiw_add['quota'] = (int)$_REQUEST['tquota'] * (pow(2, 20));

        } else $kiw_add['quota'] = 0;


        if (isset($_REQUEST['minutes']) && !empty($_REQUEST['minutes'])) {

            $kiw_add['minutes'] = (int)$_REQUEST['minutes'] * 60;

        } else $kiw_add['minutes'] = 0;


        if ($kiw_total_code > 0 && $kiw_code_length > 0){


            $random_id = random_string_id($kiw_total_code, 'y');

            $bulk_id = "TP" . $random_id;


            for ($kiw_x = 0; $kiw_x < $kiw_total_code; $kiw_x++){


                while (true) {


                    $kiw_code_gen = random_string_id($kiw_code_length, 'y');

                    //concatenate topup prefix with random generated code
                    $kiw_code = $kiw_prefix . $kiw_code_gen;

                    $kiw_test = $kiw_db->query_first("SELECT COUNT(*) AS ccount FROM kiwire_topup_code WHERE tenant_id = '{$_SESSION['tenant_id']}' AND code = '{$kiw_code}'");


                    if ($kiw_test['ccount'] == 0) {

                        $kiw_db->query("INSERT INTO kiwire_topup_code(id, price, updated_date, tenant_id, creator, code, status, username, plan_name, date_create, date_activate, date_expiry, bulk_id, quota, time, remark) VALUE (NULL, '{$_REQUEST['price']}' ,NOW(), '{$_SESSION['tenant_id']}', '{$kiw_creator}', '{$kiw_code}', 'n', NULL, '{$kiw_plan}', NOW(), NULL, '{$kiw_date_expiry}', '{$bulk_id}', '{$kiw_add['quota']}', '{$kiw_add['minutes']}', '{$kiw_remark}')");

                        break;

                    }


                }


            }
            
            sync_logger("{$_SESSION['user_name']} create topup code", $_SESSION['tenant_id']);

            echo json_encode(array("status" => "success", "message" => "SUCCESS: New topup code generated", "data" => null));


        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please provide number of code to be generated", "data" => null));

        }


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}





function delete()
{


    global $kiw_db, $tenant_id;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $id = $kiw_db->escape($_POST['id']);

        if (!empty($id)) {


            $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_topup_code WHERE id ='{$id}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");


            $kiw_db->query("DELETE FROM kiwire_topup_code WHERE tenant_id = '{$_SESSION['tenant_id']}' AND id = '{$id}' LIMIT 1");


            sync_logger("{$_SESSION['user_name']} deleted topup code {$kiw_temp['code']}", $_SESSION['tenant_id']);

            echo json_encode(array("status" => "success", "message" => "SUCCESS: Topup code {$kiw_temp['code']} deleted", "data" => null));


        } else {


            echo json_encode(array("status" => "error", "message" => "ERROR: Missing code id from request", "data" => null));


        }


    } else {


        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));


    }


}


function reset_code() 
{


    global $kiw_db;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        $id = $kiw_db->escape($_POST['id']);

        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_topup_code WHERE id ='{$id}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");


        if (!empty($id)) {


            $kiw_db->query("UPDATE kiwire_topup_code SET status = 'n', username = NULL, date_activate = NULL WHERE id ='{$id}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");

            $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(),  profile_cus = NULL WHERE tenant_id ='{$_SESSION['tenant_id']}' AND username = '{$kiw_temp['username']}' LIMIT 1");



            sync_logger("{$_SESSION['user_name']} reset topup code {$kiw_temp['code']}", $_SESSION['tenant_id']);

            echo json_encode(array("status" => "success", "message" => "SUCCESS: Topup code [{$kiw_temp['code']}] has been reset", "data" => null));


        }

    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }



}




function edit_single_data()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {
     
        csrf($kiw_db->escape($_REQUEST['token']));

        sync_logger("{$_SESSION['user_name']} updated profile {$_REQUEST['name']}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Topup code has been updated", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}



function statistics(){

    
    global $kiw_db;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        $kiw_code = $kiw_db->escape($_REQUEST['id']);


        if (strlen($kiw_code) > 0) {
  

            $kiw_result = array();

   
            $kiw_result['topup'] = $kiw_db->query_first("SELECT * FROM kiwire_topup_code WHERE code = '{$kiw_code}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");

            $kiw_username = $kiw_result['topup']['username'];

            $kiw_result['auth'] = $kiw_db->query_first("SELECT * FROM kiwire_account_auth WHERE username = '{$kiw_username}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1");

            
            if (empty($kiw_result['auth'])) {


                $kiw_result['auth']['username']      = "NONE";
                $kiw_result['auth']['fullname']      = "NONE";
                $kiw_result['auth']['email_address'] = "NONE";
                $kiw_result['auth']['phone_number']  = "NONE";


            }

            $kiw_result['topup']['date_create']   = sync_tolocaltime($kiw_result['topup']['date_create'], $_SESSION['timezone']);
            $kiw_result['topup']['date_activate'] = sync_tolocaltime($kiw_result['topup']['date_activate'], $_SESSION['timezone']);

            $kiw_result['topup']['quota'] =  (int)$kiw_result['topup']['quota'] / (pow(2, 20));
            $kiw_result['topup']['time'] =  (int)$kiw_result['topup']['time'] / 60;

            $kiw_result['creator']  = $_SESSION['user_name'];


            echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_result));



        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Missing account details", "data" => null));

        }



    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }



}


function history() {

    global $kiw_db;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        $kiw_result = array();

        $kiw_code = $kiw_db->escape($_REQUEST['id']);


        $kiw_timezone = $_SESSION['timezone'];

        if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


        if (strlen($kiw_code) > 0) {


            foreach (range(0, 5) as $kiw_range) {


                $kiw_current_month = date("Ym", strtotime("-{$kiw_range} Month"));

                $kiw_result_db = $kiw_db->fetch_array("SELECT tenant_id, CONVERT_TZ(start_time, 'UTC', '{$kiw_timezone}') AS start_time, CONVERT_TZ(stop_time, 'UTC', '{$kiw_timezone}') AS stop_time, session_time, mac_address, class, brand, ip_address, ipv6_address, quota_in, quota_out, terminate_reason FROM kiwire_sessions_{$kiw_current_month} WHERE username = '{$kiw_code}' AND tenant_id = '{$_SESSION['tenant_id']}' ORDER BY id DESC");


                if (is_array($kiw_result_db)) {

                    $kiw_result = array_merge($kiw_result, $kiw_result_db);
                }


                // make sure not too many data send in one session

                if (count($kiw_result) > 1000) break;


            }

            echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_result));


        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Missing account details", "data" => null));

        }


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}