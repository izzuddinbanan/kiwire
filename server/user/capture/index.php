<?php


require_once dirname(__FILE__, 2) . "/includes/include_session.php";
require_once dirname(__FILE__, 2) . "/includes/include_account.php";

require_once dirname(__FILE__, 3) . "/admin/includes/include_connection.php";
require_once dirname(__FILE__, 3) . "/libs/class.sql.helper.php";


$kiw_username = $_REQUEST['username'];
$kiw_password = $_REQUEST['password'];


$kiw_mapping = file_get_contents(dirname(__FILE__, 3) . "/custom/{$_SESSION['controller']['tenant_id']}/data-mapping.json");
$kiw_mapping = json_decode($kiw_mapping, true);


// collect all necessary data

if (is_array($kiw_mapping)) {


    foreach ($kiw_mapping as $kiw_map) {


        if ($kiw_map['variable'] != "[empty]") {


            foreach ($_REQUEST as $item => $value) {


                if ($kiw_map['variable'] == $item) {


                    $kiw_create_user[$kiw_map['field']] = preg_replace('/[^A-Za-z0-9\-._@]/', '', $_REQUEST[$item]);

                    break;


                }


            }


        }


    }


    $kiw_create_user['tenant_id'] = $_SESSION['controller']['tenant_id'];
    $kiw_create_user['username'] = $kiw_username;
    $kiw_create_user['source'] = "capture";


    $kiw_db->query(sql_insert($kiw_db, "kiwire_account_info", $kiw_create_user));


}

login_user($kiw_username, $kiw_password, $_REQUEST['session']);
