<?php


header("Content-Type: application/json");

require_once "../includes/include_general.php";
require_once "../includes/include_session.php";

require_once "../../admin/includes/include_report.php";
require_once "../../user/includes/include_account.php";

require_once "../../libs/ssp.class.php";


global $kiw_db;


$action = $_REQUEST['action'];


switch ($action) {

    case "get_all":
        get_all();
        break;
    case "create":
        create();
        break;
    case "statistics":
        statistics($kiw_db);
        break;
    case "delete":
        delete();
        break;
    case "reset":
        voucher_reset();
        break;

    default:
        echo "ERROR: Wrong implementation";
}


function get_all()
{

    global $kiw_db;

    $kiw_tenant   = $_SESSION['cpanel']['tenant_id'];

    $kiw_timezone = $_SESSION['timezone'];

    if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


    $kiw_columns = array(
        array('db' => 'username',     'dt' => 1),
        array('db' => 'status',       'dt' => 2),
        array('db' => 'profile_subs', 'dt' => 3),
        array('db' => 'price',        'dt' => 4),
        array('db' => 'date_create',  'dt' => 5),
        array('db' => 'date_expiry',  'dt' => 6),
        array('db' => 'remark',       'dt' => 7),
    );


    $kiw_sqlinfo = array('user' => SYNC_DB1_USER, 'pass' => SYNC_DB1_PASSWORD, 'db' => SYNC_DB1_DATABASE, 'host' => SYNC_DB1_HOST, 'port' => SYNC_DB1_PORT);


    $kiw_where = "ktype = 'voucher' AND tenant_id = '{$kiw_tenant}'";


    // $kiw_test = $kiw_db->fetch_array("SELECT * FROM kiwire_account_auth WHERE ktype ='voucher' AND  tenant_id = '{$kiw_tenant}'");


    // echo json_encode(array("tet" => $kiw_test));



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
}


function create()
{

    global $kiw_db;

    $kiw_tenant   = $_SESSION['cpanel']['tenant_id'];

    $kiw_username = $_SESSION['cpanel']['username'];


    $kiw_timezone = $_SESSION['timezone'];

    if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


    // csrf($kiw_db->escape($_REQUEST['token']));


    // $kiw_balance = $kiw_db->query_first("SELECT * FROM kiwire_admin WHERE tenant_id = '{$kiw_tenant}' AND username = '{$_SESSION['user_name']}' LIMIT 1");

    $kiw_profile = $kiw_db->query_first("SELECT * FROM kiwire_profiles WHERE tenant_id = '{$kiw_tenant}' AND name = '{$kiw_db->escape($_REQUEST['plan'])}' LIMIT 1");


    // $kiw_cost = $kiw_profile['price'] * (int)$_REQUEST['qty'];


    // if ($kiw_profile['price'] > 0 && $kiw_balance['balance_credit'] >= $kiw_cost || (int)$kiw_profile['price'] == 0) {


    $kiw_data['action']        = "create_voucher";
    $kiw_data['tenant_id']     = $kiw_tenant;
    $kiw_data['creator']       = $kiw_username;
    $kiw_data['quantity']      = (int)$_REQUEST['qty'];
    $kiw_data['price']         = floatval($kiw_profile['price']);
    $kiw_data['prefix']        = $kiw_db->escape($_REQUEST['prefix']);
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


        // $kiw_db->query("UPDATE kiwire_admin SET updated_date = NOW(),  balance_credit = (balance_credit - {$kiw_cost}) WHERE tenant_id = '{$_SESSION['tenant_id']}' AND username = '{$_SESSION['user_name']}' LIMIT 1");


        sync_logger("{$_SESSION['user_name']} create voucher", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Voucher has been created", "data" => null));
    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: There is an error generate the voucher", "data" => null));
    }
    
    // } else {

    //     echo json_encode(array("status" => "failed", "message" => "ERROR: Insufficient credit", "data" => null));
    // }

}


function statistics($kiw_db)
{

    global $kiw_tenant;


    $kiw_timezone = $_SESSION['timezone'];

    if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


    $kiw_account = $kiw_db->escape($_REQUEST['id']);

    if (strlen($kiw_account) > 0) {


        $kiw_result = array();


        $kiw_result['auth'] = $kiw_db->query_first("SELECT * FROM kiwire_account_auth WHERE username = '{$kiw_account}' AND tenant_id = '{$kiw_tenant}' LIMIT 1");
        $kiw_result['info'] = $kiw_db->query_first("SELECT * FROM kiwire_account_info WHERE username = '{$kiw_account}' AND tenant_id = '{$kiw_tenant}' LIMIT 1");

        $kiw_result['profile'] = $kiw_db->query_first("SELECT * FROM kiwire_profiles WHERE name = '{$kiw_result['auth']['profile_subs']}' AND tenant_id = '{$kiw_tenant}' LIMIT 1");


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


        $kiw_result['auth']['date_create'] = sync_tolocaltime($kiw_result['auth']['date_create'], $kiw_timezone);
        $kiw_result['auth']['date_value'] = sync_tolocaltime($kiw_result['auth']['date_value'], $kiw_timezone);
        $kiw_result['auth']['date_expiry'] = sync_tolocaltime($kiw_result['auth']['date_expiry'], $kiw_timezone);
        $kiw_result['auth']['date_last_login'] = sync_tolocaltime($kiw_result['auth']['date_last_login'], $kiw_timezone);
        $kiw_result['auth']['date_activate'] = sync_tolocaltime($kiw_result['auth']['date_activate'], $kiw_timezone);


        unset($kiw_result['auth']['password']);


        echo json_encode(array("status" => "success", "message" => null,  "data" => $kiw_result, "test" => $kiw_timezone));
    
    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: Missing account details", "data" => null));
    
    }

}


function delete()
{

    global $kiw_db, $kiw_tenant, $kiw_username;

    csrf($kiw_db->escape($_REQUEST['token']));

    $kiw_account = $kiw_db->escape($_REQUEST['username']);

    if (strlen($kiw_account) > 0) {

        $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(),  status = 'suspend' WHERE username ='{$kiw_account}' AND tenant_id = '{$kiw_tenant}' LIMIT 1");

        sync_logger("{$_SESSION['cpanel']['username']} deleted voucher {$kiw_account}", $kiw_tenant);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Invitation code [{$kiw_account}] has been deactivated", "data" => "UPDATE kiwire_account_auth SET updated_date = NOW(),  status = 'suspend' WHERE username ='{$kiw_account}' AND tenant_id = '{$kiw_tenant}' LIMIT 1"));
    }
}


function voucher_reset()
{

    global $kiw_db, $kiw_tenant;


    $kiw_account = $kiw_db->escape($_REQUEST['username']);

    if (!empty($kiw_account)) {


        $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), status = 'active', session_time = 0, quota_in = 0, quota_out = 0, date_activate = NULL WHERE username ='{$kiw_account}' AND tenant_id = '{$kiw_tenant}' LIMIT 1");


        sync_logger("{$_SESSION['cpanel']['username']} reset voucher {$kiw_account}", $kiw_tenant);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Invitation code [{$kiw_account}] has been reset", "data" => null));
    }
}