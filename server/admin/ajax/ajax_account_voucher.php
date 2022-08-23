<?php

$kiw['module'] = "Account -> Voucher -> List";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_general.php";
require_once "../includes/include_connection.php";
require_once "../includes/include_report.php";


require_once "../../libs/ssp.class.php";

require_once "../../user/includes/include_account.php";


if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));
}


$action = $_REQUEST['action'];


// check the tenant id to be used

if ($_SESSION['access_level'] == "superuser") {

    if (!empty($_SESSION['tenant_allowed'])) {

        $kiw_tenants = explode(",", $_SESSION['tenant_allowed']);

        if (in_array($_REQUEST['tenant_id'], $kiw_tenants)) {

            $kiw_tenant_id = $kiw_db->escape($_REQUEST['tenant_id']);
        } else $kiw_tenant_id = $_SESSION['tenant_id'];
    } else $kiw_tenant_id = $kiw_db->escape($_REQUEST['tenant_id']);
} else $kiw_tenant_id = $_SESSION['tenant_id'];



switch ($action) {

    case "history":
        get_history($kiw_db, $kiw_tenant_id);
        break;
    case "statistics":
        statistics($kiw_db);
        break;
    case "reset":
        voucher_reset();
        break;
    case "reset-mac":
        voucher_reset_mac();
        break;
    case "create":
        create();
        break;
    case "get_all":
        get_all();
        break;
    case "delete":
        delete();
        break;
    case "line_chart":
        line_chart();
        break;
    case "chart_history":
        chart_history();
        break;
    case "get_csv":
        get_csv($kiw_db);
        break;

    default:
        echo "ERROR: Wrong implementation";
}

function get_csv($kiw_db)
{

    set_time_limit(0); 

    $kiw_path = dirname(__FILE__, 3);


    $kiw_timezone = $_SESSION['timezone'];

    $kiw_timezone = empty($kiw_timezone) ?: "Asia/Kuala_Lumpur";


    $data = array(
        'username',
        'status',
        'profile_subs',
        'price',
        'date_create',
        'date_expiry',
        'remark',
        'tenant_id'
    );

    $kiw_where = "ktype = 'voucher'";


    if (!empty($_REQUEST['username'])) {

        $kiw_where .= " AND username LIKE '" . $kiw_db->escape($_REQUEST['username']) . "%'";
    }


    if (!empty($_REQUEST['status'])) {

        $kiw_where .= " AND status = '" . $kiw_db->escape($_REQUEST['status']) . "'";
    }


    if (!empty($_REQUEST['profile'])) {

        $kiw_where .= " AND profile_subs LIKE '" . $kiw_db->escape($_REQUEST['profile']) . "%'";
    }


    if (!empty($_REQUEST['created_date'])) {


        $kiw_temp = date("Y-m-d H:i:s", strtotime($_REQUEST['created_date']));

        $kiw_temp = sync_toutctime($kiw_temp);


        $kiw_where .= " AND date_create >= '{$kiw_temp}'";
    }


    if (!empty($_REQUEST['expiry_from'])) {


        $kiw_temp = date("Y-m-d H:i:s", strtotime($_REQUEST['expiry_from']));

        $kiw_temp = sync_toutctime($kiw_temp);


        $kiw_where .= " AND date_expiry >= '{$kiw_temp}'";
    }

    if (!empty($_REQUEST['remark'])) {

        $kiw_where .= " AND remark LIKE '" . $kiw_db->escape($_REQUEST['remark']) . "%'";
    }


    // pending tenant check

    $tenant_id = !empty($_REQUEST['tenant_id']) ? $_REQUEST['tenant_id'] : $_SESSION['tenant_id'];

    if ($_SESSION['access_level'] == "superuser") {


        if (!empty($_REQUEST['tenant_id'])) {


            if (!empty($_SESSION['tenant_allowed'])) {


                $kiw_temp = explode(",", $_SESSION['tenant_allowed']);


                if (in_array($_REQUEST['tenant_id'], $kiw_temp) == true) {


                    $kiw_where .= " AND tenant_id = '" . $kiw_db->escape($_REQUEST['tenant_id']) . "'";
                } else {


                    $kiw_where .= " AND tenant_id IN ('" . implode("','", $kiw_temp) . "')";
                }
            } else {

                $kiw_where .= " AND tenant_id = '" . $kiw_db->escape($_REQUEST['tenant_id']) . "'";
            }
        } else {


            if (!empty($_SESSION['tenant_allowed'])) {


                $kiw_where .= " AND tenant_id IN ('" . implode("','", explode(",", $_SESSION['tenant_allowed'])) . "')";
            }
        }
    } else  $kiw_where .= " AND tenant_id = '{$_SESSION['tenant_id']}'";


    //file name to download
    $kiw_filename   = "account_voucher_{$_SESSION['tenant_id']}_" . date("Ymd") ."_". time(). "_{$_SESSION['user_name']}";

    if (file_exists( $kiw_path . "/custom/{$tenant_id}/account_voucher_export") == false) {

        mkdir( $kiw_path . "/custom/{$tenant_id}/account_voucher_export", 0755,true);

    }

    //open file to write
    $fp = fopen("{$kiw_path}/custom/{$tenant_id}/account_voucher_export/{$kiw_filename}.csv", 'w');
    // echo json_encode(['url'=>"{$kiw_path}/custom/{$tenant_id}/account_voucher_export/{$kiw_filename}.csv"]);

    fputcsv($fp, $data);

    
    //loop all table involved
    $offset = 0;
    $limit = 5000;

    //get total row in a table
    $kcount = $kiw_db->query("SELECT count(id) as total FROM kiwire_account_auth  WHERE {$kiw_where} LIMIT 1")[0];


    //create partition 
    $part = $kcount['total'] / $limit;
    $part = round($part) + 1;

    unset($kcount);


    //loop by partition created
    for ($i=1; $i <= $part; $i++) {

        $query_data = $kiw_db->fetch_array("SELECT " . implode(",", $data) . " FROM kiwire_account_auth WHERE {$kiw_where} LIMIT {$limit} OFFSET {$offset} ");
        
        foreach ($query_data as $info) {

            //write data inside csv
            fputcsv($fp, $info);
    
        }

        //unset to avoid memory limit
        unset($query_data);

        $offset = $offset + $limit;

    } 


    //close file after finish 
    fclose($fp);    
    
    unset($data);
    unset($fp);

    echo json_encode(array("status" => "completed", "link"=>"/custom/{$tenant_id}/account_voucher_export/{$kiw_filename}.csv"));
}


function get_all()
{

    global $kiw_db;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_timezone = $_SESSION['timezone'];

        $kiw_timezone = empty($kiw_timezone) ?: "Asia/Kuala_Lumpur";


        $kiw_columns = array(
            array('db' => 'username',     'dt' => 1),
            array('db' => 'status',       'dt' => 2),
            array('db' => 'profile_subs', 'dt' => 3),
            array('db' => 'price',        'dt' => 4),
            array('db' => 'date_create',  'dt' => 5),
            array('db' => 'date_expiry',  'dt' => 6),
            array('db' => 'remark',       'dt' => 7),
            array('db' => 'tenant_id',    'dt' => 8)
        );


        $kiw_sqlinfo = array('user' => SYNC_DB1_USER, 'pass' => SYNC_DB1_PASSWORD, 'db' => SYNC_DB1_DATABASE, 'host' => SYNC_DB1_HOST, 'port' => SYNC_DB1_PORT);


        $kiw_where = "ktype = 'voucher'";


        if (!empty($_REQUEST['username'])) {

            $kiw_where .= " AND username LIKE '" . $kiw_db->escape($_REQUEST['username']) . "%'";
        }


        if (!empty($_REQUEST['status'])) {

            $kiw_where .= " AND status = '" . $kiw_db->escape($_REQUEST['status']) . "'";
        }


        if (!empty($_REQUEST['profile'])) {

            $kiw_where .= " AND profile_subs LIKE '" . $kiw_db->escape($_REQUEST['profile']) . "%'";
        }


        if (!empty($_REQUEST['created_date'])) {


            $kiw_temp = date("Y-m-d H:i:s", strtotime($_REQUEST['created_date']));

            $kiw_temp = sync_toutctime($kiw_temp);


            $kiw_where .= " AND date_create >= '{$kiw_temp}'";
        }


        if (!empty($_REQUEST['expiry_from'])) {


            $kiw_temp = date("Y-m-d H:i:s", strtotime($_REQUEST['expiry_from']));

            $kiw_temp = sync_toutctime($kiw_temp);


            $kiw_where .= " AND date_expiry >= '{$kiw_temp}'";
        }

        if (!empty($_REQUEST['remark'])) {

            $kiw_where .= " AND remark LIKE '" . $kiw_db->escape($_REQUEST['remark']) . "%'";
        }


        // pending tenant check

        if ($_SESSION['access_level'] == "superuser") {


            if (!empty($_REQUEST['tenant_id'])) {


                if (!empty($_SESSION['tenant_allowed'])) {


                    $kiw_temp = explode(",", $_SESSION['tenant_allowed']);


                    if (in_array($_REQUEST['tenant_id'], $kiw_temp) == true) {


                        $kiw_where .= " AND tenant_id = '" . $kiw_db->escape($_REQUEST['tenant_id']) . "'";
                    } else {


                        $kiw_where .= " AND tenant_id IN ('" . implode("','", $kiw_temp) . "')";
                    }
                } else {

                    $kiw_where .= " AND tenant_id = '" . $kiw_db->escape($_REQUEST['tenant_id']) . "'";
                }
            } else {


                if (!empty($_SESSION['tenant_allowed'])) {


                    $kiw_where .= " AND tenant_id IN ('" . implode("','", explode(",", $_SESSION['tenant_allowed'])) . "')";
                }
            }
        } else  $kiw_where .= " AND tenant_id = '{$_SESSION['tenant_id']}'";


        $kiw_data = SSP::complex($_GET, $kiw_sqlinfo, "kiwire_account_auth", "id", $kiw_columns, null, $kiw_where);


        $kiw_start = $_GET['start'] + 1;

        $kiw_end = count($kiw_data['data']) + $kiw_start;


        for ($x = $kiw_start; $x < $kiw_end; $x++) {


            $kiw_data['data'][$x - $kiw_start][0] = $x;

            $kiw_data['data'][$x - $kiw_start][5] = sync_tolocaltime($kiw_data['data'][$x - $kiw_start][5], $kiw_timezone);

            $kiw_data['data'][$x - $kiw_start][6] = sync_tolocaltime($kiw_data['data'][$x - $kiw_start][6], $kiw_timezone);

            // $kiw_data['data'][$x - $kiw_start][5] = report_date_format($kiw_data['data'][$x - $kiw_start][5]);

            // $kiw_data['data'][$x - $kiw_start][6] = report_date_format($kiw_data['data'][$x - $kiw_start][6]);


        }


        echo json_encode($kiw_data);
    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
    }
}



function create()
{

    global $kiw_db, $kiw_tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));


        $kiw_balance = $kiw_db->query_first("SELECT * FROM kiwire_admin WHERE tenant_id = '{$_SESSION['tenant_id']}' AND username = '{$_SESSION['user_name']}' LIMIT 1");

        $kiw_profile = $kiw_db->query_first("SELECT * FROM kiwire_profiles WHERE tenant_id = '{$_SESSION['tenant_id']}' AND name = '{$kiw_db->escape($_REQUEST['plan'])}' LIMIT 1");


        $kiw_cost = $kiw_profile['price'] * (int)$_REQUEST['qty'];


        // if ($kiw_profile['price'] > 0 && $kiw_balance['balance_credit'] >= $kiw_cost || (int)$kiw_profile['price'] == 0) {


            $kiw_data['action']        = "create_voucher";
            $kiw_data['tenant_id']     = $_SESSION['tenant_id'];
            $kiw_data['creator']       = $_SESSION['user_name'];
            $kiw_data['quantity']      = (int)$_REQUEST['qty'];
            $kiw_data['price']         = floatval($kiw_profile['price']);
            $kiw_data['prefix']        = $kiw_db->sanitize($_REQUEST['prefix']);
            $kiw_data['remark']        = $kiw_db->escape($_REQUEST['remark']);
            $kiw_data['profile']       = $kiw_db->escape($_REQUEST['plan']);
            $kiw_data['allowed_zone']  = $kiw_db->escape($_REQUEST['zone']);
            $kiw_data['expiry_date']   = date("Y-m-d H:i:s", strtotime($_REQUEST['date_expiry']));


            $kiw_temp = curl_init();


            curl_setopt($kiw_temp, CURLOPT_URL, "http://127.0.0.1:9956");
            curl_setopt($kiw_temp, CURLOPT_POST, true);
            curl_setopt($kiw_temp, CURLOPT_POSTFIELDS, http_build_query($kiw_data));
            curl_setopt($kiw_temp, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($kiw_temp, CURLOPT_TIMEOUT, 15);
            curl_setopt($kiw_temp, CURLOPT_CONNECTTIMEOUT, 5);

            unset($kiw_data);

            $kiw_creation = curl_exec($kiw_temp);

            curl_close($kiw_temp);


            // decode the response from agent

            $kiw_creation = json_decode($kiw_creation, true);


            if ($kiw_creation['status'] == "success") {


                $kiw_db->query("UPDATE kiwire_admin SET updated_date = NOW(),  balance_credit = (balance_credit - {$kiw_cost}) WHERE tenant_id = '{$_SESSION['tenant_id']}' AND username = '{$_SESSION['user_name']}' LIMIT 1");


                sync_logger("{$_SESSION['user_name']} create voucher", $_SESSION['tenant_id']);

                echo json_encode(array("status" => "success", "message" => "SUCCESS: Voucher has been created", "data" => null));
            } else {

                echo json_encode(array("status" => "failed", "message" => "ERROR: There is an error generate the voucher", "data" => null));
            }
        // } else {

        //     echo json_encode(array("status" => "failed", "message" => "ERROR: Insufficient credit", "data" => null));
        // }
    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
    }
}


function delete()
{

    global $kiw_db, $kiw_tenant_id;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $kiw_account = $kiw_db->escape($_REQUEST['username']);

        if (strlen($kiw_account) > 0) {

            $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(),  status = 'suspend' WHERE username ='{$kiw_account}' AND tenant_id = '{$kiw_tenant_id}' LIMIT 1");

            sync_logger("{$_SESSION['user_name']} deleted voucher {$kiw_account}", $kiw_tenant_id);

            echo json_encode(array("status" => "success", "message" => "SUCCESS: Voucher [{$kiw_account}] has been deleted", "data" => "UPDATE kiwire_account_auth SET updated_date = NOW(),  status = 'suspend' WHERE username ='{$kiw_account}' AND tenant_id = '{$kiw_tenant_id}' LIMIT 1"));
        }
    
    
    } else {


        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
    }
}


function voucher_reset()
{

    global $kiw_db, $kiw_tenant_id;;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        $kiw_account = $kiw_db->escape($_REQUEST['username']);


        if (!empty($kiw_account)) {


            $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), status = 'active', session_time = 0, quota_in = 0, quota_out = 0, date_activate = NULL WHERE username ='{$kiw_account}' AND tenant_id = '{$kiw_tenant_id}' LIMIT 1");


            sync_logger("{$_SESSION['user_name']} reset voucher {$kiw_account}", $_SESSION['tenant_id']);

            echo json_encode(array("status" => "success", "message" => "SUCCESS: Voucher [{$kiw_account}] has been reset", "data" => null));
        }
    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
    }
}


function statistics($kiw_db)
{

    global $kiw_tenant_id;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        $kiw_account = $kiw_db->escape($_REQUEST['id']);

        if (strlen($kiw_account) > 0) {


            $kiw_result = array();


            $kiw_result['auth'] = $kiw_db->query_first("SELECT * FROM kiwire_account_auth WHERE username = '{$kiw_account}' AND tenant_id = '{$kiw_tenant_id}' LIMIT 1");
            $kiw_result['info'] = $kiw_db->query_first("SELECT * FROM kiwire_account_info WHERE username = '{$kiw_account}' AND tenant_id = '{$kiw_tenant_id}' LIMIT 1");

            $kiw_result['profile'] = $kiw_db->query_first("SELECT * FROM kiwire_profiles WHERE name = '{$kiw_result['auth']['profile_subs']}' AND tenant_id = '{$kiw_tenant_id}' LIMIT 1");


            if (empty($kiw_result['profile'])) {

                $kiw_result['auth']['profile_subs'] = "Missing Profile";
                $kiw_result['auth']['profile_curr'] = "Missing Profile";

                $kiw_result['profile']['type'] = "0";
                $kiw_result['profile']['price'] = 0;
                $kiw_result['profile']['attribute']['reply:Idle-Timeout'] = 0;
                $kiw_result['profile']['attribute']['control:Simultaneous-Use'] = 0;
                $kiw_result['profile']['attribute']['control:Kiwire-Total-Quota'] = 0;
                $kiw_result['profile']['attribute']['reply:WISPr-Bandwidth-Max-Down'] = 0;
                $kiw_result['profile']['attribute']['reply:WISPr-Bandwidth-Max-Up'] = 0;
            } else $kiw_result['profile']['attribute'] = json_decode($kiw_result['profile']['attribute'], true);


            if ($kiw_result['auth']['date_activate'] === '0000-00-00 00:00:00') $kiw_result['date_activate'] = $kiw_result['auth']['date_activate'];


            $kiw_result['auth']['date_create'] = sync_tolocaltime($kiw_result['auth']['date_create'], $_SESSION['timezone']);
            $kiw_result['auth']['date_value'] = sync_tolocaltime($kiw_result['auth']['date_value'], $_SESSION['timezone']);
            $kiw_result['auth']['date_expiry'] = sync_tolocaltime($kiw_result['auth']['date_expiry'], $_SESSION['timezone']);
            $kiw_result['auth']['date_last_login'] = sync_tolocaltime($kiw_result['auth']['date_last_login'], $_SESSION['timezone']);
            $kiw_result['auth']['date_activate'] = sync_tolocaltime($kiw_result['auth']['date_activate'], $_SESSION['timezone']);


            unset($kiw_result['auth']['password']);


            echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_result, "test" => "SELECT * FROM kiwire_account_auth WHERE username = '{$kiw_account}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1"));
        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Missing account details", "data" => null));
        }
    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
    }
}


function get_history($kiw_db, $kiw_tenant_id)
{


    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        $kiw_result = array();

        $kiw_account = $kiw_db->escape($_REQUEST['account']);


        $kiw_timezone = $_SESSION['timezone'];

        if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


        if (strlen($kiw_account) > 0) {


            foreach (range(0, 5) as $kiw_range) {


                $kiw_current_month = date("Ym", strtotime("-{$kiw_range} Month"));

                $kiw_result_db = $kiw_db->fetch_array("SELECT tenant_id, CONVERT_TZ(start_time, 'UTC', '{$kiw_timezone}') AS start_time, CONVERT_TZ(stop_time, 'UTC', '{$kiw_timezone}') AS stop_time, session_time, mac_address, class, brand, ip_address, ipv6_address, quota_in, quota_out, terminate_reason FROM kiwire_sessions_{$kiw_current_month} WHERE username = '{$kiw_account}' AND tenant_id = '{$kiw_tenant_id}' ORDER BY id DESC");


                if (is_array($kiw_result_db)) {

                    $kiw_result = array_merge($kiw_result, $kiw_result_db);
                }


                // make sure not too many data send in one session

                if (count($kiw_result) > 1000) break;
            }

            echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_result));
        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Missing account details", "data" => null));
        }
    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
    }
}

function voucher_reset_mac()
{


    global $kiw_db, $kiw_tenant_id;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        $kiw_account = $kiw_db->escape($_REQUEST['username']);

        if (!empty($kiw_account)) {


            if (in_array("Account -> Voucher -> Reset", $_SESSION['access_list'])) {


                $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(),  allowed_mac = NULL WHERE username ='{$kiw_account}' AND tenant_id = '{$kiw_tenant_id}' LIMIT 1");

                sync_logger("{$_SESSION['user_name']} reset mac list for voucher {$kiw_account}", $_SESSION['tenant_id']);

                echo json_encode(array("status" => "success", "message" => "SUCCESS: Voucher [{$kiw_account}] mac list has been reset", "data" => null));
            
            } else {

                die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to reset MAC address", "data" => null)));
            }
        
        } else {

            die(json_encode(array("status" => "failed", "message" => "ERROR: Invalid voucher code provided", "data" => null)));
        }
    
    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
    }

}


function line_chart()
{

    global $kiw_db;


    $kiw_username = $kiw_db->escape($_REQUEST['username']);

    $kiw_tenant = $kiw_db->escape($_REQUEST['tenant']);

    $kiw_session = $kiw_db->fetch_array("SELECT DATE(CONVERT_TZ(start_time, 'UTC', 'Asia/Kuala_Lumpur')) AS xstart_time, SUM(session_time) AS session_time, SUM(quota_in + quota_out) AS quota FROM kiwire_sessions_202008 WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant}'  GROUP BY xstart_time");


    if (!empty($kiw_session)) {

        echo json_encode(array("status" => "success", "data" => $kiw_session));
    
    
    } else echo json_encode(array("status" => "failed", "message" => "ERROR: Missing sessions data", "data" => null));

}


function chart_history()
{


    global $kiw_db;

    $kiw_username = $kiw_db->escape($_REQUEST['username']);

    $kiw_tenant = $kiw_db->escape($_REQUEST['tenant']);


    $kiw_user = $kiw_db->query_first("SELECT * FROM kiwire_account_auth WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant}' LIMIT 1");

    $kiw_profile = $kiw_db->query_first("SELECT * FROM kiwire_profiles WHERE name = '{$kiw_user['profile_subs']}' AND tenant_id = '{$kiw_user['tenant_id']}' LIMIT 1");


    $kiw_profile['attribute'] = json_decode($kiw_profile['attribute'], true);


    $kiw_total_byte = $kiw_profile['attribute']['control:Kiwire-Total-Quota'];


    try {


        if ($kiw_profile['type'] == "free") {


            $kiw_remaining_time = "Unlimited";

            $kiw_percentage_time = 0;
        } elseif ($kiw_profile['type'] == "countdown") {


            // $kiw_remaining_time = $kiw_user['session_time'] - $kiw_profile['attribute']['control:Max-All-Session'];
            $kiw_remaining_time = $kiw_profile['attribute']['control:Max-All-Session'] - $kiw_user['session_time'];


            $kiw_percentage_time = ($kiw_user['session_time'] / $kiw_profile['attribute']['control:Max-All-Session']) * 100;
        } elseif ($kiw_profile['type'] == "expiration") {


            if (empty($kiw_user['date_activate']) || $kiw_user['date_activate'] === '0000-00-00 00:00:00') {


                $kiw_remaining_time = $kiw_profile['attribute']['control:Access-Period'];

                $kiw_percentage_time = 0;
            } else {


                $kiw_remaining_time = $kiw_profile['attribute']['control:Access-Period'] - (time() - strtotime($kiw_user['date_activate']));

                $kiw_percentage_time = ((time() - strtotime($kiw_user['date_activate'])) / $kiw_profile['attribute']['control:Access-Period']) * 100;
            }
        }
    } catch (Exception $e) {


        die("ERROR: " . $e->getMessage());
    }


    try {


        $kiw_percentage_quota = (($kiw_user['quota_in'] + $kiw_user['quota_out']) / ($kiw_total_byte * 1024 * 1024)) * 100;

        $kiw_remaining_byte = round((($kiw_total_byte * 1024 * 1024) - ($kiw_user['quota_in'] + $kiw_user['quota_out'])) / (1024 * 1024), 3, PHP_ROUND_HALF_DOWN);

        if ($kiw_remaining_byte < 0) $kiw_remaining_byte = "Unlimited";
    } catch (Exception $e) {


        die("ERROR: " . $e->getMessage());
    }


    if (!empty($kiw_user) && !empty($kiw_profile)) {


        echo json_encode(
            [
                "status" => "success",
                "remaining_quota" => round(($kiw_remaining_byte > 0) ? $kiw_remaining_byte : 0, 2, PHP_ROUND_HALF_UP),
                "remaining_time" => round(($kiw_remaining_time > 0) ? ($kiw_remaining_time / 60) : 0, 1, PHP_ROUND_HALF_UP),
                "percentage_quota" => (int)$kiw_percentage_quota,
                "percentage_time" => (int)$kiw_percentage_time
            ]
        );
    } else echo json_encode(array("status" => "failed", "message" => "ERROR: Missing accounts data", "data" => null));
}
