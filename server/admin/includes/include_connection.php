<?php

require_once dirname(__FILE__, 2) . "/includes/include_config.php";
require_once dirname(__FILE__, 3) . "/libs/class.database.php";


$kiw_db = Database::obtain();
$kiw_db->connect();

$kiw_cache = new Redis();
$kiw_cache->connect(SYNC_REDIS_HOST, SYNC_REDIS_PORT);
$kiw_cache->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);


