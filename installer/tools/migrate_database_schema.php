<?php


$kiw_database_name = "kiwire";

$kiw_action = "";




// check for all variables

$kiw_input = fopen("php://stdin", "r");

echo "Updating database schema will take some times to complete depending on the amount of data involve.\n";
echo "Are you sure to update the database schema for this server? (Y or N): ";


if (strtolower(trim(fread($kiw_input, 10))) !== "y"){

    echo "Terminated\n";
    die();

}



while (!in_array($kiw_action, array("dump", "update"))){


    echo "Please confirm your action [ dump / update / cancel ] : ";

    $kiw_action = strtolower(trim(fread($kiw_input, 10)));

    if ($kiw_action == "cancel") die("\n");


}


echo "Check database connection.. ";

$kiw_db = new mysqli("127.0.0.1", "root", "", $kiw_database_name);

if ($kiw_db->connect_errno) die("ERROR: Unable to connect to database.\n");

echo "OK\n";


if ($kiw_action == "update") {


    echo "Check database schema.. ";

    $kiw_columns = file_get_contents("migrate_schema.json");

    $kiw_columns = json_decode($kiw_columns, true);


    if (is_array($kiw_columns)) {


        echo "OK\n";


        $kiw_total_column = count($kiw_columns);

        $kiw_current_column = 1;


        foreach ($kiw_columns as $kiw_column) {


            echo "[ {$kiw_current_column} / {$kiw_total_column} ] Processing table: [ {$kiw_column['table_name']} ] column: [ {$kiw_column['column_name']} ]\n";


            // check if table available.

            $kiw_result = $kiw_db->query("SELECT COUNT(*) AS kcount FROM information_schema.TABLES WHERE TABLE_SCHEMA = '{$kiw_database_name}' AND TABLE_NAME = '{$kiw_column['table_name']}' LIMIT 1");

            if ($kiw_result) $kiw_result = $kiw_result->fetch_all(MYSQLI_ASSOC)[0];


            try {


                if ($kiw_result['kcount'] > 0) {


                    $kiw_test = explode("(", $kiw_column['column_type']);


                    if (in_array($kiw_test[0], array("int", "bigint", "tinyint"))) {

                        $kiw_column['column_type'] = $kiw_test[0];

                    } elseif (in_array($kiw_test[0], array("varchar"))) {

                        $kiw_column['column_type'] = str_replace('varchar', 'char', $kiw_column['column_type']);

                    }


                    unset($kiw_test);


                    if ($kiw_column['column_default'] == null) $kiw_column['column_default'] = "NULL";


                    // only add column

                    $kiw_db->query("ALTER TABLE {$kiw_column['table_name']} ADD COLUMN IF NOT EXISTS {$kiw_column['column_name']} {$kiw_column['column_type']} DEFAULT {$kiw_column['column_default']}");


                } else {


                    // create table first with the first column

                    $kiw_db->query("CREATE TABLE {$kiw_column['table_name']} ({$kiw_column['column_name']} {$kiw_column['column_type']} DEFAULT {$kiw_column['column_default']})");


                }


                if ($kiw_column['column_key'] == "PRI") {


                    $kiw_db->query("ALTER TABLE {$kiw_column['table_name']} MODIFY {$kiw_column['column_name']} {$kiw_column['column_type']} AUTO_INCREMENT PRIMARY KEY");


                } elseif ($kiw_column['column_key'] == "MUL") {


                    $kiw_db->query("CREATE INDEX {$kiw_column['column_name']} ON {$kiw_column['table_name']}({$kiw_column['column_name']})");


                }


            } catch (Exception $kiw_exception) {


                echo "ERROR: " . $kiw_exception->getMessage() . "\n";

                die("Terminated");


            }


            unset($kiw_result);

            $kiw_current_column++;


        }

        echo "[ DONE ] all table has been updated.\n";


    } else {

        echo "\nERROR: Not found or invalid JSON file\n";

        die("Terminated\n\n");

    }


} elseif ($kiw_action == "dump"){


    echo "Get database schema.. ";


    $kiw_result = $kiw_db->query("SELECT table_name,column_name,column_default,column_type,column_key FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$kiw_database_name}' AND TABLE_NAME NOT LIKE '%kiwire_sessions_%' ORDER BY column_key DESC");

    if ($kiw_result) $kiw_result = $kiw_result->fetch_all(MYSQLI_ASSOC);

    echo "OK\n";


    file_put_contents("migrate_schema.json", json_encode($kiw_result, JSON_PRETTY_PRINT));

    echo "[ DONE ] migrate_schema.json has been updated.\n";


}