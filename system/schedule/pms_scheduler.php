<?php


error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);


$kiw_lock_name = "kiwire-pms.lock";

require_once "scheduler_lock.php";



// check for idb payment / dbswap that need to be posted



