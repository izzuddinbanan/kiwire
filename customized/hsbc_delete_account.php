<?php


require_once dirname(__FILE__, 3) . "/server/admin/includes/include_config.php";
require_once dirname(__FILE__, 3) . "/server/admin/includes/include_connection.php";


$kiw_clouds = array("hsbc_bin", "hsbc_dm", "hsbc_klc", "hsbc_pjc", "hsbc_usj");


foreach ($kiw_clouds as $kiw_cloud) {


    $kiw_accounts = $kiw_db->query("SELECT username FROM kiwire_account_auth WHERE tenant_id = '{$kiw_cloud}' AND status = 'suspend' AND date_create < DATE_SUB(NOW(), INTERVAL 10 MINUTE)");


    foreach ($kiw_accounts as $kiw_account) {


        $kiw_db->query("DELETE FROM kiwire_account_auth WHERE tenant_id = '{$kiw_cloud}' AND username = '{$kiw_account['username']}' LIMIT 1");

        $kiw_cache->del("OTP:GENERATED:{$kiw_cloud}:{$kiw_account['username']}");


    }


}
