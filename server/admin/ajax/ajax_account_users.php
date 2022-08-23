<?php

$kiw['module'] = "Account -> Account -> List";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

require_once "../includes/include_session.php";
require_once "../includes/include_general.php";
require_once "../includes/include_connection.php";

require_once "../../libs/ssp.class.php";

require_once "../../user/includes/include_account.php";
require_once "../../user/includes/include_radius.php";


header("Content-Type: application/json");


if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}


$action = $_REQUEST['action'];


// check the tenant id to be used

if ($_SESSION['access_level'] == "superuser") {

    if (!empty($_SESSION['tenant_allowed'])){

        $kiw_tenants = explode(",", $_SESSION['tenant_allowed']);

        if (in_array($_REQUEST['tenant_id'], $kiw_tenants)){

            $kiw_tenant_id = $kiw_db->escape($_REQUEST['tenant_id']);

        } else $kiw_tenant_id = $_SESSION['tenant_id'];

    } else $kiw_tenant_id = $kiw_db->escape($_REQUEST['tenant_id']);

} else $kiw_tenant_id = $_SESSION['tenant_id'];


switch ($action) {

    case "import_account": import_account($kiw_db, $kiw_cache); break;
    case "user_chart_history": user_chart_history(); break;
    case "user_line_graph": user_line_graph(); break;
    case "history": get_history($kiw_db, $kiw_tenant_id); break;
    case "statistics": statistics($kiw_db); break;
    case "reset": user_reset(); break;
    case "get_all": get_all(); break;
    case "create": create(); break;
    case "get_update": get_update(); break;
    case "delete": delete(); break;
    case "user_security": user_security(); break;
    case "unblock_user": unblock_user(); break;
    case "edit_single_data": edit_single_data(); break;

    default: echo "ERROR: Wrong implementation";

}


function get_all() {


    global $kiw_db;


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_timezone = $_SESSION['timezone'];

        $kiw_timezone = empty($kiw_timezone) ?: "Asia/Kuala_Lumpur";


        $kiw_columns = array(
            array( 'db' => 'username',      'dt' => 1 ),
            array( 'db' => 'fullname',      'dt' => 2 ),
            array( 'db' => 'profile_subs',  'dt' => 3 ),
            array( 'db' => 'status',        'dt' => 4 ),
            array( 'db' => 'date_expiry',   'dt' => 5 ),
            array( 'db' => 'tenant_id',     'dt' => 6 )
        );


        $kiw_sqlinfo = array('user' => SYNC_DB1_USER, 'pass' => SYNC_DB1_PASSWORD, 'db' => SYNC_DB1_DATABASE, 'host' => SYNC_DB1_HOST, 'port' => SYNC_DB1_PORT);


        $kiw_where = "(ktype != 'voucher' AND ktype != 'simcard')";


        if (!empty($_REQUEST['username'])){

            $kiw_where .= " AND username LIKE '" . $kiw_db->escape($_REQUEST['username']) . "%'";

        }


        if (!empty($_REQUEST['status'])){

            $kiw_where .= " AND status = '" . $kiw_db->escape($_REQUEST['status']) . "'";

        }


        if (!empty($_REQUEST['profile'])){

            $kiw_where .= " AND profile_subs LIKE '" . $kiw_db->escape($_REQUEST['profile']) . "%'";

        }


        if (!empty($_REQUEST['expiry_from'])){


            $kiw_temp = date("Y-m-d H:i:s", strtotime($_REQUEST['expiry_from']));

            $kiw_temp = sync_toutctime($kiw_temp);


            $kiw_where .= " AND date_expiry >= '{$kiw_temp}'";


        }


        if (!empty($_REQUEST['expiry_until'])){


            $kiw_temp = date("Y-m-d H:i:s", strtotime($_REQUEST['expiry_until']));

            $kiw_temp = sync_toutctime($kiw_temp);


            $kiw_where .= " AND date_expiry <= '{$kiw_temp}'";


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


        $kiw_data = SSP::complex( $_GET, $kiw_sqlinfo, "kiwire_account_auth", "id", $kiw_columns, null, $kiw_where);


        $kiw_start = $_GET['start'] + 1;

        $kiw_end = count($kiw_data['data']) + $kiw_start;


        for ($x = $kiw_start; $x < $kiw_end; $x++){

            $kiw_data['data'][$x - $kiw_start][0] = $x;

            $kiw_data['data'][$x - $kiw_start][5] = sync_tolocaltime($kiw_data['data'][$x - $kiw_start][5], $kiw_timezone);

        }


        echo json_encode($kiw_data);


    }


}


function create()
{


    global $kiw_db, $kiw_cache, $kiw_tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $kiw_timezone = $_SESSION['timezone'];

        if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


        $kiw_username = $kiw_db->escape($_REQUEST['username']);

        if (strpos($kiw_username, " ") > 0) {


            die(json_encode(array("status" => "failed", "message" => "ERROR: Username cannot have space.", "data" => null)));
        }


        $_REQUEST['username'] = $kiw_db->escape($_REQUEST['username']);


        $kiw_existed = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_account_auth WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant_id}' LIMIT 1");


        if ($kiw_existed['kcount'] == 0) {


            $kiw_user = array();

            $kiw_user['tenant_id'] = $kiw_tenant_id;

            $kiw_user['creator']        = $_SESSION['user_name'];
            $kiw_user['username']       = $kiw_db->sanitize($_REQUEST['username']);
            $kiw_user['fullname']       = $kiw_db->sanitize($_REQUEST['fullname']);
            $kiw_user['password']       = $_REQUEST['password'];
            $kiw_user['email_address']  = $kiw_db->escape($_REQUEST['email_address']);

            $kiw_user['phone_number']   = $_REQUEST['phone_number'];
            $kiw_user['remark']         = $kiw_db->sanitize($_REQUEST['remark']);
            $kiw_user['profile_subs']   = $kiw_db->escape($_REQUEST['profile_subs']);
            $kiw_user['profile_curr']   = $kiw_db->escape($_REQUEST['profile_subs']);
            $kiw_user['ktype']          = "account";
            $kiw_user['status']         = $_REQUEST['status'];

            $kiw_user['integration']    = $_REQUEST['integration'];
            $kiw_user['allowed_zone']   = $_REQUEST['allowed_zone'];
            $kiw_user['date_expiry']    = sync_toutctime(date("Y-m-d H:i:s", strtotime($_REQUEST['date_expiry'])), $kiw_timezone);
            $kiw_user['date_value']     = "NOW()";

            if(create_account($kiw_db, $kiw_cache, $kiw_user)){
            
                sync_logger("{$_SESSION['user_name']} create users {$_REQUEST['username']}", $kiw_tenant_id);

                echo json_encode(array("status" => "success", "message" => "SUCCESS: New user added", "data" => null));

            }else{

                echo json_encode(array("status" => "failed", "message" => "ERROR: Check your input.", "data" => null));
            
            }

        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: This username already existed in the system.", "data" => null));

        }


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}


function get_update() {


    global $kiw_db, $kiw_tenant_id;


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_result = array();


        $kiw_username = $kiw_db->escape($_REQUEST['username']);


        $kiw_user = $kiw_db->query_first("SELECT * FROM kiwire_account_auth WHERE tenant_id = '{$kiw_tenant_id}' AND username = '{$kiw_username}' LIMIT 1");

        $kiw_result['username']     = $kiw_user['username'];
        $kiw_result['fullname']     = $kiw_user['fullname'];
        $kiw_result['password']     = $kiw_user['password'];
        $kiw_result['remark']       = $kiw_user['remark'];
        $kiw_result['profile_subs'] = $kiw_user['profile_subs'];
        $kiw_result['ktype']        = "account";
        $kiw_result['status']       = $kiw_user['status'];
        $kiw_result['integration']  = $kiw_user['integration'];
        $kiw_result['allowed_zone'] = $kiw_user['allowed_zone'];
        $kiw_result['date_expiry']  = sync_tolocaltime($kiw_user['date_expiry'], $_SESSION['timezone']);
        $kiw_result['date_value']   = sync_tolocaltime($kiw_user['date_value'], $_SESSION['timezone']);


        $kiw_result['date_expiry']  = date("m/d/Y", strtotime($kiw_result['date_expiry']));
        $kiw_result['date_value']   = date("m/d/Y", strtotime($kiw_result['date_value']));

        $kiw_result['email_address']  = $kiw_user['email_address'];
        $kiw_result['phone_number']   = $kiw_user['phone_number'];

        $kiw_result['tenant_id']    = $kiw_user['tenant_id'];

        echo json_encode(array("status" => "success", "message" => $_SESSION['timezone'], "data" => $kiw_result));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}


function delete() {


    global $kiw_db, $kiw_cache, $kiw_tenant_id;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $kiw_user = $kiw_db->escape($_REQUEST['username']);


        if (strlen($kiw_user) > 0) {


            $kiw_db->query("DELETE FROM kiwire_account_info WHERE username ='{$kiw_user}' AND tenant_id = '{$kiw_tenant_id}' LIMIT 1");
            $kiw_db->query("DELETE FROM kiwire_account_auth WHERE username ='{$kiw_user}' AND tenant_id = '{$kiw_tenant_id}' LIMIT 1");


            // set the auto-login to false

            $kiw_db->query("UPDATE kiwire_device_history SET updated_date = NOW(), last_account = '' WHERE last_account ='{$kiw_user}' AND tenant_id = '{$kiw_tenant_id}' LIMIT 1");


            // check if online, then kicked

            $kiw_temp = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_active_session WHERE username ='{$kiw_user}' AND tenant_id = '{$kiw_tenant_id}'");

            if ($kiw_temp['kcount'] > 0){

                disconnect_user($kiw_db, $kiw_cache, $kiw_tenant_id, $kiw_user);

            }


            $kiw_replication = @file_get_contents(dirname(__FILE__, 3) . "/custom/ha_setting.json");

            $kiw_replication = json_decode($kiw_replication, true);


            if ($kiw_replication['enabled'] == "y" && $kiw_replication['role'] == "master"){


                $kiw_curl = curl_init();

                curl_setopt($kiw_curl, CURLOPT_URL, "http://{$kiw_replication['backup_ip_address']}:9958");
                curl_setopt($kiw_curl, CURLOPT_POST, true);
                curl_setopt($kiw_curl, CURLOPT_POSTFIELDS, http_build_query(array("action" => "delete", "username" => $kiw_user, "tenant_id" => $kiw_tenant_id)));

                curl_setopt($kiw_curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($kiw_curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($kiw_curl, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($kiw_curl, CURLOPT_TIMEOUT, 10);
                curl_setopt($kiw_curl, CURLOPT_CONNECTTIMEOUT, 10);

                unset($kiw_file);

                curl_exec($kiw_curl);
                curl_close($kiw_curl);


            }


            sync_logger("{$_SESSION['user_name']} deleted users {$kiw_user}", $kiw_tenant_id);

            echo json_encode(array("status" => "success", "message" => "SUCCESS: User [{$kiw_user}] has been deleted", "data" => null));


        }


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}


function edit_single_data()
{

    global $kiw_db, $kiw_tenant_id;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        csrf($kiw_db->escape($_REQUEST['token']));
        
        $kiw_account = $kiw_db->sanitize($_REQUEST['username']);

        $kiw_timezone = $_SESSION['timezone'];

        if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


        $kiw_result = array();


        // check if password is not hash then means need to update

        if (sync_decrypt($_REQUEST['password']) == false) {

            $kiw_result['password'] = sync_encrypt($_REQUEST['password']);

        }

        $kiw_result['fullname']         = $kiw_db->sanitize($_REQUEST['fullname']);
        $kiw_result['remark']           = $kiw_db->sanitize($_REQUEST['remark']);
        $kiw_result['fullname']         = $kiw_db->sanitize($_REQUEST['fullname']);
        $kiw_result['email_address']    = $kiw_db->escape($_REQUEST['email_address']);

        $kiw_result['phone_number']     = $_REQUEST['phone_number'];
        $kiw_result['profile_subs']     = $kiw_db->escape($_REQUEST['profile_subs']);
        $kiw_result['profile_curr']     = $kiw_db->escape($_REQUEST['profile_subs']);
        $kiw_result['ktype']            = "account";

        $kiw_result['status']           = $_REQUEST['status'];
        $kiw_result['integration']      = $_REQUEST['integration'];
        $kiw_result['allowed_zone']     = $_REQUEST['allowed_zone'];
        $kiw_result['allowed_mac']      = $_REQUEST['allowed_mac'];

        $kiw_result['date_expiry']      = sync_toutctime(date("Y-m-d H:i:s", strtotime($_REQUEST['date_expiry'])), $kiw_timezone);


        $kiw_db->query(sql_update($kiw_db, "kiwire_account_auth", $kiw_result, "username = '{$kiw_account}' AND tenant_id = '{$kiw_tenant_id}' LIMIT 1"));


        sync_logger("{$_SESSION['user_name']} updated users {$kiw_account}", $kiw_tenant_id);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: User has been updated", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}


function user_reset(){


    global $kiw_db, $kiw_cache, $kiw_tenant_id;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        $kiw_account = $kiw_db->escape($_REQUEST['username']);


        if (strlen($kiw_account) > 0) {


            
            $kiw_cloud = $kiw_db->query_first("SELECT reset_acc_and_date_password FROM kiwire_clouds WHERE kiwire_clouds.tenant_id = '$kiw_tenant_id' LIMIT 1");
            
            $date_password = "";
            if($kiw_cloud['reset_acc_and_date_password'] == "y") {

                $date_password = " , date_password = NULL ";
            }
            

            $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(),  status = 'active', date_activate = NULL, quota_in = 0, quota_out = 0, session_time = 0, profile_curr = profile_subs {$date_password} WHERE tenant_id = '{$kiw_tenant_id}' AND username = '{$kiw_account}' LIMIT 1");


            $kiw_cache->del("RETRY_ERROR:COUNT:{$kiw_tenant_id}:{$kiw_account}");

            $kiw_cache->del("LOGIN_ATTEMP:{$kiw_tenant_id}:{$kiw_account}");


            sync_logger("{$_SESSION['user_name']} reset users {$kiw_account}", $kiw_tenant_id);

            echo json_encode(array("status" => "success", "message" => "SUCCESS: Account [{$kiw_account}] has been reset"));


        }


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}


function statistics($kiw_db){


    global $kiw_tenant_id;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        $kiw_account = $kiw_db->escape($_REQUEST['id']);

        if (strlen($kiw_account) > 0) {


            $kiw_timezone = $_SESSION['timezone'];

            if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


            $kiw_result = array();


            $kiw_result['auth'] = $kiw_db->query_first("SELECT * FROM kiwire_account_auth WHERE username = '{$kiw_account}' AND tenant_id = '{$kiw_tenant_id}' LIMIT 1");
            $kiw_result['info'] = $kiw_db->query_first("SELECT * FROM kiwire_account_info WHERE username = '{$kiw_account}' AND tenant_id = '{$kiw_tenant_id}' LIMIT 1");
            $kiw_result['profile'] = $kiw_db->query_first("SELECT * FROM kiwire_profiles WHERE name = '{$kiw_result['auth']['profile_subs']}' AND tenant_id = '{$kiw_tenant_id}' LIMIT 1");


            if (empty($kiw_result['profile'])){

                $kiw_result['auth']['profile_subs'] = "Missing Profile";
                $kiw_result['auth']['profile_curr'] = "Missing Profile";

                $kiw_result['profile']['type']  = "0";
                $kiw_result['profile']['price'] = 0;
                $kiw_result['profile']['attribute']['reply:Idle-Timeout']  = 0;
                $kiw_result['profile']['attribute']['control:Simultaneous-Use'] = 0;
                $kiw_result['profile']['attribute']['control:Kiwire-Total-Quota']  = 0;
                $kiw_result['profile']['attribute']['reply:WISPr-Bandwidth-Max-Down'] = 0;
                $kiw_result['profile']['attribute']['reply:WISPr-Bandwidth-Max-Up'] = 0;

            } else $kiw_result['profile']['attribute'] = json_decode($kiw_result['profile']['attribute'], true);


            if ($kiw_result['auth']['date_activate'] === '0000-00-00 00:00:00') $kiw_result['date_activate'] = $kiw_result['auth']['date_activate'];


            $kiw_result['auth']['date_create']      = sync_tolocaltime($kiw_result['auth']['date_create'], $kiw_timezone);
            $kiw_result['auth']['date_value']       = sync_tolocaltime($kiw_result['auth']['date_value'], $kiw_timezone);
            $kiw_result['auth']['date_expiry']      = sync_tolocaltime($kiw_result['auth']['date_expiry'], $kiw_timezone);
            $kiw_result['auth']['date_last_login']  = sync_tolocaltime($kiw_result['auth']['date_last_login'], $kiw_timezone);
            $kiw_result['auth']['date_activate']    = sync_tolocaltime($kiw_result['auth']['date_activate'], $kiw_timezone);


            unset($kiw_result['auth']['password']);


            echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_result));


        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Missing account details", "data" => null));

        }


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}


function get_history($kiw_db, $kiw_tenant_id){


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


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


function import_account($kiw_db, $kiw_cache){

    $kiw_timezone = $_SESSION['timezone'];

    if (empty($kiw_timezone)) $kiw_timezone = "Asia/Kuala_Lumpur";


    $kiw_file = $_FILES['accounts_file'];

    $ext = end((explode(".", $kiw_file['name'])));

    if($ext == 'csv'){

        if ($kiw_file['size'] > 0){

            // check the tenant id to be used
    
            if ($_SESSION['access_level'] == "superuser") {
    
                if (!empty($_SESSION['tenant_allowed'])){
    
                    $kiw_tenants = explode(",", $_SESSION['tenant_allowed']);
    
                    if (in_array($_REQUEST['tenant_id_import'], $kiw_tenants)){
    
                        $kiw_tenant_id = $kiw_db->escape($_REQUEST['tenant_id_import']);
    
                    } else $kiw_tenant_id = $_SESSION['tenant_id'];
    
                } else $kiw_tenant_id = $kiw_db->escape($_REQUEST['tenant_id_import']);
    
            } else $kiw_tenant_id = $_SESSION['tenant_id'];
    
            $kiw_this_session_path = dirname(__FILE__, 3) . "/temp/import_users_status_" . substr(md5(time()), 0, 4) . ".csv";
    
    
            // get the account setting
    
            $kiw_config['profile']       = $kiw_db->escape($_REQUEST['iprofile']);
            $kiw_config['integration']   = $kiw_db->escape($_REQUEST['iintegration']);
            $kiw_config['allowed_zone']  = $kiw_db->escape($_REQUEST['izone']);
            $kiw_config['status']        = $kiw_db->escape($_REQUEST['istatus']);
    
            $kiw_config['date_expiry']   = $kiw_db->escape($_REQUEST['iexpire']);
    
    
            $kiw_file_reader = fopen($kiw_file['tmp_name'], "r");
    
    
            file_put_contents($kiw_this_session_path, implode(",", ["Username", "Status", "Reason"]) . "\n", FILE_APPEND);
    
    
            while (!feof($kiw_file_reader)){
    
    
                $kiw_account = fgetcsv($kiw_file_reader);
    
                if (!empty($kiw_account)){
    
    
                    if (count($kiw_account) == 7){
    
    
                        $kiw_existed = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_account_auth WHERE username = '{$kiw_account[0]}' AND tenant_id = '{$kiw_tenant_id}'");
    
                        if ($kiw_existed['kcount'] == 0){
    
    
                            $kiw_user = array();
    
                            $kiw_user['creator']        = $_SESSION['user_name'];
                            $kiw_user['tenant_id']      = $kiw_tenant_id;
                            $kiw_user['username']       = $kiw_account[0];
                            $kiw_user['fullname']       = $kiw_account[2];
                            $kiw_user['password']       = $kiw_account[1];
                            $kiw_user['email_address']  = $kiw_account[3];
    
                            $kiw_user['phone_number']   = $kiw_account[4];
                            $kiw_user['remark']         = $kiw_account[5];
                            $kiw_user['profile_subs']   = $kiw_config['profile'];
                            $kiw_user['profile_curr']   = $kiw_config['profile'];
                            $kiw_user['ktype']          = "account";
                            $kiw_user['status']         = $kiw_config['status'];;
    
                            $kiw_user['integration']    = $kiw_config['integration'];
                            $kiw_user['allowed_zone']   = $kiw_config['allowed_zone'];
                            $kiw_user['date_expiry']    = sync_toutctime(date("Y-m-d H:i:s", strtotime($kiw_config['date_expiry'])), $kiw_timezone);
                            $kiw_user['date_value']     = "NOW()";
    
                            $kiw_user['allowed_mac']    = implode(",", explode(";", $kiw_account[6]));
    
    
                            if(create_account($kiw_db, $kiw_cache, $kiw_user)){
    
                                sync_logger("{$_SESSION['user_name']} imported users {$kiw_account[0]}", $kiw_tenant_id);
        
                                file_put_contents($kiw_this_session_path, implode(",", [$kiw_account[0], "Succeed", ""]) . "\n", FILE_APPEND);
                            }
                            else{
                                
                                file_put_contents($kiw_this_session_path, implode(",", [$kiw_account[0], "Failed", "Create user error"]) . "\n", FILE_APPEND);
                            
                            }
    
    
                        } else {
    
    
                            file_put_contents($kiw_this_session_path, implode(",", [$kiw_account[0], "Failed", "Duplicate"]) . "\n", FILE_APPEND);
    
    
                        }
    
    
                    } else {
    
    
                        file_put_contents($kiw_this_session_path, implode(",", [$kiw_account[0], "Failed", "Invalid"]) . "\n", FILE_APPEND);
    
    
                    }
    
    
                }
    
    
            }
    
    
            echo json_encode(array("status" => "success", "message" => "SUCCESS: Users has been imported. Please check log file for details.", "data" => basename($kiw_this_session_path)));
    
    
        } else {
    
            echo json_encode(array("status" => "failed", "message" => "ERROR: Missing accounts file.", "data" => null));
            
        }
        
    }
    else echo json_encode(array("status" => "failed", "message" => "ERROR: Invalid file format [ {$ext} ]. Only .csv allowed", "data" => $kiw_file));
    
    
    

}


function user_chart_history(){


    global $kiw_db;

    $kiw_username = $kiw_db->escape($_REQUEST['username']);

    $kiw_tenant = $kiw_db->escape($_REQUEST['tenant']);



    $kiw_user = $kiw_db->query_first("SELECT * FROM kiwire_account_auth WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant}' LIMIT 1" );

    $kiw_profile = $kiw_db->query_first("SELECT * FROM kiwire_profiles WHERE name = '{$kiw_user['profile_curr']}' AND tenant_id = '{$kiw_user['tenant_id']}' LIMIT 1");


    $kiw_profile['attribute'] = json_decode($kiw_profile['attribute'], true);


    $kiw_total_byte = $kiw_profile['attribute']['control:Kiwire-Total-Quota'];


    try {


        if($kiw_profile['type'] == "free"){


            $kiw_remaining_time = "Unlimited";

            $kiw_percentage_time = 0;


        } elseif($kiw_profile['type'] == "countdown"){


            // $kiw_remaining_time = $kiw_user['session_time'] - $kiw_profile['attribute']['control:Max-All-Session'];

            $kiw_remaining_time = $kiw_profile['attribute']['control:Max-All-Session'] - $kiw_user['session_time'];

            $kiw_percentage_time = ($kiw_user['session_time'] / $kiw_profile['attribute']['control:Max-All-Session'])  * 100;


        } elseif($kiw_profile['type'] == "expiration"){

          

            if(empty($kiw_user['date_activate'])){


                $kiw_remaining_time = $kiw_profile['attribute']['control:Access-Period'];

                $kiw_percentage_time = 0;

                
            } else {
            

                $kiw_remaining_time = $kiw_profile['attribute']['control:Access-Period'] - (time() - strtotime($kiw_user['date_activate']));

                $kiw_percentage_time = ((time() - strtotime($kiw_user['date_activate'])) / $kiw_profile['attribute']['control:Access-Period']) * 100;

            
            }



        }

    } catch (Exception $e){
        
        die("ERROR: " . $e->getMessage());

    }


    try{


        $kiw_percentage_quota = (($kiw_user['quota_in'] + $kiw_user['quota_out']) / ($kiw_total_byte * 1024 * 1024)) * 100;

        $kiw_remaining_byte = round((($kiw_total_byte * 1024 * 1024) - ($kiw_user['quota_in'] + $kiw_user['quota_out'])) / (1024 * 1024), 3, PHP_ROUND_HALF_DOWN);

        if($kiw_remaining_byte < 0) $kiw_remaining_byte = "Unlimited";


    } catch (Exception $e){

        die("ERROR: " . $e->getMessage());

    }

    if(!empty($kiw_user) && !empty($kiw_profile)){

    
        echo json_encode(
            [
                "status" => "success",
                "remaining_quota" => round(($kiw_remaining_byte > 0) ? $kiw_remaining_byte : 0, 2, PHP_ROUND_HALF_UP),
                "remaining_time" => round(($kiw_remaining_time  > 0) ? ($kiw_remaining_time / 60) : 0, 1, PHP_ROUND_HALF_UP),
                "percentage_quota" => (int)$kiw_percentage_quota,
                "percentage_time" => (int)$kiw_percentage_time,
             
            ]
        );


    } else echo json_encode(array("status" => "failed", "message" => "ERROR: Missing accounts data", "data" => null));



}

function user_line_graph()
{

    global $kiw_db;


    $kiw_username = $kiw_db->escape($_REQUEST['username']);

    $kiw_tenant = $kiw_db->escape($_REQUEST['tenant']);


    $kiw_session = $kiw_db->fetch_array("SELECT DATE(CONVERT_TZ(start_time, 'UTC', 'Asia/Kuala_Lumpur')) AS xstart_time, SUM(session_time) AS session_time, SUM(quota_in + quota_out) AS quota FROM kiwire_sessions_202008 WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant}' GROUP BY xstart_time");


    if (!empty($kiw_session)) {

        echo json_encode(array("status" => "success", "data" => $kiw_session));

    } else echo json_encode(array("status" => "failed", "message" => "ERROR: Missing sessions data", "data" => null));

    
}

function user_security(){

    global $kiw_db;

    $kiw_username = $kiw_db->escape($_REQUEST['username']);

    $kiw_tenant = $kiw_db->escape($_REQUEST['tenant']);

    if(!empty($kiw_username) && !empty($kiw_tenant)){
        
        $kiw_security = $kiw_db->fetch_array("SELECT date_time,severity,vuln FROM kiwire_paloalto WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant}' ORDER BY date_time DESC LIMIT 10" );
        // $kiw_security = $kiw_db->fetch_array("SELECT date_time,severity,vuln FROM kiwire_paloalto WHERE username = 'Guest' AND tenant_id = 'default'   ORDER BY date_time DESC LIMIT 10" );
        
        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_security));
        
    }else{

        echo json_encode(array("status" => "failed", "message" => null, "data" => null));
    }

}

function unblock_user(){

    global $kiw_db;

    $kiw_username = $kiw_db->escape($_REQUEST['username']);
    $kiw_tenant = $kiw_db->escape($_REQUEST['tenant']);

    if(!empty($kiw_username) && !empty($kiw_tenant)){
    
        $kiw_user = $kiw_db->fetch_array("SELECT status FROM kiwire_account_auth WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant}' LIMIT 1" );
        if($kiw_user){
            
            if($kiw_user['status'] = "blocked"){

                $kiw_db->query("UPDATE kiwire_account_auth SET status='active'  WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant}'");

                echo json_encode(array("status" => "success", "message" => 'Success', "data" => null));
            }
            else{
                
                echo json_encode(array("status" => "failed", "message" => 'User status not blocked', "data" => null));
            }
            
        }
        else{
            
            echo json_encode(array("status" => "failed", "message" => 'User not found', "data" => null));
        }

    }

}