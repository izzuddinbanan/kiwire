<?php

$kiw['module'] = "Help -> License Usage";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_connection.php";
require_once "../includes/include_general.php";


if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}


if (in_array($_SESSION['permission'], array("r", "rw"))) {


    $kiw_result = [];


    // get the master license

    $kiw_master = @file_get_contents(dirname(__FILE__, 3) . "/custom/cloud.license");

    $kiw_master = sync_license_decode($kiw_master);


    if ($_SESSION['access_level'] == "administrator"){

        $kiw_single_tenant = "WHERE tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1";

    } else $kiw_single_tenant = "";


    // get the list of tenant

    $kiw_clouds = $kiw_db->fetch_array("SELECT tenant_id,name FROM kiwire_clouds {$kiw_single_tenant}");


    if (is_array($kiw_clouds) && count($kiw_clouds) > 0){


        // count number of device

        $kiw_controllers = $kiw_db->fetch_array("SELECT unique_id,tenant_id FROM kiwire_controller WHERE device_type = 'controller'");

        $kiw_controller_count = [];

        foreach ($kiw_controllers as $kiw_controller){

            $kiw_controller_count[$kiw_controller['tenant_id']] += 1;

        }


        $kiw_timezone = $_SESSION['timezone'];

        if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


        if (strlen($_SESSION['tenant_allowed']) > 0){

            $kiw_allowed_tenants = explode(",", $_SESSION['tenant_allowed']);

        }


        foreach ($kiw_clouds as $kiw_cloud){


            $kiw_temp = [];


            $kiw_license = @file_get_contents(dirname(__FILE__, 3) . "/custom/{$kiw_cloud['tenant_id']}/tenant.license");

            $kiw_license = sync_license_decode($kiw_license);


            if ($kiw_license) {

                $kiw_temp['name'] = $kiw_license['client_name'];
                $kiw_temp['limit'] = $kiw_license['device_limit'];
                $kiw_temp['type'] = "Tenant";
                $kiw_temp['current'] = isset($kiw_controller_count[$kiw_cloud['tenant_id']]) ? $kiw_controller_count[$kiw_cloud['tenant_id']] : 0;
                $kiw_temp['expire'] = sync_tolocaltime(date("Y-m-d H:i:s", $kiw_license['expire_on']), $kiw_timezone);
                $kiw_temp['status'] = (time() - $kiw_license['expire_on']) > 0 ? "Expired" : "Active";
                $kiw_temp['percent'] = round(($kiw_temp['current'] / $kiw_temp['limit']) * 100, 0);

            } else {

                if ($kiw_master) {

                    $kiw_temp['name'] = $kiw_cloud['name'];
                    $kiw_temp['limit'] = $kiw_master['device_limit'];
                    $kiw_temp['current'] = isset($kiw_controller_count[$kiw_cloud['tenant_id']]) ? $kiw_controller_count[$kiw_cloud['tenant_id']] : 0;
                    $kiw_temp['expire'] = sync_tolocaltime(date("Y-m-d H:i:s", $kiw_master['expire_on']), $kiw_timezone);
                    $kiw_temp['status'] = (time() - $kiw_master['expire_on']) > 0 ? "Expired" : "Active";
                    $kiw_temp['percent'] = round(($kiw_temp['current'] / $kiw_temp['limit']) * 100, 0);


                } else {

                    $kiw_temp['name'] = $kiw_cloud['name'];
                    $kiw_temp['limit'] = 0;
                    $kiw_temp['current'] = $kiw_controller_count[$kiw_cloud['tenant_id']];
                    $kiw_temp['expire'] = "Unlicensed";
                    $kiw_temp['status'] = $kiw_temp['expire'];
                    $kiw_temp['percent'] = "Unavailable";

                }


                $kiw_temp['type'] = "Cloud";


            }


            if (empty($kiw_allowed_tenants) || in_array($kiw_cloud['tenant_id'], $kiw_allowed_tenants)) {

                $kiw_result[$kiw_cloud['tenant_id']] = $kiw_temp;

            }


            unset($kiw_temp);


        }



    }


    echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_result));


} else {

    echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

}
