<?php

$kiw['module'] = "Cloud -> Overview";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_general.php";
require_once "../includes/include_connection.php";
require_once "../includes/include_report.php";


if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}


if (in_array($_SESSION['permission'], array("r", "rw"))) {


    if ($_REQUEST['action'] == "tenant_list") {


        $kiw_result = [];


        $kiw_temps = $kiw_db->fetch_array("SELECT tenant_id, COUNT(*) AS kcount FROM kiwire_active_session GROUP BY tenant_id");

        foreach ($kiw_temps as $kiw_temp){

            $kiw_actives[$kiw_temp['tenant_id']] = $kiw_temp['kcount'];

        }


        $kiw_temps = $kiw_db->fetch_array("SELECT tenant_id, COUNT(*) AS kcount FROM kiwire_account_auth GROUP BY tenant_id");

        foreach ($kiw_temps as $kiw_temp){

            $kiw_accounts[$kiw_temp['tenant_id']] = $kiw_temp['kcount'];

        }


        $kiw_clouds = $kiw_db->fetch_array("SELECT tenant_id,name FROM kiwire_clouds");

        foreach ($kiw_clouds as $kiw_cloud){

            $kiw_result[] = array(
                "tenant_id" => $kiw_cloud['tenant_id'],
                "tenant_name" => $kiw_cloud['name'],
                "tenant_account" => isset($kiw_accounts[$kiw_cloud['tenant_id']]) ? (int)$kiw_accounts[$kiw_cloud['tenant_id']] : 0,
                "tenant_active" => isset($kiw_actives[$kiw_cloud['tenant_id']]) ? (int)$kiw_actives[$kiw_cloud['tenant_id']] : 0
            );

        }


        echo json_encode(array("status" => "success", "message" => "", "data" => $kiw_result));


    } elseif ($_REQUEST['action'] == "system_info"){


        $kiw_temps = $kiw_db->fetch_array("SELECT ktype, COUNT(*) AS kcount FROM kiwire_account_auth GROUP BY ktype");

        foreach ($kiw_temps as $kiw_temp){

            $kiw_account[$kiw_temp['ktype']] = $kiw_temp['kcount'];

        }


        unset($kiw_temp);
        unset($kiw_temps);


        $kiw_active = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_active_session");


        $kiw_temps = $kiw_db->fetch_array("SELECT status, COUNT(*) AS kcount FROM kiwire_controller WHERE monitor_method <> 'none' GROUP BY status");

        foreach ($kiw_temps as $kiw_temp){

            $kiw_controller[$kiw_temp['status']] = $kiw_temp['kcount'];

        }

        unset($kiw_temp);
        unset($kiw_temps);


        $kiw_temps = $kiw_db->fetch_array("SELECT data, SUM(value) AS value FROM kiwire_total_counter GROUP BY data");

        foreach ($kiw_temps as $kiw_temp){

            $kiw_counter[$kiw_temp['data']] = $kiw_temp['value'];

        }


        echo json_encode(array("status" => "success", "message" => "", "data" => array(
            "accounts"         => isset($kiw_account['account']) ? (int)$kiw_account['account'] : 0,
            "vouchers"         => isset($kiw_account['voucher']) ? (int)$kiw_account['voucher'] : 0,
            "emails"           => isset($kiw_counter['email']) ? (int)$kiw_counter['email'] : 0,
            "smss"             => isset($kiw_counter['sms']) ? (int)$kiw_counter['sms'] : 0,
            "active"           => (int)$kiw_active['kcount'],
            "controller_up"    => isset($kiw_controller['running']) ? (int)$kiw_controller['running'] : 0,
            "controller_down"  => isset($kiw_controller['down']) ? (int)$kiw_controller['down'] : 0,
        )));


    }


}