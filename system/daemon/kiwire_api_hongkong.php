<?php


error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING );


// require_once dirname(__FILE__, 3) . "/server/admin/includes/include_config.php";
// require_once dirname(__FILE__, 3) . "/server/admin/includes/include_general.php";


// $kiwire_server = new Swoole\Http\Server("0.0.0.0", 9960, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);


// $kiwire_server->set(
//     array(
//         'worker_num' => 1,
//         'max_conn' => 512,
//         'max_request' => 1024,
//         'group' => 'nginx',
//         'user' => 'nginx',
//         'pid_file' => '/run/kiwire-mobile.pid',
//         'daemonize' => 0,
//         'ssl_cert_file' => "/etc/ssl/certs/nginx-selfsigned.crt",
//         'ssl_key_file' => "/etc/ssl/private/nginx-selfsigned.key",
//         'ssl_ciphers' => 'HIGH:!aNULL:!MD5',
//         'open_tcp_keepalive' => true,
//     )
// );


// $kiwire_server->on("start", function ($server) {

//     echo "Kiwire API Hong Kong server service started at : " . date("Y-m-d H:i:s") . "\n";

// });


// $kiwire_server->on("request", function ($request, $response) {


//     $kiw_data = $request->rawcontent();


//     $response->header("Content-Type", "application/json");


//     if (empty($kiw_data)) {


//         $response->end(json_encode(array("status" => "error", "message" => "Empty payload", "data" => null)));

//         return;


//     }


//     $kiw_data = json_decode($kiw_data, true);


//     if (is_array($kiw_data)){


//         $kiw_db = new mysqli("p:" . SYNC_DB1_HOST, SYNC_DB1_USER, SYNC_DB1_PASSWORD, SYNC_DB1_DATABASE, SYNC_DB1_PORT);
        

//         // $kiw_api_setting = $kiw_db->query("SELECT api_key FROM kiwire_int_api_setting WHERE tenant_id = '' LIMIT 1")[0];



//         $kiw_social_username = md5(time(). random_int(1000,9999));


//         $kiw_user = $kiw_db->query("SELECT username,password,date_last_login FROM kiwire_account_auth WHERE username = '{$kiw_social_username}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1")[0];
        




//         if (empty($kiw_user)) {


//             $kiw_social_password = substr(hash("sha256", time()), 0, 12);

//             $kiw_user = array();

//             $kiw_user['tenant_id']          = "";
//             $kiw_user['username']           = $kiw_social_username;
//             $kiw_user['password']           = $kiw_social_password;
//             $kiw_user['fullname']           = "User HK $kiw_social_username";
//             $kiw_user['email_address']      = "unknown";
//             $kiw_user['remark']             = "";
//             $kiw_user['profile_subs']       = "";
//             $kiw_user['profile_curr']       = "";
//             $kiw_user['ktype']              = "account";
//             $kiw_user['status']             = "active";
//             $kiw_user['integration']        = "mobile";
//             $kiw_user['allowed_zone']       = "none";
//             $kiw_user['date_value']         = "NOW()";
//             $kiw_user['date_password']      = "NOW()";

//             if (create_account($kiw_db, $kiw_cache, $kiw_user) == false){


//                 $response->end(json_encode(array("status" => "error", "message" => "Error: create user error", "data" => null)));

//                 return;

//             }

//             unset($kiw_user);


//         } else {


//             $kiw_social_password = sync_decrypt($kiw_user['password']);

//             if (time() - strtotime($kiw_user['date_last_login']) > (86400 * 30)) {


//                 $kiw_user = array();

//                 $kiw_user['fullname']       = $kiw_data['fullname'];
//                 $kiw_user['gender']         = $kiw_data['gender'];
//                 $kiw_user['picture']        = $kiw_data['image_link'];
//                 $kiw_user['email_address']  = $kiw_data['email'];
//                 $kiw_user['birthday']       = $kiw_data['birthday'];
//                 $kiw_user['interest']       = $kiw_data['interest'];
//                 $kiw_user['age_group']      = $kiw_data['age_range'];
//                 $kiw_user['location']       = $kiw_data['location'];

//                 $kiw_db->query(sql_update($kiw_db, "kiwire_account_info", $kiw_user, "tenant_id = '{$kiw_temp['tenant_id']}' AND username = '{$kiw_social_username}' LIMIT 1"));


//             }


//         }

//         $kiw_fortiap = array(
//             "status"   => "success",
//             "message"  => null,
//             "data"     => [
//                 "user_name" => $kiw_social_username,
//                 "password"  => $kiw_social_password,
//                 "magic"     => $magic ?? "",
//                 "action"    => $kiw_url ?? ""
//             ]
//         ));



//         $response->end(json_encode(array("status" => "error", "message" => "", "data" => $kiw_fortiap)));



//     } else {


//         $response->end(json_encode(array("status" => "error", "message" => "Unknown request", "data" => null)));


//     }


// });


// $kiwire_server->start();
