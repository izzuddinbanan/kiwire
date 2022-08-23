<?php

$kiw['module'] = "Login Engine -> Journey";
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


$action = $_REQUEST['journey_action'];

switch ($action) {

    case "get_path": get_path($kiw_db, $_SESSION['tenant_id']); break;
    case "create": create($kiw_db, $_SESSION['tenant_id']); break;
    case "update": update($kiw_db, $_SESSION['tenant_id']); break;
    case "get_all": get_data($kiw_db, $_SESSION['tenant_id']); break;
    case "get_update": get_update($kiw_db, $_SESSION['tenant_id']); break;
    case "delete": delete($kiw_db, $_SESSION['tenant_id']); break;
    default: echo "ERROR: Wrong implementation";

}


function get_path($kiw_db, $tenant_id){

    $kiw_temp = $kiw_db->escape($_REQUEST['page_id']);

    if (!empty($kiw_temp)){

        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_login_pages WHERE tenant_id = '{$tenant_id}' AND unique_id = '{$kiw_temp}' LIMIT 1");

        if (!empty($kiw_temp)){

            echo json_encode(array("status" => "success", "message" => null, "data" => array("url" => "/custom/{$tenant_id}/thumbnails/{$kiw_temp['unique_id']}.png", "page_name" => $kiw_temp['page_name'])));

        }

    }

}

function create($kiw_db, $tenant_id)
{

    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        $kiw_temp['page_list']      = $kiw_db->escape($_REQUEST['zone_pages']);
        $kiw_temp['journey_name']   = $kiw_db->sanitize($_REQUEST['journey_name']);
        $kiw_temp['lang']           = $kiw_db->escape($_REQUEST['journey_lang']);

        $kiw_temp['pre_login']        = $kiw_db->escape($_REQUEST['journey_pre']);
        $kiw_temp['pre_login_url']    = urlencode($kiw_db->escape($_REQUEST['journey_pre_url']));
        $kiw_temp['post_login']       = $kiw_db->escape($_REQUEST['journey_post']);
        $kiw_temp['post_login_url']   = urlencode($kiw_db->escape($_REQUEST['journey_post_url']));
        

        $kiw_temp['tenant_id']      = $tenant_id;

        if ($_REQUEST['journey_status'] == "true") $kiw_temp['status'] = "y";
        else $kiw_temp['status'] = "n";

        $journey = $kiw_db->query("INSERT INTO kiwire_login_journey(tenant_id, page_list, status, journey_name, lang, pre_login, pre_login_url, post_login, post_login_url) VALUE ('{$kiw_temp['tenant_id']}', '{$kiw_temp['page_list']}', '{$kiw_temp['status']}', '{$kiw_temp['journey_name']}', '{$kiw_temp['lang']}', '{$kiw_temp['pre_login']}', '{$kiw_temp['pre_login_url']}', '{$kiw_temp['post_login']}', '{$kiw_temp['post_login_url']}')");

        if($journey) {

        // if($kiw_db->insert("kiwire_login_journey", $kiw_temp)){

            sync_logger("{$_SESSION['user_name']} create Journey {$kiw_temp['journey_name']}", $_SESSION['tenant_id']);

            echo json_encode(array("status" => "success", "message" => "SUCCESS: Login journey [{$kiw_temp['journey_name']}] has been created", "data" => null));
    
    
        }
        else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }
        
        
    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}

function update($kiw_db, $tenant_id)
{

    if (in_array($_SESSION['permission'], array("w", "rw"))) {


        $kiw_temp['page_list']      = $kiw_db->escape($_REQUEST['zone_pages']);
        $kiw_temp['journey_name']   = $kiw_db->sanitize($_REQUEST['journey_name']);
        $kiw_temp['lang']           = $kiw_db->escape($_REQUEST['journey_lang']);

        $kiw_temp['pre_login']        = $kiw_db->escape($_REQUEST['journey_pre']);
        $kiw_temp['pre_login_url']    = urlencode($kiw_db->escape($_REQUEST['journey_pre_url']));
        $kiw_temp['post_login']       = $kiw_db->escape($_REQUEST['journey_post']);
        $kiw_temp['post_login_url']   = urlencode($kiw_db->escape($_REQUEST['journey_post_url']));

        $kiw_temp['tenant_id']      = $tenant_id;

        if ($_REQUEST['journey_status'] == "true") $kiw_temp['status'] = "y";
        else $kiw_temp['status'] = "n";

        $kiw_update = $kiw_db->query("UPDATE kiwire_login_journey SET updated_date = NOW(), journey_name = '{$kiw_temp['journey_name']}', page_list = '{$kiw_temp['page_list']}', status = '{$kiw_temp['status']}', lang = '{$kiw_temp['lang']}', pre_login = '{$kiw_temp['pre_login']}', pre_login_url = '{$kiw_temp['pre_login_url']}', post_login = '{$kiw_temp['post_login']}', post_login_url = '{$kiw_temp['post_login_url']}'  WHERE tenant_id = '{$tenant_id}' AND journey_name = '{$kiw_temp['journey_name']}' LIMIT 1");

        if($kiw_update) {

        // if($kiw_db->update("kiwire_login_journey", $kiw_temp, "tenant_id = '{$tenant_id}' AND journey_name = '{$kiw_temp['journey_name']}' LIMIT 1")){
            
            sync_logger("{$_SESSION['user_name']} updated Journey {$kiw_temp['journey_name']}", $_SESSION['tenant_id']);
    
            echo json_encode(array("status" => "success", "message" => "SUCCESS: Login journey [{$kiw_temp['journey_name']}] has been updated", "data" => null));
        }
        else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

        }




    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}


function get_data($kiw_db, $tenant_id)
{


    if (in_array($_SESSION['permission'], array("r", "rw"))) {

        $kiw_temp = $kiw_db->fetch_array("SELECT  * FROM kiwire_login_journey WHERE tenant_id = '{$tenant_id}' LIMIT 1000");


        for ($i = 0; $i < count($kiw_temp); $i++){

            $kiw_temp[$i]['pre_login'] = ucfirst($kiw_temp[$i]['pre_login']);
            $kiw_temp[$i]['post_login'] = ucfirst($kiw_temp[$i]['post_login']);

        }


        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));

    }


}


function get_update($kiw_db, $tenant_id){


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->escape($_POST['journey_name']);


        if (!empty($kiw_temp)){


            $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_login_journey WHERE journey_name = '{$kiw_temp}' AND tenant_id = '{$tenant_id}' LIMIT 1");


            if (count($kiw_temp) > 0) {

                echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));

            } else {

                echo json_encode(array("status" => "error", "message" => "No journey with the specific name", "data" => null));

            }


        } else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Missing journey name", "data" => null));

        }


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}


function delete($kiw_db, $tenant_id)
{

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        $kiw_temp = $kiw_db->escape($_POST['journey_name']);


        if (!empty($kiw_temp)) {

            $kiw_db->query("DELETE FROM kiwire_login_journey WHERE journey_name = '{$kiw_temp}' AND tenant_id = '$tenant_id' LIMIT 1");

        }


        sync_logger("{$_SESSION['user_name']} create Journey {$kiw_temp}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Journey [{$kiw_temp}] has been deleted", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }

}
