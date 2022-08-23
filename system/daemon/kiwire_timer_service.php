<?php


function run() {

    shell_exec("nohup /usr/bin/php /var/www/kiwire/system/daemon/kiwire_paloalto.php 1>/dev/null 2>&1 &");

}

//run every 1s
Swoole\Timer::tick(10000, "run");