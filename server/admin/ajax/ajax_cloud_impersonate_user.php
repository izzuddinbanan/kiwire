<?php

$kiw['module'] = "Cloud -> Impersonate User";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_general.php";
require_once "../includes/include_connection.php";

if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));
}

$action = $_REQUEST['action'];

switch ($action) {
    case "get_all": get_data(); break;
    case "login": change_user(); break;
    default: echo "ERROR: Wrong implementation";
}


function get_data()
{
    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->fetch_array("SELECT  tenant_id, username, fullname, email, lastlogin, password FROM kiwire_admin WHERE tenant_id != 'superuser' LIMIT 1000");

        if($kiw_temp){

            echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));
        }
        else{
            
            echo json_encode(array("status" => "failed", "message" => null, "data" => null));
        }

    }

}

function change_user(){

    global $kiw_db, $tenant_id;

    $kiw_tenant     = $kiw_db->escape($_REQUEST['tenant_id']);
    $kiw_username   = $kiw_db->escape($_REQUEST['username']);
    $kiw_password   = $kiw_db->escape($_REQUEST['pass']);

    if(empty($kiw_tenant) && empty($kiw_username) && empty($kiw_password)){

        echo json_encode(array("status" => "failed", "message" => "Invalid User.", "data" => null));

        die();
    }

    $kiw_user = $kiw_db->fetch_array("SELECT SQL_CACHE * FROM kiwire_admin WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant}' LIMIT 1")[0];

    if($_SESSION['access_level'] == "superuser"){
        $kiw_curr_user = $kiw_db->fetch_array("SELECT SQL_CACHE * FROM kiwire_admin WHERE username = '{$_SESSION['user_name']}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1")[0];
    }

    if ($kiw_user) {

        if ($kiw_user['password'] == $kiw_password) {


            $_SESSION['back_superuser'] = true;
            $_SESSION['user_superuser'] = $_SESSION['access_level'] == "superuser" ? $kiw_curr_user['username'] : '';
            $_SESSION['pass_superuser'] = $_SESSION['access_level'] == "superuser" ? $kiw_curr_user['password'] : '';
            $_SESSION['id']             = $kiw_user['id'];
            $_SESSION['permission']     = $kiw_user['permission'];
            $_SESSION['role']           = $kiw_user['groupname'];
            $_SESSION['theme']          = $kiw_user['theme'];
            $_SESSION['email']          = $kiw_user['email'];
            $_SESSION['photo']          = $kiw_user['photo'];


            $_SESSION['user_name']              = $kiw_user['username'];
            $_SESSION['full_name']              = $kiw_user['fullname'];
            $_SESSION['first_login']            = $kiw_user['first_login'];
            $_SESSION['last_password_change']   = $kiw_user['last_change_pass'];


            $_SESSION['last_active'] = time();


            $_SESSION['system_admin'] = true;

            if ($kiw_tenant == "superuser") {


                $_SESSION['access_level']   = "superuser";
                $_SESSION['tenant_allowed'] = $kiw_user['tenant_allowed'];
                $_SESSION['tenant_id']      = $kiw_user['tenant_default'];


            } else {


                $_SESSION['access_level'] = "administrator";
                $_SESSION['tenant_id'] = $kiw_user['tenant_id'];


            }


            $kiw_data = $kiw_db->fetch_array("SELECT SQL_CACHE moduleid FROM kiwire_admin_group WHERE groupname = '{$_SESSION['role']}' AND tenant_id = '{$kiw_tenant}' ORDER BY moduleid ASC LIMIT 500");

            if ($kiw_data) {


                $_SESSION['access_group'] = array();

                foreach ($kiw_data as $role) {


                    $kiw_temp = trim($role['moduleid']);
                    $kiw_temp_group = trim(explode('->', $kiw_temp)[0]);


                    if (!in_array($kiw_temp_group, $_SESSION['access_group'])) $_SESSION['access_group'][] =  $kiw_temp_group;


                    $_SESSION['access_list'][] = $kiw_temp;


                }

                unset($kiw_data);


            } else {

                echo json_encode(array("status" => "failed", "message" => "There is no role setup for this user.", "data" => null));

                die();

            }


            $kiw_data = $kiw_db->fetch_array("SELECT * FROM kiwire_clouds WHERE tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1")[0];

            if ($kiw_data) {


                $_SESSION['metrics'] = $kiw_data['volume_metrics'];

                $_SESSION['company_name'] = $kiw_data['name'];

                $_SESSION['timezone'] = $kiw_data['timezone'];

                $_SESSION['date_format'] = $kiw_data['date_format'];


                if (empty($_SESSION['metrics'])) $_SESSION['metrics'] = "Gb";

                if (empty($_SESSION['timezone'])) $_SESSION['timezone'] = "Asia/Kuala_Lumpur";

                if (empty($_SESSION['style'])){
                    if($kiw_data['custom_style'] == 'y') $_SESSION['style'] = true;
                    else if($kiw_data['custom_style'] == 'n') $_SESSION['style'] = false;
                }


            } else {

                echo json_encode(array("status" => "failed", "message" => "Missing tenant information", "data" => null));

                die();

            }


            // set a default timezone if user not set

            if (empty($_SESSION['timezone'])) $_SESSION['timezone'] = "Asia/Kuala_Lumpur";

            
            // set a default date format if user not set

            if (empty($_SESSION['date_format'])) $_SESSION['date_format'] = "d-m-Y";



            // update last login for admin user

            // $kiw_db->query("UPDATE kiwire_admin SET updated_date = NOW(), lastlogin = NOW() WHERE tenant_id = '{$kiw_tenant}' AND username = '{$kiw_username}' LIMIT 1");

            echo json_encode(array("status" => "success", "message" => "", "data" => array("next" => "default", "page" => "/admin/dashboard.php")));
            
        } else {


            echo json_encode(array("status" => "failed", "message" => "Incorrect username or password has been provided", "data" => null));


        }


    } else {


        echo json_encode(array("status" => "failed", "message" => "Incorrect username or password has been provided.", "data" => null));


    }

    
}

