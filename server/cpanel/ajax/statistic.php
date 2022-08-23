<?php


header("Content-Type: application/json");


require_once "../includes/include_general.php";
require_once "../includes/include_session.php";


global $kiw_db, $kiw_tenant, $kiw_username, $kiw_cloud, $kiw_cpanel;


$action = $_REQUEST['action'];

switch ($action) {

    case "get_data":get_data(); break;

    default:
        echo "ERROR: Wrong implementation";
}



function get_data() {


    global $kiw_db, $kiw_tenant, $kiw_username;


    $kiw_result = array();


    $kiw_timezone = $_SESSION['timezone'];


    if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


    // $kiw_action = $_REQUEST['interval'];


    $kiw_data['interval']  = $kiw_db->escape($_REQUEST['interval']);

    $kiw_data['start']  = $kiw_db->escape($_REQUEST['start_date']);
    $kiw_data['end']    = $kiw_db->escape($_REQUEST['end_date']);

    $kiw_data['start']  = new DateTime($kiw_data['start'], new DateTimeZone($_SESSION['timezone']));
    $kiw_data['end']    = new DateTime($kiw_data['end'], new DateTimeZone($_SESSION['timezone']));

    $kiw_data['start']->setTimezone(new DateTimeZone("UTC"));
    $kiw_data['end']->setTimezone(new DateTimeZone("UTC"));




    if (strlen($kiw_username) > 0) {


        if ($kiw_data['interval'] == "hourly") {


            foreach (range(0, 5) as $kiw_range) {


                $kiw_current_month = date("Ym", strtotime("-{$kiw_range} Month"));


                // $kiw_result = $kiw_db->fetch_array("SELECT DATE(CONVERT_TZ(updated_date, 'UTC', '{$kiw_timezone}')) AS updated_date, COUNT(*) AS kcount, tenant_id, SUM(session_time) AS session_time, SUM(quota_in) AS quota_in, SUM(quota_out) AS quota_out FROM kiwire_sessions_{$kiw_current_month} WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant}' AND (updated_date BETWEEN '{$kiw_data['start']->format("Y-m-d H:i:s")}' AND '{$kiw_data['end']->format("Y-m-d H:i:s")}') GROUP BY updated_date");

                $kiw_result = $kiw_db->fetch_array("SELECT HOUR(CONVERT_TZ(updated_date, 'UTC', '{$kiw_timezone}')) AS updated_date, COUNT(*) AS kcount, tenant_id, SUM(session_time) AS session_time, SUM(quota_in) AS quota_in, SUM(quota_out) AS quota_out FROM kiwire_sessions_{$kiw_current_month} WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant}' AND updated_date = '{$kiw_data['start']->format("Y-m-d H:i:s")}' GROUP BY HOUR(CONVERT_TZ(updated_date, 'UTC', '{$kiw_timezone}'))");

                // make sure not too many data send in one session

                if (count($kiw_result) > 1000) break;
            
            }


        } elseif ($kiw_data['interval'] == "daily") {


            foreach (range(0, 5) as $kiw_range) {
                

                $kiw_current_month = date("Ym", strtotime("-{$kiw_range} Month"));


                // $kiw_result = $kiw_db->fetch_array("SELECT DATE(CONVERT_TZ(updated_date, 'UTC', '{$kiw_timezone}')) AS updated_date, COUNT(*) AS kcount, tenant_id, SUM(session_time) AS session_time, SUM(quota_in) AS quota_in, SUM(quota_out) AS quota_out FROM kiwire_sessions_{$kiw_current_month} WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant}' AND (updated_date BETWEEN '{$kiw_data['start']->format("Y-m-d H:i:s")}' AND '{$kiw_data['end']->format("Y-m-d H:i:s")}') GROUP BY updated_date");

                $kiw_result = $kiw_db->fetch_array("SELECT DATE(CONVERT_TZ(updated_date, 'UTC', '{$kiw_timezone}')) AS updated_date, COUNT(*) AS kcount, tenant_id, SUM(session_time) AS session_time, SUM(quota_in) AS quota_in, SUM(quota_out) AS quota_out FROM kiwire_sessions_{$kiw_current_month} WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant}' AND (updated_date BETWEEN '{$kiw_data['start']->format("Y-m-d H:i:s")}' AND '{$kiw_data['end']->format("Y-m-d H:i:s")}') GROUP BY DATE(CONVERT_TZ(updated_date, 'UTC', '{$kiw_timezone}'))");

                // make sure not too many data send in one session

                if (count($kiw_result) > 1000) break;


            }


        } elseif ($kiw_data['interval'] == "monthly") {



            foreach (range(0, 5) as $kiw_range) {


                $kiw_current_month = date("Ym", strtotime("-{$kiw_range} Month"));


                $kiw_result_db = $kiw_db->fetch_array("SELECT COUNT(*) AS kcount, tenant_id, MONTHNAME(CONVERT_TZ(updated_date, 'UTC', '{$kiw_timezone}')) AS monthly,  SUM(session_time) AS session_time, SUM(quota_in) AS quota_in, SUM(quota_out) AS quota_out FROM kiwire_sessions_{$kiw_current_month} WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant}' GROUP BY monthly");


                if (is_array($kiw_result_db)) {

                    $kiw_result = array_merge($kiw_result, $kiw_result_db);
                }


                // make sure not too many data send in one session

                if (count($kiw_result) > 1000) break;


            }


        }


        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_result));


    } else {


        echo json_encode(array("status" => "failed", "message" => "Missing account details", "data" => null));


    }


}




// function get_data()
// {


//     global $kiw_db, $kiw_tenant, $kiw_username;


//     $kiw_result = array();

//     $kiw_timezone = $_SESSION['timezone'];


//     if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


//     if (strlen($kiw_username) > 0) {


//         foreach (range(0, 5) as $kiw_range) {


//             $kiw_current_month = date("Ym", strtotime("-{$kiw_range} Month"));


//             $kiw_result_db = $kiw_db->fetch_array("SELECT COUNT(*) AS kcount, tenant_id, MONTHNAME(CONVERT_TZ(updated_date, 'UTC', '{$kiw_timezone}')) AS monthly,  SUM(session_time) AS session_time, SUM(quota_in) AS quota_in, SUM(quota_out) AS quota_out FROM kiwire_sessions_{$kiw_current_month} WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant}' GROUP BY monthly");


//             if (is_array($kiw_result_db)) {

//                 $kiw_result = array_merge($kiw_result, $kiw_result_db);
//             }



//             // make sure not too many data send in one session

//             if (count($kiw_result) > 1000) break;
//         }


//         echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_result));


//     } else {

//         echo json_encode(array("status" => "failed", "message" => "Missing account details", "data" => null));


//     }


// }







// $kiw_action = $_REQUEST['interval'];


// if ($kiw_action == "hourly"){




// } elseif ($kiw_action == "daily"){




// } elseif ($kiw_action == "monthly"){




// }

// echo json_encode(array("status" => "success", "message" => "", "metrics" => $kiw_cloud['volume_metrics'], "data" => $kiw_data));
