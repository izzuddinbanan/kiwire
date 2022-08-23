<?php


session_start();


$kiw_username = $_SESSION['cpanel']['username'];
$kiw_tenant = $_SESSION['cpanel']['tenant_id'];


global $kiw_db;


if (empty($kiw_username) || empty($kiw_tenant)) {


    header("Location: /cpanel/index.php");

    die();


}


$kiw_cpanel = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_cpanel_template WHERE tenant_id = '{$kiw_tenant}' LIMIT 1");

$kiw_cloud = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_clouds WHERE tenant_id = '{$kiw_tenant}' LIMIT 1");


if ($kiw_cloud['volume_metrics'] == "Mb"){

    $kiw_cloud['volume_metrics'] = pow(1024, 2);

} else $kiw_cloud['volume_metrics'] = pow(1024, 3);

