<?php

require_once "config.php";

ini_set('mysql.connect_timeout','0');  
echo "Check v3 server DB connection.. \n";
$kiw_v3 = new mysqli(DB_V3_HOST, DB_V3_USER, DB_V3_PASS, DB_V3_NAME, DB_V3_PORT);
if ($kiw_v3->connect_errno) die("Kiwire v3: Unable to connect to database.\n");
echo "OK\n============\n";



echo "Check v2 server DB connection.. \n";
$kiw_v2 = new mysqli(DB_V2_HOST, DB_V2_USER, DB_V2_PASS, DB_V2_NAME, DB_V2_PORT);
if ($kiw_v2->connect_errno) die("Kiwire v2: Unable to connect to database {{ $kiw_v2->connect_errno }} .\n");
echo "OK\n============\n";

// declare(strict_types=1);

/**
 * This script takes about 1 second to finish, with 2,000 coroutines created. Without coroutine enabled (in line 14),
 * this script takes about 2,000 seconds to finish.
 *
 * How to run this script:
 *     docker exec -t $(docker ps -qf "name=client") bash -c "./csp/coroutines/for.php"
 *
 * You can run following command to see how much time it takes to run the script:
 *     docker exec -t $(docker ps -qf "name=client") bash -c "time ./csp/coroutines/for.php"
 */
// Swoole\Runtime::enableCoroutine();

// for ($i = 1; $i <= 2000; $i++) {
//     go(function () {
//         sleep(1);
//     });
// }

// echo count(Swoole\Coroutine::listCoroutines()), " active coroutines when reaching the end of the PHP script.\n";





