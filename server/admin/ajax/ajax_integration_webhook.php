<?php

$kiw['module'] = "Integration -> Web hook";
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



// function create()
// {

//     global $kiw_db, $tenant_id;

//     if (in_array($_SESSION['permission'], array("w", "rw"))) {

//         $data['status']            = isset($_REQUEST['status']) ? "y" : "n";;
//         $data['name']              = $_REQUEST['name'];
//         $data['when']              = $_REQUEST['when'];
//         $data['url']               = $_REQUEST['url'];

//         $data['method']            = $_REQUEST['method'];
//         $data['payload']           = $_REQUEST['payload'];
//         $data['header']            = $_REQUEST['header'];
//         $data['tenant_id']         = $tenant_id;

//         $kiw_db->insert("kiwire_int_webhook", $data);


//         sync_logger("{$_SESSION['user_name']} create web hook {$_GET['name']}", $_SESSION['tenant_id']);

      
//         echo json_encode(array("status" => "success", "message" => "SUCCESS: New Web Hook : " . $_REQUEST['name'] . " added", "data" => null));
  
//     } else {

//         echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
  
//     }

// }


function create()
{

    global $kiw_db, $tenant_id;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));
        // $kiw_header = array();

        // $kiw_header['web-header']  = $_REQUEST['header'];


        // $kiw_data = json_encode($kiw_header);

        // unset($kiw_header);
    
        $header = $_REQUEST['header'];


        $header_array = array_merge(array(date("Y-m-d H:i:s") => "{$header}"));

        $header_str = implode(" , ",$header_array);

        $data['status']            = isset($_REQUEST['status']) ? "y" : "n";;
        $data['name']              = $kiw_db->escape($_REQUEST['name']);
        $data['when']              = $kiw_db->escape($_REQUEST['when']);
        $data['url']               = $kiw_db->escape($_REQUEST['url']);

        $data['method']            = $kiw_db->escape($_REQUEST['method']);
        $data['payload']           = $kiw_db->escape($_REQUEST['payload']);
        $data['header']            = $header_str;
        $data['tenant_id']         = $tenant_id;

        // $data['is_24_hour']     = isset($_REQUEST['is_24']) && $_REQUEST['is_24'] ? 1 : 0;
        // $data['start_time']     = $kiw_db->escape($_REQUEST['start_time']);

        // if(!$data['is_24_hour']){
            
        //     if($_REQUEST['start_time'] == $_REQUEST['stop_time'])
        //         die(json_encode(array("status" => "failed", "message" => "ERROR: Stop Time cannot same as Start Time", "data" => null)));
        //     else
        //         $data['stop_time'] =   $kiw_db->escape($_REQUEST['stop_time']);
        
        //     }



        if($kiw_db->insert("kiwire_int_webhook", $data)){
        
            sync_logger("{$_SESSION['user_name']} create web hook {$_GET['name']}", $_SESSION['tenant_id']);
        
            echo json_encode(array("status" => "success", "message" => "SUCCESS: New Web Hook : " . $_REQUEST['name'] . " added", "data" => null));
            
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


            $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_int_webhook WHERE id = '{$id}' AND tenant_id = '{$tenant_id}'");

            $del_name = $kiw_temp['name'];

            $kiw_db->query("DELETE FROM kiwire_int_webhook WHERE id = '{$id}' AND tenant_id = '{$tenant_id}'");
        }


        sync_logger("{$_SESSION['user_name']} delete Web Hook {$del_name}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Web Hook : $del_name has been deleted", "data" => null));
   
   
    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));
  
    }

}



function get_data()
{

    global $kiw_db, $tenant_id;


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->fetch_array("SELECT * FROM kiwire_int_webhook WHERE tenant_id = '{$tenant_id}' LIMIT 1000");


        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));
   
    }

}



function get_single_data()
{

    global $kiw_db, $tenant_id;


    $id = $kiw_db->escape($_REQUEST['id']);


    if (in_array($_SESSION['permission'], array("r", "rw"))) {


        $kiw_temp = $kiw_db->query_first("SELECT * FROM kiwire_int_webhook WHERE tenant_id = '{$tenant_id}' AND id = '{$id}' LIMIT 1");


        echo json_encode(array("status" => "success", "message" => null, "data" => $kiw_temp));

    }

}




function edit_single_data()
{

    global $kiw_db;

    if (in_array($_SESSION['permission'], array("w", "rw"))) {

        csrf($kiw_db->escape($_REQUEST['token']));
        
        $kiw_id = $kiw_db->escape($_REQUEST['reference']);

        $kiw_data['status']    = isset($_REQUEST['status']) ? "y" : "n";
        $kiw_data['name']      = $kiw_db->escape($_REQUEST['name']);
        $kiw_data['when']      = $kiw_db->escape($_REQUEST['when']);
        $kiw_data['url']       = $kiw_db->escape($_REQUEST['url']);

        $kiw_data['method']    = $kiw_db->escape($_REQUEST['method']);
        $kiw_data['payload']   = $kiw_db->escape($_REQUEST['payload']);
        $kiw_data['header']    = $kiw_db->escape($_REQUEST['header']);

        $kiw_data['is_24_hour']     = isset($_REQUEST['is_24']) && $_REQUEST['is_24'] ? 1 : 0;
        $kiw_data['start_time']     = $kiw_db->escape($_REQUEST['start_time']);
        
        if(!$kiw_data['is_24_hour']){
            
            if($_REQUEST['start_time'] == $_REQUEST['stop_time'])
                die(json_encode(array("status" => "failed", "message" => "ERROR: Stop Time cannot same as Start Time", "data" => null)));
            else
                $kiw_data['stop_time'] =   $kiw_db->escape($_REQUEST['stop_time']);
        }

        $kiw_db->query(sql_update($kiw_db, "kiwire_int_webhook", $kiw_data, "id = '{$kiw_id}' AND tenant_id = '{$_SESSION['tenant_id']}' LIMIT 1"));


        sync_logger("{$_SESSION['user_name']} updated Web Hook {$kiw_data['name']}", $_SESSION['tenant_id']);

        echo json_encode(array("status" => "success", "message" => "SUCCESS: Web Hook has been updated", "data" => null));

    } else {

        echo json_encode(array("status" => "failed", "message" => "ERROR: You are not allowed to access this module", "data" => null));

    }
    
}

