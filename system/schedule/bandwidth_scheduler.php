<?php


error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);


$kiw_lock_name = "kiwire-bandwidth.lock";

require_once "scheduler_lock.php";


require_once dirname(__FILE__, 3) . "/server/admin/includes/include_config.php";
require_once dirname(__FILE__, 3) . "/server/admin/includes/include_connection.php";


$kiw_bandwidths = $kiw_db->fetch_array("SELECT * FROM kiwire_bandwidth");


foreach ($kiw_bandwidths as $kiw_bandwidth){





}






