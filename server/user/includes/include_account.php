<?php

require_once dirname(__FILE__, 3) . "/admin/includes/include_general.php";
require_once dirname(__FILE__, 3) . "/libs/class.sql.helper.php";


function create_account($kiw_db, $kiw_cache, $kiw_user){

    // introduce available until what time
    // expired after when

    $kiw_time = date("YmdH");

    // if empty user data, return false

    if (empty($kiw_user['tenant_id']) || empty($kiw_user['username'])) return false;
    if (empty($kiw_user['profile_subs']) || empty($kiw_user['password'])) return false;


    // if invalid profile skip

    if ($kiw_user['profile_subs'] == "none") return false;


    $kiw_temp['id']             = "NULL";
    $kiw_temp['tenant_id']      = $kiw_db->escape($kiw_user['tenant_id']);
    $kiw_temp['username']       = $kiw_db->escape($kiw_user['username']);
    $kiw_temp['updated_date']   = "NOW()";
    $kiw_temp['ktype']          = !empty($kiw_user['ktype']) ? $kiw_user['ktype'] : "account";


    $kiw_temp['fullname']        = (strlen($kiw_user['fullname']) > 0) ? $kiw_db->escape($kiw_user['fullname']) : "NA";
    $kiw_temp['email_address']   = (strlen($kiw_user['email_address']) > 0) ? $kiw_db->escape($kiw_user['email_address']) : "NA";
    $kiw_temp['phone_number']    = (strlen($kiw_user['phone_number']) > 0) ? $kiw_db->escape($kiw_user['phone_number']) : "NA";

    // set the creator to system if not available

    $kiw_temp['creator'] = (strlen($kiw_user['creator']) > 0) ? $kiw_user['creator'] : "system";


    // use hash as password for privacy

    $kiw_temp['password']       = sync_encrypt($kiw_user['password']);

    $kiw_temp['price']          = ($kiw_user['price'] > 0) ? $kiw_user['price'] : 0;
    $kiw_temp['remark']         = $kiw_user['remark'];
    $kiw_temp['profile_subs']   = $kiw_user['profile_subs'];
    $kiw_temp['profile_curr']   = (empty($kiw_user['profile_curr']) ? $kiw_user['profile_subs'] : $kiw_user['profile_curr']);
    $kiw_temp['profile_cus']    = $kiw_user['profile_cus'];
    $kiw_temp['bulk_id']        = $kiw_user['bulk_id'] ?: "";
    $kiw_temp['status']         = ($kiw_user['status'] ?: "active");
    $kiw_temp['integration']    = ($kiw_user['integration'] ?: "int");
    $kiw_temp['allowed_zone']   = ($kiw_user['allowed_zone'] ?: "none");
    $kiw_temp['allowed_mac']    = ($kiw_user['allowed_mac'] ?: "");
    $kiw_temp['date_create']    = "NOW()";
    $kiw_temp['date_activate']  = "NULL";
    $kiw_temp['date_value']     = ($kiw_user['date_value'] ?: "NOW()");
    $kiw_temp['date_expiry']    = ($kiw_user['date_expiry'] ?: date("Y-m-d H:i:s", strtotime("+10 Year")));
    $kiw_temp['date_remove']    = ($kiw_user['date_remove'] ?: date("Y-m-d H:i:s", strtotime("+10 Year")));
    $kiw_temp['login']          = isset($kiw_user['login']) ? 1 : 0;
    $kiw_temp['date_last_login']    = isset($kiw_user['date_last_login']) ? $kiw_user['date_last_login'] : NULL;
    $kiw_temp['date_activate']      = isset($kiw_user['date_activate']) ? $kiw_user['date_activate'] : NULL;
    $kiw_temp['date_password']      = isset($kiw_user['date_password']) ? $kiw_user['date_password'] : NULL;


    if (isset($kiw_user['date_password']) && !empty($kiw_user['date_password'])){

        $kiw_temp['date_password'] = "NOW()";

    } else $kiw_temp['date_password'] = "NULL";


    if(!$kiw_db->query(sql_insert($kiw_db, "kiwire_account_auth", $kiw_temp))){
        return false;
    };

    $kiw_cache->incr("REPORT_ACCOUNT_CREATION:{$kiw_time}:{$kiw_temp['tenant_id']}:{$kiw_temp['ktype']}");


    return true;


}


function update_account_info($kiw_db, $kiw_tenant, $kiw_account, $kiw_info){


    if (!isset($kiw_info['source'])) $kiw_info['source'] = "system";

    $kiw_db->query(sql_update($kiw_db, "kiwire_account_info", $kiw_info, "tenant_id = '{$kiw_tenant}' AND username = '{$kiw_account}' LIMIT 1"));


}



function check_account_exist($kiw_db, $username = "", $tenant_id = ""){


    if (empty($tenant_id)) {

        if (!empty($_SESSION['controller']['tenant_id'])) {

            $tenant_id = $_SESSION['controller']['tenant_id'];

        } else return true;

    }


    if (!empty($username)){

        $kiw_temp = $kiw_db->query_first("SELECT COUNT(username) AS ucount FROM kiwire_account_auth WHERE username = '{$username}' AND tenant_id = '{$tenant_id}'");

        if ($kiw_temp['ucount'] == 0) return false;
        else return true;

    } else return true;


}


function login_user($username, $password, $session_id){

    echo "
        
        <form name='login' action='/user/login/?session={$session_id}' method='post'>
            <input type='hidden' name='username' value='{$username}'>
            <input type='hidden' name='password' value='{$password}'>
        </form>
        
        <script>
            window.onload = function(){
                login.submit();
            }
        </script>

    ";

    die();

}

function login_auth($kiw_db, $kiw_user){

    $kiw_temp['tenant_id']      = $kiw_db->escape($kiw_user['tenant_id']);
    $kiw_temp['updated_date']   = "NOW()";
    $kiw_temp['username']       = $kiw_db->escape($kiw_user['username']);
    $kiw_temp['password']       = $kiw_db->escape($kiw_user['password']);
    $kiw_temp['status']         = $kiw_db->escape($kiw_user['status']);
    $kiw_temp['reason']         = $kiw_db->escape($kiw_user['reason']);

    if(!$kiw_db->query(sql_insert($kiw_db, "kiwire_login_auth", $kiw_temp))){
        return false;
    };

    return true;

}










