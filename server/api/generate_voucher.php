<?php

global $kiw_request, $kiw_api, $kiw_roles;

require_once dirname(__FILE__, 2) . "/admin/includes/include_general.php";


if (in_array("Account -> Voucher -> List", $kiw_roles) == false) {

    die(json_encode(array("status" => "error", "message" => "This API key not allowed to access this module [ {$request_module[0]} ]", "data" => "")));

}


if ($kiw_request['method'] == "POST") {


    // check for required info to execute

    $_REQUEST = file_get_contents("php://input");

    $_REQUEST = json_decode($_REQUEST, true);


    foreach (array("tenant_id", "prefix", "quantity", "profile", "zone", "remark", "expiry_date") as $kiw_key) {


        if (empty($_REQUEST[$kiw_key])) {

            die(json_encode(array("status" => "error", "message" => "Missing required data [ {$kiw_key} ]", "data" => "")));

        } else $kiw_data[$kiw_key] = $kiw_db->escape($_REQUEST[$kiw_key]);


    }


    if ($kiw_request['tenant'] !== "superuser") {

        if ($kiw_data['tenant_id'] !== $kiw_request['tenant']){

            die(json_encode(array("status" => "error", "message" => "You can only access your own tenant", "data" => "")));

        }

    }


    $kiw_profile = $kiw_db->query_first("SELECT * FROM kiwire_profiles WHERE tenant_id = '{$kiw_api['tenant_id']}' AND name = '{$kiw_data['profile']}' LIMIT 1");


    $kiw_data['action']         = "create_voucher";
    $kiw_data['tenant_id']      = $kiw_api['tenant_id'];
    $kiw_data['creator']        = "API";
    $kiw_data['quantity']       = (int)$kiw_data['quantity'];
    $kiw_data['price']          = $kiw_profile['price'];
    $kiw_data['allowed_zone']   = $kiw_db->escape($_REQUEST['zone']);
    $kiw_data['remark']         = $kiw_db->escape($_REQUEST['remark']);
    $kiw_data['expiry_date']    = date("Y-m-d H:i:s", strtotime($kiw_data['expiry_date']));


    $kiw_temp = curl_init();

    curl_setopt($kiw_temp, CURLOPT_URL, "http://127.0.0.1:9956");
    curl_setopt($kiw_temp, CURLOPT_POST, true);
    curl_setopt($kiw_temp, CURLOPT_POSTFIELDS, http_build_query($kiw_data));
    curl_setopt($kiw_temp, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($kiw_temp, CURLOPT_TIMEOUT, 15);
    curl_setopt($kiw_temp, CURLOPT_CONNECTTIMEOUT, 5);

    // unset($kiw_data);

    $kiw_creation = curl_exec($kiw_temp);

    curl_close($kiw_temp);


    $kiw_creation = json_decode($kiw_creation, true);


    if ($kiw_creation['status'] == "success") {


        echo json_encode(array("status" => "success", "message" => "", "bulk-id" => $kiw_creation['bulk']));

        logger_api($kiw_api['tenant_id'], "{$kiw_request['api_key']} created {$kiw_data['quantity']} voucher with bulk id {$kiw_creation['bulk']}");


    } else {

        echo json_encode(array("status" => "error", "message" => "Fail to generate voucher", "data" => ""));

    }

    unset($kiw_data);


} else {

    echo json_encode(array("status" => "error", "message" => "Please Use POST method to generate vouchers", "data" => ""));


}

