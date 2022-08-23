<?php

require_once dirname(__FILE__, 3) . "/includes/include_session.php";

require_once dirname(__FILE__, 4) . "/admin/includes/include_general.php";
require_once dirname(__FILE__, 4) . "/admin/includes/include_connection.php";


$kiw_time = date("YmdH");

$kiw_temp['name'] = $_REQUEST['name'];
$kiw_temp['source'] = $kiw_db->escape($_REQUEST['source']);


$kiw_temp['name'] = urldecode(base64_decode($kiw_temp['name']));


if (strpos($kiw_temp['name'], "||") == false){

    $kiw_temp['name'] = base64_decode($kiw_temp['name']);

}


if (strpos($kiw_temp['name'], "||")) {


    // this is for test comparison in the below logic

    $kiw_test = base64_encode(urlencode($kiw_temp['name']));



    $kiw_clicked = $kiw_db->query_first("SELECT mac_address,click FROM kiwire_device_unique WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' AND mac_address = '{$_SESSION['user']['mac']}' LIMIT 1");

    $kiw_new_device = empty($kiw_clicked['mac_address']);

    $kiw_clicked = array_filter(explode(",", $kiw_clicked['click']));


    if (!in_array($kiw_test, $kiw_clicked)) {


        $kiw_cache->incr("REPORT_CAMPAIGN_UCLICK:{$kiw_time}:{$_SESSION['controller']['tenant_id']}:{$_SESSION['user']['zone']}:{$kiw_temp['source']}:{$kiw_test}");


        $kiw_clicked[] = $kiw_test;


    }


    $kiw_clicked = implode(",", $kiw_clicked);


    if ($kiw_new_device == true) {

        $kiw_db->query("INSERT INTO kiwire_device_unique(id, tenant_id, click, mac_address) VALUE (NULL, '{$_SESSION['controller']['tenant_id']}', '{$kiw_clicked}', '{$_SESSION['user']['mac']}')");

    } else {

        $kiw_db->query("UPDATE kiwire_device_unique SET updated_date = NOW(), click = '{$kiw_clicked}' WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' AND mac_address = '{$_SESSION['user']['mac']}' LIMIT 1");

    }



    $kiw_cache->incr("REPORT_CAMPAIGN_CLICK:{$kiw_time}:{$_SESSION['controller']['tenant_id']}:{$_SESSION['user']['zone']}:{$kiw_temp['source']}:{$kiw_test}");

    kiw_logger($_SESSION['controller']['tenant_id'], $kiw_test, "click", $_SESSION['user']['mac'] . " | " . $_SESSION['user']['username']);


    $kiw_url = sync_decrypt(urldecode($_REQUEST['load']));

    if (strlen($kiw_url) > 0) {

        header("Location: {$kiw_url}");

    } else header("Location: /user/pages/?session={$session_id}");


}


function kiw_logger($kiw_tenant, $kiw_campaign_id, $kiw_action, $kiw_data){


    if (file_exists(dirname(__FILE__, 5) .  "/logs/campaign/") == false) mkdir(dirname(__FILE__, 5) . "/logs/campaign/", 755, true);


    if (!empty($kiw_tenant) && !empty($kiw_campaign_id)) {

        file_put_contents(dirname(__FILE__, 5) . "/logs/campaign/kiwire-campaign-{$kiw_action}-{$kiw_tenant}-{$kiw_campaign_id}", date("Y-m-d H:i:s") . " :: " . $kiw_data . "\n", FILE_APPEND);

    }


}