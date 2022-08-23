<?php


error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);


require_once dirname(__FILE__, 3) . "/server/admin/includes/include_config.php";
require_once dirname(__FILE__, 3) . "/server/admin/includes/include_connection.php";


$kiw_not_oid = array("id", "tenant_id", "mib_name", "updated_date", "description", "if_speed");


$kiw_file_descriptor = fopen('php://stdin', 'r');

$kiw_device = null;


while (true){


    //$kiw_device = '{"unique_id" : "Developer-Test","tenant_id" : "tutorial2","device_ip" : "192.168.0.113","monitor_method" : "snmp","mib" : "test-mib","snmpv" : "1","community" : "public"}';


    $kiw_device .= trim(fread($kiw_file_descriptor, 1024));


    $kiw_device = json_decode($kiw_device, true);


    if ($kiw_device) {


        $kiw_status = array();


        $kiw_status['unique_id'] = $kiw_device['unique_id'];
        $kiw_status['tenant_id'] = $kiw_device['tenant_id'];


        $kiw_device['device_ip'] = preg_replace("/[^A-Za-z0-9-.]+$/", "", $kiw_device['device_ip']);

        if (strlen($kiw_device['device_ip']) > 3) {


            $kiw_results = `/usr/bin/ping -c 5 -W 5 {$kiw_device['device_ip']} 2>&1`;

            $kiw_results = explode(PHP_EOL, $kiw_results);

            $kiw_result = "";


            foreach ($kiw_results as $kiw_result) {


                if (strpos($kiw_result, "transmitted")) {

                    if (strpos($kiw_result, "errors")) {


                        $kiw_result = explode(",", $kiw_result);

                        $kiw_result = trim(explode(" ", trim($kiw_result[3]))[0]) . "\n";


                    } else {


                        $kiw_result = explode(",", $kiw_result);

                        $kiw_result = trim(explode(" ", trim($kiw_result[2]))[0]) . "\n";


                    }

                    break;

                }


            }


            unset($kiw_results);


            // change percentage failed to percentage success

            if (strlen($kiw_result) > 0) {

                $kiw_status['ping'] = 100 - (int)trim($kiw_result, "%\n");

            } else $kiw_status['ping'] = 0;


            // set the status based on ping

            if ($kiw_status['ping'] > 50) {

                $kiw_status['reason'] = "Ping OK";
                $kiw_status['status'] = "running";

            } elseif ($kiw_status['ping'] == 0){

                $kiw_status['reason'] = "Not reachable";
                $kiw_status['status'] = "down";

            } else {

                $kiw_status['reason'] = "Ping < 50%";
                $kiw_status['status'] = "warning";

            }


        } else {


            $kiw_status['ping'] = 0;

            $kiw_status['status'] = "unknown";


        }


        if ($kiw_status['status'] == "running" && $kiw_device['monitor_method'] == "snmp"){


            $kiw_snmp_mib = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_nms_mib WHERE mib_name = '{$kiw_device['mib']}' AND tenant_id = '{$kiw_device['tenant_id']}' LIMIT 1");

            $kiw_test = true;


            if (is_array($kiw_snmp_mib) && count($kiw_snmp_mib) > 0){


                foreach ($kiw_snmp_mib as $kiw_key => $kiw_value){


                    if (!in_array($kiw_key, $kiw_not_oid)) {


                        if (!empty($kiw_value)) {


                            if ($kiw_device['snmpv'] == "2c"){

                                $kiw_data = snmp2_walk($kiw_device['device_ip'], $kiw_device['community'], $kiw_value, 30000, 3);

                            } else {

                                $kiw_data = snmpwalk($kiw_device['device_ip'], $kiw_device['community'], $kiw_value, 30000, 3);

                            }


                            if ($kiw_data == false) $kiw_test = false;


                            // process the data received

                            if (in_array($kiw_key, array("input_vol", "output_vol"))){


                                $kiw_interfaces = explode(",", $kiw_snmp_mib['if_speed']);


                                if (is_array($kiw_interfaces) && count($kiw_interfaces) > 0) {


                                    foreach ($kiw_interfaces as $kiw_interface) {

                                        $kiw_status[$kiw_key] += (int)(explode(":", $kiw_data[$kiw_interface])[1]);

                                    }

                                    unset($kiw_interface);


                                } else {

                                    foreach($kiw_data as $kiw_datum) {

                                        $kiw_status[$kiw_key] += (int)(explode(":", $kiw_datum)[1]);

                                    }

                                    unset($kiw_datum);

                                }


                            } elseif (in_array($kiw_key, array("if_status", "if_desc"))){


                                foreach($kiw_data as $kiw_datum) {

                                    $kiw_status[$kiw_key][] = ltrim(explode(":", $kiw_datum)[1]);

                                }


                                unset($kiw_datum);


                            } elseif ($kiw_key == "uptime"){


                                $kiw_time = ltrim(trim(explode(":", $kiw_data[0])[1]));

                                $kiw_time = substr($kiw_time, (strpos($kiw_time, "(") + 1), (strpos($kiw_time, ")") - 1));

                                $kiw_status[$kiw_key] = (int)($kiw_time / 100);

                                unset($kiw_time);


                            } elseif ($kiw_key == "device_count") {


                                $kiw_status[$kiw_key] = count($kiw_data);


                            } else {


                                if (count($kiw_data) > 1 && substr($kiw_data[0], 0, 7) == "INTEGER") {


                                    foreach ($kiw_data as $kiw_datum) {

                                        $kiw_status[$kiw_key] += (int)(explode(":", $kiw_datum)[1]);

                                    }


                                } else {


                                    $kiw_status[$kiw_key] = ltrim(trim(explode(":", $kiw_data[0])[1]));


                                }


                            }


                            unset($kiw_data);


                        }


                    }


                }


            }


            if ($kiw_test == false){

                $kiw_status['reason'] = "SNMP or part of it are not retrievable";
                $kiw_status['status'] = "warning";

            }


            unset($kiw_test);


        }


        // save array to json format for future reference

        if (is_array($kiw_status['if_status'])) $kiw_status['if_status'] = json_encode($kiw_status['if_status']);
        if (is_array($kiw_status['if_desc'])) $kiw_status['if_desc']   = json_encode($kiw_status['if_desc']);


        // set to zero so that nms processor pickup the data

        $kiw_status['processed'] = 0;


        // update the device status

        if ($kiw_status['status'] !== "down") $kiw_status_time = ", last_update = NOW()";
        else $kiw_status_time = "";

        $kiw_db->query("UPDATE kiwire_controller SET updated_date = NOW(), status = '{$kiw_status['status']}'{$kiw_status_time} WHERE unique_id = '{$kiw_status['unique_id']}' AND tenant_id = '{$kiw_status['tenant_id']}' LIMIT 1");


        // insert to nms log

        $kiw_db->insert("kiwire_nms_log", $kiw_status);


        unset($kiw_status);


    }


    // tell scheduler to move to next device

    echo json_encode(array("status" => "success"));


}
