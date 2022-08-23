<?php


header("Content-Type: application/json");


require_once "../includes/include_general.php";
require_once "../includes/include_session.php";


global $kiw_db;


$action = $_REQUEST['action'];

switch ($action) {

    case "history":get_usage_history($kiw_db); break;

    default: echo "ERROR: Wrong implementation";
}


function get_usage_history($kiw_db)
{

        $kiw_result = array();

        $kiw_username = $_SESSION['cpanel']['username'];

        $kiw_tenant   = $_SESSION['cpanel']['tenant_id'];

        $kiw_timezone = $_SESSION['timezone'];
        

        if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


        if (strlen($kiw_username) > 0) {


            foreach (range(0, 5) as $kiw_range) {


                $kiw_current_month = date("Ym", strtotime("-{$kiw_range} Month"));

                $kiw_result_db = $kiw_db->fetch_array("SELECT tenant_id, CONVERT_TZ(start_time, 'UTC', '{$kiw_timezone}') AS start_time, CONVERT_TZ(stop_time, 'UTC', '{$kiw_timezone}') AS stop_time, session_time, mac_address, ip_address, quota_in, quota_out, terminate_reason FROM kiwire_sessions_{$kiw_current_month} WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant}' ORDER BY id DESC");


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


}
