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

    kiw_print_response(false, "Invalid tenant info has been provided");

}



require_once dirname(__FILE__, 5) . "/admin/includes/include_config.php";

require_once dirname(__FILE__, 6) . "/system/pms/kiwire_pms_functions.php";


// set the content type to be json format

header("Content-Type: application/json");


// start connections to db and cache

$kiw_cache = new Redis();

$kiw_cache->connect(SYNC_REDIS_HOST, SYNC_REDIS_PORT);

$kiw_cache->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);


$kiw_db = new mysqli(SYNC_DB1_HOST, SYNC_DB1_USER, SYNC_DB1_PASSWORD, SYNC_DB1_DATABASE, SYNC_DB1_PORT);



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

if ($kiw_pms_setting['enabled'] != "y" || $kiw_pms_setting['pms_type'] != "idb") {

    kiw_print_response(false, "PMS setting has been disabled");

}


// get the data from input stream since we will received json

$kiw_data = file_get_contents("php://input");

$kiw_data = json_decode($kiw_data, true);


// if not valid json then ignore and print error

if ($kiw_data){


    pms_logger("RX: " . json_encode($kiw_data), "idb", $kiw_tenant);


    // do check for essential attribute

    if (!isset($kiw_data['COMMAND']) || empty($kiw_data['COMMAND'])){

        kiw_print_response(false, "Required key COMMAND is empty");

    } elseif (!isset($kiw_data['ROOM']) || empty($kiw_data['ROOM'])){

        kiw_print_response(false, "Required key ROOM is empty");

    }


    // if in-house change then check couple of variables

    if (in_array($kiw_data['COMMAND'], array("CHECK_IN", "INHOUSE_CHANGE"))){


        if (empty($kiw_data['GUEST'])) {

            kiw_print_response(false, 'Required key GUEST is empty');

        } elseif (empty($kiw_data['LASTNAME'])) {

            kiw_print_response(false, 'Required key LASTNAME is empty');

        } elseif (empty($kiw_data['ARRDATE'])) {

            kiw_print_response(false, 'Required key ARRDATE is empty');

        } elseif (empty($kiw_data['DEPDATE'])) {

            kiw_print_response(false, 'Required key DEPDATE is empty');

        } elseif (!isset($kiw_data['VIP'])) {

            kiw_print_response(false, 'Required key VIP is empty');

        } elseif (kiw_check_date($kiw_data['ARRDATE']) === false) {

            kiw_print_response(false, 'Invalid value for ARRDATE key, acceptable format is YYMMDD');

        } elseif (kiw_check_date($kiw_data['DEPDATE']) === false) {

            kiw_print_response(false, 'Invalid value for DEPDATE key, acceptable format is YYMMDD');

        } elseif (!isset($kiw_data['VIP'])) {

            kiw_print_response(false, 'Invalid value for VIP key, must be an integer');

        }


    }


    // check command if support

    if (!in_array($kiw_data['COMMAND'], array("CHECK_IN", "INHOUSE_CHANGE", "CHECK_OUT"))){

        kiw_print_response(false, 'Unknown value for COMMAND key, acceptable values are CHECK_IN, INHOUSE_CHANGE and CHECK_OUT');

    }


    $kiw_response = array();


    // get the pms password based on setting

    switch ($kiw_pms_setting['pass_mode']){

        case 0: $kiw_response['password'] = $kiw_pms_setting['pass_predefined']; break;
        case 1: $kiw_response['password'] = $kiw_data['room_no']; break;
        default: $kiw_response['password'] = pms_password(); break;

    }


    if (isset($kiw_data['NEWROOM'])) {

        $kiw_response['room_no']   = $kiw_db->escape_string($kiw_data['NEWROOM']);
        $kiw_response['room_old']  = $kiw_db->escape_string($kiw_data['ROOM']);

    } else $kiw_response['room_no'] = $kiw_db->escape_string($kiw_data['ROOM']);


    $kiw_response['guest_first']   = $kiw_db->escape_string($kiw_data['GUEST']);
    $kiw_response['guest_last']    = $kiw_db->escape_string($kiw_data['LASTNAME']);
    $kiw_response['guest_arrival'] = $kiw_db->escape_string($kiw_data['ARRDATE']);
    $kiw_response['guest_vip']     = $kiw_db->escape_string($kiw_data['VIP']);

    $kiw_response['guest_name']    = "{$kiw_response['guest_first']}, {$kiw_response['guest_last']}";


    // execute pms instruction (check in, check out or change info)

    if ($kiw_data['COMMAND'] == "CHECK_IN"){


        if (pms_check_in($kiw_db, "idb", $kiw_tenant, $kiw_response, $kiw_pms_setting['vip_match'], $kiw_vips) == true){

            pms_logger("CHECK-IN confirmed. Room [ {$kiw_response['room_no']} ] Guest [ {$kiw_response['guest_name']} ].", "idb", $kiw_tenant);

        } else {

            pms_logger("CHECK-IN failed. Room [ {$kiw_response['room_no']} ] Guest [ {$kiw_response['guest_name']} ].", "idb", $kiw_tenant);

            kiw_print_response(false, "CHECK-IN failed.");

        }


    } elseif ($kiw_data['COMMAND'] == "INHOUSE_CHANGE"){

        
        if (pms_move_room($kiw_db, "idb", $kiw_tenant, $kiw_response['room_old'], $kiw_response['room_no']) == true){

            pms_logger("CHANGE-ROOM confirmed. Room [ {$kiw_response['room_no']} ].", "idb", $kiw_tenant);

        } else {

            pms_logger("CHANGE-ROOM failed. Room [ {$kiw_response['room_no']} ].", "idb", $kiw_tenant);

            kiw_print_response(false, "CHANGE-ROOM failed.");

        }
        

    } elseif ($kiw_data['COMMAND'] == "CHECK_OUT"){


        if (pms_check_out($kiw_db, "idb", $kiw_tenant, $kiw_response) == true){

            pms_logger("CHECK-OUT confirmed. Room [ {$kiw_response['room_no']} ].", "idb", $kiw_tenant);

        } else {


            pms_logger("CHECK-OUT failed. Room [ {$kiw_response['room_no']} ].", "idb", $kiw_tenant);

            kiw_print_response(false, "CHECK-OUT failed.");


        }
        

    }


    echo json_encode(array("status" => 1, "message" => ""));


} else {

    echo "POST data is empty";

}



function kiw_check_date($kiw_date){


    $kiw_test = DateTime::createFromFormat("ymd", $kiw_date);

    return is_object($kiw_test);


}

function kiw_print_response($kiw_status = false, $kiw_message = ""){

    die(json_encode(array("status" => ($kiw_status == true) ? 1 : 0, "message" => $kiw_message)));

}