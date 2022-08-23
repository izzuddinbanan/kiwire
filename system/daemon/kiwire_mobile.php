<?php


error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING );


require_once dirname(__FILE__, 3) . "/server/admin/includes/include_config.php";
require_once dirname(__FILE__, 3) . "/server/admin/includes/include_general.php";


$kiwire_server = new Swoole\Http\Server("0.0.0.0", 9957, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);


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

    echo "Kiwire mobile server service started at : " . date("Y-m-d H:i:s") . "\n";

});


$kiwire_server->on("request", function ($request, $response) {


    $kiw_data = $request->rawcontent();


    $response->header("Content-Type", "application/json");


    if ($kiw_data == "check"){


        $response->end(json_encode(array("service" => "Kiwire Mobile API", "version" => 1, "time" => time())));

        return;


    }


    if (empty($kiw_data)) {


        $response->end(json_encode(array("status" => "error", "message" => "Empty payload", "data" => null)));

        return;


    }


    $kiw_data = json_decode($kiw_data, true);


    if (is_array($kiw_data)){


        if (empty($kiw_data['date']) || strlen($kiw_data['date']) < 8){


            $response->end(json_encode(array("status" => "error", "message" => "Date not long enough", "data" => null)));

            return;


        }



        if (in_array($kiw_data['action'], array("login", "voucher", "dashboard", "create_voucher", "list_voucher", "list_profile", "tenant_data", "print_voucher"))){


            $kiw_db = new mysqli("p:" . SYNC_DB1_HOST, SYNC_DB1_USER, SYNC_DB1_PASSWORD, SYNC_DB1_DATABASE, SYNC_DB1_PORT);


            if (empty($kiw_data['tenant_id'])) $kiw_data['tenant_id'] = "default";


            $kiw_admin = $kiw_db->query("SELECT SQL_CACHE tenant_id,username,password,lastlogin,fullname,permission,balance_credit FROM kiwire_admin WHERE username = '{$kiw_data['username']}' AND tenant_id = '{$kiw_data['tenant_id']}' LIMIT 1");


            if ($kiw_admin) {


                $kiw_admin = $kiw_admin->fetch_all(MYSQLI_ASSOC)[0];

                $kiw_credential = md5($kiw_data['tenant_id'] . $kiw_data['username'] . $kiw_data['date'] . md5(sync_decrypt($kiw_admin['password'])));


                if ($kiw_credential == $kiw_data['token']){


                    if ($kiw_data['action'] == "login"){


                        unset($kiw_admin['password']);

                        $response->end(json_encode(array("status" => "success", "message" => "Password matched", "data" => $kiw_admin)));

                        return;


                    } elseif ($kiw_data['action'] == "dashboard"){


                        // the the number of online user, connect and disconnect


                        $response->end(json_encode(array("status" => "success", "message" => "Password matched", "data" => null)));

                        return;


                    } elseif ($kiw_data['action'] == "create_voucher"){


                        // check if admin got money to create


                        if (!empty($kiw_data['data']['profile'])) {


                            $kiw_profile = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_profiles WHERE tenant_id = '{$kiw_data['tenant_id']}' AND name = '{$kiw_data['data']['profile']}' LIMIT 1");


                            if ($kiw_profile) {


                                $kiw_profile = $kiw_profile->fetch_all(MYSQLI_ASSOC)[0];


                                if (empty($kiw_profile)){


                                    $response->end(json_encode(array("status" => "error", "message" => "Invalid profile", "data" => "")));

                                    return;


                                }


                                $kiw_dashboard['action']       = "create_voucher";
                                $kiw_dashboard['tenant_id']    = $kiw_data['tenant_id'];
                                $kiw_dashboard['creator']      = $kiw_data['username'];
                                $kiw_dashboard['quantity']     = (int)$kiw_data['data']['quantity'];
                                $kiw_dashboard['price']        = (int)$kiw_profile['price'];
                                $kiw_dashboard['expiry_date']  = date("Y-m-d H:i:s", strtotime($kiw_data['data']['expiry_date']));

                                $kiw_dashboard['profile']        = $kiw_profile['name'];
                                $kiw_dashboard['prefix']         = $kiw_data['prefix'];
                                $kiw_dashboard['remark']         = $kiw_data['remark'];
                                $kiw_dashboard['allowed_zone']   = $kiw_data['zone_restriction'];

                                // use default value if empty

                                if (empty($kiw_dashboard['quantity'])) $kiw_dashboard['quantity'] = 1;
                                if (empty($kiw_dashboard['expiry_date'])) $kiw_dashboard['expiry_date'] = date("Y-m-d H:i:s", strtotime("+1 Month"));
                                if (empty($kiw_dashboard['allowed_zone'])) $kiw_dashboard['allowed_zone'] = "none";
                                if (empty($kiw_dashboard['prefix'])) $kiw_dashboard['prefix'] = "";
                                if (empty($kiw_dashboard['remark'])) $kiw_dashboard['remark'] = "";


                                $kiw_cost = $kiw_profile['price'] * $kiw_dashboard['quantity'];

                                if ($kiw_admin['balance_credit'] < $kiw_cost){


                                    $response->end(json_encode(array("status" => "error", "message" => "Insufficient fund", "data" => "")));

                                    return;


                                }



                                $kiw_temp = curl_init();

                                curl_setopt($kiw_temp, CURLOPT_URL, "http://127.0.0.1:9956");
                                curl_setopt($kiw_temp, CURLOPT_POST, true);
                                curl_setopt($kiw_temp, CURLOPT_POSTFIELDS, http_build_query($kiw_dashboard));
                                curl_setopt($kiw_temp, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($kiw_temp, CURLOPT_TIMEOUT, 15);
                                curl_setopt($kiw_temp, CURLOPT_CONNECTTIMEOUT, 5);

                                unset($kiw_dashboard);

                                $kiw_creation = curl_exec($kiw_temp);

                                curl_close($kiw_temp);


                                $kiw_creation = json_decode($kiw_creation, true);


                                if ($kiw_creation['status'] == "success") {


                                    $kiw_db->query("UPDATE kiwire_admin SET updated_date = NOW(), balance_credit = (balance_credit - {$kiw_cost}) WHERE tenant_id = '{$kiw_data['tenant_id']}' AND username = '{$kiw_data['username']}' LIMIT 1");

                                    $response->end(json_encode(array("status" => "success", "message" => "", "data" => $kiw_creation['voucher'])));


                                } else {

                                    $response->end(json_encode(array("status" => "error", "message" => $kiw_creation['message'], "data" => "")));

                                }


                            } else {

                                $response->end(json_encode(array("status" => "error", "message" => "Invalid profile", "data" => "")));


                            }


                        }


                        return;


                    } elseif ($kiw_data['action'] == "list_voucher"){


                        if (!empty($kiw_data['data']['search'])) {

                            $kiw_code = "AND username LIKE '%{$kiw_data['data']['search']}%'";

                        } else $kiw_code = "";


                        $kiw_search_date_type = $kiw_data['data']['date_type'];

                        if (empty($kiw_search_date_type)) $kiw_search_date_type = "date_expiry";


                        $kiw_date_start = (!empty($kiw_data['data']['date_start']) ? date("Y-m-d H:i:s", strtotime($kiw_data['data']['date_start'])) : date("Y-m-d H:i:s", strtotime("-30 Day")));
                        $kiw_date_end   = (!empty($kiw_data['data']['date_end']) ? date("Y-m-d H:i:s", strtotime($kiw_data['data']['date_end'])) : date("Y-m-d H:i:s"));


                        $kiw_timezone = $kiw_db->query("SELECT SQL_CACHE timezone FROM kiwire_clouds WHERE tenant_id = '{$kiw_data['tenant_id']}' LIMIT 1");

                        if ($kiw_timezone){


                            $kiw_timezone = $kiw_timezone->fetch_all(MYSQLI_ASSOC)[0];

                            $kiw_timezone = $kiw_timezone['timezone'];


                        }

                        if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


                        $kiw_vouchers = $kiw_db->query("SELECT username, status, CONVERT_TZ(date_create, 'UTC', '{$kiw_timezone}') AS date_create, CONVERT_TZ(date_expiry, 'UTC', '{$kiw_timezone}') AS date_expiry, CONVERT_TZ(date_activate, 'UTC', '{$kiw_timezone}') AS date_activate, bulk_id, remark FROM kiwire_account_auth WHERE (`{$kiw_search_date_type}` BETWEEN '{$kiw_date_start}' AND '{$kiw_date_end}') AND ktype = 'voucher' AND tenant_id = '{$kiw_data['tenant_id']}' {$kiw_code} LIMIT 1000");


                        if ($kiw_vouchers) {


                            $kiw_vouchers = $kiw_vouchers->fetch_all(MYSQLI_ASSOC);

                            $response->end(json_encode(array("status" => "success", "message" => null, "data" => $kiw_vouchers)));


                        } else $response->end(json_encode(array("status" => "error", "message" => "No voucher available", "data" => null)));


                        return;


                    } elseif ($kiw_data['action'] == "list_profile"){


                        $kiw_profiles = $kiw_db->query("SELECT name,price FROM kiwire_profiles WHERE tenant_id = '{$kiw_data['tenant_id']}' LIMIT 50");


                        if ($kiw_profiles) {


                            $kiw_profiles = $kiw_profiles->fetch_all(MYSQLI_ASSOC);

                            $response->end(json_encode(array("status" => "success", "message" => null, "data" => $kiw_profiles)));


                        } else $response->end(json_encode(array("status" => "error", "message" => "No profile available", "data" => null)));


                        return;


                    } elseif ($kiw_data['action'] == "tenant_data"){



                        $kiw_data = $kiw_db->query("SELECT name,address,phone,website,currency,voucher_prefix FROM kiwire_clouds WHERE tenant_id = '{$kiw_data['tenant_id']}' LIMIT 1");


                        if ($kiw_data) {


                            $kiw_data = $kiw_data->fetch_all(MYSQLI_ASSOC)[0];

                            $response->end(json_encode(array("status" => "success", "message" => null, "data" => $kiw_data)));


                        } else $response->end(json_encode(array("status" => "error", "message" => "No profile available", "data" => null)));


                        return;



                    } elseif ($kiw_data['action'] == "print_voucher"){


                        // check for logo

                        foreach (array("jpg", "jpeg", "png") as $kiw_extension){


                            if (file_exists(dirname(__FILE__, 3) . "/server/custom/{$kiw_data['tenant_id']}/logo-{$kiw_data['tenant_id']}.{$kiw_extension}") == true){


                                $kiw_logo = parse_url($request->header['host'], PHP_URL_HOST);

                                $kiw_logo = "http://{$kiw_logo}/custom/{$kiw_data['tenant_id']}/logo-{$kiw_data['tenant_id']}.{$kiw_extension}";

                                $kiw_logo = "<img src='{$kiw_logo}' style='max-height: 300px; max-width: 300px;'>";

                                break;


                            }


                        }


                        $kiw_voucher_template = $kiw_db->query("SELECT SQL_CACHE content FROM kiwire_html_template WHERE tenant_id = '{$kiw_data['tenant_id']}' AND type = 'voucher' AND id = (SELECT voucher_template FROM kiwire_clouds WHERE tenant_id = '{$kiw_data['tenant_id']}' LIMIT 1) LIMIT 1");


                        if ($kiw_voucher_template) {


                            $kiw_voucher_template = $kiw_voucher_template->fetch_all(MYSQLI_ASSOC)[0];


                            if (!empty($kiw_voucher_template)) {


                                $kiw_voucher_template = $kiw_voucher_template['content'];


                                // update logo path and company info

                                $kiw_voucher_template = str_replace("{{logo}}", $kiw_logo, $kiw_voucher_template);


                                $kiw_html_string = "";


                                $kiw_timezone = $kiw_db->query("SELECT SQL_CACHE timezone FROM kiwire_clouds WHERE tenant_id = '{$kiw_data['tenant_id']}' LIMIT 1");

                                if ($kiw_timezone){


                                    $kiw_timezone = $kiw_timezone->fetch_all(MYSQLI_ASSOC)[0];

                                    $kiw_timezone = $kiw_timezone['timezone'];


                                }


                                if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


                                if (!empty($kiw_data['data']['voucher_group'])){


                                    $kiw_vouchers = $kiw_db->query("SELECT username, password,CONVERT_TZ(date_expiry, 'UTC', '{$kiw_timezone}') AS date_expiry, price, profile_subs, remark FROM kiwire_account_auth WHERE bulk_id = '{$kiw_data['data']['voucher_group']}' AND tenant_id = '{$kiw_data['tenant_id']}' AND ktype = 'voucher' LIMIT 100");

                                    if ($kiw_vouchers) $kiw_vouchers = $kiw_vouchers->fetch_all(MYSQLI_ASSOC);


                                    foreach ($kiw_vouchers as $kiw_voucher){


                                        // update voucher info

                                        $kiw_voucher_template = str_replace("{{username}}", $kiw_voucher['username'], $kiw_voucher_template);
                                        $kiw_voucher_template = str_replace("{{password}}", sync_decrypt($kiw_voucher['password']), $kiw_voucher_template);
                                        $kiw_voucher_template = str_replace("{{date_expiry}}", $kiw_voucher['date_expiry'], $kiw_voucher_template);
                                        $kiw_voucher_template = str_replace("{{price}}", $kiw_voucher['price'], $kiw_voucher_template);
                                        $kiw_voucher_template = str_replace("{{profile}}", $kiw_voucher['profile_subs'], $kiw_voucher_template);
                                        $kiw_voucher_template = str_replace("{{remark}}", $kiw_voucher['remark'], $kiw_voucher_template);

                                        $kiw_html_string .= $kiw_voucher_template;


                                    }


                                } else {


                                    $kiw_voucher = $kiw_db->query("SELECT username, password,CONVERT_TZ(date_expiry, 'UTC', '{$kiw_timezone}') AS date_expiry, price, profile_subs, remark FROM kiwire_account_auth WHERE username = '{$kiw_data['data']['voucher_code']}' AND tenant_id = '{$kiw_data['tenant_id']}' AND ktype = 'voucher' LIMIT 1");

                                    if ($kiw_voucher) $kiw_voucher = $kiw_voucher->fetch_all(MYSQLI_ASSOC);

                                    if (!empty($kiw_voucher)) {


                                        $kiw_voucher = $kiw_voucher[0];

                                        // update voucher info

                                        $kiw_voucher_template = str_replace("{{username}}", $kiw_voucher['username'], $kiw_voucher_template);
                                        $kiw_voucher_template = str_replace("{{password}}", sync_decrypt($kiw_voucher['password']), $kiw_voucher_template);
                                        $kiw_voucher_template = str_replace("{{date_expiry}}", $kiw_voucher['date_expiry'], $kiw_voucher_template);
                                        $kiw_voucher_template = str_replace("{{price}}", $kiw_voucher['price'], $kiw_voucher_template);
                                        $kiw_voucher_template = str_replace("{{profile}}", $kiw_voucher['profile_subs'], $kiw_voucher_template);
                                        $kiw_voucher_template = str_replace("{{remark}}", $kiw_voucher['remark'], $kiw_voucher_template);

                                        $kiw_html_string = $kiw_voucher_template;


                                    }


                                }


                                // return html file to print

                                if (!empty($kiw_html_string)) {


                                    $response->end(json_encode(array("status" => "success", "message" => null, "data" => base64_encode($kiw_html_string))));


                                } else $response->end(json_encode(array("status" => "error", "message" => "No voucher available", "data" => null)));


                            } else $response->end(json_encode(array("status" => "error", "message" => "Empty voucher template", "data" => null)));


                        } else $response->end(json_encode(array("status" => "error", "message" => "No voucher template provided", "data" => null)));


                        return;



                    }



                } else {


                    $response->end(json_encode(array("status" => "error", "message" => "Wrong token provided", "data" => $kiw_credential)));

                    return;


                }


            } else {


                $response->end(json_encode(array("status" => "error", "message" => "User not found", "data" => null)));

                return;


            }


        } else {


            $response->end(json_encode(array("status" => "error", "message" => "Wrong action", "data" => null)));

            return;


        }


    } else {


        $response->end(json_encode(array("status" => "error", "message" => "Unknown request", "data" => null)));


    }


});


$kiwire_server->start();
