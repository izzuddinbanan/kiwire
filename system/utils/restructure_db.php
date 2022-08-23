<?

require_once dirname(__FILE__, 3) . "/server/admin/includes/include_connection.php";

require_once dirname(__FILE__, 3) . "/server/libs/class.sql.helper.php";


ini_set("memory_limit", '30G');
ini_set("max_execution_time", 0);


global $kiw_db;

echo "Load database 'kiwire' ....\n";

$kiw_temp['count_table'] = $kiw_db->query_first("SELECT COUNT(*) AS total_table FROM information_schema.tables WHERE table_schema = 'kiwire'");

$kiw_tables = $kiw_db->fetch_array("SELECT table_name FROM information_schema.tables WHERE table_schema = 'kiwire'");

foreach ($kiw_tables as $key => $kiw_table) {


    $kiw_column = $kiw_db->fetch_array("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'kiwire' AND TABLE_NAME = '{$kiw_table["table_name"]}'  AND COLUMN_NAME NOT IN ('id', 'session_id')");

    if(empty($kiw_column)) continue;
    // if($kiw_table["table_name"] != "kiwire_topup_code") continue;

    $col_names      = array_column($kiw_column, 'COLUMN_NAME');
    $col_default    = array_column($kiw_column, 'COLUMN_DEFAULT');
    $col_types      = array_column($kiw_column, 'COLUMN_TYPE');


    // **** YANG BETUL ****////
    // ALTER TABLE `kiwire_account_auth`CHANGE `creator` `creator` char(64) COLLATE 'utf8mb4_general_ci' NOT NULL, CHANGE `username` `username` char(120) COLLATE 'utf8mb4_general_ci' NOT NULL

    echo "Alter table  {$kiw_table["table_name"]} :: ";

    $kiw_modify_query = "ALTER TABLE {$kiw_table["table_name"]} ";

    $i = 1;
    foreach ($col_names as $key => $kiw_col) {

        $default = ($col_default[$key] == NULL || $col_default[$key] == "'NULL'") ? "NULL DEFAULT NULL " : "NULL DEFAULT ". $col_default[$key] . ""; 

        // var_dump($col_default[$key]);

        if($col_names[$key] == "updated_date") {
            $default = "NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
            $col_types[$key] = " timestamp ";
        }

        if($col_names[$key] == "tenant_id") {
            $default = "NOT NULL";
        }
            



        $continue = $i != sizeof($col_names) ? ", " : " ";

        $kiw_modify_query .= " CHANGE `{$kiw_col}` `{$kiw_col}` ". $col_types[$key] . " {$default} {$continue}";

        $i++;
    }


    $conn = new mysqli(SYNC_DB1_HOST, SYNC_DB1_USER, SYNC_DB1_PASSWORD, SYNC_DB1_DATABASE, SYNC_DB1_PORT);
    
    $kiw_alter = $conn->query($kiw_modify_query);

    if(!$kiw_alter) echo "FAIL \n";
    else echo "SUCCESS \n";



}

echo "\nComplete restructure database \n";
die();
