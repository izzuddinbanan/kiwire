<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);


require_once dirname(__FILE__, 4) . "/admin/includes/include_config.php";

require_once dirname(__FILE__, 5) . "/system/pms/kiwire_pms_functions.php";


$kiw_tenant = dirname(__FILE__);

if (strpos($kiw_tenant, "server/custom") == false) {

    echo "This script need to be run from the tenant folder. Example: /custom/default/pms/\n";

}

$kiw_tenant = array_filter(explode("/", $kiw_tenant));


foreach ($kiw_tenant as $kiw_index => $kiw_tenant_) {

    if ($kiw_tenant_ == "custom") {

        $kiw_tenant = $kiw_tenant[$kiw_index + 1];

        break;

    }

}

unset($kiw_index);

unset($kiw_tenant_);


// get the latest data from database

$kiw_db = new mysqli(SYNC_DB1_HOST, SYNC_DB1_USER, SYNC_DB1_PASSWORD, SYNC_DB1_DATABASE, SYNC_DB1_PORT);

$kiw_pms_setting = $kiw_db->query("SELECT * FROM kiwire_int_pms WHERE tenant_id = '{$kiw_tenant}' LIMIT 1");

if ($kiw_pms_setting) $kiw_pms_setting = $kiw_pms_setting->fetch_all(MYSQLI_ASSOC)[0];
else {

    pms_logger("No PMS setting has been set for this tenant.", "pms_guest", $kiw_tenant);

    die("No PMS setting has been set for this tenant.");

}


// set the timezone for this script to follow tenant setting

$kiw_temp = $kiw_db->query("SELECT timezone FROM kiwire_clouds WHERE tenant_id = '{$kiw_tenant}' LIMIT 1");

if ($kiw_temp) $kiw_temp = $kiw_temp->fetch_all(MYSQLI_ASSOC)[0];

if (empty($kiw_temp['timezone'])) $kiw_temp['timezone'] = "Asia/Kuala_Lumpur";

date_default_timezone_set($kiw_temp['timezone']);

unset($kiw_temp);


// check for vip profiles

$kiw_vips = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_int_pms_vipcode WHERE tenant_id = '{$kiw_tenant}'");

if ($kiw_vips) $kiw_vips = $kiw_vips->fetch_all(MYSQLI_ASSOC);


$kiw_db->close();

unset($kiw_db);


$kiw_pms_setting['pms_port'] = (int)$kiw_pms_setting['pms_port'];

if (empty($kiw_pms_setting['pms_port'])) {

    pms_logger("No port specified for this PMS integration", "pms_guest", $kiw_tenant);

    die();

}


$kiw_server = new swoole_server("0.0.0.0", $kiw_pms_setting['pms_port'], SWOOLE_PROCESS, SWOOLE_SOCK_TCP);


$kiw_server->set(array(
    'worker_num' => 1,
    'daemonize' => 0,
    'backlog' => 128
));


$kiw_server->on('Connect', function () use ($kiw_tenant) {

    pms_logger("PMS connected", "pms_guest", $kiw_tenant);

});


$kiw_server->on('Receive', function ($kiw_server, $kiw_fd, $kiw_from, $kiw_data) use ($kiw_tenant, $kiw_pms_setting, $kiw_vips) {


    if (trim($kiw_data) == "HELLO"){

        $kiw_server->send($kiw_fd, "ALIVE");

        return;

    }


    $kiw_db = new mysqli(SYNC_DB1_HOST, SYNC_DB1_USER, SYNC_DB1_PASSWORD, SYNC_DB1_DATABASE, SYNC_DB1_PORT);


    $kiw_data = ltrim(trim($kiw_data, ";"), ":");

    $kiw_data = explode("|", $kiw_data);


    if (!is_array($kiw_data)) return;


    // take action based on the data

    $kiw_response['guest_id']   = $kiw_data[0];
    $kiw_response['room_no']    = $kiw_data[1];
    $kiw_response['folio']      = $kiw_data[2];

    $kiw_response['action']         = $kiw_data[3];
    $kiw_response['guest_first']    = $kiw_data[4];
    $kiw_response['guest_last']     = $kiw_data[5];

    $kiw_response['occupancy']  = $kiw_data[6];
    $kiw_response['room_old']   = $kiw_data[7];
    $kiw_response['guest_vip']  = $kiw_data[8];

    $kiw_response['guest_name'] = $kiw_response['guest_first'] . ", " . $kiw_response['guest_last'];


    switch ($kiw_pms_setting['pass_mode']) {

        case 0:
            $kiw_response['password'] = $kiw_pms_setting['pass_predefined'];
            break;
        case 1:
            $kiw_response['password'] = $kiw_response['room_no'];
            break;
        default:
            $kiw_response['password'] = pms_password();
            break;

    }


    if ($kiw_response['action'] == "1") {


        if (pms_check_in($kiw_db, "pms_guest", $kiw_tenant, $kiw_response, $kiw_pms_setting['vip_match'], $kiw_vips) == true) {

            pms_logger("CHECK-IN confirmed. Room [ {$kiw_response['room_no']} ] Guest [ {$kiw_response['guest_name']} ].", "pms_guest", $kiw_tenant);

        } else pms_logger("CHECK-IN failed. Room [ {$kiw_response['room_no']} ] Guest [ {$kiw_response['guest_name']} ].", "pms_guest", $kiw_tenant);


    // guest check out

    } elseif ($kiw_response['action'] == "2") {


        if (pms_check_out($kiw_db, "pms_guest", $kiw_tenant, $kiw_response) == true) {

            pms_logger("CHECK-OUT confirmed. Room [ {$kiw_response['room_no']} ].", "pms_guest", $kiw_tenant);

        } else pms_logger("CHECK-OUT failed. Room [ {$kiw_response['room_no']} ].", "pms_guest", $kiw_tenant);


    // guest change info

    } elseif ($kiw_response['action'] == "3") {


        if (pms_move_room($kiw_db, "pms_guest", $kiw_tenant, $kiw_response['room_old'], $kiw_response['room_no']) == true) {

            pms_logger("CHANGE-ROOM confirmed. Room [ {$kiw_response['room_no']} ].", "pms_guest", $kiw_tenant);

        } else pms_logger("CHANGE-ROOM failed. Room [ {$kiw_response['room_no']} ].", "pms_guest", $kiw_tenant);



    } elseif ($kiw_response['action'] == "4") {


        if (pms_update_info($kiw_db, "pms_guest", $kiw_tenant, $kiw_response) == true) {

            pms_logger("UPDATE-INFO confirmed. Room [ {$kiw_response['room_no']} ].", "pms_guest", $kiw_tenant);

        } else pms_logger("UPDATE-INFO failed. Room [ {$kiw_response['room_no']} ].", "pms_guest", $kiw_tenant);


    }


});


$kiw_server->on('Close', function () use ($kiw_tenant) {


    pms_logger("Connection closed", "pms_guest", $kiw_tenant);


});


$kiw_server->start();


Swoole\Timer::tick(1000, function () use ($kiw_tenant, $kiw_pms_setting) {


    $kiw_db = new mysqli(SYNC_DB1_HOST, SYNC_DB1_USER, SYNC_DB1_PASSWORD, SYNC_DB1_DATABASE, SYNC_DB1_PORT);


    $kiw_posts = $kiw_db->query("SELECT * FROM kiwire_int_pms_payment WHERE tenant_id = '{$kiw_tenant}' AND status = 'new'");

    if ($kiw_posts) $kiw_posts = $kiw_posts->fetch_all(MYSQLI_ASSOC);


    if (count($kiw_posts) > 0) {


        $kiw_connection = new swoole_client(SWOOLE_TCP);

        $kiw_connection->connect($kiw_pms_setting['pms_host'], $kiw_pms_setting['pms_port'], 10);


        if ($kiw_connection->isconnected()) {


            $kiw_time['date'] = date("Y-m-d");

            $kiw_time['time'] = date("H:i:s");


            foreach ($kiw_posts as $kiw_post) {


                $kiw_connection->send(":{$kiw_post['room']}|{$kiw_time['date']}|{$kiw_time['time']}|{$kiw_post['room']}|{$kiw_post['amount']}|WIFI-PKG-{$kiw_post['profile']};");

                $kiw_db->query("UPDATE kiwire_int_pms_payment SET updated_date = NOW(), status = 'sent' WHERE tenant_id = '{$kiw_tenant}' AND id = '{$kiw_post['id']}' LIMIT 1");


            }


        }


    }


    $kiw_db->close();


});
