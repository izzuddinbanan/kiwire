<?


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once dirname(__FILE__, 3) . "/kiwire/server/admin/includes/include_config.php";
require_once dirname(__FILE__, 3) . "/kiwire/server/admin/includes/include_connection.php";


// define('DB_V3_HOST', "10.100.0.207");
// define('DB_V3_USER', "user-proxy");
// define('DB_V3_PASS', "synchro*123");
// define('DB_V3_NAME', "kiwire");
// define('DB_V3_PORT', "3306");


define('SYNC_DB_V3_HOST', '127.0.0.1');
define('SYNC_DB_V3_PORT', '3306');
define('SYNC_DB_V3_USER', 'root');
define('SYNC_DB_V3_PASSWORD', '');
define('SYNC_DB_V3_DATABASE', 'kiwire');


ini_set("memory_limit", '30G');
ini_set("max_execution_time", 0);

global $kiw_db;

$kiw_total_data = $kiw_db->query_first("SELECT count(*) as total_user FROM kiwire_account_auth WHERE ktype = 'voucher'");


if (empty($kiw_total_data)) die("Error on count user data.\n");

$kiw_total_data = $kiw_total_data["total_user"];


$file = 'compare_calculation.txt';
$date = date("d/m : H:i :");
file_put_contents($file, "$date : Started \n", FILE_APPEND);

echo "Total row in kiwire_user v3 [ " . $kiw_total_data . " ] .. \n";
file_put_contents($file, "$date : Total row in kiwire_user v3 [ " . $kiw_total_data . " ] .. \n", FILE_APPEND);



$kiw_conf["limit"]  = 300000;
$kiw_conf["offset"] = 0;


$kiw_conf["block"] = $kiw_total_data / $kiw_conf["limit"];
$kiw_conf["block"] = round($kiw_conf["block"]) + 1;


echo "Split data into  [ " . $kiw_conf["block"] . " ] block \n";
file_put_contents($file, "$date : Split data into  [ " . $kiw_conf["block"] . " ] block  \n", FILE_APPEND);

file_put_contents($file, " =============== START ============  \n", FILE_APPEND);




$kiw_tables = $kiw_db->fetch_array("SELECT table_name FROM information_schema.tables WHERE table_schema = 'kiwire' AND table_name like  'kiwire_sessions%'");


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

        $kiw_users = $kiw_v3->query("SELECT 
                                username, tenant_id
                                FROM kiwire_account_auth
                                WHERE ktype = 'voucher'
                                LIMIT {$kiw_conf["limit"]} OFFSET {$kiw_conf['offset']}");

        $kiw_total_row  = count($kiw_users);



        $kiw_curr_row   = 0;


        foreach ($kiw_users as $kiw_user) {


            //     //go(function () use($kiw_conf, $transaction_time, $kiw_v3, &$kiw_curr_row, $kiw_user, $i, $kiw_total_row, $kiw_tables, $kiw_v2){   


            $kiw_curr_row++;
            echo "Processing Block [ {$i} / {$kiw_conf['block']} ] - Row {$kiw_curr_row} / {$kiw_total_row} ..\n";


            if ($kiw_curr_row == $kiw_total_row) {


                file_put_contents($file, "$date : End Block  [ {$i} / {$kiw_conf['block']} ] - Total row [ {$kiw_curr_row} ] . Completed .. \n", FILE_APPEND);

                echo "===========\n";
                echo "Completed Block  [ {$i} / {$kiw_conf['block']} ] \n";
                echo "===========\n";
            }


            $account_users["username"] = $kiw_user['username'];
            $account_users["quota_in"] = 0;
            $account_users["quota_out"] = 0;
            $account_users["session_time"] = 0;


            foreach ($kiw_tables as $kiw_table) {


                $kiw_connection = $kiw_v3->query("SELECT username, SUM(session_time) AS session_time, SUM(quota_in) AS quota_in, SUM(quota_out) AS quota_out FROM {$kiw_table["table_name"]} WHERE tenant_id = '{$kiw_user['tenant_id']}' AND username = '{$kiw_user['username']}'");


                if ($kiw_connection[0]["username"] != NULL || $kiw_connection[0]["username"] != "") {

                    // $query = "SELECT session_time, quota_in, quota_out FROM kiwire_account_auth WHERE username='{$kiw_user['username']}'";

                    $account_users["session_time"]  += (($kiw_connection[0]["session_time"] != "" || $kiw_connection[0]["session_time"] != NULL) ? $kiw_connection[0]["session_time"] : 0);
                    $account_users["quota_in"]      += (($kiw_connection[0]["quota_in"] != "" || $kiw_connection[0]["quota_in"] != NULL)     ? $kiw_connection[0]["quota_in"] : 0);
                    $account_users["quota_out"]     += (($kiw_connection[0]["quota_out"] != "" || $kiw_connection[0]["quota_out"] != NULL)     ? $kiw_connection[0]["quota_out"] : 0);
                }

     
            }

            // ##############
            // LOGIC COMPARE DATA
            // ##############

            $kiw_select_data = $kiw_v3->query("SELECT username, tenant_id, session_time, quota_in, quota_out FROM kiwire_account_auth WHERE tenant_id = '{$kiw_user['tenant_id']}' AND  username = '{$kiw_user['username']}'");

            foreach ($kiw_select_data as $kiw_data) {

                if ($kiw_data['quota_in'] == NULL || $kiw_data['quota_in'] == "") {

                    $kiw_data['quota_in'] = 0;

                } else if ($kiw_data['quota_out'] == NULL || $kiw_data['quota_out'] == "") {

                    $kiw_data['quota_out'] = 0;
                }

                // if (($kiw_data['quota_in'] ==  $account_users["quota_in"])  || ($kiw_data['quota_out'] == $account_users["quota_out"])) {
                if ($account_users["quota_in"] != $kiw_data['quota_in']  || $account_users["quota_out"] != $kiw_data['quota_out']) {

                    file_put_contents($file, "$date : Voucher issue v3 : [ " . $kiw_data['username'] . " ] | SESSION: in -  [ " . $account_users["quota_in"] . " ] out - [ " . $account_users["quota_out"] . " ] | AUTH: in - [ " . $kiw_data['quota_in'] . " ] out - [ " . $kiw_data['quota_out'] . " ].. \n", FILE_APPEND);
                }

            }


            if (!$kiw_select_data) {

                echo "ERROR SELECT kiwire_account_auth ..\n";
            }


            unset($kiw_temp);
            unset($kiw_temp_result);

            // });

        }

    });


    $kiw_conf['offset'] = $kiw_conf['offset'] + $kiw_conf["limit"];
}

echo count(Swoole\Coroutine::listCoroutines()), " active coroutines when reaching the end of the PHP script.\n";
