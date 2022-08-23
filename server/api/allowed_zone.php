<?php


global $kiw_request, $kiw_api, $kiw_roles;


if (in_array("Policy -> Zone Restriction", $kiw_roles) == false) {

    die(json_encode(array("status" => "error", "message" => "This API key not allowed to access this module [ {$request_module[0]} ]", "data" => "")));

}


if ($kiw_request['method'] == "GET") {


    if ($kiw_request['tenant'] !== "superuser") {

        $kiw_tenant_query = "WHERE tenant_id = '{$kiw_request['tenant']}'";

    } else $kiw_tenant_query = "";


    if (count($request_module) == 2) {


        $kiw_request['id'] = $kiw_db->escape($request_module[1]);


        if (empty($kiw_tenant_query)) $kiw_tenant_query = "WHERE id = '{$kiw_request['id']}'";
        else $kiw_tenant_query .= " AND id = '{$kiw_request['id']}'";


        $kiw_data = $kiw_db->query_first("SELECT * FROM kiwire_allowed_zone {$kiw_tenant_query} LIMIT 1");
 

    } else {


        if (count($request_module) > 2) {

            $kiw_config['offset']   = (int)$request_module[1];
            $kiw_config['limit']    = (int)$request_module[2];
            $kiw_config['column']   = $kiw_db->escape($request_module[3]);
            $kiw_config['order']    = strtolower($request_module[4]) == "asc" ? "ASC" : "DESC";

        } else {

            $kiw_config['limit']    = 10;
            $kiw_config['offset']   = 0;
            $kiw_config['column']   = "id";
            $kiw_config['order']    = "DESC";

        }

      
        $kiw_data = $kiw_db->fetch_array("SELECT * FROM kiwire_allowed_zone {$kiw_tenant_query} ORDER BY {$kiw_config['column']} {$kiw_config['order']} LIMIT {$kiw_config['offset']}, {$kiw_config['limit']}");


    }
   

    echo json_encode(array("status" => "success", "message" => "", "data" => $kiw_data));


} elseif ($kiw_request['method'] == "POST") {


    // check for required info to execute

    $_REQUEST = file_get_contents("php://input");

    $_REQUEST = json_decode($_REQUEST, true);


    foreach (array("tenant_id", "name", "zone") as $kiw_key) {


        if (empty($_REQUEST[$kiw_key])) {


            die(json_encode(array("status" => "error", "message" => "Missing required data [ {$kiw_key} ]", "data" => "")));


        } else $kiw_data[$kiw_key] = $kiw_db->escape($_REQUEST[$kiw_key]);


    }


    // add remaining variable set

    foreach ($_REQUEST as $kiw_key => $kiw_value){

        if (!isset($kiw_data[$kiw_key]) && !in_array($kiw_key, array("id", "updated_date"))){

            $kiw_data[$kiw_key] = $kiw_db->escape($kiw_value);

        }


    }


    if ($kiw_request['tenant'] !== "superuser") {

        if ($kiw_data['tenant_id'] !== $kiw_request['tenant']){

            die(json_encode(array("status" => "error", "message" => "You can only access your own tenant", "data" => "")));

        }

    }


    // check if the name already been used

    $kiw_test = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_allowed_zone WHERE name = '{$kiw_data['name']}' AND tenant_id = '{$kiw_data['tenant_id']}'");


    if ($kiw_test['kcount'] == 0) {


        $kiw_db->insert("kiwire_allowed_zone", $kiw_data);


        if ($kiw_db->db_affected_row > 0) {

            echo json_encode(array("status" => "success", "message" => "", "data" => ""));

            logger_api($kiw_api['tenant_id'], "{$kiw_request['api_key']} created allowed zone {$kiw_data['name']}");


        } else echo json_encode(array("status" => "error", "message" => "", "data" => ""));


    } else {


         echo json_encode(array("status" => "success", "message" => "Name already existed", "data" => ""));

    }



} elseif ($kiw_request['method'] == "PATCH") {


    // check for required info to execute

    $_REQUEST = file_get_contents("php://input");

    $_REQUEST = json_decode($_REQUEST, true);


    foreach (array("tenant_id") as $kiw_key) {


        if (empty($_REQUEST[$kiw_key])) {


            die(json_encode(array("status" => "error", "message" => "Missing required data [ {$kiw_key} ]", "data" => "")));
    
    
        } else $kiw_data[$kiw_key] = $kiw_db->escape($_REQUEST[$kiw_key]);
   
   
    }


    // add remaining variable set

    foreach ($_REQUEST as $kiw_key => $kiw_value){

        if (!isset($kiw_data[$kiw_key]) && !in_array($kiw_key, array("id", "updated_date", "name"))){

            $kiw_data[$kiw_key] = $kiw_db->escape($kiw_value);

        }


    }


    if ($kiw_request['tenant'] !== "superuser") {

        if ($kiw_data['tenant_id'] !== $kiw_request['tenant']){

            die(json_encode(array("status" => "error", "message" => "You can only access your own tenant", "data" => "")));

        }

    }


    if (count($request_module) == 2) {


        $kiw_request['id'] = $kiw_db->escape($request_module[1]);

        $kiw_db->update("kiwire_allowed_zone", $kiw_data, "id = '{$kiw_request['id']}' AND tenant_id = '{$kiw_data['tenant_id']}'");


        if ($kiw_db->db_affected_row > 0) {

            echo json_encode(array("status" => "success", "message" => "", "data" => ""));

            logger_api($kiw_api['tenant_id'], "{$kiw_request['api_key']} updated allowed zone {$kiw_request['name']}");


        } else echo json_encode(array("status" => "error", "message" => "", "data" => ""));

 
    } else die(json_encode(array("status" => "error", "message" => "Missing ID for this request", "data" => "")));


}  elseif ($kiw_request['method'] == "DELETE") {


    if (count($request_module) == 2) {


        $kiw_request['id'] = $kiw_db->escape($request_module[1]);


        // if not superuser then need to use tenant

        if ($kiw_request['tenant'] !== "superuser") {

            $kiw_tenant_query = "WHERE tenant_id = '{$kiw_request['tenant']}'";

        } else $kiw_tenant_query = "";


        if (empty($kiw_tenant_query)) $kiw_tenant_query = "WHERE id = '{$kiw_request['id']}'";
        else $kiw_tenant_query .= " AND id = '{$kiw_request['id']}'";

        $kiw_name = $kiw_db->query_first("SELECT name FROM kiwire_allowed_zone {$kiw_tenant_query} LIMIT 1");
 
        $kiw_db->query("DELETE FROM kiwire_allowed_zone {$kiw_tenant_query} LIMIT 1");
       
        
        if ($kiw_db->db_affected_row > 0) {

            echo json_encode(array("status" => "success", "message" => "", "data" => ""));
          
            logger_api($kiw_api['tenant_id'], "{$kiw_request['api_key']} deleted allowed zone {$kiw_name['name']}");


        } else {

            echo json_encode(array("status" => "error", "message" => "Id not existed", "data" => ""));

        }


    } else {

        echo json_encode(array("status" => "error", "message" => "ID not existed", "data" => ""));

    }


}