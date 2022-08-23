<?php

global $kiw_request, $kiw_api, $kiw_roles;


require_once dirname(__FILE__, 2) . "/admin/includes/include_general.php";


if (in_array("Account -> Voucher -> List", $kiw_roles) == false) {

    die(json_encode(array("status" => "error", "message" => "This API key not allowed to access this module [ {$request_module[0]} ]", "data" => "")));

}


if ($kiw_request['method'] == "GET") {


    if ($kiw_request['tenant'] !== "superuser") {

        $kiw_tenant_query = "WHERE ktype = 'voucher' AND tenant_id = '{$kiw_request['tenant']}'";

    } else $kiw_tenant_query = "WHERE ktype = 'voucher'";


    if (count($request_module) == 2) {

        $kiw_request['id'] = $kiw_db->escape($request_module[1]);

        if (empty($kiw_tenant_query)) $kiw_tenant_query = "WHERE (id = '{$kiw_request['id']}' OR username = '{$kiw_request['id']}')";
        else $kiw_tenant_query .= " AND (id = '{$kiw_request['id']}' OR username = '{$kiw_request['id']}')";

        $kiw_data = $kiw_db->query_first("SELECT id,
                                        tenant_id,
                                        CONVERT_TZ(updated_date,'+00:00','+8:00') as updated_date,
                                        creator,
                                        username,
                                        fullname,
                                        email_address,
                                        phone_number,
                                        password,
                                        remark,
                                        profile_subs,
                                        profile_curr,
                                        profile_cus,
                                        price,
                                        ktype,
                                        bulk_id,
                                        status,
                                        integration,
                                        allowed_zone,
                                        allowed_mac,
                                        CONVERT_TZ(date_create,'+00:00','+8:00') as date_create,
                                        CONVERT_TZ(date_value,'+00:00','+8:00') as date_value,
                                        CONVERT_TZ(date_expiry,'+00:00','+8:00') as date_expiry,
                                        CONVERT_TZ(date_last_login,'+00:00','+8:00') as date_last_login,
                                        CONVERT_TZ(date_last_logout,'+00:00','+8:00') as date_last_logout,
                                        CONVERT_TZ(date_activate,'+00:00','+8:00') as date_activate,
                                        CONVERT_TZ(date_remove,'+00:00','+8:00') as date_remove,
                                        CONVERT_TZ(date_password,'+00:00','+8:00') as date_password,
                                        session_time,
                                        quota_in,
                                        quota_out,
                                        login,
                                        password_history,
                                        total_outstanding,
                                        campaign_history
                                        FROM kiwire_account_auth {$kiw_tenant_query} LIMIT 1");


        unset($kiw_data[0]['password']);

        echo json_encode(array("status" => "success", "message" => "", "data" => $kiw_data));



    } else if (count($request_module) == 3) {


        if ($request_module[1] == "bulk_id") {

            $kiw_request['bulk_id'] = $kiw_db->escape($request_module[2]);

            if (empty($kiw_tenant_query)) $kiw_tenant_query = "WHERE (bulk_id = '{$kiw_request['bulk_id']}')";
            else $kiw_tenant_query .= " AND (bulk_id = '{$kiw_request['bulk_id']}')";

           
            
            $kiw_data = $kiw_db->fetch_array("SELECT username FROM kiwire_account_auth {$kiw_tenant_query}");


            for ($kiw_c = 0; $kiw_c < count($kiw_data); $kiw_c++) {

                unset($kiw_data[$kiw_c]['password']);

                $kiw_count = count($kiw_data);

                
            }

            if ($kiw_count > 0) {

                echo json_encode(array("status" => "success", "message" => "Display {$kiw_count} of vouchers with bulk id {$kiw_request['bulk_id']}", "data" => $kiw_data));

            } else {

                echo json_encode(array("status" => "error", "message" => "Data not exist", "data" => ""));


            }

        

        } else {

            echo json_encode(array("status" => "error", "message" => "Invalid url. Please use valid url to get voucher list by bulk id", "data" => ""));

        }

        
        
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
                                            tenant_id,
                                            CONVERT_TZ(updated_date,'+00:00','+8:00') as updated_date,
                                            creator,
                                            username,
                                            fullname,
                                            email_address,
                                            phone_number,
                                            password,
                                            remark,
                                            profile_subs,
                                            profile_curr,
                                            profile_cus,
                                            price,
                                            ktype,
                                            bulk_id,
                                            status,
                                            integration,
                                            allowed_zone,
                                            allowed_mac,
                                            CONVERT_TZ(date_create,'+00:00','+8:00') as date_create,
                                            CONVERT_TZ(date_value,'+00:00','+8:00') as date_value,
                                            CONVERT_TZ(date_expiry,'+00:00','+8:00') as date_expiry,
                                            CONVERT_TZ(date_last_login,'+00:00','+8:00') as date_last_login,
                                            CONVERT_TZ(date_last_logout,'+00:00','+8:00') as date_last_logout,
                                            CONVERT_TZ(date_activate,'+00:00','+8:00') as date_activate,
                                            CONVERT_TZ(date_remove,'+00:00','+8:00') as date_remove,
                                            CONVERT_TZ(date_password,'+00:00','+8:00') as date_password,
                                            session_time,
                                            quota_in,
                                            quota_out,
                                            login,
                                            password_history,
                                            total_outstanding,
                                            campaign_history
                                            FROM kiwire_account_auth {$kiw_tenant_query} ORDER BY {$kiw_config['column']} {$kiw_config['order']} LIMIT {$kiw_config['offset']}, {$kiw_config['limit']}");


        for ($kiw_c = 0; $kiw_c < count($kiw_data); $kiw_c++) {

            unset($kiw_data[$kiw_c]['password']);

        }

        echo json_encode(array("status" => "success", "message" => "", "data" => $kiw_data));

    }

    

} elseif ($kiw_request['method'] == "POST") {


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


        if($kiw_data['quantity'] > 100) {

            echo json_encode(array("status" => "success", "message" => "Successfully generated {$kiw_data['quantity']} vouchers", "bulk-id" => $kiw_creation['voucher']));

        } else {

            echo json_encode(array("status" => "success", "message" => "Successfully generated {$kiw_data['quantity']} vouchers", "data" => $kiw_creation['voucher']));

        }

        logger_api($kiw_api['tenant_id'], "{$kiw_request['api_key']} created voucher {$kiw_data['username']}");


    } else {

        echo json_encode(array("status" => "error", "message" => "Fail to generate voucher", "data" => ""));

    }

    unset($kiw_data);



} elseif ($kiw_request['method'] == "DELETE") {


    if (count($request_module) == 2) {

        $kiw_request['id'] = $kiw_db->escape($request_module[1]);


        // if not superuser then need to use tenant

        if ($kiw_request['tenant'] !== "superuser") {

            $kiw_tenant_query = "WHERE tenant_id = '{$kiw_request['tenant']}'";

        } else $kiw_tenant_query = "";


        if (empty($kiw_tenant_query)) $kiw_tenant_query = "WHERE username = '{$kiw_request['id']}' AND ktype = 'voucher'";
        else $kiw_tenant_query .= " AND username = '{$kiw_request['id']}' AND ktype = 'voucher'";


        $kiw_name = $kiw_db->query_first("SELECT * FROM kiwire_account_auth {$kiw_tenant_query} LIMIT 1");
 
        $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), status = 'suspend' {$kiw_tenant_query} LIMIT 1");


        if ($kiw_db->db_affected_row > 0) {

            echo json_encode(array("status" => "success", "message" => "Voucher have been suspended", "data" => null));

            logger_api($kiw_api['tenant_id'], "{$kiw_request['api_key']} deleted voucher {$kiw_name['username']}");


        } else {

            echo json_encode(array("status" => "error", "message" => "Missing voucher code to process this request", "data" => $kiw_name));

        }


    } else {

        echo json_encode(array("status" => "error", "message" => "Voucher not existed", "data" => "")); 

    }

}