<?php

// new api, no login need to be perform. counter party can only straight push data directly
// however, they need to always use https connection


header("Content-Type: application/json");


$request_module = explode("/", $_REQUEST['request']);

$request_module = array_filter($request_module);


$request_module_path = dirname(__FILE__) . "/{$request_module[0]}.php";



if (file_exists($request_module_path) == true) {


    require_once dirname(__FILE__, 2) . "/admin/includes/include_connection.php";

    $kiw_request['method'] = strtoupper($_SERVER['REQUEST_METHOD']);


    $kiw_header = getallheaders();


    foreach ($kiw_header as $kiw_key => $kiw_value) {

        if ($kiw_key == "K-Api-Key") {

            $kiw_request['api_key'] = $kiw_db->escape($kiw_value);

        } elseif ($kiw_key == "K-Api-Tenant"){

            $kiw_request['tenant'] = $kiw_db->escape($kiw_value);

        }

    }

    unset($kiw_key);
    unset($kiw_value);
    unset($kiw_header);


    if ($_SERVER['HTTPS'] == "on") {


        if (!empty($kiw_request['api_key'])) {


            // check api setting for the tenant

            $kiw_api = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_int_api_setting WHERE api_key = '{$kiw_request['api_key']}' AND tenant_id = '{$kiw_request['tenant']}' LIMIT 1");


            if ($kiw_api['api_key'] == $kiw_request['api_key']) {


                if ($kiw_api['enabled'] == "y") {


                    // check permission for this api key

                    $kiw_permitted = false;

                    if ($kiw_api['permission'] !== "rw") {


                        if ($kiw_api['permission'] == "r" && $kiw_request['method'] == "GET") $kiw_permitted = true;
                        elseif ($kiw_api['permission'] == "w" && $kiw_request['method'] !== "GET") $kiw_permitted = true;


                    } else $kiw_permitted = true;


                    if ($kiw_permitted) {


                        $kiw_roles = $kiw_cache->get("API_ROLES:{$kiw_api['tenant_id']}:{$kiw_request['api_key']}");


                        if (empty($kiw_roles)){


                            $kiw_roles_x = $kiw_db->fetch_array("SELECT * FROM kiwire_admin_group WHERE tenant_id = '{$kiw_api['tenant_id']}' AND groupname = '{$kiw_api['module']}'");

                            foreach ($kiw_roles_x as $kiw_role_x){

                                $kiw_roles[] = $kiw_role_x['moduleid'];

                            }

                            $kiw_cache->set("API_ROLES:{$kiw_api['tenant_id']}:{$kiw_request['api_key']}", $kiw_roles, 1800);


                        }


                        logger_api("general","[ " . explode("-", $kiw_api['api_key'])[0] . " ] [ {$kiw_request['method']} ] {$_SERVER['REQUEST_URI']}");


                        // load the module script to process

                        require_once "{$request_module_path}";


                    } else die(json_encode(array("status" => "error", "message" => "This API key not allowed to perform this action [ {$kiw_request['method']} ]", "data" => "")));


                } else die(json_encode(array("status" => "error", "message" => "API is disabled in system", "data" => "")));


            } else die(json_encode(array("status" => "error", "message" => "Wrong API key provided", "data" => "")));


        } else die(json_encode(array("status" => "error", "message" => "Missing API key", "data" => "")));


    } else die(json_encode(array("status" => "error", "message" => "Please use encrypted connection [ https ] to proceed", "data" => "")));


} else die(json_encode(array("status" => "error", "message" => "Module not existed", "data" => "")));



function logger_api($kiw_tenant_id = "", $kiw_message = ""){


    if (!empty($kiw_message) && !empty($kiw_tenant_id)) {


        $kiw_path = dirname(__FILE__, 3) . "/logs/api/{$kiw_tenant_id}";


        if (file_exists($kiw_path) == false) {

            mkdir($kiw_path, 0755, true);

        }


        @file_put_contents("{$kiw_path}/api-request-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s") . " :: {$kiw_message}\n", FILE_APPEND);


    }


}