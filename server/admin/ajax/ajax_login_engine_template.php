<?php

$kiw['module'] = "Login Engine -> Template Engine";
$kiw['name'] = basename($_SERVER['SCRIPT_NAME'], ".php");
$kiw['version'] = 1;
$kiw['custom'] = false;

header("Content-Type: application/json");

require_once "../includes/include_session.php";
require_once "../includes/include_general.php";
require_once "../includes/include_connection.php";

require_once "../../libs/class.sql.helper.php";


if (!in_array($kiw['module'], $_SESSION['access_list'])) {

    die(json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null)));

}

$action = $_REQUEST['action'];

switch ($action) {

    case "create": create(); break;
    case "delete": delete(); break;
    case "get_all": get_data(); break;
    case "get_update": get_single_data(); break;
    case "edit_single_data": edit_single_data(); break;
    default: echo "ERROR: Wrong implementation";

}


function create()
{
    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));

        $kiw_name = $kiw_db->sanitize(str_replace("'", "", $_GET['name']));

        $kiw_exist = $kiw_db->query_first("SELECT COUNT(*) AS ccount FROM kiwire_html_template WHERE name = '{$kiw_name}' AND tenant_id = '{$tenant_id}'");


        if ($kiw_exist['ccount'] < 1) {

            $data['id']             = "NULL";
            $data['tenant_id']      = $tenant_id;
            $data['updated_date']   = date('Y-m-d H-i-s');
            $data['type']           = $_REQUEST['type'];
            
            $data['name']           = $kiw_db->sanitize($_REQUEST['name']);
            $data['subject']        = $kiw_db->sanitize($_REQUEST['subject']);
            $data['content']        = trim($_REQUEST['content']);


            if (substr($data['content'], -3) == "<p>"){

                $data['content'] = substr($data['content'], 0, strlen($data['content']) - 3);

            }


            $data['content'] = str_replace('\n', '', $data['content']);


            if($kiw_db->insert("kiwire_html_template", $data)){

                sync_logger("{$_SESSION['user_name']} create Template {$data['name']}", $_SESSION['tenant_id']);

                echo json_encode(array("status" => "success", "message" => "SUCCESS: New Template : " . $kiw_db->escape($_GET['ref']) . " added", "data" => null));

            }
            else {

                echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));
    
            }

        } else {

            echo json_encode(array("status" => "Error", "message" => "ERROR: template name already exists!", "data" => null));

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

        $id = $kiw_db->escape($_POST['id']);

        if (!empty($id)) {


            $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_html_template WHERE id = '{$id}' AND tenant_id = '$tenant_id'");
            $kiw_temp = $kiw_temp['name'];

            $kiw_db->query("DELETE FROM kiwire_html_template WHERE id = '{$id}' AND tenant_id = '$tenant_id' LIMIT 1");


        }


        sync_logger("{$_SESSION['user_name']} deleted Template {$kiw_temp}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Template : [{$kiw_temp}] has been deleted", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}




function get_data()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->fetch_array("SELECT  * FROM kiwire_html_template WHERE tenant_id = '{$tenant_id}' LIMIT 1000");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }

}



function get_single_data()
{

    global $kiw_db, $tenant_id;

    $id = $kiw_db->escape($_REQUEST['id']);

    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_html_template WHERE tenant_id = '{$tenant_id}' AND id = '{$id}' LIMIT 1");

        $kiw_temp['content'] = stripcslashes($kiw_temp['content']);


        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }

}



function edit_single_data()
{

    global $kiw_db;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));
        
        $id = $kiw_db->escape($_REQUEST['reference']);

        $kiw_data['updated_date'] = date('Y-m-d H-i-s');

        $kiw_data['name']       = $kiw_db->sanitize($_REQUEST['name']);
        $kiw_data['subject']    = $kiw_db->sanitize($_REQUEST['subject']);
        $kiw_data['content']    = $kiw_db->escape($_REQUEST['content']);
        $kiw_data['type']       = $kiw_db->escape($_REQUEST['type']);


        if (substr($kiw_data['content'], -3) == "<p>"){

            $kiw_data['content'] = substr($kiw_data['content'], 0, strlen($kiw_data['content']) - 3);

        }


        $kiw_data['content'] = str_replace('\n', '', $kiw_data['content']);


        $kiw_db->query(sql_update($kiw_db, "kiwire_html_template", $kiw_data, "id = '{$id}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1"));


        sync_logger("{$_SESSION['user_name']} updated template {$kiw_data['name']}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Template has been updated", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}
