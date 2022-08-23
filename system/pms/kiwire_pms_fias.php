<?php


error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);


require_once dirname(__FILE__, 4) . "/admin/includes/include_config.php";

require_once dirname(__FILE__, 5) . "/system/pms/kiwire_pms_functions.php";


$kiw_tenant = dirname(__FILE__);

if (strpos($kiw_tenant, "server/custom") == false){

    echo "This script need to be run from the tenant folder. Example: /custom/default/pms/\n";

}

$kiw_tenant = array_filter(explode("/", $kiw_tenant));


foreach ($kiw_tenant as $kiw_index => $kiw_tenant_){

    if ($kiw_tenant_ == "custom"){

        $kiw_tenant = $kiw_tenant[$kiw_index + 1];

        break;

    }

}

unset($kiw_index);

unset($kiw_tenant_);


if (is_array($kiw_tenant)){

    echo "This script need to be run from the tenant folder. Example: /custom/default/pms/\n";

}



$kiw_stx = chr(02);
$kiw_etx = chr(03);

$kiw_alive_time = time();
$kiw_action_time = time();

$kiw_night_audit = false;
$kiw_db_swap = false;


while (true){


    // get the latest data from database

    $kiw_db = new mysqli(SYNC_DB1_HOST, SYNC_DB1_USER, SYNC_DB1_PASSWORD, SYNC_DB1_DATABASE, SYNC_DB1_PORT);

    $kiw_pms_setting = $kiw_db->query("SELECT * FROM kiwire_int_pms WHERE tenant_id = '{$kiw_tenant}' LIMIT 1");

    if ($kiw_pms_setting) $kiw_pms_setting = $kiw_pms_setting->fetch_all(MYSQLI_ASSOC)[0];
    else {

        pms_logger("No PMS setting has been set for this tenant.", "micros", $kiw_tenant);

        die("No PMS setting has been set for this tenant.");

    }


    // set the timezone for this script to follow tenant setting

    $kiw_temp = $kiw_db->query("SELECT timezone FROM kiwire_clouds WHERE tenant_id = '{$kiw_tenant}' LIMIT 1");

    if ($kiw_temp) $kiw_temp = $kiw_temp->fetch_all(MYSQLI_ASSOC)[0];

    if (empty($kiw_temp['timezone'])) $kiw_temp['timezone'] = "Asia/Kuala_Lumpur";

    date_default_timezone_set($kiw_temp['timezone']);

    unset($kiw_temp);


    // check for vip profiles

    $kiw_vips = $kiw_db->query("SELECT SQL_CACHE * FROM kiwire_int_pms_vipcode WHERE tenant_id = '{$kiw_tenant}'");

    if ($kiw_vips) $kiw_vips = $kiw_vips->fetch_all(MYSQLI_ASSOC);


    $kiw_db->close();

    unset($kiw_db);


    // check the pms setting

    if (in_array($kiw_pms_setting['pms_type'], array("fias", "opera", "infor", "oasis")) == false) {

        pms_logger("PMS was set not to use FIAS protocol", $kiw_pms_setting['pms_type'], $kiw_tenant);

        die("PMS was set not to use FIAS protocol");

    }


    // get the connection and reconnect if lost

    if ($kiw_connection = stream_socket_client("tcp://{$kiw_pms_setting['pms_host']}:{$kiw_pms_setting['pms_port']}", $kiw_error, $kiw_error_string, 60, STREAM_CLIENT_CONNECT)) {


        pms_logger("Connected to Host: {$kiw_pms_setting['pms_host']} Port: {$kiw_pms_setting['pms_port']}", "micros", $kiw_tenant);


        stream_set_blocking($kiw_connection, false);

        stream_set_timeout($kiw_connection, 3);


        while (true){


            if (feof($kiw_connection)){


                pms_logger("Connection lost", "micros", $kiw_tenant);

                stream_socket_shutdown($kiw_connection, STREAM_SHUT_WR);

                break;


            }


            if ((time() - $kiw_alive_time) >= 60){


                $kiw_time['date'] = date("ymd");
                $kiw_time['time'] = date("his");


                pms_write_stream($kiw_connection, "{$kiw_stx}LA|DA{$kiw_time['date']}|TI{$kiw_time['time']}|{$kiw_etx}", "micros", $kiw_tenant);

                unset($kiw_time);


                // set time to now to ssend alive for another 60 seconds

                $kiw_alive_time = time();


            }



            // capture new data coming in from pms server

            $kiw_buffer .= fread($kiw_connection, 1024);


            if (!empty($kiw_buffer)) {


                // split to array

                $kiw_buffer = explode($kiw_etx, $kiw_buffer);


                // remove empty string

                $kiw_buffer = array_filter($kiw_buffer);


                // get the oldest data first

                $kiw_data = array_shift($kiw_buffer);


                // remove any unneeded character from the string

                $kiw_data = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $kiw_data);


                // process the data

                if (!empty($kiw_data)) {


                    pms_logger("RX: " . $kiw_data, "micros", $kiw_tenant);


                    $kiw_db = new mysqli(SYNC_DB1_HOST, SYNC_DB1_USER, SYNC_DB1_PASSWORD, SYNC_DB1_DATABASE, SYNC_DB1_PORT);


                    if ($kiw_db->connect_errno == 0){


                        $kiw_data = array_filter(explode("|", $kiw_data));


                        if ($kiw_data[0] == "RE"){


                            array_shift($kiw_data);
                            array_shift($kiw_data);
                            array_shift($kiw_data);

                            $kiw_data = array_filter($kiw_data);


                        }


                        if ($kiw_data[0] == "DE"){


                            pms_logger("RX: DB Swap completed.", "micros", $kiw_tenant);

                            $kiw_db_swap = false;


                        } elseif ($kiw_data[0] == "LE"){


                            pms_logger("RX: Link terminated as per instructed.", "micros", $kiw_tenant);

                            break;


                        } elseif ($kiw_data[0] == "NS"){


                            pms_logger("RX: Night audit started. Posting paused.", "micros", $kiw_tenant);

                            $kiw_night_audit = true;


                        } elseif ($kiw_data[0] == "NE"){


                            pms_logger("RX: Night audit finished. Posting resumed.", "micros", $kiw_tenant);

                            $kiw_night_audit = false;


                        } elseif ($kiw_data[0] == "LS"){


                            pms_logger("RX: Link started.", "micros", $kiw_tenant);


                            $kiw_time['date'] = date("ymd");
                            $kiw_time['time'] = date("his");

                            pms_write_stream($kiw_connection, "{$kiw_stx}LD|DA{$kiw_time['date']}|TI{$kiw_time['time']}|V#1.0|IFWW|{$kiw_etx}", "micros", $kiw_tenant);
                            pms_write_stream($kiw_connection, "{$kiw_stx}LR|RIPS|FLRNTADATIPTCTP#|{$kiw_etx}", "micros", $kiw_tenant);
                            pms_write_stream($kiw_connection, "{$kiw_stx}LR|RIPA|FLASDATIRNP#|{$kiw_etx}", "micros", $kiw_tenant);
                            pms_write_stream($kiw_connection, "{$kiw_stx}LR|RIGI|FLRNG#GFGTGSGNGLGVGAGDA0A1A2A3SF|{$kiw_etx}", "micros", $kiw_tenant);
                            pms_write_stream($kiw_connection, "{$kiw_stx}LR|RIGC|FLRNG#GFGTGSGNGLGVGAGDA0A1A2A3RO|{$kiw_etx}", "micros", $kiw_tenant);
                            pms_write_stream($kiw_connection, "{$kiw_stx}LR|RIGO|FLRNG#GSSF|{$kiw_etx}", "micros", $kiw_tenant);
                            pms_write_stream($kiw_connection, "{$kiw_stx}LR|RINS|FLDATI|{$kiw_etx}", "micros", $kiw_tenant);
                            pms_write_stream($kiw_connection, "{$kiw_stx}LR|RINE|FLDATI|{$kiw_etx}", "micros", $kiw_tenant);
                            pms_write_stream($kiw_connection, "{$kiw_stx}LR|RIDR|FLDATI|{$kiw_etx}", "micros", $kiw_tenant);
                            pms_write_stream($kiw_connection, "{$kiw_stx}LR|RIDS|FLDATI|{$kiw_etx}", "micros", $kiw_tenant);
                            pms_write_stream($kiw_connection, "{$kiw_stx}LR|RIDE|FLDATI|{$kiw_etx}", "micros", $kiw_tenant);
                            pms_write_stream($kiw_connection, "{$kiw_stx}LR|RIRE|FLRNCS|{$kiw_etx}", "micros", $kiw_tenant);
                            pms_write_stream($kiw_connection, "{$kiw_stx}LA|DA{$kiw_time['date']}|TI{$kiw_time['time']}|{$kiw_etx}", "micros", $kiw_tenant);

                            unset($kiw_time);


                        } elseif ($kiw_data[0] == "PA"){


                            pms_logger("RX: Posting answer.", "micros", $kiw_tenant);


                            $kiw_response = array();


                            foreach ($kiw_data as $kiw_datum){


                                switch (substr($kiw_datum, 0, 2)){


                                    case "AS" : $kiw_response['status']     = strtolower(substr($kiw_datum, 2)); break;
                                    case "RN" : $kiw_response['room_no']    = substr($kiw_datum, 2); break;
                                    case "DA" : $kiw_response['date']       = substr($kiw_datum, 2); break;
                                    case "TI" : $kiw_response['time']       = substr($kiw_datum, 2); break;
                                    case "P#" : $kiw_response['id']         = substr($kiw_datum, 2); break;


                                }


                            }


                            $kiw_db->query("UPDATE kiwire_int_pms_payment SET updated_date = NOW(), status = '{$kiw_response['status']}' WHERE (id = '{$kiw_response['id']}' || room = '{$kiw_response['room_no']}') AND tenant_id = '{$kiw_tenant}' LIMIT 1");

                            unset($kiw_response);


                        } elseif (in_array($kiw_data[0], array("GI", "GO", "GC"))){


                            // get the data detail

                            $kiw_response = array();

                            $kiw_response['action'] = $kiw_data[0];


                            foreach ($kiw_data as $kiw_datum){


                                switch (strtoupper(substr($kiw_datum, 0, 2))){

                                    case "RN" : $kiw_response['room_no']       = substr($kiw_datum, 2); break;
                                    case "G#" : $kiw_response['guest_id']      = substr($kiw_datum, 2); break;
                                    case "GS" : $kiw_response['guest_sharer']  = substr($kiw_datum, 2); break;
                                    case "GN" : $kiw_response['guest_name']    = substr($kiw_datum, 2); break;
                                    case "GF" : $kiw_response['guest_first']   = substr($kiw_datum, 2); break;
                                    case "GT" : $kiw_response['guest_title']   = substr($kiw_datum, 2); break;
                                    case "GA" : $kiw_response['guest_arrival'] = substr($kiw_datum, 2); break;
                                    case "GD" : $kiw_response['guest_depart']  = substr($kiw_datum, 2); break;
                                    case "GV" : $kiw_response['guest_vip']     = substr($kiw_datum, 2); break;
                                    case "RO" : $kiw_response['room_old']      = substr($kiw_datum, 2); break;
                                    case "DA" : $kiw_response['date']          = substr($kiw_datum, 2); break;
                                    case "TI" : $kiw_response['time']          = substr($kiw_datum, 2); break;
                                    case "GL" : $kiw_response['language']      = substr($kiw_datum, 2); break;
                                    case "A0" : $kiw_response['a0']            = substr($kiw_datum, 2); break;
                                    case "A1" : $kiw_response['a1']            = substr($kiw_datum, 2); break;
                                    case "A2" : $kiw_response['a2']            = substr($kiw_datum, 2); break;
                                    case "A3" : $kiw_response['a3']            = substr($kiw_datum, 2); break;
                                    case "A4" : $kiw_response['a4']            = substr($kiw_datum, 2); break;
                                    case "A5" : $kiw_response['a5']            = substr($kiw_datum, 2); break;
                                    case "A6" : $kiw_response['a6']            = substr($kiw_datum, 2); break;
                                    case "A7" : $kiw_response['a7']            = substr($kiw_datum, 2); break;
                                    case "A8" : $kiw_response['a8']            = substr($kiw_datum, 2); break;
                                    case "A9" : $kiw_response['a9']            = substr($kiw_datum, 2); break;

                                }


                            }

                            unset($kiw_datum);


                            if (empty($kiw_response['guest_vip'])) $kiw_response['guest_vip'] = "";


                            // transform the full name from array

                            if (in_array($kiw_response['action'], array("GI", "GC"))) {


                                if (empty($kiw_response['guest_first'])) {


                                    $kiw_temp = explode(",", $kiw_response['guest_name']);

                                    if (count($kiw_temp) > 1) {

                                        $kiw_response['guest_first'] = $kiw_temp[1];
                                        $kiw_response['guest_last'] = $kiw_temp[0];

                                    } else {

                                        $kiw_response['guest_first'] = $kiw_temp[0];
                                        $kiw_response['guest_last'] = $kiw_temp[0];

                                    }


                                    $kiw_response['guest_name'] = "{$kiw_response['guest_first']}, {$kiw_response['guest_last']}";

                                    unset($kiw_temp);


                                } else {


                                    $kiw_temps = array_filter(explode(",", $kiw_response['guest_name']));


                                    if (count($kiw_temps) > 1) {


                                        foreach ($kiw_temps as $kiw_temp) {

                                            if (trim($kiw_temp) != $kiw_response['guest_first']) {

                                                $kiw_temp_[] = $kiw_temp;

                                            }

                                        }


                                        $kiw_response['guest_last'] = ltrim(trim($kiw_temp_, ","));

                                        unset($kiw_temp_);
                                        unset($kiw_temps);
                                        unset($kiw_temp);


                                        $kiw_response['guest_name'] = "{$kiw_response['guest_first']}, {$kiw_response['guest_last']}";


                                    } else {


                                        $kiw_response['guest_last'] = $kiw_temps[0];

                                        $kiw_response['guest_name'] = $kiw_response['guest_first'] . ", " . $kiw_temps[0];


                                    }


                                }


                            }


                            // check for title

                            /*
                            if (!empty($kiw_response['guest_title'])){

                                $kiw_response['guest_name'] = $kiw_response['guest_title'] . " " . $kiw_response['guest_name'];

                            }
                            */


                            // get the random password if required

                            switch ($kiw_pms_setting['pass_mode']){

                                case 0: $kiw_response['password'] = $kiw_pms_setting['pass_predefined']; break;
                                case 1: $kiw_response['password'] = $kiw_response['room_no']; break;
                                default: $kiw_response['password'] = pms_password(); break;

                            }



                            // guest check in

                            if ($kiw_response['action'] == "GI"){


                                if (pms_check_in($kiw_db, "micros", $kiw_tenant, $kiw_response) == true){

                                    pms_logger("CHECK-IN confirmed. Room [ {$kiw_response['room_no']} ] Guest [ {$kiw_response['guest_name']} ].", "micros", $kiw_tenant);

                                } else pms_logger("CHECK-IN failed. Room [ {$kiw_response['room_no']} ] Guest [ {$kiw_response['guest_name']} ].", "micros", $kiw_tenant);


                            // guest check out

                            } elseif ($kiw_response['action'] == "GO"){


                                if (pms_check_out($kiw_db, "micros", $kiw_tenant, $kiw_response) == true){

                                    pms_logger("CHECK-OUT confirmed. Room [ {$kiw_response['room_no']} ].", "micros", $kiw_tenant);

                                } else pms_logger("CHECK-OUT failed. Room [ {$kiw_response['room_no']} ].", "micros", $kiw_tenant);


                            // guest change info

                            } elseif ($kiw_response['action'] == "GC"){


                                if (!empty($kiw_response['room_old'])){


                                    if (pms_move_room($kiw_db, "micros", $kiw_tenant, $kiw_response['room_old'], $kiw_response['room_no']) == true){

                                        pms_logger("CHANGE-ROOM confirmed. Room [ {$kiw_response['room_no']} ].", "micros", $kiw_tenant);

                                    } else pms_logger("CHANGE-ROOM failed. Room [ {$kiw_response['room_no']} ].", "micros", $kiw_tenant);


                                } else {


                                    if (pms_update_info($kiw_db, "micros", $kiw_tenant, $kiw_response) == true){

                                        pms_logger("UPDATE-INFO confirmed. Room [ {$kiw_response['room_no']} ].", "micros", $kiw_tenant);

                                    } else pms_logger("UPDATE-INFO failed. Room [ {$kiw_response['room_no']} ].", "micros", $kiw_tenant);


                                }


                            }


                            unset($kiw_response);


                        }


                        $kiw_db->close();


                    }


                    unset($kiw_db);


                }


                unset($kiw_data);


                if (!empty($kiw_buffer)) {


                    // add into buffer again.

                    foreach ($kiw_buffer as $kiw_buffer_) {

                        if (substr($kiw_buffer_, 0, 1) == $kiw_stx) {

                            $kiw_buffer__[] = $kiw_buffer_;

                        }

                    }


                    unset($kiw_buffer_);

                    $kiw_buffer = implode($kiw_etx, $kiw_buffer__);

                    unset($kiw_buffer__);


                }


                // force buffer to become string instead of array

                if (is_array($kiw_buffer)) $kiw_buffer = implode($kiw_etx, $kiw_buffer);


            } else {


                if ((time() - $kiw_action_time) > 30 && $kiw_night_audit == false && $kiw_db_swap == false){


                    // check if any action required to action

                    $kiw_cache = new Redis();
                    $kiw_cache->connect(SYNC_REDIS_HOST, SYNC_REDIS_PORT);
                    $kiw_cache->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);


                    $kiw_time['date'] = date("ymd");
                    $kiw_time['time'] = date("his");


                    if ($kiw_cache->exists("PMS_ACTION:{$kiw_tenant}")){


                        $kiw_action = $kiw_cache->get("PMS_ACTION:{$kiw_tenant}");


                        if ($kiw_action == "shutdown"){


                            pms_logger("Kiwire instructed to close connection", "micros", $kiw_tenant);

                            pms_write_stream($kiw_connection, "{$kiw_stx}LE|DA{$kiw_time['date']}|TI{$kiw_time['time']}|{$kiw_etx}", "micros", $kiw_tenant);

                            stream_socket_shutdown($kiw_connection, STREAM_SHUT_WR);

                            break;


                        } elseif ($kiw_action == "sync") {


                            $kiw_db = new mysqli(SYNC_DB1_HOST, SYNC_DB1_USER, SYNC_DB1_PASSWORD, SYNC_DB1_DATABASE, SYNC_DB1_PORT);

                            $kiw_db->query("UPDATE kiwire_int_pms_transaction SET updated_date = NOW(), check_out_date = NOW(), status = 'db-sync' WHERE (status = 'check-in' OR status = 'move-in') AND tenant_id = '{$kiw_tenant}' LIMIT 1");

                            $kiw_db->close();

                            unset($kiw_db);


                            $kiw_db_swap = true;

                            pms_write_stream($kiw_connection, "{$kiw_stx}DR|DA{$kiw_time['date']}|TI{$kiw_time['time']}|{$kiw_etx}", "micros", $kiw_tenant);


                        }


                        unset($kiw_time);


                        // delete key to avoid duplicate request

                        $kiw_cache->del("PMS_ACTION:{$kiw_tenant}");


                    } else {


                        // check if post required to pms

                        $kiw_db = new mysqli(SYNC_DB1_HOST, SYNC_DB1_USER, SYNC_DB1_PASSWORD, SYNC_DB1_DATABASE, SYNC_DB1_PORT);

                        $kiw_pms_posts = $kiw_db->query("SELECT * FROM kiwire_int_pms_payment WHERE status = 'new' AND tenant_id = '{$kiw_tenant}' LIMIT 1");

                        if ($kiw_pms_posts) $kiw_pms_posts = $kiw_pms_posts->fetch_all(MYSQLI_ASSOC);


                        foreach ($kiw_pms_posts as $kiw_pms_post){


                            $kiw_pms_post['amount'] = number_format($kiw_pms_post['amount'], 2, "", "");

                            pms_write_stream($kiw_connection, "{$kiw_stx}PS|RN{$kiw_pms_post['room']}|TA{$kiw_pms_post['amount']}|DA{$kiw_time['date']}|TI{$kiw_time['time']}|PTC|CTWIFI/{$kiw_pms_post['profile']}|P#{$kiw_pms_post['id']}|{$kiw_etx}", "micros", $kiw_tenant);

                            $kiw_db->query("UPDATE kiwire_int_pms_payment SET updated_date = NOW(), post_date = NOW(), status = 'sent' WHERE tenant_id = '{$kiw_tenant}' AND id = '{$kiw_pms_post['id']}' LIMIT 1");

                            usleep(500);


                        }


                        $kiw_db->close();


                        unset($kiw_db);

                        unset($kiw_pms_post);

                        unset($kiw_pms_posts);


                    }


                    // save action time to redis so that we can view in admin page

                    $kiw_cache->set("PMS_ACTION_CHECK:{$kiw_tenant}", time());

                    $kiw_cache->close();

                    unset($kiw_cache);


                    // update action time to latest to avoid duplicate

                    $kiw_action_time = time();


                }


            }


            sleep(1);


        }


    }


}
