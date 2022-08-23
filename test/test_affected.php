<?php

require_once dirname(__FILE__, 2) . "/server/admin/includes/include_config.php";


go(function () {

    $kiw_db = new Swoole\Coroutine\MySQL();

    $kiw_db->connect(array('host' => SYNC_DB1_HOST, 'user' => SYNC_DB1_USER, 'password' => SYNC_DB1_PASSWORD, 'database' => SYNC_DB1_DATABASE, 'port' => SYNC_DB1_PORT));

    $kiw_db->query("UPDATE kiwire_total_counter SET value = value + 1 WHERE data = 'sms' AND tenant_id = 'default' LIMIT 1");

    var_dump($kiw_db->affected_rows == false);

});


echo "test2";
