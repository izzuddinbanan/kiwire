<?php


require_once dirname(__FILE__, 4) . "/admin/includes/include_connection.php";


$kiw_temp['unique_id']      = preg_replace("/[^a-zA-Z0-9]+/", "",  $_GET['gw_id']);
$kiw_temp['processed']      = 0;
$kiw_temp['reason']         = "Check OK";
$kiw_temp['status']         = "running";
$kiw_temp['ping']           = 100;
$kiw_temp['uptime']         = $_REQUEST['sys_uptime'];
$kiw_temp['cpu_load']       = $_REQUEST['sys_load'];
$kiw_temp['memory_used']    = $_REQUEST['sys_memfree'];
$kiw_temp['updated_date']   = date("Y-m-d H:i:s");


$kiw_report = $kiw_cache->get("WD:PING:{$kiw_temp['unique_id']}");


if ((time() - $kiw_report) >= 300) {


    $kiw_tenant = $kiw_db->query_first("SELECT SQL_CACHE tenant_id,monitor_method FROM kiwire_controller WHERE unique_id = '{$kiw_temp['unique_id']}' LIMIT 1");


    if (!empty($kiw_tenant['tenant_id'])) {


        if ($kiw_tenant['monitor_method'] == "wifidog") {


            $kiw_temp['tenant_id'] = $kiw_tenant['tenant_id'];

            $kiw_db->insert("kiwire_nms_log", $kiw_temp);

            $kiw_db->query("UPDATE kiwire_controller SET updated_date = NOW(), status = 'running', last_update = NOW() WHERE unique_id = '{$kiw_temp['unique_id']}' LIMIT 1");


        }


    }


}


$kiw_cache->set("WD:PING:{$kiw_temp['unique_id']}", time(), 1900);


echo "Pong";

