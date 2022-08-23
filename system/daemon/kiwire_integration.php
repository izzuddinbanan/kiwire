<?php

/*
 * This agent is to received request internally for integration so that we can improve performance and speed
 */


require_once dirname(__FILE__, 3) . "/server/admin/includes/include_config.php";

require_once dirname(__FILE__, 3) . "/server/user/includes/include_account.php";
require_once dirname(__FILE__, 3) . "/server/user/includes/include_radius.php";

require_once dirname(__FILE__, 3) . "/server/libs/twilio/Twilio/autoload.php";
require_once dirname(__FILE__, 3) . "/server/libs/phpmailer/Exception.php";
require_once dirname(__FILE__, 3) . "/server/libs/phpmailer/PHPMailer.php";
require_once dirname(__FILE__, 3) . "/server/libs/phpmailer/SMTP.php";

require_once dirname(__FILE__, 3) . "/server/libs/adldap/adLDAP.php";


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


$kiw_system = @file_get_contents(dirname(__FILE__, 3) . "/server/custom/system_setting.json");
$kiw_system_enhance = @file_get_contents(dirname(__FILE__, 3) . "/server/custom/system_setting_enhance.json");

$kiw_system = json_decode($kiw_system, true);
$kiw_system_enhance = json_decode($kiw_system_enhance, true);


if (empty($kiw_system['integration_worker'])) $kiw_system['integration_worker'] = 1;


$kiw_skip_list = array("int", "pms", "bc", "sms", "email");

$kiwire_server = new Swoole\Http\Server("127.0.0.1", 9956);

$kiwire_server->set(
    array(
        'worker_num' => $kiw_system['integration_worker'],
        'max_conn' => $kiw_system_enhance["max_conn"] ?? 512,
        'max_request' => $kiw_system_enhance["max_request"] ?? 4096,
        'group' => 'nginx',
        'user' => 'nginx',
        'pid_file' => '/run/kiwire-integration.pid',
        'daemonize' => 1
    )
);


$kiwire_server->on("start", function ($server) {

    check_logger("Kiwire integration service started at : " . date("Y-m-d H:i:s"), "general");

});

$kiwire_server->on("request", function ($request, $response) use ($kiw_skip_list) {


    if (empty($request->post)){

        $response->status(404);
        return;

    }


    // initiate mysql connection

    $kiw_db = new Swoole\Coroutine\MySQL();

    $kiw_db->connect(array('host' => SYNC_DB1_HOST, 'user' => SYNC_DB1_USER, 'password' => SYNC_DB1_PASSWORD, 'database' => SYNC_DB1_DATABASE, 'port' => SYNC_DB1_PORT));



    // initiate cache connection

    $kiw_cache = new Swoole\Coroutine\Redis();

    $kiw_cache->connect(SYNC_REDIS_HOST, SYNC_REDIS_PORT, true);


    // set the header to be json type

    $response->header("Content-Type", "application/json");


    // list the function that we will received for this agent

    if ($request->post['action'] == "create_account_replicate") {


        go(function () use ($kiw_db, $kiw_cache, $request){

            create_account_replicate($kiw_db, $kiw_cache, $request);

        });


    } elseif ($request->post['action'] == "create_voucher") {


        $kiw_voucher_list = [];


        if ($request->post['quantity'] > 100){


            go(function () use ($kiw_db, $kiw_cache, $request, $response) {

                $temp_voucher_list = create_voucher($kiw_db, $kiw_cache, $request);

                 //decode return string
                 $temp_voucher_list = json_decode($temp_voucher_list, true);

                 $response->end(json_encode(array("status" => "success", "voucher" => $temp_voucher_list['username'], "bulk" => $temp_voucher_list['bulk_id'])));
            
            });



        } else {


            $kiw_voucher_list = create_voucher($kiw_db, $kiw_cache, $request);

            //decode return string
            $kiw_voucher_list = json_decode($kiw_voucher_list, true);

            if (count($kiw_voucher_list) > 0) {
   
                $response->end(json_encode(array("status" => "success", "voucher" => $kiw_voucher_list['username'], "bulk" => $kiw_voucher_list['bulk_id'])));
   
            } else $response->end(json_encode(array("status" => "error", "voucher" => null)));
   

        }


        check_logger("Create voucher request by {$request->post['creator']} for {$request->post['quantity']} unit", $request->post['tenant_id']);

        return;


    } elseif ($request->post['action'] == "disconnect_user"){


        disconnect_user($kiw_db, $kiw_cache, $kiw_db->escape($request->post['tenant_id']), $kiw_db->escape($request->post['username']));


    } elseif ($request->post['action'] == "send_email"){


        go(function () use ($kiw_db, $kiw_cache, $request) {

            send_email_to($kiw_db, $kiw_cache, $request->post['tenant_id'], $request->post['email_address'], $request->post['subject'], $request->post['content'], $request->post['name'], $request->post['purpose']);

        });


    } elseif ($request->post['action'] == "send_sms"){


        go(function () use ($kiw_db, $kiw_cache, $request) {

            send_sms_to($kiw_db, $kiw_cache, $request->post['tenant_id'], $request->post['phone_number'], $request->post['content'], $request->post['purpose']);

        });


    } elseif ($request->post['action'] == "change_ip"){


        $kiw_data['gateway_ip'] = filter_var($request->post['gateway_ip'], FILTER_VALIDATE_IP);
        $kiw_data['dns_one']    = filter_var($request->post['dns_one'], FILTER_VALIDATE_IP);
        $kiw_data['dns_two']    = filter_var($request->post['dns_two'], FILTER_VALIDATE_IP);

        $kiw_data['ip_address'] = $kiw_db->escape($request->post['ip_address']);
        $kiw_data['connection'] = $kiw_db->escape($request->post['connection_name']);
        $kiw_data['hostname']   = $kiw_db->escape($request->post['hostname']);


        if (!empty($kiw_data['connection'])) {


            system("sudo nmcli connection modify {$kiw_data['connection']} -ipv4.dns 0 1>/dev/null 2>&1");
            system("sudo nmcli connection modify {$kiw_data['connection']} -ipv4.dns 0 1>/dev/null 2>&1");


            if (!empty($kiw_data['ip_address'])) system("sudo nmcli connection modify {$kiw_data['connection']} ipv4.addresses '{$kiw_data['ip_address']}'");
            if (!empty($kiw_data['dns_one']))    system("sudo nmcli connection modify {$kiw_data['connection']} +ipv4.dns '{$kiw_data['dns_one']}'");
            if (!empty($kiw_data['dns_two']))    system("sudo nmcli connection modify {$kiw_data['connection']} +ipv4.dns '{$kiw_data['dns_two']}'");
            if (!empty($kiw_data['gateway_ip'])) system("sudo nmcli connection modify {$kiw_data['connection']} ipv4.gateway '{$kiw_data['gateway_ip']}'");
            if (!empty($kiw_data['hostname']))   system("sudo hostnamectl set-hostname {$kiw_data['hostname']}");

            system("sudo systemctl restart systemd-hostnamed 1>/dev/null 2>&1");
            system("sudo systemctl restart network 1>/dev/null 2>&1");

            $response->end(json_encode(array("status" => "success", "message" => "Your server network setting has been updated", "data" => null)));


        } else {


            $response->end(json_encode(array("status" => "failed", "message" => "Unknown connection name", "data" => null)));


        }

        return;


    } elseif (in_array($request->post['action'], array("check_ldap", "check_msad"))){


        // check for duplicate request

        $kiw_result_hash = md5($request->post['tenant_id'] . $request->post['username'] . $request->post['password']);

        $kiw_result = $kiw_cache->get("ADLDAP_CACHE:{$request->post['tenant_id']}:{$kiw_result_hash}");


        if (!isset($kiw_result['status'])) {


            $kiw_request['tenant_id'] = $kiw_db->escape($request->post['tenant_id']);
            $kiw_request['username']  = $kiw_db->escape($request->post['username']) ;
            $kiw_request['password']  = urldecode(urlencode($request->post['password'])) ;


            if ($request->post['action'] == "check_ldap") {

                $kiw_result = check_int_adldap("ldap", $kiw_db, $kiw_cache, $kiw_request, $kiw_skip_list);

            } else {

                $kiw_result = check_int_adldap("msad", $kiw_db, $kiw_cache, $kiw_request, $kiw_skip_list);

            }


            $kiw_cache->set("ADLDAP_CACHE:{$request->post['tenant_id']}:{$kiw_result_hash}", $kiw_result, 20);


        }


        $response->end(json_encode($kiw_result));

        return;


    } elseif ($request->post['action'] == "check_radius"){


        $kiw_result_hash = md5($request->post['tenant_id'] . $request->post['username'] . $request->post['password']);

        $kiw_result = $kiw_cache->get("RADIUS_CACHE:{$request->post['tenant_id']}:{$kiw_result_hash}");


        if (!empty($kiw_result)){


            $response->end(json_encode($kiw_result));

            return;


        }


        $kiw_request['tenant_id'] = $kiw_db->escape($request->post['tenant_id']);
        $kiw_request['username']  = $kiw_db->escape($request->post['username']) ;
        $kiw_request['password']  = urldecode(urlencode($request->post['password'])) ;


        // split domain from username if available

        if (strpos($kiw_request['username'], "@") > 0) {


            $kiw_request['username'] = explode("@", $kiw_request['username']);

            $kiw_request['domain'] = $kiw_request['username'][1];
            $kiw_request['username'] = $kiw_request['username'][0];

            $kiw_temp = "AND domain = '{$kiw_request['domain']}'";


        }


        $kiw_hosts = $kiw_cache->get("RADIUS_SETTING:{$kiw_request['tenant_id']}:{$kiw_request['domain']}");

        if (empty($kiw_hosts)) {

            $kiw_hosts = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_int_radius WHERE enabled = 'y' AND tenant_id = '{$kiw_request['tenant_id']}' {$kiw_temp} LIMIT 10");

            if (empty($kiw_hosts)) $kiw_hosts = array("dummy" => true);

            $kiw_cache->set("RADIUS_SETTING:{$kiw_request['tenant_id']}:{$kiw_request['domain']}", $kiw_hosts, 1800);

        }


        if (count($kiw_hosts) > 0) {


            $kiw_server_matched = array();


            foreach ($kiw_hosts as $kiw_host) {


                $kiw_connection = radius_auth_open() or die("ERROR: Unable to create RADIUS connection");

                radius_add_server($kiw_connection, $kiw_host['host'], $kiw_host['port'], $kiw_host['secret'], 3, 3);
                radius_create_request($kiw_connection, RADIUS_ACCESS_REQUEST);


                if ($kiw_host['use_domain'] == "y") radius_put_string($kiw_connection, RADIUS_USER_NAME, $kiw_request['username'] . "@" . $kiw_request['domain']);
                else radius_put_string($kiw_connection, RADIUS_USER_NAME, $kiw_request['username']);


                radius_put_string($kiw_connection, RADIUS_USER_PASSWORD, $kiw_request['password']);
                radius_put_string($kiw_connection, RADIUS_NAS_IDENTIFIER, $kiw_host['nasid']);


                $radius_r = radius_send_request($kiw_connection);


                if ($radius_r == RADIUS_ACCESS_ACCEPT){


                    if (!empty($kiw_host['keyword_str'])) {


                        $kiw_auth = false;


                        while ($kiw_data = radius_get_attr($kiw_connection)) {


                            if ($kiw_data['attr'] == RADIUS_VENDOR_SPECIFIC) {


                                $kiw_data_wrapper = radius_get_vendor_attr($kiw_data['data']);

                                switch ($kiw_host['data_type']) {

                                    case "string"   : $kiw_data_wrapper = radius_cvt_string($kiw_data_wrapper['data']); break;
                                    case "int"      : $kiw_data_wrapper = radius_cvt_int($kiw_data_wrapper['data']); break;
                                    case "addr"     : $kiw_data_wrapper = radius_cvt_addr($kiw_data_wrapper['data']); break;

                                }


                                if ($kiw_host['keyword_str'] == $kiw_data_wrapper){


                                    $kiw_auth = true;

                                    $kiw_server_matched = $kiw_host;

                                    break;


                                }


                            }


                        }


                    } else {


                        $kiw_auth = true;

                        $kiw_server_matched = $kiw_host;

                        break;


                    }



                }


            }


            if ($kiw_auth == true){


                $kiw_user = $kiw_db->query("SELECT * FROM kiwire_account_auth WHERE tenant_id = '{$kiw_request['tenant_id']}' AND username = '{$kiw_request['username']}' LIMIT 1")[0];


                if (empty($kiw_user['integration'])) {


                    $kiw_user = array();

                    $kiw_user['tenant_id']      = $kiw_request['tenant_id'];
                    $kiw_user['username']       = $kiw_request['username'];
                    $kiw_user['password']       = substr(md5(time()), 6, 8);
                    $kiw_user['remark']         = "RADIUS Account @{$kiw_server_matched['domain']}";
                    $kiw_user['profile_subs']   = $kiw_server_matched['profile'];
                    $kiw_user['profile_curr']   = $kiw_server_matched['profile'];
                    $kiw_user['ktype']          = "account";
                    $kiw_user['status']         = "active";
                    $kiw_user['integration']    = "radius";
                    $kiw_user['allowed_zone']   = $kiw_server_matched['allowed_zone'];
                    $kiw_user['date_value']     = "NOW()";
                    $kiw_user['date_expiry']    = date("Y-m-d H:i:s", strtotime("+{$kiw_server_matched['validity']} Day"));

                    create_account($kiw_db, $kiw_cache, $kiw_user);


                }


                $kiw_cache->set("RADIUS_CACHE:{$request->post['tenant_id']}:{$kiw_result_hash}", array("status" => "success"), 20);

                $response->end(json_encode(array("status" => "success")));

                return;


            } else {


                $kiw_cache->set("RADIUS_CACHE:{$request->post['tenant_id']}:{$kiw_result_hash}", array("status" => "failed"), 20);

                $response->end(json_encode(array("status" => "failed")));

                return;


            }



        } else {


            $kiw_cache->set("RADIUS_CACHE:{$request->post['tenant_id']}:{$kiw_result_hash}", array("status" => "failed"), 20);

            $response->end(json_encode(array("status" => "failed")));

            return;

        }


    } else {


        $response->end(json_encode(array("status" => "failed")));

        return;


    }


    $response->end(json_encode(array("status" => "success")));


});


$kiwire_server->start();


// list of integration functions

function check_logger($message, $tenant_id = "general"){


    if (file_exists(dirname(__FILE__, 3) . "/logs/{$tenant_id}/") == false) mkdir(dirname(__FILE__, 3) . "/logs/{$tenant_id}/", 0775, true);


    file_put_contents( dirname(__FILE__, 3) . "/logs/{$tenant_id}/kiwire-integration-{$tenant_id}-" . date("Ymd-H") . ".log", date("Y-m-d H:i:s") . " :: " . $message . "\n", FILE_APPEND);


}


function send_sms_to($kiw_db, $kiw_cache, $tenant_id, $phone_number, $sms_content, $kiw_purpose = "general"){


    $kiw_temp = $kiw_cache->get("SMS_SETTING:{$tenant_id}");

    if (empty($kiw_temp)) {

        $kiw_temp = $kiw_db->query("SELECT * FROM kiwire_int_sms WHERE tenant_id = '{$tenant_id}' LIMIT 1")[0];

        if (empty($kiw_temp)) $kiw_temp = array("dummy" => true);

        $kiw_cache->set("SMS_SETTING:{$tenant_id}", $kiw_temp, 1800);

    }


    $kiw_time = date("YmdH");

    $kiw_cache->incr("REPORT_SMS_SENT:{$kiw_time}:{$tenant_id}:nozone");



    if (!$kiw_temp['dummy'] && $kiw_temp['enabled'] == "y") {


        // check for few things before sending sms

        $phone_number = preg_replace('/\D/', '', $phone_number);

        if ($kiw_temp['prefix_phoneno'] == "y") $phone_number = "+" . $phone_number;

        $kiw_status = false;


        if ($kiw_temp['operator'] == "twilio"){


            // force add + sign for twilio.

            if (substr($phone_number, 0, 1) != "+") $phone_number = "+" . $phone_number;


            try {


                if ($kiw_temp['twilio_use_whatsapp'] == "y"){

                    $phone_number = "whatsapp:" . $phone_number;

                    $kiw_temp['twilio_no'] = "whatsapp:" . $kiw_temp['twilio_no'];

                }


                $kiw_client = new Twilio\Rest\Client($kiw_temp['twilio_sid'], $kiw_temp['twilio_token']);

                $kiw_message = $kiw_client->messages->create($phone_number, array("from" => $kiw_temp['twilio_no'], "body" => trim(strip_tags($sms_content))));


                $kiw_status = $kiw_message->status;


            } catch (Exception $e){

                check_logger("Error: Unable to send out SMS to Twilio: " . $e->getMessage(), $tenant_id);

            }


        } elseif ($kiw_temp['operator'] == "synchroweb"){


            $data_json = json_encode(array('api_key' => $kiw_temp['key'], "phone_number_to" => $phone_number, "message" => $sms_content));

            $kiw_client = send_http_request("https://sms.synchroweb.com/agent/index_sms.php", array("data" => $data_json),"post");

            if ($kiw_client['status'] == 200) $kiw_status = "succeed";


        } elseif ($kiw_temp['operator'] == "generic"){


            // check for url that will be use, if no protocol then add

            // check for variable to use

            $kiw_client['phone_var'] = $kiw_temp['u_phoneno'];

            $kiw_client['message_var'] = $kiw_temp['u_message'];


            try {


                if ($kiw_temp['u_method'] == "get") {


                    $kiw_client['url'] = $kiw_temp['u_uri'];

                    $kiw_client['status'] = send_http_request($kiw_client['url'], array($kiw_client['phone_var'] => $phone_number, $kiw_client['message_var'] => $sms_content), "get");


                } else {

                    $kiw_client['status'] = send_http_request($kiw_temp['u_uri'], array($kiw_client['phone_var'] => $phone_number, $kiw_client['message_var'] => $sms_content), "post", $kiw_temp['u_header']);

                }


                if ($kiw_client['status'] == 200) $kiw_status = $kiw_client['status'];


            } catch (Exception $e){

                check_logger("Error: Unable to send out SMS to {$kiw_temp['name']}: " . $e->getMessage(), $tenant_id);

            }


        } elseif ($kiw_temp['operator'] == "genusis"){


            if (substr($sms_content, 0, 2) != "RM"){

                $sms_content = "RM0 {$sms_content}";

            }


            if (substr($phone_number, 0, 1) == "+"){

                $phone_number = substr($phone_number, 1);

            }


            $kiw_message = array(
                "DigitalMedia" => array(
                    "ClientID" => $kiw_temp['g_clientid'],
                    "Username" => $kiw_temp['g_username'],
                    "SEND" => array(
                        array(
                            "Media" => "SMS",
                            "MessageType" => "S",
                            "Message" => $sms_content,
                            "Destination" => array(
                                array(
                                    "MSISDN" => $phone_number,
                                    "MessageType" => "S"
                                )
                            )
                        )
                    )
                )
            );


            $kiw_message = json_encode($kiw_message);

            $kiw_signature = md5($kiw_message.$kiw_temp['g_key']);

            $kiw_temp['g_url'] = "{$kiw_temp['g_url']}?Key={$kiw_signature}";


            $kiw_curl = curl_init();

            curl_setopt($kiw_curl, CURLOPT_URL, $kiw_temp['g_url']);
            curl_setopt($kiw_curl, CURLOPT_POST, true);
            curl_setopt($kiw_curl, CURLOPT_POSTFIELDS, $kiw_message);
            curl_setopt($kiw_curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($kiw_curl, CURLOPT_TIMEOUT, 20);
            curl_setopt($kiw_curl, CURLOPT_CONNECTTIMEOUT, 20);

            $kiw_test = curl_exec($kiw_curl);

            $kiw_test = json_decode($kiw_test, true);

            $kiw_status = $kiw_test['DigitalMedia'][0]['Result'];

            if (empty($kiw_status)) $kiw_status = "error";


        } else return false;


        $kiw_db->query("UPDATE kiwire_total_counter SET value = value + 1 WHERE data = 'sms' AND tenant_id = '{$tenant_id}' LIMIT 1");

        if ($kiw_db->affected_rows == 0){

            $kiw_db->query("INSERT INTO kiwire_total_counter(id, tenant_id, data, value) VALUE (NULL, '{$tenant_id}', 'sms', 1)");

        }


        check_logger("SMS sent to [ {$phone_number} ] with status: {$kiw_status}", $tenant_id);


    } else return false;


    return true;


}


function create_account_replicate($kiw_db, $kiw_cache, $kiw_user){

    create_account($kiw_db, $kiw_cache, $kiw_user);

}


function create_voucher($kiw_db, $kiw_cache, $kiw_request){


    $kiw_voucher_batch_id = null;
    $kiw_created_voucher = 0;


    // get the voucher configuration

    $kiw_voucher_conf = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_clouds WHERE tenant_id = '{$kiw_request->post['tenant_id']}' LIMIT 1")[0];


    $kiw_voucher_list = [];
    $kiw_voucher_created = [];


    $kiw_db_available = file_exists("/var/www/kiwire/server/custom/kiwire_voucher.sqlite");

    $kiw_sqlite = new SQLite3("/var/www/kiwire/server/custom/kiwire_voucher.sqlite");


    if ($kiw_db_available == false){


        $kiw_sqlite->exec("CREATE TABLE voucher_list (code char(64), bulk_id char(8), tenant char(64))");

        $kiw_sqlite->exec("CREATE TABLE bulk_list (who char(64), quantity int, profile char(64), tenant char(64))");


        $kiw_sqlite->exec("CREATE INDEX main_index ON voucher_list(code, tenant)");

        $kiw_sqlite->exec("CREATE INDEX bulk_list ON bulk_list(who, tenant)");


        // get the id from database once

        $kiw_db_ids = $kiw_db->query("SELECT bulk_id,creator,COUNT(*) AS quantity,profile_subs,tenant_id FROM kiwire_account_auth WHERE bulk_id <> '' GROUP BY bulk_id ORDER BY bulk_id LIMIT 10000");

        foreach($kiw_db_ids as $kiw_db_id){

            $kiw_sqlite->exec("INSERT INTO bulk_list (who, quantity, profile, tenant) VALUES ('{$kiw_db_id['creator']}', {$kiw_db_id['quantity']}, '{$kiw_db_id['profile_subs']}', '{$kiw_db_id['tenant_id']}')");

        }


    }

    unset($kiw_db_available);


    $kiw_sqlite->exec("INSERT INTO bulk_list (who, quantity, profile, tenant) VALUES ('{$kiw_request->post['creator']}', {$kiw_request->post['quantity']}, '{$kiw_request->post['profile']}', '{$kiw_request->post['tenant_id']}')");


    $kiw_voucher_batch_id = $kiw_sqlite->lastInsertRowID();

    $kiw_voucher_batch_id = str_pad($kiw_voucher_batch_id, 7, "0", STR_PAD_LEFT);


    // change format to V0000007

    $kiw_voucher_batch_id = "V{$kiw_voucher_batch_id}";



    // do some config checks

    if ((int)$kiw_voucher_conf['voucher_limit'] < 2) $kiw_voucher_conf['voucher_limit'] = 2;

    if (empty($kiw_voucher_conf['voucher_engine'])) $kiw_voucher_conf['voucher_engine'] = "uuid";


    $kiw_random_prefix = generate_uid($kiw_voucher_conf['voucher_limit']);

    try {

        $kiw_random_ts = random_int(100, 999);

    }catch (Exception $e){

        $kiw_random_ts = bcmod(time(), 1000);

    }


    for ($x = 0; $x < $kiw_request->post['quantity']; $x++){


        $kiw_random_ts++;


        do {


            if ($kiw_voucher_conf['voucher_engine'] == "serial"){


                $kiw_voucher_new = $kiw_request->post['prefix'] . $kiw_random_prefix . $kiw_random_ts;


            } elseif ($kiw_voucher_conf['voucher_engine'] == "uuid"){


                $kiw_voucher_new = $kiw_request->post['prefix'] .  substr(uniqid(), -$kiw_voucher_conf['voucher_limit']);


            } elseif($kiw_voucher_conf['voucher_engine'] == "random"){


                $kiw_check = 1;

                $kiw_start = time();


                while ($kiw_check > 0) {


                    $kiw_temp = random_string_id($kiw_voucher_conf['voucher_limit'], $kiw_voucher_conf['voucher_avoid_ambiguous']);

                    $kiw_check = $kiw_sqlite->querySingle("SELECT COUNT(*) AS kcount FROM voucher_list WHERE code = '{$kiw_temp}' AND tenant = '{$kiw_request->post['tenant_id']}'");


                    if ((time() - $kiw_start) > 300){


                        if (count($kiw_voucher_created) > 1) {

                            $kiw_message['title'] = "Voucher Creation: Not All Voucher Created";
                            $kiw_message['message'] = "Only " . count($kiw_voucher_created) . " vouchers out of {$kiw_request->post['quantity']} unit was completed due to taking more than 5 minutes to get a random string for one voucher. Please increase the length of the voucher code.";

                        } else {

                            $kiw_message['title'] = "Voucher Creation: Failed";
                            $kiw_message['message'] = "Voucher creation was failed due to taking more than 5 minutes to get a random string for one voucher. Please increase the length of the voucher code.";

                        }

                        break 2;

                    }


                    if ($kiw_check > 0) sleep(1);


                }


                $kiw_sqlite->exec("INSERT INTO voucher_list (code, bulk_id, tenant) VALUES ('{$kiw_temp}', '{$kiw_voucher_batch_id}', '{$kiw_request->post['tenant_id']}')");

                $kiw_voucher_new = $kiw_request->post['prefix'] . $kiw_temp;


                unset($kiw_start);
                unset($kiw_temp);


            } else break 2;


        } while (in_array($kiw_voucher_new, $kiw_voucher_list) || empty($kiw_voucher_new));


        $kiw_created_voucher++;

        $kiw_voucher_list[] = $kiw_voucher_new;

        $kiw_voucher_qty = $kiw_request->post['quantity'];


        $kiw_voucher_password = generate_password();


        $kiw_user['tenant_id']      = $kiw_request->post['tenant_id'];
        $kiw_user['creator']        = $kiw_request->post['creator'];
        $kiw_user['username']       = $kiw_voucher_new;
        $kiw_user['password']       = $kiw_voucher_password;
        $kiw_user['remark']         = $kiw_request->post['remark'];
        $kiw_user['profile_subs']   = $kiw_request->post['profile'];
        $kiw_user['profile_curr']   = $kiw_request->post['profile'];
        $kiw_user['price']          = $kiw_request->post['price'];
        $kiw_user['ktype']          = "voucher";
        $kiw_user['bulk_id']        = $kiw_voucher_batch_id;
        $kiw_user['status']         = "active";
        $kiw_user['integration']    = "int";
        $kiw_user['allowed_zone']   = $kiw_request->post['allowed_zone'];
        $kiw_user['date_value']     = "NOW()";
        $kiw_user['date_expiry']    = $kiw_request->post['expiry_date'];

        if (create_account($kiw_db, $kiw_cache, $kiw_user) == true){

            $kiw_voucher_created[] = $kiw_voucher_new;

            $kiw_bulk_id = $kiw_voucher_batch_id;

        }

        unset($kiw_voucher_new);
        unset($kiw_voucher_password);
        unset($kiw_user);


    }


    if (!empty($kiw_voucher_batch_id)) {
      
        $kiw_track['tenant_id']   = $kiw_request->post['tenant_id'];
        $kiw_track['bulk_id']     = $kiw_voucher_batch_id;
        $kiw_track['quantity']    = $kiw_request->post['quantity'];
        $kiw_track['created_at']  = "NOW()";
        $kiw_track['updated_at']  = "NOW()";

        $kiw_db->query(sql_insert($kiw_db, "kiwire_voucher_generate", $kiw_track));

    }
  


    $kiw_sqlite->close();

    unset($kiw_sqlite);


    if (empty($kiw_message['title'])) $kiw_message['title'] = "Voucher Creation: Completed";
    if (empty($kiw_message['message'])) $kiw_message['message'] = "Your request to create {$kiw_request->post['quantity']} vouchers has been completed.";


    if ($kiw_request->post['superuser'] == "true"){

        $kiw_message['tenant_id'] = "superuser";

    } else $kiw_message['tenant_id'] = $kiw_request->post['tenant_id'];


    $kiw_message['updated_date'] = "NOW()";
    $kiw_message['sender']       = "System";
    $kiw_message['recipient']    = $kiw_request->post['creator'];
    $kiw_message['date_sent']    = "NOW()";

    $kiw_db->query(sql_insert($kiw_db, "kiwire_message", $kiw_message));


    // return $kiw_voucher_created;

    //return voucher code and bulk id
    return json_encode(["username" => $kiw_voucher_created, "bulk_id" => $kiw_bulk_id]);


}


function generate_password($length = 8){

    $conso = array("b", "c", "d", "f", "g", "h", "j", "k", "l",
        "m", "n", "p", "r", "s", "t", "v", "w", "x", "y", "z");

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


function generate_uid($length = 3){

    $characters = '01234567890123456789';

    $string = '';

    for ($p = 0; $p < $length; $p++) {

        $string .= $characters[mt_rand(0, strlen($characters))];

    }

    return $string;

}




function send_email_to($kiw_db, $kiw_cache, $kiw_tenant, $kiw_email_address, $kiw_subject, $kiw_content, $kiw_name = "", $kiw_purpose = "general"){


    if ($kiw_tenant == "superuser") {


        $kiw_temp = @file_get_contents(dirname(__FILE__, 3) . "/server/custom/system_smtp.json");

        if (empty($kiw_temp)) $kiw_temp = array("dummy" => true);
        else $kiw_temp = json_decode($kiw_temp, true);


    } else {


        $kiw_temp = $kiw_cache->get("EMAIL_SETTING:{$kiw_tenant}");

        if (empty($kiw_temp)) {

            $kiw_temp = $kiw_db->query("SELECT * FROM kiwire_int_email WHERE tenant_id = '{$kiw_tenant}' LIMIT 1")[0];

            if (empty($kiw_temp)) $kiw_temp = array("dummy" => true);

            $kiw_cache->set("EMAIL_SETTING:{$kiw_tenant}", $kiw_temp, 1800);

        }


    }


    $kiw_time = date("YmdH");


    if ($kiw_tenant != "superuser") {

        $kiw_cache->incr("REPORT_EMAIL_SENT:{$kiw_time}:{$kiw_tenant}:nozone");

    }


    $kiw_subject = trim($kiw_subject);

    $kiw_content = html_entity_decode(urldecode($kiw_content));



    if (!$kiw_temp['dummy'] && !empty($kiw_temp['host'])){


        if (in_array($kiw_temp['host'], array("mail-delivery-system.synchroweb.com"))){


            $kiw_curl = curl_init("https://{$kiw_temp['host']}/");

            $kiw_data = array();

            $kiw_data['id']        = $kiw_temp['user'];
            $kiw_data['time']      = date("Y-m-d H:i:s");
            $kiw_data['from']      = $kiw_temp['from_email'];
            $kiw_data['to']        = $kiw_email_address;
            $kiw_data['subject']   = $kiw_subject;
            $kiw_data['content']   = base64_encode($kiw_content);
            $kiw_data['token']     = md5("{$kiw_data['time']}|{$kiw_data['to']}|{$kiw_temp['password']}");

            curl_setopt($kiw_curl, CURLOPT_POST, 1);
            curl_setopt($kiw_curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($kiw_curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($kiw_curl, CURLOPT_DNS_CACHE_TIMEOUT, 86400);
            curl_setopt($kiw_curl, CURLOPT_POSTFIELDS, json_encode($kiw_data));
            curl_setopt($kiw_curl, CURLOPT_TIMEOUT, 5);

            unset($kiw_data);

            $kiw_data = json_decode(curl_exec($kiw_curl), true);

            curl_close($kiw_curl);

            if (isset($kiw_data['status']) && $kiw_data['status'] == "succeed") $kiw_status = "succeed";
            else $kiw_status = "failed";


        } else {


            $kiw_email = new PHPMailer(false);

            $kiw_email->Timeout = 10;
            $kiw_email->SMTPDebug = 0;

            $kiw_email->Host = trim($kiw_temp['host']);
            $kiw_email->Port = trim($kiw_temp['port']);


            if (!empty($kiw_temp['auth']) && $kiw_temp['auth'] != "none") {

                $kiw_email->SMTPSecure = trim($kiw_temp['auth']);

            }


            if (!empty($kiw_temp['user']) && !empty($kiw_temp['password'])) {

                $kiw_email->SMTPAuth = true;

                $kiw_email->Username = trim($kiw_temp['user']);
                $kiw_email->Password = trim($kiw_temp['password']);

            }


            $kiw_email->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );


            try {


                $kiw_email->isSMTP();
                $kiw_email->setFrom(trim($kiw_temp['from_email']), trim($kiw_temp['from_name']));
                $kiw_email->addAddress($kiw_email_address, $kiw_name);

                $kiw_email->addReplyTo(trim($kiw_temp['from_email']));
                $kiw_email->isHTML(true);

                $kiw_email->Subject = $kiw_subject;
                $kiw_email->Body = $kiw_content;

                // send the email to smtp server

                $kiw_email->send();

                if ($kiw_email->ErrorInfo == "") {

                    $kiw_status = "succeed";

                } else $kiw_status = $kiw_email->ErrorInfo;


            } catch (Exception $e){


                $kiw_email->Body = "";
                $kiw_email->clearAllRecipients();
                $kiw_email->clearAttachments();
                $kiw_email->smtpClose();

                $kiw_status = $e->getMessage();


            }


            unset($kiw_email);


        }


    } else {


        // try to send using mail function

        $kiw_headers[] = "MIME-Version: 1.0";

        $kiw_headers[] = "Content-type: text/html; charset=iso-8859-1";


        if (!empty($kiw_temp['from_email']) && !empty($kiw_temp['from_name'])) {

            $kiw_headers[] = "From: {$kiw_temp['from_email']} <{$kiw_temp['from_name']}>";

        }


        if (mail($kiw_email_address, $kiw_subject, $kiw_content, implode("\r\n", $kiw_headers))) {

            $kiw_status = "sent";

        } else $kiw_status = "fail";


    }


    $kiw_db->query("UPDATE kiwire_total_counter SET value = value + 1 WHERE data = 'email' AND tenant_id = '{$kiw_tenant}' LIMIT 1");

    if ($kiw_db->affected_rows == 0){

        $kiw_db->query("INSERT INTO kiwire_total_counter(id, tenant_id, data, value) VALUE (NULL, '{$kiw_tenant}', 'email', 1)");

    }


    check_logger("Email sent to [ {$kiw_email_address} ] with status: {$kiw_status}", $kiw_tenant);

    return true;


}


function check_int_adldap($kiw_method, $kiw_db, $kiw_cache, $kiw_request, $kiw_skip_list) {


    $kiw_time = date("YmdH");

    $kiw_cache->incr("REPORT_INTEGRATION:{$kiw_time}:{$kiw_request['tenant_id']}:" . strtoupper($kiw_method));
    

    if ($kiw_method == "ldap") {


        $kiw_user = $kiw_db->query("SELECT * FROM kiwire_account_auth WHERE tenant_id = '{$kiw_request['tenant_id']}' AND username = '{$kiw_request['username']}' AND integration = 'ldap' LIMIT 1")[0];


        if (in_array($kiw_user, $kiw_skip_list)) return array("status" => "failed");


        $kiw_temp = $kiw_cache->get("LDAP_DATA:{$kiw_request['tenant_id']}");

        if (empty($kiw_temp)) {

            $kiw_temp = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_int_ldap WHERE tenant_id = '{$kiw_request['tenant_id']}' LIMIT 1")[0];

            if (empty($kiw_temp)) $kiw_temp = array("dummy" => true);

            $kiw_cache->set("LDAP_DATA:{$kiw_request['tenant_id']}", $kiw_temp, 1800);

        }


        if ($kiw_temp['enabled'] == "y"){


            $kiw_ldap = ldap_connect($kiw_temp['host'], $kiw_temp['port']);


            ldap_set_option($kiw_ldap, LDAP_OPT_PROTOCOL_VERSION, 3);

            ldap_set_option($kiw_ldap, LDAP_OPT_REFERRALS, 0);


            if ($kiw_ldap) {


                $kiw_temp['rdn'] = array_filter(explode(";", $kiw_temp['rdn']));


                foreach ($kiw_temp['rdn'] as $kiw_rdn) {


                    $kiw_rdn = str_replace("{{username}}", $kiw_request['username'], $kiw_rdn);

                    $kiw_ldap_bind = ldap_bind($kiw_ldap, $kiw_rdn, $kiw_request['password']);

                    if ($kiw_ldap_bind) break;


                }


                if ($kiw_ldap_bind) {


                    if (empty($kiw_user)) {


                        $kiw_groups = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_group_mapping WHERE tenant_id = '{$kiw_request['tenant_id']}' AND type = 'ldap'");

                        foreach ($kiw_groups as $kiw_group) {


                            $kiw_failed = false;


                            if (!check_group_ex($kiw_ldap, $kiw_rdn, $kiw_group['group_name'])) {

                                $kiw_failed = true;

                            }


                            if ($kiw_failed == false) {

                                $kiw_result['profile'] = $kiw_group['profile'];
                                $kiw_result['allowed_zone'] = $kiw_group['allowed_zone'];

                                break;

                            }


                        }


                        if (empty($kiw_result['profile'])) $kiw_result['profile'] = $kiw_temp['profile'];
                        if (empty($kiw_result['allowed_zone'])) $kiw_result['allowed_zone'] = $kiw_temp['allowed_zone'];


                        $kiw_result['details'] = ldap_read($kiw_ldap, $kiw_rdn, '(objectclass=*)', array('mail', 'givenname'));
                        $kiw_result['entries'] = ldap_get_entries($kiw_ldap, $kiw_result['details']);


                        $kiw_user = array();

                        $kiw_user['tenant_id']      = $kiw_request['tenant_id'];
                        $kiw_user['username']       = $kiw_request['username'];
                        $kiw_user['password']       = substr(md5(time()), 6, 8);
                        $kiw_user['email_address']  = $kiw_result['entries'][0]['mail'][0];
                        $kiw_user['fullname']       = $kiw_result['entries'][0]['givenname'][0];
                        $kiw_user['remark']         = "LDAP Account";
                        $kiw_user['profile_subs']   = $kiw_result['profile'];
                        $kiw_user['profile_curr']   = $kiw_result['profile'];
                        $kiw_user['ktype']          = "account";
                        $kiw_user['status']         = "active";
                        $kiw_user['integration']    = "ldap";
                        $kiw_user['allowed_zone']   = $kiw_result['allowed_zone'];
                        $kiw_user['date_value']     = "NOW()";

                        create_account($kiw_db, $kiw_cache, $kiw_user);


                    }


                    return array("status" => "success");


                } else return array("status" => "failed");


            } else return array("status" => "failed");


        } else return array("status" => "failed");


    } else {


        $kiw_user = $kiw_db->query("SELECT * FROM kiwire_account_auth WHERE tenant_id = '{$kiw_request['tenant_id']}' AND username = '{$kiw_request['username']}' AND integration = 'msad' LIMIT 1")[0];


        if (in_array($kiw_user, $kiw_skip_list)) return array("status" => "failed");


        $kiw_temp = $kiw_cache->get("MSAD_DATA:{$kiw_request['tenant_id']}");

        if (empty($kiw_temp)) {

            $kiw_temp = $kiw_db->query("SELECT * FROM kiwire_int_msad WHERE tenant_id = '{$kiw_request['tenant_id']}' LIMIT 1")[0];

            if (empty($kiw_temp)) $kiw_temp = array("dummy" => true);

            $kiw_cache->set("MSAD_DATA:{$kiw_request['tenant_id']}", $kiw_temp, 1800);

        }


        if ($kiw_temp['enabled'] == "y"){


            try {


                $kiw_ldap = new adLDAP(array("base_dn" => $kiw_temp['basedn'], "account_suffix" => $kiw_temp['accsuffix'], "domain_controllers" => explode(",", $kiw_temp['host']), "admin_username" => $kiw_temp['adminuser'], "admin_password" => $kiw_temp['adminpw']));

                $kiw_result['authenticated'] = $kiw_ldap->user()->authenticate($kiw_request['username'], $kiw_request['password']);

                $kiw_result['group'] = $kiw_ldap->user()->groups($kiw_request['username']);


            } catch (adLDAPException $e) {

                check_logger("MSAD Error :: " . $e->getMessage(), $kiw_request['tenant_id']);

            }


            if ($kiw_result['authenticated']) {


                // check if user created, if not then create

                if (empty($kiw_user)) {


                    $kiw_groups = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_group_mapping WHERE tenant_id = '{$kiw_request['tenant_id']}' AND type = 'msad'");

                    foreach ($kiw_groups as $kiw_group) {


                        $kiw_failed = false;

                        $kiw_current_group = explode(",", $kiw_group['group_name']);

                        foreach ($kiw_current_group as $kiw_each_group) {

                            if (!in_array($kiw_each_group, $kiw_result['group'], true)) {

                                $kiw_failed = true;

                            }

                        }


                        if ($kiw_failed == false) {

                            $kiw_result['profile'] = $kiw_group['profile'];
                            $kiw_result['allowed_zone'] = $kiw_group['allowed_zone'];

                            break;

                        }

                    }


                    if (empty($kiw_result['profile'])) $kiw_result['profile'] = $kiw_temp['profile'];
                    if (empty($kiw_result['allowed_zone'])) $kiw_result['allowed_zone'] = $kiw_temp['allowed_zone'];


                    try {

                        $result = $kiw_ldap->user()->infoCollection($kiw_request['username'], array("*"));

                        $kiw_result['email_address'] = $result->mail;
                        $kiw_result['fullname'] = $result->displayName;

                    } catch (Exception $e){

                        $kiw_result['email_address'] = "";
                        $kiw_result['fullname'] = "";

                    }


                    $kiw_user = array();

                    $kiw_user['tenant_id']      = $kiw_request['tenant_id'];
                    $kiw_user['username']       = $kiw_request['username'];
                    $kiw_user['email_address']  = $kiw_result['email_address'];
                    $kiw_user['fullname']       = $kiw_result['fullname'];
                    $kiw_user['password']       = substr(md5(time()), 6, 8);
                    $kiw_user['remark']         = "Active Directory Account";
                    $kiw_user['profile_subs']   = $kiw_result['profile'];
                    $kiw_user['profile_curr']   = $kiw_result['profile'];
                    $kiw_user['ktype']          = "account";
                    $kiw_user['status']         = "active";
                    $kiw_user['integration']    = "msad";
                    $kiw_user['allowed_zone']   = $kiw_result['allowed_zone'];
                    $kiw_user['date_value']     = "NOW()";

                    create_account($kiw_db, $kiw_cache, $kiw_user);


                }


                return array("status" => "success");


            } else return array("status" => "failed");


        } else return array("status" => "failed");


    }


}


function check_group_ex($kiw_ldap, $kiw_dn, $kiw_group) {


    $kiw_attribute = array('memberof');

    $kiw_result = ldap_read($kiw_ldap, $kiw_dn, '(objectclass=*)', $kiw_attribute);


    if ($kiw_result === false) return false;


    $kiw_result = ldap_get_entries($kiw_ldap, $kiw_result);

    if ($kiw_result['count'] <= 0) return false;


    if (empty($kiw_result[0]['memberof'])) {

        return false;

    } else {

        for ($i = 0; $i < $kiw_result[0]['memberof']['count']; $i++) {

            if ($kiw_result[0]['memberof'][$i] == $kiw_group) {

                return true;

            } elseif (check_group_ex($kiw_ldap, $kiw_result[0]['memberof'][$i], $kiw_group)) {

                return true;

            }

        }

    }


    return false;

}

