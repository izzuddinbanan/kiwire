<?php

function check_in_room($kiw_db, $kiw_user){

    $kiw_temp = $kiw_db->query("SELECT * FROM kiwire_account_auth WHERE username = '{$kiw_user['username']}' AND tenant_id = '{$kiw_user['tenant_id']}' LIMIT 1")[0];

    if (empty($kiw_temp)){

        // create user if no account info

        echo "create user";

    }


    return true;

}


function check_out_room($kiw_db, $kiw_user){

    $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), status = 'suspend', date_activate = 'NULL' WHERE username = '{$kiw_user['username']}' AND tenant_id = '{$kiw_user['tenant_id']}' LIMIT 1");
    $kiw_db->query("UPDATE kiwire_account_info SET updated_date = NOW(), fullname = '{$kiw_user['username']}' WHERE username = '{$kiw_user['username']}' AND tenant_id = '{$kiw_user['tenant_id']}' LIMIT 1");

    $kiw_temp = $kiw_db->query("SELECT * FROM kiwire_active_session WHERE username = '{$kiw_user['username']}' AND tenant_id = '{$kiw_user['tenant_id']}'");

    if (count($kiw_temp) > 0){

        // disconnect user since no more internet should be allow

    }

    return true;

}


function change_room_info($kiw_db, $kiw_cache, $kiw_user){

    if (isset($kiw_user['leave'])){

        // get the current info first

        $kiw_temp = $kiw_db->query("SELECT * FROM kiwire_account_auth WHERE username = '{$kiw_user['username']}' AND tenant_id = '{$kiw_user['tenant_id']}' LIMIT 1");



    } else {

        $kiw_db->query("UPDATE kiwire_account_auth SET updated_date = NOW(), fullname = '' WHERE username = '{$kiw_user['username']}' AND tenant_id = '{$kiw_user['tenant_id']}' LIMIT 1");
        $kiw_db->query("UPDATE kiwire_account_info SET updated_date = NOW(), fullname = '' WHERE username = '{$kiw_user['username']}' AND tenant_id = '{$kiw_user['tenant_id']}' LIMIT 1");

    }

    return true;


}



