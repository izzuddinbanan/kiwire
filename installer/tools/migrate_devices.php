<?php


$kiw_action = "";


$kiw_input = fopen("php://stdin", "r");


while (!in_array($kiw_action, array("dump", "update"))){


    echo "Please confirm your action [ dump / update / cancel ] : ";

    $kiw_action = strtolower(trim(fread($kiw_input, 10)));

    if ($kiw_action == "cancel") die("\n");


}


fclose($kiw_input);


if ($kiw_action == "dump"){


    echo "Check database connection.. ";

    $kiw_db = new mysqli("127.0.0.1", "root", "", "kiwire");

    if ($kiw_db->connect_errno) die("Unable to connect to Kiwire Database\n");

    echo "OK\n";


    $kiw_devices = $kiw_db->query("SELECT nasname AS device_ip, shortname AS unique_id, type AS vendor, ports AS coa_port, 'controller' AS device_type, secret AS shared_secret, community, description, location, username, password, cloud_id AS tenant_id, seamless_type, snmpv, mib FROM nas");


    if ($kiw_devices){


        $kiw_devices = $kiw_devices->fetch_all(MYSQLI_ASSOC);

        if (!empty($kiw_devices)){


            file_put_contents("devices.json", json_encode($kiw_devices));

            echo "Devices data has been saved to [ devices.json ]..\n";


        } else {

            echo "No devices found..\n";

        }


    } else {

        echo "No devices found..\n";

    }


    echo "Done\n";


} elseif ($kiw_action == "update"){


    require_once "/var/www/kiwire/server/admin/includes/include_connection.php";

    $kiw_db = Database::obtain();

    $kiw_devices = file_get_contents("devices.json");


    if (!empty($kiw_devices)) {


        $kiw_devices = json_decode($kiw_devices, true);


        if ($kiw_devices) {


            $kiw_total_devices = count($kiw_devices);

            $kiw_current_count = 1;


            foreach ($kiw_devices as $kiw_device) {


                echo "Processing {$kiw_current_count}/{$kiw_total_devices} Unique ID [ {$kiw_device['unique_id']} ]..\n";

                $kiw_current_count++;


                $kiw_temp = array();


                $kiw_temp['id'] = "NULL";
                $kiw_temp['tenant_id'] = $kiw_device['tenant_id'];
                $kiw_temp['updated_date'] = "NOW()";
                $kiw_temp['unique_id'] = $kiw_device['unique_id'];
                $kiw_temp['device_ip'] = $kiw_device['device_ip'];
                $kiw_temp['coa_port'] = $kiw_device['coa_port'];
                $kiw_temp['vendor'] = $kiw_device['vendor'];
                $kiw_temp['device_type'] = $kiw_device['device_type'];
                $kiw_temp['shared_secret'] = $kiw_device['shared_secret'];
                $kiw_temp['description'] = $kiw_device['description'];
                $kiw_temp['community'] = $kiw_device['community'];
                $kiw_temp['location'] = $kiw_device['location'];
                $kiw_temp['monitor_method'] = $kiw_device['monitor_method'];
                $kiw_temp['username'] = $kiw_device['username'];
                $kiw_temp['password'] = $kiw_device['password'];
                $kiw_temp['seamless_type'] = $kiw_device['seamless_type'];
                $kiw_temp['snmpv'] = $kiw_device['snmpv'];
                $kiw_temp['mib'] = $kiw_device['mib'];


                $kiw_db->insert("kiwire_controller", $kiw_temp);

                unset($kiw_temp);


            }


        } else {

            echo "Not valid [ devices.json ] file.\n";

        }


    } else {

        echo "File [ devices.json ] not found.\n";

    }

    echo "Done\n";


}
















