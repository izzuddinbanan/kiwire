<?php


$kiw_tenant = dirname(__FILE__);

if (strpos($kiw_tenant, "server/custom") == false){

    kiw_print_response(false, "Invalid tenant info has been provided");

}

$kiw_tenant = array_filter(explode("/", $kiw_tenant));


foreach ($kiw_tenant as $kiw_index => $kiw_tenant_){

    if ($kiw_tenant_ == "custom"){

        $kiw_tenant = $kiw_tenant[$kiw_index + 1];

        break;

    }

}

unset($kiw_index);

unset($kiw_tenant_);


if (is_array($kiw_tenant)){

    die(json_encode(array("status" => "invalid")));

}


require_once dirname(__FILE__, 5) . "/admin/includes/include_config.php";

require_once dirname(__FILE__, 6) . "/system/pms/kiwire_pms_functions.php";


header("Content-Type: application/json");


$kiw_cache = new Redis();

$kiw_cache->connect(SYNC_REDIS_HOST, SYNC_REDIS_PORT);

$kiw_cache->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);


$kiw_db = new mysqli("p:" . SYNC_DB1_HOST, SYNC_DB1_USER, SYNC_DB1_PASSWORD, SYNC_DB1_DATABASE, SYNC_DB1_PORT);



// get the pms setting to start with

$kiw_pms_setting = $kiw_cache->get("PMS_SETTING:{$kiw_tenant}");

if (empty($kiw_pms_setting)){


    $kiw_pms_setting = $kiw_db->query("SELECT * FROM kiwire_int_pms WHERE tenant_id = '{$kiw_tenant}' LIMIT 1");

    if ($kiw_pms_setting) $kiw_pms_setting = $kiw_pms_setting->fetch_all(MYSQLI_ASSOC)[0];

    if (empty($kiw_pms_setting)) $kiw_pms_setting = array("dummy" => true);

    $kiw_cache->set("PMS_SETTING:{$kiw_tenant}", $kiw_pms_setting, 1800);


}


$kiw_vips = $kiw_cache->get("PMS_VIP_CODE:{$kiw_tenant}");

if (empty($kiw_vips)) {


    $kiw_vips = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_int_pms_vipcode WHERE tenant_id = '{$kiw_tenant}'");

    if ($kiw_vips) $kiw_vips = $kiw_vips->fetch_all(MYSQLI_ASSOC);

    if (empty($kiw_vips)) $kiw_vips = array("dummy" => true);

    $kiw_cache->set("PMS_VIP_CODE:{$kiw_tenant}", $kiw_vips, 1800);


}


// check if pms setting enabled and correct setup

// if ($kiw_pms_setting['enabled'] != "y" || $kiw_pms_setting['pms_type'] != "rhealta") {

if ($kiw_pms_setting['enabled'] != "y") {

    die(json_encode(array("status" => "disabled")));

}


foreach ($_POST as $kiw_key => $kiw_value){

    $kiw_data[$kiw_key] = $kiw_db->escape_string($kiw_value);

}


if ($kiw_data){


    switch ($kiw_pms_setting['pass_mode']){

        case 0: $kiw_response['password'] = $kiw_pms_setting['pass_predefined']; break;
        case 1: $kiw_response['password'] = $kiw_data['room_no']; break;
        default: $kiw_response['password'] = pms_password(); break;

    }


    $kiw_response['room_no']   	   = $kiw_db->escape_string($kiw_data['room']);
    $kiw_response['guest_first']   = $kiw_db->escape_string($kiw_data['fnm']);
    $kiw_response['guest_last']    = $kiw_db->escape_string($kiw_data['lnm']);
    $kiw_response['guest_arrival'] = $kiw_db->escape_string($kiw_data['ci']);
    $kiw_response['guest_vip']     = $kiw_db->escape_string($kiw_data['vip']);

    $kiw_response['guest_name']    = "{$kiw_response['guest_first']}, {$kiw_response['guest_last']}";


    // if room shared with multiple people

    if (!empty($kiw_data['fnm2']) && $kiw_data['fnm'] !== $kiw_data['fnm2']){

        $kiw_response['guest_name'] .= " | " . $kiw_db->escape_string("{$kiw_data['fnm2']}, {$kiw_data['lnm2']}");

    }

    if (!empty($kiw_data['fnm3']) && $kiw_data['fnm'] !== $kiw_data['fnm3']){

        $kiw_response['guest_name'] .= " | " . $kiw_db->escape_string("{$kiw_data['fnm3']}, {$kiw_data['lnm3']}");

    }


    if ($kiw_data['mode'] == "checkin"){


        if (pms_check_in($kiw_db, "rhealta", $kiw_tenant, $kiw_response, $kiw_pms_setting['vip_match'], $kiw_vips) == true){

            pms_logger("CHECK-IN confirmed. Room [ {$kiw_response['room_no']} ] Guest [ {$kiw_response['guest_name']} ].", "rhealta", $kiw_tenant);

        } else {

            pms_logger("CHECK-IN failed. Room [ {$kiw_response['room_no']} ] Guest [ {$kiw_response['guest_name']} ].", "rhealta", $kiw_tenant);

            die(json_encode(array("status" => "CHECK-IN failed.")));

        }


    } elseif ($kiw_data['mode'] == "update"){


        if (pms_update_info($kiw_db, "rhealta", $kiw_tenant, $kiw_response) == true){

            pms_logger("UPDATE-ROOM confirmed. Room [ {$kiw_response['room_no']} ].", "rhealta", $kiw_tenant);

        } else {

            pms_logger("UPDATE-ROOM failed. Room [ {$kiw_response['room_no']} ].", "rhealta", $kiw_tenant);

            die(json_encode(array("status" => "UPDATE-ROOM failed.")));

        }


    } elseif ($kiw_data['mode'] == "checkout"){


        if (pms_check_out($kiw_db, "rhealta", $kiw_tenant, $kiw_response) == true){

            pms_logger("CHECK-OUT confirmed. Room [ {$kiw_response['room_no']} ].", "rhealta", $kiw_tenant);

        } else {


            pms_logger("CHECK-OUT failed. Room [ {$kiw_response['room_no']} ].", "rhealta", $kiw_tenant);

            die(json_encode(array("status" => "CHECK-OUT failed.")));


        }


    }


    echo json_encode(array("status" => "ok"));


} else {


    echo json_encode(array("status" => "no data"));


}


