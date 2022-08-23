<?


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once dirname(__FILE__, 3) . "/kiwire/server/admin/includes/include_config.php";
require_once dirname(__FILE__, 3) . "/kiwire/server/admin/includes/include_connection.php";

require_once dirname(__FILE__, 3) . "/libs/class.sql.helper.php";


define('SYNC_DB_V3_HOST', '127.0.0.1');
define('SYNC_DB_V3_PORT', '3306');
define('SYNC_DB_V3_USER', 'root');
define('SYNC_DB_V3_PASSWORD', '');
define('SYNC_DB_V3_DATABASE', 'kiwire');


ini_set("memory_limit", '30G');
ini_set("max_execution_time", 0);


global $kiw_db;

$kiw_total_table = $kiw_db->query_first("SELECT COUNT(*) AS total_table FROM information_schema.tables WHERE table_schema = 'kiwire';");

if (empty($kiw_total_table)) die("Error on count user data.\n");

$kiw_total_table = $kiw_total_table["total_table"];


$file = 'modify_column.txt';
$date = date("d/m : H:i :");
file_put_contents($file, "$date : Started \n", FILE_APPEND);

echo "Total table in database kiwire v3 [ " . $kiw_total_table . " ] .. \n";
file_put_contents($file, "$date : Total table in database kiwire v3 [ " . $kiw_total_table . " ] .. \n", FILE_APPEND);


$kiw_conf["limit"]  = 40;
$kiw_conf["offset"] = 0;

$kiw_conf["block"] = $kiw_total_table / $kiw_conf["limit"];
$kiw_conf["block"] = round($kiw_conf["block"]) + 1;


echo "Split data into  [ " . $kiw_conf["block"] . " ] block \n";
file_put_contents($file, "$date : Split data into  [ " . $kiw_conf["block"] . " ] block  \n", FILE_APPEND);

file_put_contents($file, " =============== START ============  \n", FILE_APPEND);


$kiw_tables = $kiw_db->fetch_array("SELECT table_name FROM information_schema.tables WHERE table_schema = 'kiwire'");


Swoole\Runtime::enableCoroutine();


for ($i = 1; $i <= $kiw_conf["block"]; $i++) {


    go(function () use ($kiw_conf, $i, $kiw_tables, $date, $file) {

        $kiw_v3 = new Swoole\Coroutine\MySQL();
        $kiw_v3->connect([
            'host' => SYNC_DB_V3_HOST,
            'user' => SYNC_DB_V3_USER,
            'password' => SYNC_DB_V3_PASSWORD,
            'database' => SYNC_DB_V3_DATABASE,
            'port' => SYNC_DB_V3_PORT
        ]);

        echo "Start block  [ {$i} / {$kiw_conf['block']} ] \n";


        $kiw_tables = $kiw_v3->query("SELECT table_name FROM information_schema.tables 
                                      WHERE table_schema = 'kiwire'LIMIT {$kiw_conf["limit"]} OFFSET {$kiw_conf['offset']}");

        $kiw_total_tbl  = count($kiw_tables);

        $kiw_curr_tbl   = 0;


        foreach ($kiw_tables as $kiw_table) {

            
            $kiw_curr_tbl++;
            echo "Processing Block [ {$i} / {$kiw_conf['block']} ] - Table {$kiw_curr_tbl} / {$kiw_total_tbl} ..\n";


            if ($kiw_curr_tbl == $kiw_total_tbl) {

                file_put_contents($file, "$date : End Block  [ {$i} / {$kiw_conf['block']} ] - Total table [ {$kiw_curr_tbl} ] . Completed .. \n", FILE_APPEND);

                echo "===========\n";
                echo "Completed Block  [ {$i} / {$kiw_conf['block']} ] \n";
                echo "===========\n";

            }


            // Find column which datatype NOT NULL in each table

            // $kiw_column = $kiw_v3->query("SELECT COLUMN_NAME 
            //                                FROM INFORMATION_SCHEMA.COLUMNS 
            //                                WHERE TABLE_SCHEMA = 'kiwire'
            //                                AND TABLE_NAME = {$kiw_table["table_name"]} 
            //                                AND `IS_NULLABLE` = 'NO' AND COLUMN_NAME NOT IN ('id', 'updated_date', 'tenant_id', 'creator', 'login', 'session_id')");

            $kiw_column = $kiw_v3->fetch_array("SELECT *
                                                FROM INFORMATION_SCHEMA.COLUMNS 
                                                WHERE TABLE_SCHEMA = 'kiwire'
                                                AND TABLE_NAME = 'kiwire_account_auth' 
                                                AND `IS_NULLABLE` = 'NO' 
                                                AND COLUMN_NAME 
                                                NOT IN ('id', 'updated_date', 'tenant_id', 'creator', 'login', 'session_id');");


            $column_names = array_column($kiw_column, 'COLUMN_NAME');

            // $kiw_list_column = "('" . implode("', '", $column_names) . "')";
            // $kiw_modify_query = "CHANGE '{$kiw_column['COLUMN_NAME']}' '{$kiw_column['COLUMN_NAME']}' NULL,";


            // Alter all table columns in db to null except column data generated from system
            
            foreach ($column_names as $kiw_col) {

                $kiw_modify_query = "CHANGE '{$kiw_col}' '{$kiw_col}' NULL,";
    
                $kiw_query[] = $kiw_modify_query;
    
            }


            $kiw_alter = $kiw_v3->query("ALTER TABLE {$kiw_table["table_name"]} MODIFY {$kiw_query}");


            if(!$kiw_alter) {

                echo "ERROR ALTER TABLE .... LIMIT 1 ..\n";

            }
        


        }


    });

    $kiw_conf['offset'] = $kiw_conf['offset'] + $kiw_conf["limit"];

}

echo count(Swoole\Coroutine::listCoroutines()), " active coroutines when reaching the end of the PHP script.\n";

