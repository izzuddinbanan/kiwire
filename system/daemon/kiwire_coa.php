<?php


error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING );


$kiw_server = new swoole_server("0.0.0.0", 3799, SWOOLE_PROCESS, SWOOLE_SOCK_TCP);


$kiw_server->set(array(
    'worker_num' => 1,
    'daemonize' => 0,
    'backlog' => 1024,
    'pid_file' => '/run/kiwire-coa.pid'
));


$kiw_server->on('Receive', function ($kiw_server, $kiw_fd, $kiw_from, $kiw_data){


    $kiw_data = json_decode($kiw_data, true);


    if (is_array($kiw_data)){


        if (!isset($kiw_data['controller']) || !isset($kiw_data['session'])){

            return;

        }


        go(function () use ($kiw_data){

            actual_coa($kiw_data);

        });


    }



});


$kiw_server->start();


Swoole\Timer::tick(6000,function (){


    $kiw_redis = new Redis();

    $kiw_redis->connect("127.0.0.1", 6379);


    $kiw_redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);


    $kiw_report_it = null;


    $omy_reports = $kiw_redis->scan($kiw_report_it, "COA_PENDING:*", 1000);


    foreach ($omy_reports as $omy_report) {


        $kiw_data = $kiw_redis->get($omy_report);


        if (is_array($kiw_data)) {


            go(function () use ($kiw_data) {

                actual_coa($kiw_data);

            });


        }


    } while ($kiw_report_it != 0);


    $kiw_redis->close();



});


function actual_coa($kiw_data){


    $kiw_connection = radius_acct_open();

    radius_add_server($kiw_connection, $kiw_data['controller']['device_ip'], $kiw_data['controller']['coa_port'], $kiw_data['controller']['shared_secret'], 5, 3);


    if ($kiw_data['type'] == "coa"){


        // create connection

        radius_create_request($kiw_connection, RADIUS_COA_REQUEST);


        // update max upload speed

        if ($kiw_data['profile']['reply:WISPr-Bandwidth-Max-Up'] > 0) radius_put_vendor_int($kiw_connection, 14122, 7, $kiw_data['profile']['reply:WISPr-Bandwidth-Max-Up']);
        if ($kiw_data['profile']['reply:WISPr-Bandwidth-Min-Up'] > 0) radius_put_vendor_int($kiw_connection, 14122, 5, $kiw_data['profile']['reply:WISPr-Bandwidth-Min-Up']);


        // update max download speed

        if ($kiw_data['profile']['reply:WISPr-Bandwidth-Max-Down'] > 0) radius_put_vendor_int($kiw_connection, 14122, 8, $kiw_data['profile']['reply:WISPr-Bandwidth-Max-Down']);
        if ($kiw_data['profile']['reply:WISPr-Bandwidth-Min-Down'] > 0) radius_put_vendor_int($kiw_connection, 14122, 6, $kiw_data['profile']['reply:WISPr-Bandwidth-Min-Down']);


        // update session time to new

        if ($kiw_data['profile']['control:Access-Period'] > 0){

            radius_put_int($kiw_connection, RADIUS_SESSION_TIMEOUT, $kiw_data['profile']['control:Access-Period']);

        } elseif ($kiw_data['profile']['control:Max-All-Session'] > 0){

            radius_put_int($kiw_connection, RADIUS_SESSION_TIMEOUT, $kiw_data['profile']['control:Max-All-Session']);

        } else {

            radius_put_int($kiw_connection, RADIUS_SESSION_TIMEOUT, 0);

        }


        // update idle time-out

        if ($kiw_data['profile']['reply:Idle-Timeout'] > 0) radius_put_int($kiw_connection, RADIUS_IDLE_TIMEOUT, $kiw_data['profile']['reply:Idle-Timeout']);



        if (in_array($kiw_data['controller']['vendor'], array("ruckus_vsz", "ruckus_scg"))) {


            radius_put_string($kiw_connection, RADIUS_ACCT_SESSION_ID, $kiw_data['session']['session_id']);


        } else {


            // total quota if controller support

            if ($kiw_data['profile']['control:Kiwire-Total-Quota'] > 0){


                if ($kiw_data['controller']['vendor'] == "mikrotik"){

                    radius_put_vendor_int($kiw_connection, 14988, 17, $kiw_data['profile']['control:Kiwire-Total-Quota'] * pow(1024, 2));

                } elseif ($kiw_data['controller']['vendor'] == "chillispot") {

                    radius_put_vendor_int($kiw_connection, 14559, 3, $kiw_data['profile']['control:Kiwire-Total-Quota'] * pow(1024, 2));

                }


            } else {


                if ($kiw_data['controller']['vendor'] == "mikrotik"){

                    radius_put_vendor_int($kiw_connection, 14988, 17, 0);

                } elseif ($kiw_data['controller']['vendor'] == "chillispot") {

                    radius_put_vendor_int($kiw_connection, 14559, 3, 0);

                }


            }


            radius_put_string($kiw_connection, RADIUS_USER_NAME, $kiw_data['session']['username']);

            radius_put_addr($kiw_connection, RADIUS_FRAMED_IP_ADDRESS, $kiw_data['session']['ip_address']);

            radius_put_int($kiw_connection, 55, time());


        }


    } else {

        // create connection

        radius_create_request($kiw_connection, RADIUS_DISCONNECT_REQUEST);

        if (in_array($kiw_data['controller']['vendor'], array("mikrotik", "aruba", "xirrus", "chilispot", "chillispot", "fortios", "ruckus_vsz", "ruckus_scg"))) {


            radius_put_string($kiw_connection, RADIUS_USER_NAME, $kiw_data['session']['username']);
            radius_put_addr($kiw_connection, RADIUS_FRAMED_IP_ADDRESS, $kiw_data['session']['ip_address']);


        } elseif (in_array($kiw_data['controller']['vendor'], array("meraki", "ubnt"))) {


            radius_put_string($kiw_connection, RADIUS_ACCT_SESSION_ID, $kiw_data['session']['session_id']);
            radius_put_int($kiw_connection, 55, time());


        } elseif ($kiw_data['controller']['vendor'] == "cisco_wlc") {


            radius_put_addr($kiw_connection, RADIUS_FRAMED_IP_ADDRESS, $kiw_data['session']['ip_address']);
            radius_put_addr($kiw_connection, RADIUS_NAS_IP_ADDRESS, $kiw_data['session']['controller_ip']);
            radius_put_string($kiw_connection, RADIUS_CALLING_STATION_ID, $kiw_data['session']['mac_address']);
            radius_put_string($kiw_connection, RADIUS_ACCT_SESSION_ID, $kiw_data['session']['session_id']);


        } elseif ($kiw_data['controller']['vendor'] == "cambium") {


            radius_put_string($kiw_connection, RADIUS_USER_NAME, $kiw_data['session']['username']);
            radius_put_addr($kiw_connection, RADIUS_NAS_IP_ADDRESS, $kiw_data['session']['controller_ip']);
            radius_put_string($kiw_connection, RADIUS_CALLING_STATION_ID, $kiw_data['session']['mac_address']);


        } elseif ($kiw_data['controller']['vendor'] == "cmcc") {


            radius_put_string($kiw_connection, RADIUS_USER_NAME, $kiw_data['session']['username']);
            radius_put_string($kiw_connection, RADIUS_ACCT_SESSION_ID, $kiw_data['session']['session_id']);


        }


    }


    // send radius packet to controller

    if (in_array(radius_send_request($kiw_connection), array(RADIUS_COA_ACK, RADIUS_DISCONNECT_ACK))){


        $kiw_data['type'] = strtoupper($kiw_data['type']);

        file_put_contents("/var/www/coa-" . date("Ymd-H") . ".log", "[ " . date("Y-m-d H:i:s") . " ] {$kiw_data['type']} sent to {$kiw_data['session']['controller']} for {$kiw_data['session']['username']}", FILE_APPEND);

        return true;


    } else {



        // keep the data for further procession

        $kiw_redis = new Redis();

        $kiw_redis->connect("127.0.0.1", 6379);

        $kiw_redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);


        if ($kiw_data['retry'] < 6) {


            $kiw_data['retry'] += 1;

            file_put_contents("/var/www/coa-" . date("Ymd-H") . ".log", "[ " . date("Y-m-d H:i:s") . " ] {$kiw_data['type']} retry [ {$kiw_data['session']['controller']} ] [ {$kiw_data['session']['username']} ]", FILE_APPEND);

            $kiw_redis->set("COA_PENDING:{$kiw_data['session']['username']}:{$kiw_data['session']['mac_address']}:{$kiw_data['session']['controller']}", $kiw_data, (86400 * 2));


        } else {


            file_put_contents("/var/www/coa-" . date("Ymd-H") . ".log", "[ " . date("Y-m-d H:i:s") . " ] {$kiw_data['type']} failed to send after 6 retries [ {$kiw_data['session']['controller']} ] [ {$kiw_data['session']['username']} ]", FILE_APPEND);

            $kiw_redis->del("COA_PENDING:{$kiw_data['session']['username']}:{$kiw_data['session']['mac_address']}:{$kiw_data['session']['controller']}");


        }


        $kiw_redis->close();


    }


}