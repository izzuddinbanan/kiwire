<?php 

require_once dirname(__FILE__, 3) . "/admin/includes/include_connection.php";
require_once dirname(__FILE__, 3) . "/admin/includes/include_general.php";

require_once dirname(__FILE__, 2) . "/includes/include_general.php";



$kiw_tenant = $kiw_db->escape($_REQUEST['tenant_id']);


$kiw_cloud = $kiw_db->query_first("SELECT tenant_id FROM kiwire_clouds WHERE tenant_id = '{$kiw_tenant}'");


var_dump($kiw_cloud);

