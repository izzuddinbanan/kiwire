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


    if (count($request_module) == 3) {

        if ($request_module[1] == "bulk_id") {

            $kiw_request['bulk_id'] = $kiw_db->escape($request_module[2]);

            if (empty($kiw_tenant_query)) $kiw_tenant_query = "WHERE (bulk_id = '{$kiw_request['bulk_id']}')";
            else $kiw_tenant_query .= " AND (bulk_id = '{$kiw_request['bulk_id']}')";

           
            
            $kiw_data = $kiw_db->fetch_array("SELECT username FROM kiwire_account_auth {$kiw_tenant_query}");


            for ($kiw_c = 0; $kiw_c < count($kiw_data); $kiw_c++) {

                unset($kiw_data[$kiw_c]['password']);

                $kiw_count = count($kiw_data);

                
            }

            $kiw_qty = $kiw_db->query_first("SELECT * FROM kiwire_voucher_generate WHERE tenant_id = '{$kiw_request['tenant']}' AND bulk_id = '{$kiw_request['bulk_id']}'");


            if ($kiw_count > 0) {


                if ($kiw_count == $kiw_qty['quantity']) {

                    echo json_encode(array("status" => "success", "message" => "Display {$kiw_count} vouchers with bulk id {$kiw_request['bulk_id']}", "data" => $kiw_data));
                

                } else {

                    echo json_encode(array("status" => "error", "message" => "Pending request. Voucher still generating. Please wait for a while to check again.", "data" => null));
                
                }
    

            } else {

                echo json_encode(array("status" => "error", "message" => "Data not exist", "data" => ""));

            }


        } else {

            echo json_encode(array("status" => "error", "message" => "Invalid url. Please use valid url to get voucher list by bulk id", "data" => ""));

        }


    } else {

        echo json_encode(array("status" => "error", "message" => "Wrong API path", "data" => ""));

    }


    unset($kiw_data);


} else {

    echo json_encode(array("status" => "error", "message" => "Please Use GET method to retrieve list of vouchers", "data" => ""));

}




