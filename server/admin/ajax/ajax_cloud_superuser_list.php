<?php

$kiw['module'] = "Cloud -> Manage Superuser";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;


header("Content-Type: application/json");


require_once "../includes/include_session.php";
require_once "../includes/include_connection.php";
require_once "../includes/include_general.php";

require_once dirname(__FILE__, 3) . "/libs/class.sql.helper.php";


if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}


$action = $_REQUEST['action'];


switch ($action) {

    case "reset": reset_mfactor_key($kiw_db); break;
    case "create": create(); break;
    case "delete": delete(); break;
    case "get_all": get_all(); break;
    case "get_update": get_update(); break;
    case "edit_single_data": edit_single_data(); break;
    default: echo "ERROR: Wrong implementation";

}


function create()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {
        csrf($kiw_db->escape($_REQUEST['token']));

        $kiw_admin = $kiw_db->sanitize($_REQUEST['username']);

        $kiw_row = $kiw_db->query_first("SELECT COUNT(*) AS ccount FROM kiwire_admin WHERE username = '{$kiw_admin}' AND tenant_id = 'superuser'");


        if ($kiw_row['ccount'] < 1) {


            $data['username']           = $kiw_admin;
            $data['password']           = sync_encrypt($_REQUEST['password']);
            $data['tenant_id']          = "superuser";
            $data['updated_date']       = date('Y-m-d H-i-s');

            $data['fullname']           = $kiw_db->sanitize($_REQUEST['fullname']);
            $data['email']              = $kiw_db->sanitize($_REQUEST['email']);
            $data['permission']         = $kiw_db->escape($_REQUEST['permission']);
            $data['monitor']            = $_REQUEST['monitor'] == "y" ? "y" : "n";

            $data['require_mfactor']    = $_REQUEST['2-factors'] == "y" ? "y" : "n";
            $data['temp_pass']          = 1;
            $data['groupname']          = (empty($_REQUEST['groupname']) ? "" : $_REQUEST['groupname']);
            $data['tenant_default']     = $_REQUEST['tenant_default'];

            $data['tenant_allowed']     = (isset($_REQUEST['tenants'])) ? implode(",", $_REQUEST['tenants']) : "";

            if($kiw_db->insert("kiwire_admin", $data)){

                if (isset($_REQUEST['send_email'])){


                    $kiw_email_content = @file_get_contents(dirname(__FILE__, 3) . "/user/templates/email-admin.html");
    
    
                    if (!empty($kiw_email_content)){
    
    
                        // get the first row as subject
    
                        $kiw_subject = explode(PHP_EOL, $kiw_email_content)[0];
    
                        $kiw_subject = trim($kiw_subject);
    
    
                        $kiw_email_content = preg_replace('/^.+\n/', '', $kiw_email_content);
    
    
                        $kiw_email = array();
    
    
                        $kiw_email['content'] = htmlentities(str_replace(array('{{username}}', '{{password}}', '{{tenant_id}}'), array($kiw_admin, $_REQUEST['password'], "superuser"), $kiw_email_content));
    
    
                        $kiw_email['action']        = "send_email";
                        $kiw_email['tenant_id']     = "superuser";
                        $kiw_email['email_address'] = $kiw_db->escape($_REQUEST['email']);
                        $kiw_email['subject']       = $kiw_subject;
                        $kiw_email['name']          = $kiw_db->escape($_REQUEST['fullname']);
    
    
                        $kiw_connection = curl_init();
    
    
                        curl_setopt($kiw_connection, CURLOPT_URL, "http://127.0.0.1:9956");
                        curl_setopt($kiw_connection, CURLOPT_POST, true);
                        curl_setopt($kiw_connection, CURLOPT_POSTFIELDS, http_build_query($kiw_email));
                        curl_setopt($kiw_connection, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($kiw_connection, CURLOPT_TIMEOUT, 5);
                        curl_setopt($kiw_connection, CURLOPT_CONNECTTIMEOUT, 5);
    
    
                        curl_exec($kiw_connection);
                        curl_close($kiw_connection);
    
    
                    }
    
    
                }
    
    
                sync_logger("{$_SESSION['user_name']} create superadmin {$_REQUEST['username']}", "general");
    
                echo json_encode(array("status" => "success", "message" => "SUCCESS: Super admin has been successfully created", "data" => null));

            }
            else {

                echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

            }


        } else {

            echo json_encode(array("status" => "Error", "message" => "ERROR: Username already exists!", "data" => null));

        }

    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }
}


function delete()
{
    global $kiw_db, $tenant_id;


    if (in_array($_SESSION['permission'], array("w", "rw"))) {
        csrf($kiw_db->escape($_REQUEST['token']));

        $kiw_row = $kiw_db->query_first("SELECT COUNT(*) AS tcount FROM kiwire_admin WHERE tenant_id ='superuser'");

        if ($kiw_row['tcount'] > 1) {


            $id = $kiw_db->escape($_GET['id']);

            $kiw_db->query("DELETE FROM kiwire_admin WHERE username = '{$id}' AND tenant_id = 'superuser' LIMIT 1");


            sync_logger("{$_SESSION['user_name']} deleted administrator {$id}", "general");

            echo json_encode(array("status" => "success", "message" => "SUCCESS: Administrator [ {$id} ] has been deleted", "data" => null));


        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: You need to have at least one super admin", "data" => null));

        }

    }


}


function get_all()
{

    global $kiw_db;


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->fetch_array("SELECT * FROM kiwire_admin WHERE tenant_id = 'superuser' LIMIT 1000");


        for ($x = 0; $x < count($kiw_temp); $x++) {

            if ($kiw_temp[$x]['lastlogin'] != null) {

                $kiw_temp[$x]['lastlogin'] = sync_tolocaltime($kiw_temp[$x]['lastlogin'], $_SESSION['timezone']);
        
            } else {

                $kiw_temp[$x]['lastlogin'] = "Never";
            }
            
        }


        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }


}



function get_update()
{


    global $kiw_db;

    $id = $kiw_db->escape($_REQUEST['id']);


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_admin WHERE tenant_id = 'superuser' AND username = '{$id}' LIMIT 1");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}



function edit_single_data()
{

    global $kiw_db;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $id = $kiw_db->escape($_REQUEST['reference']);


        // if new password inserted, then update database

        if (sync_decrypt($_REQUEST['password']) == false) {

            $kiw_temp['password'] = sync_encrypt($_REQUEST['password']);

        }


        $kiw_temp['username']   = $kiw_db->sanitize($_REQUEST['username']);
        $kiw_temp['fullname']   = $kiw_db->sanitize($_REQUEST['fullname']);
        $kiw_temp['email']      = $kiw_db->sanitize($_REQUEST['email']);

        $kiw_temp['permission']     = $kiw_db->escape($_REQUEST['permission']);
        $kiw_temp['monitor']        = $kiw_db->escape(empty($_REQUEST['monitor']) ? "n" : "y");
        $kiw_temp['groupname']      = $kiw_db->escape(empty($_REQUEST['groupname']) ? "" : $_REQUEST['groupname']);

        $kiw_temp['require_mfactor'] = $_REQUEST['2-factors'] == "y" ? "y" : "n";
        $kiw_temp['tenant_default']  = $kiw_db->escape($_REQUEST['tenant_default']);
        $kiw_temp['tenant_allowed']  = $kiw_db->escape(implode(",", $_REQUEST['tenants']));

        $kiw_db->query(sql_update($kiw_db, "kiwire_admin", $kiw_temp, "id = '{$id}' AND tenant_id = 'superuser' LIMIT 1"));

        sync_logger("{$_SESSION['user_name']} updated superuser {$kiw_temp['username']}", "general");

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Superuser has been updated", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}


function reset_mfactor_key($kiw_db){


    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        if (in_array("General -> Reset 2-Factors", $_SESSION['access_list'])) {


            $kiw_username = $kiw_db->escape($_REQUEST['username']);


            if (!empty($kiw_username)) {


                $kiw_db->query("UPDATE kiwire_admin SET updated_date = NOW(), mfactor_key = NULL WHERE username = '{$kiw_username}' AND tenant_id = 'superuser' LIMIT 1");

                echo json_encode(array("status" => "success", "message" => "SUCCESS: Key for [ {$kiw_username} ] has been reset", "data" => null));


            } else {

                die(json_encode(array("status" => "failed", "message" => "ERROR: Missing username to be reset", "data" => null)));

            }


        } else {

            die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

        }


    }


}