<?php


// check for all variables

if ($argc != 3) {

    echo "Insufficient arguments\n";
    echo "Usage: \tphp " . basename(__FILE__) . " [action] [tenant]\n";
    echo "  action: dump or update\n";
    echo "  tenant: tenant [previously known as cloud id]\n";

    die();

}


$kiw_action = $argv[1];
$kiw_tenant = $argv[2];


if (!in_array($kiw_action, array("dump", "update"))){

    echo "Unknown action\n";
    die();

}


$kiw_input = fopen("php://stdin", "r");

echo "Are you sure to [ {$kiw_action} ] for tenant [ {$kiw_tenant} ] ? (Y or N): ";


if (strtolower(trim(fread($kiw_input, 10))) !== "y"){

    echo "Terminated\n";
    die();

}


echo "Check database connection.. ";

$kiw_db = new mysqli("127.0.0.1", "root", "", "kiwire");

if ($kiw_db->connect_errno) die("Kiwire: Unable to connect to database.\n");

echo "OK\n";


// set the default filename

$kiw_filename = "migrate_password_history_{$kiw_tenant}.csv";


if ($kiw_action == "dump") {



    echo "Check table existance.. ";

    $kiw_test = $kiw_db->query("SELECT username FROM kiwire_user LIMIT 1");

    if ($kiw_test->num_rows == 0){

        echo "\nEmpty table, or table not found. Action [ {$kiw_action} ] only work with version 2\n";
        echo "Terminated\n";
        die();

    }

    echo "OK\n";


    echo "Check if dump file existed.. ";


    if (file_exists(dirname(__FILE__) . "/" . $kiw_filename) == true) {


        echo "\nFile [ {$kiw_filename} ] already existed. Do you want to overwrite? (Y or N): ";


        if (strtolower(trim(fread($kiw_input, 10))) !== "y") {

            echo "OK. Please rename your current file to something else and re-run this program.\n";
            echo "Terminated\n";
            die();

        }


    }

    echo "OK\n";


    echo "Collect data for tenant [ {$kiw_tenant} ].. ";

    $kiw_connection = $kiw_db->query("SELECT cloud_id,username,pass_his FROM kiwire_user WHERE cloud_id = '{$kiw_tenant}' AND pass_his IS NOT NULL");

    echo "OK\n";


    $kiw_max = $kiw_connection->num_rows;

    $kiw_counter = 0;


    echo "There are [ {$kiw_max} ] records.\n";

    echo "Start dumping data to [ {$kiw_filename} ]..\n";


    $kiw_csv = fopen(dirname(__FILE__) . "/" . $kiw_filename, "w");


    while ($kiw_password = $kiw_connection->fetch_assoc()) {


        $kiw_password['pass_his'] = explode("||", $kiw_password['pass_his']);

        $kiw_password['pass_his'] = array_filter($kiw_password['pass_his']);



        if (is_array($kiw_password['pass_his']) && !empty($kiw_password['pass_his'])){


            for ($kiw_x = 0; $kiw_x < count($kiw_password['pass_his']); $kiw_x++){

                $kiw_password['pass_his'][$kiw_x] = trim($kiw_password['pass_his'][$kiw_x]);

            }

            $kiw_password['pass_his'] = implode("|*|", $kiw_password['pass_his']);


        } else {

            $kiw_password['pass_his'] = "[EMPTY]";

        }


        fwrite($kiw_csv, "{$kiw_password['cloud_id']},{$kiw_password['username']},{$kiw_password['pass_his']}\n");


        $kiw_counter++;


        echo "\rProcessing: " . round(($kiw_counter / $kiw_max) * 100, 0) . "%";


        if ($kiw_counter == $kiw_max){

            echo "\nCompleted.\n";

        }


    }


    echo "Save the CSV file.. ";

    fclose($kiw_csv);

    echo "OK\n";



    echo "Close database connection.. ";

    $kiw_db->close();

    echo "OK\n";


    echo "Dumping data for tenant [ {$kiw_tenant} ] completed..\n";
    echo "You can now copy the file to the new server and run this script with update command\n";
    echo "Example: scp {$kiw_filename} root@192.168.0.0:~\n\n";


} else {


    if (file_exists("/var/www/kiwire/server/admin/includes/include_general.php") == false){

        echo "Action [ {$kiw_action} ] only work with version 3\n";
        echo "Terminated\n";
        die();

    }


    require_once "/var/www/kiwire/server/admin/includes/include_general.php";


    echo "Check the CSV file.. ";


    $kiw_filename = dirname(__FILE__) . "/" . $kiw_filename;


    if (file_exists($kiw_filename) == false){


        echo "\nFile [ {$kiw_filename} ] not existed.\n";
        echo "Please provide full path to the file [example: /root/migrate_password_history_{$kiw_tenant}.csv] or N to cancel: ";


        $kiw_filename = trim(fread($kiw_input, 255));


        if (strtolower($kiw_filename) == "n") {

            echo "OK.\n";
            echo "Terminated\n";
            die();

        } elseif (file_exists($kiw_filename) == false){

            echo "File not found.\n";
            echo "Terminated\n";
            die();

        }


    }

    echo "OK\n";


    echo "Confirm update database for tenant [ {$kiw_tenant} ] using file [ " . basename($kiw_filename) . " ] ? (Y or N): ";


    if (strtolower(trim(fread($kiw_input, 10))) !== "y"){

        echo "OK\n";
        echo "Terminated\n";
        die();

    }


    $kiw_counter = 0;
    $kiw_row_number = 0;

    $kiw_ignore_mismatched = false;
    $kiw_possible_error = false;


    $kiw_csv = fopen($kiw_filename, "r");


    while (!feof($kiw_csv)){


        $kiw_password = trim(fgets($kiw_csv, 1024));

        $kiw_password = explode(",", $kiw_password);


        if (count($kiw_password) > 3){


            file_put_contents(dirname($kiw_filename) . "/migrate_history_error_{$kiw_tenant}.log", "LINE: {$kiw_counter} TENANT: {$kiw_password[0]} USERNAME: {$kiw_password[1]}\n", FILE_APPEND);

            $kiw_possible_error = true;

            continue;


        }


        if (is_array($kiw_password) && !empty($kiw_password) && !empty($kiw_password[0])) {


            if ($kiw_tenant !== $kiw_password[0] && $kiw_ignore_mismatched == false){

                echo "\n\n";
                echo str_repeat("*", 30) . " ERROR! " . str_repeat("*", 30);
                echo "\n\n";

                echo "Tenant ID mismatched between you have specified [ {$kiw_tenant} ] and data in the file [ {$kiw_password[0]} ].\n";
                echo "Proceed will cause the password updated for tenant [ {$kiw_tenant} ] instead of tenant [ {$kiw_password[0]} ]\n";
                echo "Are you sure to proceed? (Y or N): ";

                if (strtolower(trim(fread($kiw_input, 10))) !== "y"){

                    echo "OK\n";
                    echo "Terminated\n";
                    die();

                } else {

                    $kiw_ignore_mismatched = true;

                }

                echo "\n";


            }


            if ($kiw_password[2] !== "[EMPTY]") {


                $kiw_password[2] = explode("|*|", $kiw_password[2]);


                if (!empty($kiw_password[2])) {


                    for ($kiw_x = 0; $kiw_x < count($kiw_password[2]); $kiw_x++) {

                        $kiw_password[2][$kiw_x] = sync_encrypt(trim($kiw_password[2][$kiw_x]));

                    }


                    $kiw_password[2] = implode(",", $kiw_password[2]);


                    //$kiw_db->query("UPDATE kiwire_account_auth SET password_history = '{$kiw_password[2]}' WHERE tenant_id = '{$kiw_password[0]}' AND username = '{$kiw_password[1]}' LIMIT 1");

                    $kiw_row_number++;

                    sleep(1);

                }


            }


            $kiw_counter++;

            echo "\rProcessing: Line number {$kiw_counter}";


        }


    }


    echo "\nClose the CSV file.. ";

    fclose($kiw_csv);

    echo "OK\n";


    echo "Close database connection.. ";

    $kiw_db->close();

    echo "OK\n";


    echo "Database has been updated with [ {$kiw_row_number} ] out of [ {$kiw_counter} ] records..\n";


    if ($kiw_possible_error == true){

        echo "PLEASE CHECK FILE migrate_history_error_{$kiw_tenant}.log AS THERE IS ERROR HAPPENED DURING THE PROCESS\n";

    }

    echo "\n";


}


// close the cli input

fclose($kiw_input);

