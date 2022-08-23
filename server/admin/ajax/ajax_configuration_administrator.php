<?php

$kiw['module'] = "Configuration -> Administrator";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_connection.php";
require_once "../includes/include_general.php";

if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));
    
}

$action = $_REQUEST['action'];

switch ($action) {

    case "reset": reset_mfactor_key($kiw_db, $_SESSION['tenant_id']); break;
    case "create": create(); break;
    case "delete": delete(); break;
    case "get_all": get_all(); break;
    case "get_update": get_update(); break;
    case "edit_single_data": edit_single_data(); break;
    case "topup": topup(); break;
    case "unblock_user": unblock_user(); break;

    default: echo "ERROR: Wrong implementation";
}


function unblock_user()
{

    global $kiw_db, $tenant_id;


    $id = $kiw_db->escape($_REQUEST['id']);

    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_admin WHERE tenant_id = '$tenant_id' AND id = '{$id}' LIMIT 1");

        if($kiw_temp){

            $kiw_db->query("UPDATE kiwire_admin SET is_active = 1 WHERE  tenant_id = '$tenant_id' AND id = '{$id}'");

            echo json_encode(array("status" => "success", "message" => null, "data" => NULL));
        }
        else{
            
            echo json_encode(array("status" => "failed", "message" => "ERROR: No user found", "data" => null));
        }
        

    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}

function create()
{
    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {
        csrf($kiw_db->escape($_REQUEST['token']));

        $kiw_row = $kiw_db->escape($_GET['username']);

        $kiw_row = $kiw_db->query_first("SELECT COUNT(*) AS ccount FROM kiwire_admin WHERE username = '{$kiw_row}' AND tenant_id = '{$tenant_id}'");


        if ($kiw_row['ccount'] < 1) {


            $data['username']   = $kiw_db->sanitize($_GET['username']);
            $data['password']   = sync_encrypt($_GET['password']);
            $data['fullname']   = $kiw_db->sanitize($_GET['fullname']);
            $data['email']      = $kiw_db->escape($_GET['email']);

            $data['monitor']    = (empty($_GET['monitor']) ? "n" : "y");
            $data['permission'] = $kiw_db->escape($_GET['permission']);
            $data['groupname']  = (empty($_GET['groupname']) ? "" : $_GET['groupname']);
            $data['tenant_id']  = $tenant_id;
            
            if($kiw_db->insert("kiwire_admin", $data)){

                if (isset($_REQUEST['send_email'])){


                    $kiw_email_content = @file_get_contents(dirname(__FILE__, 3) . "/user/templates/email-admin.html");
    
    
                    if (!empty($kiw_email_content)){
    
    
                        // get the first row as subject
    
                        $kiw_subject = explode(PHP_EOL, $kiw_email_content)[0];
    
                        $kiw_subject = trim($kiw_subject);
    
    
                        $kiw_email_content = preg_replace('/^.+\n/', '', $kiw_email_content);
    
    
                        $kiw_email = array();
    
    
                        $kiw_email['content'] = htmlentities(str_replace(array('{{username}}', '{{password}}', '{{tenant_id}}'), array($_GET['username'], $_GET['password'], $_SESSION['tenant_id']), $kiw_email_content));
    
    
                        $kiw_email['action']        = "send_email";
                        $kiw_email['tenant_id']     = $_SESSION['tenant_id'];
                        $kiw_email['email_address'] = $kiw_db->escape($_REQUEST['email']);
                        $kiw_email['subject']       = $kiw_subject;
                        $kiw_email['name']          = $kiw_db->escape($_REQUEST['fullname']);
    
    
                        $kiw_connection = curl_init();
    
                        file_put_contents("/tmp/email.log", http_build_query($kiw_email) . "\n", FILE_APPEND);
    
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
    
    
                sync_logger("{$_SESSION['user_name']} create administrator {$_GET['username']}", $_SESSION['tenant_id']);
        
                echo json_encode(array("status" => "success", "message" => "SUCCESS: New administrator added", "data" => null));

            }
            else {

                echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

            }


        } else {

            echo json_encode(array("status" => "Error", "message" => "ERROR: Username already exists!", "data" => null));

        }

    
    } else

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
}


function delete() {

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {
        csrf($kiw_db->escape($_REQUEST['token']));

        $id = $kiw_db->escape($_POST['id']);

        $kiw_row = $kiw_db->query_first("SELECT COUNT(*) AS tcount FROM kiwire_admin WHERE tenant_id ='$tenant_id'");


        if ($kiw_row['tcount'] > 1) {


            $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_admin WHERE id = '" . $id . "' AND tenant_id = '$tenant_id'");
            
            $del_username = $kiw_temp['username'];

            $kiw_db->query("DELETE FROM kiwire_admin WHERE id = '" . $id . "' AND tenant_id = '$tenant_id'");


            sync_logger("{$_SESSION['user_name']} deleted administrator {$del_username}", $_SESSION['tenant_id']);
            
            echo json_encode(array("status" => "success", "message" => "SUCCESS: Administrator : $del_username has been deleted", "data" => null));


        } else {


            $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_admin WHERE id = '" . $id . "' AND tenant_id = '$tenant_id'");
            
            echo json_encode(array("status" => "failed", "message" => "ERROR: You need to have at least one superadmin", "data" => null));


        }


    }


}


function get_all(){

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->fetch_array("SELECT * FROM kiwire_admin WHERE tenant_id = '{$tenant_id}' ORDER BY username");
        

        for($x = 0; $x < count($kiw_temp); $x++){

            $kiw_temp[$x]['user_permission'] = $_SESSION['permission'];
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
    global $kiw_db, $tenant_id;


    $id = $kiw_db->escape($_REQUEST['id']);

    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_admin WHERE tenant_id = '$tenant_id' AND id = '{$id}' LIMIT 1");
        
        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}


function edit_single_data()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {
        csrf($kiw_db->escape($_REQUEST['token']));

        $id = $kiw_db->escape($_REQUEST['reference']);


        if (sync_decrypt($_REQUEST['password']) == false) {

            $new_password = sync_encrypt($_REQUEST['password']);

        } else {

            $new_password = $_REQUEST['password'];

        }


        $new_username   = $kiw_db->sanitize($_REQUEST['username']);
        $new_fullname   = $kiw_db->sanitize($_REQUEST['fullname']);
        $new_email      = $kiw_db->escape($_REQUEST['email']);

        $new_monitor      = $kiw_db->escape(empty($_REQUEST['monitor']) ? "n" : "y");
        $new_permission   = $kiw_db->escape($_REQUEST['permission']);
        $new_groupname    = $kiw_db->escape(empty($_REQUEST['groupname']) ? "" : $_GET['groupname']);
        
        $kiw_db->query("UPDATE kiwire_admin SET updated_date = NOW(), username = '{$new_username}', password = '{$new_password}', fullname = '{$new_fullname}', email = '{$new_email}', monitor = '{$new_monitor}', permission = '{$new_permission}', groupname = '{$new_groupname}' WHERE id = '{$id}' AND tenant_id = '{$tenant_id}' LIMIT 1");
        

        sync_logger("{$_SESSION['user_name']} updated administrator {$new_username}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Administrator has been updated", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}


function topup()
{
    global $kiw_db, $tenant_id;
    
    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        $id = $kiw_db->escape($_REQUEST['id']);


        $new_bal   = (float)($_REQUEST['balance_credit']);
        
        $kiw_db->query("UPDATE kiwire_admin SET updated_date = NOW(), balance_credit = balance_credit + '{$new_bal}' WHERE id = '{$id}' AND tenant_id = '{$tenant_id}' LIMIT 1");
        

        sync_logger("{$_SESSION['user_name']} updated balance {$new_bal} for {$_REQUEST['username']}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Credit balance has been updated", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}

function reset_mfactor_key($kiw_db, $kiw_tenant){


    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        if (in_array("General -> Reset 2-Factors", $_SESSION['access_list'])) {


            $kiw_username = $kiw_db->escape($_REQUEST['username']);

            if (!empty($kiw_username)) {


                $kiw_db->query("UPDATE kiwire_admin SET updated_date = NOW(), mfactor_key = NULL WHERE username = '{$kiw_username}' AND tenant_id = '{$kiw_tenant}' LIMIT 1");

                echo json_encode(array("status" => "success", "message" => "SUCCESS: Key for [ {$kiw_username} ] has been reset", "data" => null));


            } else {

                die(json_encode(array("status" => "failed", "message" => "ERROR: Missing username to be reset", "data" => null)));

            }


        } else {

            die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

        }


    }


}


