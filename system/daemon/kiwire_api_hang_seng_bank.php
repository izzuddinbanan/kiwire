<?php


error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING );

require_once dirname(__FILE__, 3) . "/server/user/includes/include_account.php";

require_once dirname(__FILE__, 3) . "/server/admin/includes/include_config.php";
require_once dirname(__FILE__, 3) . "/server/admin/includes/include_general.php";


$kiwire_server = new Swoole\Http\Server("0.0.0.0", 9960, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);


$kiwire_server->set(
    array(
        'worker_num' => 1,
        'max_conn' => 512,
        'max_request' => 1024,
        'group' => 'nginx',
        'user' => 'nginx',
        'pid_file' => '/run/kiwire-mobile.pid',
        'daemonize' => 0,
        'ssl_cert_file' => "/etc/ssl/certs/nginx-selfsigned.crt",
        'ssl_key_file' => "/etc/ssl/private/nginx-selfsigned.key",
        'ssl_ciphers' => 'HIGH:!aNULL:!MD5',
        'open_tcp_keepalive' => true,
    )
);


$kiwire_server->on("start", function ($server) {

    echo "Kiwire API Hong Kong server service started at : " . date("Y-m-d H:i:s") . "\n";

});


$kiwire_server->on("request", function ($request, $response) {


    
    // initiate cache connection

    $kiw_cache = new Swoole\Coroutine\Redis();

    $kiw_cache->connect(SYNC_REDIS_HOST, SYNC_REDIS_PORT, true);

    // initiate mysql connection

    $kiw_db = new Swoole\Coroutine\MySQL();

    $kiw_db->connect(array('host' => SYNC_DB1_HOST, 'user' => SYNC_DB1_USER, 'password' => SYNC_DB1_PASSWORD, 'database' => SYNC_DB1_DATABASE, 'port' => SYNC_DB1_PORT));


    $response->header("Content-Type", "application/json");


    $kiw_data = $request->post;



    if(strtolower(trim($request->server['request_method'])) == "post") {
        
        

        if (strtolower(trim($request->server['path_info'])) == "/auth/getuser") {


            if (empty($kiw_data)) {


                $response->end(json_encode(baseApiRespond(null, false, "Empty payload. Please check your parameter.")));
                return;


            }


            // $kiw_data = json_decode($kiw_data, true);
            
            if (is_array($kiw_data)){


                if(!isset($kiw_data["session_id"])) {
    
    
                    $response->end(json_encode(baseApiRespond(null, false, "Please check your paramenter. 'session_id' is empty.", 500)));
                    return;
                }
    
                
    
                $kiw_cache_data = $kiw_cache->get("MOBILE_DATA:{$kiw_data["session_id"]}");
    
                
                if(empty($kiw_cache_data)){
    
    
                    $response->end(json_encode(baseApiRespond(null, false, "Please check your paramenter. 'session_id' is not valid.", 500)));
                    return;
                }
    
    
        
                // $response->end(json_encode($kiw_cache_data));
                // return;



                $kiw_social_username = md5(time(). random_int(1000,9999));


                $kiw_user = $kiw_db->query("SELECT username,password,date_last_login FROM kiwire_account_auth WHERE username = '{$kiw_social_username}' AND tenant_id = '{$kiw_cache_data['controller']['tenant_id']}' LIMIT 1")[0];



                if (empty($kiw_user)) {


                    $kiw_social_password = substr(hash("sha256", time()), 0, 12);

                    $kiw_user = array();

                    $kiw_user['tenant_id']          = $kiw_cache_data['controller']['tenant_id'];
                    $kiw_user['username']           = $kiw_social_username;
                    $kiw_user['password']           = $kiw_social_password;
                    $kiw_user['fullname']           = "user::hk::$kiw_social_username";
                    $kiw_user['email_address']      = "unknown";
                    $kiw_user['remark']             = "";
                    $kiw_user['profile_subs']       = "tech";
                    $kiw_user['profile_curr']       = null;
                    $kiw_user['ktype']              = "account";
                    $kiw_user['status']             = "active";
                    $kiw_user['integration']        = "mobile";
                    $kiw_user['allowed_zone']       = "none";
                    $kiw_user['date_value']         = "NOW()";
                    $kiw_user['date_password']      = "NOW()";

                    if (create_account($kiw_db, $kiw_cache, $kiw_user) == false){


                        $response->end(json_encode(array("status" => "error", "message" => "Error: create user error", "data" => null)));
                        return;

                    }

                    unset($kiw_user);


                }

                $kiw_fortiap = array(
                    "data"     => [
                        "user_name"     => $kiw_social_username,
                        "password"      => $kiw_social_password,
                        "magic"         => $kiw_cache_data['controller']['magic'],
                        "action"        => $kiw_cache_data['controller']['login'],
                        "data_kiwire_service"    => [
                            "macaddress"    => $kiw_cache_data['user']['mac'],
                            "ipaddress"     => $kiw_cache_data['user']['ip'],
                            "ipv6address"   => "",
                            "zone"          => $kiw_cache_data['user']['zone'],
                            "nasid"         => $kiw_cache_data['controller']['id'],
                            "tenant_id"     => $kiw_cache_data['controller']['tenant_id'],
                            "device_vendor" => $kiw_cache_data['controller']['type'],
                            "action"        => "authorize",
                            "system"        => $kiw_cache_data['user']['system'],
                            "class"         => $kiw_cache_data['user']['class'],
                            "brand"         => $kiw_cache_data['user']['brand'],
                            "model"         => $kiw_cache_data['user']['model'],
                        ], 

                        // "data_type"     => "post",
                        // "session_id"    => $kiw_data["session_id"],
                        // "gateway_ip"    => $session_id ?? "gateway_ip",

                    ]
                );



                $response->end(json_encode(baseApiRespond($kiw_fortiap, true, "Success.")));
                return;

            } else {


                $response->end(json_encode(baseApiRespond(null, false, "Unknown request.")));
                return;

            }
        }
        else if(strtolower(trim($request->server['path_info'])) == "/auth/getcode"){


            if (is_array($kiw_data)){


                if(!isset($kiw_data["tenant_id"])) {
    
    
                    $response->end(json_encode(baseApiRespond(null, false, "Please check your paramenter. 'tenant_id' is empty.", 500)));
                    return;
                }

                
                if($kiw_data["tenant_id"] != "default") {
    
                    $response->end(json_encode(baseApiRespond(null, false, "You are not allowed to access this API.", 500)));
                    return;
                }
    
                
                while (true){


                    $kiw_social_username = random_string_id(6, 'y');

                    $kiw_user = $kiw_db->query("SELECT username,password,date_last_login FROM kiwire_account_auth WHERE username = '{$kiw_social_username}' AND tenant_id = '{$kiw_data["tenant_id"]}' LIMIT 1")[0];

    
                    if(empty($kiw_user)){
    
                        break;
                    }
    
    
                }


                $kiw_social_password = substr(hash("sha256", time()), 0, 12);

                $kiw_user = array();

                $kiw_user['create']             = "system";
                $kiw_user['tenant_id']          = $kiw_data["tenant_id"];
                $kiw_user['username']           = $kiw_social_username;
                $kiw_user['password']           = $kiw_social_password;
                $kiw_user['fullname']           = "hangseng-$kiw_social_username";
                $kiw_user['email_address']      = "unknown";
                $kiw_user['remark']             = "";
                $kiw_user['profile_subs']       = "two-hour-usage";
                $kiw_user['profile_curr']       = null;
                $kiw_user['ktype']              = "voucher";
                $kiw_user['status']             = "active";
                $kiw_user['integration']        = "int";
                $kiw_user['allowed_zone']       = "none";
                $kiw_user['date_value']         = "NOW()";
                $kiw_user['date_password']      = "NOW()";

                if (create_account($kiw_db, $kiw_cache, $kiw_user) == false){


                    $response->end(json_encode(array("status" => "error", "message" => "Error: create code", "data" => null)));
                    return;

                }

                unset($kiw_user);



                $kiw_response_api = array(
                    "data"     => [
                        "code"          => $kiw_social_username,
                    ]
                );



                $response->end(json_encode(baseApiRespond($kiw_response_api, true, "Success.")));
                return;

            } else {


                $response->end(json_encode(baseApiRespond(null, false, "Unknown request.")));
                return;

            }


            $response->end(json_encode(baseApiRespond(null, false, "Unknown request.", 400)));
            return;

        }else{


            $response->end(json_encode(baseApiRespond(null, false, "Unknown request.", 400)));
            return;

        }
    } else {


        $response->end(json_encode(baseApiRespond(null, false, "Method not allowed.", 405)));
        return;

    }

});



$kiwire_server->start();

function baseApiRespond($data, $type = true, $message = "", $code = 200) {

    
    $respond["status"] = [
        "type"  => $type ? true : false,
        "code"  => $code,
        "message" => $message,
    ];


    if(empty($data)) {

        $data["data"] = "";

    }


    return array_merge($data, $respond);

}



function random_string_id($kiw_length = 6, $kiw_avoid_ambiguous = 'n') {


    $kiw_char = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    if($kiw_avoid_ambiguous == "y") $kiw_char = remove_ambiguous($kiw_char);


    $kiw_char_len   = strlen($kiw_char);
    $kiw_rand_str   = '';


    for ($i = 0; $i < $kiw_length; $i++) {

        $kiw_rand_str .= $kiw_char[random_int(0, $kiw_char_len - 1)];

    }


    return $kiw_rand_str;


}




function remove_ambiguous($kiw_string){


    $kiw_string = preg_replace('/[iIoO0|lL1]/', "", $kiw_string);

    $kiw_string = count_chars(strtoupper($kiw_string), 3);

    return $kiw_string;


}