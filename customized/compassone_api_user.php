<?php


global $kiw_request, $kiw_api, $kiw_roles;


require_once dirname(__FILE__, 2) . "/admin/includes/include_general.php";


if (in_array("Account -> Account -> List", $kiw_roles) == false) {

    die(json_encode(array("status" => "error", "message" => "This API key not allowed to access this module [ {$request_module[0]} ]", "data" => "")));

}


$kiw_required = array(
    "tenant_id",
    "username",
    "fullname",
    "profile_subs",
    "status",
    "integration",
    "allowed_zone"
);


if ($kiw_request['method'] == "GET") {


    if ($kiw_request['tenant'] !== "superuser") {

        $kiw_tenant_query = "WHERE ktype != 'voucher' AND tenant_id = '{$kiw_request['tenant']}'";

    } else $kiw_tenant_query = "WHERE ktype != 'voucher'";


    if (count($request_module) == 2) {


        $kiw_request['id'] = $kiw_db->escape($request_module[1]);

        if (empty($kiw_tenant_query)) $kiw_tenant_query = "WHERE id = '{$kiw_request['id']}'";
        else $kiw_tenant_query .= " AND id = '{$kiw_request['id']}'";


        $kiw_data = $kiw_db->query_first("SELECT * FROM kiwire_account_auth {$kiw_tenant_query} LIMIT 1");

        unset($kiw_data['password']);


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


        $kiw_data = $kiw_db->fetch_array("SELECT * FROM kiwire_account_auth {$kiw_tenant_query} ORDER BY {$kiw_config['column']} {$kiw_config['order']} LIMIT {$kiw_config['offset']},{$kiw_config['limit']}");


        for ($kiw_c = 0; $kiw_c < count($kiw_data); $kiw_c++) {

            unset($kiw_data[$kiw_c]['password']);

        }


    }


    echo json_encode(array("status" => "success", "message" => "", "data" => $kiw_data));


} elseif ($kiw_request['method'] == "POST") {


    // check for required info to execute

    $_REQUEST = file_get_contents("php://input");

    $_REQUEST = json_decode($_REQUEST, true);


    foreach ($kiw_required as $kiw_key) {


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


    require_once dirname(__FILE__, 2) . "/user/includes/include_account.php";


    // check if the username already been used

    $kiw_test = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_account_auth WHERE username = '{$kiw_data['username']}' AND tenant_id = '{$kiw_data['tenant_id']}'");


    if ($kiw_test['kcount'] == 0) {


        $kiw_timezone = $kiw_db->query_first("SELECT timezone FROM kiwire_clouds WHERE tenant_id = '{$kiw_data['tenant_id']}' LIMIT 1");

        $kiw_timezone = $kiw_timezone['timezone'];

        if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


        $kiw_data['password'] = kiw_password(6);


        $kiw_user = array();

        $kiw_user['creator']        = "API";
        $kiw_user['tenant_id']      = $kiw_data['tenant_id'];
        $kiw_user['username']       = $kiw_data['username'];
        $kiw_user['fullname']       = $kiw_data['fullname'];
        $kiw_user['password']       = $kiw_data['password'];
        $kiw_user['email_address']  = $kiw_data['email_address'];

        $kiw_user['phone_number']   = $kiw_data['phone_number'];
        $kiw_user['remark']         = $kiw_data['remark'];
        $kiw_user['profile_subs']   = $kiw_data['profile_subs'];
        $kiw_user['profile_curr']   = $kiw_data['profile_subs'];
        $kiw_user['ktype']          = "account";
        $kiw_user['status']         = $kiw_data['status'];

        $kiw_user['integration']    = $kiw_data['integration'];
        $kiw_user['allowed_zone']   = $kiw_data['allowed_zone'];
        $kiw_user['date_expiry']    = date("Y-m-d H:i:s", strtotime("+1 Year"));
        $kiw_user['date_value']     = "NOW()";

        $kiw_result = create_account($kiw_db, $kiw_cache, $kiw_user);

        if ($kiw_result == true) {


            // send email to user

            $kiw_content = @file_get_contents("user_email.html");

            if (empty($kiw_content)) $kiw_content = "Welcome! Thank you for joining Compass One Loyalty Program. Enjoy additional 1 hour free Wifi.\n<br>Username: {{username}}\n<br>Password: {{password}}";


            $kiw_content = str_replace('{{username}}', $kiw_data['username'], $kiw_content);
            $kiw_content = str_replace('{{password}}', $kiw_data['password'], $kiw_content);

            $kiw_email['action']        = "send_email";
            $kiw_email['tenant_id']     = $kiw_data['tenant_id'];
            $kiw_email['email_address'] = $kiw_data['email_address'];
            $kiw_email['subject']       = "CompassOne Wifi Login";
            $kiw_email['content']       = htmlentities($kiw_content);
            $kiw_email['name']          = $kiw_user['fullname'];

            unset($kiw_content);


            $kiw_temp = curl_init();

            curl_setopt($kiw_temp, CURLOPT_URL, "http://127.0.0.1:9956");
            curl_setopt($kiw_temp, CURLOPT_POST, true);
            curl_setopt($kiw_temp, CURLOPT_POSTFIELDS, http_build_query($kiw_email));
            curl_setopt($kiw_temp, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($kiw_temp, CURLOPT_TIMEOUT, 5);
            curl_setopt($kiw_temp, CURLOPT_CONNECTTIMEOUT, 5);

            curl_exec($kiw_temp);
            curl_close($kiw_temp);


            echo json_encode(array("message" => "Success", "response-code" => "500", "uid" => $kiw_data['username'], "pass" => $kiw_data['password']));

            logger_api($kiw_api['tenant_id'], "{$kiw_request['api_key']} created user {$kiw_data['username']}");


        } else echo json_encode(array("status" => "error", "message" => "", "data" => ""));


    } else {

        echo json_encode(array("status" => "error", "message" => "Username already existed", "data" => ""));

    }


} elseif ($kiw_request['method'] == "PATCH") {


    require_once dirname(__FILE__, 2) . "/user/includes/include_account.php";


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

        if (!isset($kiw_data[$kiw_key]) && !in_array($kiw_key, array("id", "updated_date", "username"))){

            $kiw_data[$kiw_key] = $kiw_db->escape($kiw_value);

        }


    }


    if ($kiw_request['tenant'] !== "superuser") {

        if ($kiw_data['tenant_id'] !== $kiw_request['tenant']){

            die(json_encode(array("status" => "error", "message" => "You can only access your own tenant", "data" => "")));

        }

    }


    if (isset($_REQUEST['password'])){

        $kiw_data['password'] = sync_encrypt($_REQUEST['password']);

    }


    if (count($request_module) == 2) {


        $kiw_request['id'] = $kiw_db->escape($request_module[1]);

        $kiw_db->update("kiwire_account_auth", $kiw_data, "id = '{$kiw_request['id']}' AND tenant_id = '{$kiw_data['tenant_id']}'");


        if ($kiw_db->db_affected_row > 0) {

            echo json_encode(array("status" => "success", "message" => "", "data" => ""));

            logger_api($kiw_api['tenant_id'], "{$kiw_request['api_key']} updated user {$kiw_request['username']}");


        } else echo json_encode(array("status" => "error", "message" => "", "data" => ""));


    } else die(json_encode(array("status" => "error", "message" => "Missing ID for this request", "data" => "")));

    

} elseif ($kiw_request['method'] == "DELETE") {


    if (count($request_module) == 2) {


        $kiw_request['id'] = $kiw_db->escape($request_module[1]);


        // if not superuser then need to use tenant

        if ($kiw_request['tenant'] !== "superuser") {

            $kiw_tenant_query = "WHERE tenant_id = '{$kiw_request['tenant']}'";

        } else $kiw_tenant_query = "";


        if (empty($kiw_tenant_query)) $kiw_tenant_query = "WHERE id = '{$kiw_request['id']}'";
        else $kiw_tenant_query .= " AND id = '{$kiw_request['id']}'";

        $kiw_name = $kiw_db->query_first("SELECT username FROM kiwire_account_auth {$kiw_tenant_query} LIMIT 1");
 
        $kiw_db->query("DELETE FROM kiwire_account_auth {$kiw_tenant_query} LIMIT 1");
       

        if ($kiw_db->db_affected_row > 0) {

            echo json_encode(array("status" => "success", "message" => "", "data" => ""));

            logger_api($kiw_api['tenant_id'], "{$kiw_request['api_key']} deleted user {$kiw_name['username']}");


        } else {

            echo json_encode(array("status" => "error", "message" => "Missing ID to process this request", "data" => ""));

        }


    } else {

        echo json_encode(array("status" => "error", "message" => "ID not existed", "data" => ""));

    }


} elseif ($kiw_request['method'] == "PUT") {


    require_once dirname(__FILE__, 2) . "/user/includes/include_account.php";


    $_REQUEST = file_get_contents("php://input");

    $_REQUEST = json_decode($_REQUEST, true);


    $kiw_username = $kiw_db->escape($_REQUEST['username']);


    $kiw_user = $kiw_db->query_first("SELECT fullname,email_address FROM kiwire_account_auth WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_request['tenant']}' LIMIT 1");


    if (!empty($kiw_user)) {


        $kiw_password_raw = kiw_password();

        $kiw_password_enc = sync_encrypt($kiw_password_raw);


        $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), password = '{$kiw_password_enc}' WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_request['tenant']}' LIMIT 1");


        $kiw_content = @file_get_contents("user_email_2.html");

        if (empty($kiw_content)) $kiw_content = "Welcome! Thank you for joining Compass One Loyalty Program. Enjoy additional 1 hour free Wifi.\n<br>Username: {{username}}\n<br>Password: {{password}}";


        $kiw_content = str_replace('{{username}}', $kiw_username, $kiw_content);
        $kiw_content = str_replace('{{password}}', $kiw_password_raw, $kiw_content);

        $kiw_email['action']            = "send_email";
        $kiw_email['tenant_id']         = $kiw_request['tenant'];
        $kiw_email['email_address']     = $kiw_user['email_address'];
        $kiw_email['subject']           = "CompassOne Wifi Login";
        $kiw_email['content']           = htmlentities($kiw_content);
        $kiw_email['name']              = $kiw_user['fullname'];

        unset($kiw_content);


        $kiw_temp = curl_init();

        curl_setopt($kiw_temp, CURLOPT_URL, "http://127.0.0.1:9956");
        curl_setopt($kiw_temp, CURLOPT_POST, true);
        curl_setopt($kiw_temp, CURLOPT_POSTFIELDS, http_build_query($kiw_email));
        curl_setopt($kiw_temp, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($kiw_temp, CURLOPT_TIMEOUT, 5);
        curl_setopt($kiw_temp, CURLOPT_CONNECTTIMEOUT, 5);

        curl_exec($kiw_temp);
        curl_close($kiw_temp);


        echo json_encode(array("message" => "Success", "response-code" => "500", "uid" => $kiw_username, "pass" => $kiw_password_raw));


    } else {

        echo json_encode(array("message" => "User not found", "response-code" => "300"));

    }


}


function kiw_password($length = 6){


    $conso = array("b", "c", "d", "f", "g", "h", "j", "k", "l", "m", "n", "p", "r", "s", "t", "v", "w", "x", "y", "z");

    $vocal = array("a", "e", "i", "o", "u");

    $password = "";


    srand((double)microtime() * 1000000);

    $max = $length / 2;


    for ($i = 1; $i <= $max; $i++) {
        $password .= $conso[rand(0, 19)];
        $password .= $vocal[rand(0, 4)];
    }


    return $password;


}
