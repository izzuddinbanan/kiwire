<?php


require_once "admin/includes/include_config.php";


// check for mariadb connection

try {


    mysqli_report(MYSQLI_REPORT_STRICT);

    $kiw_health = new mysqli(SYNC_DB1_HOST, SYNC_DB1_USER, SYNC_DB1_PASSWORD, SYNC_DB1_DATABASE, SYNC_DB1_PORT);

    if ($kiw_health->connect_errno) die("error");


} catch (Exception $e){

    http_response_code(500);

    echo "error";
    exit(1000);

}

$kiw_health->close();

unset($kiw_health);


// check radius connection

$kiw_health = `/bin/echo "Message-Authenticator=0x00,FreeRADIUS-Statistics-Type=0x1" | radclient -x -r 1 -t 1 127.0.0.1:18121 status adminsecret 2>&1 | grep "FreeRADIUS-Total-Access-Requests"`;

$kiw_health = trim(ltrim($kiw_health));

$kiw_health = explode("=", $kiw_health);

if (trim(ltrim($kiw_health[0])) !== "FreeRADIUS-Total-Access-Requests"){

    http_response_code(500);

    echo "error";
    exit(1000);

}

unset($kiw_health);


// check for redis server

try {


    $kiw_health = new Redis();

    $kiw_health->connect(SYNC_REDIS_HOST, SYNC_REDIS_PORT);


    if ($kiw_health->ping() !== "+PONG"){

        echo "error";
        exit(1000);

    }


} catch (Exception $e){

    http_response_code(500);

    echo "error";
    exit(1000);

}

$kiw_health->close();


echo "running";
exit(0);
