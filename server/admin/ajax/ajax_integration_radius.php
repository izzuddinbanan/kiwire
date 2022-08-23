<?php

$kiw['module'] = "Integration -> Realm";
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

        $data['domain']             = $kiw_db->escape($_REQUEST['domain']);
        $data['host']               = $kiw_db->escape($_REQUEST['host']);
        $data['secret']             = $kiw_db->escape($_REQUEST['secret']);

        $data['nasid']              = $kiw_db->escape($_REQUEST['nasid']);
        $data['forward_profile']    = $kiw_db->escape($_REQUEST['forward_profile']);
        $data['validity']           = $kiw_db->escape($_REQUEST['validity']);
        $data['keyword_str']        = $kiw_db->escape($_REQUEST['keyword_str']);

        $data['profile']            = $kiw_db->escape($_REQUEST['profile']);
        $data['data_type']          = $kiw_db->escape($_REQUEST['data_type']);
        $data['allowed_zone']       = $kiw_db->escape($_REQUEST['allowed_zone']);
        $data['use_domain']         = isset($_REQUEST['use_domain']) ? "y" : "n";

        $data['enabled']            = isset($_REQUEST['enabled']) ? "y" : "n";;
        $data['tenant_id']          = $tenant_id;

        // $data['is_24_hour']     = isset($_REQUEST['is_24']) && $_REQUEST['is_24'] ? 1 : 0;
        // $data['start_time']     = $kiw_db->escape($_REQUEST['start_time']);

        // if(!$data['is_24_hour']){
        //     if($_REQUEST['start_time'] == $_REQUEST['stop_time'])
        //         die(json_encode(array("status" => "failed", "message" => "ERROR: Stop Time cannot same as Start Time", "data" => null)));
        //     else
        //         $data['stop_time'] =   $kiw_db->escape($_REQUEST['stop_time']);

        // }

        if($kiw_db->insert("kiwire_int_radius", $data)){

            sync_logger("{$_SESSION['user_name']} create Radius {$_GET['domain']}", $_SESSION['tenant_id']);

            echo json_encode(array("status" => "success", "message" => "SUCCESS: New Radius : " . $_REQUEST['domain'] . " added", "data" => null));
    
        }
        else {

            echo json_encode(array("status" => "failed", "message" => "ERROR: Please check your data or contact our administrator", "data" => null));

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


            $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_int_radius WHERE id = '{$id}' AND tenant_id = '{$tenant_id}'");

            $del_radius = $kiw_temp['realm'];

            $kiw_db->query("DELETE FROM kiwire_int_radius WHERE id = '{$id}' AND tenant_id = '{$tenant_id}'");


        }


        sync_logger("{$_SESSION['user_name']} delete Radius {$del_radius}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Radius : $del_radius has been deleted", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}


function get_data()

{
    global $kiw_db, $tenant_id;


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->fetch_array("SELECT * FROM kiwire_int_radius WHERE tenant_id = '{$tenant_id}' LIMIT 1000");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }


}


function get_single_data()

{

    global $kiw_db, $tenant_id;


    $id = $kiw_db->escape($_REQUEST['id']);


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_int_radius WHERE tenant_id = '{$tenant_id}' AND id = '{$id}' LIMIT 1");

        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));


    }


}


function edit_single_data()
{
    
    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));
        
        $kiw_id = $kiw_db->escape($_REQUEST['reference']);

        $kiw_data['domain']   = $kiw_db->escape($_REQUEST['domain']);
        $kiw_data['host']     = $kiw_db->escape($_REQUEST['host']);
        $kiw_data['secret']   = $kiw_db->escape($_REQUEST['secret']);
        $kiw_data['nasid']    = $kiw_db->escape($_REQUEST['nasid']);

        $kiw_data['forward_profile']  = $kiw_db->escape($_REQUEST['forward_profile']);
        $kiw_data['profile']          = $kiw_db->escape($_REQUEST['profile']);
        $kiw_data['validity']         = $kiw_db->escape($_REQUEST['validity']);
        $kiw_data['keyword_str']      = $kiw_db->escape($_REQUEST['keyword_str']);

        $kiw_data['data_type']     = $kiw_db->escape($_REQUEST['data_type']);
        $kiw_data['allowed_zone']  = $kiw_db->escape($_REQUEST['allowed_zone']);
        $kiw_data['use_domain']    = isset($_REQUEST['use_domain']) ? "y" : "n";
        $kiw_data['enabled']       = isset($_REQUEST['enabled']) ? "y" : "n";
 

        // $kiw_data['is_24_hour']     = isset($_REQUEST['is_24']) && $_REQUEST['is_24'] ? 1 : 0;
        // $kiw_data['start_time']     = $kiw_db->escape($_REQUEST['start_time']);

        // if(!$kiw_data['is_24_hour']){
        //     if($_REQUEST['start_time'] == $_REQUEST['stop_time'])
        //         die(json_encode(array("status" => "failed", "message" => "ERROR: Stop Time cannot same as Start Time", "data" => null)));
        //     else
        //         $kiw_data['stop_time'] =   $kiw_db->escape($_REQUEST['stop_time']);

        // }


        $kiw_db->query(sql_update($kiw_db, "kiwire_int_radius", $kiw_data, "id = '{$kiw_id}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1"));
                        

        sync_logger("{$_SESSION['user_name']} updated Radius {$kiw_data['domain']}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Radius has been updated", "data" => null));


    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }


}


