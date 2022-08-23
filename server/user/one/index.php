<?php


require_once dirname(__FILE__, 2) . "/includes/include_redirect_from_login.php";

require_once dirname(__FILE__, 2) . "/includes/include_session.php";
require_once dirname(__FILE__, 2) . "/includes/include_general.php";
require_once dirname(__FILE__, 2) . "/includes/include_account.php";

require_once dirname(__FILE__, 3) . "/admin/includes/include_connection.php";


$kiw_signup = $kiw_cache->get("ONE_CLICK_DATA:{$_SESSION['controller']['tenant_id']}");

if (empty($kiw_signup)){

    $kiw_signup = $kiw_db->query_first("SELECT SQL_CACHE * FROM kiwire_one_click_login WHERE tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

    if (empty($kiw_signup)) $kiw_signup = array("dummy" => true);

    $kiw_cache->set("ONE_CLICK_DATA:{$_SESSION['controller']['tenant_id']}", $kiw_signup, 1800);

}


if (!empty($kiw_signup) && $kiw_signup['enabled'] == "y"){


    $mac_address = preg_replace("/[^a-zA-Z0-9]+/", "", $_SESSION['user']['mac']);


    $kiw_create_user = array();

    if ($kiw_signup['login_using_id'] == "username"){


        $kiw_user = $kiw_db->query_first("SELECT username,password FROM kiwire_account_auth WHERE username = '{$kiw_signup['username']}' AND tenant_id = '{$_SESSION['controller']['tenant_id']}' LIMIT 1");

        if (!empty($kiw_user)) {


            $kiw_create_user['username'] = $kiw_user['username'];
            $kiw_create_user['password'] = $kiw_user['password'];

            unset($kiw_user);


        } else {


            error_redirect($_SERVER['HTTP_REFERER'],  "You are login using invalid credential.");


        }


    } else {



        if (check_account_exist($kiw_db, $mac_address, $_SESSION['controller']['tenant_id']) == false){


            $kiw_create_user['username']       = $mac_address;
            $kiw_create_user['password']       = $mac_address;
            $kiw_create_user['fullname']       = $kiw_db->escape($_REQUEST['fullname']);
            $kiw_create_user['email_address']  = $kiw_db->escape($_REQUEST['email_address']);
	        $kiw_create_user['phone_number']   = $kiw_db->escape($_REQUEST['phone_number']);

            $kiw_create_user['tenant_id']      = $_SESSION['controller']['tenant_id'];
            $kiw_create_user['profile_subs']   = $kiw_signup['profile'];
            $kiw_create_user['ktype']          = "account";
            $kiw_create_user['status']         = "active";
            $kiw_create_user['integration']    = "int";
            $kiw_create_user['allowed_zone']   = $kiw_signup['allowed_zone'];
            $kiw_create_user['date_value']     = "NOW()";
            $kiw_create_user['date_expiry']    = date("Y-m-d H:i:s", strtotime("+{$kiw_signup['validity']} Day"));

            create_account($kiw_db, $kiw_cache, $kiw_create_user);


            unset($kiw_create_user);


            // get the data mapping setting

            $kiw_mapping = file_get_contents(dirname(__FILE__, 3) . "/custom/{$_SESSION['controller']['tenant_id']}/data-mapping.json");

            $kiw_mapping = json_decode($kiw_mapping, true);


            // collect all necessary data

            if (strlen($kiw_signup['data']) > 0 && count($kiw_mapping) > 0) {


                foreach (explode(",", $kiw_signup['data']) as $kiw_data) {


                    if (isset($_REQUEST[$kiw_data]) && !empty($_REQUEST[$kiw_data])) {

                        foreach ($kiw_mapping as $kiw_map) {

                            if ($kiw_map['variable'] == $kiw_data) {

                                $kiw_create_user[$kiw_map['field']] = $kiw_db->escape($_REQUEST[$kiw_data]);

                                break;

                            }

                        }

                    }


                }


                $kiw_create_user['tenant_id']   = $_SESSION['controller']['tenant_id'];
                $kiw_create_user['username']    = $mac_address;
                $kiw_create_user['source']      = "system";

                $kiw_db->query(sql_insert($kiw_db, "kiwire_account_info", $kiw_create_user));

                unset($kiw_create_user);


            }


        }


    }


    login_user($mac_address, $mac_address, $session_id);


} else {


    error_redirect($_SERVER['HTTP_REFERER'], "You are not allowed to access this module.");


}