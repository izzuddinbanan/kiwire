<?php


global $kiw_request, $kiw_api, $kiw_roles;


if (in_array("Configuration -> Access Level", $kiw_roles) == false) {

    die(json_encode(array("status" => "error", "message" => "This API key not allowed to access this module [ {$request_module[0]} ]", "data" => "")));

}



if ($kiw_request['method'] == "GET") {


    if ($request_module[1] == "list_module") {


        $kiw_data = $kiw_db->fetch_array("SELECT moduleid,mod_group AS module_group FROM kiwire_moduleid");


    } elseif (count($request_module) == 3) {


        if ($kiw_request['tenant'] !== "superuser") {

            if ($kiw_request['tenant'] != $request_module[1]){

                die(json_encode(array("status" => "error", "message" => "You can only access your own tenant", "data" => "")));

            }

        }


        $kiw_request['tenant_id'] = $kiw_db->escape($request_module[1]);
        $kiw_request['groupname'] = $kiw_db->escape($request_module[2]);

        $kiw_data_dbs = $kiw_db->fetch_array("SELECT moduleid FROM kiwire_admin_group WHERE tenant_id = '{$kiw_request['tenant_id']}' AND groupname = '{$kiw_request['groupname']}'");


        foreach ($kiw_data_dbs as $kiw_data_db){

            $kiw_data[] = $kiw_data_db['moduleid'];

        }


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


        if ($kiw_request['tenant'] !== "superuser") {

            $kiw_tenant_query = "WHERE tenant_id = '{$kiw_request['tenant']}'";

        } else $kiw_tenant_query = "";


        $kiw_data = $kiw_db->fetch_array("SELECT groupname,tenant_id,COUNT(moduleid) as total_permission FROM kiwire_admin_group {$kiw_tenant_query} GROUP BY groupname,tenant_id ORDER BY {$kiw_config['column']} {$kiw_config['order']} LIMIT {$kiw_config['offset']}, {$kiw_config['limit']}");


    }


    echo json_encode(array("status" => "success", "message" => "", "data" => $kiw_data));


} elseif ($kiw_request['method'] == "POST") {


    // check for required info to execute

    $_REQUEST = file_get_contents("php://input");

    $_REQUEST = json_decode($_REQUEST, true);


    foreach (array("tenant_id", "groupname") as $kiw_key) {


        if (empty($_REQUEST[$kiw_key])) {


            die(json_encode(array("status" => "error", "message" => "Missing required data [ {$kiw_key} ]", "data" => "")));


        } else $kiw_data[$kiw_key] = $kiw_db->escape($_REQUEST[$kiw_key]);


    }


    if ($kiw_request['tenant'] !== "superuser") {

        if ($kiw_data['tenant_id'] !== $kiw_request['tenant']){

            die(json_encode(array("status" => "error", "message" => "You can only access your own tenant", "data" => "")));

        }

    }


    // check if the name already been used

    $kiw_test = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_admin_group WHERE groupname = '{$kiw_data['groupname']}' AND tenant_id = '{$kiw_data['tenant_id']}'");


    if ($kiw_test['kcount'] == 0) {


        if (is_array($_REQUEST['module_list']) && COUNT($_REQUEST['module_list']) > 0) {


            foreach ($_REQUEST['module_list'] as $kiw_list){


                $kiw_list = $kiw_db->escape($kiw_list);

                $kiw_db->query("INSERT INTO kiwire_admin_group VALUE (NULL, '{$kiw_data['groupname']}', '{$kiw_list}', '{$kiw_data['tenant_id']}', NULL)");


            }

            echo json_encode(array("status" => "success", "message" => "", "data" => ""));

            logger_api($kiw_api['tenant_id'], "{$kiw_request['api_key']} created role {$kiw_data['groupname']}");


        } else echo json_encode(array("status" => "error", "message" => "Missing required data [ module_list ]", "data" => ""));


    } else {

         echo json_encode(array("status" => "error", "message" => "Name already existed", "data" => ""));

    }


} elseif ($kiw_request['method'] == "DELETE") {


    if (count($request_module) == 3) {


        if ($kiw_request['tenant'] !== "superuser") {

            if ($kiw_request['tenant'] != $request_module[1]){

                die(json_encode(array("status" => "error", "message" => "You can only access your own tenant", "data" => "")));

            }

        }


        $kiw_request['tenant_id'] = $kiw_db->escape($request_module[1]);
        $kiw_request['groupname'] = $kiw_db->escape($request_module[2]);

        $kiw_name = $kiw_db->query_first("SELECT groupname FROM kiwire_admin_group LIMIT 1");
 
        $kiw_db->query("DELETE FROM kiwire_admin_group WHERE tenant_id = '{$kiw_request['tenant_id']}' AND groupname = '{$kiw_request['groupname']}'");
       

        if ($kiw_db->db_affected_row > 0) {

            echo json_encode(array("status" => "success", "message" => "", "data" => ""));

            logger_api($kiw_api['tenant_id'], "{$kiw_request['api_key']} deleted role {$kiw_name['groupname']}");


        } else {

            echo json_encode(array("status" => "error", "message" => "Id not existed", "data" => ""));

        }


    } else {

        echo json_encode(array("status" => "error", "message" => "Id not existed", "data" => ""));

    }


}
