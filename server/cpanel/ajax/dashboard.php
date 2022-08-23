<?php


header("Content-Type: application/json");


require_once "../includes/include_general.php";
require_once "../includes/include_session.php";


global $kiw_db, $kiw_tenant, $kiw_username, $kiw_cloud, $kiw_cpanel;


$kiw_action = $_REQUEST['action'];


if ($kiw_action == "usage"){


    $kiw_data = array();


    // make sure non-zero

    if ($kiw_cpanel['history_month'] < 1) $kiw_cpanel['history_month'] = 1;


    for ($kiw_x = 0; $kiw_x < $kiw_cpanel['history_month']; $kiw_x++) {


        $kiw_current_month = date("Ym", strtotime("-{$kiw_x} Months"));

        $kiw_temp = $kiw_db->fetch_array("SELECT DATE(CONVERT_TZ(start_time, 'UTC', '{$kiw_cloud['timezone']}')) AS xreport_date, SUM(quota_in) AS upload, SUM(quota_out) AS download, SUM(session_time) AS time FROM kiwire_sessions_{$kiw_current_month} WHERE tenant_id = '{$kiw_tenant}' AND username = '{$kiw_username}' GROUP BY xreport_date");


        if (!empty($kiw_temp)) {

            $kiw_data = array_merge($kiw_data, $kiw_temp);

        }


        unset($kiw_temp);


    }


} elseif ($kiw_action == "invoice"){


    $kiw_data = $kiw_db->fetch_array("SELECT * FROM kiwire_invoice WHERE tenant_id = '{$kiw_tenant}' AND username = '{$kiw_username}'");


} elseif ($kiw_action == "login"){


    $kiw_current_month = date("Ym");

    $kiw_data = $kiw_db->fetch_array("SELECT start_time,stop_time,controller,session_time, (quota_in + quota_out) AS quota FROM kiwire_sessions_202012 WHERE tenant_id = '{$kiw_tenant}' AND username = '{$kiw_username}' ORDER BY id DESC LIMIT 5");


}


echo json_encode(array("status" => "success", "message" => "", "metrics" => $kiw_cloud['volume_metrics'], "data" => $kiw_data));
