<?php


require_once dirname(__FILE__, 4) . "/admin/includes/include_connection.php";


$kiw_settings = dirname(__FILE__, 4) . "/custom/system_setting.json";

$kiw_settings = json_decode($kiw_settings, true);


if (empty($kiw_settings['system_url'])) $kiw_settings['system_url'] = "http://google.com";


$kiw_ap = htmlspecialchars($_REQUEST['gw_sn'], ENT_QUOTES | ENT_HTML5);

$kiw_mac = htmlspecialchars($_REQUEST['mac'], ENT_QUOTES | ENT_HTML5);



if (!empty($kiw_mac)) {


    $kiw_temp = $kiw_cache->get("WD:PORTAL:{$kiw_mac}");

    if (empty($kiw_temp)) $kiw_temp = $kiw_settings['system_url'];


} else {


    $kiw_temp = $kiw_settings['system_url'];


};


?>

<meta http-equiv='refresh' content='0; URL=<?= $kiw_temp ?>'>

