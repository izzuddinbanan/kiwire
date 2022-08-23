<?php


error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);


require_once dirname(__FILE__, 3) . "/server/admin/includes/include_config.php";

require_once dirname(__FILE__, 3) . "/system/pms/kiwire_pms_functions.php";


$kiwire_server = new Swoole\Http\Server("0.0.0.0", 9958, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);


$kiwire_server->set(
    array(
        'worker_num' => 1,
        'max_conn' => 64,
        'max_request' => 64,
        'group' => 'nginx',
        'user' => 'nginx',
        'pid_file' => '/run/kiwire-pms-api.pid',
        'daemonize' => 1,
        'ssl_cert_file' => "/etc/ssl/certs/nginx-selfsigned.crt",
        'ssl_key_file' => "/etc/ssl/private/nginx-selfsigned.key",
        'ssl_ciphers' => 'HIGH:!aNULL:!MD5',
        'open_tcp_keepalive' => true,
    )
);


$kiwire_server->on("start", function ($server) {

    echo "Kiwire PMS API server service started at : " . date("Y-m-d H:i:s") . "\n";

});

$kiwire_server->on("request", function ($request, $response) {


    $kiw_data = $request->rawContent();

    $kiw_data = json_decode($kiw_data, true);


    $response->header("Server", "Kiwire-PMS-API");

    $response->header("Content-Type", "application/json");


    if (empty($kiw_data)) {


        $response->status(500);

        $response->end(json_encode(array("status" => "failed", "message" => "Data error, expected JSON but unknown received", "data" => "")));

        return;


    }


    // get the latest data from database

    $kiw_db = new mysqli(SYNC_DB1_HOST, SYNC_DB1_USER, SYNC_DB1_PASSWORD, SYNC_DB1_DATABASE, SYNC_DB1_PORT);


    $kiw_data = array_map(function ($kiw_item) use ($kiw_db){

        return $kiw_db->escape_string($kiw_item);

    }, $kiw_data);



    if (!isset($kiw_data['tenant_id']) || empty($kiw_data['tenant_id'])){


        $response->status(500);

        $response->end(json_encode(array("status" => "failed", "message" => "Missing tenant information from the JSON request", "data" => "")));

        return;


    }


    $kiw_pms_setting = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_int_pms WHERE tenant_id = '{$kiw_data['tenant_id']}' LIMIT 1");

    if ($kiw_pms_setting) $kiw_pms_setting = $kiw_pms_setting->fetch_all(MYSQLI_ASSOC)[0];
    else {

        pms_logger("No PMS setting has been set for this tenant.", "pms_api", $kiw_data['tenant_id']);

        $response->status(500);

        $response->end(json_encode(array("status" => "failed", "message" => "Unknown tenant provided or setting has not been setup", "data" => "")));

        return;

    }


    if ($kiw_pms_setting['pms_type'] != "json"){


        $response->status(500);

        $response->end(json_encode(array("status" => "failed", "message" => "PMS is not set to use JSON API", "data" => "")));

        return;


    }


    if ($kiw_pms_setting['enabled'] != "y"){


        $response->status(500);

        $response->end(json_encode(array("status" => "failed", "message" => "PMS has been disabled", "data" => "")));

        return;


    }


    if (empty($kiw_pms_setting['credential_string']) || $kiw_pms_setting['credential_string'] !== $kiw_data['token']){


        pms_logger("Invalid token provided", "pms_api", $kiw_data['tenant_id']);

        $response->status(500);

        $response->end(json_encode(array("status" => "failed", "message" => "Invalid token has been provided", "data" => "")));

        return;


    }


    // check for vip profiles

    $kiw_vips = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_int_pms_vipcode WHERE tenant_id = '{$kiw_data['tenant_id']}'");

    if ($kiw_vips) $kiw_vips = $kiw_vips->fetch_all(MYSQLI_ASSOC);


    // take action based on the data

    $kiw_response['guest_id']       = $kiw_data["guest_id"];
    $kiw_response['room_no']        = $kiw_data["room_no"];

    $kiw_response['action']         = $kiw_data["action"];
    $kiw_response['guest_first']    = $kiw_data["guest_firstname"];
    $kiw_response['guest_last']     = $kiw_data["guest_lastname"];

    $kiw_response['room_old']       = $kiw_data["room_old"];
    $kiw_response['guest_vip']      = $kiw_data["guest_vip"];

    $kiw_response['guest_name']     = $kiw_response['guest_first'] . ", " . $kiw_response['guest_last'];


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


    if ($kiw_response['action'] == "check-in") {


        if (pms_check_in($kiw_db, "pms_api", $kiw_data['tenant_id'], $kiw_response, $kiw_pms_setting['vip_match'], $kiw_vips) == true) {


            $response->end(json_encode(array("status" => "succeed", "message" => "check-in confirmed", "data" => "")));

            pms_logger("CHECK-IN confirmed. Room [ {$kiw_response['room_no']} ] Guest [ {$kiw_response['guest_name']} ].", "pms_api", $kiw_data['tenant_id']);


        } else {


            $response->status(500);

            $response->end(json_encode(array("status" => "failed", "message" => "check-in failed", "data" => "")));

            pms_logger("CHECK-IN failed. Room [ {$kiw_response['room_no']} ] Guest [ {$kiw_response['guest_name']} ].", "pms_api", $kiw_data['tenant_id']);


        }


    // guest check out

    } elseif ($kiw_response['action'] == "check-out") {


        if (pms_check_out($kiw_db, "pms_api", $kiw_data['tenant_id'], $kiw_response) == true) {


            $response->end(json_encode(array("status" => "succeed", "message" => "check-out confirmed", "data" => "")));

            pms_logger("CHECK-OUT confirmed. Room [ {$kiw_response['room_no']} ].", "pms_api", $kiw_data['tenant_id']);


        } else {


            $response->status(500);

            $response->end(json_encode(array("status" => "failed", "message" => "check-out failed", "data" => "")));

            pms_logger("CHECK-OUT failed. Room [ {$kiw_response['room_no']} ].", "pms_api", $kiw_data['tenant_id']);


        }


    // guest change info

    } elseif ($kiw_response['action'] == "change-room") {


        if (pms_move_room($kiw_db, "pms_api", $kiw_data['tenant_id'], $kiw_response['room_old'], $kiw_response['room_no']) == true) {


            $response->end(json_encode(array("status" => "succeed", "message" => "change-room confirmed", "data" => "")));

            pms_logger("CHANGE-ROOM confirmed. Room [ {$kiw_response['room_no']} ].", "pms_api", $kiw_data['tenant_id']);


        } else {


            $response->status(500);

            $response->end(json_encode(array("status" => "failed", "message" => "change-room failed", "data" => "")));

            pms_logger("CHANGE-ROOM failed. Room [ {$kiw_response['room_no']} ].", "pms_api", $kiw_data['tenant_id']);


        }



    } elseif ($kiw_response['action'] == "update-info") {


        if (pms_update_info($kiw_db, "pms_api", $kiw_data['tenant_id'], $kiw_response) == true) {


            $response->end(json_encode(array("status" => "succeed", "message" => "update-info confirmed", "data" => "")));

            pms_logger("UPDATE-INFO confirmed. Room [ {$kiw_response['room_no']} ].", "pms_api", $kiw_data['tenant_id']);


        } else {


            $response->status(500);

            $response->end(json_encode(array("status" => "failed", "message" => "update-info failed", "data" => "")));

            pms_logger("UPDATE-INFO failed. Room [ {$kiw_response['room_no']} ].", "pms_api", $kiw_data['tenant_id']);


        }


    } else {


        $response->status(500);

        $response->end(json_encode(array("status" => "failed", "message" => "Unknown action has been provided", "data" => "")));


    }


});


$kiwire_server->start();