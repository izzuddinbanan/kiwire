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

$kiw_filename = "migrate_account_quota_{$kiw_tenant}.csv";


if ($kiw_action == "dump") {



    echo "Check table existance.. ";

    $kiw_test = $kiw_db->query("SELECT * FROM information_schema.TABLES WHERE table_name = 'radacct'");

    if ($kiw_test->num_rows == 0){

        echo "\nTable not found. Action [ {$kiw_action} ] only work with version 2\n";
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

    $kiw_connection = $kiw_db->query("SELECT cloud_id,username, SUM(acctsessiontime) AS session_time, SUM(acctinputoctets) AS quota_in, SUM(acctoutputoctets) AS quota_out, MIN(acctstarttime) AS date_activate FROM radacct WHERE cloud_id = '{$kiw_tenant}' GROUP BY username");

    echo "OK\n";


    $kiw_max = $kiw_connection->num_rows;

    $kiw_counter = 0;


    echo "There are [ {$kiw_max} ] records.\n";

    echo "Start dumping data to [ {$kiw_filename} ]..\n";


    $kiw_csv = fopen(dirname(__FILE__) . "/" . $kiw_filename, "w");


    while ($kiw_quota = $kiw_connection->fetch_assoc()) {


        fputcsv($kiw_csv, $kiw_quota);

        $kiw_counter++;


        echo "\rProcessing: " . round(($kiw_counter / $kiw_max) * 100, 0) . "%";


        if ($kiw_counter == $kiw_max){

            echo "\nCompleted.\n";

        } elseif (($kiw_counter % 1000) == 0) sleep(1);


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
        echo "Please provide full path to the file [example: /root/migrate_account_quota_{$kiw_tenant}.csv] or N to cancel: ";


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

    $kiw_csv = fopen($kiw_filename, "r");


    while (!feof($kiw_csv)){


        $kiw_quota = fgetcsv($kiw_csv, 1024);


        if (is_array($kiw_quota) && !empty($kiw_quota) && !empty($kiw_quota[0])) {


            if ($kiw_tenant !== $kiw_quota[0] && $kiw_ignore_mismatched == false){

                echo "\n\n";
                echo str_repeat("*", 30) . " ERROR! " . str_repeat("*", 30);
                echo "\n\n";

                echo "Tenant ID mismatched between you have specified [ {$kiw_tenant} ] and data in the file [ {$kiw_quota[0]} ].\n";
                echo "Proceed will cause the password updated for tenant [ {$kiw_tenant} ] instead of tenant [ {$kiw_quota[0]} ]\n";
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


            // echo "UPDATE kiwire_account_auth SET session_time = '{$kiw_quota[2]}', quota_in = '{$kiw_quota[3]}', quota_out = '{$kiw_quota[4]}',  date_activate = '{$kiw_quota[5]}' WHERE username = '{$kiw_quota[0]}' AND tenant_id = '{$kiw_tenant}' LIMIT 1\n";

            // $kiw_db->query("UPDATE kiwire_account_auth SET session_time = '{$kiw_quota[2]}', quota_in = '{$kiw_quota[3]}', quota_out = '{$kiw_quota[4]}',  date_activate = '{$kiw_quota[5]}' WHERE username = '{$kiw_quota[0]}' AND tenant_id = '{$kiw_tenant}' LIMIT 1");

            if ($kiw_db->affected_rows > 0){

                $kiw_row_number++;

            }


            $kiw_counter++;

            echo "\rProcessing: Line number {$kiw_counter}";

            if (($kiw_counter % 1) == 0) sleep(1);



        }


    }


    echo "\nClose the CSV file.. ";

    fclose($kiw_csv);

    echo "OK\n";


    echo "Close database connection.. ";

    $kiw_db->close();

    echo "OK\n";


    echo "Database has been updated with [ {$kiw_row_number} ] out of [ {$kiw_counter} ] records..\n";


    echo "\n";


}


// close the cli input

fclose($kiw_input);

