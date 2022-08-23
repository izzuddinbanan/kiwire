<?php

header("Content-Type: application/json");

require_once dirname(__FILE__, 4) . "/admin/includes/include_config.php";
require_once dirname(__FILE__, 4) . "/admin/includes/include_general.php";
require_once dirname(__FILE__, 4) . "/admin/includes/include_connection.php";
require_once dirname(__FILE__, 4) . "/user/includes/include_radius.php";
require_once dirname(__FILE__, 4) . "/libs/class.sql.helper.php";


// CHECK TENANT 
$kiw_tenant = dirname(__FILE__);
$kiw_tenant = explode("/", $kiw_tenant);
$kiw_tenant = $kiw_tenant[count($kiw_tenant) - 2];



$kiw_header = getallheaders();


foreach ($kiw_header as $kiw_key => $kiw_value) {

    if ($kiw_key == "K-Api-Key") {

        $kiw_request['api_key'] = $kiw_db->escape($kiw_value);

    } elseif ($kiw_key == "K-Api-Tenant"){

        $kiw_request['tenant'] = $kiw_db->escape($kiw_value);


    } elseif ($kiw_key == "K-Type"){

        $kiw_request['type'] = $kiw_db->escape($kiw_value);


    }

}

if(empty($kiw_request['tenant'])) die(json_encode(base_api_respond([], false, "Missing Tenant")));
if($kiw_tenant != $kiw_request['tenant']) die(json_encode(base_api_respond([], false, "Invalid Tenant")));

if(empty($kiw_request['type'])) die(json_encode(base_api_respond([], false, "Missing API Type")));
if(!in_array($kiw_request['type'], ["connectme"])) die(json_encode(base_api_respond([], false, "Invalid API")));


unset($kiw_key);
unset($kiw_value);
unset($kiw_header);




if ($_SERVER['HTTPS'] != "on") {

    if (!empty($kiw_request['api_key'])) {


        // check api setting for the tenant

        $kiw_api = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_int_api_setting WHERE api_key = '{$kiw_request['api_key']}' AND tenant_id = '{$kiw_request['tenant']}' LIMIT 1");


        if ($kiw_api['api_key'] == $kiw_request['api_key']) {


            if ($kiw_api['enabled'] == "y") {

                $kiw_request['method'] = strtoupper($_SERVER['REQUEST_METHOD']);

                if($kiw_request["type"] == "connectme") {

                    if($kiw_request['method'] != "POST") die(json_encode(base_api_respond([], false, "Method are not allowed for this action.")));

                    // IN POSTMAN , test in body -> raw -> json
                    $_REQUEST = file_get_contents("php://input");
                    $_REQUEST = json_decode($_REQUEST, true);


                    foreach (array("ssid","device_mac_address", "ip_address") as $kiw_key) {

                        if (empty($_REQUEST[$kiw_key])) {

                            die(json_encode(base_api_respond([], false, "Missing required data [ {$kiw_key} ]")));
                    
                        } else $kiw_data[$kiw_key] = $kiw_db->escape($_REQUEST[$kiw_key]);

                    }


                    $kiw_device = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_controller WHERE unique_id = '{$kiw_data['ssid']}' AND tenant_id = '{$kiw_request['tenant']}' LIMIT 1");

                    
                    if(empty($kiw_device)) die(json_encode(base_api_respond([], false, "Wrong SSID.")));

                    else {
                        // UPDATE USER PROFILE HERE

                        $kiw_data['device_mac_address'] = preg_replace("/[^a-zA-Z0-9]+/", "", $kiw_data['device_mac_address']);
                        

                        $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), profile_curr = 'registered', profile_subs = 'registered' WHERE username = '{$kiw_data['device_mac_address']}' AND tenant_id = '{$kiw_request['tenant']}' LIMIT 1");
                        

                        // SEND COA HERE
                        coa_user($kiw_db, $kiw_cache, $kiw_request['tenant'], $kiw_data['device_mac_address'], 'registered');

                        // SEND API RESPOND
                        die(json_encode(base_api_respond([], true, "Success.")));


                    }

                }


                die(json_encode(base_api_respond([], false, "Please contact our administrator.")));

                



            } else die(json_encode(base_api_respond([], false, "This API key not allowed to perform this action [ {$kiw_request['method']} ]")));


        } else die(json_encode(base_api_respond([], false, "Wrong API key provided")));


    } else die(json_encode(base_api_respond([], false, "Missing API key")));


} else die(json_encode(base_api_respond([], false, "Please use encrypted connection [ https ] to proceed")));








