<?php

require_once dirname(__FILE__, 4) . "/includes/include_general.php";
require_once dirname(__FILE__, 4) . "/includes/include_radius.php";

require_once dirname(__FILE__, 5) . "/admin/includes/include_general.php";
require_once dirname(__FILE__, 5) . "/admin/includes/include_connection.php";


$kiw_data = sync_decrypt($_REQUEST['data']);

$kiw_data = json_decode($kiw_data, true);


if (is_array($kiw_data)){


    $kiw_signup = $kiw_cache->get("EMAIL_CONFIRM:{$kiw_data['tenant_id']}");

    if (empty($kiw_signup)) {


        $kiw_signup = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_int_email WHERE tenant_id = '{$kiw_data['tenant_id']}' LIMIT 1");

        if (empty($kiw_signup)) $kiw_signup = array("dummy" => true);

        $kiw_cache->set("EMAIL_CONFIRM:{$kiw_data['tenant_id']}", $kiw_signup, 1800);


    }


    if ($kiw_signup['enabled'] == "y") {


        $kiw_user = $kiw_db->query_first("SELECT status,profile_subs FROM kiwire_account_auth WHERE username = '{$kiw_data['username']}' AND tenant_id = '{$kiw_data['tenant_id']}' LIMIT 1");


        if (!empty($kiw_user)) {


            // no need to update kiwire_account_auth with latest profile since it will be handle by coa_user function


            $kiw_db->query("DELETE FROM kiwire_temporary_access WHERE username = '{$kiw_data['username']}' AND tenant_id = '{$kiw_data['tenant_id']}'");


            // do coa to update user information

            coa_user($kiw_db, $kiw_cache, $kiw_data['tenant_id'], $kiw_data['username'], $kiw_user['profile_subs']);


            $kiw_status = base64_encode("Your account has been verified.");



        } else {


            $kiw_status = base64_encode("Invalid account or account already expired.");


        }


        header("Location: /user/pages/public/{$kiw_data['tenant_id']}/{$kiw_signup['confirm_page']}/{$kiw_data['session']}/{$kiw_status}");


    }


}


