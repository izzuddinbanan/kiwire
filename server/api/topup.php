<?php

global $kiw_request, $kiw_api, $kiw_roles;


require_once dirname(__FILE__, 2) . "/admin/includes/include_general.php";


if (in_array("Account -> Topup Code", $kiw_roles) == false) {

    die(json_encode(array("status" => "error", "message" => "This API key not allowed to access this module [ {$request_module[0]} ]", "data" => "")));
}


if ($kiw_request['method'] == "GET") {


    if ($kiw_request['tenant'] !== "superuser") {

        $kiw_tenant_query = "WHERE tenant_id = '{$kiw_request['tenant']}'";
    
    } else $kiw_tenant_query = "";


    if (count($request_module) == 2) {

        $kiw_request['id'] = $kiw_db->escape($request_module[1]);

        if (empty($kiw_tenant_query)) $kiw_tenant_query = "WHERE (id = '{$kiw_request['id']}' OR code = '{$kiw_request['id']}')";
        else $kiw_tenant_query .= " AND (id = '{$kiw_request['id']}' OR code = '{$kiw_request['id']}')";

        $kiw_data = $kiw_db->query_first("SELECT id,
                                         CONVERT_TZ(updated_date,'+00:00','+8:00') as updated_date, 
                                         tenant_id, 
                                         creator, 
                                         code, 
                                         status, 
                                         price, 
                                         plan_name, 
                                         CONVERT_TZ(date_create,'+00:00','+8:00') as date_create, 
                                         CONVERT_TZ(date_activate,'+00:00','+8:00') as date_activate, 
                                         CONVERT_TZ(date_expiry,'+00:00','+8:00') as date_expiry, 
                                         bulk_id, 
                                         quota, 
                                         time 
                                         FROM kiwire_topup_code {$kiw_tenant_query} LIMIT 1");
    } else {


        if (count($request_module) > 2) {

            $kiw_config['offset'] = (int)$request_module[1];
            $kiw_config['limit'] = (int)$request_module[2];
            $kiw_config['column'] = $kiw_db->escape($request_module[3]);
            $kiw_config['order'] = strtolower($request_module[4]) == "asc" ? "ASC" : "DESC";
        
        } else {

            $kiw_config['limit'] = 10;
            $kiw_config['offset'] = 0;
            $kiw_config['column'] = "id";
            $kiw_config['order'] = "DESC";
        }

        $kiw_data = $kiw_db->fetch_array("SELECT id,
                                         CONVERT_TZ(updated_date,'+00:00','+8:00') as updated_date, 
                                         tenant_id, 
                                         creator, 
                                         code, 
                                         status, 
                                         price,   
                                         plan_name, 
                                         CONVERT_TZ(date_create,'+00:00','+8:00') as date_create, 
                                         CONVERT_TZ(date_activate,'+00:00','+8:00') as date_activate, 
                                         CONVERT_TZ(date_expiry,'+00:00','+8:00') as date_expiry, 
                                         bulk_id, 
                                         quota, 
                                         time 
                                         FROM kiwire_topup_code {$kiw_tenant_query} ORDER BY {$kiw_config['column']} {$kiw_config['order']} LIMIT {$kiw_config['offset']}, {$kiw_config['limit']}");
    }

    echo json_encode(array("status" => "success", "message" => "", "data" => $kiw_data));


} elseif ($kiw_request['method'] == "POST") {


    // check for required info to execute

    $_REQUEST = file_get_contents("php://input");

    $_REQUEST = json_decode($_REQUEST, true);


    foreach (array("tenant_id", "price", "plan_name", "prefix", "quantity", "code_length", "quota", "time", "remark", "date_expiry") as $kiw_key) {

        if (empty($_REQUEST[$kiw_key])) {

            die(json_encode(array("status" => "error", "message" => "Missing required data [ {$kiw_key} ]", "data" => "")));

        } else $kiw_data[$kiw_key] = $kiw_db->escape($_REQUEST[$kiw_key]);

    }


    if ($kiw_request['tenant'] !== "superuser") {

        if ($kiw_data['tenant_id'] !== $kiw_request['tenant']){

            die(json_encode(array("status" => "error", "message" => "You can only access your own tenant", "data" => "")));

        }

    }


    $kiw_tenant_id      = $kiw_api['tenant_id'];
    $kiw_creator        = "API";
    $kiw_quantity       = (int)$kiw_data['quantity'];
    $kiw_price          = $kiw_data['price'];
    $kiw_plan_name      = $kiw_data['plan_name'];
    $kiw_prefix         = $kiw_data['prefix'];
    $kiw_code_length    = $kiw_data['code_length'];
    $kiw_quota          = $kiw_data['quota'];
    $kiw_time           = $kiw_data['time'];
    $kiw_remark         = $kiw_data['remark'];
    $kiw_date_expiry    = date("Y-m-d H:i:s", strtotime($kiw_data['date_expiry']));


    if ($kiw_quantity > 0 && $kiw_code_length > 0) {


        $random_id = random_string_id($kiw_code_length, 'y');

        $bulk_id = "TP" . $random_id;


        for ($kiw_x = 0; $kiw_x < $kiw_quantity; $kiw_x++){


            while (true) {


                $kiw_code = random_string_id($kiw_code_length, 'y');

                $kiw_code = $kiw_prefix.$kiw_code;

                $kiw_temp = $kiw_db->query_first("SELECT COUNT(*) AS ccount FROM kiwire_topup_code WHERE tenant_id = '{$kiw_tenant_id}' AND code = '{$kiw_code}'");


                if ($kiw_temp['ccount'] == 0) {

                    $kiw_db->query("INSERT INTO kiwire_topup_code(id, price, updated_date, tenant_id, creator, code, status, username, plan_name, date_create, date_activate, date_expiry, bulk_id, quota, time, remark) VALUE (NULL, '{$kiw_price}' ,NOW(), '{$kiw_tenant_id}', '{$kiw_creator}', '{$kiw_code}', 'n', NULL, '{$kiw_plan_name}', NOW(), NULL, '{$kiw_date_expiry}', '{$bulk_id}', '{$kiw_quota}', '{$kiw_time}', '{$kiw_remark}')");

                    break;

                }


            }

            logger_api($kiw_api['tenant_id'], "{$kiw_request['api_key']} created topup code {$kiw_code}");

            echo json_encode(array("status" => "success", "message" => "SUCCESS: New topup code generated", "data" => $kiw_code));


        }


    } else echo json_encode(array("status" => "failed", "message" => "ERROR: Please provide number of code or topup code length to be generated", "data" => null));



} elseif ($kiw_request['method'] == "DELETE") {


    if (count($request_module) == 2) {


        $kiw_request['id'] = $kiw_db->escape($request_module[1]);


        // if not superuser then need to use tenant

        if ($kiw_request['tenant'] !== "superuser") {

            $kiw_tenant_query = "WHERE tenant_id = '{$kiw_request['tenant']}'";

        } else $kiw_tenant_query = "";

        
        if (empty($kiw_tenant_query)) $kiw_tenant_query = "WHERE id = '{$kiw_request['id']}'";
        else $kiw_tenant_query .= " AND id = '{$kiw_request['id']}'";


        $kiw_topup = $kiw_db->query_first("SELECT code FROM kiwire_topup_code WHERE id = '{$kiw_request['id']}' LIMIT 1");

        $kiw_db->query("DELETE FROM kiwire_topup_code {$kiw_tenant_query} LIMIT 1");


        if ($kiw_db->db_affected_row > 0) {

            echo json_encode(array("status" => "success", "message" => "Successfully delete topup code", "data" => $kiw_topup['code']));

            logger_api($kiw_api['tenant_id'], "{$kiw_request['api_key']} deleted topup code {$kiw_topup['code']}");


        } else {

            echo json_encode(array("status" => "error", "message" => "Missing ID to process this request", "data" => ""));

        }


    } else echo json_encode(array("status" => "error", "message" => "ID not existed", "data" => ""));


}






